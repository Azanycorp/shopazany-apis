<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read array $product_ids
 */
class Banner extends Model
{
    use ClearsResponseCache, HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'image',
        'public_id',
        'start_date',
        'end_date',
        'type',
        'products',
        'status',
        'deal_id',
    ];

    protected function casts(): array
    {
        return [
            'products' => 'array',
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Deal, $this>
     */
    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'deal_id');
    }

    protected function products(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Product::whereIn('id', is_array($value) ? $value : json_decode($value, true))
                ->select(['id', 'name', 'product_price', 'description', 'discount_price', 'slug'])
                ->get()
        );
    }

    protected function productIds(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => is_array($attributes['products'])
                    ? $attributes['products']
                    : json_decode($attributes['products'], true)
        );
    }

    protected function b2bProducts(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => B2BProduct::whereIn('id', is_array($value) ? $value : json_decode($value, true))->get()
        );
    }
}
