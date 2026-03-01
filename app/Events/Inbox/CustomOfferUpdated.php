<?php

namespace App\Events\Inbox;

use App\Models\Chat;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomOfferUpdated implements ShouldBroadcastNow, ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Chat $chat,
        public string $offerStatus, // accepted | rejected | withdrawn
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("inbox.room.{$this->chat->room_id}"),
            new PrivateChannel("inbox.list.{$this->chat->receiver_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'offer.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'chat_id'        => $this->chat->id,
            'room_id'        => $this->chat->room_id,
            'custom_offer_id' => $this->chat->custom_offer_id,
            'offer_status'   => $this->offerStatus,
            'type'           => $this->chat->type,
            'text'           => $this->chat->text,
            'metadata'       => $this->chat->metadata,
            'created_at'     => $this->chat->created_at->toISOString(),
        ];
    }
}
