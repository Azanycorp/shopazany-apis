<?php

namespace App\Models;

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
 * @property string|null $quote_no
 * @property int $product_id
 * @property float $product_quantity the MOQ of the product
 * @property float $p_unit_price preferred_unit_price
 * @property float $total_amount
 * @property array<array-key, mixed>|null $product_data
 * @property string $payment_status
 * @property string $status
 * @property string|null $type
 * @property string|null $delivery_date
 * @property string|null $shipped_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property numeric $seller_unit_price
 * @property numeric $buyer_unit_price
 * @property numeric $buyer_total_amount
 * @property numeric $seller_total_amount
 * @property-read User|null $buyer
 * @property-read Collection<int, RfqMessage> $messages
 * @property-read int|null $messages_count
 * @property-read B2BProduct|null $product
 * @property-read User|null $seller
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereBuyerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereBuyerTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereBuyerUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereDeliveryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq wherePUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereProductData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereProductQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereQuoteNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereSellerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereSellerTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereSellerUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereShippedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'buyer_id',
    'seller_id',
    'quote_no',
    'product_id',
    'product_quantity',
    'p_unit_price',
    'seller_unit_price',
    'buyer_unit_price',
    'buyer_total_amount',
    'seller_total_amount',
    'product_data',
    'total_amount',
    'payment_status',
    'status',
    'type',
    'delivery_date',
    'shipped_date',
])]
class Rfq extends Model
{
    protected function casts(): array
    {
        return [
            'product_data' => 'array',
        ];
    }

    /**
     * @return BelongsTo<B2BProduct, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(B2BProduct::class, 'product_id', 'id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id', 'id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id', 'id');
    }

    public function messages(): HasMany
    {
        return $this->HasMany(RfqMessage::class);
    }

    public static function stats()
    {
        return DB::select("SELECT
                (SELECT ROUND(SUM(`total_amount`), 2) FROM `rfqs` WHERE `status`='confirmed') AS total_sales,
                -- Today

                (SELECT ROUND(SUM(`total_amount`), 2)
                    FROM `rfqs`
                    WHERE `status`='confirmed' AND
                    DAY(created_at) = DAY(NOW())
                ) AS total_sales_today,

                -- Weekly

                (SELECT ROUND(SUM(`total_amount`), 2)
                    FROM `rfqs`
                    WHERE `status`='confirmed' AND
                    WEEK(created_at) = WEEK(NOW())
                ) AS total_sales_this_week,

                -- Monthly

                (SELECT
                    ROUND(SUM(`total_amount`), 2)
                    FROM `rfqs`
                    WHERE `status`='confirmed' AND
                        MONTH(created_at) = MONTH(NOW()) AND
                        YEAR(created_at) = YEAR(NOW())
                ) AS total_sales_this_month,

                -- Yearly

                (SELECT
                    ROUND(SUM(`total_amount`), 2)
                    FROM `rfqs`
                    WHERE `status`='confirmed' AND
                    YEAR(created_at) = YEAR(NOW())
                ) AS total_sales_this_year

                 ")[0];
    }
}
