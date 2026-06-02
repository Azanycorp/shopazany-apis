<?php

namespace App\Console\Commands;

use App\Enum\Coupon as EnumCoupon;
use App\Models\Coupon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Description('Deactivate expired coupons')]
#[Signature('coupon:expire')]
class CouponExpire extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Coupon::wherePast('expire_at')
            ->whereStatus(EnumCoupon::ACTIVE)
            ->update(['status' => EnumCoupon::INACTIVE]);

        $this->info('Coupons expired successfully');
    }
}
