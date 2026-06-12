<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $image
 * @property string|null $public_id
 * @property string|null $link
 * @property string|null $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SliderImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SliderImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SliderImage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SliderImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SliderImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SliderImage whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SliderImage whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SliderImage wherePublicId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SliderImage whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SliderImage whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'image',
    'public_id',
    'type',
    'link',
])]
class SliderImage extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::created(function ($slider): void {
            cache()->forget('home_sliders');

            cache()->rememberForever('home_sliders', function () {
                return SliderImage::orderBy('created_at', 'desc')->take(5)->get();
            });
        });
    }
}
