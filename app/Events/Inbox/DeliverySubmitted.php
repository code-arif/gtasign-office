<?php

namespace App\Events\Order;

use App\Models\Order;
use App\Models\OrderDelivery;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliverySubmitted implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;
    public function __construct(public Order $order, public OrderDelivery $delivery) {}
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("order.{$this->order->id}"),
            new PrivateChannel("inbox.room.{$this->order->room_id}"),
            new PrivateChannel("user.{$this->order->buyer_id}"),
        ];
    }
    public function broadcastAs(): string
    {
        return 'delivery.submitted';
    }
    public function broadcastWith(): array
    {
        return ['order_id' => $this->order->id, 'order_number' => $this->order->order_number, 'order_status' => $this->order->status, 'room_id' => $this->order->room_id, 'delivery_id' => $this->delivery->id, 'delivery_number' => $this->delivery->delivery_number, 'file_count' => count($this->delivery->files ?? []), 'message' => $this->delivery->message, 'submitted_at' => $this->delivery->submitted_at->toISOString()];
    }
}
