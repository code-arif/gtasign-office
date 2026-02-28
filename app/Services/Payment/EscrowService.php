<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\SellerEarnings;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Models\OrderActivity;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * EscrowService
 *
 * Handles the full earning lifecycle:
 *  pending → clearing (14-day hold) → available → withdrawn
 *
 * Money flow:
 *  Client pays → funds in Stripe (platform account)
 *  Order completed → 14-day hold starts (SellerEarning: clearing)
 *  After 14 days → SellerEarning: available (expert can withdraw)
 *  Expert withdraws → Stripe Transfer to expert's Connected Account
 *
 * Why Stripe Connect?
 *  Admin has a platform Stripe account.
 *  Each expert has a Stripe Express account linked to the platform.
 *  Stripe handles the actual money movement via Transfers API.
 *  The platform_fee stays in the platform Stripe account automatically.
 */
class EscrowService
{
    public function __construct(
        protected StripePaymentService $stripeService
    ) {}

    // ─────────────────────────────────────────────────────────────────
    // ESCROW LIFECYCLE
    // ─────────────────────────────────────────────────────────────────

    /**
     * Move earning to "clearing" after order completion.
     * Called by InboxOrderService::acceptDelivery()
     */
    // public function startClearingPeriod(int $orderId, int $holdDays = 14): SellerEarnings
    // {
    //     $earning = SellerEarnings::where('order_id', $orderId)->first();

    //     if (!$earning) {
    //         throw new Exception("No earning record found for order #{$orderId}");
    //     }

    //     if ($earning->status !== 'pending') {
    //         throw new Exception("Earning is not in pending state (current: {$earning->status})");
    //     }

    //     $availableAt = now()->addDays($holdDays);

    //     $earning->update([
    //         'status'       => 'clearing',
    //         'available_at' => $availableAt,
    //     ]);

    //     // Update seller's pending_clearance balance on User model
    //     $earning->seller()->increment('pending_clearance', $earning->net_amount);

    //     return $earning->fresh();
    // }


    public function startClearingPeriod(int $orderId, int $holdDays = 14): SellerEarnings
    {
        $order = Order::find($orderId);

        // If you don't have Earning, create it now.
        $earning = SellerEarnings::firstOrCreate(
            ['order_id' => $orderId],
            [
                'seller_id'    => $order->seller_id,
                'gross_amount' => $order->price,
                'platform_fee' => $order->platform_fee,
                'net_amount'   => $order->seller_earnings,
                'status'       => 'pending',
            ]
        );

        if ($earning->status !== 'pending') {
            throw new Exception("Earning is not in pending state (current: {$earning->status})");
        }

        $availableAt = now()->addDays($holdDays);

        $earning->update([
            'status'       => 'clearing',
            'available_at' => $availableAt,
        ]);

        $earning->seller()->increment('pending_clearance', $earning->net_amount);

        return $earning->fresh();
    }

    /**
     * Release earnings after clearing period (called by scheduled command).
     * Moves SellerEarning from "clearing" → "available".
     * Updates seller's wallet/balance.
     */
    public function releaseClearedEarnings(): int
    {
        $released = 0;

        $earnings = SellerEarnings::with('seller', 'order')
            ->where('status', 'clearing')
            ->where('available_at', '<=', now())
            ->get();

        foreach ($earnings as $earning) {
            DB::beginTransaction();
            try {
                $earning->update(['status' => 'available']);

                // Move from pending_clearance → available_balance on User
                $earning->seller()->decrement('pending_clearance', $earning->net_amount);
                $earning->seller()->increment('available_balance', $earning->net_amount);

                OrderActivity::create([
                    'order_id'    => $earning->order_id,
                    'user_id'     => null,
                    'type'        => 'escrow_released',
                    'description' => "Escrow released: \${$earning->net_amount} now available to seller",
                ]);

                DB::commit();
                $released++;
            } catch (Exception $e) {
                DB::rollBack();
                Log::error("Failed to release earning #{$earning->id}: " . $e->getMessage());
            }
        }

        return $released;
    }

    // ─────────────────────────────────────────────────────────────────
    // WITHDRAWAL
    // ─────────────────────────────────────────────────────────────────

    /**
     * Process a withdrawal request — transfer to expert's Stripe Connect account.
     */
    public function processWithdrawal(int $withdrawalId): WithdrawalRequest
    {
        DB::beginTransaction();
        try {
            $withdrawal = WithdrawalRequest::with('seller.profile')->find($withdrawalId);

            if (!$withdrawal) {
                throw new Exception('Withdrawal request not found');
            }

            if ($withdrawal->status !== 'pending') {
                throw new Exception("Withdrawal is not in pending state (current: {$withdrawal->status})");
            }

            $seller = $withdrawal->seller;
            $stripeAccountId = $seller->profile?->stripe_account_id;

            if (!$stripeAccountId) {
                throw new Exception('Expert does not have a connected Stripe account');
            }

            // Check expert has enough available balance
            if ($seller->available_balance < $withdrawal->amount) {
                throw new Exception("Insufficient balance. Available: {$seller->available_balance}");
            }

            // Verify Stripe Connect account is ready
            if (!$this->stripeService->isConnectAccountReady($stripeAccountId)) {
                throw new Exception('Expert Stripe account is not fully onboarded');
            }

            // Execute Stripe Transfer
            $amountCents = (int) ($withdrawal->net_amount * 100);
            $transfer = $this->stripeService->processWithdrawal(
                $stripeAccountId,
                $amountCents,
                $withdrawal->id
            );

            // Update withdrawal record
            $withdrawal->update([
                'status'             => 'processing',
                'stripe_transfer_id' => $transfer->id,
                'processed_at'       => now(),
            ]);

            // Deduct from seller's available balance
            $seller->decrement('available_balance', $withdrawal->amount);

            // Mark related earnings as withdrawn
            SellerEarnings::where('seller_id', $seller->id)
                ->where('status', 'available')
                ->oldest('available_at')
                ->take($this->calculateEarningsToMark($withdrawal->amount))
                ->update([
                    'status'         => 'withdrawn',
                    'withdrawal_id'  => $withdrawal->id,
                    'withdrawn_at'   => now(),
                ]);

            DB::commit();

            return $withdrawal->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mark withdrawal as completed (called from webhook when transfer succeeds)
     */
    public function markWithdrawalCompleted(string $stripeTransferId): void
    {
        $withdrawal = WithdrawalRequest::where('stripe_transfer_id', $stripeTransferId)->first();

        if ($withdrawal) {
            $withdrawal->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────

    private function calculateEarningsToMark(float $amount): int
    {
        // Simple: mark all available earnings (sum should cover amount)
        // In production, implement proper FIFO matching
        return PHP_INT_MAX;
    }

    /**
     * Get seller's wallet summary
     */
    public function getWalletSummary(int $sellerId): array
    {
        $seller = User::findOrFail($sellerId);

        $earnings = SellerEarnings::where('seller_id', $sellerId);

        return [
            'available_balance'  => $seller->available_balance ?? 0,
            'pending_clearance'  => $seller->pending_clearance ?? 0,
            'total_earned'       => (clone $earnings)->whereIn('status', ['available', 'withdrawn', 'clearing'])->sum('net_amount'),
            'total_withdrawn'    => (clone $earnings)->where('status', 'withdrawn')->sum('net_amount'),
            'earnings_breakdown' => [
                'pending'   => (clone $earnings)->where('status', 'pending')->sum('net_amount'),
                'clearing'  => (clone $earnings)->where('status', 'clearing')->sum('net_amount'),
                'available' => (clone $earnings)->where('status', 'available')->sum('net_amount'),
                'withdrawn' => (clone $earnings)->where('status', 'withdrawn')->sum('net_amount'),
            ],
        ];
    }
}
