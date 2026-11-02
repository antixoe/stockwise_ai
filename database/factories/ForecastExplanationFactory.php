<?php

namespace Database\Factories;

use App\Models\ForecastExplanation;
use App\Models\Forecast;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ForecastExplanation>
 */
class ForecastExplanationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['forecast_id' => Forecast::factory(), 'explainer_name' => 'shap', 'feature_contributions' => ['lag_7' => 1.2], 'status' => 'available'];
    }
}
