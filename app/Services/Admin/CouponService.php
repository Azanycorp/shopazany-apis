<?php

namespace App\Services\Admin;

use App\Enum\Coupon as EnumCoupon;
use App\Models\Coupon;
use App\Trait\HttpResponse;
use Illuminate\Support\Str;

class CouponService
{
    use HttpResponse;

    public function createCoupon($request)
    {
        if ($request->type === EnumCoupon::MULTI_USE) {
            $this->createMultiUseCoupon($request);
        } else {
            $this->createOneTimeCoupon($request);
        }

        return $this->success(null, 'Coupons created successfully', 201);
    }

    private function createMultiUseCoupon($request)
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (Coupon::where('code', $code)->exists());

        $link = app()->environment('production')
            ? config('services.seller_baseurl').'?coupon='.$code
            : config('services.staging_seller_baseurl').'?coupon='.$code;

        Coupon::create([
            'name' => 'Signup coupon',
            'code' => $code,
            'link' => $link,
            'type' => EnumCoupon::MULTI_USE,
            'max_use' => $request->numbers,
            'expire_at' => now()->addDays(30),
            'status' => EnumCoupon::ACTIVE,
        ]);
    }

    private function createOneTimeCoupon($request)
    {
        for ($i = 0; $i < $request->numbers; $i++) {
            do {
                $code = strtoupper(Str::random(8));
            } while (Coupon::where('code', $code)->exists());

            if (app()->environment('production')) {
                $link = config('services.seller_baseurl').'?coupon='.$code;
            } else {
                $link = config('services.staging_seller_baseurl').'?coupon='.$code;
            }

            $coupon = Coupon::create([
                'name' => 'Signup coupon',
                'code' => $code,
                'link' => $link,
                'type' => EnumCoupon::ONE_TIME,
                'expire_at' => now()->addDays(30),
                'status' => EnumCoupon::ACTIVE,
            ]);

            $coupons[] = $coupon;
        }
    }

    public function getCoupon(): array
    {
        $coupons = Coupon::select('id', 'name', 'code', 'link', 'used', 'max_use', 'total_used', 'type', 'expire_at', 'status')
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return [
            'status' => 'true',
            'message' => 'All products',
            'data' => $coupons,
            'pagination' => [
                'current_page' => $coupons->currentPage(),
                'last_page' => $coupons->lastPage(),
                'per_page' => $coupons->perPage(),
                'prev_page_url' => $coupons->previousPageUrl(),
                'next_page_url' => $coupons->nextPageUrl(),
            ],
        ];
    }
}
