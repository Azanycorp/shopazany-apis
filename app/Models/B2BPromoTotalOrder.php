<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class B2BPromoTotalOrder extends Model
{
    protected $table = 'b2b_promo_total_orders';

    protected $fillable = [
        'promo_id',
        'minimum_cart_amount',
        'maximum_discount_amount',
    ];
}
