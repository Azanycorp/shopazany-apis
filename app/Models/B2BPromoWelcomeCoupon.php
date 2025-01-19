<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class B2BPromoWelcomeCoupon extends Model
{
    protected $table = "b2b_promo_welcome_coupons";

    protected $fillable = [
        'promo_id',
        'minimum_shopping_amount',
        'number_of_days_valid',
    ];
}
