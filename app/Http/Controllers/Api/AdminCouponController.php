<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Admin\CouponService;
use Illuminate\Http\Request;

class AdminCouponController extends Controller
{
    public function __construct(private CouponService $couponService)
    {}

    public function createCoupon(Request $request)
    {
        $request->validate([
            'no_of_coupon' => 'required|integer|min:1',
        ]);

        return $this->couponService->createCoupon($request);
    }

    public function getCoupon()
    {
        return $this->couponService->getCoupon();
    }
}
