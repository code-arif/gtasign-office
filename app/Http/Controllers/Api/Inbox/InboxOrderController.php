<?php

namespace App\Http\Controllers\Api\Inbox;

use App\Http\Controllers\Controller;
use App\Http\Requests\Extension\RespondToExtensionRequest;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Requests\Order\RequestExtensionRequest;
use App\Http\Requests\Order\RequestRevisionRequest;
use App\Http\Requests\Order\SubmitDeliveryRequest;
use App\Http\Resources\Inbox\ExtensionRequestResource;
use App\Http\Resources\Inbox\OrderInboxResource;
use App\Services\Gig\InboxOrderService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InboxOrderController extends Controller
{
    use ApiResponse;

    protected $inboxOrderService;

    public function __construct(InboxOrderService $inboxOrderService)
    {
        $this->inboxOrderService = $inboxOrderService;
    }

    /**
     * Create order from custom offer
     * POST v1/api/inbox/orders/create-from-offer/{offerId}
     */
    public function createFromOffer(Request $request, int $offerId)
    {
        try {
            $user = auth('api')->user();

            if (!$user->hasRole('expert')) {
                return $this->error(
                    null,
                    'Only clients can create orders from offers',
                    403
                );
            }

            // Create order
            $order = $this->inboxOrderService->createOrderFromOffer(
                $offerId,
            );

            return $this->success(
                'Order created successfully. Please proceed to payment.',
                ['order' => new OrderInboxResource($order)],
                201
            );
        } catch (Exception $e) {
            Log::error('Create order from offer error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                $e->getMessage(),
                $e->getMessage() === 'Custom offer not found' ? 404 : 400
            );
        }
    }

    /**
     * Create order from gig (direct)
     * POST v1/api/inbox/orders/create-from-gig/{gigId}
     */
    public function createFromGig(CreateOrderRequest $request, int $gigId)
    {
        try {
            $user = auth('api')->user();

            if (!$user->hasRole('client')) {
                return $this->error(
                    null,
                    'Only clients can create orders from gigs',
                    403
                );
            }

            // Create order
            $order = $this->inboxOrderService->createOrderFromGig(
                $gigId,
                $user->id,
                $request->validated()
            );

            return $this->success(
                'Order created successfully. Please proceed to payment.',
                ['order' => new OrderInboxResource($order)],
                201
            );
        } catch (Exception $e) {
            Log::error('Create order from gig error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                $e->getMessage(),
                $e->getMessage() === 'Gig not available' ? 404 : 400
            );
        }
    }

    /**
     * Mark order as paid (called after payment gateway success)
     * POST /api/inbox/orders/{orderId}/mark-paid
     */
    // public function markPaid(Request $request, int $orderId)
    // {
    //     try {
    //         $user = auth('api')->user();

    //         if (!$user->hasRole('client')) {
    //             return $this->error(
    //                 null,
    //                 'Only clients can mark orders as paid',
    //                 403
    //             );
    //         }

    //         // This should be called from payment webhook/callback
    //         // For now, accepting payment data from request
    //         $paymentData = [
    //             'payment_method' => $request->input('payment_method', 'stripe'),
    //             'payment_intent_id' => $request->input('payment_intent_id'),
    //         ];

    //         // Mark as paid
    //         $order = $this->inboxOrderService->markOrderAsPaid($orderId, $paymentData);

    //         return $this->success(
    //             'Payment successful. Order is now active.',
    //             ['order' => new OrderInboxResource($order)]
    //         );
    //     } catch (Exception $e) {
    //         Log::error('Mark order paid error: ' . $e->getMessage());

    //         return $this->error(
    //             ['exception' => $e->getMessage()],
    //             $e->getMessage(),
    //             $e->getMessage() === 'Order not found' ? 404 : 400
    //         );
    //     }
    // }

    /**
     * Submit delivery for QA review (Expert)
     * POST /api/inbox/orders/{orderId}/submit-delivery
     */
    public function submitDelivery(SubmitDeliveryRequest $request, int $orderId)
    {
        try {
            $user = auth('api')->user();

            // Check if user is expert
            if (!$user->hasRole('expert')) {
                return $this->error(
                    null,
                    'Only experts can submit deliveries',
                    403
                );
            }

            // Submit delivery
            $order = $this->inboxOrderService->submitDelivery(
                $orderId,
                $user->id,
                $request->validated()
            );

            return $this->success(
                'Delivery submitted for QA review',
                ['order' => new OrderInboxResource($order)]
            );
        } catch (Exception $e) {
            Log::error('Submit delivery error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                $e->getMessage(),
                $e->getMessage() === 'Order not found' ? 404 : ($e->getMessage() === 'Unauthorized' ? 403 : 400)
            );
        }
    }

    /**
     * Withdraw delivery
     */
    public function withdrawDelivery(int $orderId)
    {
        try {
            $user = auth('api')->user();

            if (!$user->hasRole('expert')) {
                return $this->error(null, 'Only experts can withdraw deliveries', 403);
            }

            $order = $this->inboxOrderService->withdrawDelivery($orderId, $user->id);

            return $this->success(
                'Delivery withdrawn successfully',
                ['order' => new OrderInboxResource($order)]
            );
        } catch (Exception $e) {
            Log::error('Withdraw delivery error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                $e->getMessage(),
                match ($e->getMessage()) {
                    'Order not found'  => 404,
                    'Unauthorized'     => 403,
                    default            => 400
                }
            );
        }
    }

    /**
     * Request extension (Expert)
     * POST /api/inbox/orders/{orderId}/request-extension
     */
    public function requestExtension(RequestExtensionRequest $request, int $orderId)
    {
        try {
            $user = auth('api')->user();

            // Request extension
            $extension = $this->inboxOrderService->requestExtension(
                $orderId,
                $user->id,
                $request->validated()
            );

            return $this->success(
                'Extension requested successfully',
                ['extension' => new ExtensionRequestResource($extension)]
            );
        } catch (Exception $e) {
            Log::error('Request extension error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                $e->getMessage(),
                $e->getMessage() === 'Order not found' ? 404 : ($e->getMessage() === 'Unauthorized' ? 403 : 400)
            );
        }
    }

    /**
     * Withdraw request extension (Expert)
     * POST /api/inbox/request-extension/{extensionId}/withdraw
     */
    public function withdrawRequestExtension(Request $request, int $extensionId)
    {
        try {
            $user = auth('api')->user();

            $extension = $this->inboxOrderService->withdrawExtensionRequest(
                $extensionId,
                $user->id
            );

            return $this->success(
                'Extension request withdrawn successfully',
                ['extension' => new ExtensionRequestResource($extension)]
            );
        } catch (Exception $e) {
            Log::error('Withdraw extension error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                $e->getMessage(),
                match ($e->getMessage()) {
                    'Extension not found' => 404,
                    'Unauthorized'        => 403,
                    default               => 400
                }
            );
        }
    }

    /**
     * Respond to extension request (Client)
     * POST /api/inbox/orders/extensions/{extensionId}/respond
     */
    public function respondToExtension(RespondToExtensionRequest $request, int $extensionId)
    {
        try {
            $user = auth('api')->user();

            $action = $request->input('action'); // approve or reject

            // Respond to extension
            $extension = $this->inboxOrderService->respondToExtension(
                $extensionId,
                $user->id,
                $action
            );

            return $this->success(
                'Extension request ' . ($action === 'approve' ? 'approved' : 'rejected'),
                ['extension' => new ExtensionRequestResource($extension)]
            );
        } catch (Exception $e) {
            Log::error('Respond to extension error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                $e->getMessage(),
                $e->getMessage() === 'Extension request not found' ? 404 : ($e->getMessage() === 'Unauthorized' ? 403 : 400)
            );
        }
    }

    /**
     * Request revision (Client)
     * POST /api/inbox/orders/{orderId}/request-revision
     */
    public function requestRevision(RequestRevisionRequest $request, int $orderId)
    {
        try {
            $user = auth('api')->user();

            // Request revision
            $order = $this->inboxOrderService->requestRevision(
                $orderId,
                $user->id,
                $request->input('reason')
            );

            return $this->success(
                'Revision requested successfully',
                ['order' => new OrderInboxResource($order)]
            );
        } catch (Exception $e) {
            Log::error('Request revision error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                $e->getMessage(),
                $e->getMessage() === 'Order not found' ? 404 : ($e->getMessage() === 'Unauthorized' ? 403 : 400)
            );
        }
    }

    /**
     * QA approved — Deliver to client
     * POST /api/inbox/orders/{orderId}/deliver-to-client
     */
    public function deliverToClient(int $orderId)
    {
        try {
            $user = auth('api')->user();

            // Only expert roles can deliver.
            if (!$user->hasRole('expert')) {
                return $this->error(null, 'Unauthorized', 403);
            }

            $order = $this->inboxOrderService->deliverToClient($orderId, $user->id);

            return $this->success(
                'Delivery sent to client successfully',
                ['order' => new OrderInboxResource($order)]
            );
        } catch (Exception $e) {
            Log::error('Deliver to client error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                $e->getMessage(),
                match ($e->getMessage()) {
                    'Order not found'  => 404,
                    'Unauthorized'     => 403,
                    default            => 400,
                }
            );
        }
    }

    /**
     * Accept delivery (Client - after QA approval)
     * POST /api/inbox/orders/{orderId}/reject-delivery
     */
    public function acceptDelivery(int $orderId)
    {
        try {
            $user = auth('api')->user();

            // Accept delivery
            $order = $this->inboxOrderService->acceptDelivery($orderId, $user->id);

            return $this->success(
                'Delivery accepted. Order completed successfully.',
                ['order' => new OrderInboxResource($order)]
            );
        } catch (Exception $e) {
            Log::error('Accept delivery error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                $e->getMessage(),
                $e->getMessage() === 'Order not found' ? 404 : ($e->getMessage() === 'Unauthorized' ? 403 : 400)
            );
        }
    }

    /**
     * Reject delivery (Client)
     * POST /api/inbox/orders/{orderId}/reject-delivery
     */
    public function rejectDelivery(Request $request, int $orderId)
    {
        try {
            $user = auth('api')->user();

            $request->validate([
                'reason' => 'required|string|min:10|max:1000',
            ]);

            $order = $this->inboxOrderService->rejectDelivery(
                $orderId,
                $user->id,
                $request->input('reason')
            );

            return $this->success(
                'Delivery rejected. Order has been cancelled.',
                ['order' => new OrderInboxResource($order)]
            );
        } catch (Exception $e) {
            Log::error('Reject delivery error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                $e->getMessage(),
                match ($e->getMessage()) {
                    'Order not found' => 404,
                    'Unauthorized'    => 403,
                    default           => 400
                }
            );
        }
    }


    /**
     * Get order details
     * GET /api/inbox/orders/{orderId}
     */
    public function show(int $orderId)
    {
        try {
            $user = auth('api')->user();

            // Get order details
            $order = $this->inboxOrderService->getOrderDetails($orderId, $user->id);

            return $this->success(
                'Order details retrieved successfully',
                ['order' => new OrderInboxResource($order)]
            );
        } catch (Exception $e) {
            Log::error('Get order details error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                $e->getMessage(),
                $e->getMessage() === 'Order not found' ? 404 : ($e->getMessage() === 'Unauthorized access to this order' ? 403 : 500)
            );
        }
    }
}
