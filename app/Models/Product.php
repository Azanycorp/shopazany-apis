<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'category_id',
        'sub_category_id',
        'brand_id',
        'color_id',
        'unit_id',
        'size_id',
        'product_sku',
        'product_price',
        'discount_price',
        'price',
        'current_stock_quantity',
        'minimum_order_quantity',
        'image',
        'added_by',
        'country_id',
        'status'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function productimages(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
