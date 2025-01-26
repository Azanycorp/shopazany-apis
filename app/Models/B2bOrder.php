<?php

namespace App\Models;

use App\Enum\OrderStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class B2bOrder extends Model
{
    protected $fillable = [
        'buyer_id',
        'seller_id',
        'product_id',
        'product_quantity',
        'order_no',
        'shipping_address',
        'product_data',
        'total_amount',
        'payment_method',
        'payment_status',
        'status',
        'delivery_date',
        'shipped_date',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id', 'id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id', 'id');
    }

    protected function casts(): array
    {
        return [
            'shipping_address' => 'array',
            'product_data' => 'array'
        ];
    }

   

    public static function orderStats()
    {
        return DB::select(
            "SELECT
                (SELECT ROUND(COUNT(`id`), 2) FROM `b2b_orders`) AS total_orders,
                (SELECT ROUND(COUNT(`id`), 2) FROM `b2b_orders` WHERE `status`='delivered' ) AS total_delivered,
                (SELECT ROUND(COUNT(`id`), 2) FROM `b2b_orders` WHERE `status`='pending' ) AS total_pending,
                (SELECT ROUND(COUNT(`id`), 2) FROM `b2b_orders` WHERE `status`='shipped' ) AS total_shipped,


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
                    WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())
                ) AS total_order_amount_month

            "
        )[0];
    }
}
