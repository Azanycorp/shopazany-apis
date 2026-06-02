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
 * @property string $name
 * @property string $email
 * @property string $subject
 * @property string $type
 * @property string $description
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSupport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSupport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSupport query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSupport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSupport whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSupport whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSupport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSupport whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSupport whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSupport whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSupport whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSupport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerSupport whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'user_id',
    'name',
    'email',
    'subject',
    'type',
    'description',
    'status',
])]
class CustomerSupport extends Model
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
