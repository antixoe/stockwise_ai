<?php

namespace Database\Seeders;

use App\Models\Forecast;
use App\Models\ForecastExplanation;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\SalesHistory;
use App\Models\Supplier;
use App\Services\RestockDecisionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AiDemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::firstOrCreate(['code' => 'CAT-DEMO'], ['name' => 'Kategori Demo', 'is_active' => true]);
        $supplier = Supplier::firstOrCreate(['code' => 'SUP-DEMO'], ['name' => 'Supplier Demo', 'default_lead_time_days' => 7, 'is_active' => true]);
        $product = Product::firstOrCreate(['sku' => 'SKU-DEMO-001'], ['category_id' => $category->id, 'name' => 'Produk Demo AI', 'unit' => 'pcs', 'unit_price' => 10000, 'reorder_point' => 30, 'safety_stock' => 15, 'minimum_order_quantity' => 10, 'order_multiple' => 5, 'is_active' => true]);
        Inventory::firstOrCreate(['product_id' => $product->id], ['on_hand_quantity' => 12, 'reserved_quantity' => 0, 'incoming_quantity' => 0]);
        $product->suppliers()->syncWithoutDetaching([$supplier->id => ['lead_time_days' => 7, 'is_primary' => true, 'minimum_order_quantity' => 10, 'order_multiple' => 5]]);

        for ($daysAgo = 89; $daysAgo >= 0; $daysAgo--) {
            $date = today()->subDays($daysAgo);
            $quantity = 8 + (($daysAgo * 3) % 7) + ($date->isWeekend() ? 3 : 0);

            SalesHistory::updateOrCreate(
                ['product_id' => $product->id, 'demand_date' => $date->toDateString(), 'source' => 'demo_ai'],
                ['quantity_demanded' => $quantity, 'quantity_sold' => $quantity, 'unit_price' => $product->unit_price, 'promotion_flag' => $daysAgo % 21 === 0, 'stock_available' => max(0, 80 - $daysAgo), 'features' => ['dataset_type' => 'demo']]
            );
        }

        $runUuid = '00000000-0000-4000-8000-000000000099';
        $forecasts = collect();
        for ($day = 1; $day <= 7; $day++) {
            $forecast = Forecast::updateOrCreate(
                ['run_uuid' => $runUuid, 'product_id' => $product->id, 'forecast_date' => today()->addDays($day)->toDateString()],
                [
                    'data_as_of' => today(), 'predicted_quantity' => 12 + $day, 'lower_bound' => 9 + $day, 'upper_bound' => 15 + $day,
                    'unit' => $product->unit, 'model_name' => 'demo-baseline', 'model_version' => 'demo-v1', 'horizon' => '7',
                    'status' => 'success', 'mae' => 1.25, 'wape' => 0.12, 'input_snapshot' => ['data_type' => 'demo', 'history_rows' => 90],
                ]
            );
            $forecasts->push($forecast);
        }

        $firstForecast = $forecasts->first();
        ForecastExplanation::updateOrCreate(
            ['forecast_id' => $firstForecast->id],
            [
                'explainer_name' => 'demo-explanation', 'explainer_version' => 'demo-v1', 'status' => 'available',
                'feature_contributions' => [
                    ['feature' => 'rolling_mean_7', 'label' => 'Rata-rata demand 7 hari', 'contribution' => 2.4, 'direction' => 'positive'],
                    ['feature' => 'day_of_week', 'label' => 'Hari dalam minggu', 'contribution' => 0.8, 'direction' => 'positive'],
                    ['feature' => 'promotion', 'label' => 'Promosi', 'contribution' => -0.3, 'direction' => 'negative'],
                ],
                'base_value' => ['value' => 10.1, 'reconstructed_prediction' => 13.0],
                'limitation_note' => 'Data demo untuk preview UI. Kontribusi bukan hubungan sebab-akibat.',
            ]
        );

        app(RestockDecisionService::class)->generate($product);
        $this->command?->info('Data demo AI siap: 90 history, 7 forecast, 1 explanation, dan rekomendasi restock.');
    }
}
