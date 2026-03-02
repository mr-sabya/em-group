<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    // coupons page
    public function index()
    {
        return view('backend.pages.coupon.index');
    }

    // create coupon
    public function create()
    {
        return view('backend.pages.coupon.create');
    }

    // edit coupon
    public function edit($id)
    {
        $coupon = Coupon::findOrFail($id);
        return view('backend.pages.coupon.edit', compact('coupon'));
    }
}
