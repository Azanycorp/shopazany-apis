<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoProduct extends Model
{
    protected $table = "promo_products";

    use HasFactory;

    protected $fillable = [
        'promo_id',
        'product_id',
    ];
}
