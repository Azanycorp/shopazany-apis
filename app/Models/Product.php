<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

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

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class, 'product_id');
    }

    public function orders()
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
        return Attribute::make(
            get: function () {
                if (! $this->authManager->guard('sanctum')->check()) {
                    return false;
                }

                return Wishlist::where([
                    ['user_id', $this->authManager->guard('sanctum')->id()],
                    ['product_id', $this->id],
                ])->exists();
            }
        );
    }

    protected function discountedPrice(): Attribute
    {
        return Attribute::get(function () {
            $discountType = $this->discount_type;
            $discountValue = $this->discount_value;
            if ($discountType === 'percentage') {
                return $this->product_price - ($this->product_price * ($discountValue / 100));
            }

            if ($discountType === 'flat') {
                return $this->product_price - $discountValue;
            }

            return $this->product_price;
        });
    }

    // Scopes
    public function scopeTopRated(Builder $query, $userId)
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
            ->where('products.user_id', $userId)
            ->leftJoin('product_reviews', 'products.id', '=', 'product_reviews.product_id')
            ->withCount(['orders as sold_count' => function ($query): void {
                $query->select(DB::raw('COUNT(*)'));
            }])
            ->groupBy('products.id', 'products.name', 'products.price', 'products.image', 'products.user_id')
            ->orderByDesc('average_rating')
            ->orderByDesc('sold_count');
    }

    public function scopeMostFavorite(Builder $query, $userId)
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
            ->where('products.user_id', $userId)
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
