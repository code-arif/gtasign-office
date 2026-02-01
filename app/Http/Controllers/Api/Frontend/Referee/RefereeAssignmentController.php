<?php

namespace App\Http\Controllers\Api\Frontend\Referee;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Director\Models\Camp;
use App\Http\Controllers\Controller;
use Modules\Director\Models\GameSlot;
use Modules\Director\Models\CampRefereeCheckin;
use Modules\Director\Models\GameSlotAssignment;

class RefereeAssignmentController extends Controller
{
    use ApiResponse;

    /**
     * Get all game slots assigned to referee for a specific camp
     * Camp-specific assignments with grouped referees
     */
    public function getCampAssignedSlots(Request $request, $campId)
    {
        $referee = auth('api')->user();

        // Only referees can access this endpoint
        if (!$referee->hasRole('referee')) {
            return $this->error('Only referees can access this endpoint.', null, 403);
        }

        $camp = Camp::with('schedule')->find($campId); // Load schedule relationship
        if (!$camp) {
            return $this->error('Camp not found.', null, 404);
        }

        // Check if schedule exists
        if (!$camp->schedule) {
            return $this->error('No schedule found for this camp.', null, 404);
        }

        // Check if schedule is published
        if ($camp->schedule->status !== 'published') {
            return $this->error('This camp schedule is not published yet!', null, 403);
        }

        // Check if referee is registered for this camp
        $checkin = CampRefereeCheckin::where('camp_id', $campId)
            ->where('referee_id', $referee->id)
            ->first();

        if (!$checkin) {
            return $this->error('You are not registered for this camp.', null, 403);
        }

        // Get per_page from request, default 15
        $perPage = $request->get('per_page', 15);
        $today = now()->toDateString();

        // Get all game slot assignments for this referee in this specific camp
        // Sort by closest to today first
        $assignments = GameSlotAssignment::with([
            'gameSlot.location',
            'gameSlot.schedule',
            'gameSlot.slotAssignments.assignable'
        ])
            ->where('assignable_type', User::class)
            ->where('assignable_id', $referee->id)
            ->where('assignment_type', 'individual')
            ->whereHas('gameSlot.schedule', function ($query) use ($campId) {
                $query->where('camp_id', $campId);
            })
            ->whereHas('gameSlot', function ($query) use ($today) {
                // Order by closest to today (upcoming first, then past)
                $query->orderByRaw("ABS(DATEDIFF(game_date, ?)) ASC", [$today])
                    ->orderBy('start_time', 'asc');
            })
            ->paginate($perPage);

        if ($assignments->isEmpty()) {
            return $this->success('No game slots assigned to you in this camp yet.', [
                'camp' => [
                    'id' => $camp->id,
                    'name' => $camp->camp_name,
                    'logo' => $camp->camp_logo ? asset($camp->camp_logo) : asset('default/no_image.webp'),
                    'location' => $camp->location,
                    'start_date' => $camp->start_date->toDateString(),
                    'end_date' => $camp->end_date->toDateString(),
                ],
                'total_assignments' => 0,
                'game_slots' => []
            ]);
        }

        // Format game slots with all assigned referees
        $gameSlots = $assignments->map(function ($assignment) use ($referee) {
            $gameSlot = $assignment->gameSlot;

            // Get all assignments for this game slot
            $allAssignments = $gameSlot->slotAssignments;

            // Separate crew and individual assignments
            $crewAssignments = $allAssignments->where('assignment_type', 'crew');
            $individualAssignments = $allAssignments->where('assignment_type', 'individual');

            // Build crew members list
            $crewMembers = [];
            if ($crewAssignments->isNotEmpty()) {
                foreach ($crewAssignments as $crewAssignment) {
                    $crew = $crewAssignment->assignable; // Crew model
                    if ($crew && $crew->members) {
                        foreach ($crew->members as $member) {
                            $crewMembers[] = [
                                'id' => $member->id,
                                'name' => $member->first_name . ' ' . $member->last_name,
                                'avatar' => $member->avatar ? asset($member->avatar) : asset('default/profile.jpg'),
                                'email' => $member->email,
                                'phone' => $member->phone,
                                'type' => 'crew_member',
                                'crew_name' => $crew->crew_name ?? 'N/A',
                                'is_me' => $member->id === $referee->id,
                            ];
                        }
                    }
                }
            }

            // Build individual referees list
            $individualReferees = $individualAssignments->map(function ($individualAssignment) use ($referee) {
                $assignedReferee = $individualAssignment->assignable; // User model

                return [
                    'id' => $assignedReferee->id,
                    'name' => $assignedReferee->first_name . ' ' . $assignedReferee->last_name,
                    'avatar' => $assignedReferee->avatar ? asset($assignedReferee->avatar) : asset('default/profile.jpg'),
                    'email' => $assignedReferee->email,
                    'phone' => $assignedReferee->phone,
                    'type' => 'individual',
                    'is_me' => $assignedReferee->id === $referee->id, // Flag to identify current user
                ];
            })->values()->toArray();

            // Merge crew members and individual referees
            $allReferees = array_merge($crewMembers, $individualReferees);

            return [
                'game_slot_id' => $gameSlot->id,
                'game_details' => [
                    'date' => $gameSlot->game_date->toDateString(),
                    'start_time' => $gameSlot->start_time,
                    'end_time' => $gameSlot->end_time,
                    'court_name' => $gameSlot->court_name,
                    'court_number' => $gameSlot->court_number,
                    'status' => $gameSlot->status,
                    'is_blocked' => (bool) $gameSlot->is_block,
                ],
                'location' => [
                    'id' => $gameSlot->location->id ?? null,
                    'name' => $gameSlot->location->location_name ?? 'N/A',
                    'latitude' => $gameSlot->location->latitude ?? null,
                    'longitude' => $gameSlot->location->longitude ?? null,
                ],
                'my_position' => $assignment->position,
                'assigned_at' => $assignment->assigned_at->format('Y-m-d H:i:s'),
                'is_auto_assigned' => (bool) $assignment->is_auto_assigned,

                // All referees assigned to this game slot (grouped)
                'assigned_referees' => [
                    'total' => count($allReferees),
                    'referees' => $allReferees,
                ],
            ];
        })->unique('game_slot_id')->values();

        // Calculate statistics
        $totalSlots = $gameSlots->count();
        $upcomingSlots = $gameSlots->filter(function ($slot) {
            return $slot['game_details']['date'] >= now()->toDateString();
        })->count();
        $completedSlots = $gameSlots->filter(function ($slot) {
            return $slot['game_details']['status'] === 'completed';
        })->count();

        return $this->success(
            'Your assigned game slots for this camp retrieved successfully.',
            [
                'camp' => [
                    'id' => $camp->id,
                    'name' => $camp->camp_name,
                    'logo' => $camp->camp_logo ? asset($camp->camp_logo) : asset('default/no_image.webp'),
                    'location' => $camp->location,
                    'start_date' => $camp->start_date->toDateString(),
                    'end_date' => $camp->end_date->toDateString(),
                    'timezone' => $camp->timezone,
                ],
                'statistics' => [
                    'total_assigned_slots' => $totalSlots,
                    'upcoming_slots' => $upcomingSlots,
                    'completed_slots' => $completedSlots,
                ],
                'game_slots' => $gameSlots,
                'pagination' => [
                    'total' => $assignments->total(),
                    'per_page' => $assignments->perPage(),
                    'current_page' => $assignments->currentPage(),
                    'last_page' => $assignments->lastPage(),
                ],
            ]
        );
    }

    /**
     * Get all game slots where the authenticated referee is assigned
     */
    public function getMyAssignedSlots(Request $request)
    {
        $referee = auth('api')->user();

        // Only referees can access this endpoint
        if (!$referee->hasRole('referee')) {
            return $this->error('Only referees can access this endpoint.', null, 403);
        }

        // Get all game slot assignments where this referee is assigned
        $assignments = GameSlotAssignment::with([
            'gameSlot.location',
            'gameSlot.schedule.camp',
            'gameSlot.slotAssignments.assignable' // Load all assignments for each slot
        ])
            ->where('assignable_type', User::class)
            ->where('assignable_id', $referee->id)
            ->where('assignment_type', 'individual')
            // IMPORTANT LOGIC: only published schedules
            ->whereHas('gameSlot', function ($q) {
                $q->whereHas('schedule', function ($q2) {
                    $q2->where('status', 'published');
                });
            })
            ->orderBy('assigned_at', 'desc')
            ->get();

        if ($assignments->isEmpty()) {
            return $this->success('No game slots assigned to you yet.', [
                'total_assignments' => 0,
                'game_slots' => []
            ]);
        }

        // Group by game slot to avoid duplicates
        $gameSlots = $assignments->map(function ($assignment) use ($referee) {
            $gameSlot = $assignment->gameSlot;
            $camp = $gameSlot->schedule->camp;

            // Get all assignments for this game slot
            $allAssignments = $gameSlot->slotAssignments;

            // Separate crew and individual assignments
            $crewAssignments = $allAssignments->where('assignment_type', 'crew');
            $individualAssignments = $allAssignments->where('assignment_type', 'individual');

            // Build crew members list
            $crewMembers = [];
            if ($crewAssignments->isNotEmpty()) {
                foreach ($crewAssignments as $crewAssignment) {
                    $crew = $crewAssignment->assignable; // Crew model
                    if ($crew && $crew->members) {
                        foreach ($crew->members as $member) {
                            $crewMembers[] = [
                                'id' => $member->id,
                                'name' => $member->first_name . ' ' . $member->last_name,
                                'avatar' => $member->avatar ? asset($member->avatar) : asset('default/profile.jpg'),
                                'email' => $member->email,
                                'phone' => $member->phone,
                                'type' => 'crew_member',
                                'crew_name' => $crew->crew_name ?? 'N/A',
                                'position' => $crewAssignment->position,
                            ];
                        }
                    }
                }
            }

            // Build individual referees list
            $individualReferees = $individualAssignments->map(function ($individualAssignment) use ($referee) {
                $assignedReferee = $individualAssignment->assignable; // User model

                return [
                    'id' => $assignedReferee->id,
                    'name' => $assignedReferee->first_name . ' ' . $assignedReferee->last_name,
                    'avatar' => $assignedReferee->avatar ? asset($assignedReferee->avatar) : asset('default/profile.jpg'),
                    'email' => $assignedReferee->email,
                    'phone' => $assignedReferee->phone,
                    'type' => 'individual',
                    'position' => $individualAssignment->position,
                    'is_me' => $assignedReferee->id === $referee->id, // Flag to identify current user
                ];
            })->values()->toArray();

            // Merge crew members and individual referees
            $allReferees = array_merge($crewMembers, $individualReferees);

            return [
                'game_slot_id' => $gameSlot->id,
                'camp' => [
                    'id' => $camp->id,
                    'name' => $camp->camp_name,
                    'logo' => $camp->camp_logo ? asset($camp->camp_logo) : asset('default/no_image.webp'),
                    'location' => $camp->location,
                    'start_date' => $camp->start_date->toDateString(),
                    'end_date' => $camp->end_date->toDateString(),
                ],
                'game_details' => [
                    'date' => $gameSlot->game_date->toDateString(),
                    'start_time' => $gameSlot->start_time,
                    'end_time' => $gameSlot->end_time,
                    'court_name' => $gameSlot->court_name,
                    'court_number' => $gameSlot->court_number,
                    'status' => $gameSlot->status,
                    'is_blocked' => (bool) $gameSlot->is_block,
                ],
                'location' => [
                    'name' => $gameSlot->location->location_name ?? 'N/A',
                    'latitude' => $gameSlot->location->latitude ?? null,
                    'longitude' => $gameSlot->location->longitude ?? null,
                ],
                'my_position' => $assignment->position,
                'assigned_at' => $assignment->assigned_at->format('Y-m-d H:i:s'),
                'is_auto_assigned' => (bool) $assignment->is_auto_assigned,

                // All referees assigned to this game slot
                'assigned_referees' => [
                    'total' => count($allReferees),
                    'referees' => $allReferees,
                ],
            ];
        })->unique('game_slot_id')->values();

        return $this->success(
            'Your assigned game slots retrieved successfully.',
            [
                'total_assignments' => $gameSlots->count(),
                'game_slots' => $gameSlots,
            ]
        );
    }

    /**
     * Get details of a specific game slot (with all assigned referees)
     */
    public function getGameSlotDetails($gameSlotId)
    {
        $referee = auth('api')->user();

        if (!$referee->hasRole('referee')) {
            return $this->error('Only referees can access this endpoint.', null, 403);
        }

        $gameSlot = GameSlot::with([
            'location',
            'schedule.camp',
            'slotAssignments.assignable'
        ])
            ->find($gameSlotId);

        if (!$gameSlot) {
            return $this->error('Game slot not found.', null, 404);
        }

        // Check if this referee is assigned to this game slot
        $isAssigned = GameSlotAssignment::where('game_slot_id', $gameSlotId)
            ->where('assignable_id', $referee->id)
            ->where('assignable_type', User::class)
            ->exists();

        if (!$isAssigned) {
            return $this->error('You are not assigned to this game slot.', null, 403);
        }

        $camp = $gameSlot->schedule->camp;

        // Get all assignments
        $crewAssignments = $gameSlot->slotAssignments->where('assignment_type', 'crew');
        $individualAssignments = $gameSlot->slotAssignments->where('assignment_type', 'individual');

        // Build crew members list
        $crewMembers = [];
        if ($crewAssignments->isNotEmpty()) {
            foreach ($crewAssignments as $crewAssignment) {
                $crew = $crewAssignment->assignable;
                if ($crew && $crew->members) {
                    foreach ($crew->members as $member) {
                        $crewMembers[] = [
                            'id' => $member->id,
                            'name' => $member->first_name . ' ' . $member->last_name,
                            'avatar' => $member->avatar ? asset($member->avatar) : asset('default/profile.jpg'),
                            'email' => $member->email,
                            'phone' => $member->phone,
                            'type' => 'crew_member',
                            'crew_name' => $crew->crew_name ?? 'N/A',
                            'position' => $crewAssignment->position,
                        ];
                    }
                }
            }
        }

        // Build individual referees list
        $individualReferees = $individualAssignments->map(function ($individualAssignment) use ($referee) {
            $assignedReferee = $individualAssignment->assignable;

            return [
                'id' => $assignedReferee->id,
                'name' => $assignedReferee->first_name . ' ' . $assignedReferee->last_name,
                'avatar' => $assignedReferee->avatar ? asset($assignedReferee->avatar) : asset('default/profile.jpg'),
                'email' => $assignedReferee->email,
                'phone' => $assignedReferee->phone,
                'type' => 'individual',
                'position' => $individualAssignment->position,
                'is_me' => $assignedReferee->id === $referee->id,
            ];
        })->values()->toArray();

        $allReferees = array_merge($crewMembers, $individualReferees);

        $response = [
            'game_slot' => [
                'id' => $gameSlot->id,
                'date' => $gameSlot->game_date->toDateString(),
                'start_time' => $gameSlot->start_time,
                'end_time' => $gameSlot->end_time,
                'court_name' => $gameSlot->court_name,
                'court_number' => $gameSlot->court_number,
                'status' => $gameSlot->status,
                'is_blocked' => (bool) $gameSlot->is_block,
            ],
            'camp' => [
                'id' => $camp->id,
                'name' => $camp->camp_name,
                'logo' => $camp->camp_logo ? asset($camp->camp_logo) : asset('default/no_image.webp'),
                'location' => $camp->location,
                'details' => $camp->camp_details,
            ],
            'location' => [
                'name' => $gameSlot->location->location_name ?? 'N/A',
                'latitude' => $gameSlot->location->latitude ?? null,
                'longitude' => $gameSlot->location->longitude ?? null,
            ],
            'assigned_referees' => [
                'total' => count($allReferees),
                'referees' => $allReferees,
            ],
        ];

        return $this->success(
            'Game slot details retrieved successfully.',
            $response
        );
    }

    /**
     * Get upcoming game slots for the referee
     */
    public function getUpcomingSlots(Request $request)
    {
        $referee = auth('api')->user();

        if (!$referee->hasRole('referee')) {
            return $this->error('Only referees can access this endpoint.', null, 403);
        }

        $today = now()->toDateString();

        $assignments = GameSlotAssignment::with([
            'gameSlot.location',
            'gameSlot.schedule.camp',
            'gameSlot.slotAssignments.assignable'
        ])
            ->where('assignable_type', User::class)
            ->where('assignable_id', $referee->id)
            ->where('assignment_type', 'individual')
            ->whereHas('gameSlot', function ($query) use ($today) {
                $query->where('game_date', '>=', $today)
                    ->where('status', 'assigned');
            })
            ->orderBy('assigned_at', 'asc')
            ->get();

        $upcomingSlots = $assignments->map(function ($assignment) use ($referee) {
            $gameSlot = $assignment->gameSlot;
            $camp = $gameSlot->schedule->camp;

            $allAssignments = $gameSlot->slotAssignments;
            $totalReferees = $allAssignments->where('assignment_type', 'individual')->count();

            return [
                'game_slot_id' => $gameSlot->id,
                'camp_name' => $camp->camp_name,
                'date' => $gameSlot->game_date->toDateString(),
                'time' => $gameSlot->start_time . ' - ' . $gameSlot->end_time,
                'court' => $gameSlot->court_name,
                'location' => $gameSlot->location->location_name ?? 'N/A',
                'my_position' => $assignment->position,
                'total_referees' => $totalReferees,
            ];
        })->unique('game_slot_id')->values();

        return $this->success(
            'Upcoming game slots retrieved successfully.',
            [
                'total_upcoming' => $upcomingSlots->count(),
                'slots' => $upcomingSlots,
            ]
        );
    }

    /**
     * Get previous camp list
     */
    public function getMyCheckins()
    {
        $referee = auth('api')->user();

        $checkins = CampRefereeCheckin::where('referee_id', $referee->id)
            ->with([
                'camp:id,camp_name,location,start_date,end_date,camp_logo,price',
                'payment:id,amount,paid_at'
            ])
            ->latest('checked_in_at')
            ->get();

        $formatted = $checkins->map(function ($checkin) {
            return [
                'checkin_id' => $checkin->id,
                'camp' => [
                    'id' => $checkin->camp->id,
                    'camp_name' => $checkin->camp->camp_name,
                    'location' => $checkin->camp->location,
                    'camp_logo' => $checkin->camp->camp_logo ? asset($checkin->camp->camp_logo) : asset('default/no_image.webp'),
                    'start_date' => $checkin->camp->start_date->toDateString(),
                    'end_date' => $checkin->camp->end_date->toDateString(),
                    'price' => $checkin->camp->price
                ],
                'payment' => $checkin->payment ? [
                    'amount' => $checkin->payment->amount,
                    'paid_at' => $checkin->payment->paid_at->format('Y-m-d H:i:s')
                ] : null,
                'checked_in_at' => $checkin->checked_in_at->format('Y-m-d H:i:s')
            ];
        });

        return $this->success(
            'My check-ins fetched successfully.',
            ['checkin_camps' => $formatted],
            200
        );
    }
}
