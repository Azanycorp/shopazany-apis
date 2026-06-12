<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $type
 * @property string $slug
 * @property int $category_id
 * @property string|null $sub_category_id
 * @property int|null $brand_id
 * @property array<array-key, mixed> $keywords
 * @property string $description
 * @property string $front_image
 * @property string|null $public_id
 * @property int $minimum_order_quantity
 * @property string|null $unit
 * @property string $fob_price
 * @property int $country_id
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property float $unit_price
 * @property float $availability_quantity
 * @property float $quantity
 * @property string $default_currency
 * @property float $sold
 * @property string|null $admin_comment
 * @property-read Collection<int, B2bProdctLike> $b2bLikes
 * @property-read int|null $b2b_likes_count
 * @property-read Collection<int, B2BProductImage> $b2bProductImages
 * @property-read int|null $b2b_product_images_count
 * @property-read Collection<int, B2bProdctReview> $b2bProductReview
 * @property-read int|null $b2b_product_review_count
 * @property-read Collection<int, B2BRequestRefund> $b2bRequestRefunds
 * @property-read int|null $b2b_request_refunds_count
 * @property-read B2bProductCategory|null $category
 * @property-read Country|null $country
 * @property-read Collection<int, B2bOrder> $orders
 * @property-read int|null $orders_count
 * @property-read ShopCountry|null $shopCountry
 * @property-read B2bProductSubCategory|null $subCategory
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProduct whereAdminComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProduct whereAvailabilityQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProduct whereBrandId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProduct whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProduct whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProduct whereDefaultCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProduct whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProduct whereFobPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProduct whereFrontImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProduct whereKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProduct whereMinimumOrderQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProduct whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProduct wherePublicId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProduct whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProduct whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProduct whereSold($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProduct whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProduct whereSubCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProduct whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProduct whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProduct whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProduct whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BProduct whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'user_id',
    'name',
    'slug',
    'category_id',
    'sub_category_id',
    'brand_id',
    'keywords',
    'description',
    'front_image',
    'public_id',
    'minimum_order_quantity',
    'unit_price',
    'quantity',
    'availability_quantity',
    'admin_comment',
    'sold',
    'fob_price',
    'default_currency',
    'status',
    'logo',
    'country_id',
    'type',
])]
#[Table(name: 'b2b_products')]
class B2BProduct extends Model
{
    use ClearsResponseCache, HasFactory;

    protected function casts(): array
    {
        return [
            'keywords' => 'array',
        ];
    }

    /**
     * @return HasMany<B2bProdctLike, $this>
     */
    public function b2bLikes(): HasMany
    {
        return $this->hasMany(B2bProdctLike::class, 'product_id');
    }

    /**
     * @return HasMany<B2bProdctReview, $this>
     */
    public function b2bProductReview(): HasMany
    {
        return $this->hasMany(B2bProdctReview::class, 'product_id');
    }

    /**
     * @return HasMany<B2BProductImage, $this>
     */
    public function b2bProductImages(): HasMany
    {
        return $this->hasMany(B2BProductImage::class, 'b2b_product_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<ShopCountry, $this>
     */
    public function shopCountry(): BelongsTo
    {
        return $this->belongsTo(ShopCountry::class, 'country_id', 'country_id');
    }

    /**
     * @return BelongsTo<B2bProductCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(B2bProductCategory::class, 'category_id');
    }

    /**
     * @return BelongsTo<B2bProductSubCategory, $this>
     */
    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(B2bProductSubCategory::class, 'category_id');
    }

    /**
     * @return HasMany<B2bOrder, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(B2bOrder::class, 'product_id');
    }

    /**
     * @return BelongsTo<Country, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * @return HasMany<B2BRequestRefund, $this>
     */
    public function b2bRequestRefunds(): HasMany
    {
        return $this->hasMany(B2BRequestRefund::class, 'b2b_product_id');
    }
}
