<?php

namespace Modules\Director\Http\Controllers\Api\CourtAssign;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Director\Models\Camp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Modules\Director\Models\GameSlot;
use Modules\Director\Models\GameSlotAssignment;

class AutoCourtAssignController extends Controller
{
    use ApiResponse;

    /**
     * Auto-assign available referees to all slots
     * Rules:
     * 1. Minimum 1 game rest between games (no back-to-back)
     * 2. Court rotation: Referees skip the NEXT occurrence of their last court
     * 3. Fair distribution of games among referees
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
            ->orderBy('court_number')
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

        // Track assignment counts and court rotation for each referee
        $refereeStats = [];
        foreach ($checkedInReferees as $referee) {
            $refereeStats[$referee->id] = [
                'referee' => $referee,
                'assignment_count' => 0,
                'last_assigned_court' => null, // Track last court assigned
                'skip_next_court' => null, // Court to skip on next assignment
                'court_history' => [], // Full court assignment history
            ];
        }

        $assignmentsCreated = 0;
        $slotsAssigned = 0;
        $conflictCount = 0;
        $restNeededCount = 0;
        $courtRotationSkips = 0;

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

                // RULE 2 (FIXED): Court rotation logic
                // If this is the court they should skip, look for alternatives
                if ($stats['skip_next_court'] === $slot->court_name) {
                    // Check if there are other available referees who can take this court
                    $hasAlternative = false;
                    foreach ($refereeStats as $altRefId => $altStats) {
                        if (
                            $altRefId != $refereeId &&
                            $altStats['skip_next_court'] !== $slot->court_name &&
                            $altStats['assignment_count'] <= $stats['assignment_count'] &&
                            !GameSlotAssignment::where('game_slot_id', $slot->id)
                                ->where('assignable_type', User::class)
                                ->where('assignable_id', $altRefId)
                                ->exists()
                        ) {
                            // Check if alternative doesn't need rest and has no conflicts
                            if (
                                !GameSlotAssignment::needsRest($altRefId, User::class, $slot) &&
                                !GameSlotAssignment::hasTimeConflict($altRefId, User::class, $slot)
                            ) {
                                $hasAlternative = true;
                                break;
                            }
                        }
                    }

                    // If alternatives exist, skip this referee for this court
                    if ($hasAlternative) {
                        $courtRotationSkips++;
                        continue;
                    }
                    // If no alternatives, clear the skip flag and allow assignment
                }

                // SUCCESS: Assign referee
                GameSlotAssignment::create([
                    'game_slot_id'     => $slot->id,
                    'assignable_type'  => User::class,
                    'assignable_id'    => $referee->id,
                    'assignment_type'  => 'individual',
                    'is_auto_assigned' => true,
                    'assigned_at'      => now(),
                ]);

                // Update referee stats with court rotation logic
                $refereeStats[$refereeId]['assignment_count']++;
                $refereeStats[$refereeId]['court_history'][] = $slot->court_name;

                // Set skip flag: Skip this court on the NEXT assignment
                $refereeStats[$refereeId]['last_assigned_court'] = $slot->court_name;
                $refereeStats[$refereeId]['skip_next_court'] = $slot->court_name;

                $assignmentsCreated++;
                $assignedToThisSlot++;

                // Clear skip flag after one skip (they can play on this court again after skipping once)
                // This is done in the next iteration when they're assigned to a different court
                if ($stats['last_assigned_court'] !== null && $stats['last_assigned_court'] !== $slot->court_name) {
                    $refereeStats[$refereeId]['skip_next_court'] = $slot->court_name;
                }
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
            'court_rotations_applied'   => $courtRotationSkips, // UPDATED
            'distribution' => [
                'min_assignments_per_referee' => $minAssignments,
                'max_assignments_per_referee' => $maxAssignments,
                'avg_assignments_per_referee' => $avgAssignments,
                'variance' => $maxAssignments - $minAssignments,
            ],
            'average_per_slot' => $slotsAssigned > 0 ? round($assignmentsCreated / $slotsAssigned, 2) : 0,
        ];

        Log::info('Auto-assignment completed', [
            'director_id' => $user->id,
            'camp_id' => $campId,
            'assignments_created' => $assignmentsCreated,
            'slots_assigned' => $slotsAssigned,
            'court_rotations' => $courtRotationSkips,
        ]);

        return $this->success(
            'Auto-assignment completed with fair distribution and court rotation applied.',
            $stats,
            200
        );
    }
}
