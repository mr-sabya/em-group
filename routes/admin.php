<?php

use Illuminate\Support\Facades\Route;


// super admin dashboard
Route::get('/dashboard', [App\Http\Controllers\Admin\HomeController::class, 'index'])->name('dashboard');
