<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $name
 * @property int $point
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RedeemPoint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RedeemPoint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RedeemPoint query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RedeemPoint whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RedeemPoint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RedeemPoint whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RedeemPoint wherePoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RedeemPoint whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RedeemPoint whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RedeemPoint whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'user_id',
    'name',
    'point',
    'status',
])]
class RedeemPoint extends Model
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
