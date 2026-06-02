<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property array<array-key, mixed> $value
 * @property bool $use_for_variation
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttribute newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttribute newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttribute query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttribute whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttribute whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttribute whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttribute whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttribute whereUseForVariation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttribute whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttribute whereValue($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'user_id',
    'name',
    'value',
    'use_for_variation',
])]
class ProductAttribute extends Model
{
    protected function casts(): array
    {
        return [
            'value' => 'array',
            'use_for_variation' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
