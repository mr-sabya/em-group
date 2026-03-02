<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DealController extends Controller
{
    //
    public function index()
    {
        return view('backend.pages.deal.index');    
    }
    
    // create
    public function create()
    {
        return view('backend.pages.deal.create');    
    }

    // edit
    public function edit($id)
    {
        return view('backend.pages.deal.edit', ['dealId' => $id]);    
    }
}
