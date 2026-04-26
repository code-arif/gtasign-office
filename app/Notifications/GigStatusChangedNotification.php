<?php

namespace App\Notifications;

use App\Models\Gig;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GigStatusChangedNotification extends Notification
{
    use Queueable;

    protected $gig;
    protected $status;
    protected $rejectionReason;

    /**
     * Create a new notification instance.
     */
    public function __construct(Gig $gig, string $status, ?string $rejectionReason = null)
    {
        $this->gig = $gig;
        $this->status = $status;
        $this->rejectionReason = $rejectionReason;
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
        $message = 'Your gig "' . $this->gig->title . '" status has been changed to ' . str_replace('_', ' ', $this->status) . '.';
        
        if ($this->status === 'rejected' && $this->rejectionReason) {
            $message .= ' Reason: ' . $this->rejectionReason;
        }

        return [
            'gig_id' => $this->gig->id,
            'title' => $this->gig->title,
            'status' => $this->status,
            'rejection_reason' => $this->rejectionReason,
            'message' => $message,
            'type' => 'gig_status_change'
        ];
    }
}
