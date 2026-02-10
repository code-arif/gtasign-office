<?php

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Middleware\ApiAdminMiddleware;
use App\Http\Middleware\WebAdminMiddleware;
use App\Http\Middleware\ApiCustomerMiddleware;
use App\Http\Middleware\WebAuthCheckMiddleware;
use App\Http\Middleware\WebDeveloperMiddleware;
use Illuminate\Session\Middleware\StartSession;
use Spatie\Permission\Middleware\RoleMiddleware;
use App\Http\Middleware\ApiOtpVerifiedMiddleware;
use App\Http\Middleware\WebOtpVerifiedMiddleware;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
        then: function () {
            Route::middleware(['web', 'web-developer'])->prefix('developer')->name('developer.')->group(base_path('routes/web-developer.php'));
            Route::middleware(['web', 'web-admin'])->prefix('admin')->name('admin.')->group(base_path('routes/web-admin.php'));
            Route::middleware(['api', 'api-admin'])->prefix('api.admin')->name('api.admin.')->group(base_path('routes/api-admin.php'));
            Route::middleware(['api', 'otp', 'api-customer'])->prefix('api/customer')->name('api.customer.')->group(base_path('routes/api-customer.php'));
            Route::middleware(['api'])->group(base_path('routes/api-stripe.php'));
        }
    )
    ->withBroadcasting(
        __DIR__ . '/../routes/channels.php',
        ['prefix' => 'api', 'middleware' => ['auth:api']],
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'web-developer'         => WebDeveloperMiddleware::class,
            'web-admin'             => WebAdminMiddleware::class,
            'api-admin'             => ApiAdminMiddleware::class,
            'api-customer'          => ApiCustomerMiddleware::class,
            'api-otp'               => ApiOtpVerifiedMiddleware::class,
            'web-otp'               => WebOtpVerifiedMiddleware::class,
            'check'                 => WebAuthCheckMiddleware::class,
            'role'                  => RoleMiddleware::class,
            'permission'            => PermissionMiddleware::class,
            'role_or_permission'    => RoleOrPermissionMiddleware::class
        ]);
        $middleware->validateCsrfTokens(except: [
            'http://localhost:5174/*',
            'http://localhost:5174/',
            'http://localhost:5174',
            'http://localhost:5173/*',
            'http://localhost:5173/',
            'http://localhost:5173',
            'https://gtasign.thewarriors.team/api',
            'https://gtasign.thewarriors.team/api/',
            'https://gtasign.thewarriors.team/api/*',
            '*',
        ]);
        $middleware->api([
            StartSession::class,
        ]);
    })
    // ->withSchedule(function (Schedule $schedule) {
    //     // $schedule->command('app:send-emails')->everySecond();
    //     $schedule->command('notifications:send-special-date')->daily();
    //     $schedule->command('app:partnertrashdelete')->daily();
    // })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (UnauthorizedException $e, Request $request): JsonResponse {

            return response()->json([
                'status'  => false,
                'message' => 'You are not authorized to access this resource.',
                'data'    => null,
                'errors'  => [
                    'role' => 'User does not have the required role.',
                ],
            ], 403);
        });
    })->create();
