<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
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
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $type
 * @property-read B2bProductCategory|null $category
 * @property-read Collection<int, B2BProduct> $products
 * @property-read int|null $products_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProductSubCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProductSubCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProductSubCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProductSubCategory whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProductSubCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProductSubCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProductSubCategory whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProductSubCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProductSubCategory whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProductSubCategory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProductSubCategory whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bProductSubCategory whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'category_id',
    'name',
    'image',
    'slug',
    'status',
    'type',
])]
class B2bProductSubCategory extends Model
{
    use ClearsResponseCache;

    /**
     * @return BelongsTo<B2bProductCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(B2bProductCategory::class, 'category_id');
    }

    /**
     * @return HasMany<B2BProduct, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(B2BProduct::class, 'sub_category_id');
    }
}
