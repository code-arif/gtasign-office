<?php
// app/Notifications/OrderCreatedNotification.php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class OrderCreatedNotification extends Notification
{
    use Queueable;

    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['database'];  // You can add more channels like 'mail', 'slack', etc.
    }

    public function toDatabase($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'message' => 'You have successfully purchased a new order on ' . now()->format('Y-m-d H:i:s') . ' (UTC).',
            'order_total' => $this->order->total_amount,
        ];
    }
}
