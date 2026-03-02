<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    //country
    public function countries()
    {
        return view('backend.pages.locations.countries');
    }

    // state
    public function states()
    {
        return view('backend.pages.locations.states');
    }

    // city
    public function cities()
    {
        return view('backend.pages.locations.cities');
    }
}
