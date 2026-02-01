<?php

namespace App\Services;

use Exception;
use Stripe\Stripe;
use App\Models\User;
use App\Models\CampPayment;
use Stripe\Checkout\Session;
use Modules\Director\Models\Camp;
use App\Models\CampPaymentAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StripePaymentService
{

    // Processing fee percentage (3%)
    const PROCESSING_FEE_PERCENTAGE = 3;

    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Calculate total amount with processing fee
     */
    private function calculateTotalAmount(float $basePrice): array
    {
        $processingFee = round($basePrice * (self::PROCESSING_FEE_PERCENTAGE / 100), 2);
        $totalAmount = round($basePrice + $processingFee, 2);

        return [
            'base_price' => $basePrice,
            'processing_fee' => $processingFee,
            'processing_fee_percentage' => self::PROCESSING_FEE_PERCENTAGE,
            'total_amount' => $totalAmount
        ];
    }

    /**
     * Check if referee can initiate payment for camp
     */
    public function canInitiatePayment(Camp $camp, User $referee): array
    {
        // Check if already paid
        $existingPayment = CampPayment::where('camp_id', $camp->id)
            ->where('referee_id', $referee->id)
            ->where('status', 'succeeded')
            ->first();

        if ($existingPayment) {
            return [
                'can_pay' => false,
                'reason' => 'Already paid for this camp',
                'payment' => $existingPayment
            ];
        }

        // Check for pending attempts
        $recentAttempt = CampPaymentAttempt::where('camp_id', $camp->id)
            ->where('referee_id', $referee->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if ($recentAttempt) {
            return [
                'can_pay' => false,
                'reason' => 'Please wait for 1 minutes before creating a new payment',
                'session_id' => $recentAttempt->stripe_session_id,
                'expires_at' => $recentAttempt->expires_at
            ];
        }

        // Check for cancelled/failed attempts within cooldown period
        $cooldownMinutes = config('payment.retry_cooldown', 1);
        $recentFailedAttempt = CampPaymentAttempt::where('camp_id', $camp->id)
            ->where('referee_id', $referee->id)
            ->whereIn('status', ['cancelled', 'failed'])
            ->where('updated_at', '>', now()->subMinutes($cooldownMinutes))
            ->first();

        if ($recentFailedAttempt) {
            $waitUntil = $recentFailedAttempt->updated_at->addMinutes($cooldownMinutes);
            $waitMinutes = now()->diffInMinutes($waitUntil, false);

            return [
                'can_pay' => false,
                'reason' => 'Please wait before retrying payment',
                'wait_until' => $waitUntil,
                'wait_minutes' => max(1, ceil($waitMinutes))
            ];
        }

        return ['can_pay' => true];
    }

    /**
     * Get valid image URL for Stripe
     */
    private function getValidImageUrl(?string $logoPath): ?string
    {
        if (!$logoPath) {
            return null;
        }

        // Generate full URL
        $fullUrl = url($logoPath);

        // Check if URL is valid and uses HTTPS in production
        $isHttps = str_starts_with($fullUrl, 'https://');
        $isLocalhost = str_contains($fullUrl, 'localhost') || str_contains($fullUrl, '127.0.0.1');

        // In production, only return HTTPS URLs
        // In local development, skip image to avoid Stripe errors
        if (app()->environment('production')) {
            return $isHttps ? $fullUrl : null;
        }

        // In local/staging, don't send image URL as Stripe won't accept it
        return null;
    }

    /**
     * Create Stripe checkout session
     */
    // public function createCheckoutSession(Camp $camp, User $referee): array
    // {
    //     DB::beginTransaction();

    //     try {
    //         // Check if can initiate payment
    //         $canPay = $this->canInitiatePayment($camp, $referee);
    //         if (!$canPay['can_pay']) {
    //             return [
    //                 'success' => false,
    //                 'error' => $canPay['reason'],
    //                 'data' => $canPay
    //             ];
    //         }

    //         // Mark old expired attempts as failed
    //         CampPaymentAttempt::where('camp_id', $camp->id)
    //             ->where('referee_id', $referee->id)
    //             ->where('status', 'pending')
    //             ->where('expires_at', '<', now())
    //             ->update(['status' => 'failed']);

    //         // Stripe requires minimum 30 minutes
    //         $stripeSessionExpiry = 30; // Stripe minimum
    //         $retryWindow = (int) config('payment.retry_cooldown', 2); // Your custom retry window

    //         $description = "Location: {$camp->location}";
    //         if ($camp->start_date && $camp->end_date) {
    //             $description .= " | {$camp->start_date} to {$camp->end_date}";
    //         }

    //         // Get valid image URL (only for production HTTPS)
    //         $imageUrl = $this->getValidImageUrl($camp->camp_logo);

    //         // Prepare product data
    //         $productData = [
    //             'name' => "Camp Registration: {$camp->camp_name}",
    //             'description' => $description,
    //         ];

    //         // Only add images if we have a valid URL
    //         if ($imageUrl) {
    //             $productData['images'] = [$imageUrl];
    //         }

    //         // Create Stripe Checkout Session
    //         $session = Session::create([
    //             'payment_method_types' => ['card'],
    //             'line_items' => [[
    //                 'price_data' => [
    //                     'currency' => 'usd',
    //                     'product_data' => $productData,
    //                     'unit_amount' => (int)($camp->price * 100),
    //                 ],
    //                 'quantity' => 1,
    //             ]],
    //             'mode' => 'payment',
    //             'success_url' => config('payment.success_url') . '?session_id={CHECKOUT_SESSION_ID}',
    //             'cancel_url' => config('payment.cancel_url') . '?session_id={CHECKOUT_SESSION_ID}',
    //             'metadata' => [
    //                 'camp_id' => $camp->id,
    //                 'referee_id' => $referee->id,
    //                 'camp_name' => $camp->camp_name,
    //                 'referee_email' => $referee->email
    //             ],
    //             'customer_email' => $referee->email,
    //             'expires_at' => now()->addMinutes($stripeSessionExpiry)->timestamp // 30 min minimum
    //         ]);

    //         // Create payment attempt with YOUR custom expiry (2 min)
    //         $attempt = CampPaymentAttempt::create([
    //             'camp_id' => $camp->id,
    //             'referee_id' => $referee->id,
    //             'stripe_session_id' => $session->id,
    //             'amount' => $camp->price,
    //             'status' => 'pending',
    //             'expires_at' => now()->addMinutes($retryWindow) // Your custom: 2 minutes
    //         ]);

    //         DB::commit();

    //         return [
    //             'success' => true,
    //             'session_id' => $session->id,
    //             'checkout_url' => $session->url,
    //             'attempt_id' => $attempt->id,
    //             'expires_at' => $attempt->expires_at
    //         ];
    //     } catch (Exception $e) {
    //         DB::rollBack();

    //         // Log the error for debugging
    //         Log::error('Stripe payment session creation failed', [
    //             'camp_id' => $camp->id,
    //             'referee_id' => $referee->id,
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);

    //         return [
    //             'success' => false,
    //             'error' => 'Failed to create payment session',
    //             'message' => $e->getMessage()
    //         ];
    //     }
    // }

    public function createCheckoutSession(Camp $camp, User $referee): array
    {
        DB::beginTransaction();

        try {
            // Check if can initiate payment
            $canPay = $this->canInitiatePayment($camp, $referee);
            if (!$canPay['can_pay']) {
                return [
                    'success' => false,
                    'error' => $canPay['reason'],
                    'data' => $canPay
                ];
            }

            // Calculate total amount with processing fee
            $amountCalculation = $this->calculateTotalAmount($camp->price);

            // Mark old expired attempts as failed
            CampPaymentAttempt::where('camp_id', $camp->id)
                ->where('referee_id', $referee->id)
                ->where('status', 'pending')
                ->where('expires_at', '<', now())
                ->update(['status' => 'failed']);

            // Stripe requires minimum 30 minutes
            $stripeSessionExpiry = 30; // Stripe minimum
            $retryWindow = (int) config('payment.retry_cooldown', 2); // Your custom retry window

            $description = "Location: {$camp->location}";
            if ($camp->start_date && $camp->end_date) {
                $description .= " | {$camp->start_date} to {$camp->end_date}";
            }
            $description .= " | Includes {$amountCalculation['processing_fee_percentage']}% processing fee";

            // Get valid image URL (only for production HTTPS)
            $imageUrl = $this->getValidImageUrl($camp->camp_logo);

            // Prepare product data
            $productData = [
                'name' => "Camp Registration: {$camp->camp_name}",
                'description' => $description,
            ];

            // Only add images if we have a valid URL
            if ($imageUrl) {
                $productData['images'] = [$imageUrl];
            }

            // Create Stripe Checkout Session with total amount (including processing fee)
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => $productData,
                        'unit_amount' => (int)($amountCalculation['total_amount'] * 100), // Total with fee
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => config('payment.success_url') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => config('payment.cancel_url') . '?session_id={CHECKOUT_SESSION_ID}',
                'metadata' => [
                    'camp_id' => $camp->id,
                    'referee_id' => $referee->id,
                    'camp_name' => $camp->camp_name,
                    'referee_email' => $referee->email,
                    'base_price' => $amountCalculation['base_price'],
                    'processing_fee' => $amountCalculation['processing_fee'],
                    'processing_fee_percentage' => $amountCalculation['processing_fee_percentage'],
                    'total_amount' => $amountCalculation['total_amount']
                ],
                'customer_email' => $referee->email,
                'expires_at' => now()->addMinutes($stripeSessionExpiry)->timestamp // 30 min minimum
            ]);

            // Create payment attempt with total amount
            $attempt = CampPaymentAttempt::create([
                'camp_id' => $camp->id,
                'referee_id' => $referee->id,
                'stripe_session_id' => $session->id,
                'amount' => $amountCalculation['total_amount'], // Store total amount
                'status' => 'pending',
                'expires_at' => now()->addMinutes($retryWindow) // Your custom: 2 minutes
            ]);

            DB::commit();

            return [
                'success' => true,
                'session_id' => $session->id,
                'checkout_url' => $session->url,
                'attempt_id' => $attempt->id,
                'expires_at' => $attempt->expires_at,
                'amount_breakdown' => $amountCalculation
            ];
        } catch (Exception $e) {
            DB::rollBack();

            // Log the error for debugging
            Log::error('Stripe payment session creation failed', [
                'camp_id' => $camp->id,
                'referee_id' => $referee->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to create payment session',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Handle successful payment
     */
    public function handleSuccess(string $sessionId): array
    {
        DB::beginTransaction();

        try {
            // Retrieve the session from Stripe
            $session = Session::retrieve([
                'id' => $sessionId,
                'expand' => ['payment_intent']
            ]);

            if ($session->payment_status !== 'paid') {
                return [
                    'success' => false,
                    'error' => 'Payment not completed'
                ];
            }

            // Find payment attempt
            $attempt = CampPaymentAttempt::where('stripe_session_id', $sessionId)->first();

            if (!$attempt) {
                return [
                    'success' => false,
                    'error' => 'Payment attempt not found'
                ];
            }

            // Check if already processed
            if ($attempt->status === 'completed') {
                $payment = CampPayment::where('payment_attempt_id', $attempt->id)->first();
                return [
                    'success' => true,
                    'already_processed' => true,
                    'payment' => $payment
                ];
            }

            // Create payment record
            $payment = CampPayment::create([
                'camp_id' => $attempt->camp_id,
                'referee_id' => $attempt->referee_id,
                'payment_attempt_id' => $attempt->id,
                'stripe_payment_intent_id' => $session->payment_intent->id,
                'stripe_session_id' => $sessionId,
                'amount' => $attempt->amount,
                'currency' => strtolower($session->currency ?? 'usd'),
                'status' => 'succeeded',
                'paid_at' => now(),
                'metadata' => [
                    'payment_method' => $session->payment_intent->payment_method ?? null,
                    'customer_email' => $session->customer_email
                ]
            ]);

            // Update attempt
            $attempt->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

            DB::commit();

            return [
                'success' => true,
                'payment' => $payment,
                'camp_id' => $attempt->camp_id
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'error' => 'Failed to process payment',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Handle cancelled payment
     */
    public function handleCancel(string $sessionId): array
    {
        $attempt = CampPaymentAttempt::where('stripe_session_id', $sessionId)->first();

        if (!$attempt) {
            return [
                'success' => false,
                'error' => 'Payment attempt not found'
            ];
        }

        if ($attempt->status === 'pending') {
            $attempt->update(['status' => 'cancelled']);
        }

        return [
            'success' => true,
            'attempt' => $attempt,
            'can_retry_at' => now()->addMinutes(config('payment.retry_cooldown', 1))
        ];
    }

    /**
     * Verify payment exists for camp and referee
     */
    public function hasValidPayment(int $campId, int $refereeId): bool
    {
        return CampPayment::where('camp_id', $campId)
            ->where('referee_id', $refereeId)
            ->where('status', 'succeeded')
            ->exists();
    }
}
