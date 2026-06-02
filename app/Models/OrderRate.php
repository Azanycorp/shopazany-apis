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
 * @property string $order_no
 * @property numeric $rating
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderRate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderRate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderRate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderRate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderRate whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderRate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderRate whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderRate whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderRate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderRate whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'user_id',
    'order_no',
    'rating',
    'description',
])]
class OrderRate extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
