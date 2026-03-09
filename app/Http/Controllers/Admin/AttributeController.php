<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    //
    public function attributes()
    {
        return view('admin.attribute.attributes.index');
    }

    // attribute value
    public function attributeValues()
    {
        return view('admin.attribute.attribute-value.index');
    }

    // attribute set
    public function attributeSets()
    {
        return view('admin.attribute.attribute-set.index');
    }
}
