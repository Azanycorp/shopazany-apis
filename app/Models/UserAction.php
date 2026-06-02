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
 * @property int $action_id
 * @property int $points
 * @property int $is_rewarded
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property float|string|null $value
 * @property string|null $currency
 * @property-read Action $action
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAction whereActionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAction whereIsRewarded($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAction wherePoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAction whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAction whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'user_id',
    'action_id',
    'points',
    'is_rewarded',
    'status',
])]
class UserAction extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<Action, $this>
     */
    public function action(): BelongsTo
    {
        return $this->belongsTo(Action::class, 'action_id');
    }
}
