<?php

namespace App\Http\Controllers\Api\Inbox;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomOffer\CreateCustomOfferRequest;
use App\Http\Requests\CustomOffer\RespondToOfferRequest;
use App\Http\Resources\Inbox\CustomOfferResource;
use App\Services\Gig\CustomOfferService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomOfferController extends Controller
{
    use ApiResponse;

    protected $customOfferService;

    public function __construct(CustomOfferService $customOfferService)
    {
        $this->customOfferService = $customOfferService;
    }

    /**
     * Create custom offer (Expert sends to client)
     * POST /api/inbox/custom-offers/create
     */
    public function create(CreateCustomOfferRequest $request)
    {
        try {
            $user = auth('api')->user();

            // Check if user is expert
            if (!$user->hasRole('expert')) {
                return $this->error(
                    null,
                    'Only experts can send custom offers',
                    403
                );
            }

            // Create offer
            $offer = $this->customOfferService->createOffer(
                $user->id,
                $request->validated()
            );

            return $this->success(
                'Custom offer sent successfully',
                ['offer' => new CustomOfferResource($offer)],
                201
            );
        } catch (Exception $e) {
            Log::error('Create custom offer error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                $e->getMessage(),
                $e->getMessage() === 'Client not found' ? 404 : ($e->getMessage() === 'Cannot send offer to yourself' ? 400 : 500)
            );
        }
    }

    /**
     * Withdraw custom offer (Expert withdraws)
     * DELETE /api/inbox/custom-offers/{offerId}/withdraw
     */
    public function withdraw(int $offerId)
    {
        try {
            $user = auth('api')->user();

            // Check if user is expert
            if (!$user->hasRole('expert')) {
                return $this->error(
                    null,
                    'Only experts can withdraw custom offers',
                    403
                );
            }

            // Withdraw offer
            $offer = $this->customOfferService->withdrawOffer($offerId, $user->id);

            return $this->success(
                'Custom offer withdrawn successfully',
                ['offer' => new CustomOfferResource($offer)]
            );
        } catch (Exception $e) {
            Log::error('Withdraw offer error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                $e->getMessage(),
                $e->getMessage() === 'Offer not found' ? 404 : ($e->getMessage() === 'Unauthorized to withdraw this offer' ? 400 : 500)
            );
        }
    }

    /**
     * Accept custom offer (Client accepts)
     * POST v1/api/inbox/custom-offers/{offerId}/accept
     */
    // public function accept(int $offerId)
    // {
    //     try {
    //         $user = auth('api')->user();

    //         if (!$user->hasRole('client')) {
    //             return $this->error(
    //                 null,
    //                 'Only clients can accept custom offers',
    //                 403
    //             );
    //         }

    //         // Accept offer
    //         $offer = $this->customOfferService->acceptOffer($offerId, $user->id);

    //         return $this->success(
    //             'Custom offer accepted successfully',
    //             ['offer' => new CustomOfferResource($offer)]
    //         );
    //     } catch (Exception $e) {
    //         Log::error('Accept offer error: ' . $e->getMessage());

    //         return $this->error(
    //             ['exception' => $e->getMessage()],
    //             $e->getMessage(),
    //             $e->getMessage() === 'Offer not found' ? 404 : (in_array($e->getMessage(), ['Unauthorized to accept this offer', 'Offer cannot be accepted (expired or already responded)']) ? 400 : 500)
    //         );
    //     }
    // }

    /**
     * Accept custom offer — creates order + returns Stripe checkout URL
     * POST /api/inbox/custom-offers/{offerId}/accept
     *
     * Response gives frontend the checkout_url to redirect the client to Stripe.
     * After payment, webhook activates the order automatically.
     */
    public function accept(int $offerId)
    {
        try {
            $user = auth('api')->user();

            if (!$user->hasRole('client')) {
                return $this->error(null, 'Only clients can accept custom offers', 403);
            }

            // 1. Accept the offer + auto-create order + get checkout URL
            $result = $this->customOfferService->acceptOffer($offerId, $user->id);

            return $this->success(
                'Offer accepted. Please complete payment to activate your order.',
                [
                    'offer'        => new CustomOfferResource($result['offer']),
                    'order_id'     => $result['order_id'],
                    'order_number' => $result['order_number'],
                    'checkout_url' => $result['checkout_url'],   // ← Redirect client here
                    'expires_at'   => $result['expires_at'],     // Checkout session expiry
                ]
            );
        } catch (Exception $e) {
            Log::error('Accept offer error: ' . $e->getMessage());

            $status = match ($e->getMessage()) {
                'Offer not found'                                         => 404,
                'Unauthorized to accept this offer',
                'Offer cannot be accepted (expired or already responded)' => 400,
                default                                                   => 500,
            };

            return $this->error(['exception' => $e->getMessage()], $e->getMessage(), $status);
        }
    }

    /**
     * Reject custom offer
     * POST /api/inbox/custom-offers/{offerId}/reject
     */
    public function reject(RespondToOfferRequest $request, int $offerId)
    {
        try {
            $user = auth('api')->user();

            $reason = $request->input('rejection_reason');

            // Reject offer
            $offer = $this->customOfferService->rejectOffer($offerId, $user->id, $reason);

            return $this->success(
                'Custom offer rejected',
                ['offer' => new CustomOfferResource($offer)]
            );
        } catch (Exception $e) {
            Log::error('Reject offer error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                $e->getMessage(),
                $e->getMessage() === 'Offer not found' ? 404 : 400
            );
        }
    }

    /**
     * Get offers in a room
     * GET /api/inbox/custom-offers/room/{roomId}
     */
    public function roomOffers(Request $request, int $roomId)
    {
        try {
            $user = auth('api')->user();

            $filters = $request->only(['status']);
            $perPage = $request->input('per_page', 15);

            // Get room offers
            $offers = $this->customOfferService->getRoomOffers(
                $roomId,
                $user->id,
                $filters,
                $perPage
            );

            return $this->success(
                'Room offers retrieved successfully',
                [
                    'offers' => CustomOfferResource::collection($offers),
                    'pagination' => [
                        'total' => $offers->total(),
                        'per_page' => $offers->perPage(),
                        'current_page' => $offers->currentPage(),
                        'last_page' => $offers->lastPage(),
                    ]
                ]
            );
        } catch (Exception $e) {
            Log::error('Room offers error: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                $e->getMessage(),
                $e->getMessage() === 'Room not found or unauthorized' ? 403 : 500
            );
        }
    }
}
