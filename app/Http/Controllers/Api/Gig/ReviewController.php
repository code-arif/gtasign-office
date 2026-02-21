<?php

namespace App\Http\Controllers\Api\Gig;

use App\Http\Controllers\Controller;
use App\Http\Resources\Gig\ReviewResource;
use App\Services\Gig\ReviewService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    use ApiResponse;

    public function __construct(protected ReviewService $reviewService) {}

    /**
     * Get all public reviews for a gig.
     * GET /api/v1/gigs/{gigId}/reviews
     */
    public function index(Request $request, int $gigId)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $reviews = $this->reviewService->getGigReviews($gigId, $perPage);

            return $this->success('Reviews retrieved successfully', [
                'reviews' => ReviewResource::collection($reviews),
                'pagination' => [
                    'total'        => $reviews->total(),
                    'per_page'     => $reviews->perPage(),
                    'current_page' => $reviews->currentPage(),
                    'last_page'    => $reviews->lastPage(),
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Get gig reviews error: ' . $e->getMessage());
            return $this->error(null, 'Failed to retrieve reviews', 500);
        }
    }

    /**
     * Client submits a review for a completed order.
     * POST /api/v1/reviews/{orderId}
     */
    public function store(Request $request, int $orderId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        try {
            $review = $this->reviewService->createReview(
                $orderId,
                auth('api')->id(),
                $request->only(['rating', 'review'])
            );

            return $this->success('Review submitted successfully', [
                'review' => new ReviewResource($review),
            ], 201);
        } catch (Exception $e) {
            Log::error('Create review error: ' . $e->getMessage());

            $status = match ($e->getMessage()) {
                'Order not found'                       => 404,
                'You have already reviewed this order'  => 409,
                default                                 => 400,
            };

            return $this->error(null, $e->getMessage(), $status);
        }
    }

    /**
     * Expert replies to a review.
     * POST /api/v1/reviews/{reviewId}/reply
     */
    public function reply(Request $request, int $reviewId)
    {
        $request->validate([
            'reply' => 'required|string|max:500',
        ]);

        try {
            $review = $this->reviewService->replyToReview(
                $reviewId,
                auth('api')->id(),
                $request->input('reply')
            );

            return $this->success('Reply submitted successfully', [
                'review' => new ReviewResource($review),
            ]);
        } catch (Exception $e) {
            Log::error('Reply to review error: ' . $e->getMessage());

            $status = match ($e->getMessage()) {
                'Review not found'                          => 404,
                'You have already replied to this review'   => 409,
                default                                     => 400,
            };

            return $this->error(null, $e->getMessage(), $status);
        }
    }

    /**
     * Check if an order has been reviewed (client uses this).
     * GET /api/v1/reviews/order/{orderId}
     */
    public function checkOrderReview(int $orderId)
    {
        try {
            $review = $this->reviewService->getOrderReview($orderId);

            return $this->success('Review status retrieved', [
                'has_review' => (bool)$review,
                'review'     => $review ? new ReviewResource($review) : null,
            ]);
        } catch (Exception $e) {
            return $this->error(null, 'Failed to check review', 500);
        }
    }
}
