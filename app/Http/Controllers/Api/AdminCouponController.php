<?php

namespace App\Http\Controllers\Api;

use App\Enum\Coupon;
use App\Http\Controllers\Controller;
use App\Services\Admin\CouponService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminCouponController extends Controller
{
    public function __construct(private CouponService $couponService) {}

    public function createCoupon(Request $request)
    {
        $request->validate([
            'type' => ['required', Rule::in([
                Coupon::MULTI_USE,
                Coupon::ONE_TIME,
            ]),
            ],
            'numbers' => ['required', 'integer', 'min:1', 'max:10000'],
            'platform' => ['required', 'string', 'in:b2c,b2b,agriecom_b2c'],
        ], [
            'platform.in' => 'The platform must be one of: b2c, b2b, agriecom_b2c.',
        ]);

        return $this->couponService->createCoupon($request);
    }

    public function getCoupon()
    {
        return $this->couponService->getCoupon();
    }
}
