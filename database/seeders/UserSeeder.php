<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User; // Pastikan model User di-import

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Hapus data user lama untuk menghindari duplikasi jika seeder dijalankan lagi
        User::truncate();

        // Buat user untuk setiap peran
        $users = [
            [
                'name' => 'Administrator',
                'email' => 'admin@gmail.com',
                'role' => 'admin',
                'password' => Hash::make('12345678'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bos',
                'email' => 'bos@gmail.com',
                'role' => 'bos',
                'password' => Hash::make('12345678'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kasir',
                'email' => 'kasir@gmail.com',
                'role' => 'user', // Sesuai controller, peran kasir adalah 'user'
                'password' => Hash::make('12345678'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Masukkan data ke dalam database
        DB::table('users')->insert($users);
    }
}
