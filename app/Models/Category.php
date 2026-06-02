<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $image
 * @property string|null $public_id
 * @property bool|null $featured
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $type
 * @property float|null $percentage
 * @property float|null $total_revenue
 * @property-read Collection<int, Product> $products
 * @property-read int|null $products_count
 * @property-read Collection<int, SubCategory> $subcategory
 * @property-read int|null $subcategory_count
 *
 * @method static \Database\Factories\CategoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereMetaDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereMetaTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category wherePublicId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'name',
    'slug',
    'image',
    'public_id',
    'featured',
    'meta_title',
    'meta_description',
    'type',
    'percentage',
])]
class Category extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
        ];
    }

    /**
     * @return HasMany<SubCategory, $this>
     */
    public function subcategory(): HasMany
    {
        return $this->hasMany(SubCategory::class, 'category_id');
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
