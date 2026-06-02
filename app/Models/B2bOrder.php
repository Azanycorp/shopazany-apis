<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int $buyer_id
 * @property int $seller_id
 * @property int $product_id
 * @property int $product_quantity the MOQ of the product
 * @property string|null $order_no
 * @property array<array-key, mixed>|null $shipping_address
 * @property array<array-key, mixed>|null $product_data
 * @property float $total_amount
 * @property string $payment_method
 * @property string $payment_status
 * @property string $status
 * @property string|null $type
 * @property string|null $delivery_date
 * @property string|null $shipped_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property array<array-key, mixed>|null $billing_address
 * @property int|null $country_id
 * @property string|null $shipping_agent
 * @property int|null $centre_id
 * @property numeric $seller_unit_price
 * @property numeric $buyer_unit_price
 * @property numeric $buyer_total_amount
 * @property numeric $seller_total_amount
 * @property-read Collection<int, B2bProdctReview> $b2bProductReview
 * @property-read int|null $b2b_product_review_count
 * @property-read User|null $buyer
 * @property-read CollationCenter|null $collationCentre
 * @property-read Country|null $country
 * @property-read Collection<int, OrderStage> $orderStages
 * @property-read int|null $order_stages_count
 * @property-read B2BProduct|null $product
 * @property-read User|null $seller
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrder whereBillingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrder whereBuyerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrder whereBuyerTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrder whereBuyerUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrder whereCentreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrder whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrder whereDeliveryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrder whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrder wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrder wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrder whereProductData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrder whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrder whereProductQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrder whereSellerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrder whereSellerTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrder whereSellerUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrder whereShippedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrder whereShippingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrder whereShippingAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrder whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrder whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrder whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'buyer_id',
    'seller_id',
    'product_id',
    'product_quantity',
    'order_no',
    'shipping_address',
    'shipping_agent',
    'billing_address',
    'product_data',
    'total_amount',
    'seller_unit_price',
    'buyer_unit_price',
    'buyer_total_amount',
    'seller_total_amount',
    'payment_method',
    'payment_status',
    'status',
    'type',
    'delivery_date',
    'shipped_date',
    'centre_id',
    'country_id',
])]
class B2bOrder extends Model
{
    use ClearsResponseCache;

    /**
     * @return BelongsTo<B2BProduct, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(B2BProduct::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id', 'id');
    }

    /**
     * @return BelongsTo<CollationCenter, $this>
     */
    public function collationCentre(): BelongsTo
    {
        return $this->belongsTo(CollationCenter::class, 'centre_id');
    }

    /**
     * @return BelongsTo<Country, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id', 'id');
    }

    protected function casts(): array
    {
        return [
            'shipping_address' => 'array',
            'billing_address' => 'array',
            'product_data' => 'array',
        ];
    }

    /**
     * @return HasMany<OrderStage, $this>
     */
    public function orderStages(): HasMany
    {
        return $this->hasMany(OrderStage::class, 'order_id');
    }

    /**
     * @return HasMany<B2bProdctReview, $this>
     */
    public function b2bProductReview(): HasMany
    {
        return $this->hasMany(B2bProdctReview::class, 'product_id');
    }

    public static function orderStats($type = null)
    {
        return DB::selectOne("SELECT
                (SELECT ROUND(COUNT(`id`), 2) FROM `b2b_orders`) AS total_orders,
                (SELECT ROUND(COUNT(`id`), 2) FROM `b2b_orders` WHERE `status`='delivered' ) AS total_delivered,
                (SELECT ROUND(COUNT(`id`), 2) FROM `b2b_orders` WHERE `status`='cancelled' ) AS total_cancelled,
                (SELECT ROUND(COUNT(`id`), 2) FROM `b2b_orders` WHERE `status`='pending' ) AS total_pending,
                (SELECT ROUND(COUNT(`id`), 2) FROM `b2b_orders` WHERE `status`='shipped' ) AS total_shipped,


                (SELECT ROUND(SUM(`total_amount`), 2)
                    FROM `b2b_orders` WHERE status='delivered' AND `type` = ?
                ) AS total_order_delivered_amount,

                (SELECT ROUND(SUM(`total_amount`), 2)
                    FROM `b2b_orders`
                    WHERE (YEARWEEK(`created_at`) = YEARWEEK(CURDATE())) AND `type` = ?
                ) AS total_order_amount_week,

                (SELECT ROUND(COUNT(`id`), 2)
                    FROM `b2b_orders`
                    WHERE (YEARWEEK(`created_at`) = YEARWEEK(CURDATE())) AND `type` = ?
                ) AS total_order_count_week,

                (SELECT
                    ROUND(SUM(`total_amount`), 2)
                    FROM `b2b_orders`
                    WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())
                ) AS total_order_amount_month

            "
        )[0];
    }
}
