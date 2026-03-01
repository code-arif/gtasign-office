<?php

namespace App\Services\Gig;

use App\Events\Inbox\CustomOfferUpdated;
use App\Events\Inbox\MessageSent;
use App\Models\Chat;
use App\Models\CustomOffer;
use App\Models\Order;
use App\Models\OrderActivity;
use App\Models\Room;
use App\Models\User;
use App\Services\Payment\StripePaymentService;
use Exception;
use Illuminate\Support\Facades\DB;

class CustomOfferService
{
    /**
     * Create custom offer (Expert sends to client)
     */
    public function createOffer(int $expertId, array $data)
    {
        DB::beginTransaction();
        try {
            // Validate client exists
            $client = User::find($data['client_id']);
            if (!$client) {
                throw new Exception('Client not found');
            }

            // Cannot send offer to yourself
            if ($expertId === $data['client_id']) {
                throw new Exception('Cannot send offer to yourself');
            }

            // Get or create room
            $room = Room::betweenUsers($expertId, $data['client_id'])->first();

            if (!$room) {
                $room = Room::create([
                    'first_user_id' => $expertId,
                    'second_user_id' => $data['client_id'],
                ]);
            }

            // Calculate expiry (3 days)
            $expiresAt = now()->addDays(3);

            // Create custom offer
            $offer = CustomOffer::create([
                'gig_id' => $data['gig_id'] ?? null,
                'expert_id' => $expertId,
                'client_id' => $data['client_id'],
                'room_id' => $room->id,
                'title' => $data['title'] ?? null,
                'description' => $data['description'] ?? null,
                'price' => $data['price'],
                'delivery_days' => $data['delivery_days'],
                'revisions' => $data['revisions'] ?? 1,
                'status' => 'pending',
                'expires_at' => $expiresAt,
            ]);

            // Send message in inbox
            Chat::create([
                'sender_id' => $expertId,
                'receiver_id' => $data['client_id'],
                'room_id' => $room->id,
                'type' => 'custom_offer',
                'custom_offer_id' => $offer->id,
                'text' => "Sent you a custom offer: {$offer->title}",
                'metadata' => [
                    'offer_id' => $offer->id,
                    'price' => $offer->price,
                    'delivery_days' => $offer->delivery_days,
                ],
            ]);

            // Update room last message time
            $room->update(['last_message_at' => now()]);

            DB::commit();

            return $offer->load(['gig', 'expert.profile', 'client.profile', 'room']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Withdraw custom offer (Expert withdraws)
     */
    public function withdrawOffer(int $offerId, int $expertId)
    {
        DB::beginTransaction();
        try {
            $offer = CustomOffer::where('id', $offerId)
                ->where('expert_id', $expertId)
                ->where('status', 'pending')
                ->first();

            if (!$offer) {
                throw new Exception('Offer not found or cannot be withdrawn');
            }

            // Update offer status
            $offer->update([
                'status' => 'withdrawn',
                'withdrawn_at' => now(),
            ]);

            // Send withdrawal message
            Chat::create([
                'sender_id' => $expertId,
                'receiver_id' => $offer->client_id,
                'room_id' => $offer->room_id,
                'type' => 'offer_withdrawn',
                'custom_offer_id' => $offer->id,
                'text' => "Withdrew the custom offer: {$offer->title}",
            ]);

            // Update room last message time
            $offer->room->update(['last_message_at' => now()]);

            DB::commit();

            return $offer->fresh(['gig', 'expert.profile', 'client.profile', 'room']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Accept custom offer (Client accepts)
     */
    // public function acceptOffer(int $offerId, int $clientId)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $offer = CustomOffer::with(['expert', 'client', 'room'])
    //             ->where('id', $offerId)
    //             ->where(function ($q) use ($clientId) {
    //                 $q->where('expert_id', $clientId)->orWhere('client_id', $clientId);
    //             })
    //             ->first();

    //         if (!$offer) {
    //             throw new Exception('Offer not found');
    //         }

    //         // Validate client is the recipient
    //         if ($offer->client_id !== $clientId) {
    //             throw new Exception('Unauthorized to accept this offer');
    //         }

    //         // Check if offer can be accepted
    //         if (!$offer->canAccept()) {
    //             throw new Exception('Offer cannot be accepted (expired or already responded)');
    //         }

    //         // Update offer status
    //         $offer->update([
    //             'status' => 'accepted',
    //             'accepted_at' => now(),
    //         ]);

    //         // Send acceptance message
    //         Chat::create([
    //             'sender_id' => $clientId,
    //             'receiver_id' => $offer->expert_id,
    //             'room_id' => $offer->room_id,
    //             'type' => 'offer_accepted',
    //             'custom_offer_id' => $offer->id,
    //             'text' => "Accepted your custom offer: {$offer->title}",
    //         ]);

    //         // Update room
    //         $offer->room->update(['last_message_at' => now()]);

    //         DB::commit();

    //         return $offer->fresh(['gig', 'expert.profile', 'client.profile', 'room']);
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         throw $e;
    //     }
    // }


    /**
     * Accept custom offer — creates the order and Stripe checkout in one step.
     *
     * Returns array with offer, order details, and Stripe checkout URL.
     * The order stays in 'pending_payment' until Stripe webhook confirms payment.
     */
    public function acceptOffer(int $offerId, int $clientId): array
    {
        DB::beginTransaction();
        try {
            // ── 1. Load and validate offer ────────────────────────────────
            $offer = CustomOffer::with(['expert', 'client', 'room', 'gig'])
                ->where('id', $offerId)
                ->where(function ($q) use ($clientId) {
                    $q->where('expert_id', $clientId)->orWhere('client_id', $clientId);
                })
                ->first();

            if (!$offer) {
                throw new Exception('Offer not found');
            }

            if ($offer->client_id !== $clientId) {
                throw new Exception('Unauthorized to accept this offer');
            }

            if (!$offer->canAccept()) {
                throw new Exception('Offer cannot be accepted (expired or already responded)');
            }

            // ── 2. Mark offer as accepted ─────────────────────────────────
            $offer->update([
                'status'      => 'accepted',
                'accepted_at' => now(),
            ]);

            // ── 3. Create order in pending_payment state ──────────────────
            $price          = $offer->price;
            $platformFee    = round($price * 0.10, 2);
            $sellerEarnings = $price - $platformFee;

            $order = Order::create([
                'gig_id'              => $offer->gig_id,
                'custom_offer_id'     => $offer->id,
                'buyer_id'            => $clientId,
                'seller_id'           => $offer->expert_id,
                'room_id'             => $offer->room_id,
                'price'               => $price,
                'platform_fee'        => $platformFee,
                'seller_earnings'     => $sellerEarnings,
                'delivery_days'       => $offer->delivery_days,
                'expected_delivery_at' => now()->addDays($offer->delivery_days),
                'max_revisions'       => $offer->revisions,
                'requirements'        => $offer->description,
                'status'              => 'pending_payment',
            ]);

            // Mark offer as converted
            $offer->update(['status' => 'converted_to_order']);

            // ── 4. Chat message (order placed) ────────────────────────────
            $chat = Chat::create([
                'sender_id'   => $clientId,
                'receiver_id' => $offer->expert_id,
                'room_id'     => $offer->room_id,
                'type'        => 'order_placed',
                'order_id'    => $order->id,
                'text'        => "Order placed from custom offer: {$offer->title} — #{$order->order_number}",
                'metadata'    => [
                    'order_id'     => $order->id,
                    'order_number' => $order->order_number,
                    'price'        => $order->price,
                ],
            ]);

            $offer = $offer->room->update(['last_message_at' => now()]);

            OrderActivity::create([
                'order_id'    => $order->id,
                'user_id'     => $clientId,
                'type'        => 'order_placed',
                'description' => 'Order created from accepted custom offer. Awaiting payment.',
            ]);

            // ── 5. Create Stripe Checkout Session ─────────────────────────
            $stripeService = app(StripePaymentService::class);
            $checkout = $stripeService->createCheckoutSession($order);

            DB::commit();

            // Notify expert offer accepted
            // broadcast(new CustomOfferUpdated($offer, 'accepted'))->toOthers();

            // send realtime chat message
            // broadcast(new MessageSent(
            //     $chat->load(['sender.profile', 'receiver.profile'])
            // ))->toOthers();

            return [
                'offer'        => $offer->fresh(['gig', 'expert.profile', 'client.profile', 'room']),
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'checkout_url' => $checkout['checkout_url'],
                'expires_at'   => $checkout['expires_at'],
            ];
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    /**
     * Reject custom offer
     */
    public function rejectOffer(int $offerId, int $clientId, ?string $reason = null)
    {
        DB::beginTransaction();
        try {
            $offer = CustomOffer::with(['expert', 'client', 'room'])
                ->where('id', $offerId)
                ->where(function ($q) use ($clientId) {
                    $q->where('expert_id', $clientId)->orWhere('client_id', $clientId);
                })
                ->first();

            if (!$offer) {
                throw new Exception('Offer not found');
            }

            if ($offer->client_id !== $clientId) {
                throw new Exception('Unauthorized to reject this offer');
            }

            if (!$offer->canAccept()) {
                throw new Exception('Offer cannot be rejected');
            }

            // Update offer
            $offer->update([
                'status' => 'rejected',
                'rejected_at' => now(),
            ]);

            // Send rejection message
            $messageText = "Rejected your custom offer: {$offer->title}";
            if ($reason) {
                $messageText .= "\nReason: {$reason}";
            }

            Chat::create([
                'sender_id' => $clientId,
                'receiver_id' => $offer->expert_id,
                'room_id' => $offer->room_id,
                'type' => 'offer_rejected',
                'custom_offer_id' => $offer->id,
                'text' => $messageText,
                'metadata' => ['reason' => $reason],
            ]);

            $offer->room->update(['last_message_at' => now()]);

            DB::commit();

            return $offer->fresh(['gig', 'expert.profile', 'client.profile', 'room']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get offers in room
     */
    public function getRoomOffers(int $roomId, int $userId, array $filters = [], int $perPage = 15)
    {
        // Verify user is part of the room
        $room = Room::find($roomId);

        if (!$room || !$room->hasUser($userId)) {
            throw new Exception('Room not found or unauthorized');
        }

        $query = CustomOffer::with(['gig', 'expert.profile', 'client.profile'])
            ->where('room_id', $roomId);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Mark expired offers (for scheduled task)
     */
    public function markExpiredOffers()
    {
        $expiredOffers = CustomOffer::where('status', 'pending')
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($expiredOffers as $offer) {
            try {
                $offer->update(['status' => 'expired']);
            } catch (Exception $e) {
                \Log::error("Failed to expire offer {$offer->id}: " . $e->getMessage());
            }
        }

        return $expiredOffers->count();
    }
}
