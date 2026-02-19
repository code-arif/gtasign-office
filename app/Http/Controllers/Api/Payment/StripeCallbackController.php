<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Payment\StripePaymentService;
use Exception;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;

class StripeCallbackController extends Controller
{
    public function __construct(protected StripePaymentService $stripeService) {}

    /**
     * Stripe redirects here after onboarding is completed (or partially done).
     * GET /stripe/success/{id}   where {id} = stripe_account_id
     */
    public function success(string $id)
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            // Retrieve account from Stripe
            $account = \Stripe\Account::retrieve($id);

            // Find the user by stripe_account_id
            $user = User::where('stripe_account_id', $id)->first();

            if (!$user) {
                // Try finding via profile
                $user = User::whereHas('profile', fn($q) => $q->where('stripe_account_id', $id))->first();
            }

            if ($user) {
                // Ensure both locations have the account ID saved
                $user->update(['stripe_account_id' => $id]);
                if ($user->profile) {
                    $user->profile->update([
                        'stripe_account_id'   => $id,
                        'stripe_onboarded_at' => now(),
                    ]);
                }
            }

            // Check if fully onboarded
            $isReady = $account->charges_enabled && $account->payouts_enabled;

            // Redirect to frontend with status
            $frontendUrl = config('app.frontend_url') . '/expert/stripe/success?'
                . http_build_query([
                    'account_id' => $id,
                    'status'     => $isReady ? 'complete' : 'pending',
                ]);

            return redirect()->away($frontendUrl);
        } catch (Exception $e) {
            Log::error('Stripe success callback error: ' . $e->getMessage());

            $frontendUrl = config('app.frontend_url') . '/expert/stripe/error';
            return redirect()->away($frontendUrl);
        }
    }

    /**
     * Stripe redirects here if the onboarding link expires before completion.
     * GET /stripe/refresh/{id}
     */
    public function refresh(string $id)
    {
        try {
            // Generate a fresh onboarding link
            $newLink = $this->stripeService->refreshOnboardingLink($id);

            // Redirect to the new Stripe onboarding page
            return redirect()->away($newLink);
        } catch (Exception $e) {
            Log::error('Stripe refresh callback error: ' . $e->getMessage());

            $frontendUrl = config('app.frontend_url') . '/expert/stripe/error';
            return redirect()->away($frontendUrl);
        }
    }
}
