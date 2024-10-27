<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class B2BProduct extends Model
{
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
        'unit',
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

    public function b2bProductImages()
    {
        return $this->hasMany(B2BProductImage::class, 'b2b_product_id');
    }
}
