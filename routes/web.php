<?php

use App\Http\Controllers\Web\Frontend\CartController;
use App\Http\Controllers\Web\Frontend\HomeController;
use App\Http\Controllers\Web\Frontend\OrderController;
use App\Http\Controllers\Web\Frontend\ProductController;
use App\Http\Controllers\Web\Frontend\ProfileController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/admin/login', function () {
    return view('welcome');
});

// handle web routes

// webiste routes

Route::get('product/{slug}', [ProductController::class, 'index'])->name('website.product.index');

// stock clearance routes
Route::get('product/stock/clearance', [ProductController::class, 'clearance'])->name('website.product.clearance');

Route::get('/search', [ProductController::class, 'search'])->name('website.search');

Route::middleware(['auth:web'])->group(function () {

    // get profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('website.profile.index');
});

// home
Route::get('/', [HomeController::class, 'index'])->name('home');

// for cart
Route::middleware('auth')->group(function () {
    // AJAX Add to Cart
    Route::get('website/cart', [CartController::class, 'index'])->name('website.cart.index');
    Route::post('/website/cart/store', [CartController::class, 'store'])->name('website.cart.store');
    Route::post('/website/cart/update', [CartController::class, 'update'])->name('website.cart.update');
    Route::post('/website/cart/remove', [CartController::class, 'remove'])->name('website.cart.remove');

    Route::post('/website/order/store', [OrderController::class, 'store'])->name('website.order.store');
    Route::get('/order/thank-you', [OrderController::class, 'thankYou'])->name('website.order.thankyou');

});

Route::get('/run-migrate', function () {
    try {
        $output = Artisan::call('migrate:fresh');
        return response()->json([
            'message' => 'Migrations executed.',
            'output'  => nl2br($output),
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'An error occurred while running migrations.',
            'error'   => $e->getMessage(),
        ], 500);
    }
});

Route::get('/run-migrate-fresh', function () {
    try {
        $output = Artisan::call('migrate:fresh', ['--seed' => true]);
        return response()->json([
            'message' => 'Migrations executed.',
            'output'  => nl2br($output),
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'An error occurred while running migrations.',
            'error'   => $e->getMessage(),
        ], 500);
    }
});

// Run composer update
Route::get('/run-composer-update', function () {
    $output = shell_exec('composer update 2>&1');
    return response()->json([
        'message' => 'Composer update command executed.',
        'output'  => nl2br($output),
    ]);
});
// Run optimize:clear
Route::get('/run-optimize-clear', function () {
    $output = Artisan::call('optimize:clear');
    return response()->json([
        'message' => 'Optimize clear command executed.',
        'output'  => nl2br($output),
    ]);
});
// Run db:seed
Route::get('/run-db-seed', function () {
    $output = Artisan::call('db:seed', ['--force' => true]);
    return response()->json([
        'message' => 'Database seeding executed.',
        'output'  => nl2br($output),
    ]);
});
// Run cache:clear
Route::get('/run-cache-clear', function () {
    $output = Artisan::call('cache:clear');
    return response()->json([
        'message' => 'Cache cleared.',
        'output'  => nl2br($output),
    ]);
});
// Run queue:restart
Route::get('/run-queue-restart', function () {
    $output = Artisan::call('queue:restart');
    return response()->json([
        'message' => 'Queue workers restarted.',
        'output'  => nl2br($output),
    ]);
});

// Create storage symbolic link
Route::get('/run-storage-link', function () {
    try {
        $output = Artisan::call('storage:link');
        return response()->json([
            'message' => 'Storage symbolic link created.',
            'output'  => nl2br($output),
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'An error occurred while creating storage symbolic link.',
            'error'   => $e->getMessage(),
        ], 500);
    }
});

require __DIR__ . '/auth.php';
