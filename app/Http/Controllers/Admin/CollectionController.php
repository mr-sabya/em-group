<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    //
    public function index()
    {
        return view('backend.pages.collection.index');    
    }
    
    // create
    public function create()
    {
        return view('backend.pages.collection.create');
    }


    // edit
    public function edit($id)
    {
        return view('backend.pages.collection.edit', ['collectionId' => $id]);
    }
}
