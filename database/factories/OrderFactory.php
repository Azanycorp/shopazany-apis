<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Product;
use App\Enum\OrderStatus;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'seller_id' => User::factory(),
            'product_id' => Product::factory(),
            'status' => OrderStatus::DELIVERED,
            'country_id' => $this->faker->numberBetween(1, 5),
            'product_quantity' => $this->faker->numberBetween(1, 5),
            'total_amount' => $this->faker->randomFloat(2, 10, 500),
            'order_no' => Str::random(20),
            'shipping_address' => $this->faker->address,
            'order_date' => $this->faker->date,
            'total_amount' => $this->faker->randomFloat(2, 10, 100),
            'payment_method' => 'paystack',
        ];
    }
}
