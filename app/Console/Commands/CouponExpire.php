<?php

namespace App\Console\Commands;

use App\Enum\Coupon as EnumCoupon;
use App\Models\Coupon;
use Illuminate\Console\Command;

class CouponExpire extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coupon:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate expired coupons';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Coupon::wherePast('expire_at')
            ->whereStatus(EnumCoupon::ACTIVE)
            ->update(['status' => EnumCoupon::INACTIVE]);

        $this->info('Coupons expired successfully');
    }
}
