<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class B2bOrder extends Model
{
    use ClearsResponseCache;

    protected $fillable = [
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
        'payment_method',
        'payment_status',
        'status',
        'type',
        'delivery_date',
        'shipped_date',
        'centre_id',
        'country_id',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(B2BProduct::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id', 'id');
    }

    public function collationCentre(): BelongsTo
    {
        return $this->belongsTo(CollationCenter::class, 'centre_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

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

    public function b2bProductReview(): HasMany
    {
        return $this->hasMany(B2bProdctReview::class, 'product_id');
    }

    public static function orderStats($type = null)
    {
        if ($type) {
            return DB::selectOne(
                "SELECT
                (SELECT ROUND(COUNT(`id`), 2) FROM `b2b_orders` WHERE `type` = ?) AS total_orders,
                (SELECT ROUND(COUNT(`id`), 2) FROM `b2b_orders` WHERE `status`='delivered' AND `type` = ?) AS total_delivered,
                (SELECT ROUND(COUNT(`id`), 2) FROM `b2b_orders` WHERE `status`='cancelled' AND `type` = ?) AS total_cancelled,
                (SELECT ROUND(COUNT(`id`), 2) FROM `b2b_orders` WHERE `status`='pending' AND `type` = ?) AS total_pending,
                (SELECT ROUND(COUNT(`id`), 2) FROM `b2b_orders` WHERE `status`='shipped' AND `type` = ?) AS total_shipped,

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
                    WHERE MONTH(created_at) = MONTH(NOW())
                    AND YEAR(created_at) = YEAR(NOW()) AND `type` = ?
                ) AS total_order_amount_month",
                [$type, $type, $type, $type, $type, $type, $type, $type, $type]
            );
        }

        return DB::selectOne(
            "SELECT
            (SELECT ROUND(COUNT(`id`), 2) FROM `b2b_orders`) AS total_orders,
            (SELECT ROUND(COUNT(`id`), 2) FROM `b2b_orders` WHERE `status`='delivered') AS total_delivered,
            (SELECT ROUND(COUNT(`id`), 2) FROM `b2b_orders` WHERE `status`='cancelled') AS total_cancelled,
            (SELECT ROUND(COUNT(`id`), 2) FROM `b2b_orders` WHERE `status`='pending') AS total_pending,
            (SELECT ROUND(COUNT(`id`), 2) FROM `b2b_orders` WHERE `status`='shipped') AS total_shipped,

            (SELECT ROUND(SUM(`total_amount`), 2)
                FROM `b2b_orders` WHERE status='delivered'
            ) AS total_order_delivered_amount,

            (SELECT ROUND(SUM(`total_amount`), 2)
                FROM `b2b_orders`
                WHERE (YEARWEEK(`created_at`) = YEARWEEK(CURDATE()))
            ) AS total_order_amount_week,

            (SELECT ROUND(COUNT(`id`), 2)
                FROM `b2b_orders`
                WHERE (YEARWEEK(`created_at`) = YEARWEEK(CURDATE()))
            ) AS total_order_count_week,

            (SELECT
                ROUND(SUM(`total_amount`), 2)
                FROM `b2b_orders`
                WHERE MONTH(created_at) = MONTH(NOW())
                AND YEAR(created_at) = YEAR(NOW())
            ) AS total_order_amount_month"
        );
    }
}
