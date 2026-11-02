<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperadminDemoSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('STOCKWISE_SUPERADMIN_PASSWORD');

        if (! $password) {
            $this->command?->error('STOCKWISE_SUPERADMIN_PASSWORD belum diset; akun superadmin dummy tidak dibuat.');

            return;
        }

        User::where('email', 'superadmin@stockwise.local')
            ->update(['email' => 'superadmin@example.com']);

        User::updateOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Superadmin Demo',
                'role' => 'admin',
                'is_active' => true,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ],
        );

        $this->command?->info('Akun Superadmin Demo siap digunakan.');
    }
}
