<?php

namespace Database\Factories;

use App\Models\RecommendationDecision;
use App\Models\Recommendation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecommendationDecision>
 */
class RecommendationDecisionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['recommendation_id' => Recommendation::factory(), 'user_id' => User::factory(), 'decision' => 'approved', 'decision_snapshot' => [], 'decided_at' => now()];
    }
}
