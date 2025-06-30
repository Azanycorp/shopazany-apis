<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoTotalOrder extends Model
{
    protected $table = 'promo_total_orders';

    use HasFactory;

    protected $fillable = [
        'promo_id',
        'minimum_cart_amount',
        'maximum_discount_amount',
    ];
}
