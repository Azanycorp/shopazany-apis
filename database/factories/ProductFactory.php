<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word,
            'slug' => fake()->slug,
            'image' => fake()->imageUrl(640, 480, 'products'),
            'product_price' => fake()->randomFloat(2, 10, 100),
            'price' => fake()->randomFloat(2, 10, 100),
            'description' => fake()->paragraph,
            'category_id' => fake()->numberBetween(1, 10),
            'country_id' => fake()->numberBetween(1, 5),
        ];
    }
}
