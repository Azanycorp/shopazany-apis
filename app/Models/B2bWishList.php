<?php

namespace App\Models;

use App\Enum\UserType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property float $qty
 * @property-read Collection<int, B2bProdctReview> $b2bProductReview
 * @property-read int|null $b2b_product_review_count
 * @property-read B2BProduct|null $product
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWishList newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWishList newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWishList query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWishList whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWishList whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWishList whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWishList whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWishList whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWishList whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['user_id', 'product_id', 'qty'])]
class B2bWishList extends Model
{
    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->where('type', UserType::B2B_BUYER);
    }

    /**
     * @return BelongsTo<B2BProduct, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(B2BProduct::class, 'product_id');
    }

    /**
     * @return HasMany<B2bProdctReview, $this>
     */
    public function b2bProductReview(): HasMany
    {
        return $this->hasMany(B2bProdctReview::class, 'product_id');
    }
}
