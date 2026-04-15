<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CourierController extends Controller
{
    //
    public function index()
    {
        return view('admin.couriers.index');
    }
}
