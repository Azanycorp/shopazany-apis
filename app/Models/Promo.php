<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasFactory;

    protected $fillable = [
        'coupon_code',
        'discount',
        'discount_type',
        'type',
        'start_date',
        'end_date',
    ];

    public function promoProduct()
    {
        return $this->hasOne(PromoProduct::class);
    }

    public function totalOrder()
    {
        return $this->hasOne(PromoTotalOrder::class);
    }

    public function welcomeCoupon()
    {
        return $this->hasOne(PromoWelcomeCoupon::class);
    }
}
