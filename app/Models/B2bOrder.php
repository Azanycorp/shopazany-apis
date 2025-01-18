<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class B2bOrder extends Model
{
    protected $fillable = [
        'buyer_id',
        'seller_id',
        'product_id',
        'product_quantity',
        'order_no',
        'shipping_address',
        'product_data',
        'amount',
        'payment_method',
        'payment_status',
        'status',
        'delivery_date',
        'shipped_date',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class,'seller_id','id');
    }
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class,'buyer_id','id');
    }

    protected function casts(): array
    {
        return [
            'shipping_address' => 'array',
            'product_data' => 'array'
        ];
    }


}
