<?php

use App\Http\Middleware\SetDefaultTenantParameter;
use Illuminate\Support\Facades\Route;

use Stancl\Tenancy\Middleware\InitializeTenancyByPath;

// login
Route::get('/login', function () {
    return "Login Page";
})->name('login');


Route::group([
    'prefix' => '/{tenant}', // This captures the tenant ID
    'middleware' => [
        'web',

        InitializeTenancyByPath::class, // This initializes the tenant based on the URL
        SetDefaultTenantParameter::class // This sets the default tenant parameter for URL generation
    ],
], function () {

    Route::get('/dashboard', function () {
        return "Welcome to the tenant application! <a href=\"" . route('tenant.dashboard') . "\">Dashboard</a>";
    })->name('tenant.dashboard');
})->where('tenant', '^(?!admin$).*'); // <--- THIS LINE IS THE FIX;
