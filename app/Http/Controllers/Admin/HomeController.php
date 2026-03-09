<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    //
    public function index()
    {
        return view('admin.home.index');
    }



    // brands page
    public function brands()
    {
        return view('admin.brand.index');
    }

    

    // tags page
    public function tags()
    {
        return view('admin.tag.index');
    }
}
