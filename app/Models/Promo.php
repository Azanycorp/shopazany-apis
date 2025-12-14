<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function promoProduct(): HasOne
    {
        return $this->hasOne(PromoProduct::class);
    }

    public function totalOrder(): HasOne
    {
        return $this->hasOne(PromoTotalOrder::class);
    }

    public function welcomeCoupon(): HasOne
    {
        return $this->hasOne(PromoWelcomeCoupon::class);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(PromoRedemption::class);
    }
}
