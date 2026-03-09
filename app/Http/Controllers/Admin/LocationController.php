<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    //country
    public function countries()
    {
        return view('admin.locations.countries');
    }

    // state
    public function states()
    {
        return view('admin.locations.states');
    }

    // city
    public function cities()
    {
        return view('admin.locations.cities');
    }
}
