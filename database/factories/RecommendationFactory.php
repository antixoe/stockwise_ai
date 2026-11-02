<?php

namespace Database\Factories;

use App\Models\Recommendation;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Recommendation>
 */
class RecommendationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['product_id' => Product::factory(), 'recommendation_uuid' => fake()->uuid(), 'status' => 'pending', 'recommended_order_date' => today(), 'reorder_quantity' => 10, 'demand_during_lead_time' => 10, 'on_hand_at_calculation' => 0, 'reserved_at_calculation' => 0, 'incoming_at_calculation' => 0, 'safety_stock_at_calculation' => 5, 'lead_time_days' => 7, 'rule_version' => 'v1', 'input_snapshot' => [], 'rationale' => 'Test', 'generated_at' => now()];
    }
}
