<?php

use App\Http\Controllers\Api\ResetPasswordController;
use App\Http\Controllers\Api\User\Auth\AuthenticationController;
use App\Http\Controllers\Api\User\Auth\SocialLoginController;
use App\Http\Controllers\Api\User\Auth\UserProfileController;
use App\Http\Controllers\Api\User\Profile\ClientProfileController;
use App\Http\Controllers\Api\Website\UserManageController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Web\Backend\Settings\DynamicPageController;
use App\Http\Controllers\Web\Backend\SplashController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('splash', [SplashController::class, 'Splash']);

Route::get('privacy-policy', [DynamicPageController::class, 'privacyPolicy']);
Route::get('term-conditions', [DynamicPageController::class, 'agreement']);


/*
|--------------------------------------------------------------------------
| Guest Routes (No Auth Required)
|--------------------------------------------------------------------------
*/

Broadcast::routes([
    'middleware' => ['auth:api'], // or 'auth:jwt' depending on guard
]);

Route::group(['middleware' => 'guest:api'], function () {
    // Authentication
    Route::post('/login', [AuthenticationController::class, 'login']);
    Route::post('/register', [AuthenticationController::class, 'register'])->name('api.register');
    Route::post('/register-otp-verify', [AuthenticationController::class, 'RegistrationVerifyOtp']);
    Route::post('/resend-otp', [AuthenticationController::class, 'ResendOtp']);
    // Password Reset
    Route::post('forgot-password', [ResetPasswordController::class, 'forgotPassword']);
    Route::post('/verify-otp', [ResetPasswordController::class, 'VerifyOTP']);
    Route::post('/reset-password/{token}', [ResetPasswordController::class, 'ResetPassword']);
    // Social Login
    Route::post('social/signin/{provider}', [SocialLoginController::class, 'socialSignin']);
});
Route::group(['middleware' => 'auth:api'], function () {
    // profile
    Route::get('/profile', [ClientProfileController::class, 'profile']);
    Route::post('/profile/update', [ClientProfileController::class, 'profileUpdate']);
    Route::post('/profile/change-password', [UserProfileController::class, 'updatePassword']);

    Route::post('/profile/delete', [UserProfileController::class, 'deleteProfile']);
    Route::post('/logout', [AuthenticationController::class, 'logout']);
});

// logout



Route::get('/languages', [LanguageController::class, 'getLangApi']);



/*
|--------------------------------------------------------------------------
| Authenticated Routes (Prefix: auth)
|--------------------------------------------------------------------------
*/

Route::post('/update/role', [AuthenticationController::class, 'updateRole']);





// website route list

// Route::middleware('auth')->prefix('auth')->group(function () {

Route::get('/website/user/details', [UserManageController::class, 'user_info']);
Route::post('/website/user/avatar/update', [UserManageController::class, 'user_avatar_update']);

// job list

Route::get('/website/featured/jobs/list', [UserManageController::class, 'featuredJobList']);
Route::get('/website/recommanded/jobs/list', [UserManageController::class, 'recommadedJobList']);

Route::get('website/company/job/list', [UserManageController::class, 'companyJobList']);

Route::get('website/company/job/applicants/{id}', [UserManageController::class, 'jobApplicantList']);
