<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class B2BPromoProduct extends Model
{
    protected $table = "b2b_promo_products";

    protected $fillable = [
        'promo_id',
        'product_id',
    ];
}
