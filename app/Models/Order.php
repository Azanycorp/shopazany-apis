<?php

namespace App\Models;

use App\Enum\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'seller_id',
        'product_id',
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
            'shipping_address' => 'array'
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->where('type', 'customer');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id')->where('type', 'seller');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'id', 'product_id');
    }

    public static function saveOrder($user, $payment, $seller, $item, $orderNo, $address, $method, $status)
    {
        $data = new self();

        $data->user_id = $user->id;
        $data->seller_id = $seller->id;
        $data->product_id = $item['product_id'] ?? $item['itemId'];
        $data->payment_id = $payment->id;
        $data->product_quantity = $item['product_quantity'] ?? $item['quantity'];
        $data->order_no = $orderNo;
        $data->shipping_address = $address;
        $data->order_date = now();
        $data->total_amount = $item['total_amount'] ?? $item['unitPrice'];
        $data->payment_method = $method;
        $data->payment_status = $status;
        $data->status = OrderStatus::PENDING;
        $data->country_id = $user->country ?? 160;

        $data->save();

        return $data;
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class,  'payment_id');
    }

}
