<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\User;
use Exception;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Transfer;
use Stripe\Webhook;

class StripePaymentService
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    // ─────────────────────────────────────────────────────────────────
    // CHECKOUT SESSION
    // ─────────────────────────────────────────────────────────────────

    /**
     * Create Stripe Checkout Session for an order
     */
    public function createCheckoutSession(Order $order): array
    {
        $order->load(['gig', 'buyer', 'seller']);

        $lineItems = [
            [
                'price_data' => [
                    'currency'     => 'usd',
                    'unit_amount'  => (int) ($order->price * 100), // cents
                    'product_data' => [
                        'name'        => $order->gig?->title ?? "Custom Order #{$order->order_number}",
                        'description' => "Order #{$order->order_number} - Expert: {$order->seller?->profile?->full_name}",
                    ],
                ],
                'quantity' => 1,
            ],
        ];

        $session = $this->stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items'           => $lineItems,
            'mode'                 => 'payment',
            'success_url'          => config('app.url') . '/' . $order->id . '/payment/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'           => config('app.url') . '/' . $order->id . '/payment/cancel',
            'customer_email'       => $order->buyer?->email,
            'metadata'             => [
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'buyer_id'     => $order->buyer_id,
                'seller_id'    => $order->seller_id,
            ],
            'expires_at'           => now()->addHours(24)->timestamp, // 24hr expiry
        ]);

        // Store session ID on the order for later verification
        $order->update(['stripe_checkout_session_id' => $session->id]);

        return [
            'checkout_url' => $session->url,
            'session_id'   => $session->id,
            'expires_at'   => now()->addHours(24)->toISOString(),
        ];
    }

    /**
     * Verify a checkout session (for success page verification)
     */
    public function verifyCheckoutSession(string $sessionId): object
    {
        return $this->stripe->checkout->sessions->retrieve($sessionId, [
            'expand' => ['payment_intent'],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // STRIPE CONNECT — EXPERT ONBOARDING
    // ─────────────────────────────────────────────────────────────────

    /**
     * Create Stripe Connect Account for Expert
     */
    public function createConnectAccount(User $expert): string
    {
        $account = $this->stripe->accounts->create([
            'type'  => 'express',
            'email' => $expert->email,
            'metadata' => [
                'user_id' => $expert->id,
            ],
            'capabilities' => [
                'transfers' => ['requested' => true],
            ],
        ]);

        // Save stripe account id
        $expert->profile()->update(['stripe_account_id' => $account->id]);

        return $account->id;
    }

    /**
     * Generate Stripe Connect Onboarding Link
     */
    public function createConnectOnboardingLink(string $stripeAccountId, int $expertId): string
    {
        $link = $this->stripe->accountLinks->create([
            'account'     => $stripeAccountId,
            'refresh_url' => config('app.frontend_url') . '/expert/stripe/refresh',
            'return_url'  => config('app.frontend_url') . '/expert/stripe/success',
            'type'        => 'account_onboarding',
        ]);

        return $link->url;
    }

    /**
     * Check if Expert's Connect account is fully onboarded
     */
    public function isConnectAccountReady(string $stripeAccountId): bool
    {
        $account = $this->stripe->accounts->retrieve($stripeAccountId);

        return $account->charges_enabled && $account->payouts_enabled;
    }

    /**
     * Get Stripe Connect Dashboard login link
     */
    public function getConnectDashboardLink(string $stripeAccountId): string
    {
        $link = $this->stripe->accounts->createLoginLink($stripeAccountId);

        return $link->url;
    }

    // ─────────────────────────────────────────────────────────────────
    // PAYOUTS — TRANSFER TO EXPERT
    // ─────────────────────────────────────────────────────────────────

    /**
     * Transfer funds to expert via Stripe Connect
     * Called when escrow is released (14 days after completion)
     */
    public function transferToExpert(
        string $stripeAccountId,
        int    $amountCents,
        int    $orderId,
        string $currency = 'usd'
    ): object {
        $transfer = $this->stripe->transfers->create([
            'amount'      => $amountCents,
            'currency'    => $currency,
            'destination' => $stripeAccountId,
            'metadata'    => [
                'order_id' => $orderId,
                'type'     => 'seller_payout',
            ],
        ]);

        return $transfer;
    }

    /**
     * Process withdrawal request to expert's connected account
     */
    public function processWithdrawal(
        string $stripeAccountId,
        int    $amountCents,
        int    $withdrawalId
    ): object {
        $transfer = $this->stripe->transfers->create([
            'amount'      => $amountCents,
            'currency'    => 'usd',
            'destination' => $stripeAccountId,
            'metadata'    => [
                'withdrawal_id' => $withdrawalId,
                'type'          => 'withdrawal',
            ],
        ]);

        return $transfer;
    }

    // ─────────────────────────────────────────────────────────────────
    // WEBHOOK VERIFICATION
    // ─────────────────────────────────────────────────────────────────

    /**
     * Verify and construct Stripe webhook event
     */
    public function constructWebhookEvent(string $payload, string $signature): object
    {
        return Webhook::constructEvent(
            $payload,
            $signature,
            config('services.stripe.webhook_secret')
        );
    }
}
