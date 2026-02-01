<?php

namespace App\Http\Controllers\Api\Frontend\Referee;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Director\Models\Camp;
use App\Http\Controllers\Controller;
use Modules\Director\Models\{Crew, CrewMember, GameSlot, GameSlotAssignment};

class RefereeAssignmentCrewController extends Controller
{
    use ApiResponse;

    /**
     * Get all crews where the referee is a member (across all camps)
     */
    /**
     * Get all crews where the referee is a member (across all camps)
     */
    public function getMyCrews()
    {
        $referee = auth('api')->user();

        if (!$referee->hasRole('referee')) {
            return $this->error('Only referees can access this endpoint.', null, 403);
        }

        // Get all crews where this referee is a member
        $crewMemberships = CrewMember::with([
            'crew.camp' => function ($q) {
                $q->select('id', 'camp_name', 'camp_logo', 'location', 'start_date', 'end_date', 'status');
            },
            'crew.members' // Load crew members directly (they are User models based on your pivot table)
        ])
            ->where('referee_id', $referee->id)
            ->get();

        if ($crewMemberships->isEmpty()) {
            return $this->success('You are not a member of any crew yet.', [
                'total_crews' => 0,
                'crews' => []
            ]);
        }

        // Format crews with member details and assignment statistics
        $crews = $crewMemberships->map(function ($membership) use ($referee) {
            $crew = $membership->crew;

            // Check if crew exists
            if (!$crew) {
                return null; // Skip if crew is null
            }

            $camp = $crew->camp;

            // Check if camp exists
            if (!$camp) {
                return null; // Skip if camp is null
            }

            // Get all crew assignments (game slots where this crew is assigned)
            $crewAssignments = GameSlotAssignment::with('gameSlot')
                ->where('assignable_type', Crew::class)
                ->where('assignable_id', $crew->id)
                ->where('assignment_type', 'crew')
                ->get();

            // Count upcoming games
            $upcomingGames = $crewAssignments->filter(function ($assignment) {
                return $assignment->gameSlot
                    && $assignment->gameSlot->game_date >= now()->toDateString()
                    && $assignment->gameSlot->status === 'assigned';
            })->count();

            // Count completed games
            $completedGames = $crewAssignments->filter(function ($assignment) {
                return $assignment->gameSlot && $assignment->gameSlot->status === 'completed';
            })->count();

            // Format crew members
            $members = $crew->members->map(function ($member) use ($referee) {
                // $member is directly the User model (referee)
                if (!$member) {
                    return null;
                }

                return [
                    'id' => $member->id,
                    'name' => $member->first_name . ' ' . $member->last_name,
                    'email' => $member->email,
                    'phone' => $member->phone ?? null,
                    'avatar' => $member->avatar ? asset($member->avatar) : asset('default/profile.jpg'),
                    'joined_at' => $member->pivot->joined_at ?? null,
                    'is_me' => $member->id === $referee->id
                ];
            })->filter()->values(); // Remove null values

            return [
                'crew_id' => $crew->id,
                'crew_name' => $crew->name,
                'crew_description' => $crew->description ?? null,
                'crew_status' => $crew->status,
                'joined_at' => $membership->joined_at,
                'camp' => [
                    'id' => $camp->id,
                    'name' => $camp->camp_name,
                    'logo' => $camp->camp_logo ? asset($camp->camp_logo) : asset('default/no_image.webp'),
                    'location' => $camp->location,
                    'start_date' => $camp->start_date,
                    'end_date' => $camp->end_date,
                    'status' => $camp->status
                ],
                'statistics' => [
                    'total_members' => $crew->members->count(),
                    'total_games_assigned' => $crewAssignments->count(),
                    'upcoming_games' => $upcomingGames,
                    'completed_games' => $completedGames
                ],
                'members' => $members
            ];
        })->filter()->values(); // Remove null entries

        return $this->success(
            'Your crew memberships retrieved successfully.',
            [
                'total_crews' => $crews->count(),
                'crews' => $crews
            ]
        );
    }

    /**
     * Get crews for a specific camp where the referee is a member
     */
    public function getCampCrews($campId)
    {
        $referee = auth('api')->user();

        if (!$referee->hasRole('referee')) {
            return $this->error('Only referees can access this endpoint.', null, 403);
        }

        // Check if camp exists
        $camp = Camp::find($campId);
        if (!$camp) {
            return $this->error('Camp not found.', null, 404);
        }

        // Get crews for this camp where referee is a member
        $crewMemberships = CrewMember::with([
            'crew.members.referee' => function ($q) {
                $q->select('id', 'first_name', 'last_name', 'email', 'phone', 'avatar');
            }
        ])
            ->where('referee_id', $referee->id)
            ->whereHas('crew', function ($q) use ($campId) {
                $q->where('camp_id', $campId);
            })
            ->get();

        if ($crewMemberships->isEmpty()) {
            return $this->success(
                'You are not a member of any crew in this camp.',
                [
                    'camp' => [
                        'id' => $camp->id,
                        'name' => $camp->camp_name,
                        'logo' => $camp->camp_logo ? asset($camp->camp_logo) : asset('default/no_image.webp'),
                        'location' => $camp->location,
                        'start_date' => $camp->start_date,
                        'end_date' => $camp->end_date,
                    ],
                    'total_crews' => 0,
                    'crews' => []
                ]
            );
        }

        // Format crews with detailed game assignments
        $crews = $crewMemberships->map(function ($membership) use ($referee, $campId) {
            $crew = $membership->crew;

            // Get all game slot assignments for this crew in this camp
            $crewAssignments = GameSlotAssignment::with([
                'gameSlot.location',
                'gameSlot.schedule'
            ])
                ->where('assignable_type', Crew::class)
                ->where('assignable_id', $crew->id)
                ->where('assignment_type', 'crew')
                ->whereHas('gameSlot.schedule', function ($q) use ($campId) {
                    $q->where('camp_id', $campId);
                })
                ->get();

            // Format game slots
            $gameSlots = $crewAssignments->map(function ($assignment) {
                $slot = $assignment->gameSlot;
                return [
                    'game_slot_id' => $slot->id,
                    'date' => $slot->game_date,
                    'start_time' => $slot->start_time,
                    'end_time' => $slot->end_time,
                    'court_name' => $slot->court_name,
                    'court_number' => $slot->court_number,
                    'location_name' => $slot->location->location_name ?? 'N/A',
                    'status' => $slot->status,
                    'is_blocked' => (bool) $slot->is_block,
                    'assigned_at' => $assignment->assigned_at->format('Y-m-d H:i:s')
                ];
            })->values();



            // Statistics
            $upcomingGames = $crewAssignments->filter(function ($assignment) {
                return $assignment->gameSlot->game_date >= now()->toDateString()
                    && $assignment->gameSlot->status === 'assigned';
            })->count();


            $completedGames = $crewAssignments->filter(function ($assignment) {
                return $assignment->gameSlot->status === 'completed';
            })->count();

            // Format crew members
            // $members = $crew->members->map(function ($member) use ($referee) {
            //     $memberReferee = $member->referee;
            //     return [
            //         'id' => $memberReferee->id,
            //         'name' => $memberReferee->first_name . ' ' . $memberReferee->last_name,
            //         'email' => $memberReferee->email,
            //         'phone' => $memberReferee->phone,
            //         'avatar' => $memberReferee->avatar ? asset($memberReferee->avatar) : asset('default/profile.jpg'),
            //         'joined_at' => $member->joined_at,
            //         'is_me' => $memberReferee->id === $referee->id
            //     ];
            // })->values();

            $members = $crew->members->map(function ($member) use ($referee) {
                // $member is directly the User model (referee)
                if (!$member) {
                    return null;
                }

                return [
                    'id' => $member->id,
                    'name' => $member->first_name . ' ' . $member->last_name,
                    'email' => $member->email,
                    'phone' => $member->phone ?? null,
                    'avatar' => $member->avatar ? asset($member->avatar) : asset('default/profile.jpg'),
                    'joined_at' => $member->pivot->joined_at ?? null,
                    'is_me' => $member->id === $referee->id
                ];
            })->filter()->values(); // Remove null values

            return [
                'crew_id' => $crew->id,
                'crew_name' => $crew->name,
                'crew_description' => $crew->description,
                'crew_status' => $crew->status,
                'joined_at' => $membership->joined_at,
                'statistics' => [
                    'total_members' => $crew->members->count(),
                    'total_games_assigned' => $crewAssignments->count(),
                    'upcoming_games' => $upcomingGames,
                    'completed_games' => $completedGames
                ],
                'members' => $members,
                'assigned_game_slots' => $gameSlots
            ];
        })->values();

        return $this->success(
            'Your crew memberships for this camp retrieved successfully.',
            [
                'camp' => [
                    'id' => $camp->id,
                    'name' => $camp->camp_name,
                    'logo' => $camp->camp_logo ? asset($camp->camp_logo) : asset('default/no_image.webp'),
                    'location' => $camp->location,
                    'start_date' => $camp->start_date,
                    'end_date' => $camp->end_date,
                ],
                'total_crews' => $crews->count(),
                'crews' => $crews
            ]
        );
    }

    /**
     * Get specific crew details with all game assignments
     */
    public function getCrewDetails($crewId)
    {
        $referee = auth('api')->user();

        if (!$referee->hasRole('referee')) {
            return $this->error('Only referees can access this endpoint.', null, 403);
        }

        // Find crew
        $crew = Crew::with([
            'camp',
            'members.referee'
        ])->find($crewId);

        if (!$crew) {
            return $this->error('Crew not found.', null, 404);
        }

        // Check if referee is a member of this crew
        $isMember = $crew->members->contains('referee_id', $referee->id);
        if (!$isMember) {
            return $this->error('You are not a member of this crew.', null, 403);
        }

        // Get all game assignments for this crew
        $crewAssignments = GameSlotAssignment::with([
            'gameSlot.location',
            'gameSlot.schedule'
        ])
            ->where('assignable_type', Crew::class)
            ->where('assignable_id', $crew->id)
            ->where('assignment_type', 'crew')
            ->orderBy('assigned_at', 'desc')
            ->get();

        // Format game slots grouped by date
        $gameSlotsByDate = $crewAssignments->groupBy(function ($assignment) {
            return $assignment->gameSlot->game_date;
        })->map(function ($dateAssignments, $date) {
            return [
                'date' => $date,
                'formatted_date' => \Carbon\Carbon::parse($date)->format('F d, Y'),
                'day' => \Carbon\Carbon::parse($date)->format('l'),
                'games' => $dateAssignments->map(function ($assignment) {
                    $slot = $assignment->gameSlot;
                    return [
                        'game_slot_id' => $slot->id,
                        'start_time' => $slot->start_time,
                        'end_time' => $slot->end_time,
                        'court_name' => $slot->court_name,
                        'court_number' => $slot->court_number,
                        'location_name' => $slot->location->location_name ?? 'N/A',
                        'status' => $slot->status,
                        'assigned_at' => $assignment->assigned_at->format('Y-m-d H:i:s')
                    ];
                })->values()
            ];
        })->values();

        // Statistics
        $upcomingGames = $crewAssignments->filter(function ($assignment) {
            return $assignment->gameSlot->game_date >= now()->toDateString()
                && $assignment->gameSlot->status === 'assigned';
        })->count();

        $completedGames = $crewAssignments->filter(function ($assignment) {
            return $assignment->gameSlot->status === 'completed';
        })->count();

        // Format crew members
        $members = $crew->members->map(function ($member) use ($referee) {
            $memberReferee = $member->referee;
            return [
                'id' => $memberReferee->id,
                'name' => $memberReferee->first_name . ' ' . $memberReferee->last_name,
                'email' => $memberReferee->email,
                'phone' => $memberReferee->phone,
                'avatar' => $memberReferee->avatar ? asset($memberReferee->avatar) : asset('default/profile.jpg'),
                'joined_at' => $member->joined_at,
                'is_me' => $memberReferee->id === $referee->id
            ];
        })->values();

        $camp = $crew->camp;

        return $this->success(
            'Crew details retrieved successfully.',
            [
                'crew' => [
                    'id' => $crew->id,
                    'name' => $crew->name,
                    'description' => $crew->description,
                    'status' => $crew->status,
                    'created_at' => $crew->created_at->format('Y-m-d H:i:s')
                ],
                'camp' => [
                    'id' => $camp->id,
                    'name' => $camp->camp_name,
                    'logo' => $camp->camp_logo ? asset($camp->camp_logo) : asset('default/no_image.webp'),
                    'location' => $camp->location,
                    'start_date' => $camp->start_date,
                    'end_date' => $camp->end_date,
                ],
                'statistics' => [
                    'total_members' => $crew->members->count(),
                    'total_games_assigned' => $crewAssignments->count(),
                    'upcoming_games' => $upcomingGames,
                    'completed_games' => $completedGames
                ],
                'members' => $members,
                'game_slots_by_date' => $gameSlotsByDate
            ]
        );
    }

    /**
     * Get upcoming games for all crews where referee is a member
     */
    public function getMyCrewUpcomingGames()
    {
        $referee = auth('api')->user();

        if (!$referee->hasRole('referee')) {
            return $this->error('Only referees can access this endpoint.', null, 403);
        }

        // Get all crews where referee is a member
        $crewIds = CrewMember::where('referee_id', $referee->id)
            ->pluck('crew_id');

        if ($crewIds->isEmpty()) {
            return $this->success('You are not a member of any crew.', [
                'total_upcoming_games' => 0,
                'games' => []
            ]);
        }

        $today = now()->toDateString();

        // Get upcoming game assignments for these crews
        $upcomingAssignments = GameSlotAssignment::with([
            'gameSlot.location',
            'gameSlot.schedule.camp',
            'assignable' // The crew
        ])
            ->where('assignment_type', 'crew')
            ->where('assignable_type', Crew::class)
            ->whereIn('assignable_id', $crewIds)
            ->whereHas('gameSlot', function ($q) use ($today) {
                $q->where('game_date', '>=', $today)
                    ->where('status', 'assigned');
            })
            ->orderBy('assigned_at', 'asc')
            ->get();

        $games = $upcomingAssignments->map(function ($assignment) {
            $slot = $assignment->gameSlot;
            $camp = $slot->schedule->camp;
            $crew = $assignment->assignable;

            return [
                'game_slot_id' => $slot->id,
                'date' => $slot->game_date,
                'formatted_date' => \Carbon\Carbon::parse($slot->game_date)->format('F d, Y'),
                'day' => \Carbon\Carbon::parse($slot->game_date)->format('l'),
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'court_name' => $slot->court_name,
                'court_number' => $slot->court_number,
                'location_name' => $slot->location->location_name ?? 'N/A',
                'crew' => [
                    'id' => $crew->id,
                    'name' => $crew->name,
                    'member_count' => $crew->members()->count()
                ],
                'camp' => [
                    'id' => $camp->id,
                    'name' => $camp->camp_name,
                    'location' => $camp->location
                ]
            ];
        })->values();

        return $this->success(
            'Upcoming crew games retrieved successfully.',
            [
                'total_upcoming_games' => $games->count(),
                'games' => $games
            ]
        );
    }
}
