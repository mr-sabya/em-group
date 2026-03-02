<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    //
    public function index()
    {
        return view('backend.page.index');
    }

    // create
    public function create()
    {
        return view('backend.page.create');
    }

    // edit
    public function edit($id)
    {
        $page = Page::findOrFail($id);
        return view('backend.page.edit', compact('page'));
    }
}
