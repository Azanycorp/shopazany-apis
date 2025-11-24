<?php

namespace App\Services\Admin;

use App\Enum\BannerType;
use App\Enum\Coupon as EnumCoupon;
use App\Models\Coupon;
use App\Trait\HttpResponse;
use Illuminate\Support\Str;

class CouponService
{
    use HttpResponse;

    public function createCoupon($request)
    {
        if ($request->type === EnumCoupon::MULTI_USE->value) {
            $this->createMultiUseCoupon($request);
        } elseif ($request->type === EnumCoupon::ONE_TIME->value) {
            $this->createOneTimeCoupon($request);
        }

        return $this->success(null, 'Coupons created successfully', 201);
    }

    private function createMultiUseCoupon($request): void
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (Coupon::where('code', $code)->exists());

        $link = generate_coupon_links($request->platform, $code);

        Coupon::create([
            'name' => 'Signup coupon',
            'code' => $code,
            'link' => $link,
            'type' => EnumCoupon::MULTI_USE->value,
            'max_use' => $request->numbers,
            'expire_at' => now()->addDays(30),
            'platform' => $request->platform,
            'status' => EnumCoupon::ACTIVE->value,
        ]);
    }

    private function createOneTimeCoupon($request): void
    {
        for ($i = 0; $i < $request->numbers; $i++) {
            do {
                $code = strtoupper(Str::random(8));
            } while (Coupon::where('code', $code)->exists());

            $link = generate_coupon_links($request->platform, $code);

            $coupon = Coupon::create([
                'name' => 'Signup coupon',
                'code' => $code,
                'link' => $link,
                'type' => EnumCoupon::ONE_TIME,
                'expire_at' => now()->addDays(30),
                'platform' => $request->platform,
                'status' => EnumCoupon::ACTIVE,
            ]);

            $coupons[] = $coupon;
        }
    }

    public function getCoupon($request)
    {
        $platform = $request->query('platform', BannerType::B2C);

        if (! in_array($platform, [BannerType::B2C, BannerType::B2B, BannerType::AGRIECOM_B2C])) {
            return $this->error(null, "Invalid type {$platform}", 400);
        }

        $coupons = Coupon::select('id', 'name', 'code', 'link', 'used', 'max_use', 'total_used', 'type', 'expire_at', 'status', 'platform')
            ->where('platform', $platform)
            ->latest()
            ->paginate(25);

        return $this->withPagination($coupons, 'All coupons');
    }
}
