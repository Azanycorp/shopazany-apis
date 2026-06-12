<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $product_id
 * @property int|null $buyer_id
 * @property float $rating
 * @property string|null $title
 * @property string|null $note
 * @property string|null $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProdctReview newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProdctReview newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProdctReview query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProdctReview whereBuyerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProdctReview whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProdctReview whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProdctReview whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProdctReview whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProdctReview whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProdctReview whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProdctReview whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProdctReview whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'product_id',
    'buyer_id',
    'rating',
    'title',
    'note',
    'type',
])]
class B2bProdctReview extends Model
{
    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
}
