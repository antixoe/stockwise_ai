<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['code' => fake()->unique()->bothify('SUP-####'), 'name' => fake()->company(), 'contact_name' => fake()->name(), 'email' => fake()->companyEmail(), 'phone' => fake()->phoneNumber(), 'default_lead_time_days' => 7, 'is_active' => true];
    }
}
