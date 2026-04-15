<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    //
    public function index()
    {
        return view('admin..page.index');
    }

    // create
    public function create()
    {
        return view('admin..page.create');
    }

    // edit
    public function edit($id)
    {
        $page = Page::findOrFail($id);
        return view('admin..page.edit', compact('page'));
    }
}
