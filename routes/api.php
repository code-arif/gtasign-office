<?php

use App\Http\Controllers\Api\Auth\Client\ClientAuthController;
use App\Http\Controllers\Api\Auth\Client\UserLanguageController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\SocialLoginController;
use App\Http\Controllers\Api\Auth\UserController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\FirebaseTokenController;
use App\Http\Controllers\Api\Frontend\CMS\AboutPageController;
use App\Http\Controllers\Api\Frontend\CMS\HomePageController;
use App\Http\Controllers\Api\Frontend\ContactController;
use App\Http\Controllers\Api\Frontend\NotificationController;
use App\Http\Controllers\Api\Frontend\PrivecyPolicyController;
use App\Http\Controllers\Api\Frontend\SettingsController;
use App\Http\Controllers\Api\Gig\GigCategoryController;
use App\Http\Controllers\Api\Gig\GigController;
use App\Http\Controllers\Api\Gig\GigTagController;
use App\Http\Controllers\Api\Inbox\CustomOfferController;
use App\Http\Controllers\Api\Inbox\InboxController;
use App\Http\Controllers\Api\Inbox\InboxOrderController;
use App\Http\Controllers\Api\Payment\PaymentController;
use App\Http\Controllers\Api\Payment\StripeConnectController;
use App\Http\Controllers\Api\User\Profile\CertificateController;
use App\Http\Controllers\Api\User\Profile\EducationController;
use App\Http\Controllers\Api\User\Profile\UserAvailabilityController;
use App\Http\Controllers\Api\User\Profile\UserExperienceController;
use Illuminate\Support\Facades\Route;

// health check
Route::get('/health-check', function () {
    return response()->json([
        'status' => "OK",
        'Message' => "Project is ready to serve",
    ], 200);
});


/*
|--------------------------------------------------------------------------
| User Authentication Routes
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'guest:api', 'prefix' => 'v1'], function ($router) {
    //register
    Route::post('/register', [RegisterController::class, 'register']); // done
    Route::post('/verify-email', [RegisterController::class, 'VerifyEmail']); // done
    Route::post('/resend-otp', [RegisterController::class, 'ResendOtp']); // done

    //login
    Route::post('/login', [LoginController::class, 'login']); // done

    //forgot password
    Route::post('/forgot-password', [ResetPasswordController::class, 'sendOtp']); // done
    Route::post('/verify-otp', [ResetPasswordController::class, 'verifyOtp']); // done
    Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword']); // done

    //social login
    Route::post('/social-login', [SocialLoginController::class, 'SocialLogin']);

    /*
    |--------------------------------------------------------------------------
    | Public route
    |--------------------------------------------------------------------------
    */
    Route::prefix('gigs')->group(function () {
        // Browse active gigs
        Route::get('/', [GigController::class, 'index']); // done
        Route::get('/active', [GigController::class, 'active']);
        Route::get('/search', [GigController::class, 'search']); // done
        Route::get('/details/{id}', [GigController::class, 'show']); // done

        // Track analytics
        Route::post('/{id}/track-click', [GigController::class, 'trackClick']); // done (partial)

        // Gig cageroires
        Route::get('/gig-categories', [GigCategoryController::class, 'index']); // done
        Route::get('/gig-tags', [GigTagController::class, 'index']); // done
    });
});

/*
|--------------------------------------------------------------------------
| User Profile and After Auth Route (Expert role)
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'auth:api', 'prefix' => 'v1'], function ($router) {
    Route::post('/refresh-token', [LoginController::class, 'refreshToken']); // done
    Route::post('/logout', [LogoutController::class, 'logout']); // done

    Route::get('/profile', [UserController::class, 'profile']); // done
    Route::post('/update-profile', [UserController::class, 'updateProfile']); // done
    Route::post('/update-avatar', [UserController::class, 'updateAvatar']); // done
    Route::delete('/delete-profile', [UserController::class, 'destroy']); // done
    Route::post('/change-password', [UserController::class, 'changePassword']); // done
});

/*
|--------------------------------------------------------------------------
| User Profile and After Auth Route (Client role)
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'v1/client', 'middleware' => ['auth:api', 'role:client'],], function () {
    Route::get('/profile', [ClientAuthController::class, 'profile']); // done
    Route::post('/update-profile', [ClientAuthController::class, 'updateProfile']);
    Route::post('/update-overview', [ClientAuthController::class, 'updateOverview']);
    Route::delete('/delete-profile', [ClientAuthController::class, 'destroy']);
    Route::post('/change-password', [ClientAuthController::class, 'changePassword']);

    // Languages Routes
    Route::get('/languages', [UserLanguageController::class, 'languages']);
    Route::post('/add/languages', [UserLanguageController::class, 'updateLanguages']);
    Route::delete('/remove/languages/{language_id}', [UserLanguageController::class, 'removeLanguage']);
});


/*
|--------------------------------------------------------------------------
| Expert Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api', 'role:expert'])->prefix('v1/expert')->group(function () {
    // Education
    Route::group(['prefix' => 'educations'], function () {
        Route::get('/', [EducationController::class, 'index']);
        Route::post('/store', [EducationController::class, 'store']);
        Route::post('/update', [EducationController::class, 'update']);
        Route::delete('/delete/{id}', [EducationController::class, 'destroy']);
    });

    // Certificate
    Route::group(['prefix' => 'certifications'], function () {
        Route::get('/', [CertificateController::class, 'index']);
        Route::post('/store', [CertificateController::class, 'store']);
        Route::post('/update', [CertificateController::class, 'update']);
        Route::delete('/delete/{id}', [CertificateController::class, 'destroy']);
    });

    // Skills and Expertise
    Route::group(['prefix' => 'skills'], function () {
        Route::get('/', [UserExperienceController::class, 'index']);
        Route::post('/store', [UserExperienceController::class, 'store']);
        Route::post('/update', [UserExperienceController::class, 'update']);
        Route::delete('/delete/{id}', [UserExperienceController::class, 'destroy']);
    });

    // Set Availability
    Route::group(['prefix' => 'availability'], function () {
        Route::post('/store', [UserAvailabilityController::class, 'store']);
        Route::get('/show', [UserAvailabilityController::class, 'show']);
        Route::delete('/cancel', [UserAvailabilityController::class, 'cancel']);
    });


    /*
    |--------------------------------------------------------------------------
    | Gig API Routes
    |--------------------------------------------------------------------------
    */
    // Protected routes (Expert only)
    Route::group(['prefix' => 'gigs'], function () {
        Route::get('/my/list', [GigController::class, 'myGigs']); // done
        Route::post('/store', [GigController::class, 'store']); // done
        Route::post('/update/{id}', [GigController::class, 'update']); // done
        Route::delete('delete/{id}', [GigController::class, 'destroy']); // done
        // Route::post('/{id}/publish', [GigController::class, 'publish']);
        Route::delete('delete/{id}/image', [GigController::class, 'deleteImage']); // done
        Route::delete('delete/{id}/document', [GigController::class, 'deleteDocument']); // done
    });
});


/*
|--------------------------------------------------------------------------
| Inbox routes (requires authentication)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api'])->prefix('v1/inbox')->group(function () {
    // Main inbox
    Route::get('/', [InboxController::class, 'index']); // DONE: Get inbox list
    Route::get('/conversation/{roomId}', [InboxController::class, 'conversation']); // DONE: Get conversation
    Route::post('/send/{receiverId}', [InboxController::class, 'sendMessage']); // DONE: Send message
    // Route::post('/start/{userId}', [InboxController::class, 'startConversation']); // Start conversation
    Route::post('/{roomId}/mark-read', [InboxController::class, 'markAsRead']); // Mark as read (PROBLEM)

    // Custom offers
    Route::prefix('custom-offers')->group(function () {
        Route::post('/create', [CustomOfferController::class, 'create']); // DONE: Create offer
        Route::post('/{offerId}/withdraw', [CustomOfferController::class, 'withdraw']); // DONE: Withdraw offer

        // Client actions on received offers
        Route::post('/{offerId}/accept', [CustomOfferController::class, 'accept']); // DONE: Accept offer
        Route::post('/{offerId}/reject', [CustomOfferController::class, 'reject']); // DONE: Reject offer
        // Route::get('/room/{roomId}', [CustomOfferController::class, 'roomOffers']); // Room offers
    });

    // Orders
    Route::prefix('orders')->group(function () {
        // Route::post('/create-from-offer/{offerId}', [InboxOrderController::class, 'createFromOffer']); // DONE: Order from offer
        // Route::post('/{orderId}/mark-paid', [InboxOrderController::class, 'markPaid']); // Mark as paid


        Route::post('/{orderId}/request-revision', [InboxOrderController::class, 'requestRevision']); // Request revision
        Route::post('/{orderId}/accept-delivery', [InboxOrderController::class, 'acceptDelivery']); // Accept delivery
        Route::get('/{orderId}', [InboxOrderController::class, 'show']); // Order details

        // Extension request & responses
        Route::post('/{orderId}/request-extension', [InboxOrderController::class, 'requestExtension']); // DONE: Request extension
        Route::post('/extensions/{extensionId}/respond', [InboxOrderController::class, 'respondToExtension']); // DONE: Respond to extension
    });
});


/*
|--------------------------------------------------------------------------
| Ordder and payment from gig
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api'])->prefix('v1/order')->group(function () {
    Route::post('/client/create-from-gig/{gigId}', [InboxOrderController::class, 'createFromGig']); // DONE: Order from gig
    Route::post('/client/{orderId}/checkout', [PaymentController::class, 'createCheckout']); // DONE: Mark as paid

    Route::post('/delivery/{orderId}/submit-delivery-to-qa', [InboxOrderController::class, 'submitDelivery']); // DONE: Submit delivery to qa
});

/*
|--------------------------------------------------------------------------
|  PAYMENTS (Client-facing)
|--------------------------------------------------------------------------
*/
Route::prefix('v1/payment')->group(function () {
    // Create checkout session for an order → returns Stripe URL
    // Route::post('/checkout/{orderId}', [PaymentController::class, 'createCheckout']);

    // Verify payment success (called after Stripe redirect)
    Route::post('/verify', [PaymentController::class, 'verifyPayment']);

    // LOCAL TEST ONLY (disabled in production)
    // Route::post('/test/simulate-success/{orderId}', [PaymentController::class, 'simulatePaymentSuccess']);
});

/*
|--------------------------------------------------------------------------
| EXPERT — STRIPE CONNECT + WALLET
|--------------------------------------------------------------------------
*/
Route::prefix('v1/expert')->group(function () {
    // Stripe Connect onboarding
    Route::post('/stripe/connect', [StripeConnectController::class, 'connect']); // DONE: Connect Stripe account
    Route::get('/stripe/status', [StripeConnectController::class, 'status']); // DONE: Check Stripe connection status
    Route::get('/stripe/dashboard', [StripeConnectController::class, 'dashboard']);

    // Wallet & Withdrawals
    Route::get('/wallet', [StripeConnectController::class, 'wallet']);
    Route::post('/wallet/withdraw', [StripeConnectController::class, 'withdraw']);
    Route::get('/wallet/withdrawals', [StripeConnectController::class, 'withdrawalHistory']);
});

// contact from submit
Route::post('v1/contact-form', [ContactController::class, 'submitContact']);

// get home page cms data
Route::get('/cms/home', [HomePageController::class, 'home']);
Route::get('/cms/about', [AboutPageController::class, 'about']);

// get privacy policy data
Route::get('/privacy-policy', [PrivecyPolicyController::class, 'privecyPolicy']);
Route::get('/terms-and-conditions', [PrivecyPolicyController::class, 'termsAndConditions']);

// get setting data
Route::get('/settings', [SettingsController::class, 'index']);




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
