<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $type
 * @property-read Collection<int, B2CBlog> $blogs
 * @property-read int|null $blogs_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2CBlogCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2CBlogCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2CBlogCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2CBlogCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2CBlogCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2CBlogCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2CBlogCategory whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2CBlogCategory whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2CBlogCategory whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'name',
    'slug',
    'type',
])]
class B2CBlogCategory extends Model
{
    public function blogs()
    {
        return $this->hasMany(B2CBlog::class, 'b2_c_blog_category_id');
    }
}
