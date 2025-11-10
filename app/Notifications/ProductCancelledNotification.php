<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductCancelledNotification extends Notification
{
    use Queueable;

   private $product;

    public function __construct($product)
    {
        $this->product = $product;
    }

    public function via($notifiable)
    {
        return ['database']; 
    }
    public function toArray($notifiable)
    {
        return [
            'message' => 'Your listing  '. $this->product->title .'  has been cancelled.',
            'product_id' => $this->product->id,
        ];
    }
}
