<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Services\StockTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Volt\Volt;
use Tests\TestCase;

class InventoryBackendTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_requires_valid_credentials(): void
    {
        $user = User::factory()->create(['email' => 'staff@example.test', 'password' => 'secret-password', 'email_verified_at' => now()]);

        Volt::test('pages.auth.login')->set('form.email', $user->email)->set('form.password', 'wrong')->call('login')->assertHasErrors();
        Volt::test('pages.auth.login')->set('form.email', $user->email)->set('form.password', 'secret-password')->call('login')->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_inactive_user_cannot_authenticate(): void
    {
        $user = User::factory()->create(['is_active' => false]);
        Volt::test('pages.auth.login')->set('form.email', $user->email)->set('form.password', 'password')->call('login')->assertHasErrors();
        $this->assertGuest();
    }

    public function test_staff_cannot_create_product_but_admin_can(): void
    {
        $category = Category::factory()->create();
        $staff = User::factory()->create(['role' => 'warehouse_staff']);
        $this->actingAs($staff)->postJson('/internal/products', $this->productPayload($category))->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->postJson('/internal/products', $this->productPayload($category))->assertCreated()->assertJsonPath('sku', 'SKU-TEST-001');
    }

    public function test_category_product_supplier_crud_and_relationships(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertTrue($product->category->is($category));
        $product->suppliers()->attach($supplier, ['lead_time_days' => 7, 'is_primary' => true]);
        $this->assertTrue($product->suppliers->contains($supplier));
        $this->actingAs($admin)->getJson('/internal/products')->assertOk();
    }

    public function test_receipt_and_issue_update_stock_and_history(): void
    {
        [$staff, $product] = $this->staffAndProduct();
        $service = app(StockTransactionService::class);

        $service->post(['product_id' => $product->id, 'type' => 'receipt', 'quantity' => 10, 'occurred_at' => today(), 'idempotency_key' => (string) Str::uuid()], $staff->id);
        $service->post(['product_id' => $product->id, 'type' => 'issue', 'quantity' => 3, 'occurred_at' => today(), 'idempotency_key' => (string) Str::uuid()], $staff->id);

        $this->assertSame('7.000', Inventory::where('product_id', $product->id)->value('on_hand_quantity'));
        $this->assertDatabaseCount('stock_transactions', 2);
        $this->assertDatabaseHas('audit_logs', ['action' => 'stock_transaction.posted']);
    }

    public function test_issue_cannot_create_negative_stock(): void
    {
        [$staff, $product] = $this->staffAndProduct();
        $this->expectException(\App\Exceptions\InsufficientStockException::class);
        app(StockTransactionService::class)->post(['product_id' => $product->id, 'type' => 'issue', 'quantity' => 1, 'occurred_at' => today(), 'idempotency_key' => (string) Str::uuid()], $staff->id);
        $this->assertDatabaseMissing('stock_transactions', ['product_id' => $product->id]);
    }

    public function test_duplicate_submission_is_idempotent(): void
    {
        [$staff, $product] = $this->staffAndProduct();
        $key = (string) Str::uuid();
        $data = ['product_id' => $product->id, 'type' => 'receipt', 'quantity' => 5, 'occurred_at' => today(), 'idempotency_key' => $key];
        $first = app(StockTransactionService::class)->post($data, $staff->id);
        $second = app(StockTransactionService::class)->post($data, $staff->id);

        $this->assertTrue($first->is($second));
        $this->assertDatabaseCount('stock_transactions', 1);
        $this->assertSame('5.000', Inventory::where('product_id', $product->id)->value('on_hand_quantity'));
    }

    public function test_sequential_locked_updates_preserve_balance(): void
    {
        [$staff, $product] = $this->staffAndProduct();
        $service = app(StockTransactionService::class);
        foreach ([4, 6, 5] as $quantity) {
            $service->post(['product_id' => $product->id, 'type' => 'receipt', 'quantity' => $quantity, 'occurred_at' => today(), 'idempotency_key' => (string) Str::uuid()], $staff->id);
        }
        $this->assertSame('15.000', Inventory::where('product_id', $product->id)->value('on_hand_quantity'));
    }

    public function test_transaction_rolls_back_when_posting_fails(): void
    {
        [$staff, $product] = $this->staffAndProduct();
        try {
            app(StockTransactionService::class)->post(['product_id' => $product->id, 'type' => 'issue', 'quantity' => 99, 'occurred_at' => today(), 'idempotency_key' => (string) Str::uuid()], $staff->id);
        } catch (\Throwable) {
            // Expected domain failure; the assertions below verify no partial write.
        }
        $this->assertDatabaseCount('stock_transactions', 0);
        $this->assertSame('0.000', Inventory::where('product_id', $product->id)->value('on_hand_quantity'));
    }

    public function test_reversal_is_audited_without_deleting_history(): void
    {
        [$staff, $product] = $this->staffAndProduct();
        $transaction = app(StockTransactionService::class)->post(['product_id' => $product->id, 'type' => 'receipt', 'quantity' => 8, 'occurred_at' => today(), 'idempotency_key' => (string) Str::uuid()], $staff->id);
        app(StockTransactionService::class)->reverse($transaction, $staff->id, (string) Str::uuid());

        $this->assertDatabaseCount('stock_transactions', 2);
        $this->assertDatabaseHas('stock_transactions', ['id' => $transaction->id, 'status' => 'reversed']);
        $this->assertSame('0.000', Inventory::where('product_id', $product->id)->value('on_hand_quantity'));
    }

    private function staffAndProduct(): array
    {
        $staff = User::factory()->create(['role' => 'warehouse_staff']);
        $product = Product::factory()->create();
        Inventory::create(['product_id' => $product->id, 'on_hand_quantity' => 0, 'reserved_quantity' => 0, 'incoming_quantity' => 0]);
        return [$staff, $product];
    }

    private function productPayload(Category $category): array
    {
        return ['category_id' => $category->id, 'sku' => 'SKU-TEST-001', 'name' => 'Produk Test', 'unit' => 'pcs', 'unit_price' => 1000, 'reorder_point' => 5, 'safety_stock' => 2, 'minimum_order_quantity' => 1, 'order_multiple' => 1, 'is_active' => true];
    }
}
