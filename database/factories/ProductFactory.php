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
            'name' => $this->faker->word,
            'slug' => $this->faker->slug,
            'image' => $this->faker->imageUrl(640, 480, 'products'),
            'product_price' => $this->faker->randomFloat(2, 10, 100),
            'price' => $this->faker->randomFloat(2, 10, 100),
            'description' => $this->faker->paragraph,
            'category_id' => $this->faker->numberBetween(1, 10),
            'country_id' => $this->faker->numberBetween(1, 5),
        ];
    }
}
