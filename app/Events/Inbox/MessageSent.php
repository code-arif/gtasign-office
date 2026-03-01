<?php

namespace App\Events\Inbox;

use App\Models\Chat;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Chat $chat) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("inbox.room.{$this->chat->room_id}"),
            new PrivateChannel("inbox.list.{$this->chat->receiver_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        $this->chat->load(['sender.profile']);
        return [
            'id'                   => $this->chat->id,
            'room_id'              => $this->chat->room_id,
            'sender_id'            => $this->chat->sender_id,
            'receiver_id'          => $this->chat->receiver_id,
            'text'                 => $this->chat->text,
            'type'                 => $this->chat->type,
            'status'               => $this->chat->status,
            'metadata'             => $this->chat->metadata,
            'order_id'             => $this->chat->order_id,
            'delivery_id'          => $this->chat->delivery_id,
            'extension_request_id' => $this->chat->extension_request_id ?? null,
            'custom_offer_id'      => $this->chat->custom_offer_id,
            'sender' => [
                'id'       => $this->chat->sender->id,
                'name'     => trim($this->chat->sender->profile?->first_name . ' ' . $this->chat->sender->profile?->last_name),
                'avatar'   => $this->chat->sender->profile?->avatar,
                'username' => $this->chat->sender->profile?->username,
            ],
            'created_at' => $this->chat->created_at->toISOString(),
        ];
    }
}
