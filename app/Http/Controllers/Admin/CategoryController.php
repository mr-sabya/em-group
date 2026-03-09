<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // categories page
    public function index()
    {
        return view('admin.categories.index');
    }

    // add new category
    public function create()
    {
        return view('admin.categories.create');
    }

    // edit category
    public function edit($id)
    {
        return view('admin.categories.edit', compact('id'));
    }
}
