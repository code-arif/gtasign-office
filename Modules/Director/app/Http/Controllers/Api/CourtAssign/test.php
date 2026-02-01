<?php

// namespace Modules\Director\Http\Controllers\Api\CourtAssign;

use App\Models\User;
use Carbon\Carbon;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Exception;
use Modules\Director\Models\{
    Camp,
    GameSlot,
    GameSlotAssignment,
    CampRefereeCheckin,
    Crew,
    Schedule
};
use Modules\Director\Transformers\CourtAssign\GameSlotRefereeResource;

class CourtAssignController extends Controller
{
    use ApiResponse;


    /**
     * Assign individual referees to a game slot
     * Max referees per slot based on schedule settings
     * FIXED: Proper success/failure tracking with detailed messages
     */
    public function assignIndividualReferees(Request $request, $slotId)
    {
        $request->validate([
            'referee_ids' => 'required|array',
            'referee_ids.*' => 'exists:users,id',
        ]);

        $user = auth('api')->user();
        $slot = GameSlot::with('schedule.camp')->find($slotId);

        if (!$slot) {
            return $this->error('Game slot not found!', null, 404);
        }

        // Authorization check
        if ($slot->schedule->camp->director_id !== $user->id) {
            return $this->error('Unauthorized.', null, 403);
        }

        // Check if slot already has crew assignment
        $hasCrewAssignment = GameSlotAssignment::where('game_slot_id', $slotId)
            ->where('assignment_type', 'crew')
            ->exists();

        if ($hasCrewAssignment) {
            return $this->error('This slot is already assigned to a crew. Remove crew first.', null, 400);
        }

        // Get current individual assignments
        $currentAssignments = GameSlotAssignment::where('game_slot_id', $slotId)
            ->where('assignment_type', 'individual')
            ->count();

        $maxReferees = $slot->schedule->max_referees_per_slot;
        $availableSlots = $maxReferees - $currentAssignments;

        if ($availableSlots <= 0) {
            return $this->error("This slot already has maximum {$maxReferees} referees.", null, 400);
        }

        // Track results
        $successfulAssignments = [];
        $failedAssignments = [];
        $skippedReferees = [];

        DB::beginTransaction();
        try {
            foreach ($request->referee_ids as $refereeId) {
                // Stop if we've reached the maximum
                if (count($successfulAssignments) >= $availableSlots) {
                    $failedAssignments[] = [
                        'referee_id' => $refereeId,
                        'reason' => 'Maximum slot capacity reached',
                        'can_retry' => false
                    ];
                    continue;
                }

                // Get referee details for better error messages
                $referee = User::find($refereeId);
                $refereeName = $referee ? "{$referee->first_name} {$referee->last_name}" : "Referee #{$refereeId}";

                // Check if referee is checked-in
                $checkedIn = CampRefereeCheckin::where('camp_id', $slot->schedule->camp_id)
                    ->where('referee_id', $refereeId)
                    ->exists();

                if (!$checkedIn) {
                    $failedAssignments[] = [
                        'referee_id' => $refereeId,
                        'referee_name' => $refereeName,
                        'reason' => 'Referee is not checked-in to this camp',
                        'can_retry' => false
                    ];
                    continue;
                }

                // Check if already assigned to this slot
                $alreadyAssigned = GameSlotAssignment::where('game_slot_id', $slotId)
                    ->where('assignable_type', User::class)
                    ->where('assignable_id', $refereeId)
                    ->exists();

                if ($alreadyAssigned) {
                    $skippedReferees[] = [
                        'referee_id' => $refereeId,
                        'referee_name' => $refereeName,
                        'reason' => 'Already assigned to this slot'
                    ];
                    continue;
                }

                // Check if referee needs rest (played in previous slot)
                if (GameSlotAssignment::needsRest($refereeId, User::class, $slot)) {
                    $failedAssignments[] = [
                        'referee_id' => $refereeId,
                        'referee_name' => $refereeName,
                        'reason' => 'Referee needs rest - played in the previous time slot',
                        'can_retry' => false
                    ];
                    continue;
                }

                // Check for time conflicts (overlapping games)
                $hasConflict = GameSlotAssignment::hasTimeConflict(
                    $refereeId,
                    User::class,
                    $slot
                );

                if ($hasConflict) {
                    $conflictingSlots = GameSlotAssignment::getConflictingSlots(
                        $refereeId,
                        User::class,
                        $slot
                    );

                    $conflictDetails = $conflictingSlots->map(function ($assignment) {
                        $conflictSlot = $assignment->gameSlot;
                        return [
                            'court' => $conflictSlot->court_name,
                            'time' => Carbon::parse($conflictSlot->start_time)->format('h:i A') . ' - ' .
                                Carbon::parse($conflictSlot->end_time)->format('h:i A'),
                        ];
                    })->toArray();

                    $failedAssignments[] = [
                        'referee_id' => $refereeId,
                        'referee_name' => $refereeName,
                        'reason' => 'Time conflict with other assignments',
                        'conflicting_slots' => $conflictDetails,
                        'can_retry' => false
                    ];
                    continue;
                }

                // SUCCESS: Create assignment
                GameSlotAssignment::create([
                    'game_slot_id' => $slotId,
                    'assignable_type' => User::class,
                    'assignable_id' => $refereeId,
                    'assignment_type' => 'individual',
                    'is_auto_assigned' => false,
                ]);

                $successfulAssignments[] = [
                    'referee_id' => $refereeId,
                    'referee_name' => $refereeName,
                    'assigned_at' => now()->format('Y-m-d H:i:s')
                ];
            }

            // Update slot status if any assignments were made
            if (count($successfulAssignments) > 0) {
                $slot->update(['status' => 'assigned']);
            }

            DB::commit();

            // Fetch assigned referees with details
            $assignedReferees = $this->getSlotReferees($slotId);

            // Determine response message and status code
            $totalAttempted = count($request->referee_ids);
            $totalSuccessful = count($successfulAssignments);
            $totalFailed = count($failedAssignments);
            $totalSkipped = count($skippedReferees);

            if ($totalSuccessful === 0) {
                // No assignments made at all
                $message = 'No referees could be assigned.';
                $statusCode = 400;
            } elseif ($totalSuccessful === $totalAttempted) {
                // All successful
                $message = "All {$totalSuccessful} referee(s) assigned successfully.";
                $statusCode = 201;
            } else {
                // Partial success
                $message = "{$totalSuccessful} of {$totalAttempted} referee(s) assigned successfully.";
                $statusCode = 207; // Multi-Status
            }

            return response()->json([
                'success' => $totalSuccessful > 0,
                'message' => $message,
                'data' => [
                    'summary' => [
                        'total_attempted' => $totalAttempted,
                        'successful' => $totalSuccessful,
                        'failed' => $totalFailed,
                        'skipped' => $totalSkipped,
                        'remaining_capacity' => $availableSlots - $totalSuccessful
                    ],
                    'successful_assignments' => $successfulAssignments,
                    'failed_assignments' => $failedAssignments,
                    'skipped_assignments' => $skippedReferees,
                    'assigned_referees' => $assignedReferees,
                ],
                'code' => $statusCode
            ], $statusCode);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error('Failed to assign referees: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Assign crew to a game slot
     * Crew replaces any individual assignments
     */
    public function assignCrew(Request $request, $slotId)
    {
        $request->validate([
            'crew_id' => 'required|exists:crews,id',
        ]);

        $user = auth('api')->user();
        $slot = GameSlot::with('schedule.camp')->findOrFail($slotId);

        // Authorization check
        if ($slot->schedule->camp->director_id !== $user->id) {
            return $this->error('Unauthorized.', null, 403);
        }

        $crew = Crew::with('members')->findOrFail($request->crew_id);

        // Verify crew belongs to same camp
        if ($crew->camp_id !== $slot->schedule->camp_id) {
            return $this->error('Crew does not belong to this camp.', null, 400);
        }

        // Use the new time conflict check
        $hasConflict = GameSlotAssignment::hasTimeConflict(
            $crew->id,
            Crew::class,
            $slot
        );

        if ($hasConflict) {
            $conflictingSlots = GameSlotAssignment::getConflictingSlots(
                $crew->id,
                Crew::class,
                $slot
            );

            $conflictDetails = $conflictingSlots->map(function ($assignment) {
                $conflictSlot = $assignment->gameSlot;
                return [
                    'court' => $conflictSlot->court_name,
                    'date' => $conflictSlot->game_date,
                    'time' => Carbon::parse($conflictSlot->start_time)->format('h:i A') . ' - ' .
                        Carbon::parse($conflictSlot->end_time)->format('h:i A'),
                ];
            })->toArray();

            return $this->error(
                'This crew has time conflicts with other assignments.',
                ['conflicting_slots' => $conflictDetails],
                400
            );
        }

        DB::beginTransaction();
        try {
            // Remove all existing individual assignments
            GameSlotAssignment::where('game_slot_id', $slotId)
                ->where('assignment_type', 'individual')
                ->delete();

            // Create crew assignment
            $slot_assign = GameSlotAssignment::create([
                'game_slot_id' => $slotId,
                'assignable_type' => Crew::class,
                'assignable_id' => $crew->id,
                'assignment_type' => 'crew',
                'is_auto_assigned' => false,
            ]);

            // Update slot status
            $slot->update(['status' => 'assigned']);

            DB::commit();

            $crewData = [
                'assignment_id' => $slot_assign->id,
                'crew_id' => $crew->id,
                'crew_name' => $crew->name,
                'members' => $crew->members->map(fn($m) => [
                    'id' => $m->id,
                    'name' => $m->first_name . ' ' . $m->last_name,
                    'avatar' => $m->avatar ? asset($m->avatar) : asset('default/profile.jpg'),
                ])
            ];

            return $this->success(
                'Crew assigned successfully.',
                ['crew' => $crewData],
                201
            );
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error('Failed to assign crew: ' . $e->getMessage(), null, 500);
        }
    }


    /**
     * Auto-assign available referees to all slots
     * Rules:
     * 1. Minimum 1 game rest between games (no back-to-back)
     * 2. Referees will not be assigned to previously assigned court unless no other courts available
     * 3. Referees will be assigned similar number of games (fair distribution)
     */
    public function autoAssignReferees($campId)
    {
        $user = auth('api')->user();

        $camp = Camp::where('id', $campId)
            ->where('director_id', $user->id)
            ->first();

        if (!$camp) {
            return $this->error('Camp not found.', null, 404);
        }

        // Get available slots (not blocked, no crew assignments)
        $availableSlots = GameSlot::with('schedule')
            ->whereHas('schedule', function ($q) use ($campId) {
                $q->where('camp_id', $campId);
            })
            ->where('is_block', false)
            ->whereDoesntHave('slotAssignments', function ($q) {
                $q->where('assignment_type', 'crew');
            })
            ->orderBy('game_date')
            ->orderBy('start_time')
            ->get();

        if ($availableSlots->isEmpty()) {
            return $this->error('No available slots found.', null, 404);
        }

        // Get checked-in referees
        $checkedInReferees = User::whereIn('id', function ($query) use ($campId) {
            $query->select('referee_id')
                ->from('camp_referee_checkins')
                ->where('camp_id', $campId);
        })->get();

        if ($checkedInReferees->isEmpty()) {
            return $this->error('No checked-in referees available.', null, 404);
        }

        // Clear previous auto-assignments
        DB::transaction(function () use ($availableSlots) {
            GameSlotAssignment::whereIn('game_slot_id', $availableSlots->pluck('id'))
                ->where('assignment_type', 'individual')
                ->where('is_auto_assigned', true)
                ->delete();
        });

        // Track assignment counts and court history for each referee
        $refereeStats = [];
        foreach ($checkedInReferees as $referee) {
            $refereeStats[$referee->id] = [
                'referee' => $referee,
                'assignment_count' => 0,
                'assigned_courts' => [], // Track which courts referee has been assigned to
            ];
        }

        $assignmentsCreated = 0;
        $slotsAssigned = 0;
        $conflictCount = 0;
        $restNeededCount = 0;
        $courtPreferenceSkips = 0;

        foreach ($availableSlots as $slot) {
            $assignedToThisSlot = 0;
            $maxPerSlot = $slot->schedule->max_referees_per_slot ?? 3;

            // Sort referees by assignment count (least assigned first) for fair distribution
            uasort($refereeStats, function ($a, $b) {
                return $a['assignment_count'] <=> $b['assignment_count'];
            });

            foreach ($refereeStats as $refereeId => $stats) {
                if ($assignedToThisSlot >= $maxPerSlot) {
                    break;
                }

                $referee = $stats['referee'];

                // Check if already assigned to this slot
                $alreadyAssigned = GameSlotAssignment::where('game_slot_id', $slot->id)
                    ->where('assignable_type', User::class)
                    ->where('assignable_id', $referee->id)
                    ->exists();

                if ($alreadyAssigned) {
                    continue;
                }

                // RULE 1: Check if referee needs rest (played in previous slot)
                if (GameSlotAssignment::needsRest($referee->id, User::class, $slot)) {
                    $restNeededCount++;
                    continue;
                }

                // Check for time conflicts (overlapping games)
                $hasConflict = GameSlotAssignment::hasTimeConflict(
                    $referee->id,
                    User::class,
                    $slot
                );

                if ($hasConflict) {
                    $conflictCount++;
                    continue;
                }

                // RULE 2: Check if referee has been assigned to this court before
                // Only skip if other courts are available (not enforced strictly)
                $hasBeenOnThisCourt = in_array($slot->court_name, $stats['assigned_courts']);

                if ($hasBeenOnThisCourt) {
                    // Check if there are other available referees who haven't been on this court
                    $hasAlternative = false;
                    foreach ($refereeStats as $altRefId => $altStats) {
                        if (
                            $altRefId != $refereeId &&
                            !in_array($slot->court_name, $altStats['assigned_courts']) &&
                            $altStats['assignment_count'] <= $stats['assignment_count']
                        ) {
                            $hasAlternative = true;
                            break;
                        }
                    }

                    // If alternatives exist, skip this referee for this court
                    if ($hasAlternative) {
                        $courtPreferenceSkips++;
                        continue;
                    }
                }

                // Assign referee
                GameSlotAssignment::create([
                    'game_slot_id'     => $slot->id,
                    'assignable_type'  => User::class,
                    'assignable_id'    => $referee->id,
                    'assignment_type'  => 'individual',
                    'is_auto_assigned' => true,
                    'assigned_at'      => now(),
                ]);

                // Update referee stats
                $refereeStats[$refereeId]['assignment_count']++;
                $refereeStats[$refereeId]['assigned_courts'][] = $slot->court_name;

                $assignmentsCreated++;
                $assignedToThisSlot++;
            }

            if ($assignedToThisSlot > 0) {
                $slotsAssigned++;
                $slot->update(['status' => 'assigned']);
            }
        }

        // Calculate distribution fairness
        $assignmentCounts = array_column($refereeStats, 'assignment_count');
        $minAssignments = min($assignmentCounts) ?: 0;
        $maxAssignments = max($assignmentCounts) ?: 0;
        $avgAssignments = $checkedInReferees->count() > 0
            ? round(array_sum($assignmentCounts) / $checkedInReferees->count(), 2)
            : 0;

        $stats = [
            'total_slots'               => $availableSlots->count(),
            'slots_assigned'            => $slotsAssigned,
            'total_referee_assignments' => $assignmentsCreated,
            'total_checked_in_referees' => $checkedInReferees->count(),
            'time_conflicts_avoided'    => $conflictCount,
            'rest_periods_enforced'     => $restNeededCount,
            'court_preference_applied'  => $courtPreferenceSkips,
            'distribution' => [
                'min_assignments_per_referee' => $minAssignments,
                'max_assignments_per_referee' => $maxAssignments,
                'avg_assignments_per_referee' => $avgAssignments,
                'variance' => $maxAssignments - $minAssignments,
            ],
            'average_per_slot' => $slotsAssigned > 0 ? round($assignmentsCreated / $slotsAssigned, 2) : 0,
        ];

        return $this->success(
            'Auto-assignment completed with fair distribution and all rules applied.',
            $stats,
            200
        );
    }

    /**
     * Get all referees for a specific slot with availability status
     * Shows ALL checked-in referees with their current status
     */
    public function getAvailableRefereesForSlot($slotId)
    {
        $user = auth('api')->user();
        $slot = GameSlot::with('schedule.camp')->find($slotId);

        if (!$slot) {
            return $this->error([], 'Game court not found!', 404);
        }

        if ($slot->schedule->camp->director_id !== $user->id) {
            return $this->error([], 'Unauthorized.', 403);
        }

        // Get all checked-in referees
        $allReferees = User::whereIn('id', function ($query) use ($slot) {
            $query->select('referee_id')
                ->from('camp_referee_checkins')
                ->where('camp_id', $slot->schedule->camp_id);
        })->get();

        // Get already assigned referees to this slot
        $assignedRefereeIds = GameSlotAssignment::where('game_slot_id', $slotId)
            ->where('assignment_type', 'individual')
            ->where('assignable_type', User::class)
            ->pluck('assignable_id')
            ->toArray();

        // Prepare referees with availability status
        $refereesWithStatus = $allReferees->map(function ($referee) use ($slot, $assignedRefereeIds) {
            // Check various conditions
            $isAssignedToThisSlot = in_array($referee->id, $assignedRefereeIds);

            $hasTimeConflict = GameSlotAssignment::hasTimeConflict(
                $referee->id,
                User::class,
                $slot
            );

            $needsRest = GameSlotAssignment::needsRest(
                $referee->id,
                User::class,
                $slot
            );

            // Determine status and availability
            $status = 'available';
            $statusMessage = 'Available for assignment';
            $canAssign = true;

            if ($isAssignedToThisSlot) {
                $status = 'assigned_to_this_slot';
                $statusMessage = 'Already assigned to this slot';
                $canAssign = false;
            } elseif ($hasTimeConflict) {
                $status = 'time_conflict';
                $statusMessage = 'Already assigned to another court at this time';
                $canAssign = false;
            } elseif ($needsRest) {
                $status = 'needs_rest';
                $statusMessage = 'Played in previous slot - needs rest';
                $canAssign = false;
            }

            // Get conflicting slot details if exists
            $conflictDetails = null;
            if ($hasTimeConflict) {
                $conflictingAssignments = GameSlotAssignment::getConflictingSlots(
                    $referee->id,
                    User::class,
                    $slot
                );

                if ($conflictingAssignments->isNotEmpty()) {
                    $conflictSlot = $conflictingAssignments->first()->gameSlot;
                    $conflictDetails = [
                        'court_name' => $conflictSlot->court_name,
                        'time' => Carbon::parse($conflictSlot->start_time)->format('h:i A') . ' - ' .
                            Carbon::parse($conflictSlot->end_time)->format('h:i A'),
                    ];
                }
            }

            return [
                'id' => $referee->id,
                'name' => trim("{$referee->first_name} {$referee->last_name}"),
                'email' => $referee->email,
                'avatar' => $referee->avatar ? asset($referee->avatar) : asset('default/profile.jpg'),
                'status' => $status,
                'jourcy_number' => $referee->jourcy_number,
                'status_message' => $statusMessage,
                'can_assign' => $canAssign,
                'conflict_details' => $conflictDetails,
            ];
        });

        // Sort: assigned first, then available, then unavailable
        $sorted = $refereesWithStatus->sort(function ($a, $b) {
            $order = [
                'assigned_to_this_slot' => 1,
                'available' => 2,
                'needs_rest' => 3,
                'time_conflict' => 4,
            ];
            return ($order[$a['status']] ?? 5) <=> ($order[$b['status']] ?? 5);
        })->values();

        // Count by status
        $statusCounts = [
            'available' => $refereesWithStatus->where('status', 'available')->count(),
            'assigned_to_this_slot' => $refereesWithStatus->where('status', 'assigned_to_this_slot')->count(),
            'needs_rest' => $refereesWithStatus->where('status', 'needs_rest')->count(),
            'time_conflict' => $refereesWithStatus->where('status', 'time_conflict')->count(),
        ];

        $response = [
            'slot_info' => [
                'slot_id' => $slot->id,
                'court_name' => $slot->court_name,
                'date' => $slot->game_date,
                'start_time' => Carbon::parse($slot->start_time)->format('h:i A'),
                'end_time' => Carbon::parse($slot->end_time)->format('h:i A'),
            ],
            'total_checked_in' => $allReferees->count(),
            'status_summary' => $statusCounts,
            'referees' => $sorted,
        ];

        return $this->success(
            'Referees with availability status fetched successfully.',
            $response,
            200
        );
    }

    /**
     * Remove assignment (individual referee or entire crew)
     */
    // public function removeAssignment($assignmentId)
    // {
    //     $user = auth('api')->user();

    //     $assignment = GameSlotAssignment::with('gameSlot.schedule.camp')
    //         ->find($assignmentId);

    //     if (!$assignment) {
    //         return $this->error([], 'Assignment not found.', 404);
    //     }

    //     // Authorization check
    //     if ($assignment->gameSlot->schedule->camp->director_id !== $user->id) {
    //         return $this->error('Unauthorized.', null, 403);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         $gameSlot = $assignment->gameSlot;
    //         $assignment->delete();

    //         // Update slot status if no more assignments
    //         $remainingAssignments = GameSlotAssignment::where('game_slot_id', $gameSlot->id)->count();

    //         if ($remainingAssignments === 0) {
    //             $gameSlot->update(['status' => 'available']);
    //         }

    //         DB::commit();

    //         return $this->success('Assignment removed successfully.', null, 200);
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         return $this->error('Failed to remove assignment: ' . $e->getMessage(), null, 500);
    //     }
    // }

    /**
     * Remove assignment (individual referee or entire crew)
     * Sends notification to affected referees
     */
    public function removeAssignment($assignmentId)
    {
        $user = auth('api')->user();

        $assignment = GameSlotAssignment::with([
            'gameSlot.schedule.camp',
            'gameSlot.location',
            'assignable'
        ])->find($assignmentId);

        if (!$assignment) {
            return $this->error('Assignment not found.', null, 404);
        }

        // Authorization check
        if ($assignment->gameSlot->schedule->camp->director_id !== $user->id) {
            return $this->error('Unauthorized.', null, 403);
        }

        DB::beginTransaction();
        try {
            $gameSlot = $assignment->gameSlot;
            $camp = $gameSlot->schedule->camp;

            // Collect referees to notify based on assignment type
            $refereesToNotify = collect();
            $assignmentType = $assignment->assignment_type;
            $crewName = null;

            if ($assignmentType === 'crew') {
                // Crew assignment - notify all crew members
                $crew = $assignment->assignable;
                $crewName = $crew->name;
                $refereesToNotify = $crew->members; // Assuming crew has members relationship

                Log::info('Crew assignment removed', [
                    'director_id' => $user->id,
                    'crew_id' => $crew->id,
                    'crew_name' => $crewName,
                    'slot_id' => $gameSlot->id,
                    'members_count' => $refereesToNotify->count(),
                ]);
            } else {
                // Individual assignment - notify single referee
                $referee = $assignment->assignable;
                $refereesToNotify->push($referee);

                Log::info('Individual referee assignment removed', [
                    'director_id' => $user->id,
                    'referee_id' => $referee->id,
                    'referee_name' => "{$referee->first_name} {$referee->last_name}",
                    'slot_id' => $gameSlot->id,
                ]);
            }

            // Delete the assignment
            $assignment->delete();

            // Update slot status if no more assignments
            $remainingAssignments = GameSlotAssignment::where('game_slot_id', $gameSlot->id)->count();

            if ($remainingAssignments === 0) {
                $gameSlot->update(['status' => 'available']);
            }

            // Send notifications to affected referees
            if ($refereesToNotify->isNotEmpty()) {
                Notification::send(
                    $refereesToNotify,
                    new RefereeRemovedFromCourtNotification(
                        $gameSlot,
                        $camp,
                        $user,
                        $assignmentType,
                        $crewName,
                        'Assignment removed by director' // Optional reason
                    )
                );
            }

            DB::commit();

            $responseData = [
                'assignment_id' => $assignmentId,
                'assignment_type' => $assignmentType,
                'slot_id' => $gameSlot->id,
                'court_name' => $gameSlot->court_name,
                'notifications_sent' => $refereesToNotify->count(),
                'remaining_assignments' => $remainingAssignments,
                'slot_status' => $remainingAssignments === 0 ? 'available' : 'assigned',
            ];

            if ($assignmentType === 'crew') {
                $responseData['crew_name'] = $crewName;
                $responseData['affected_members'] = $refereesToNotify->count();
            } else {
                $responseData['referee_name'] = $refereesToNotify->first()->first_name . ' ' .
                    $refereesToNotify->first()->last_name;
            }

            return $this->success(
                'Assignment removed successfully. Notifications sent to affected referee(s).',
                $responseData,
                200
            );
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to remove assignment', [
                'error' => $e->getMessage(),
                'assignment_id' => $assignmentId,
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->error('Failed to remove assignment: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Clear all assinment for a schedule
     */
    public function clearScheduleAssignments($scheduleId)
    {
        $user = auth('api')->user();

        // Step 1: Find the schedule and verify ownership
        $schedule = Schedule::with('camp')->find($scheduleId); // assume Schedule model exists

        if (!$schedule) {
            return $this->error('Schedule not found.', null, 404);
        }

        if ($schedule->camp->director_id !== $user->id) {
            return $this->error('Unauthorized. You can only clear assignments for your own camp.', null, 403);
        }

        // Step 2: Check if any slots exist
        $slotCount = GameSlot::where('schedule_id', $scheduleId)->count();
        if ($slotCount === 0) {
            return $this->error('No game slots found for this schedule.', null, 404);
        }

        // Step 3: Count current assignments (for response)
        $assignmentCount = GameSlotAssignment::whereHas('gameSlot', function ($q) use ($scheduleId) {
            $q->where('schedule_id', $scheduleId);
        })->count();

        if ($assignmentCount === 0) {
            return $this->success(
                'No assignments to clear. All slots are already available.',
                [
                    'cleared_count' => 0,
                    'schedule_id' => $scheduleId,
                    'slot_count' => $slotCount,
                ],
                200
            );
        }

        // Step 4: Clear assignments in transaction (safe delete)
        DB::beginTransaction();
        try {
            // Delete all assignments for this schedule's slots
            $deleted = GameSlotAssignment::whereHas('gameSlot', function ($q) use ($scheduleId) {
                $q->where('schedule_id', $scheduleId);
            })->delete();

            // Optional: Reset slot statuses to 'available'
            GameSlot::where('schedule_id', $scheduleId)
                ->where('status', 'assigned') // only assigned ones
                ->update(['status' => 'available']);

            DB::commit();

            return $this->success(
                'All assignments cleared successfully.',
                [
                    'cleared_count' => $deleted,
                    'schedule_id' => $scheduleId,
                    'slot_count' => $slotCount,
                    'available_slots' => $slotCount, // now all are available
                ],
                200
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to clear assignments: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get all assignments for a specific slot
     */
    public function getSlotAssignments($slotId)
    {
        $user = auth('api')->user();

        $slot = GameSlot::with('schedule.camp')->findOrFail($slotId);

        if ($slot->schedule->camp->director_id !== $user->id) {
            return $this->error('Unauthorized.', null, 403);
        }

        $assignments = GameSlotAssignment::where('game_slot_id', $slotId)
            ->with(['assignable'])
            ->get()
            ->map(function ($assignment) {
                if ($assignment->assignment_type === 'crew') {
                    return [
                        'assignment_id' => $assignment->id,
                        'type' => 'crew',
                        'crew' => [
                            'id' => $assignment->assignable->id,
                            'name' => $assignment->assignable->name,
                            'members' => $assignment->assignable->members->map(fn($m) => [
                                'id' => $m->id,
                                'name' => $m->first_name . ' ' . $m->last_name,
                                'avatar' => $m->avatar ? asset($m->avatar) : null,
                            ])
                        ],
                        'is_auto_assigned' => $assignment->is_auto_assigned,
                    ];
                } else {
                    return [
                        'assignment_id' => $assignment->id,
                        'type' => 'individual',
                        'referee' => [
                            'id' => $assignment->assignable->id,
                            'name' => $assignment->assignable->first_name . ' ' . $assignment->assignable->last_name,
                            'email' => $assignment->assignable->email,
                            'avatar' => $assignment->assignable->avatar ? asset($assignment->assignable->avatar) : null,
                        ],
                        'is_auto_assigned' => $assignment->is_auto_assigned,
                    ];
                }
            });

        return $this->success(
            'Slot assignments fetched successfully.',
            [
                'slot_id' => $slotId,
                'court_name' => $slot->court_name,
                'total_assignments' => $assignments->count(),
                'assignments' => $assignments
            ],
            200
        );
    }

    /**
     * Get available referees for a camp (not assigned anywhere)
     */
    public function getAvailableReferees($campId)
    {
        $user = auth('api')->user();

        $camp = Camp::where('id', $campId)
            ->where('director_id', $user->id)
            ->first();

        if (!$camp) {
            return $this->error('Camp not found.', null, 404);
        }

        // Get checked-in referees
        $checkedInRefereeIds = CampRefereeCheckin::where('camp_id', $campId)
            ->pluck('referee_id');

        // Get assigned referee IDs (individual assignments only)
        $assignedRefereeIds = GameSlotAssignment::whereHas('gameSlot.schedule', function ($q) use ($campId) {
            $q->where('camp_id', $campId);
        })
            ->where('assignment_type', 'individual')
            ->where('assignable_type', User::class)
            ->pluck('assignable_id')
            ->toArray();

        // Available = checked-in but not assigned
        $availableRefereeIds = $checkedInRefereeIds->diff($assignedRefereeIds);

        $referees = User::whereIn('id', $availableRefereeIds)
            ->select('id', 'first_name', 'last_name', 'email', 'avatar')
            ->get()
            ->map(function ($ref) {
                return [
                    'id' => $ref->id,
                    'name' => $ref->first_name . ' ' . $ref->last_name,
                    'email' => $ref->email,
                    'avatar' => $ref->avatar ? asset($ref->avatar) : asset('default/profile.jpg'),
                ];
            });

        return $this->success(
            'Available referees fetched successfully.',
            [
                'total' => $referees->count(),
                'referees' => $referees
            ],
            200
        );
    }

    /**
     * Get assigned referees for a slot
     */
    public function getAssignedRefereesOrCrew($slotId)
    {
        $user = auth('api')->user();

        $slot = GameSlot::with('schedule.camp')->findOrFail($slotId);

        if ($slot->schedule->camp->director_id !== $user->id) {
            return $this->error('Unauthorized.', null, 403);
        }

        $court = [
            'court_id'   => $slot->id,
            'court_name' => $slot->court_name ?? 'Unknown',
        ];

        // Individual assignments
        $individualAssignments = GameSlotAssignment::with([
            'assignable' => fn($q) => $q->select('id', 'first_name', 'last_name', 'email', 'avatar'),
        ])
            ->where('game_slot_id', $slotId)
            ->where('assignment_type', 'individual')
            ->get();

        // Crew assignments (max 1 expected)
        $crewAssignments = GameSlotAssignment::with([
            'assignable.members.referee' => fn($q) => $q->select('id', 'first_name', 'last_name', 'email', 'avatar'),
        ])
            ->where('game_slot_id', $slotId)
            ->where('assignment_type', 'crew')
            ->get();

        // Prepare referees list
        $referees = $individualAssignments->map(function ($item) {
            $referee = $item->assignable;

            return [
                'assignment_id' => $item->id,
                'referee' => [
                    'referee_id' => $referee->id,
                    'name'       => trim("{$referee->first_name} {$referee->last_name}"),
                    'email'      => $referee->email,
                    'avatar'     => $referee->avatar
                        ? asset($referee->avatar)
                        : asset('default/profile.jpg'),
                ],
            ];
        })->values();

        // Prepare crew (if exists)
        $crewData = null;
        if ($crewAssignments->isNotEmpty()) {
            $crewAssignment = $crewAssignments->first();
            $crew = $crewAssignment->assignable;

            $crewData = [
                'assignment_id' => $crewAssignment->id,
                'crew' => [
                    'crew_id'      => $crew->id,
                    'crew_name'    => $crew->name,
                    'description'  => $crew->description,
                    'member_count' => $crew->members->count(),
                    'members'      => $crew->members->map(function ($member) {
                        $ref = $member->referee ?? $member;
                        return [
                            'referee_id' => $ref->id,
                            'name'       => trim("{$ref->first_name} {$ref->last_name}"),
                            'email'      => $ref->email,
                            'avatar'     => $ref->avatar
                                ? asset($ref->avatar)
                                : asset('default/profile.jpg'),
                        ];
                    })->values(),
                ],
            ];
        }

        // Final structured data
        $responseData = [
            'court'     => $court,
            'referees'  => $referees->isNotEmpty() ? $referees : [],
            'crew'      => $crewData,
        ];

        return $this->success(
            'Assigned referees and crew fetched successfully.',
            $responseData,
            200
        );
    }

    /**
     * Get all crews for a specific slot with availability status
     * Shows ALL crews with their current status (similar to referee availability)
     */
    public function getAvailableCrewsForSlot($slotId)
    {
        $user = auth('api')->user();
        $slot = GameSlot::with('schedule.camp')->find($slotId);

        if (!$slot) {
            return $this->error('Game court not found!', null, 404);
        }

        if ($slot->schedule->camp->director_id !== $user->id) {
            return $this->error('Unauthorized.', null, 403);
        }

        // Get all crews for this camp
        $allCrews = Crew::where('camp_id', $slot->schedule->camp_id)
            ->where('status', 'active')
            ->withCount('members')
            ->with(['members' => function ($query) {
                $query->select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.avatar');
            }])
            ->get();

        // Get already assigned crew to this slot
        $assignedCrewId = GameSlotAssignment::where('game_slot_id', $slotId)
            ->where('assignment_type', 'crew')
            ->where('assignable_type', Crew::class)
            ->value('assignable_id');

        // Prepare crews with availability status
        $crewsWithStatus = $allCrews->map(function ($crew) use ($slot, $assignedCrewId) {
            // Check various conditions
            $isAssignedToThisSlot = ($crew->id === $assignedCrewId);

            $hasTimeConflict = GameSlotAssignment::hasTimeConflict(
                $crew->id,
                Crew::class,
                $slot
            );

            $needsRest = GameSlotAssignment::needsRest(
                $crew->id,
                Crew::class,
                $slot
            );

            // Determine status and availability
            $status = 'available';
            $statusMessage = 'Available for assignment';
            $canAssign = true;

            if ($isAssignedToThisSlot) {
                $status = 'assigned_to_this_slot';
                $statusMessage = 'Already assigned to this slot';
                $canAssign = false;
            } elseif ($hasTimeConflict) {
                $status = 'time_conflict';
                $statusMessage = 'Already assigned to another court at this time';
                $canAssign = false;
            } elseif ($needsRest) {
                $status = 'needs_rest';
                $statusMessage = 'Played in previous slot - needs rest';
                $canAssign = false;
            }

            // Get conflicting slot details if exists
            $conflictDetails = null;
            if ($hasTimeConflict) {
                $conflictingAssignments = GameSlotAssignment::getConflictingSlots(
                    $crew->id,
                    Crew::class,
                    $slot
                );

                if ($conflictingAssignments->isNotEmpty()) {
                    $conflictSlot = $conflictingAssignments->first()->gameSlot;
                    $conflictDetails = [
                        'court_name' => $conflictSlot->court_name,
                        'time' => Carbon::parse($conflictSlot->start_time)->format('h:i A') . ' - ' .
                            Carbon::parse($conflictSlot->end_time)->format('h:i A'),
                    ];
                }
            }

            return [
                'id' => $crew->id,
                'name' => $crew->name,
                'description' => $crew->description,
                'status' => $status,
                'status_message' => $statusMessage,
                'can_assign' => $canAssign,
                'conflict_details' => $conflictDetails,
                'member_count' => $crew->members_count,
                'members' => $crew->members->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->first_name . ' ' . $member->last_name,
                        'email' => $member->email,
                        'avatar' => $member->avatar ? asset($member->avatar) : asset('default/profile.jpg'),
                        'joined_at' => $member->pivot->joined_at
                    ];
                }),
                'created_at' => $crew->created_at->format('Y-m-d H:i:s')
            ];
        });

        // Sort: assigned first, then available, then unavailable
        $sorted = $crewsWithStatus->sort(function ($a, $b) {
            $order = [
                'assigned_to_this_slot' => 1,
                'available' => 2,
                'needs_rest' => 3,
                'time_conflict' => 4,
            ];
            return ($order[$a['status']] ?? 5) <=> ($order[$b['status']] ?? 5);
        })->values();

        // Count by status
        $statusCounts = [
            'available' => $crewsWithStatus->where('status', 'available')->count(),
            'assigned_to_this_slot' => $crewsWithStatus->where('status', 'assigned_to_this_slot')->count(),
            'needs_rest' => $crewsWithStatus->where('status', 'needs_rest')->count(),
            'time_conflict' => $crewsWithStatus->where('status', 'time_conflict')->count(),
        ];

        $response = [
            'slot_info' => [
                'slot_id' => $slot->id,
                'court_name' => $slot->court_name,
                'date' => $slot->game_date,
                'start_time' => Carbon::parse($slot->start_time)->format('h:i A'),
                'end_time' => Carbon::parse($slot->end_time)->format('h:i A'),
            ],
            'total_crews' => $allCrews->count(),
            'status_summary' => $statusCounts,
            'crews' => $sorted,
        ];

        return $this->success(
            'Crews with availability status fetched successfully.',
            $response,
            200
        );
    }

    /**
     * Helper: Get all referees assigned to a slot
     */
    private function getSlotReferees($slotId)
    {
        $assignments = GameSlotAssignment::with('assignable')
            ->where('game_slot_id', $slotId)
            ->where('assignment_type', 'individual')
            ->where('assignable_type', User::class) // or your User model
            ->get();

        return GameSlotRefereeResource::collection($assignments);
    }
}
