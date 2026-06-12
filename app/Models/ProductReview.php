<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property int $rating
 * @property string $review
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Product $product
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview whereReview($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'user_id',
    'product_id',
    'rating',
    'review',
    'status',
])]
class ProductReview extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
