<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use App\Http\Middleware\IdentifyTenantByHeader;
use App\Http\Middleware\SetDefaultTenantParameter;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;

// 1. LIVEWIRE ADMIN PANEL (Domain Based)
Route::group([
    'prefix' => '/{tenant}', // This captures the tenant ID
    'middleware' => [
        'web',
        'auth',
        InitializeTenancyByPath::class, // This initializes the tenant based on the URL
        SetDefaultTenantParameter::class // This sets the default tenant parameter for URL generation
    ],
], function () {

    Route::get('/dashboard', function () {
        return "Welcome to the tenant application! <a href=\"" . route('tenant.dashboard') . "\">Dashboard</a>";
    })->name('tenant.dashboard');
})->where('tenant', '^(?!admin$).*'); // <--- THIS LINE IS THE FIX;
