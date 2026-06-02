<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property int $b2_c_blog_category_id
 * @property string $short_description
 * @property string $description
 * @property string $image
 * @property string $meta_title
 * @property string $meta_description
 * @property string $meta_keywords
 * @property string $meta_image
 * @property string $status
 * @property int $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $type
 * @property-read B2CBlogCategory $blogCategory
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2CBlog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2CBlog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2CBlog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2CBlog whereB2CBlogCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2CBlog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2CBlog whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2CBlog whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2CBlog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2CBlog whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2CBlog whereMetaDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2CBlog whereMetaImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2CBlog whereMetaKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2CBlog whereMetaTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2CBlog whereShortDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2CBlog whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2CBlog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2CBlog whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2CBlog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2CBlog whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'title',
    'slug',
    'b2_c_blog_category_id',
    'short_description',
    'description',
    'image',
    'meta_title',
    'meta_description',
    'meta_keywords',
    'meta_image',
    'status',
    'created_by',
    'type',
])]
class B2CBlog extends Model
{
    /**
     * @return BelongsTo<B2CBlogCategory, $this>
     */
    public function blogCategory(): BelongsTo
    {
        return $this->belongsTo(B2CBlogCategory::class, 'b2_c_blog_category_id');
    }
}
