<?php

namespace App\Models;

use App\Enum\OrderStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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
    //public static function saveOrder($user, $payment, $seller, $item, $orderNo, $address, $method, $status)
    public static function saveOrder($user, $payment, $seller, $item, $orderNo, $method, $status)
    {
        $data = new self();

        $data->user_id = $user->id;
        $data->seller_id = $seller->id;
        $data->product_id = $item['product_id'] ?? $item['itemId'];
        $data->payment_id = $payment->id;
        $data->product_quantity = $item['product_quantity'] ?? $item['quantity'];
        $data->order_no = $orderNo;
        // $data->shipping_address = $address;
        $data->order_date = now();
        $data->total_amount = $item['total_amount'] ?? $item['unitPrice'];
        $data->payment_method = $method;
        $data->payment_status = $status;
        $data->status = OrderStatus::PENDING;
        $data->country_id = $user->country ?? 160;

        $data->save();

        return $data;
    }

    public static function orderStats()
    {
        return DB::select(
            "SELECT
                (SELECT ROUND(COUNT(`id`), 2) FROM `b2b_orders`) AS total_orders,
                (SELECT ROUND(COUNT(`id`), 2) FROM `b2b_orders` WHERE `status`='delivered' ) AS total_delivered,
                (SELECT ROUND(COUNT(`id`), 2) FROM `b2b_orders` WHERE `status`='pending' ) AS total_pending,
                (SELECT ROUND(COUNT(`id`), 2) FROM `b2b_orders` WHERE `status`='shipped' ) AS total_shipped,


                (SELECT ROUND(SUM(`amount`), 2)
                    FROM `b2b_orders` WHERE status='delivered'
                ) AS total_order_delivered_amount,


                (SELECT ROUND(SUM(`amount`), 2)
                    FROM `b2b_orders`
                    WHERE (YEARWEEK(`created_at`) = YEARWEEK(CURDATE()))
                ) AS total_order_amount_week,

                (SELECT ROUND(COUNT(`id`), 2)
                    FROM `b2b_orders`
                    WHERE (YEARWEEK(`created_at`) = YEARWEEK(CURDATE()))
                ) AS total_order_count_week,

                (SELECT
                    ROUND(SUM(`amount`), 2)
                    FROM `b2b_orders`
                    WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())
                ) AS total_order_amount_month

            "
        )[0];
    }
}
