<?php

use App\Http\Controllers\Web\Backend\Access\PermissionController;
use App\Http\Controllers\Web\Backend\Access\RoleController;
use App\Http\Controllers\Web\Backend\Access\UserController;
use App\Http\Controllers\Web\Backend\CMS\AboutPageController;
use App\Http\Controllers\Web\Backend\CMS\AboutPageOurTeamController;
use App\Http\Controllers\Web\Backend\CMS\FeaturesController;
use App\Http\Controllers\Web\Backend\CMS\GettingStartedController;
use App\Http\Controllers\Web\Backend\CMS\HomePageController;
use App\Http\Controllers\Web\Backend\CMS\SliderController;
use App\Http\Controllers\Web\Backend\CMS\TestimonialController;
use App\Http\Controllers\Web\Backend\CMS\Web\PrivacyTerms\PrivacAndTermsController;
use App\Http\Controllers\Web\Backend\ContactController;
use App\Http\Controllers\Web\Backend\DashboardController;
use App\Http\Controllers\Web\Backend\FaqController;
use App\Http\Controllers\Web\Backend\Gig\CategoryManageController;
use App\Http\Controllers\Web\Backend\Gig\GigManageController;
use App\Http\Controllers\Web\Backend\Gig\TagManageController;
use App\Http\Controllers\Web\Backend\Order\ExtensionRequestController;
use App\Http\Controllers\Web\Backend\Order\OrderManageController;
use App\Http\Controllers\Web\Backend\Payment\AdminPaymentController;
use App\Http\Controllers\Web\Backend\QA\QaManageController;
use App\Http\Controllers\Web\Backend\Settings\CaptchaController;
use App\Http\Controllers\Web\Backend\Settings\EnvController;
use App\Http\Controllers\Web\Backend\Settings\FirebaseController;
use App\Http\Controllers\Web\Backend\Settings\GoogleMapController;
use App\Http\Controllers\Web\Backend\Settings\LogoController;
use App\Http\Controllers\Web\Backend\Settings\MailSettingController;
use App\Http\Controllers\Web\Backend\Settings\OtherController;
use App\Http\Controllers\Web\Backend\Settings\ProfileController;
use App\Http\Controllers\Web\Backend\Settings\SettingController;
use App\Http\Controllers\Web\Backend\Settings\SignatureController;
use App\Http\Controllers\Web\Backend\Settings\SocialController;
use App\Http\Controllers\Web\Backend\Settings\StripeController;
use App\Http\Controllers\Web\Backend\SubscriberController;
use App\Http\Controllers\Web\Backend\User\ClientManageController;
use App\Http\Controllers\Web\Backend\User\ExpertManageController;
use App\Http\Controllers\Web\Backend\User\LanguageManageController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Dashboard Routes
|--------------------------------------------------------------------------
*/
Route::get("dashboard", [DashboardController::class, 'index'])->name('dashboard');


/*
|--------------------------------------------------------------------------
| Category Management Routes
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'categories', 'as' => 'categories.'], function () {
    Route::get('/', [CategoryManageController::class, 'index'])->name('index');
    Route::post('/store', [CategoryManageController::class, 'store'])->name('store');
    Route::post('/update/{id}', [CategoryManageController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [CategoryManageController::class, 'destroy'])->name('destroy');
    Route::get('/status/{id}', [CategoryManageController::class, 'status'])->name('status');
    Route::get('/get/{id}', [CategoryManageController::class, 'getCategory'])->name('get');
});

/*
|--------------------------------------------------------------------------
| Tag Management Routes
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'tags', 'as' => 'tags.'], function () {
    Route::get('/', [TagManageController::class, 'index'])->name('index');
    Route::post('/store', [TagManageController::class, 'store'])->name('store');
    Route::get('/get/{id}', [TagManageController::class, 'getTag'])->name('get');
    Route::put('/update/{id}', [TagManageController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [TagManageController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Gig Management Routes
|--------------------------------------------------------------------------
*/
Route::prefix('gigs')->name('gigs.')->group(function () {
    Route::get('/', [GigManageController::class, 'index'])->name('index');
    Route::get('/data', [GigManageController::class, 'getData'])->name('get.data');
    Route::get('/{id}', [GigManageController::class, 'show'])->name('show');
    Route::post('/{id}/change-status', [GigManageController::class, 'changeStatus'])->name('change-status');
    Route::get('/categories/{categoryId}/sub-categories', [GigManageController::class, 'getSubCategories'])->name('sub-categories');
    Route::get('/export/csv', [GigManageController::class, 'export'])->name('export');
});

/*
|--------------------------------------------------------------------------
| Language Management Routes
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'languages', 'as' => 'languages.'], function () {
    Route::get('/', [LanguageManageController::class, 'index'])->name('index');
    Route::post('/store', [LanguageManageController::class, 'store'])->name('store');
    Route::get('/get/{id}', [LanguageManageController::class, 'getLanguage'])->name('get');
    Route::put('/update/{id}', [LanguageManageController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [LanguageManageController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| User Management Routes
|--------------------------------------------------------------------------
*/
Route::prefix('experts')->name('experts.')->group(function () {
    Route::get('/', [ExpertManageController::class, 'index'])->name('index');
    Route::get('/data', [ExpertManageController::class, 'getData'])->name('data');
    Route::get('/export', [ExpertManageController::class, 'export'])->name('export');
    Route::get('/{id}', [ExpertManageController::class, 'show'])->name('show');
    Route::patch('/{id}/status', [ExpertManageController::class, 'updateStatus'])->name('update-status');
    Route::delete('/{id}', [ExpertManageController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/restore', [ExpertManageController::class, 'restore'])->name('restore');
    Route::delete('/{id}/force', [ExpertManageController::class, 'forceDelete'])->name('force-delete');

    // Expert sub-pages
    Route::get('/{id}/gigs', [ExpertManageController::class, 'gigs'])->name('gigs'); // PENDING
    Route::get('/{id}/orders', [ExpertManageController::class, 'orders'])->name('orders'); // PENDING
    Route::get('/{id}/earnings', [ExpertManageController::class, 'earnings'])->name('earnings'); // PENDING

    // Level management
    Route::get('{id}/level-form',   [ExpertManageController::class, 'getLevelForm'])->name('level.form');
    Route::patch('{id}/level',      [ExpertManageController::class, 'updateLevel'])->name('level.update');
});

// Clients Management
Route::prefix('clients')->name('clients.')->group(function () {
    Route::get('/', [ClientManageController::class, 'index'])->name('index');
    Route::get('/data', [ClientManageController::class, 'getData'])->name('data');
    Route::get('/export', [ClientManageController::class, 'export'])->name('export');
    Route::get('/{id}', [ClientManageController::class, 'show'])->name('show');
    Route::patch('/{id}/status', [ClientManageController::class, 'updateStatus'])->name('update-status');
    Route::delete('/{id}', [ClientManageController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/restore', [ClientManageController::class, 'restore'])->name('restore');
    Route::delete('/{id}/force', [ClientManageController::class, 'forceDelete'])->name('force-delete');

    // Client sub-pages
    Route::get('/{id}/orders', [ClientManageController::class, 'orders'])->name('orders'); // NOT WORKING, NEEDS FIXING
});

/*
|--------------------------------------------------------------------------
| QA Management Routes
|--------------------------------------------------------------------------
*/
Route::prefix('qa')->name('qa.')->group(function () {
    Route::get('/', [QaManageController::class, 'index'])->name('index');
    Route::get('/data', [QaManageController::class, 'getData'])->name('data');
    Route::get('/{reviewId}', [QaManageController::class, 'show'])->name('show');
    Route::post('/{reviewId}/approve', [QaManageController::class, 'approve'])->name('approve');
    Route::post('/{reviewId}/reject', [QaManageController::class, 'reject'])->name('reject');
});

/*
|--------------------------------------------------------------------------
| Order Management Routes
|--------------------------------------------------------------------------
*/
Route::prefix('orders')->name('orders.')->group(function () {
    Route::get('/', [OrderManageController::class, 'index'])->name('index');
    Route::get('/data', [OrderManageController::class, 'getData'])->name('data');
    Route::get('/export', [OrderManageController::class, 'export'])->name('export');
    Route::get('/{id}', [OrderManageController::class, 'show'])->name('show');
});

/*
|--------------------------------------------------------------------------
| Extenstion Requests Management Routes
|--------------------------------------------------------------------------
*/
Route::prefix('extension-requests')->name('extension-requests.')->group(function () {
    Route::get('/', [ExtensionRequestController::class, 'index'])->name('index');
    Route::get('/data', [ExtensionRequestController::class, 'getData'])->name('data');
    Route::get('/{id}', [ExtensionRequestController::class, 'show'])->name('show');
    Route::patch('/{id}/approve', [ExtensionRequestController::class, 'approve'])->name('approve');
    Route::patch('/{id}/reject', [ExtensionRequestController::class, 'reject'])->name('reject');
});

/*
|--------------------------------------------------------------------------
| Admin Payment Routes
| Add these inside your existing admin middleware group
|--------------------------------------------------------------------------
*/
Route::prefix('payments')->name('payments.')->group(function () {

    // Dashboard
    Route::get('/', [AdminPaymentController::class, 'index'])->name('index');

    // Orders with payment control
    Route::get('/orders', [AdminPaymentController::class, 'orders'])->name('orders');
    Route::get('/orders/{orderId}/details', [AdminPaymentController::class, 'orderDetails'])->name('orders.details');
    Route::post('/orders/{orderId}/release-escrow', [AdminPaymentController::class, 'releaseEscrow'])->name('orders.release-escrow');
    Route::post('/orders/{orderId}/cancel', [AdminPaymentController::class, 'cancelOrder'])->name('orders.cancel');
    Route::post('/orders/{orderId}/force-transfer', [AdminPaymentController::class, 'forceTransfer'])->name('orders.force-transfer');

    // Stripe Accounts
    Route::get('/stripe-accounts', [AdminPaymentController::class, 'stripeAccounts'])->name('stripe-accounts');
    Route::get('/stripe/{userId}/dashboard', [AdminPaymentController::class, 'expertStripeDashboard'])->name('stripe.dashboard');

    // Expert Wallet
    Route::get('/expert/{userId}/wallet', [AdminPaymentController::class, 'expertWallet'])->name('expert.wallet');

    // Withdrawals
    Route::get('/withdrawals', [AdminPaymentController::class, 'withdrawals'])->name('withdrawals');
});

/*
|--------------------------------------------------------------------------
| Contact Management Routes
|--------------------------------------------------------------------------
*/
Route::prefix('contact')->name('contact.')->group(function () {
    Route::get('/', [ContactController::class, 'index'])->name('index');
    Route::get('/status/{id}', [ContactController::class, 'status'])->name('status');
});

/*
|--------------------------------------------------------------------------
| CMS Management Routes
|--------------------------------------------------------------------------
*/
Route::prefix('cms')->name('cms.')->group(function () {
    //Privacy and Terms
    Route::controller(PrivacAndTermsController::class)->prefix('privecyandterms')->name('privecyandterms.')->group(function () {
        Route::get('/terms', 'termsAndCondition')->name('terms');
        Route::get('/privacy', 'privacyPolicy')->name('privacy');

        Route::post('/terms-condition/update', 'termsAndConditionUpdate')->name('terms.update');
        Route::post('/privacy-policy/update', 'privacyPolicyUpdate')->name('privacy.update');
        Route::post('/why-desi-carousel/update', 'whyDesiCarouselUpdate')->name('why.desi.carousel.update');
        Route::post('/trust-and-sefty/update', 'trustAndService')->name('trust-and-sefty.update');
    });

    // home - hero section
    Route::get('/home/hero-section', [HomePageController::class, 'heroIndex'])->name('home.hero.section'); // DONE: Show hero section data
    Route::post('/home/hero-section/update', [HomePageController::class, 'heroUpdate'])->name('home.hero.section.update'); // DONE: Update hero section data

    // home - ai sysmtem section
    Route::get('/home/tags-section', [HomePageController::class, 'tagSectionIndex'])->name('home.tags.section');
    Route::post('/home/tags-section/update', [HomePageController::class, 'tagSectionUpdate'])->name('home.tags.section.update');

    // AI SECURITY SECTION
    Route::get('ai-security', [HomePageController::class, 'aiSecurityIndex'])->name('home.ai-security.index');
    Route::post('ai-security/update', [HomePageController::class, 'aiSecurityUpdate'])->name('home.ai-security.update');

    // ITEMS (Cards)
    Route::get('ai-security/items', [HomePageController::class, 'aiSecurityItems'])->name('home.ai-security.items');
    Route::post('ai-security/item/store', [HomePageController::class, 'aiSecurityItemStore'])->name('home.ai-security.item.store');
    Route::get('ai-security/item/edit/{id}', [HomePageController::class, 'aiSecurityItemEdit'])->name('home.ai-security.item.edit');
    Route::post('ai-security/item/update/{id}', [HomePageController::class, 'aiSecurityItemUpdate'])->name('home.ai-security.item.update');
    Route::delete('ai-security/item/delete/{id}', [HomePageController::class, 'aiSecurityItemDestroy'])->name('home.ai-security.item.destroy');


    // home page feature section
    Route::get('/home/features', [FeaturesController::class, 'index'])->name('home.features.index');
    Route::post('/home/features/store', [FeaturesController::class, 'store'])->name('home.features.store');
    Route::post('/home/features/item/store', [FeaturesController::class, 'storeItem'])->name('home.features.item.store');
    Route::get('/home/features/item/edit/{id}', [FeaturesController::class, 'editItem'])->name('home.features.item.edit');
    Route::post('/home/features/item/update/{id}', [FeaturesController::class, 'updateItem'])->name('home.features.item.update');
    Route::delete('/home/features/item/delete/{id}', [FeaturesController::class, 'destroy'])->name('home.features.item.destroy');

    // home - hero operations
    Route::get('/home/operations', [HomePageController::class, 'operationIndex'])->name('home.operation.section');
    Route::post('/home/operations/update', [HomePageController::class, 'operationUpdate'])->name('home.operation.section.update');

    // home page - testimonial section
    Route::get('/home/testimonial', [TestimonialController::class, 'index'])->name('home.testimonial.index');
    Route::post('/home/testimonial/update', [TestimonialController::class, 'update'])->name('home.testimonial.update');
    Route::post('/reviews/store', [TestimonialController::class, 'storeReview'])->name('home.testimonial.item.store');
    Route::get('/reviews/edit/{id}', [TestimonialController::class, 'editReview'])->name('home.testimonial.item.edit');
    Route::get('/reviews/show/{id}', [TestimonialController::class, 'showReview'])->name('home.testimonial.item.show');
    Route::post('/reviews/update/{id}', [TestimonialController::class, 'updateReview'])->name('home.testimonial.item.update');
    Route::delete('/reviews/delete/{id}', [TestimonialController::class, 'destroyReview'])->name('home.testimonial.item.delete');


    // About Page CMS
    // ================================
    // ABOUT PAGE CMS ROUTES
    // ================================
    Route::prefix('/about')->name('about.')->group(function () {

        // Main Index
        Route::get('/', [AboutPageController::class, 'index'])->name('index');

        // Page Title Section
        Route::post('/page-title/store', [AboutPageController::class, 'storePageTitle'])->name('page-title.store');

        // Mission Section
        Route::post('/mission/store', [AboutPageController::class, 'storeMission'])->name('mission.store');

        // Key to Excellence Section
        Route::post('/key-to-excellence/store', [AboutPageController::class, 'storeKeyToExcellence'])->name('key-to-excellence.store');

        // Bottom Description Section
        Route::post('/bottom-description/store', [AboutPageController::class, 'storeBottomDescription'])->name('bottom-description.store');

        // Owner Info Section
        Route::post('/owner-info/store', [AboutPageController::class, 'storeOwnerInfo'])->name('owner-info.store');

        // Feature Items
        Route::get('/items', [AboutPageController::class, 'items'])->name('items.index');
        Route::post('/items/store', [AboutPageController::class, 'storeItem'])->name('item.store');
        Route::get('/items/{id}/edit', [AboutPageController::class, 'editItem'])->name('item.edit');
        Route::post('/items/{id}/update', [AboutPageController::class, 'updateItem'])->name('item.update');
        Route::delete('/items/{id}/destroy', [AboutPageController::class, 'destroyItem'])->name('item.destroy');


        // ================================
        // OUR TEAM SECTION
        // ================================

        // Team Section Header
        Route::get('/team', [AboutPageOurTeamController::class, 'index'])->name('team.index');
        Route::post('/team-header/store', [AboutPageOurTeamController::class, 'storeTeamHeader'])->name('team-header.store');

        // Team Members CRUD
        Route::get('/team-members', [AboutPageOurTeamController::class, 'teamMembers'])->name('team-members.index');
        Route::post('/team-members/store', [AboutPageOurTeamController::class, 'storeTeamMember'])->name('team-member.store');
        Route::get('/team-members/{id}/edit', [AboutPageOurTeamController::class, 'editTeamMember'])->name('team-member.edit');
        Route::post('/team-members/{id}/update', [AboutPageOurTeamController::class, 'updateTeamMember'])->name('team-member.update');
        Route::delete('/team-members/{id}/destroy', [AboutPageOurTeamController::class, 'destroyTeamMember'])->name('team-member.destroy');


        // ================================
        // OUR TEAM SECTION
        // ================================

        // Team Section Header
        Route::get('/getting-started', [GettingStartedController::class, 'index'])->name('getting-started.index');
        Route::post('/getting-started-header/store', [GettingStartedController::class, 'storePageTitle'])->name('getting-started-header.store');
    });
});


Route::controller(FaqController::class)->prefix('faq')->name('faq.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/store', 'store')->name('store');
    Route::get('/show/{id}', 'show')->name('show');
    Route::get('/edit/{id}', 'edit')->name('edit');
    Route::post('/update/{id}', 'update')->name('update');
    Route::delete('/delete/{id}', 'destroy')->name('destroy');
    Route::get('/status/{id}', 'status')->name('status');
});

Route::get('subscriber', [SubscriberController::class, 'index'])->name('subscriber.index');





/*
* Users Access Route
*/
Route::resource('users', UserController::class);
Route::controller(UserController::class)->prefix('users')->name('users.')->group(function () {
    Route::get('/status/{id}', 'status')->name('status');
    Route::get('/new', 'new')->name('new.index');
    Route::get('/ajax/new/count', 'newCount')->name('ajax.new.count');
    Route::get('/card/{slug}', 'card')->name('card');
});
Route::resource('permissions', PermissionController::class);
Route::resource('roles', RoleController::class);

/*
*settings
*/

//! Route for Profile Settings
Route::controller(ProfileController::class)->group(function () {
    Route::get('setting/profile', 'index')->name('setting.profile.index');
    Route::put('setting/profile/update', 'UpdateProfile')->name('setting.profile.update');
    Route::put('setting/profile/update/Password', 'UpdatePassword')->name('setting.profile.update.Password');
    Route::post('setting/profile/update/avatar', 'UpdateProfilePicture')->name('update.profile.picture');
});

//! Route for Mail Settings
Route::controller(MailSettingController::class)->group(function () {
    Route::get('setting/mail', 'index')->name('setting.mail.index');
    Route::patch('setting/mail', 'update')->name('setting.mail.update');

    Route::post('setting/send', 'send')->name('setting.mail.send');
});

//! Route for Stripe Settings
Route::controller(StripeController::class)->prefix('setting/stripe')->name('setting.stripe.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::patch('/update', 'update')->name('update');
    Route::patch('/update-onboarding', 'updateOnboarding')->name('update-onboarding');
    Route::patch('/update-percentage', 'updateAdminPercentage')->name('update-percentage');
});

//! Route for Firebase Settings
Route::controller(FirebaseController::class)->prefix('setting/firebase')->name('setting.firebase.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::patch('/update', 'update')->name('update');
});

//! Route for Environment Settings
Route::controller(EnvController::class)->group(function () {
    Route::get('setting/env', 'index')->name('setting.env.index');
    Route::patch('setting/env', 'update')->name('setting.env.update');
});

//! Route for Firebase Settings
Route::controller(SocialController::class)->prefix('setting/social')->name('setting.social.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::patch('/update', 'update')->name('update');
});

//! Route for Stripe Settings
Route::controller(SettingController::class)->group(function () {
    Route::get('setting/general', 'index')->name('setting.general.index');
    Route::patch('setting/general', 'update')->name('setting.general.update');
});

//! Route for Logo Settings
Route::controller(LogoController::class)->group(function () {
    Route::get('setting/logo', 'index')->name('setting.logo.index');
    Route::patch('setting/logo', 'update')->name('setting.logo.update');
});

//! Route for Google Map Settings
Route::controller(GoogleMapController::class)->group(function () {
    Route::get('setting/google/map', 'index')->name('setting.google.map.index');
    Route::patch('setting/google/map', 'update')->name('setting.google.map.update');
});

//! Route for Google Map Settings
Route::controller(SignatureController::class)->group(function () {
    Route::get('setting/signature', 'index')->name('setting.signature.index');
    Route::patch('setting/signature', 'update')->name('setting.signature.update');
});

//! Route for Google Map Settings
Route::controller(CaptchaController::class)->group(function () {
    Route::get('setting/captcha', 'index')->name('setting.captcha.index');
    Route::patch('setting/captcha', 'update')->name('setting.captcha.update');
});

//Ajax settings
Route::prefix('setting/other')->name('setting.other')->group(function () {
    Route::get('/', [OtherController::class, 'index'])->name('.index');
    Route::get('/mail', [OtherController::class, 'mail'])->name('.mail');
    Route::get('/sms', [OtherController::class, 'sms'])->name('.sms');
    Route::get('/recaptcha', [OtherController::class, 'recaptcha'])->name('.recaptcha');
    Route::get('/pagination', [OtherController::class, 'pagination'])->name('.pagination');
    Route::get('/reverb', [OtherController::class, 'reverb'])->name('.reverb');
    Route::get('/debug', [OtherController::class, 'debug'])->name('.debug');
    Route::get('/access', [OtherController::class, 'access'])->name('.access');
});



// Run artisan commands for optimization and cache clearing
Route::get('/optimize', function () {
    Artisan::call('optimize:clear');
    Artisan::call('config:cache');
    Redis::flushAll();
    return redirect()->back()->with('t-success', 'Message sent successfully');
})->name('optimize');
