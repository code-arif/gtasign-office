<?php

namespace App\Services\Gig;

use Exception;
use App\Models\Room;
use App\Models\Chat;
use App\Models\User;
use App\Helpers\Helper;
use Illuminate\Support\Facades\DB;

class InboxService
{
    /**
     * Get user's inbox list
     */
    public function getInboxList(int $userId, ?string $search = null, int $perPage = 20)
    {
        $query = Room::with(['firstUser.profile', 'secondUser.profile', 'latestMessage'])
            ->forUser($userId)
            ->recentActivity();

        // Search by other user's name
        if ($search) {
            $query->where(function ($q) use ($userId, $search) {
                $q->whereHas('firstUser.profile', function ($pq) use ($search) {
                    $pq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                })->orWhereHas('secondUser.profile', function ($pq) use ($search) {
                    $pq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            });
        }

        $rooms = $query->paginate($perPage);

        // Add unread count and other user info to each room
        $rooms->getCollection()->transform(function ($room) use ($userId) {
            $room->other_user = $room->getOtherUser($userId);
            $room->unread_count = $room->getUnreadCount($userId);
            return $room;
        });

        return $rooms;
    }

    /**
     * Get room conversation
     */
    public function getConversation(int $roomId, int $userId, int $perPage = 50)
    {
        // Get room
        $room = Room::with(['firstUser.profile', 'secondUser.profile'])->find($roomId);

        if (!$room) {
            throw new Exception('Room not found');
        }

        // Verify user is part of room
        if (!$room->hasUser($userId)) {
            throw new Exception('Unauthorized access to this room');
        }

        // Get messages (latest first, then reverse for chat display)
        $messages = Chat::with([
            'sender.profile',
            'receiver.profile',
            'customOffer.gig',
            'order.gig',
            'delivery'
        ])
            ->where('room_id', $roomId)
            ->latest('id')
            ->paginate($perPage);

        // Mark messages as read
        Chat::where('room_id', $roomId)
            ->where('receiver_id', $userId)
            ->where('status', '!=', 'read')
            ->update(['status' => 'read']);

        // Get other user
        $otherUser = $room->getOtherUserAttribute($userId);

        return [
            'room' => $room,
            'other_user' => $otherUser,
            'messages' => $messages,
            'has_active_order' => $room->has_active_order,
        ];
    }

    /**
     * Send text message
     */
    public function sendMessage(int $senderId, int $receiverId, array $data)
    {
        DB::beginTransaction();
        try {
            // Validate receiver exists
            if (!User::where('id', $receiverId)->exists()) {
                throw new Exception('Receiver not found');
            }

            // Cannot message yourself
            if ($senderId === $receiverId) {
                throw new Exception('Cannot message yourself');
            }

            // Get or create room
            $room = Room::betweenUsers($senderId, $receiverId)->first();

            if (!$room) {
                $room = Room::create([
                    'first_user_id' => $senderId,
                    'second_user_id' => $receiverId,
                ]);
            }

            // Handle file upload
            $filePath = null;
            if (isset($data['file'])) {
                $filePath = Helper::fileUpload($data['file'], 'inbox/attachments');
            }

            // Create message
            $message = Chat::create([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'room_id' => $room->id,
                'type' => 'text',
                'text' => $data['message'] ?? null,
                'file' => $filePath,
                'status'      => 'sent',
            ]);

            // IMPORTANT
            $message->refresh();

            // Update room last message
            $room->update(['last_message_at' => now()]);

            DB::commit();

            // Load relationships
            return $message->load(['sender.profile', 'receiver.profile']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Start conversation with user
     */
    public function startConversation(int $userId, int $otherUserId)
    {
        // Validate other user exists
        if (!User::where('id', $otherUserId)->exists()) {
            throw new Exception('User not found');
        }

        // Cannot start conversation with yourself
        if ($userId === $otherUserId) {
            throw new Exception('Cannot start conversation with yourself');
        }

        // Get or create room
        $room = Room::betweenUsers($userId, $otherUserId)->first();

        if (!$room) {
            $room = Room::create([
                'first_user_id' => $userId,
                'second_user_id' => $otherUserId,
            ]);
        }

        $room->load(['firstUser.profile', 'secondUser.profile']);

        // Get other user details
        $otherUser = $room->getOtherUser($userId);

        return [
            'room' => $room,
            'other_user' => $otherUser,
        ];
    }

    /**
     * Mark room messages as read
     */
    public function markAsRead(int $roomId, int $userId): int
    {
        $chat = Chat::where('room_id', $roomId)

            // messages NOT sent by me
            ->where('sender_id', '!=', $userId)

            // unread messages only
            ->whereIn('status', ['sent', 'delivered'])

            ->update([
                'status' => 'read',
                'updated_at' => now(),
            ]);
        return $chat;
    }
}
