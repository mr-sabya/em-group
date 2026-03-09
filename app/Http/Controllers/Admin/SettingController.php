<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    // settings page
    public function index()
    {
        return view('backend.settings.index');
    }
}
