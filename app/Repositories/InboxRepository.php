<?php

namespace App\Repositories;

use App\Models\Room;
use App\Models\Chat;
use Illuminate\Pagination\LengthAwarePaginator;

class InboxRepository
{
    protected $roomModel;
    protected $chatModel;

    public function __construct(Room $roomModel, Chat $chatModel)
    {
        $this->roomModel = $roomModel;
        $this->chatModel = $chatModel;
    }

    /**
     * Get user's inbox rooms
     */
    public function getUserRooms(int $userId, ?string $search = null, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->roomModel
            ->with(['firstUser.profile', 'secondUser.profile', 'latestMessage'])
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

        return $query->paginate($perPage);
    }

    /**
     * Get or create room between two users
     */
    public function getOrCreateRoom(int $userId1, int $userId2): ?Room
    {
        if ($userId1 === $userId2) {
            return null;
        }

        $room = $this->roomModel
            ->with(['firstUser.profile', 'secondUser.profile'])
            ->betweenUsers($userId1, $userId2)
            ->first();

        if (!$room) {
            $room = $this->roomModel->create([
                'first_user_id' => $userId1,
                'second_user_id' => $userId2,
            ]);
            $room->load(['firstUser.profile', 'secondUser.profile']);
        }

        return $room;
    }

    /**
     * Get room messages
     */
    public function getRoomMessages(int $roomId, int $perPage = 50): LengthAwarePaginator
    {
        return $this->chatModel
            ->with([
                'sender.profile',
                'receiver.profile',
                'customOffer',
                'order.gig',
                'delivery'
            ])
            ->where('room_id', $roomId)
            ->latest('id')
            ->paginate($perPage);
    }

    /**
     * Create message
     */
    public function createMessage(array $data): Chat
    {
        return $this->chatModel->create($data);
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(int $roomId, int $userId): int
    {
        return $this->chatModel
            ->where('room_id', $roomId)
            ->where('receiver_id', $userId)
            ->where('status', '!=', 'read')
            ->update(['status' => 'read']);
    }

    /**
     * Update room last message time
     */
    public function updateRoomLastMessage(int $roomId): void
    {
        $room = $this->roomModel->find($roomId);
        if ($room) {
            $room->updateLastMessageTime();
        }
    }
}
