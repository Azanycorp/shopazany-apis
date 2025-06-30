<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    protected function casts(): array
    {
        return [
            'products' => 'array',
        ];
    }

    protected function products(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Product::whereIn('id', is_array($value) ? $value : json_decode($value, true))
                ->select(['id', 'name', 'product_price', 'description', 'discount_price', 'slug'])
                ->get()
        );
    }

    protected function b2bProducts(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => B2BProduct::whereIn('id', is_array($value) ? $value : json_decode($value, true))->get()
        );
    }
}
