<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use App\Http\Middleware\IdentifyTenantByHeader;

// 1. LIVEWIRE ADMIN PANEL (Domain Based)
Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
])->prefix('admin')->group(function () {
    Route::get('/', function () {
        return "Welcome to the admin dashboard!";
    });
    // Store owners go to: nike.localhost:8000/admin/dashboard
});
