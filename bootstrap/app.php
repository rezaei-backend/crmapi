<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders()
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up'
//        then: function () {
//            Route::middleware('api')
//                ->prefix('api/v1')
//                ->group(base_path('routes/api/api_v1.php'));
//
//            Route::middleware('api')
//                ->prefix('api/v2')
//                ->group(base_path('routes/api/api_v2.php'));
//        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // اگر middleware های سفارشی داشته باشید
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // پیکربندی اکسپشن‌ها
    })
    ->create();
