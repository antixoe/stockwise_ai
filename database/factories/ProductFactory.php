<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
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
        return ['category_id' => Category::factory(), 'sku' => fake()->unique()->bothify('SKU-####'), 'name' => fake()->words(3, true), 'unit' => 'pcs', 'unit_price' => fake()->randomFloat(4, 1000, 1000000), 'reorder_point' => 10, 'safety_stock' => 5, 'minimum_order_quantity' => 1, 'order_multiple' => 1, 'is_active' => true];
    }
}
