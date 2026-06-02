<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property array<array-key, mixed> $keywords
 * @property string|null $description
 * @property string|null $social_title
 * @property string|null $social_description
 * @property string|null $image
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoConfiguration newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoConfiguration newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoConfiguration query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoConfiguration whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoConfiguration whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoConfiguration whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoConfiguration whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoConfiguration whereKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoConfiguration whereSocialDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoConfiguration whereSocialTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoConfiguration whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'keywords',
    'description',
    'social_title',
    'social_description',
    'image',
])]
class SeoConfiguration extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'keywords' => 'array',
        ];
    }
}
