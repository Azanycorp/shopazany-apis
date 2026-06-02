<?php

namespace App\Models;

use App\Enum\UserType;
use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_id
 * @property int $buyer_id
 * @property int $seller_id
 * @property array<array-key, mixed>|null $product_data
 * @property float $qty
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, B2bProdctReview> $b2bProductReview
 * @property-read int|null $b2b_product_review_count
 * @property-read B2BProduct|null $product
 * @property-read User|null $seller
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bQuote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bQuote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bQuote query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bQuote whereBuyerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bQuote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bQuote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bQuote whereProductData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bQuote whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bQuote whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bQuote whereSellerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bQuote whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'buyer_id',
    'product_id',
    'seller_id',
    'product_data',
    'qty',
])]
class B2bQuote extends Model
{
    use ClearsResponseCache;

    protected function casts(): array
    {
        return [
            'product_data' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id')->where('type', UserType::B2B_BUYER);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id')->where('type', UserType::B2B_SELLER);
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
