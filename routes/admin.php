<?php

use Illuminate\Support\Facades\Route;


// super admin dashboard
Route::get('/dashboard', function () {
    return "Welcome to the super admin dashboard!";
})->name('admin.dashboard');
