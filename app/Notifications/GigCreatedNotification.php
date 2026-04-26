<?php

namespace App\Notifications;

use App\Models\Gig;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GigCreatedNotification extends Notification
{
    use Queueable;

    protected $gig;

    /**
     * Create a new notification instance.
     */
    public function __construct(Gig $gig)
    {
        $this->gig = $gig;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'gig_id' => $this->gig->id,
            'title' => $this->gig->title,
            'user_id' => $this->gig->user_id,
            'message' => 'A new gig "' . $this->gig->title . '" has been created and is pending approval.',
            'action_url' => '/admin/gigs/' . $this->gig->id, // Adjust based on admin panel URL
            'type' => 'gig_creation'
        ];
    }
}
