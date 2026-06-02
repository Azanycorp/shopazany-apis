<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $action
 * @property int|null $points_awarded
 * @property string|null $description
 * @property string|null $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserActivityLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserActivityLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserActivityLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserActivityLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserActivityLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserActivityLog whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserActivityLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserActivityLog wherePointsAwarded($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserActivityLog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserActivityLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserActivityLog whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'user_id',
    'action',
    'points_awarded',
    'description',
    'status',
])]
class UserActivityLog extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function logAction(User $user, Action $action, $status, $description = null): void
    {
        $actionName = $action['name'];
        $actionSlug = $action['slug'];
        $pointsAwarded = $action['points'];

        $log = new self;

        $log->user_id = $user['id'];
        $log->action = $actionSlug;
        $log->points_awarded = $pointsAwarded;
        $log->description = $description ?? "Activity bonus ($actionName)";
        $log->status = $status;

        $log->save();
    }
}
