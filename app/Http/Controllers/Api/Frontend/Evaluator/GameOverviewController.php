<?php

namespace App\Http\Controllers\Api\Frontend\Evaluator;

use Illuminate\Http\Request;
use Modules\Director\Models\Camp;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;

class GameOverviewController extends Controller
{
    use ApiResponse;

    // Game overview for evaluator
    public function gameOverview($campId)
    {
        $user = auth('api')->user();

        // Camp Details Controller
        $camp = Camp::where('id', $campId)
            ->with([
                'schedule.locations',
                'schedule.gameSlots.slotAssignments.assignable',
            ])
            ->first();

        if (!$camp) {
            return $this->error([], 'Camp not found.', 404);
        }

        $response = [
            'camp' => [
                'camp_name' => $camp->camp_name,
                'location' => $camp->location,
                'schedule' => [
                    'locations' => $camp->schedule?->locations->map(function ($location) {
                        return [
                            'id' => $location->id,
                            'name' => $location->location_name,
                        ] ?? null;
                    }) ?? collect(),
                    'game_courts' => [
                        'court_count' => $camp->schedule?->gameSlots->count(),
                        'game_court' => $camp->schedule?->gameSlots->map(function ($gameSlot) {
                            return [
                                'id' => $gameSlot->id,
                                'court_name' => $gameSlot->court_name,
                                // Slot Assignments (Referees/Crew)
                                'assignments' => $gameSlot->slotAssignments->map(function ($assignment) {
                                    // Check if it's crew or individual
                                    if ($assignment->assignment_type === 'crew') {
                                        return [
                                            'type' => 'crew',
                                            'crew_id' => $assignment->assignable_id,
                                            'crew_name' => $assignment->assignable->crew_name ?? 'N/A',
                                            'position' => $assignment->position,
                                            'is_auto_assigned' => $assignment->is_auto_assigned,
                                        ];
                                    } else {
                                        // Individual referee
                                        $referee = $assignment->assignable; // This is User model
                                        return [
                                            'type' => 'individual',
                                            'referee_id' => $referee->id,
                                            'name' => $referee->first_name . ' ' . $referee->last_name,
                                        ];
                                    }
                                }),
                            ];
                        }) ?? collect(),
                    ]
                ],
            ]
        ];

        return $this->success(
            'Game overview fetched successfully.',
            $response,
            200
        );
    }
}
