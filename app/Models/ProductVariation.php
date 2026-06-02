<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_id
 * @property string $variation
 * @property string $sku
 * @property numeric $price
 * @property int $stock
 * @property string|null $image
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Product $product
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariation whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariation wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariation whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariation whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariation whereStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariation whereVariation($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'product_id',
    'variation',
    'sku',
    'price',
    'stock',
    'image',
])]
class ProductVariation extends Model
{
    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
