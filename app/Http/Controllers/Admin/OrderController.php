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

    // manage view
    public function manage($orderId)
    {
        $order = Order::findOrFail($orderId);
        return view('admin.orders.manage', compact('order'));
    }

    // 
    public function cancelReasons()
    {
        return view('admin.orders.cancel-reasons');
    }
}
