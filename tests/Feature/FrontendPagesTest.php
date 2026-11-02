<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FrontendPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_operational_pages_render_for_authenticated_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        foreach (['/dashboard', '/products', '/suppliers', '/inventory', '/stock-transactions', '/reports', '/forecasts', '/recommendations', '/users'] as $path) {
            $this->actingAs($admin)->get($path)->assertOk();
        }
    }

    public function test_catalog_and_user_pages_are_restricted_to_admin(): void
    {
        $staff = User::factory()->create(['role' => 'warehouse_staff']);
        $this->actingAs($staff)->get('/products')->assertForbidden();
        $this->actingAs($staff)->get('/suppliers')->assertForbidden();
        $this->actingAs($staff)->get('/users')->assertForbidden();
        $this->actingAs($staff)->get('/inventory')->assertOk();
        $this->actingAs($staff)->get('/stock-transactions')->assertOk();
    }

    public function test_product_livewire_form_persists_real_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();

        Livewire::actingAs($admin)->test(\App\Livewire\Catalog\ProductManager::class)
            ->set('form.category_id', $category->id)
            ->set('form.sku', 'SKU-LIVEWIRE-001')
            ->set('form.name', 'Produk Livewire')
            ->set('form.unit', 'pcs')
            ->set('form.unit_price', 2500)
            ->set('form.reorder_point', 5)
            ->set('form.safety_stock', 2)
            ->set('form.minimum_order_quantity', 1)
            ->set('form.order_multiple', 1)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('products', ['sku' => 'SKU-LIVEWIRE-001', 'name' => 'Produk Livewire']);
    }

    public function test_supplier_page_uses_livewire_validation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Livewire::actingAs($admin)->test(\App\Livewire\Catalog\SupplierManager::class)
            ->set('form.code', '')
            ->set('form.name', '')
            ->call('save')
            ->assertHasErrors(['form.code', 'form.name']);
    }

    public function test_transaction_component_posts_and_updates_inventory(): void
    {
        $staff = User::factory()->create(['role' => 'warehouse_staff']);
        $product = Product::factory()->create();
        Inventory::create(['product_id' => $product->id]);

        Livewire::actingAs($staff)->test(\App\Livewire\Transactions\StockTransactionManager::class)
            ->set('form.product_id', $product->id)
            ->set('form.quantity', 12)
            ->set('form.occurred_at', today()->toDateString())
            ->call('askConfirmation')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('inventories', ['product_id' => $product->id, 'on_hand_quantity' => 12]);
    }
}
