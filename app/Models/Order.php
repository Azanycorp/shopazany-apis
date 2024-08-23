<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'seller_id',
        'product_id',
        'product_quantity',
        'order_no',
        'shipping_address',
        'order_date',
        'total_amount',
        'payment_method',
        'payment_status',
        'status',
        'country_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->where('type', 'customer');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->where('type', 'seller');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'product_id');
    }
}
