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

}
