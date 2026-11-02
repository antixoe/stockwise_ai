<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevelopmentSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('STOCKWISE_DEMO_PASSWORD');
        if (!$password) {
            $this->command?->warn('STOCKWISE_DEMO_PASSWORD tidak diset; user demo tidak dibuat.');
            return;
        }

        foreach ([['admin@stockwise.local', 'Admin Demo', 'admin'], ['gudang@stockwise.local', 'Staff Gudang Demo', 'warehouse_staff'], ['manajer@stockwise.local', 'Manajer Demo', 'manager']] as [$email, $name, $role]) {
            User::updateOrCreate(['email' => $email], ['name' => $name, 'role' => $role, 'is_active' => true, 'password' => Hash::make($password), 'email_verified_at' => now()]);
        }

        $category = Category::firstOrCreate(['code' => 'CAT-DEMO'], ['name' => 'Kategori Demo', 'is_active' => true]);
        $supplier = Supplier::firstOrCreate(['code' => 'SUP-DEMO'], ['name' => 'Supplier Demo', 'default_lead_time_days' => 7, 'is_active' => true]);
        $product = Product::firstOrCreate(['sku' => 'SKU-DEMO-001'], ['category_id' => $category->id, 'name' => 'Produk Demo', 'unit' => 'pcs', 'unit_price' => 10000, 'reorder_point' => 10, 'safety_stock' => 5, 'minimum_order_quantity' => 1, 'order_multiple' => 1, 'is_active' => true]);
        $product->suppliers()->syncWithoutDetaching([$supplier->id => ['lead_time_days' => 7, 'is_primary' => true]]);
        $product->inventory()->firstOrCreate([], ['on_hand_quantity' => 0, 'reserved_quantity' => 0, 'incoming_quantity' => 0]);
    }
}
