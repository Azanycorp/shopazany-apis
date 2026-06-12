<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $link
 * @property int $max_use
 * @property int $used
 * @property int $total_used
 * @property array<array-key, mixed>|null $used_by
 * @property string $type
 * @property string $expire_at
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $platform
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereExpireAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereMaxUse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon wherePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereTotalUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereUsedBy($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'name',
    'code',
    'link',
    'max_use',
    'used',
    'used_by',
    'type',
    'expire_at',
    'status',
    'total_used',
    'platform',
])]
class Coupon extends Model
{
    protected function casts(): array
    {
        return [
            'used_by' => 'array',
        ];
    }
}
