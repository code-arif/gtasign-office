<?php

namespace App\Events\Inbox;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageRead implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $roomId,
        public int $readByUserId,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("inbox.room.{$this->roomId}")];
    }

    public function broadcastAs(): string
    {
        return 'message.read';
    }

    public function broadcastWith(): array
    {
        return [
            'room_id'         => $this->roomId,
            'read_by_user_id' => $this->readByUserId,
            'read_at'         => now()->toISOString(),
        ];
    }
}
