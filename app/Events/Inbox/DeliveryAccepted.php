<?php

namespace App\Events\Order;

use App\Models\Order;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryAccepted implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;
    public function __construct(public Order $order) {}
    public function broadcastOn(): array
    {
        return [new PrivateChannel("order.{$this->order->id}"), new PrivateChannel("inbox.room.{$this->order->room_id}"), new PrivateChannel("user.{$this->order->seller_id}")];
    }
    public function broadcastAs(): string
    {
        return 'delivery.accepted';
    }
    public function broadcastWith(): array
    {
        return ['order_id' => $this->order->id, 'order_number' => $this->order->order_number, 'order_status' => $this->order->status, 'room_id' => $this->order->room_id, 'completed_at' => $this->order->completed_at?->toISOString(), 'escrow_hold_days' => config('orders.escrow_hold_days', 14)];
    }
}
