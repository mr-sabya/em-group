<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    // banner page
    public function banners()
    {
        return view('backend.banner.index');
    }
    // banner page
    public function features()
    {
        return view('backend.feature.index');
    }
}
