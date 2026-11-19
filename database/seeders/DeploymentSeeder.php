<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DeploymentSeeder extends Seeder
{
    /**
     * Seed only the minimum operational data required by a fresh deployment.
     * Existing operational/customer data is intentionally never exported.
     */
    public function run(): void
    {
        DB::table('harga_settings')->updateOrInsert(
            ['id' => 1],
            [
                'harga_per_jam' => 30000,
                'harga_per_jam_siang' => 25000,
                'harga_per_jam_malam' => 30000,
                'harga_membership' => 0,
                'diskon_member_default' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        foreach (range(1, 21) as $number) {
            DB::table('mejas')->updateOrInsert(
                ['nama_meja' => "Meja {$number}"],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $email = env('DEPLOY_ADMIN_EMAIL');
        $password = env('DEPLOY_ADMIN_PASSWORD');

        if (! $email && ! $password) {
            return;
        }

        if (! $email || ! $password) {
            throw new RuntimeException('DEPLOY_ADMIN_EMAIL and DEPLOY_ADMIN_PASSWORD must be set together.');
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Administrator',
                'role' => 'admin',
                'password' => Hash::make($password),
            ]
        );
    }
}
