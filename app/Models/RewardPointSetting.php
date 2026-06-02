<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property float $point
 * @property float $value
 * @property string $currency
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting wherePoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting whereValue($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'point',
    'value',
    'currency',
])]
class RewardPointSetting extends Model
{
    protected function casts(): array
    {
        return [
            'point' => 'float',
            'value' => 'float',
        ];
    }
}
