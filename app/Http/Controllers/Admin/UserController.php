<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // customers
    public function customers()
    {
        return view('admin.users.customers.index');
    }

    // create customer
    public function createCustomer()
    {
        return view('admin.users.customers.create');
    }

    // edit customer
    public function editCustomer($id)
    {
        return view('admin.users.customers.edit', ['userId' => $id]);
    }


    // permissions
    public function permissions()
    {
        return view('admin.role.permission');
    }

    // roles
    public function roles()
    {
        return view('admin.role.role');
    }
}
