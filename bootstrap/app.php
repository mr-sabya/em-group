<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        using: function () {
            // 1. Load Admin Routes FIRST
            // This ensures localhost:8000/admin is matched here 
            // and NOT mistaken for a tenant ID
            Route::middleware('web')
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));

            // 2. Load API Routes
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // 3. Load Web Routes LAST
            // This contains the /{tenant} wildcard
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(remove: [
            \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
        ]);
        $middleware->alias([
            'tenant.api' => \App\Http\Middleware\IdentifyTenantByHeader::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
