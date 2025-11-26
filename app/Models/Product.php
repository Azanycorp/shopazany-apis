<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @property-read \App\Models\Pivots\OrderItemPivot $pivot
 * @property-read int $pivot_variation_id
 * @property-read int $pivot_product_quantity
 * @property-read float $pivot_price
 * @property-read float $pivot_sub_total
 * @property-read string $pivot_status
 * @property int $country_id
 * @property string|null $currency
 * @property float $average_rating
 * @property-read ShopCountry|null $shopCountry
 * @property int $id
 * @property string $name
 * @property string $image
 * @property string $public_id
 * @property float $discounted_price
 */
class Product extends Model
{
    use ClearsResponseCache, HasFactory;

    protected $fillable = [
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
    ];

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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Admin, $this>
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\SubCategory, $this>
     */
    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\ProductImage, $this>
     */
    public function productimages(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Country, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\ShopCountry, $this>
     */
    public function shopCountry(): BelongsTo
    {
        return $this->belongsTo(ShopCountry::class, 'country_id', 'country_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Wishlist, $this>
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class, 'product_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Order, $this, \Illuminate\Database\Eloquent\Relations\Pivot>
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_items')
            ->withPivot('product_quantity', 'price', 'sub_total', 'status');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Size, $this>
     */
    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class, 'size_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Brand, $this>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Color, $this>
     */
    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class, 'color_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Cart, $this>
     */
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class, 'product_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\ProductReview, $this>
     */
    public function productReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class, 'product_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\ProductVariation, $this>
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
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
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

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
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
