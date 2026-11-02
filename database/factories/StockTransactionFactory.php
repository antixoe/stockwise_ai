<?php

namespace Database\Factories;

use App\Models\StockTransaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockTransaction>
 */
class StockTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['product_id' => Product::factory(), 'user_id' => User::factory(), 'idempotency_key' => fake()->uuid(), 'type' => 'receipt', 'status' => 'posted', 'quantity' => 1, 'unit_cost' => 1000, 'occurred_at' => now()->toDateString(), 'posted_at' => now()];
    }
}
