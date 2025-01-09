<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class B2BProduct extends Model
{
    protected $table = 'b2b_products';

    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'category_id',
        'sub_category_id',
        'keywords',
        'description',
        'front_image',
        'minimum_order_quantity',
        'unit_price',
        'quantity',
        'availability_quantity',
        'sold',
        'fob_price',
        'status',
        'country_id',
    ];

    protected function casts(): array
    {
        return [
            'keywords' => 'array'
        ];
    }

    public function b2bProductImages(): HasMany
    {
        return $this->hasMany(B2BProductImage::class, 'b2b_product_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(B2bProductCategory::class, 'category_id');
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(B2bProductSubCategory::class, 'category_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function b2bRequestRefunds(): HasMany
    {
        return $this->hasMany(B2BRequestRefund::class, 'b2b_product_id');
    }
}
