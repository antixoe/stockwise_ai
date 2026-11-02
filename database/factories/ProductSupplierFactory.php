<?php

namespace Database\Factories;

use App\Models\ProductSupplier;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductSupplier>
 */
class ProductSupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['product_id' => Product::factory(), 'supplier_id' => Supplier::factory(), 'lead_time_days' => 7, 'minimum_order_quantity' => 1, 'order_multiple' => 1, 'last_unit_cost' => 1000, 'is_primary' => true];
    }
}
