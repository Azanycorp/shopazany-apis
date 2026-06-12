<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $icon
 * @property string|null $verification_type
 * @property array<array-key, mixed>|null $country_ids
 * @property int $points
 * @property bool $default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Database\Factories\ActionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Action newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Action newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Action query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Action whereCountryIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Action whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Action whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Action whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Action whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Action whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Action whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Action wherePoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Action whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Action whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Action whereVerificationType($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'name',
    'slug',
    'points',
    'description',
    'icon',
    'verification_type',
    'country_ids',
    'default',
])]
class Action extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'country_ids' => 'array',
            'default' => 'boolean',
        ];
    }
}
