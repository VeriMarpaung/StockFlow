<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name'        => fake()->words(3, true),
            'sku'         => strtoupper(fake()->unique()->bothify('??-###')),
            'description' => fake()->optional()->sentence(),
            'price'       => fake()->numberBetween(10000, 5000000),
            'stock'       => fake()->numberBetween(0, 200),
            'threshold'   => fake()->numberBetween(5, 30),
            'version'     => 0,
        ];
    }
}
