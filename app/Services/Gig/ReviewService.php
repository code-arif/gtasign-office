<?php

namespace App\Services\Gig;

use App\Models\Order;
use App\Models\OrderReview;
use Exception;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    /**
     * Client submits review for a completed order.
     * One review per order (enforced by unique constraint on order_id).
     */
    public function createReview(int $orderId, int $reviewerId, array $data): OrderReview
    {
        DB::beginTransaction();
        try {
            $order = Order::with('gig')->find($orderId);

            if (!$order) {
                throw new Exception('Order not found');
            }

            // Only buyer can review
            if ($order->buyer_id !== $reviewerId) {
                throw new Exception('Only the client who placed the order can leave a review');
            }

            // Order must be completed
            if ($order->status !== 'completed') {
                throw new Exception('You can only review a completed order');
            }

            // Check if already reviewed
            if (OrderReview::where('order_id', $orderId)->exists()) {
                throw new Exception('You have already reviewed this order');
            }

            $rating = (int)$data['rating'];
            if ($rating < 1 || $rating > 5) {
                throw new Exception('Rating must be between 1 and 5');
            }

            $review = OrderReview::create([
                'order_id'         => $orderId,
                'reviewer_id'      => $reviewerId,
                'reviewed_user_id' => $order->seller_id,
                'gig_id'           => $order->gig_id,
                'rating'           => $rating,
                'review'           => $data['review'] ?? null,
                'is_public'        => true,
            ]);

            // Update gig average rating cache
            $this->updateGigRating($order->gig_id);

            DB::commit();

            return $review->load(['reviewer.profile', 'reviewedUser.profile', 'order', 'gig']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Expert (seller) replies to a review on their gig.
     */
    public function replyToReview(int $reviewId, int $sellerId, string $reply): OrderReview
    {
        DB::beginTransaction();
        try {
            $review = OrderReview::with('order')->find($reviewId);

            if (!$review) {
                throw new Exception('Review not found');
            }

            // Only the reviewed seller can reply
            if ($review->reviewed_user_id !== $sellerId) {
                throw new Exception('You can only reply to reviews on your own gigs');
            }

            if ($review->seller_reply) {
                throw new Exception('You have already replied to this review');
            }

            $review->update([
                'seller_reply' => $reply,
                'replied_at'   => now(),
            ]);

            DB::commit();

            return $review->fresh(['reviewer.profile', 'reviewedUser.profile', 'gig']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get all reviews for a gig (public).
     */
    public function getGigReviews(int $gigId, int $perPage = 10)
    {
        return OrderReview::with(['reviewer.profile'])
            ->where('gig_id', $gigId)
            ->where('is_public', true)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get a single review by order (to check if reviewed).
     */
    public function getOrderReview(int $orderId): ?OrderReview
    {
        return OrderReview::with(['reviewer.profile'])
            ->where('order_id', $orderId)
            ->first();
    }

    /**
     * Recalculate and cache gig's average rating.
     * Calls Gig model to update avg_rating + reviews_count.
     */
    private function updateGigRating(int $gigId): void
    {
        $stats = OrderReview::where('gig_id', $gigId)
            ->where('is_public', true)
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as reviews_count')
            ->first();

        \App\Models\Gig::where('id', $gigId)->update([
            'avg_rating'    => round($stats->avg_rating ?? 0, 1),
            'reviews_count' => $stats->reviews_count ?? 0,
        ]);
    }
}
