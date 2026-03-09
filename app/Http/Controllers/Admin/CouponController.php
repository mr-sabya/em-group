<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    // coupons page
    public function index()
    {
        return view('admin.coupon.index');
    }

    // create coupon
    public function create()
    {
        return view('admin.coupon.create');
    }

    // edit coupon
    public function edit($id)
    {
        $coupon = Coupon::findOrFail($id);
        return view('admin.coupon.edit', compact('coupon'));
    }
}
