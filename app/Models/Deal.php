<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string|null $slug
 * @property string $image
 * @property string $public_id
 * @property string $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $type
 * @property-read Collection<int, Banner> $banners
 * @property-read int|null $banners_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal wherePublicId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deal whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'title',
    'slug',
    'image',
    'public_id',
    'position',
    'type',
])]
#[Hidden([
    'public_id',
    'updated_at',
])]
class Deal extends Model
{
    /**
     * @return HasMany<Banner, $this>
     */
    public function banners(): HasMany
    {
        return $this->hasMany(Banner::class, 'deal_id');
    }
}
