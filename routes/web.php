<?php

use App\Http\Controllers\Api\Auth\SocialLoginController;
use App\Http\Controllers\Api\Payment\StripeCallbackController;
use App\Http\Controllers\Api\Payment\StripeWebhookController;
use App\Http\Controllers\Web\Frontend\AffiliateController;
// use App\Http\Controllers\Api\StripeWebhookController as ApiStripeWebhookController;
use App\Http\Controllers\Web\Frontend\HomeController;
use App\Http\Controllers\Web\Frontend\SubscriberController;
use App\Http\Controllers\Web\NotificationController;
use Illuminate\Support\Facades\Route;
// use App\Https\App\Http\Controllers\Api\Gateway\Stripe\StripeWebhookController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/affiliate/{slug}', [AffiliateController::class, 'store'])->name('store');

Route::get('/post', [HomeController::class, 'index'])->name('post.index');
Route::get('/post/show/{slug}', [HomeController::class, 'post'])->name('post.show');

//Social login test routes
Route::get('social-login/{provider}', [SocialLoginController::class, 'RedirectToProvider'])->name('social.login');
Route::get('social-login/{provider}/callback', [SocialLoginController::class, 'HandleProviderCallback']);

Route::post('subscriber/store', [SubscriberController::class, 'store'])->name('subscriber.data.store');



Route::controller(NotificationController::class)->prefix('notification')->name('notification.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('read/single/{id}', 'readSingle')->name('read.single');
    Route::POST('read/all', 'readAll')->name('read.all');
})->middleware('auth');

require __DIR__ . '/auth.php';


//test payment success and cancel page.
route::get('{orderId?}/payment/success', function ($orderId = null) {
    return view('backend.layouts.test-payment.success', compact('orderId'));
})->name('payment.success');

route::get('{orderId?}/payment/cancel', function ($orderId = null) {
    return view('backend.layouts.test-payment.cancel', compact('orderId'));
})->name('payment.cancel');

// Route::post('/webhook/stripe', [ApiStripeWebhookController::class, 'HandlePaymentWebhook']);
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->withoutMiddleware(['auth:api']); // Must exclude auth middleware

Route::prefix('stripe')->name('stripe.')->group(function () {
    Route::get('/success/{id}', [StripeCallbackController::class, 'success'])->name('success');
    Route::get('/refresh/{id}', [StripeCallbackController::class, 'refresh'])->name('refresh');
});


// Route::post('/rental/webhook', [RentedPaymentController::class, 'handleWebhook']);
