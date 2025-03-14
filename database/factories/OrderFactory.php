<?php

namespace Database\Factories;

use App\Models\User;
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
            'status' => OrderStatus::DELIVERED,
            'country_id' => fake()->numberBetween(1, 5),
            'product_quantity' => fake()->numberBetween(1, 5),
            'order_no' => Str::random(20),
            'shipping_address' => fake()->address,
            'order_date' => fake()->date,
            'total_amount' => fake()->randomFloat(2, 10, 100),
            'payment_method' => 'paystack',
        ];
    }
}
