<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\Auth\UserController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\FirebaseTokenController;
use App\Http\Controllers\Api\Auth\SocialLoginController;
use App\Http\Controllers\Api\Frontend\ContactController;
use App\Http\Controllers\Api\Frontend\SettingsController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Frontend\AnnouncementController;
use App\Http\Controllers\Api\Frontend\CMS\HomePageController;
use App\Http\Controllers\Api\Frontend\NotificationController;
use App\Http\Controllers\Api\Frontend\PrivecyPolicyController;
use App\Http\Controllers\Api\Frontend\Roster\RosterController;
use App\Http\Controllers\Api\Frontend\RefereeEvaluationController;
use App\Http\Controllers\Api\Frontend\Evaluator\EvaluatorController;
use App\Http\Controllers\Api\Frontend\Evaluator\GameOverviewController;
use App\Http\Controllers\Api\Frontend\Referee\EvaluatedRefereeController;
use App\Http\Controllers\Api\Frontend\Referee\RefereeAssignmentController;
use App\Http\Controllers\Api\Frontend\Referee\RefereeAssignmentCrewController;
use App\Http\Controllers\Api\Frontend\CampRanking\CampRankingSettingsController;
use App\Http\Controllers\Api\Frontend\CMS\AboutPageController;
use App\Http\Controllers\Api\Frontend\Evaluator\CampEvaluatorRegistrationController;
use App\Http\Controllers\Api\Frontend\Evaluator\CampEvaluatorRegisterManageForDirectorController;

// health check
Route::get('/health-check', function () {
    return "All Right... 👍";
});


/*
|--------------------------------------------------------------------------
| User Authentication Routes
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'guest:api'], function ($router) {
    //register
    Route::post('/register', [RegisterController::class, 'register']); // done
    Route::post('/verify-email', [RegisterController::class, 'VerifyEmail']); // done
    Route::post('/resend-otp', [RegisterController::class, 'ResendOtp']); // done
    // Route::post('/verify-otp', [RegisterController::class, 'VerifyEmail']); // working

    //login
    Route::post('/login', [LoginController::class, 'login'])->name('api.login'); // done

    //forgot password
    Route::post('/forgot-password', [ResetPasswordController::class, 'forgotPassword']); // done
    Route::post('/forgot-password/resend-otp', [ResetPasswordController::class, 'resendOtp']); // done
    Route::post('/otp-token', [ResetPasswordController::class, 'MakeOtpToken']); // done
    Route::post('/reset-password', [ResetPasswordController::class, 'ResetPassword']); // done

    //social login
    Route::post('/social-login', [SocialLoginController::class, 'SocialLogin']);
});

/*
|--------------------------------------------------------------------------
| User Profile and After Auth Route
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth:api', 'api-otp']], function ($router) {
    Route::get('/refresh-token', [LoginController::class, 'refreshToken']);
    Route::post('/logout', [LogoutController::class, 'logout']); // done
    Route::get('/user-details', [UserController::class, 'me']); // done
    Route::post('/update-profile', [UserController::class, 'updateProfile']); // done
    Route::post('/update-avatar', [UserController::class, 'updateAvatar']); // done
    Route::delete('/delete-profile', [UserController::class, 'destroy']); // done
    Route::post('/change-password', [UserController::class, 'changePassword']); // done
});

/*
|--------------------------------------------------------------------------
| Referee Evaluation API Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api', 'role:director|evaluator,api'])->group(function () {
    Route::prefix('referee/evaluation')->group(function () {
        Route::post('/upsert', [RefereeEvaluationController::class, 'storeOrUpdate']); // working - store evaluation
        Route::delete('/destroy/{id}', [RefereeEvaluationController::class, 'destroy']); // working - delete evaluation
        Route::get('/my-evaluations/{campId}', [RefereeEvaluationController::class, 'getMyEvaluations']); // working - get my evaluations (edit required for permission director)
        Route::get('/camp/{campId}', [RefereeEvaluationController::class, 'getEvaluationsByCamp']); // working - get evaluations by camp
        Route::get('/details/{evaluationId}', [RefereeEvaluationController::class, 'show']); // working - get evaluation details

        // Get referee statistics
        Route::get('/{refereeId}/stats', [RefereeEvaluationController::class, 'getRefereeStats']); // done

        // Get all registered referee
        Route::get('/camp/{campId}/all-registered-in-referees', [RefereeEvaluationController::class, 'getAllRegisteredInReferees']); // done
        Route::get('/camp/{campId}/all-referees', [RefereeEvaluationController::class, 'getAllReferees']); // done
    });

    // Game overview
    Route::get('/evaluator/{campId}/game-overview', [GameOverviewController::class, 'gameOverview']);
});

// Roster camp details
Route::get('/roster/camp/details/{campId}', [RosterController::class, 'campDetails'])->middleware('auth:api', 'role:director|referee|evaluator,api');

// Referee histroy
Route::get('/camp/{campId}/referee/{refereeId}/history', [RefereeEvaluationController::class, 'getRefereeEvaluationHistory'])->middleware('auth:api', 'role:director|referee|evaluator,api');

// Referee histroy
Route::get('/referee/evaluation/camp/{campId}', [RefereeEvaluationController::class, 'getEvaluationsByCamp'])->middleware('auth:api', 'role:director|referee|evaluator,api');

/*
|--------------------------------------------------------------------------
| Referee API Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api', 'role:referee'])->group(function () {
    // Get my evaluations (for logged-in referee)
    Route::get('/referee/my-evaluations/{campId}', [EvaluatedRefereeController::class, 'getRefereeEvaluations']);

    Route::prefix('referee')->group(function () {
        // Get all game slots where referee is assigned
        Route::get('/my-assigned-slots', [RefereeAssignmentController::class, 'getMyAssignedSlots']); // done

        Route::get('/camp/{campId}/assigned-slots', [RefereeAssignmentController::class, 'getCampAssignedSlots']);

        // Get specific game slot details with all assigned referees
        Route::get('/game-slot/{gameSlotId}', [RefereeAssignmentController::class, 'getGameSlotDetails']);

        // Get upcoming game slots only
        Route::get('/upcoming-slots', [RefereeAssignmentController::class, 'getUpcomingSlots']);


        // ===== NEW CREW-RELATED ROUTES =====

        // Get all crews where referee is a member (across all camps)
        Route::get('/my-crews', [RefereeAssignmentCrewController::class, 'getMyCrews']);

        // Get crews for a specific camp where referee is a member
        Route::get('/camp/{campId}/crews', [RefereeAssignmentCrewController::class, 'getCampCrews']);

        // Get specific crew details with all game assignments
        Route::get('/crew/{crewId}/details', [RefereeAssignmentCrewController::class, 'getCrewDetails']);

        // Get upcoming games for all crews where referee is a member
        Route::get('/my-crews/upcoming-games', [RefereeAssignmentCrewController::class, 'getMyCrewUpcomingGames']);
    });
});

/*
|--------------------------------------------------------------------------
| Evaluator API Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api', 'role:evaluator'])->group(function () {
    Route::prefix('/evaluator')->group(function () {
        Route::get('/active-camps', [EvaluatorController::class, 'getActiveCamps']);
    });
});


/*
|--------------------------------------------------------------------------
| EVALUATOR REGISTRATION ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api', 'role:evaluator,api'])->group(function () {
    // Evaluator registers for camps
    Route::prefix('evaluator/camp-registration')->group(function () {
        Route::post('/register', [CampEvaluatorRegistrationController::class, 'register']); // done
        Route::get('/my-registrations', [CampEvaluatorRegistrationController::class, 'myRegistrations']); //done
        Route::delete('/cancel/{registrationId}', [CampEvaluatorRegistrationController::class, 'cancel']); // done
        Route::get('/previous-camps', [CampEvaluatorRegistrationController::class, 'previousCamp']); // done
    });
});

// Director manages evaluator registrations
Route::middleware(['auth:api', 'role:director,api'])->group(function () {
    Route::prefix('director/evaluator-registrations')->group(function () {
        Route::get('/camp/{campId}', [CampEvaluatorRegisterManageForDirectorController::class, 'getCampRegistrations']); //done
        Route::post('/approve/{registrationId}', [CampEvaluatorRegisterManageForDirectorController::class, 'approve']); // done
        Route::post('/reject/{registrationId}', [CampEvaluatorRegisterManageForDirectorController::class, 'reject']); // done
        Route::delete('/remove/{registrationId}', [CampEvaluatorRegisterManageForDirectorController::class, 'removeEvaluator']); // done
    });
});

// Ranking Settings Routes (Only for Directors)
Route::middleware(['auth:api', 'role:director,api'])->group(function () {
    Route::prefix('camp/{campId}/ranking-settings')->group(function () {
        // Get ranking settings for a camp
        Route::get('/', [CampRankingSettingsController::class, 'getRankingSettings']);

        // Update ranking settings for a camp
        Route::put('/', [CampRankingSettingsController::class, 'updateRankingSettings']);

        // Toggle individual evaluator permission
        Route::put(
            '/evaluator/{evaluatorId}/toggle-permission',
            [CampRankingSettingsController::class, 'toggleEvaluatorPermission']
        );
    });
});

// contact from submit
Route::post('/contact-form', [ContactController::class, 'submitContact']);

// get home page cms data
Route::get('/cms/home', [HomePageController::class, 'home']);
Route::get('/cms/about', [AboutPageController::class, 'about']);

// get privacy policy data
Route::get('/privacy-policy', [PrivecyPolicyController::class, 'privecyPolicy']);
Route::get('/terms-and-conditions', [PrivecyPolicyController::class, 'termsAndConditions']);

// get setting data
Route::get('/settings', [SettingsController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Announcement making route
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api'])->group(function () {
    // Making announcement only for director
    Route::post('/director/announcement/store', [AnnouncementController::class, 'store'])->middleware('role:director'); // done
    Route::delete('director/announcements/delete/{id}', [AnnouncementController::class, 'destroy'])->middleware('role:director'); // done
});


// === Unified Notification Routes ===
Route::prefix('notifications')->middleware(['auth:api', 'role:referee|evaluator|director,api'])->group(function () {
    // Get all notifications (with optional type filter)
    Route::get('/', [NotificationController::class, 'index']); // done

    // Get only unread notifications
    Route::get('/unread', [NotificationController::class, 'unread']);

    // Get notification counts by type
    Route::get('/counts', [NotificationController::class, 'counts']); // done

    // Get single notification
    Route::get('/{notificationId}', [NotificationController::class, 'show']); // done

    // Mark single notification as read
    Route::post('/{notificationId}/mark-as-read', [NotificationController::class, 'markAsRead']); //done

    // Mark all as read (with optional type filter)
    Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead']); // done

    // Delete notification
    Route::delete('/delete/{notificationId}', [NotificationController::class, 'destroy']); // done

    // Clear all read notifications
    Route::delete('/clear-read', [NotificationController::class, 'clearRead']); // done
});


/*
|--------------------------------------------------------------------------
| Sinle chatting Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api'])->controller(ChatController::class)->prefix('auth/chat')->group(function () {
    Route::get('/list', 'list'); // working
    Route::post('/send/{receiver_id}', 'send'); // working
    Route::get('/conversation/{receiver_id}', 'conversation'); // working
    Route::get('room/{receiver_id}', 'room');
    Route::get('/search', 'search'); // working
    Route::get('/seen/all/{receiver_id}', 'seenAll'); // working
    Route::get('/seen/single/{chat_id}', 'seenSingle'); // working
    Route::delete('/delete/{receiver_id}', 'deleteChat'); // working
    Route::delete('/delete/chat/messages', 'deleteMessages'); // working
});

/*
# Firebase Notification Route
*/
Route::middleware(['auth:api'])->controller(FirebaseTokenController::class)->prefix('firebase')->group(function () {
    Route::get("test", "test");
    Route::post("token/add", "store");
    Route::post("token/get", "getToken");
    Route::post("token/delete", "deleteToken");
});
