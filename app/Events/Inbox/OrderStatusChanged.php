<?php

namespace App\Events\Order;

use App\Models\Order;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;
    public function __construct(public Order $order, public string $previousStatus, public ?string $triggeredByRole = null) {}
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("order.{$this->order->id}"),
            new PrivateChannel("inbox.room.{$this->order->room_id}"),
            new PrivateChannel("user.{$this->order->buyer_id}"),
            new PrivateChannel("user.{$this->order->seller_id}"),
        ];
    }
    public function broadcastAs(): string
    {
        return 'order.status.changed';
    }
    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'room_id' => $this->order->room_id,
            'previous_status' => $this->previousStatus,
            'new_status' => $this->order->status,
            'triggered_by' => $this->triggeredByRole,
            'buyer_id' => $this->order->buyer_id,
            'seller_id' => $this->order->seller_id,
            'updated_at' => $this->order->updated_at->toISOString(),
        ];
    }
}
