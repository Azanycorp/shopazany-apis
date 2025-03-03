<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory, ClearsResponseCache;

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
        'usd_price',
        'default_currency',
        'current_stock_quantity',
        'minimum_order_quantity',
        'image',
        'added_by',
        'country_id',
        'is_featured',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }

    public function productimages(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function shopCountry(): BelongsTo
    {
        return $this->belongsTo(ShopCountry::class, 'country_id', 'country_id');
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class, 'product_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'product_id');
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class, 'size_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class, 'color_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class, 'product_id');
    }

    public function productReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class, 'product_id');
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
            ->withCount(['orders as sold_count' => function ($query) {
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
            ->withCount(['orders as sold_count' => function ($query) {
                $query->select(DB::raw('COUNT(*)'));
            }])
            ->withCount(['wishlists as wishlist_count' => function ($query) {
                $query->select(DB::raw('COUNT(*)'));
            }])
            ->groupBy('products.id', 'products.name', 'products.price', 'products.image', 'products.user_id')
            ->orderByDesc('wishlist_count')
            ->orderByDesc('average_rating')
            ->orderByDesc('sold_count');
    }
}
