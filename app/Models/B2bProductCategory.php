<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $image
 * @property int $featured
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $type
 * @property-read Collection<int, B2BProduct> $products
 * @property-read int|null $products_count
 * @property-read Collection<int, B2bProductSubCategory> $subcategory
 * @property-read int|null $subcategory_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProductCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProductCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProductCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProductCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProductCategory whereFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProductCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProductCategory whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProductCategory whereMetaDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProductCategory whereMetaTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProductCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProductCategory whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProductCategory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProductCategory whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProductCategory whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'name',
    'type',
    'slug',
    'image',
    'featured',
    'meta_title',
    'meta_description',
])]
class B2bProductCategory extends Model
{
    use ClearsResponseCache;

    /**
     * @return HasMany<B2bProductSubCategory, $this>
     */
    public function subcategory(): HasMany
    {
        return $this->hasMany(B2bProductSubCategory::class, 'category_id');
    }

    /**
     * @return HasMany<B2BProduct, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(B2BProduct::class, 'category_id');
    }
}
