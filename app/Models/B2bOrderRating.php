<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $seller_id
 * @property int|null $buyer_id
 * @property string $order_no
 * @property float $rating
 * @property string|null $type
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $buyer
 * @property-read B2BProduct|null $product
 * @property-read User|null $seller
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrderRating newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrderRating newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrderRating query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrderRating whereBuyerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrderRating whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrderRating whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrderRating whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrderRating whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrderRating whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrderRating whereSellerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrderRating whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrderRating whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'buyer_id',
    'seller_id',
    'order_no',
    'rating',
    'type',
    'description',
])]
class B2bOrderRating extends Model
{
    /**
     * @return BelongsTo<User, $this>
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * @return BelongsTo<B2BProduct, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(B2BProduct::class, 'product_id');
    }
}
