<?php

namespace App\Services\Admin;

use App\Models\Coupon;
use Illuminate\Support\Str;
use App\Enum\Coupon as EnumCoupon;
use App\Trait\HttpResponse;

class CouponService
{
    use HttpResponse;

    public function createCoupon($request)
    {
        for ($i = 0; $i < $request->no_of_coupon; $i++) {
            do {
                $code = strtoupper(Str::random(8));
            } while (Coupon::where('code', $code)->exists());

            if (app()->environment('production')) {
                $link = config('services.frontend_baseurl') . '/register?coupon=' . $code;
            } else {
                $link = config('services.staging_frontend_baseurl') . '/register?coupon=' . $code;
            }

            $coupon = Coupon::create([
                'name' => "Signup coupon",
                'code' => $code,
                'link' => $link,
                'type' => EnumCoupon::ONE_TIME,
                'expire_at' => now()->addDays(30),
                'status' => EnumCoupon::ACTIVE
            ]);

            $coupons[] = $coupon;
        }

        return $this->success(null, "Coupons created successfully", 201);
    }

    public function getCoupon()
    {
        $coupons = Coupon::select('id', 'name', 'code', 'link', 'used', 'type', 'expire_at', 'status')->get();

        return $this->success($coupons, "Coupon List");
    }
}

