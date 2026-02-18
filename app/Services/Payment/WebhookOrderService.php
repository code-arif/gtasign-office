<?php

namespace App\Services\Payment;

use App\Models\Chat;
use App\Models\Order;
use App\Models\OrderActivity;
use App\Models\SellerEarnings;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * WebhookOrderService
 *
 * Encapsulates the "activate order after payment" logic so that
 * BOTH the Stripe webhook handler AND the local test endpoint
 * use the exact same code path. No duplication, no drift.
 */
class WebhookOrderService
{



    /**
     * Activate an order after confirmed payment.
     * This is idempotent — safe to call multiple times on same order.
     */
    public function activateOrder(Order $order, array $paymentData = []): Order
    {
        $now = Carbon::now();

        // Guard: already processed
        if ($order->status !== 'pending_payment') {
            return $order;
        }

        DB::beginTransaction();
        try {
            // 1. Update order
            $order->update([
                'status' => 'active',
                'paid_at' => $now,
                'started_at' => $now,
                'funds_in_escrow' => true,
                'payment_method' => $paymentData['payment_method'] ?? 'stripe',
                'payment_intent_id' => $paymentData['payment_intent_id'] ?? null,
                'stripe_checkout_session_id' => $paymentData['session_id'] ?? null,
                'auto_complete_at' => $now->copy()->addDays(config('orders.auto_complete_days', 7)),
            ]);
            
            // 2. Increment gig order count
            if ($order->gig_id) {
                $order->gig()->increment('orders');
            }

            // 3. Create seller earning record (status: pending → will move to clearing when completed)
            SellerEarnings::firstOrCreate(
                ['order_id' => $order->id],
                [
                    'seller_id'    => $order->seller_id,
                    'gross_amount' => $order->price,
                    'platform_fee' => $order->platform_fee,
                    'net_amount'   => $order->seller_earnings,
                    'status'       => 'pending',
                ]
            );

            // 4. Mark room as having active order
            $order->room()->update(['has_active_order' => true]);

            // 5. System message in chat
            Chat::create([
                'sender_id'   => $order->buyer_id,
                'receiver_id' => $order->seller_id,
                'room_id'     => $order->room_id,
                'type'        => 'system',
                'order_id'    => $order->id,
                'text'        => "Payment received. Order #{$order->order_number} is now active. Delivery expected in {$order->delivery_days} day(s).",
            ]);

            $order->room()->update(['last_message_at' => now()]);

            // 6. Activity log
            OrderActivity::create([
                'order_id'    => $order->id,
                'user_id'     => null,
                'type'        => 'payment_received',
                'description' => 'Payment confirmed, order activated',
                'metadata'    => $paymentData,
            ]);

            DB::commit();

            return $order->fresh(['gig', 'buyer.profile', 'seller.profile', 'room']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Handle refund after payment failure / cancellation
     */
    public function handlePaymentFailed(Order $order): void
    {
        if (!in_array($order->status, ['pending_payment', 'active'])) {
            return;
        }

        DB::beginTransaction();
        try {
            $order->update([
                'status'              => 'cancelled',
                'cancelled_at'        => now(),
                'cancelled_by'        => 'admin',
                'cancellation_reason' => 'Payment failed or cancelled',
            ]);

            OrderActivity::create([
                'order_id'    => $order->id,
                'user_id'     => null,
                'type'        => 'order_cancelled',
                'description' => 'Order cancelled due to payment failure',
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
