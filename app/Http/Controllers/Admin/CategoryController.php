<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // categories page
    public function index()
    {
        return view('backend.pages.categories.index');
    }

    // add new category
    public function create()
    {
        return view('backend.pages.categories.create');
    }

    // edit category
    public function edit($id)
    {
        return view('backend.pages.categories.edit', compact('id'));
    }
}
