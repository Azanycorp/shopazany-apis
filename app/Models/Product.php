<?php

namespace App\Models;

use App\Models\Pivots\OrderItemPivot;
use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int|null $admin_id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property int $category_id
 * @property int|null $sub_category_id
 * @property int|null $brand_id
 * @property int|null $color_id
 * @property int|null $unit_id
 * @property int|null $size_id
 * @property string|null $product_sku
 * @property string $product_price
 * @property string|null $discount_price
 * @property string $price
 * @property string|null $discount_type
 * @property string|null $discount_value
 * @property string|null $usd_price
 * @property string $default_currency
 * @property int $current_stock_quantity
 * @property int $minimum_order_quantity
 * @property string|null $image
 * @property string|null $public_id
 * @property string|null $added_by
 * @property int|null $country_id
 * @property bool $is_featured
 * @property string $status
 * @property OrderItemPivot|null $pivot
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $type
 * @property string|null $condition
 * @property string|null $currency
 * @property float|null $average_rating
 * @property-read Admin|null $admin
 * @property-read Brand|null $brand
 * @property-read Collection<int, Cart> $carts
 * @property-read int|null $carts_count
 * @property-read Category|null $category
 * @property-read Color|null $color
 * @property-read Country|null $country
 * @property-read mixed $discounted_price
 * @property-read mixed $is_in_wishlist
 * @property-read Collection<int, Order> $orders
 * @property-read int|null $orders_count
 * @property-read Collection<int, ProductReview> $productReviews
 * @property-read int|null $product_reviews_count
 * @property-read Collection<int, ProductVariation> $productVariations
 * @property-read int|null $product_variations_count
 * @property-read Collection<int, ProductImage> $productimages
 * @property-read int|null $productimages_count
 * @property-read ShopCountry|null $shopCountry
 * @property-read Size|null $size
 * @property-read SubCategory|null $subCategory
 * @property-read Unit|null $unit
 * @property-read User|null $user
 * @property-read Collection<int, Wishlist> $wishlists
 * @property-read int|null $wishlists_count
 *
 * @method static \Database\Factories\ProductFactory factory($count = null, $state = [])
 * @method static Builder<static>|Product mostFavorite()
 * @method static Builder<static>|Product newModelQuery()
 * @method static Builder<static>|Product newQuery()
 * @method static Builder<static>|Product query()
 * @method static Builder<static>|Product topRated()
 * @method static Builder<static>|Product whereAddedBy($value)
 * @method static Builder<static>|Product whereAdminId($value)
 * @method static Builder<static>|Product whereBrandId($value)
 * @method static Builder<static>|Product whereCategoryId($value)
 * @method static Builder<static>|Product whereColorId($value)
 * @method static Builder<static>|Product whereCondition($value)
 * @method static Builder<static>|Product whereCountryId($value)
 * @method static Builder<static>|Product whereCreatedAt($value)
 * @method static Builder<static>|Product whereCurrentStockQuantity($value)
 * @method static Builder<static>|Product whereDefaultCurrency($value)
 * @method static Builder<static>|Product whereDescription($value)
 * @method static Builder<static>|Product whereDiscountPrice($value)
 * @method static Builder<static>|Product whereDiscountType($value)
 * @method static Builder<static>|Product whereDiscountValue($value)
 * @method static Builder<static>|Product whereId($value)
 * @method static Builder<static>|Product whereImage($value)
 * @method static Builder<static>|Product whereIsFeatured($value)
 * @method static Builder<static>|Product whereMinimumOrderQuantity($value)
 * @method static Builder<static>|Product whereName($value)
 * @method static Builder<static>|Product wherePrice($value)
 * @method static Builder<static>|Product whereProductPrice($value)
 * @method static Builder<static>|Product whereProductSku($value)
 * @method static Builder<static>|Product wherePublicId($value)
 * @method static Builder<static>|Product whereSizeId($value)
 * @method static Builder<static>|Product whereSlug($value)
 * @method static Builder<static>|Product whereStatus($value)
 * @method static Builder<static>|Product whereSubCategoryId($value)
 * @method static Builder<static>|Product whereType($value)
 * @method static Builder<static>|Product whereUnitId($value)
 * @method static Builder<static>|Product whereUpdatedAt($value)
 * @method static Builder<static>|Product whereUsdPrice($value)
 * @method static Builder<static>|Product whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'user_id',
    'admin_id',
    'name',
    'slug',
    'description',
    'category_id',
    'sub_category_id',
    'brand_id',
    'color_id',
    'unit_id',
    'size_id',
    'product_sku',
    'product_price',
    'discount_price',
    'price',
    'discount_type',
    'discount_value',
    'usd_price',
    'default_currency',
    'current_stock_quantity',
    'minimum_order_quantity',
    'image',
    'public_id',
    'added_by',
    'country_id',
    'is_featured',
    'status',
    'type',
    'condition',
])]
class Product extends Model
{
    use ClearsResponseCache, HasFactory;

    protected static function booted()
    {
        static::addGlobalScope('in_stock', function (Builder $builder) {
            $builder->where('current_stock_quantity', '>', 0);
        });
    }

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<Admin, $this>
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * @return BelongsTo<SubCategory, $this>
     */
    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }

    /**
     * @return HasMany<ProductImage, $this>
     */
    public function productimages(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    /**
     * @return BelongsTo<Country, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * @return BelongsTo<ShopCountry, $this>
     */
    public function shopCountry(): BelongsTo
    {
        return $this->belongsTo(ShopCountry::class, 'country_id', 'country_id');
    }

    /**
     * @return HasMany<Wishlist, $this>
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class, 'product_id');
    }

    /**
     * @return BelongsToMany<Order, $this, Pivot>
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_items')
            ->withPivot('product_quantity', 'price', 'sub_total', 'status');
    }

    /**
     * @return BelongsTo<Size, $this>
     */
    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class, 'size_id');
    }

    /**
     * @return BelongsTo<Brand, $this>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    /**
     * @return BelongsTo<Color, $this>
     */
    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class, 'color_id');
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * @return HasMany<Cart, $this>
     */
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class, 'product_id');
    }

    /**
     * @return HasMany<ProductReview, $this>
     */
    public function productReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class, 'product_id');
    }

    /**
     * @return HasMany<ProductVariation, $this>
     */
    public function productVariations(): HasMany
    {
        return $this->hasMany(ProductVariation::class, 'product_id');
    }

    // Attributes
    protected function isInWishlist(): Attribute
    {
        $user = Auth::user();

        return Attribute::make(
            get: function () use ($user) {
                if (! $user) {
                    return false;
                }

                return Wishlist::where([
                    ['user_id', $user->id],
                    ['product_id', $this->id],
                ])->exists();
            }
        );
    }

    protected function discountedPrice(): Attribute
    {
        return Attribute::get(function () {
            $discountType = (string) $this->discount_type;
            $discountValue = (float) $this->discount_value;
            if ($discountType === 'percentage') {
                return (float) $this->product_price - ((float) $this->product_price * ($discountValue / 100));
            }

            if ($discountType === 'flat') {
                return (float) $this->product_price - $discountValue;
            }

            return (float) $this->product_price;
        });
    }

    // Scopes
    #[Scope]
    protected function topRated(Builder $query)
    {
        return $query->select(
            'products.id',
            'products.name',
            'products.price',
            'products.image',
            'products.user_id',
            DB::raw('CAST(COALESCE(ROUND(AVG(product_reviews.rating), 1), 0) AS DECIMAL(2,1)) as average_rating')
        )
            ->with('user:id,first_name,last_name,image')
            ->withCount('productReviews')
            ->leftJoin('product_reviews', 'products.id', '=', 'product_reviews.product_id')
            ->withCount(['orders as sold_count' => function ($query): void {
                $query->select(DB::raw('COUNT(*)'));
            }])
            ->groupBy('products.id', 'products.name', 'products.price', 'products.image', 'products.user_id')
            ->orderByDesc('average_rating')
            ->orderByDesc('sold_count');
    }

    #[Scope]
    protected function mostFavorite(Builder $query)
    {
        return $query->select(
            'products.id',
            'products.name',
            'products.price',
            'products.image',
            'products.user_id',
            DB::raw('CAST(COALESCE(ROUND(AVG(product_reviews.rating), 1), 0) AS DECIMAL(2,1)) as average_rating')
        )
            ->with('user:id,first_name,last_name,image')
            ->withCount('productReviews')
            ->leftJoin('product_reviews', 'products.id', '=', 'product_reviews.product_id')
            ->withCount(['orders as sold_count' => function ($query): void {
                $query->select(DB::raw('COUNT(*)'));
            }])
            ->withCount(['wishlists as wishlist_count' => function ($query): void {
                $query->select(DB::raw('COUNT(*)'));
            }])
            ->groupBy('products.id', 'products.name', 'products.price', 'products.image', 'products.user_id')
            ->orderByDesc('wishlist_count')
            ->orderByDesc('average_rating')
            ->orderByDesc('sold_count');
    }
}
