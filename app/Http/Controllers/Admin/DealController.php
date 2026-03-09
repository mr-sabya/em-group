<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DealController extends Controller
{
    //
    public function index()
    {
        return view('admin.deal.index');    
    }
    
    // create
    public function create()
    {
        return view('admin.deal.create');    
    }

    // edit
    public function edit($id)
    {
        return view('admin.deal.edit', ['dealId' => $id]);    
    }
}
