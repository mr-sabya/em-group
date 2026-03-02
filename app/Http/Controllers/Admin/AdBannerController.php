<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdBannerController extends Controller
{
    //
    public function index()
    {
        return view('backend.ad-banner.index');    
    }
}
