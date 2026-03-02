<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    //
    public function attributes()
    {
        return view('backend.attribute.attributes.index');
    }

    // attribute value
    public function attributeValues()
    {
        return view('backend.attribute.attribute-value.index');
    }

    // attribute set
    public function attributeSets()
    {
        return view('backend.attribute.attribute-set.index');
    }
}
