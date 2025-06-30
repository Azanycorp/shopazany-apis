<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoWelcomeCoupon extends Model
{
    protected $table = 'promo_welcome_coupons';

    use HasFactory;

    protected $fillable = [
        'promo_id',
        'minimum_shopping_amount',
        'number_of_days_valid',
    ];
}
