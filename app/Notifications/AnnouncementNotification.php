<?php

namespace App\Notifications;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AnnouncementNotification extends Notification
{
    use Queueable;

    protected $announcement;

    public function __construct(Announcement $announcement)
    {
        $this->announcement = $announcement;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification (for database).
     */
    public function toArray($notifiable)
    {
        return [
            'announcement_id' => $this->announcement->id,
            'subject' => $this->announcement->subject,
            'message' => $this->announcement->message,
            'created_by' => $this->announcement->created_by,
            'creator_name' => $this->announcement->creator->first_name ?? 'Director',
            'sent_at' => $this->announcement->sent_at,
            'type' => 'announcement', // Distinguish from other notification types
        ];
    }
}
