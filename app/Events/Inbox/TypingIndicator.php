<?php

namespace App\Events\Inbox;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TypingIndicator implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $roomId,
        public int $userId,
        public bool $isTyping,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("inbox.room.{$this->roomId}")];
    }

    public function broadcastAs(): string
    {
        return 'user.typing';
    }

    public function broadcastWith(): array
    {
        return [
            'room_id'   => $this->roomId,
            'user_id'   => $this->userId,
            'is_typing' => $this->isTyping,
        ];
    }
}
