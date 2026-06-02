<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $icon
 * @property string $url
 * @property string|null $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialSetting whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialSetting whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialSetting whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialSetting whereUrl($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'name',
    'icon',
    'url',
    'type',
])]
class SocialSetting extends Model {}
