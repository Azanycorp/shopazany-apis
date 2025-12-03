<?php

namespace App\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

class OrderItemPivot extends Pivot
{
    protected $table = 'order_items';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $fillable = [
        'product_id',
        'variation_id',
        'product_quantity',
        'price',
        'sub_total',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'sub_total' => 'float',
            'product_quantity' => 'integer',
            'variation_id' => 'integer',
            'order_id' => 'integer',
            'product_id' => 'integer',
            'id' => 'integer',
        ];
    }
}
