<?php

namespace App\Services\Gig;

use App\Helpers\Helper;
use App\Models\Chat;
use App\Models\CustomOffer;
use App\Models\ExtensionRequest;
use App\Models\Gig;
use App\Models\Order;
use App\Models\OrderActivity;
use App\Models\OrderDelivery;
use App\Models\OrderQaReview;
use App\Models\Room;
use App\Models\SellerEarnings;
use App\Services\Payment\EscrowService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class InboxOrderService
{
    public function __construct(protected EscrowService $escrowService) {}

    /**
     * Create order from custom offer
     */
    public function createOrderFromOffer(int $offerId)
    {
        DB::beginTransaction();
        try {
            $offer = CustomOffer::with(['gig', 'expert', 'client', 'room'])->find($offerId);

            if (!$offer) {
                throw new Exception('Custom offer not found');
            }


            // Validate offer is accepted
            if ($offer->status !== 'accepted') {
                throw new Exception('Offer must be accepted before creating order');
            }

            $buyerId = $offer->client_id;

            // Validate buyer is the client
            if (!$buyerId) {
                throw new Exception('Unauthorized to create order from this offer');
            }

            // Calculate pricing
            $price = $offer->price;
            $platformFee = $price * 0.10; // 10%
            $sellerEarnings = $price - $platformFee;
            $expectedDeliveryAt = now()->addDays($offer->delivery_days);

            // Create order
            $order = Order::create([
                'gig_id' => $offer->gig_id,
                'custom_offer_id' => $offer->id,
                'buyer_id' => $buyerId,
                'seller_id' => $offer->expert_id,
                'room_id' => $offer->room_id,
                'price' => $price,
                'platform_fee' => $platformFee,
                'seller_earnings' => $sellerEarnings,
                'delivery_days' => $offer->delivery_days,
                'expected_delivery_at' => $expectedDeliveryAt,
                'max_revisions' => $offer->revisions,
                'requirements' => $offer->description,
                'status' => 'pending_payment',
            ]);

            // Update offer status
            $offer->update(['status' => 'converted_to_order']);

            // Send message in inbox
            Chat::create([
                'sender_id' => $buyerId,
                'receiver_id' => $offer->expert_id,
                'room_id' => $offer->room_id,
                'type' => 'order_placed',
                'order_id' => $order->id,
                'text' => "Order placed: {$offer->title} - #{$order->order_number}",
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'price' => $order->price,
                ],
            ]);

            // Update room
            $offer->room->update(['last_message_at' => now()]);

            // Log activity
            OrderActivity::create([
                'order_id' => $order->id,
                'user_id' => $buyerId,
                'type' => 'order_placed',
                'description' => 'Order created from custom offer',
            ]);

            DB::commit();

            return $order->load(['gig', 'customOffer', 'buyer.profile', 'seller.profile', 'room']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Create order from gig (direct order)
     */
    public function createOrderFromGig(int $gigId, int $buyerId, array $data)
    {
        DB::beginTransaction();

        try {
            $gig = Gig::with('user')->find($gigId);

            if (!$gig || $gig->status !== 'active') {
                throw new Exception('Gig not available');
            }

            if ($gig->user_id === $buyerId) {
                throw new Exception('Cannot order your own gig');
            }

            // Room handling
            $room = Room::betweenUsers($buyerId, $gig->user_id)->first();

            if (!$room) {
                $room = Room::create([
                    'first_user_id' => $buyerId,
                    'second_user_id' => $gig->user_id,
                ]);
            }

            /*
        |--------------------------------------------------------------------------
        | PRICING CALCULATION
        |--------------------------------------------------------------------------
        */

            $quantity = $data['quantity'];
            $basePrice = $gig->price * $quantity;

            $deliveryDays = $gig->delivery_days;
            $extrasCost = 0;

            // Handle Extras (Example: Fast Delivery)
            if (!empty($data['extras']['fast_delivery'])) {

                // You can later store this in gig_extras table
                $fastDeliveryCost = 60; // From UI
                $extrasCost += $fastDeliveryCost;

                $deliveryDays = max(1, $gig->delivery_days - 1); // Faster delivery
            }

            $totalPrice = $basePrice + $extrasCost;

            $platformFee = round($totalPrice * 0.10, 2);
            $sellerEarnings = $totalPrice - $platformFee;

            $expectedDeliveryAt = now()->addDays($deliveryDays);

            /*
            |--------------------------------------------------------------------------
            | CREATE ORDER
            |--------------------------------------------------------------------------
            */

            $order = Order::create([
                'gig_id' => $gigId,
                'buyer_id' => $buyerId,
                'seller_id' => $gig->user_id,
                'room_id' => $room->id,

                'price' => $totalPrice,
                'platform_fee' => $platformFee,
                'seller_earnings' => $sellerEarnings,

                'delivery_days' => $deliveryDays,
                'expected_delivery_at' => $expectedDeliveryAt,

                'max_revisions' => 1,
                'requirements' => $gig->scope,

                'status' => 'pending_payment',
            ]);

            /*
            |--------------------------------------------------------------------------
            | SYSTEM MESSAGE
            |--------------------------------------------------------------------------
            */

            Chat::create([
                'sender_id' => $buyerId,
                'receiver_id' => $gig->user_id,
                'room_id' => $room->id,
                'type' => 'order_placed',
                'order_id' => $order->id,
                'text' => "Order placed ({$quantity}x): {$gig->title}",
                'metadata' => [
                    'quantity' => $quantity,
                    'extras_cost' => $extrasCost,
                    'total_price' => $totalPrice,
                ],
            ]);

            $room->update(['last_message_at' => now()]);

            OrderActivity::create([
                'order_id' => $order->id,
                'user_id' => $buyerId,
                'type' => 'order_placed',
                'description' => 'Order created from gig',
            ]);

            DB::commit();

            return $order->load(['gig', 'buyer.profile', 'seller.profile', 'room']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    /**
     * Mark order as paid (after payment gateway)
     */
    // public function markOrderAsPaid(int $orderId, array $paymentData = [])
    // {
    //     DB::beginTransaction();
    //     try {
    //         $order = Order::with(['gig', 'room', 'buyer', 'seller'])->find($orderId);

    //         if (!$order) {
    //             throw new Exception('Order not found');
    //         }

    //         if ($order->status !== 'pending_payment') {
    //             throw new Exception('Order is not pending payment');
    //         }

    //         // Update order
    //         $order->update([
    //             'status' => 'active',
    //             'paid_at' => now(),
    //             'started_at' => now(),
    //             'funds_in_escrow' => true,
    //             'payment_method' => $paymentData['payment_method'] ?? 'stripe',
    //             'payment_intent_id' => $paymentData['payment_intent_id'] ?? null,
    //         ]);

    //         // Increment gig orders
    //         if ($order->gig_id) {
    //             $order->gig->increment('orders');
    //         }

    //         // Create earning record (in pending status)
    //         SellerEarnings::create([
    //             'seller_id' => $order->seller_id,
    //             'order_id' => $orderId,
    //             'gross_amount' => $order->price,
    //             'platform_fee' => $order->platform_fee,
    //             'net_amount' => $order->seller_earnings,
    //             'status' => 'pending',
    //         ]);

    //         // Update room
    //         $order->room->update(['has_active_order' => true]);

    //         // Send system message
    //         Chat::create([
    //             'sender_id' => $order->buyer_id,
    //             'receiver_id' => $order->seller_id,
    //             'room_id' => $order->room_id,
    //             'type' => 'system',
    //             'order_id' => $orderId,
    //             'text' => "Payment received. Order #{$order->order_number} is now active.",
    //         ]);

    //         $order->room->update(['last_message_at' => now()]);

    //         OrderActivity::create([
    //             'order_id' => $orderId,
    //             'user_id' => null,
    //             'type' => 'payment_received',
    //             'description' => 'Payment received, order activated',
    //         ]);

    //         DB::commit();

    //         return $order->fresh(['gig', 'buyer.profile', 'seller.profile', 'room']);
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         throw $e;
    //     }
    // }

    /**
     * Submit delivery for QA review
     */
    public function submitDelivery(int $orderId, int $sellerId, array $data)
    {
        DB::beginTransaction();
        try {
            $order = Order::with(['room', 'buyer', 'deliveries'])->find($orderId);

            if (!$order) {
                throw new Exception('Order not found');
            }

            if (!$order->isOwnedBySeller($sellerId)) {
                throw new Exception('Unauthorized');
            }

            if (!$order->canSubmitToQa()) {
                throw new Exception('Cannot submit delivery in current status');
            }

            // Upload files
            $files = [];
            if (!empty($data['files']) && is_array($data['files'])) {
                foreach ($data['files'] as $file) {
                    $path = Helper::fileUpload($file, 'orders/deliveries');
                    if ($path) {
                        $files[] = $path;
                    }
                }
            }

            // Get next delivery number
            $deliveryNumber = $order->deliveries()->count() + 1;

            // Create delivery
            $delivery = OrderDelivery::create([
                'order_id' => $orderId,
                'delivery_number' => $deliveryNumber,
                'message' => $data['message'] ?? null,
                'files' => $files,
                'status' => 'pending_qa',
                'submitted_at' => now(),
            ]);

            // Update order status
            $order->update([
                'status' => 'qa_pending',
                'qa_submitted_at' => now(),
            ]);

            // Create QA review
            OrderQaReview::create([
                'order_id' => $orderId,
                'delivery_id' => $delivery->id,
                'status' => 'pending',
                'submitted_at' => now(),
            ]);

            // Send message to buyer (notification only, actual delivery after QA)
            Chat::create([
                'sender_id' => $sellerId,
                'receiver_id' => $order->buyer_id,
                'room_id' => $order->room_id,
                'type' => 'delivery_submitted',
                'order_id' => $orderId,
                'delivery_id' => $delivery->id,
                'text' => "Delivery submitted for QA review - Order #{$order->order_number}",
                'metadata' => [
                    'delivery_number' => $deliveryNumber,
                    'file_count' => count($files),
                ],
            ]);

            $order->room->update(['last_message_at' => now()]);

            OrderActivity::create([
                'order_id' => $orderId,
                'user_id' => $sellerId,
                'type' => 'delivery_submitted',
                'description' => "Delivery #{$deliveryNumber} submitted for QA",
            ]);

            DB::commit();

            return $order->fresh(['gig', 'buyer.profile', 'seller.profile', 'room', 'latestDelivery']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Request extension
     */
    public function requestExtension(int $orderId, int $sellerId, array $data)
    {
        DB::beginTransaction();
        try {
            $order = Order::with(['room', 'buyer'])->find($orderId);

            if (!$order) {
                throw new Exception('Order not found');
            }

            if (!$order->isOwnedBySeller($sellerId)) {
                throw new Exception('Unauthorized');
            }

            if (!$order->canRequestExtension()) {
                throw new Exception('Cannot request extension');
            }

            // Create extension request
            $extension = ExtensionRequest::create([
                'order_id' => $orderId,
                'requested_by' => $sellerId,
                'room_id' => $order->room_id,
                'additional_days' => $data['additional_days'],
                'reason' => $data['reason'],
                'status' => 'pending',
                'requested_at' => now(),
            ]);

            $newDeliveryDate = $order->expected_delivery_at
                ->copy()
                ->addDays((int) $data['additional_days'])
                ->format('Y-m-d H:i:s');

            // Send message
            Chat::create([
                'sender_id' => $sellerId,
                'receiver_id' => $order->buyer_id,
                'room_id' => $order->room_id,
                'type' => 'extension_request',
                'order_id' => $orderId,
                'delivery_date' => $order->expected_delivery_at,
                'text' => "Extension requested: {$data['additional_days']} days\nReason: {$data['reason']}",
                'metadata' => [
                    'extension_id' => $extension->id,
                    'additional_days' => $data['additional_days'],
                    'reason' => $data['reason'],
                    'new_delivery_date' => $newDeliveryDate,
                ],
            ]);

            $order->room->update(['last_message_at' => now()]);

            OrderActivity::create([
                'order_id' => $orderId,
                'user_id' => $sellerId,
                'type' => 'extension_requested',
                'description' => "Extension requested: {$data['additional_days']} days",
            ]);

            DB::commit();

            return $extension->fresh(['order', 'requester.profile']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Withdraw request extenstion
     */
    public function withdrawExtensionRequest(int $extensionId, int $sellerId)
    {
        DB::beginTransaction();

        try {
            $extension = ExtensionRequest::with('order.room')
                ->where('id', $extensionId)
                ->where('status', 'pending')
                ->first();

            if (!$extension) {
                throw new Exception('Extension not found');
            }

            if ($extension->requested_by !== $sellerId) {
                throw new Exception('Unauthorized');
            }

            // Update status
            $extension->update([
                'status' => 'withdrawn',
                'withdrawn_at' => now(),
            ]);

            $order = $extension->order;

            // Send chat message
            Chat::create([
                'sender_id' => $sellerId,
                'receiver_id' => $order->buyer_id,
                'room_id' => $order->room_id,
                'type' => 'extension_withdrawn',
                'order_id' => $order->id,
                'text' => "Extension request withdrawn",
                'metadata' => [
                    'extension_id' => $extension->id,
                ],
            ]);

            $order->room->update(['last_message_at' => now()]);

            // Activity log
            OrderActivity::create([
                'order_id' => $order->id,
                'user_id' => $sellerId,
                'type' => 'extension_withdrawn',
                'description' => "Extension request withdrawn",
            ]);

            DB::commit();

            return $extension->fresh(['order', 'requester.profile']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Respond to extension request
     */
    public function respondToExtension(int $extensionId, int $buyerId, string $action)
    {
        DB::beginTransaction();
        try {
            $extension = ExtensionRequest::with('order.room')->find($extensionId);

            if (!$extension) {
                throw new Exception('Extension request not found');
            }

            $order = $extension->order;

            if (!$order->isOwnedByBuyer($buyerId)) {
                throw new Exception('Unauthorized');
            }

            if (!$extension->isPending()) {
                throw new Exception('Extension already responded');
            }

            $newStatus = $action === 'approve' ? 'approved' : 'rejected';
            $messageType = $action === 'approve' ? 'extension_approved' : 'extension_rejected';
            $messageText = $action === 'approve'
                ? "Extension approved: {$extension->additional_days} days added"
                : "Extension request rejected";

            // Update extension
            $extension->update([
                'status' => $newStatus,
                'responded_at' => now(),
            ]);

            // If approved, update order delivery date
            if ($action === 'approve') {
                $newDeliveryDate = $order->expected_delivery_at->addDays($extension->additional_days);
                $order->update(['expected_delivery_at' => $newDeliveryDate]);
            }

            // Send message
            Chat::create([
                'sender_id' => $buyerId,
                'receiver_id' => $order->seller_id,
                'room_id' => $order->room_id,
                'type' => $messageType,
                'order_id' => $order->id,
                'text' => $messageText,
                'metadata' => [
                    'extension_id' => $extension->id,
                    'action' => $action,
                ],
            ]);

            $order->room->update(['last_message_at' => now()]);

            OrderActivity::create([
                'order_id' => $order->id,
                'user_id' => $buyerId,
                'type' => $action === 'approve' ? 'extension_approved' : 'extension_rejected',
                'description' => $messageText,
            ]);

            DB::commit();

            return $extension->fresh(['order', 'requester.profile']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Request revision (Client)
     */
    public function requestRevision(int $orderId, int $buyerId, string $reason)
    {
        DB::beginTransaction();
        try {
            $order = Order::with(['room', 'latestDelivery'])->find($orderId);

            if (!$order) {
                throw new Exception('Order not found');
            }

            if (!$order->isOwnedByBuyer($buyerId)) {
                throw new Exception('Unauthorized');
            }

            if (!$order->canRequestRevision()) {
                throw new Exception('Cannot request revision');
            }

            // Update latest delivery
            $latestDelivery = $order->latestDelivery;
            if ($latestDelivery) {
                $latestDelivery->update([
                    'status' => 'revision_requested',
                    'revision_reason' => $reason,
                    'client_reviewed_at' => now(),
                ]);
            }

            // Update order
            $order->update([
                'status' => 'active', // Back to active for seller to work
                'revision_count' => $order->revision_count + 1,
                'auto_complete_at' => null,
            ]);

            // Send message
            Chat::create([
                'sender_id' => $buyerId,
                'receiver_id' => $order->seller_id,
                'room_id' => $order->room_id,
                'type' => 'revision_request',
                'order_id' => $orderId,
                'text' => "Revision requested\nReason: {$reason}",
                'metadata' => [
                    'reason' => $reason,
                    'revision_number' => $order->revision_count,
                ],
            ]);

            $order->room->update(['last_message_at' => now()]);

            OrderActivity::create([
                'order_id' => $orderId,
                'user_id' => $buyerId,
                'type' => 'revision_requested',
                'description' => 'Revision requested by buyer',
            ]);

            DB::commit();

            return $order->fresh(['gig', 'buyer.profile', 'seller.profile', 'room', 'latestDelivery']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Accept delivery (Client - after QA approval)
     */
    // public function acceptDelivery(int $orderId, int $buyerId)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $order = Order::with(['room', 'latestDelivery', 'earning', 'seller'])->find($orderId);

    //         if (!$order) {
    //             throw new Exception('Order not found');
    //         }

    //         if (!$order->isOwnedByBuyer($buyerId)) {
    //             throw new Exception('Unauthorized');
    //         }

    //         if (!$order->canAccept()) {
    //             throw new Exception('Cannot accept delivery');
    //         }

    //         // Update latest delivery
    //         $latestDelivery = $order->latestDelivery;
    //         if ($latestDelivery) {
    //             $latestDelivery->update([
    //                 'status' => 'accepted',
    //                 'client_reviewed_at' => now(),
    //             ]);
    //         }

    //         // Update order
    //         $order->update([
    //             'status' => 'completed',
    //             'completed_at' => now(),
    //         ]);

    //         // Move earning to clearing (14 days hold)
    //         $earning = $order->earning;
    //         if ($earning) {
    //             $availableAt = now()->addDays(14);

    //             $earning->update([
    //                 'status' => 'clearing',
    //                 'available_at' => $availableAt,
    //             ]);

    //             // Update seller pending clearance
    //             $order->seller->increment('pending_clearance', $earning->net_amount);
    //         }

    //         // Send message
    //         Chat::create([
    //             'sender_id' => $buyerId,
    //             'receiver_id' => $order->seller_id,
    //             'room_id' => $order->room_id,
    //             'type' => 'order_completed',
    //             'order_id' => $orderId,
    //             'text' => "Order completed successfully - #{$order->order_number}",
    //         ]);

    //         $order->room->update(['last_message_at' => now()]);

    //         // Check if room has other active orders
    //         $hasActiveOrders = Order::where('room_id', $order->room_id)
    //             ->whereIn('status', ['active', 'qa_pending', 'delivered'])
    //             ->exists();

    //         $order->room->update(['has_active_order' => $hasActiveOrders]);

    //         OrderActivity::create([
    //             'order_id' => $orderId,
    //             'user_id' => $buyerId,
    //             'type' => 'order_completed',
    //             'description' => 'Order completed by buyer',
    //         ]);

    //         DB::commit();

    //         return $order->fresh(['gig', 'buyer.profile', 'seller.profile', 'room', 'latestDelivery']);
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         throw $e;
    //     }
    // }

    /**
     * Accept delivery (Client — after QA approval)
     * UPDATED to use EscrowService for proper escrow handling
     */
    public function acceptDelivery(int $orderId, int $buyerId): Order
    {
        DB::beginTransaction();
        try {
            $order = Order::with(['room', 'latestDelivery', 'seller'])->find($orderId);

            if (!$order) {
                throw new Exception('Order not found');
            }

            if (!$order->isOwnedByBuyer($buyerId)) {
                throw new Exception('Unauthorized');
            }

            // Order must be in 'delivered' status (QA approved → sent to client)
            if (!$order->canAccept()) {
                throw new Exception('Cannot accept delivery — order must be in delivered status');
            }

            // Update delivery
            $latestDelivery = $order->latestDelivery;
            if ($latestDelivery) {
                $latestDelivery->update([
                    'status'             => 'accepted',
                    'client_reviewed_at' => now(),
                ]);
            }

            // Complete the order
            $order->update([
                'status'           => 'completed',
                'completed_at'     => now(),
                'auto_complete_at' => null,
            ]);

            // ── Start 14-day escrow clearing via EscrowService ───────────
            $holdDays = config('orders.escrow_hold_days', 14);
            $this->escrowService->startClearingPeriod($order->id, $holdDays);
            // ─────────────────────────────────────────────────────────────

            // System message
            Chat::create([
                'sender_id'   => $buyerId,
                'receiver_id' => $order->seller_id,
                'room_id'     => $order->room_id,
                'type'        => 'order_completed',
                'order_id'    => $orderId,
                'text'        => "Order #{$order->order_number} completed successfully! Payment will be released to expert in {$holdDays} days.",
            ]);

            $order->room->update(['last_message_at' => now()]);

            // Check for other active orders in this room
            $hasActiveOrders = Order::where('room_id', $order->room_id)
                ->whereIn('status', ['active', 'qa_pending', 'delivered'])
                ->exists();

            $order->room->update(['has_active_order' => $hasActiveOrders]);

            OrderActivity::create([
                'order_id'    => $orderId,
                'user_id'     => $buyerId,
                'type'        => 'order_completed',
                'description' => 'Order accepted by client. Escrow period started.',
            ]);

            DB::commit();

            return $order->fresh(['gig', 'buyer.profile', 'seller.profile', 'room', 'latestDelivery']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get order details
     */
    public function getOrderDetails(int $orderId, int $userId)
    {
        $order = Order::with([
            'gig',
            'customOffer',
            'buyer.profile',
            'seller.profile',
            'room',
            'deliveries',
            'extensionRequests',
            'activities',
        ])
            ->find($orderId);

        if (!$order) {
            throw new Exception('Order not found');
        }

        // Verify user is part of this order
        if (!$order->isOwnedByBuyer($userId) && !$order->isOwnedBySeller($userId)) {
            throw new Exception('Unauthorized access to this order');
        }

        return $order;
    }
}
