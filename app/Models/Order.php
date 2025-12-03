<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int $id
 * @property-read \Illuminate\Database\Eloquent\Relations\Pivot $pivot
 * @property-read int $pivot_variation_id
 * @property-read int $pivot_product_quantity
 * @property-read float $pivot_price
 * @property-read float $pivot_sub_total
 * @property-read string $pivot_status
 */
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_id',
        'product_quantity',
        'order_no',
        'shipping_address',
        'order_date',
        'total_amount',
        'payment_method',
        'payment_status',
        'status',
        'country_id',
    ];

    protected function casts(): array
    {
        return [
            'shipping_address' => 'array',
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->where('type', 'customer');
    }

    // Deprecated method - Do not use
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, $this>
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id')->where('type', 'seller');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<
     *     \App\Models\Product,
     *     $this,
     *     \App\Models\Pivots\OrderItemPivot,
     *     'pivot'
     * >
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'order_items')
            ->withoutGlobalScope('in_stock')
            ->using(\App\Models\Pivots\OrderItemPivot::class)
            ->withPivot([
                'id',
                'product_id',
                'variation_id',
                'product_quantity',
                'price',
                'sub_total',
                'status',
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\ShopCountry, $this>
     */
    public function shopCountry(): BelongsTo
    {
        return $this->belongsTo(ShopCountry::class, 'country_id', 'country_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'id', 'payment_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\OrderActivity, $this>
     */
    public function orderActivities(): HasMany
    {
        return $this->hasMany(OrderActivity::class, 'order_id');
    }

    public static function withRelationShips()
    {
        return self::with([
            'user.userShippingAddress',
            'products' => function ($pQuery) {
                $pQuery->withoutGlobalScope('in_stock')
                    ->with([
                        'user',
                        'category',
                        'shopCountry',
                        'productVariations' => function ($vQuery) {
                            $vQuery->with([
                                'product' => function ($prodQuery) {
                                    $prodQuery->withoutGlobalScope('in_stock')
                                        ->with('shopCountry');
                                },
                            ]);
                        },
                    ]);
            },
            'orderActivities',
        ]);
    }
}
