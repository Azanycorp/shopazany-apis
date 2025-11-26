<?php

namespace App\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

class OrderItemPivot extends Pivot
{
    protected $table = 'order_items';

    protected $fillable = [
        'variation_id',
        'product_quantity',
        'price',
        'sub_total',
        'status',
    ];
}
