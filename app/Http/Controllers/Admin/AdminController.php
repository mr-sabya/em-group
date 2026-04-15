<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{

    // index
    public function index()
    {
        return view('admin.admin.index');
    }

    public function showForgotPassword()
    {
        return view('admin.auth.forgot-password');
    }

    public function showResetPassword($token)
    {
        return view('admin.auth.reset-password', ['token' => $token]);
    }

    /**
     * Show the admin profile update page.
     */
    public function profile()
    {
        return view('admin.admin.profile');
    }

    /**
     * Show the admin change password page.
     */
    public function changePassword()
    {
        return view('admin.admin.change-password');
    }
}
