<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $email
 * @property string|null $user_type
 * @property string $action
 * @property string $description
 * @property string|null $url
 * @property string $ip
 * @property string $device
 * @property string $request
 * @property string $response
 * @property string $performed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLog whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLog whereDevice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLog whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLog whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLog wherePerformedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLog whereRequest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLog whereResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLog whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLog whereUserType($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'user_id',
    'email',
    'user_type',
    'action',
    'description',
    'ip',
    'url',
    'device',
    'request',
    'response',
    'performed_at',
])]
class UserLog extends Model
{
    use HasFactory;

    protected function cast(): array
    {
        return [
            'device' => 'object',
        ];
    }
}
