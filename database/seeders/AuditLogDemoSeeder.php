<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class AuditLogDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'superadmin@example.com')->first()
            ?? User::where('role', 'admin')->first();

        if (! $user) {
            $this->command?->error('Buat akun admin terlebih dahulu sebelum mengisi audit log demo.');

            return;
        }

        $entries = [
            ['request_id' => '00000000-0000-4000-8000-000000000001', 'action' => 'auth.login', 'new_values' => ['method' => 'password'], 'created_at' => now()->subMinutes(8)],
            ['request_id' => '00000000-0000-4000-8000-000000000002', 'action' => 'product.created', 'new_values' => ['sku' => 'SKU-DEMO-001', 'name' => 'Produk Demo'], 'created_at' => now()->subMinutes(22)],
            ['request_id' => '00000000-0000-4000-8000-000000000003', 'action' => 'supplier.updated', 'new_values' => ['supplier' => 'Supplier Demo', 'lead_time_days' => 7], 'created_at' => now()->subHours(1)],
            ['request_id' => '00000000-0000-4000-8000-000000000004', 'action' => 'stock.receipt.posted', 'new_values' => ['sku' => 'SKU-DEMO-001', 'quantity' => 48], 'created_at' => now()->subHours(2)],
            ['request_id' => '00000000-0000-4000-8000-000000000005', 'action' => 'stock.issue.posted', 'new_values' => ['sku' => 'SKU-DEMO-001', 'quantity' => 6], 'created_at' => now()->subHours(3)],
            ['request_id' => '00000000-0000-4000-8000-000000000006', 'action' => 'forecast.scheduled', 'new_values' => ['horizon' => 7, 'model_version' => 'demo-v1'], 'created_at' => now()->subHours(5)],
            ['request_id' => '00000000-0000-4000-8000-000000000007', 'action' => 'recommendation.generated', 'new_values' => ['sku' => 'SKU-DEMO-001', 'status' => 'pending'], 'created_at' => now()->subHours(7)],
            ['request_id' => '00000000-0000-4000-8000-000000000008', 'action' => 'recommendation.approved', 'new_values' => ['sku' => 'SKU-DEMO-001', 'quantity' => 24], 'created_at' => now()->subHours(9)],
            ['request_id' => '00000000-0000-4000-8000-000000000009', 'action' => 'user.updated', 'new_values' => ['role' => 'admin', 'is_active' => true], 'created_at' => now()->subDay()],
            ['request_id' => '00000000-0000-4000-8000-000000000010', 'action' => 'auth.logout', 'new_values' => ['method' => 'session'], 'created_at' => now()->subDay()->subMinutes(5)],
        ];

        foreach ($entries as $entry) {
            AuditLog::updateOrCreate(
                ['request_id' => $entry['request_id']],
                [
                    'user_id' => $user->id,
                    'action' => $entry['action'],
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'StockWise Demo Seeder',
                    'old_values' => [],
                    'new_values' => $entry['new_values'],
                    'created_at' => $entry['created_at'],
                    'updated_at' => $entry['created_at'],
                ],
            );
        }

        $this->command?->info(count($entries).' audit log demo siap digunakan.');
    }
}
