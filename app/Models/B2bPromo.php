<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class B2bPromo extends Model
{
    protected $fillable = [
        'coupon_code',
        'discount',
        'discount_type',
        'type',
        'start_date',
        'end_date',
    ];

    public function b2bPromoProduct()
    {
        return $this->hasOne(B2BPromoProduct::class);
    }

    public function b2bTotalOrder()
    {
        return $this->hasOne(B2BPromoTotalOrder::class);
    }

    public function b2bWelcomeCoupon()
    {
        return $this->hasOne(B2BPromoWelcomeCoupon::class);
    }
}
