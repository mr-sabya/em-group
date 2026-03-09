<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    //
    public function index()
    {
        return view('admin.collection.index');    
    }
    
    // create
    public function create()
    {
        return view('admin.collection.create');
    }


    // edit
    public function edit($id)
    {
        return view('admin.collection.edit', ['collectionId' => $id]);
    }
}
