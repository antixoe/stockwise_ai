<?php

namespace Tests\Feature;

use App\Jobs\GenerateForecastExplanationJob;
use App\Models\Forecast;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductSupplier;
use App\Models\Recommendation;
use App\Models\Supplier;
use App\Models\User;
use App\Services\RecommendationDecisionService;
use App\Services\RestockDecisionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Tests\TestCase;

class ExplainabilityDecisionSupportTest extends TestCase
{
    use RefreshDatabase;

    public function test_restock_formula_respects_inventory_position_minimum_and_multiple(): void
    {
        $product = Product::factory()->create(['safety_stock' => 2, 'minimum_order_quantity' => 1, 'order_multiple' => 1]);
        $supplier = Supplier::factory()->create();
        ProductSupplier::factory()->create(['product_id' => $product->id, 'supplier_id' => $supplier->id, 'lead_time_days' => 2, 'minimum_order_quantity' => 5, 'order_multiple' => 5]);
        Inventory::factory()->create(['product_id' => $product->id, 'on_hand_quantity' => 1, 'reserved_quantity' => 0, 'incoming_quantity' => 0]);
        foreach (range(0, 6) as $day) Forecast::factory()->create(['product_id' => $product->id, 'forecast_date' => today()->addDays($day), 'predicted_quantity' => 3, 'status' => 'success']);
        $recommendation = app(RestockDecisionService::class)->generate($product);
        $this->assertSame('pending', $recommendation->status);
        $this->assertEquals(10.0, (float) $recommendation->reorder_quantity);
        $this->assertEquals(6.0, (float) $recommendation->demand_during_lead_time);
        $this->assertEquals(8.0, (float) data_get($recommendation->input_snapshot, 'reorder_point'));
    }

    public function test_invalid_supplier_lead_time_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $product = Product::factory()->create();
        $supplier = Supplier::factory()->create();
        ProductSupplier::factory()->create(['product_id' => $product->id, 'supplier_id' => $supplier->id, 'lead_time_days' => 0]);
        app(RestockDecisionService::class)->generate($product);
    }

    public function test_manager_approval_is_atomic_audited_and_cannot_repeat(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $recommendation = Recommendation::factory()->create(['status' => 'pending']);
        $decision = app(RecommendationDecisionService::class)->decide($recommendation, $manager, 'approved', 12.5, 'Disetujui setelah review');
        $this->assertSame('approved', $decision->decision);
        $this->assertDatabaseHas('recommendations', ['id' => $recommendation->id, 'status' => 'approved']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'recommendation.approved', 'auditable_id' => $recommendation->id]);
        $this->expectException(InvalidArgumentException::class);
        app(RecommendationDecisionService::class)->decide($recommendation->fresh(), $manager, 'approved', 12, 'duplicate');
    }

    public function test_staff_cannot_open_recommendation_dashboard(): void
    {
        $staff = User::factory()->create(['role' => 'warehouse_staff']);
        $this->actingAs($staff)->get(route('recommendations.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('explainability.index'))->assertForbidden();
    }

    public function test_explanation_job_persists_shap_payload(): void
    {
        config(['ai.secret' => 'test-secret']);
        Http::fake(function () {
            return Http::response([
                'status' => 'success', 'explainer_name' => 'shap.TreeExplainer', 'explainer_version' => 'shap-tree-v1',
                'baseline_prediction' => 5, 'reconstructed_prediction' => 8, 'limitation_note' => 'Bukan sebab-akibat.',
                'contributions' => [['feature' => 'lag_7', 'label' => 'Demand 7 hari lalu', 'value' => 8, 'contribution' => 3, 'direction' => 'positive']],
            ]);
        });
        $forecast = Forecast::factory()->create(['status' => 'success']);
        GenerateForecastExplanationJob::dispatchSync($forecast->id);
        $this->assertDatabaseHas('forecast_explanations', ['forecast_id' => $forecast->id, 'status' => 'available', 'explainer_version' => 'shap-tree-v1']);
    }
}
