<?php

namespace App\Services\Gig;

use App\Models\Chat;
use App\Models\OrderActivity;
use App\Models\OrderQaReview;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * QaService
 *
 * Flow:
 *  1. Expert submits delivery → order: qa_pending, delivery: pending_qa
 *  2. Admin reviews via QA panel
 *  3a. Admin APPROVES → delivery: qa_approved → order: delivered → delivery sent to client
 *  3b. Admin REJECTS  → delivery: qa_rejected → order: active (back to expert)
 *  4. Client ACCEPTS delivery → order: completed (earnings released to escrow)
 *  4b. Client REQUESTS revision → order: active (back to expert for another round)
 */
class QaService
{
    // ─────────────────────────────────────────────────────────────────
    // ADMIN ACTIONS
    // ─────────────────────────────────────────────────────────────────

    /**
     * Get all pending QA reviews (for Admin panel)
     */
    public function getPendingReviews(int $perPage = 20)
    {
        return OrderQaReview::with([
            'order.gig',
            'order.buyer.profile',
            'order.seller.profile',
            'delivery',
        ])
            ->where('status', 'pending')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get a single QA review with full details
     */
    public function getReview(int $reviewId): OrderQaReview
    {
        $review = OrderQaReview::with([
            'order.gig',
            'order.buyer.profile',
            'order.seller.profile',
            'delivery',
            'reviewer.profile',
        ])->find($reviewId);

        if (!$review) {
            throw new Exception('QA review not found');
        }

        return $review;
    }

    /**
     * Admin APPROVES delivery → delivers to client
     */
    public function approveDelivery(int $reviewId, int $adminId, ?string $feedback = null): OrderQaReview
    {
        $now = Carbon::now();

        DB::beginTransaction();
        try {
            $review = OrderQaReview::with(['order.room', 'delivery'])->find($reviewId);

            if (!$review) {
                throw new Exception('QA review not found');
            }

            if ($review->status !== 'pending') {
                throw new Exception('This QA review has already been processed');
            }

            $order    = $review->order;
            $delivery = $review->delivery;

            if ($order->status !== 'qa_pending') {
                throw new Exception('Order is not in QA pending state');
            }

            // 1. Update QA review
            $review->update([
                'status'      => 'approved',
                'reviewed_by' => $adminId,
                'feedback'    => $feedback,
                'reviewed_at' => $now,
            ]);

            // 2. Update delivery → delivered to client
            $delivery->update([
                'status'                  => 'qa_approved',
                'qa_feedback'             => $feedback,
                'qa_reviewed_at'          => $now,
                'delivered_to_client_at'  => $now,
            ]);

            // dd($delivery->qa_reviewed_at);

            // 3. Update order → delivered, set auto-complete timer
            $autoCompleteAt = $now->addDays(config('orders.auto_accept_days', 3));
            $order->update([
                'status'           => 'qa_approved',
                'qa_approved_at'   => $now,
                'delivered_at'     => $now,
                'auto_complete_at' => $autoCompleteAt,
            ]);

            // 4. Chat message to client (actual delivery notification)
            Chat::create([
                'sender_id'   => $order->seller_id,
                'receiver_id' => $order->buyer_id,
                'room_id'     => $order->room_id,
                'type'        => 'delivery_sent',
                'order_id'    => $order->id,
                'delivery_id' => $delivery->id,
                'text'        => "Your order has been delivered! Please review and accept or request revisions. Auto-accepted in " . config('orders.auto_accept_days', 3) . " days.",
                'metadata'    => [
                    'delivery_number' => $delivery->delivery_number,
                    'file_count'      => count($delivery->files ?? []),
                    'auto_accept_at'  => $autoCompleteAt->toISOString(),
                ],
            ]);

            $order->room->update(['last_message_at' => $now]);

            // 5. Activity log
            OrderActivity::create([
                'order_id'    => $order->id,
                'user_id'     => $adminId,
                'type'        => 'qa_approved',
                'description' => "QA approved delivery #{$delivery->delivery_number}",
            ]);

            // OrderActivity::create([
            //     'order_id'    => $order->id,
            //     'user_id'     => $adminId,
            //     'type'        => 'delivered_to_client',
            //     'description' => 'Delivery sent to client after QA approval',
            // ]);

            DB::commit();

            return $review->fresh(['order', 'delivery', 'reviewer.profile']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Admin REJECTS delivery → sends back to expert for revision
     */
    public function rejectDelivery(int $reviewId, int $adminId, string $feedback, array $issues = []): OrderQaReview
    {
        $now = Carbon::now();

        DB::beginTransaction();
        try {
            $review = OrderQaReview::with(['order.room', 'delivery'])->find($reviewId);

            if (!$review) {
                throw new Exception('QA review not found');
            }

            if ($review->status !== 'pending') {
                throw new Exception('This QA review has already been processed');
            }

            $order    = $review->order;
            $delivery = $review->delivery;

            // 1. Update QA review
            $review->update([
                'status'      => 'rejected',
                'reviewed_by' => $adminId,
                'feedback'    => $feedback,
                'issues'      => $issues,
                'reviewed_at' => $now,
            ]);

            // 2. Update delivery → qa_rejected
            $delivery->update([
                'status'         => 'qa_rejected',
                'qa_feedback'    => $feedback,
                'qa_reviewed_at' => $now,
            ]);

            // 3. Order back to active (expert needs to re-submit)
            $order->update([
                'status'          => 'active',
                'qa_submitted_at' => null,
            ]);

            // 4. Notify expert with issues
            Chat::create([
                'sender_id'   => $order->buyer_id, // system message shown from platform
                'receiver_id' => $order->seller_id,
                'room_id'     => $order->room_id,
                'type'        => 'delivery_rejected',
                'order_id'    => $order->id,
                'delivery_id' => $delivery->id,
                'text'        => "Your delivery was returned by QA. Please review the feedback and resubmit.\n\nFeedback: {$feedback}",
                'metadata'    => [
                    'issues'          => $issues,
                    'delivery_number' => $delivery->delivery_number,
                ],
            ]);

            $order->room->update(['last_message_at' => now()]);

            OrderActivity::create([
                'order_id'    => $order->id,
                'user_id'     => $adminId,
                'type'        => 'qa_rejected',
                'description' => "QA rejected delivery #{$delivery->delivery_number}: {$feedback}",
                'metadata'    => ['issues' => $issues],
            ]);

            DB::commit();

            return $review->fresh(['order', 'delivery', 'reviewer.profile']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
