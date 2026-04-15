<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    //
    public function  index()
    {
        return view('admin.orders.index');
    }

    // invoice view
    public function invoice($orderId)
    {
        return view('admin.orders.invoice', compact('orderId'));
    }

    // create view
    public function create()
    {
        return view('admin.orders.create');
    }

    // edit view
    public function edit($orderId)
    {
        $order = Order::findOrFail($orderId);
        return view('admin.orders.edit', compact('order'));
    }

    // cancel reasons view
    public function cancelReasons()
    {
        return view('admin.orders.cancel-reasons');
    }
}
