<?php

namespace Database\Seeders;

use App\Enum\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id' => 1,
                'user_id' => 6,
                'seller_id' => 9,
                'product_id' => 1,
                'product_quantity' => 1,
                'order_no' => Str::random(10),
                'shipping_address' => 'Lagos',
                'order_date' => now(),
                'total_amount' => 20000,
                'payment_method' => 'card',
                'payment_status' => 'success',
                'status' => OrderStatus::DELIVERED,
                'country_id' => 231,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'user_id' => 7,
                'seller_id' => 9,
                'product_id' => 1,
                'product_quantity' => 1,
                'order_no' => Str::random(10),
                'shipping_address' => 'Lagos',
                'order_date' => now(),
                'total_amount' => 20000,
                'payment_method' => 'card',
                'payment_status' => 'success',
                'status' => OrderStatus::DELIVERED,
                'country_id' => 160,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'user_id' => 11,
                'seller_id' => 9,
                'product_id' => 1,
                'product_quantity' => 1,
                'order_no' => Str::random(10),
                'shipping_address' => 'Lagos',
                'order_date' => now(),
                'total_amount' => 20000,
                'payment_method' => 'card',
                'payment_status' => 'success',
                'status' => OrderStatus::DELIVERED,
                'country_id' => 108,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        Order::insert($data);
    }
}
