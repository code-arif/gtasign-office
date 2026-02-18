<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Withdrawal\CreateWithdrawalRequest;
use App\Models\WithdrawalRequest;
use App\Services\Payment\EscrowService;
use App\Services\Payment\StripePaymentService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StripeConnectController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected StripePaymentService $stripeService,
        protected EscrowService $escrowService,
    ) {}

    // ─────────────────────────────────────────────────────────────────
    // ONBOARDING
    // ─────────────────────────────────────────────────────────────────

    /**
     * Start Stripe Connect onboarding for expert
     * POST /api/expert/stripe/connect
     */
    public function connect()
    {
        try {
            $user = auth('api')->user();

            if (!$user->hasRole('expert')) {
                return $this->error(null, 'Only experts can connect Stripe accounts', 403);
            }

            $stripeAccountId = $user->profile?->stripe_account_id;

            // Create account if doesn't exist
            if (!$stripeAccountId) {
                $stripeAccountId = $this->stripeService->createConnectAccount($user);
            }

            // Generate onboarding link
            $onboardingUrl = $this->stripeService->createConnectOnboardingLink(
                $stripeAccountId,
                $user->id
            );

            return $this->success('Stripe onboarding link generated', [
                'onboarding_url'  => $onboardingUrl,
                'stripe_account'  => $stripeAccountId,
                'is_connected'    => false,
            ]);
        } catch (Exception $e) {
            Log::error('Stripe connect error: ' . $e->getMessage());
            return $this->error(null, 'Failed to initiate Stripe onboarding', 500);
        }
    }

    /**
     * Check if expert's Stripe account is fully onboarded
     * GET /api/expert/stripe/status
     */
    public function status()
    {
        try {
            $user            = auth('api')->user();
            $stripeAccountId = $user->profile?->stripe_account_id;

            if (!$stripeAccountId) {
                return $this->success('Stripe account not connected', [
                    'is_connected'    => false,
                    'can_withdraw'    => false,
                    'stripe_account'  => null,
                ]);
            }

            $isReady = $this->stripeService->isConnectAccountReady($stripeAccountId);

            // Update profile if newly onboarded
            if ($isReady && !$user->profile->stripe_onboarded_at) {
                $user->profile->update(['stripe_onboarded_at' => now()]);
            }

            return $this->success('Stripe account status', [
                'is_connected'   => true,
                'can_withdraw'   => $isReady,
                'stripe_account' => $stripeAccountId,
                'onboarded_at'   => $user->profile->stripe_onboarded_at,
            ]);
        } catch (Exception $e) {
            Log::error('Stripe status error: ' . $e->getMessage());
            return $this->error(null, 'Failed to check Stripe status', 500);
        }
    }

    /**
     * Get Stripe Express Dashboard link
     * GET /api/expert/stripe/dashboard
     */
    public function dashboard()
    {
        try {
            $user            = auth('api')->user();
            $stripeAccountId = $user->profile?->stripe_account_id;

            if (!$stripeAccountId) {
                return $this->error(null, 'No Stripe account connected', 404);
            }

            $dashboardUrl = $this->stripeService->getConnectDashboardLink($stripeAccountId);

            return $this->success('Dashboard link generated', ['url' => $dashboardUrl]);
        } catch (Exception $e) {
            return $this->error(null, 'Failed to generate dashboard link', 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // WALLET & WITHDRAWALS
    // ─────────────────────────────────────────────────────────────────

    /**
     * Get expert's wallet / earnings summary
     * GET /api/expert/wallet
     */
    public function wallet()
    {
        try {
            $user    = auth('api')->user();
            $summary = $this->escrowService->getWalletSummary($user->id);

            return $this->success('Wallet summary retrieved', $summary);
        } catch (Exception $e) {
            return $this->error(null, 'Failed to retrieve wallet', 500);
        }
    }

    /**
     * Request withdrawal
     * POST /api/expert/wallet/withdraw
     */
    public function withdraw(CreateWithdrawalRequest $request)
    {
        try {
            $user   = auth('api')->user();
            $amount = $request->input('amount');

            if (!$user->hasRole('expert')) {
                return $this->error(null, 'Only experts can withdraw earnings', 403);
            }

            // Check Stripe account is ready
            $stripeAccountId = $user->profile?->stripe_account_id;
            if (!$stripeAccountId || !$this->stripeService->isConnectAccountReady($stripeAccountId)) {
                return $this->error(null, 'Please complete Stripe onboarding before withdrawing', 400);
            }

            // Check available balance
            if ($user->available_balance < $amount) {
                return $this->error(
                    null,
                    "Insufficient balance. Available: \${$user->available_balance}",
                    400
                );
            }

            // Minimum withdrawal: $10
            if ($amount < 10) {
                return $this->error(null, 'Minimum withdrawal amount is $10', 400);
            }

            // Create withdrawal record
            $fee       = 0; // No platform fee on withdrawal (configurable)
            $netAmount = $amount - $fee;

            DB::beginTransaction();
            $withdrawal = WithdrawalRequest::create([
                'seller_id'         => $user->id,
                'withdrawal_number' => 'WD-' . strtoupper(Str::random(10)),
                'amount'            => $amount,
                'fee'               => $fee,
                'net_amount'        => $netAmount,
                'stripe_account_id' => $stripeAccountId,
                'status'            => 'pending',
                'requested_at'      => now(),
            ]);
            DB::commit();

            // Process immediately (or queue for batch processing)
            $withdrawal = $this->escrowService->processWithdrawal($withdrawal->id);

            return $this->success('Withdrawal initiated successfully', [
                'withdrawal_id'     => $withdrawal->id,
                'withdrawal_number' => $withdrawal->withdrawal_number,
                'amount'            => $withdrawal->amount,
                'net_amount'        => $withdrawal->net_amount,
                'status'            => $withdrawal->status,
                'stripe_transfer'   => $withdrawal->stripe_transfer_id,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Withdrawal error: ' . $e->getMessage());
            return $this->error(['exception' => $e->getMessage()], $e->getMessage(), 400);
        }
    }

    /**
     * Get withdrawal history
     * GET /api/expert/wallet/withdrawals
     */
    public function withdrawalHistory()
    {
        try {
            $user = auth('api')->user();

            $withdrawals = WithdrawalRequest::where('seller_id', $user->id)
                ->latest()
                ->paginate(20);

            return $this->success('Withdrawal history retrieved', [
                'withdrawals' => $withdrawals->items(),
                'pagination'  => [
                    'total'        => $withdrawals->total(),
                    'per_page'     => $withdrawals->perPage(),
                    'current_page' => $withdrawals->currentPage(),
                    'last_page'    => $withdrawals->lastPage(),
                ],
            ]);
        } catch (Exception $e) {
            return $this->error(null, 'Failed to retrieve withdrawals', 500);
        }
    }
}
