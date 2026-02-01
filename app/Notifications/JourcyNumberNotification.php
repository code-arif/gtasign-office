<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JourcyNumberNotification extends Notification
{
    use Queueable;

    protected $referee;
    protected $camp;
    protected $director;
    protected $jourcyNumber;
    protected $oldJourcyNumber;

    public function __construct($referee, $camp, $director, $jourcyNumber, $oldJourcyNumber = null)
    {
        $this->referee = $referee;
        $this->camp = $camp;
        $this->director = $director;
        $this->jourcyNumber = $jourcyNumber;
        $this->oldJourcyNumber = $oldJourcyNumber;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $isUpdate = !is_null($this->oldJourcyNumber);

        $message = $isUpdate
            ? "Your jersey number has been updated from #{$this->oldJourcyNumber} to #{$this->jourcyNumber}."
            : "You have been assigned jersey number #{$this->jourcyNumber}.";

        return [
            'type' => 'jourcy_number_assigned',

            'title' => $isUpdate ? 'Jersey Number Updated' : 'Jersey Number Assigned',

            'message' => $message,

            'jersey_number' => $this->jourcyNumber,

            'previous_jersey_number' => $this->oldJourcyNumber,

            'is_update' => $isUpdate,

            'camp' => [
                'id' => $this->camp->id,
                'name' => $this->camp->camp_name,
                'logo' => $this->camp->camp_logo ? asset($this->camp->camp_logo) : asset('default/no_image.webp'),
            ],

            'assigned_by' => [
                'id' => $this->director->id,
                'name' => $this->director->first_name . ' ' . $this->director->last_name,
                'role' => 'Director',
            ],

            'assigned_at' => now()->format('Y-m-d H:i:s'),

            'action_url' => "/referee/camp/{$this->camp->id}/details",
        ];
    }
}
