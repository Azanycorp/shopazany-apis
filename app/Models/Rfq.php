<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rfq extends Model
{
    protected $fillable = [
        'buyer_id',
        'seller_id',
        'quote_no',
        'product_id',
        'product_quantity',
        'p_unit_price',
        'product_data',
        'total_amount',
        'payment_status',
        'status',
        'delivery_date',
        'shipped_date',
    ];

    protected function casts(): array
    {
        return [
            'product_data' => 'array'
        ];
    }
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id', 'id');
    }
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id', 'id');
    }

    public function messages(): HasMany
    {
        return $this->HasMany(RfqMessage::class);
    }
}
