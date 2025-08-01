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
            'numbers' => 'required|integer|min:1|max:10000',
        ]);

        return $this->couponService->createCoupon($request);
    }

    public function getCoupon(): array
    {
        return $this->couponService->getCoupon();
    }
}
