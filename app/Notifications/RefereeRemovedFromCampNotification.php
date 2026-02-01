<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RefereeRemovedFromCampNotification extends Notification
{
    use Queueable;

    protected $camp;
    protected $director;

    public function __construct($camp, $director)
    {
        $this->camp = $camp;
        $this->director = $director;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'referee_removed_from_camp',

            'title' => 'Removed from Camp',

            'message' => 'You have been removed from a camp by the camp director.',

            'camp' => [
                'id' => $this->camp->id,
                'name' => $this->camp->camp_name,
            ],

            'removed_by' => [
                'id' => $this->director->id,
                'name' => $this->director->first_name . ' ' . $this->director->last_name,
                'role' => 'Director',
            ],
        ];
    }
}
