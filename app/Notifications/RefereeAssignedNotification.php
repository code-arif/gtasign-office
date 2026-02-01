<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notification when referee is assigned to a game slot
 */
class RefereeAssignedNotification extends Notification
{
    use Queueable;

    protected $gameSlot;
    protected $camp;
    protected $director;
    protected $assignmentType; // 'individual' or 'crew'
    protected $crewName;

    public function __construct($gameSlot, $camp, $director, $assignmentType = 'individual', $crewName = null)
    {
        $this->gameSlot = $gameSlot;
        $this->camp = $camp;
        $this->director = $director;
        $this->assignmentType = $assignmentType;
        $this->crewName = $crewName;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $message = $this->assignmentType === 'crew'
            ? "You have been assigned to a game as part of {$this->crewName} crew."
            : "You have been assigned to a new game slot.";

        return [
            'type' => 'referee_assigned',

            'title' => 'New Game Assignment',

            'message' => $message,

            'assignment_type' => $this->assignmentType,

            'crew_name' => $this->crewName,

            'game_slot' => [
                'id' => $this->gameSlot->id,
                'date' => $this->gameSlot->game_date,
                'start_time' => $this->gameSlot->start_time,
                'end_time' => $this->gameSlot->end_time,
                'court_name' => $this->gameSlot->court_name,
                'court_number' => $this->gameSlot->court_number,
            ],

            'location' => [
                'name' => $this->gameSlot->location->location_name ?? 'N/A',
                'latitude' => $this->gameSlot->location->latitude ?? null,
                'longitude' => $this->gameSlot->location->longitude ?? null,
            ],

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

            'action_url' => "/referee/camp/{$this->camp->id}/assigned-slots",
        ];
    }
}
