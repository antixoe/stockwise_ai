<?php

namespace Database\Factories;

use App\Models\Forecast;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Forecast>
 */
class ForecastFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['product_id' => Product::factory(), 'run_uuid' => fake()->uuid(), 'forecast_date' => fake()->date(), 'data_as_of' => now()->toDateString(), 'predicted_quantity' => 10, 'unit' => 'pcs', 'model_name' => 'test-model', 'model_version' => 'test-1', 'horizon' => '7d', 'status' => 'eligible'];
    }
}
