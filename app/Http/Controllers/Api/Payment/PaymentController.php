<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Payment\StripePaymentService;
use App\Services\Payment\WebhookOrderService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected StripePaymentService $stripeService,
        protected WebhookOrderService $webhookOrderService,
    ) {}

    // ─────────────────────────────────────────────────────────────────
    // CREATE CHECKOUT SESSION
    // ─────────────────────────────────────────────────────────────────

    /**
     * Create Stripe Checkout Session for an order
     * POST /api/payment/checkout/{orderId}
     *
     * Client calls this after order creation to get the Stripe checkout URL
     */
    public function createCheckout(int $orderId)
    {
        try {
            $user = auth('api')->user();

            $order = Order::where('id', $orderId)
                ->where('buyer_id', $user->id)
                ->where('status', 'pending_payment')
                ->first();

            if (!$order) {
                return $this->error(null, 'Order not found or not eligible for payment', 404);
            }

            $checkoutData = $this->stripeService->createCheckoutSession($order);

            return $this->success('Checkout session created', $checkoutData);
        } catch (Exception $e) {
            Log::error('Checkout creation failed: ' . $e->getMessage());
            return $this->error(['exception' => $e->getMessage()], 'Failed to create checkout session', 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // VERIFY PAYMENT SUCCESS (Frontend callback)
    // ─────────────────────────────────────────────────────────────────

    /**
     * Verify payment success after Stripe redirect
     * GET /api/payment/verify?session_id=xxx&order_id=xxx
     *
     * Called by frontend after user is redirected back from Stripe success page.
     * This is a SECONDARY check. Primary activation happens via webhook.
     */
    public function verifyPayment(Request $request)
    {
        try {
            $sessionId = $request->input('session_id');
            $orderId   = $request->input('order_id');

            if (!$sessionId || !$orderId) {
                return $this->error(null, 'Missing session_id or order_id', 400);
            }

            $session = $this->stripeService->verifyCheckoutSession($sessionId);

            $order = Order::find($orderId);

            if (!$order) {
                return $this->error(null, 'Order not found', 404);
            }

            // If order is already active (webhook already fired), just return success
            if ($order->status === 'active') {
                return $this->success('Payment confirmed. Order is active.', [
                    'order_status' => $order->status,
                    'order_number' => $order->order_number,
                ]);
            }

            // If webhook hasn't fired yet but Stripe says paid — manually activate
            if ($session->payment_status === 'paid' && $order->status === 'pending_payment') {
                $this->webhookOrderService->activateOrder($order, [
                    'payment_intent_id' => $session->payment_intent?->id ?? $session->payment_intent,
                    'payment_method'    => 'stripe',
                    'session_id'        => $sessionId,
                ]);

                $order->refresh();

                return $this->success('Payment confirmed. Order is now active.', [
                    'order_status' => $order->status,
                    'order_number' => $order->order_number,
                ]);
            }

            // dd($session);

            return $this->error(null, 'Payment not completed', 400);
        } catch (Exception $e) {
            Log::error('Payment verification failed: ' . $e->getMessage());
            return $this->error(['exception' => $e->getMessage()], 'Verification failed', 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // LOCAL TEST ENDPOINT (dev/staging only)
    // ─────────────────────────────────────────────────────────────────

    /**
     * Simulate successful payment (LOCAL TESTING ONLY)
     * POST /api/payment/test/simulate-success/{orderId}
     *
     * Mimics exactly what the webhook does — safe to use in local/staging.
     * DISABLED IN PRODUCTION via middleware check.
     */
    public function simulatePaymentSuccess(int $orderId)
    {
        // Hard-stop in production
        if (app()->environment('production')) {
            return $this->error(null, 'This endpoint is not available in production', 403);
        }

        try {
            $order = Order::where('id', $orderId)
                ->where('status', 'pending_payment')
                ->first();

            if (!$order) {
                return $this->error(null, 'Order not found or already paid', 404);
            }

            $this->webhookOrderService->activateOrder($order, [
                'payment_intent_id' => 'pi_test_simulated_' . now()->timestamp,
                'payment_method'    => 'stripe_test',
                'session_id'        => 'cs_test_simulated_' . now()->timestamp,
            ]);

            $order->refresh()->load(['gig', 'buyer.profile', 'seller.profile']);

            return $this->success('[TEST] Payment simulated. Order is now active.', [
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'status'       => $order->status,
                'paid_at'      => $order->paid_at,
            ]);
        } catch (Exception $e) {
            Log::error('Simulate payment error: ' . $e->getMessage());
            return $this->error(['exception' => $e->getMessage()], 'Simulation failed', 500);
        }
    }
}
