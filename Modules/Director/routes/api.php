<?php

use Illuminate\Support\Facades\Route;
use Modules\Director\Http\Controllers\Api\Camp\CampManageController;
use Modules\Director\Http\Controllers\Api\Camp\NoAuthCampController;
use Modules\Director\Http\Controllers\Api\Court\CourtManageController;
use Modules\Director\Http\Controllers\Api\CourtAssign\AutoCourtAssignController;
use Modules\Director\Http\Controllers\Api\Crew\CrewManageController;
use Modules\Director\Http\Controllers\Api\Schedule\ScheduleController;
use Modules\Director\Http\Controllers\Api\CourtAssign\CourtAssignController;
use Modules\Director\Http\Controllers\Api\Referee\RefereeManageController;
use Modules\Director\Http\Controllers\Api\Schedule\RefereeCheckinController;

// ==========================================
// PUBLIC ROUTES (No Auth Required)
// ==========================================
Route::prefix('v1/camp')->group(function () {
    // Camp List & Details (Public)
    Route::get('/sports-type', [NoAuthCampController::class, 'getSportsType']);
    Route::get('/list', [NoAuthCampController::class, 'campList']);
    Route::get('/locations', [NoAuthCampController::class, 'getLocations']);
});


// ==========================================
// DIRECTOR ROUTES (Auth Required)
// ==========================================
Route::middleware(['auth:api', 'role:director'])->prefix('v1')->group(function () {
    // Camp Management
    Route::group([], function () {
        Route::post('/camp/create', [CampManageController::class, 'createCamp']); // done
        Route::post('/camp/update/{id}', [CampManageController::class, 'updateCamp']); // done
        Route::get('/timezones/available', [CampManageController::class, 'getAvailableTimezones']);
        Route::post('/camp/status/{id}', [CampManageController::class, 'updateStatus']); // done
        Route::delete('/camp/delete/{id}', [CampManageController::class, 'deleteCamp']); // done
        Route::get('/director/camp/list', [CampManageController::class, 'directorCampList']); // done
        Route::get('/camp/admin/fee', [CampManageController::class, 'getAdminFee']);
    });

    // Crew Management 
    Route::group([], function () {
        // CRUD Operations
        Route::post('/camp/{campId}/crew/create', [CrewManageController::class, 'createCrew']); // working
        Route::get('/camp/{campId}/crews', [CrewManageController::class, 'getCrews']); // working
        Route::get('/crew/{crewId}', [CrewManageController::class, 'getCrewDetails']); // working- crew details
        Route::post('/crew/update/{crewId}', [CrewManageController::class, 'updateCrew']); // working
        Route::delete('/crew/delete/{crewId}', [CrewManageController::class, 'deleteCrew']); // working

        // Member Management
        Route::post('/crew/{crewId}/add-members', [CrewManageController::class, 'addMembers']);
        Route::delete('/crew/{crewId}/remove-members', [CrewManageController::class, 'removeMembers']);

        // Available Referees
        Route::get('/camp/{campId}/available-referees-for-crew', [CrewManageController::class, 'getAvailableReferees']);
        Route::get('/camp/{campId}/all-checked-in-referees', [CrewManageController::class, 'getAllCheckedInReferees']);
    });

    // Schedule Management
    Route::group([], function () {
        // Get camp date range before creating schedule
        Route::get('camp/{campId}/date-range', [ScheduleController::class, 'getCampDateRange']); // done

        // Create & View Schedule
        Route::post('camp/{campId}/schedule/create', [ScheduleController::class, 'createSchedule']); // done
        Route::get('camp/{campId}/schedule', [ScheduleController::class, 'getSchedule']); // done
        Route::delete('camp/{campId}/schedule', [ScheduleController::class, 'deleteSchedule']); // done

        // Game Slots
        Route::get('camp/{campId}/schedule/game-slots', [ScheduleController::class, 'getGameSlots']); // done

        // Schedule Actions
        Route::post('camp/{campId}/schedule/publish', [ScheduleController::class, 'publishSchedule']); // done
        Route::post('camp/{campId}/schedule/clear', [ScheduleController::class, 'clearSchedule']); // done
    });

    // Court Assignment Management
    Route::prefix('director/court-assign')->group(function () {
        // Individual referee assignment
        Route::post('slot/{slotId}/assign-individual', [CourtAssignController::class, 'assignIndividualReferees']); // working - assign individual referees to a slot

        // Crew assignment
        Route::post('slot/{slotId}/assign-crew', [CourtAssignController::class, 'assignCrew']); // working - assign crew to a slot

        // Auto-assign all slots
        Route::post('camp/{campId}/auto-assign', [AutoCourtAssignController::class, 'autoAssignReferees']); // done

        // Remove assignment (crew or individual)
        Route::delete('assignment/{assignmentId}/remove', [CourtAssignController::class, 'removeAssignment']); // working - remove assignment by ID (crew or individual)

        // Clear game slot assignments for a camp
        Route::delete('/schedules/{scheduleId}/clear-assignments', [CourtAssignController::class, 'clearScheduleAssignments']); // working - clear all assignments for a schedule

        // Get slot assignments
        Route::get('slot/{slotId}/assignments', [CourtAssignController::class, 'getSlotAssignments']); // working - get all assignments for a slot

        // Get available referees
        Route::get('camp/{campId}/available-referees', [CourtAssignController::class, 'getAvailableReferees']); // working - get all available referees for the camp and slot assignments

        // Get assigned referees for a slot
        Route::get('slot/{slotId}/assigned-referees-crew', [CourtAssignController::class, 'getAssignedRefereesOrCrew']); // working - get all assigned referees for the slot

        Route::get('slot/{slotId}/available-referees-with-time-check', [CourtAssignController::class, 'getAvailableRefereesForSlot']);
        Route::get('slot/{slotId}/available-crew-with-time-check', [CourtAssignController::class, 'getAvailableCrewsForSlot']);
    });

    // Court mange routes
    Route::patch('slot/{slotId}/toggle-block', [CourtManageController::class, 'toggleBlockSlot']); // done - Court Block/Unblock
    Route::put('location/{locationId}/court/{courtNumber}/update-name', [CourtManageController::class, 'updateCourtName']);  // done - Change specific court name
    Route::post('location/{locationId}/courts/bulk-update-names', [CourtManageController::class, 'bulkUpdateCourtNames']);  // done - Change specific court name
    Route::get('location/{locationId}/court-names', [CourtManageController::class, 'getCourtNames']);  // done - Get court name
    Route::post('schedule/{scheduleId}/bulk-toggle-block', [CourtManageController::class, 'bulkToggleBlockByTime']); // done - Bulk Block/Unblock by time

    Route::prefix('director')->group(function () {
        // Manual check-in (single referee)
        Route::put('camp/{campId}/referee/{refereeId}/manual-checkin', [RefereeManageController::class, 'directorManualCheckInReferee']);

        // Bulk manual check-in (optional - multiple referees at once)
        Route::post('camp/{campId}/bulk-manual-checkin', [RefereeManageController::class, 'bulkManualCheckIn']);

        // Input jurcy number for a referee
        Route::patch('camp/{campId}/referee/{refereeId}/update-jourcy-number', [RefereeManageController::class, 'updateRefereeJourcyNumber']);

        // Remote referee from camp
        Route::delete('camps/{campId}/referees/{refereeId}', [RefereeManageController::class, 'removeRefereeFromCamp']);
    });
});

/**
 * globar camp details
 */
Route::get('v1/camp/details/{id}', [CampManageController::class, 'campDetails'])->middleware('auth:api', 'role:director|referee|evaluator,api');
Route::get('no-auth/camp/details/{id}', [CampManageController::class, 'noAuthCampDetails']); // no auth camp details

// ==========================================
// REFEREE ROUTES (Auth Required)
// ==========================================
Route::middleware(['auth:api', 'role:referee'])->prefix('v1')->group(function () {
    Route::group(['prefix' => 'referee'], function () {

        // Register for camp (Payment = Registration)
        Route::post('/camp/{campId}/register', [RefereeCheckinController::class, 'registerForCamp']); // working

        // Handle payment success callback (auto-registration)
        Route::post('/camp/payment-success', [RefereeCheckinController::class, 'handlePaymentSuccess']); // working

        // Check-in to camp (physical attendance)
        Route::post('/camp/{campId}/checkin', [RefereeCheckinController::class, 'checkIn']); // working

        // Get my registrations
        Route::get('/camp/my-registrations', [RefereeCheckinController::class, 'getMyRegistrations']); // working

        // Get my checkins camp list
        Route::get('/camp/my-checkins', [RefereeCheckinController::class, 'getMyCheckins']); // working

        // Get active camps
        Route::get('/camp/active-camps', [RefereeCheckinController::class, 'getActiveCamps']); // working

        // Get previous camps
        Route::get('/camp/previous-camps', [RefereeCheckinController::class, 'getPreviousCamps']);
    });
});
