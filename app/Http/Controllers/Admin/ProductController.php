<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Display a listing of the resource.
    public function index()
    {
        return view('backend.product.index');
    }

    // Show the form for creating a new resource.
    public function create()
    {
        return view('backend.product.create');
    }

    // Show the form for editing the specified resource.
    public function edit($id)
    {
        $product = Product::find($id);
        return view('backend.product.edit', compact('product'));
    }

    // review
    public function review()
    {
        return view('backend.product.review');
    }
}
