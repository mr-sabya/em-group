<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // customers
    public function customers()
    {
        return view('backend.users.customers.index');
    }

    // create customer
    public function createCustomer()
    {
        return view('backend.users.customers.create');
    }

    // edit customer
    public function editCustomer($id)
    {
        return view('backend.users.customers.edit', ['userId' => $id]);
    }

    // investors
    public function investors()
    {
        return view('backend.users.investors.index');
    }

    // create investor
    public function createInvestor()
    {
        return view('backend.users.investors.create');
    }

    // edit investor
    public function editInvestor($id)
    {
        return view('backend.users.investors.edit', ['userId' => $id]);
    }


    // vendors
    public function vendors()
    {
        return view('backend.users.vendors.index');
    }

    // create vendors
    public function createVendors()
    {
        return view('backend.users.vendors.create');
    }

    // edit vendors
    public function editVendors($id)
    {
        return view('backend.users.vendors.edit', ['userId' => $id]);
    }
}
