<?php

namespace App\Http\Controllers\Backend\Blog;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BlogPostController extends Controller
{
    // index
    public function index()
    {
        return view('backend.blog.blog-post.index');    
    }
    
    // create new blog post
    public function create()
    {
        return view('backend.blog.blog-post.create');    
    }
    
    //edit blog post by id
    public function edit($id)
    {
        return view('backend.blog.blog-post.edit', ['blogPostId' => $id]);
    }
}
