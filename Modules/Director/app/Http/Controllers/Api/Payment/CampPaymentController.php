<?php

namespace Modules\Director\Http\Controllers\Api\Payment;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Director\Models\Camp;
use App\Http\Controllers\Controller;
use App\Services\StripePaymentService;
use Illuminate\Support\Facades\Validator;

class CampPaymentController extends Controller
{
    use ApiResponse;

    protected $stripeService;

    public function __construct(StripePaymentService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Initiate payment for camp check-in
     */
    public function initiatePayment(Request $request, $campId)
    {
        $referee = auth('api')->user();

        // Verify camp exists and is active
        $camp = Camp::where('id', $campId)
            ->where('status', 'active')
            ->first();

        if (!$camp) {
            return $this->error('Camp not found or inactive.', null, 404);
        }

        // Check if can initiate payment
        $result = $this->stripeService->createCheckoutSession($camp, $referee);

        if (!$result['success']) {
            return $this->error(
                $result['error'],
                isset($result['data']) ? $result['data'] : ['message' => $result['message'] ?? null],
                400
            );
        }

        return $this->success(
            'Payment session created successfully.',
            [
                'session_id' => $result['session_id'],
                'checkout_url' => $result['checkout_url'],
                'expires_at' => $result['expires_at'],
                'camp' => [
                    'id' => $camp->id,
                    'name' => $camp->camp_name,
                    'price' => $camp->price
                ]
            ],
            201
        );
    }

    /**
     * Handle successful payment callback
     */
    public function success(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->error('Invalid request.', $validator->errors(), 422);
        }

        $result = $this->stripeService->handleSuccess($request->session_id);

        if (!$result['success']) {
            return $this->error(
                $result['error'],
                ['message' => $result['message'] ?? null],
                400
            );
        }

        $message = isset($result['already_processed'])
            ? 'Payment already processed.'
            : 'Payment completed successfully.';

        return $this->success(
            $message,
            [
                'payment_id' => $result['payment']->id,
                'camp_id' => $result['camp_id'],
                'amount' => $result['payment']->amount,
                'paid_at' => $result['payment']->paid_at,
                'next_step' => 'You can now check in to the camp.'
            ],
            200
        );
    }

    /**
     * Handle cancelled payment callback
     */
    public function cancel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->error('Invalid request.', $validator->errors(), 422);
        }

        $result = $this->stripeService->handleCancel($request->session_id);

        if (!$result['success']) {
            return $this->error($result['error'], null, 400);
        }

        return $this->success(
            'Payment cancelled.',
            [
                'message' => 'You cancelled the payment. You can retry after ' . config('payment.retry_cooldown', 5) . ' minutes.',
                'can_retry_at' => $result['can_retry_at'],
                'camp_id' => $result['attempt']->camp_id
            ],
            200
        );
    }

    /**
     * Get payment status for a camp
     */
    public function getPaymentStatus(Request $request, $campId)
    {
        $referee = auth('api')->user();

        $camp = Camp::find($campId);
        if (!$camp) {
            return $this->error('Camp not found.', null, 404);
        }

        $canPay = $this->stripeService->canInitiatePayment($camp, $referee);

        return $this->success(
            'Payment status retrieved.',
            [
                'camp_id' => $campId,
                'camp_name' => $camp->camp_name,
                'price' => $camp->price,
                'can_initiate_payment' => $canPay['can_pay'],
                'reason' => $canPay['reason'] ?? null,
                'details' => $canPay
            ],
            200
        );
    }

    /**
     * Get payment history
     */
    public function getPaymentHistory(Request $request)
    {
        $referee = auth('api')->user();

        $payments = \App\Models\CampPayment::where('referee_id', $referee->id)
            ->with('camp:id,camp_name,location,camp_logo,price')
            ->latest('paid_at')
            ->get();

        $formatted = $payments->map(function ($payment) {
            return [
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'currency' => strtoupper($payment->currency),
                'status' => $payment->status,
                'paid_at' => $payment->paid_at->format('Y-m-d H:i:s'),
                'camp' => [
                    'id' => $payment->camp->id,
                    'name' => $payment->camp->camp_name,
                    'location' => $payment->camp->location,
                    'logo' => $payment->camp->camp_logo ? asset($payment->camp->camp_logo) : asset('default/no_image.webp'),
                    'price' => $payment->camp->price
                ]
            ];
        });

        return $this->success(
            'Payment history retrieved.',
            ['payments' => $formatted],
            200
        );
    }
}
