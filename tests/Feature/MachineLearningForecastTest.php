<?php

namespace Tests\Feature;

use App\Jobs\RunForecastInferenceJob;
use App\Livewire\Forecasts\ForecastManager;
use App\Models\Product;
use App\Models\SalesHistory;
use App\Models\User;
use App\Services\AiForecastClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class MachineLearningForecastTest extends TestCase
{
    use RefreshDatabase;

    public function test_warehouse_staff_is_blocked_from_forecasting_page(): void
    {
        $user = User::factory()->create(['role' => 'warehouse_staff']);
        $this->actingAs($user)->get(route('forecasts.index'))->assertForbidden();
    }

    public function test_authorized_manager_can_schedule_forecast(): void
    {
        Queue::fake();
        $user = User::factory()->create(['role' => 'manager']);
        $product = Product::factory()->create();
        Livewire::actingAs($user)->test(ForecastManager::class)->set('productId', $product->id)->set('horizon', 30)->call('runForecast');
        Queue::assertPushed(RunForecastInferenceJob::class);
    }

    public function test_successful_inference_is_saved(): void
    {
        config(['ai.secret' => 'test-secret']);
        Http::fake(function () {
            return Http::response(['status' => 'success', 'request_id' => 'trace-1', 'data_as_of' => '2026-10-20', 'model_name' => 'random_forest', 'model_version' => 'test-v1', 'metrics' => ['mae' => 1.2, 'wape' => 0.1], 'predictions' => [['date' => '2026-10-21', 'predicted_quantity' => 3.5]]]);
        });
        $product = Product::factory()->create();
        SalesHistory::factory()->create(['product_id' => $product->id, 'demand_date' => '2026-10-20', 'quantity_demanded' => 4]);
        RunForecastInferenceJob::dispatchSync($product->id, 7);
        $this->assertDatabaseHas('forecasts', ['product_id' => $product->id, 'forecast_date' => '2026-10-21', 'model_version' => 'test-v1', 'status' => 'success']);
    }

    public function test_insufficient_data_does_not_create_fake_forecast(): void
    {
        config(['ai.secret' => 'test-secret']);
        Http::fake(Http::response(['status' => 'insufficient_data', 'predictions' => []]));
        $product = Product::factory()->create();
        RunForecastInferenceJob::dispatchSync($product->id, 7);
        $this->assertDatabaseCount('forecasts', 0);
    }

    public function test_explain_request_uses_explain_endpoint(): void
    {
        config(['ai.secret' => 'test-secret', 'ai.explain_url' => 'http://ai.test/v1/explain']);
        Http::fake(Http::response(['status' => 'failed']));

        app(AiForecastClient::class)->explain('1', '2026-10-20');

        Http::assertSent(fn ($request) => $request->url() === 'http://ai.test/v1/explain');
    }
}
