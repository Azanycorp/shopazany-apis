<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{

    protected $fillable = [
        'buyer_id',
        'seller_id',
        'product_id',
        'product_quantity',
        'order_no',
        'shipping_address',
        'shipping_agent',
        'billing_address',
        'product_data',
        'total_amount',
        'payment_method',
        'payment_status',
        'status',
        'delivery_date',
        'shipped_date',
        'centre_id',
        'country_id',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(B2BProduct::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id', 'id');
    }

    public function collationCentre(): BelongsTo
    {
        return $this->belongsTo(CollationCenter::class, 'centre_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id', 'id');
    }

    protected function casts(): array
    {
        return [
            'shipping_address' => 'array',
            'billing_address' => 'array',
            'product_data' => 'array',
        ];
    }
}
