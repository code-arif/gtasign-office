<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SellerOrderNotification extends Notification
{
    use Queueable;

    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['database']; // You can add other channels if needed
    }

    public function toDatabase($notifiable)
    {
        return [
            'order_id'    => $this->order->id,
            'message'     => 'You have received a new order for one of your products on ' . now()->format('Y-m-d H:i:s') . ' (UTC).',
            'order_total' => $this->order->total_amount,
        ];
    }
}
