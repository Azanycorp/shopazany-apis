<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_id
 * @property int $buyer_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read B2BProduct|null $b2bProduct
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProdctLike newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProdctLike newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProdctLike query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProdctLike whereBuyerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProdctLike whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProdctLike whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProdctLike whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProdctLike whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'product_id',
    'buyer_id',
])]
class B2bProdctLike extends Model
{
    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * @return BelongsTo<B2BProduct, $this>
     */
    public function b2bProduct(): BelongsTo
    {
        return $this->belongsTo(B2BProduct::class, 'product_id');
    }
}
