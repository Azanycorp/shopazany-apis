<?php

namespace App\Models;

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
        'amount',
        'payment_method',
        'payment_status',
        'status',
        'delivery_date',
        'shipped_date',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class,'seller_id','id');
    }
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class,'buyer_id','id');
    }

    protected function casts(): array
    {
        return [
            'shipping_address' => 'array',
            'product_data' => 'array'
        ];
    }
    public static function stats()
    {
        return DB::select(
            "SELECT
                (SELECT ROUND(SUM(`amount`), 2) FROM `b2b_orders` WHERE `status`='confirmed') AS total_sales,
              -- Today

                (SELECT ROUND(SUM(`amount`), 2)
                    FROM `b2b_orders`
                    WHERE `status`='confirmed' AND
                    DAY(created_at) = DAY(NOW())
                ) AS total_sales_today,

-- Weekly

                (SELECT ROUND(SUM(`amount`), 2)
                    FROM `b2b_orders`
                    WHERE `status`='confirmed' AND
                    WEEK(created_at) = WEEK(NOW())
                ) AS total_sales_this_week,

       -- Monthly

                (SELECT
                    ROUND(SUM(`amount`), 2)
                    FROM `b2b_orders`
                    WHERE `status`='confirmed' AND
                        MONTH(created_at) = MONTH(NOW()) AND
                        YEAR(created_at) = YEAR(NOW())
                ) AS total_sales_this_month,

    -- Yearly

                (SELECT
                    ROUND(SUM(`amount`), 2)
                    FROM `b2b_orders`
                    WHERE `status`='confirmed' AND
                    YEAR(created_at) = YEAR(NOW())
                ) AS total_sales_this_year

                 "
        )[0];
    }
}
