<?php

namespace Modules\Director\Http\Controllers\Api\Court;

use Exception;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Modules\Director\Models\GameSlot;
use Modules\Director\Models\Schedule;
use Modules\Director\Models\ScheduleLocation;
use Modules\Director\Models\GameSlotAssignment;

class CourtManageController extends Controller
{
    use ApiResponse;

    /**
     * Toggle block/unblock a court slot
     * When blocking: removes all assignments (crew + individual)
     */
    public function toggleBlockSlot($slotId)
    {
        $user = auth('api')->user();

        $slot = GameSlot::with('schedule.camp')->find($slotId);

        if (!$slot) {
            return $this->error('Game slot not found!', null, 404);
        }

        if ($slot->schedule->camp->director_id !== $user->id) {
            return $this->error('Unauthorized.', null, 403);
        }

        DB::beginTransaction();
        try {
            $newBlockStatus = !$slot->is_block;

            // If blocking the slot, remove all assignments
            if ($newBlockStatus) {
                $deletedCount = GameSlotAssignment::where('game_slot_id', $slotId)->count();
                GameSlotAssignment::where('game_slot_id', $slotId)->delete();

                // Update slot status to available since assignments are cleared
                $slot->update([
                    'is_block' => $newBlockStatus,
                    'status' => 'available'
                ]);

                DB::commit();

                return $this->success(
                    'Slot blocked successfully. All assignments removed.',
                    [
                        'slot_id' => $slotId,
                        'is_blocked' => $newBlockStatus,
                        'assignments_removed' => $deletedCount
                    ],
                    200
                );
            } else {
                // Just unblock, no assignment changes
                $slot->update(['is_block' => $newBlockStatus]);

                DB::commit();

                return $this->success(
                    'Slot unblocked successfully.',
                    [
                        'slot_id' => $slotId,
                        'is_blocked' => $newBlockStatus
                    ],
                    200
                );
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to toggle block status: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Update court name for a specific game slot
     */
    // public function updateCourtName(Request $request, $slotId)
    // {
    //     $request->validate([
    //         'court_name' => 'required|string|max:255'
    //     ]);

    //     $user = auth('api')->user();

    //     $slot = GameSlot::with('schedule.camp')->find($slotId);

    //     if (!$slot) {
    //         return $this->error('Game slot not found!', null, 404);
    //     }

    //     // Authorization check
    //     if ($slot->schedule->camp->director_id !== $user->id) {
    //         return $this->error('Unauthorized.', null, 403);
    //     }

    //     $slot->update(['court_name' => $request->court_name]);

    //     return $this->success(
    //         'Court name updated successfully.',
    //         [
    //             'slot_id' => $slot->id,
    //             'court_name' => $slot->court_name
    //         ],
    //         200
    //     );
    // }

    /**
     * Update court name - ONLY for specific slot
     */
    // public function updateCourtName(Request $request, $slotId)
    // {
    //     $request->validate([
    //         'court_name' => 'required|string|max:255'
    //     ]);

    //     $user = auth('api')->user();

    //     $slot = GameSlot::with('schedule.camp')->find($slotId);

    //     if (!$slot) {
    //         return $this->error('Game slot not found!', null, 404);
    //     }

    //     // Authorization check
    //     if ($slot->schedule->camp->director_id !== $user->id) {
    //         return $this->error('Unauthorized.', null, 403);
    //     }

    //     // Update ONLY this specific slot
    //     $slot->court_name = $request->court_name;
    //     $slot->save();

    //     return $this->success(
    //         'Court name updated successfully.',
    //         [
    //             'slot_id' => $slot->id,
    //             'court_name' => $slot->court_name,
    //             'location' => $slot->location->location_name,
    //             'court_number' => $slot->court_number
    //         ],
    //         200
    //     );
    // }


    /**
     * Update court name for a location
     * This will update all game slots with the old court name to the new court name
     */
    public function updateCourtName(Request $request, $locationId, $courtNumber)
    {
        $user = auth('api')->user();

        $request->validate([
            'new_court_name' => 'required|string|max:100'
        ]);

        // Find the location
        $location = ScheduleLocation::with('schedule.camp')->find($locationId);

        if (!$location) {
            return $this->error('Location not found.', null, 404);
        }

        // Authorization check
        if ($location->schedule->camp->director_id !== $user->id) {
            return $this->error('Unauthorized.', null, 403);
        }

        // Validate court number
        if ($courtNumber < 1 || $courtNumber > $location->court_count) {
            return $this->error(
                "Invalid court number. Location has {$location->court_count} courts.",
                null,
                400
            );
        }

        $oldCourtName = "Court {$courtNumber}";
        $newCourtName = $request->new_court_name;

        DB::beginTransaction();
        try {
            // Update all game slots for this location and court number
            $updated = GameSlot::where('schedule_location_id', $locationId)
                ->where('court_number', $courtNumber)
                ->update(['court_name' => $newCourtName]);

            DB::commit();

            return $this->success(
                'Court name updated successfully.',
                [
                    'location_id' => $locationId,
                    'court_number' => $courtNumber,
                    'old_court_name' => $oldCourtName,
                    'new_court_name' => $newCourtName,
                    'slots_updated' => $updated
                ],
                200
            );
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error('Failed to update court name: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Update court name for a specific location and court number
     * This updates ALL time slots for that specific court across all dates
     * Same as updateCourtName but with different URL structure
     */
    public function bulkUpdateCourtNames(Request $request, $locationId)
    {
        $user = auth('api')->user();

        $request->validate([
            'court_number' => 'required|integer|min:1',
            'new_court_name' => 'required|string|max:100'
        ]);

        // Find the location
        $location = ScheduleLocation::with('schedule.camp')->find($locationId);

        if (!$location) {
            return $this->error('Location not found.', null, 404);
        }

        // Authorization check
        if ($location->schedule->camp->director_id !== $user->id) {
            return $this->error('Unauthorized.', null, 403);
        }

        // Validate court number
        if ($request->court_number > $location->court_count) {
            return $this->error(
                "Invalid court number. Location has {$location->court_count} courts.",
                null,
                400
            );
        }

        // Get old court name (from first slot)
        $oldCourtName = GameSlot::where('schedule_location_id', $locationId)
            ->where('court_number', $request->court_number)
            ->value('court_name') ?? "Court {$request->court_number}";

        DB::beginTransaction();
        try {
            // Update ALL slots for this specific court
            $updated = GameSlot::where('schedule_location_id', $locationId)
                ->where('court_number', $request->court_number)
                ->update(['court_name' => $request->new_court_name]);

            DB::commit();

            return $this->success(
                'Court name updated successfully for all time slots.',
                [
                    'location_id' => $locationId,
                    'location_name' => $location->location_name,
                    'court_number' => $request->court_number,
                    'old_court_name' => $oldCourtName,
                    'new_court_name' => $request->new_court_name,
                    'total_slots_updated' => $updated
                ],
                200
            );
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error('Failed to update court name: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get all court names for a location (grouped by court number)
     */
    public function getCourtNames($locationId)
    {
        $user = auth('api')->user();

        // Find the location
        $location = ScheduleLocation::with('schedule.camp')->find($locationId);

        if (!$location) {
            return $this->error('Location not found.', null, 404);
        }

        // Authorization check
        if ($location->schedule->camp->director_id !== $user->id) {
            return $this->error('Unauthorized.', null, 403);
        }

        // Get unique court names for this location
        $courtNames = GameSlot::where('schedule_location_id', $locationId)
            ->select('court_number', 'court_name')
            ->groupBy('court_number', 'court_name')
            ->orderBy('court_number')
            ->get()
            ->map(function ($slot) {
                return [
                    'court_number' => $slot->court_number,
                    'court_name' => $slot->court_name
                ];
            });

        return $this->success(
            'Court names fetched successfully.',
            [
                'location_id' => $locationId,
                'location_name' => $location->location_name,
                'court_count' => $location->court_count,
                'court_names' => $courtNames
            ],
            200
        );
    }

    /**
     * Block/Unblock all courts at a specific time on a specific date
     * When blocking: removes all assignments from affected slots
     */
    public function bulkToggleBlockByTime(Request $request, $scheduleId)
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i:s',
            'action' => 'required|in:block,unblock'
        ]);

        $user = auth('api')->user();

        // Find schedule and verify ownership
        $schedule = Schedule::with('camp')->findOrFail($scheduleId);

        if ($schedule->camp->director_id !== $user->id) {
            return $this->error('Unauthorized.', null, 403);
        }

        // Get all slots matching date and time
        $slots = GameSlot::where('schedule_id', $scheduleId)
            ->where('game_date', $request->date)
            ->where('start_time', $request->start_time)
            ->get();

        if ($slots->isEmpty()) {
            return $this->error('No slots found for the given date and time.', null, 404);
        }

        DB::beginTransaction();
        try {
            // Determine new block status
            $newBlockStatus = ($request->action === 'block');
            $slotIds = $slots->pluck('id');

            $totalAssignmentsRemoved = 0;

            // If blocking, remove all assignments first
            if ($newBlockStatus) {
                $totalAssignmentsRemoved = GameSlotAssignment::whereIn('game_slot_id', $slotIds)
                    ->count();

                GameSlotAssignment::whereIn('game_slot_id', $slotIds)->delete();

                // Update all matching slots (block + set to available)
                $affectedCount = GameSlot::where('schedule_id', $scheduleId)
                    ->where('game_date', $request->date)
                    ->where('start_time', $request->start_time)
                    ->update([
                        'is_block' => $newBlockStatus,
                        'status' => 'available'
                    ]);
            } else {
                // Just unblock, keep existing assignments
                $affectedCount = GameSlot::where('schedule_id', $scheduleId)
                    ->where('game_date', $request->date)
                    ->where('start_time', $request->start_time)
                    ->update(['is_block' => $newBlockStatus]);
            }

            DB::commit();

            $message = $newBlockStatus
                ? "{$affectedCount} court(s) blocked and {$totalAssignmentsRemoved} assignment(s) removed."
                : "{$affectedCount} court(s) unblocked successfully.";

            return $this->success(
                $message,
                [
                    'date' => $request->date,
                    'start_time' => $request->start_time,
                    'affected_count' => $affectedCount,
                    'is_blocked' => $newBlockStatus,
                    'assignments_removed' => $totalAssignmentsRemoved,
                    'slots' => $slots->pluck('court_name')
                ],
                200
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to bulk toggle block: ' . $e->getMessage(), null, 500);
        }
    }
}
