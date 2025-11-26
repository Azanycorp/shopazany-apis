<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $product_id
 * @property-read Product $product
 */
class ProductVariation extends Model
{
    protected $fillable = [
        'product_id',
        'variation',
        'sku',
        'price',
        'stock',
        'image',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
