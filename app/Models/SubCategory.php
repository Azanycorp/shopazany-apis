<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string $slug
 * @property string|null $image
 * @property string|null $public_id
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $type
 * @property-read Category $category
 * @property-read Collection<int, Product> $products
 * @property-read int|null $products_count
 *
 * @method static \Database\Factories\SubCategoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubCategory whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubCategory whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubCategory wherePublicId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubCategory whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubCategory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubCategory whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubCategory whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'category_id',
    'name',
    'image',
    'public_id',
    'slug',
    'status',
    'type',
])]
class SubCategory extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'sub_category_id');
    }
}
