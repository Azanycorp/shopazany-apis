<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $b2b_product_id
 * @property string $image
 * @property string|null $public_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read B2BProduct $b2bProduct
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProductImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProductImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProductImage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProductImage whereB2bProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProductImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProductImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProductImage whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProductImage wherePublicId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProductImage whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'b2b_product_id',
    'image',
    'public_id',
])]
#[Table(name: 'b2b_product_images')]
class B2BProductImage extends Model
{
    use ClearsResponseCache, HasFactory;

    public function b2bProduct()
    {
        return $this->belongsTo(B2BProduct::class);
    }
}
