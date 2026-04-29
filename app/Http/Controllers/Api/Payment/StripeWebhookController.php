<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\WithdrawalRequest;
use App\Services\Payment\StripePaymentService;
use App\Services\Payment\WebhookOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * StripeWebhookController
 *
 * Handles incoming Stripe webhook events.
 *
 * Register webhook in Stripe Dashboard → Developers → Webhooks:
 *   URL: https://your-domain.com/api/stripe/webhook
 *   Events to listen:
 *     - checkout.session.completed
 *     - checkout.session.expired
 *     - payment_intent.payment_failed
 *     - transfer.created  (optional, for logging)
 *
 * Add to routes/api.php (OUTSIDE auth middleware — Stripe cannot authenticate):
 *   Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);
 */
class StripeWebhookController extends Controller
{
    public function __construct(
        protected StripePaymentService $stripeService,
        protected WebhookOrderService $webhookOrderService,
    ) {}

    /**
     * Main webhook entry point
     * POST /api/stripe/webhook
     */
    public function handle(Request $request)
    {
        $payload   = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        // ── Verify webhook signature ──────────────────────────────────
        try {
            $event = $this->stripeService->constructWebhookEvent($payload, $signature);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (\Exception $e) {
            Log::error('Stripe webhook error: ' . $e->getMessage());
            return response()->json(['error' => 'Webhook error'], 400);
        }

        // ── Route to appropriate handler ──────────────────────────────
        try {
            match ($event->type) {
                'checkout.session.completed'   => $this->handleCheckoutCompleted($event->data->object),
                'checkout.session.expired'     => $this->handleCheckoutExpired($event->data->object),
                'payment_intent.payment_failed' => $this->handlePaymentFailed($event->data->object),
                'transfer.created'             => $this->handleTransferCreated($event->data->object),
                default => Log::info("Unhandled Stripe event: {$event->type}"),
            };
        } catch (\Exception $e) {
            Log::error("Stripe webhook handler error [{$event->type}]: " . $e->getMessage());
            // Return 200 so Stripe doesn't retry — log it for manual investigation
        }

        // Always return 200 to acknowledge receipt
        return response()->json(['received' => true], 200);
    }

    // ─────────────────────────────────────────────────────────────────
    // EVENT HANDLERS
    // ─────────────────────────────────────────────────────────────────

    /**
     * Payment completed — activate the order
     */
    protected function handleCheckoutCompleted(object $session): void
    {
        $orderId = $session->metadata->order_id ?? null;

        if (!$orderId) {
            Log::warning('checkout.session.completed: No order_id in metadata', (array) $session->metadata);
            return;
        }

        $order = Order::find($orderId);

        if (!$order) {
            Log::warning("checkout.session.completed: Order #{$orderId} not found");
            return;
        }

        if ($order->status === 'active') {
            Log::info("checkout.session.completed: Order #{$orderId} is already active.");
            return;
        }

        if ($session->payment_status === 'paid' && $order->status === 'pending_payment') {
            Log::info("checkout.session.completed: Activating order #{$orderId}");

            $this->webhookOrderService->activateOrder($order, [
                'payment_intent_id' => is_object($session->payment_intent) ? $session->payment_intent->id : $session->payment_intent,
                'payment_method'    => 'stripe',
                'session_id'        => $session->id,
            ]);
        } else {
            Log::warning("checkout.session.completed: Payment status is not paid or order is not pending.", [
                'payment_status' => $session->payment_status,
                'order_status' => $order->status
            ]);
        }
    }

    /**
     * Checkout session expired (user didn't pay in time)
     */
    protected function handleCheckoutExpired(object $session): void
    {
        $orderId = $session->metadata->order_id ?? null;

        if (!$orderId) return;

        $order = Order::where('id', $orderId)
            ->where('status', 'pending_payment')
            ->first();

        if ($order) {
            Log::info("checkout.session.expired: Cancelling order #{$orderId}");
            $this->webhookOrderService->handlePaymentFailed($order);
        }
    }

    /**
     * Payment intent failed
     */
    protected function handlePaymentFailed(object $paymentIntent): void
    {
        // Find order by payment_intent_id or metadata
        $order = Order::where('payment_intent_id', $paymentIntent->id)->first();

        if ($order) {
            Log::info("payment_intent.payment_failed: Handling failed payment for order #{$order->id}");
            $this->webhookOrderService->handlePaymentFailed($order);
        }
    }

    /**
     * Transfer created (payout to expert) — log for auditing
     */
    protected function handleTransferCreated(object $transfer): void
    {
        $withdrawalId = $transfer->metadata->withdrawal_id ?? null;

        if ($withdrawalId) {
            WithdrawalRequest::where('id', $withdrawalId)->update([
                'stripe_transfer_id' => $transfer->id,
                'status'             => 'processing',
            ]);
        }

        Log::info("transfer.created: {$transfer->id}, amount: {$transfer->amount}");
    }
}
