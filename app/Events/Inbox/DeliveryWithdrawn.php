<?php

namespace App\Events\Order;

use App\Models\Order;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryWithdrawn implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;
    public function __construct(public Order $order) {}
    public function broadcastOn(): array
    {
        return [new PrivateChannel("order.{$this->order->id}"), new PrivateChannel("inbox.room.{$this->order->room_id}")];
    }
    public function broadcastAs(): string
    {
        return 'delivery.withdrawn';
    }
    public function broadcastWith(): array
    {
        return ['order_id' => $this->order->id, 'order_number' => $this->order->order_number, 'order_status' => $this->order->status, 'room_id' => $this->order->room_id];
    }
}
