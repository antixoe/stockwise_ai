<?php

namespace Database\Factories;

use App\Models\SalesHistory;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalesHistory>
 */
class SalesHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['product_id' => Product::factory(), 'demand_date' => fake()->date(), 'quantity_demanded' => fake()->randomFloat(3, 1, 50), 'quantity_sold' => fake()->randomFloat(3, 1, 50), 'unit_price' => 1000, 'promotion_flag' => false, 'stock_available' => 50, 'source' => 'test'];
    }
}
