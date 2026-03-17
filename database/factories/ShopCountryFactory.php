<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\ShopCountry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShopCountry>
 */
class ShopCountryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'country_id' => Country::factory(),
            'name' => fake()->country(),
            'flag' => fake()->imageUrl(),
            'currency' => fake()->currencyCode(),
        ];
    }
}
