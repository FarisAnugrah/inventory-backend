<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk mengisi data user.
     */
    public function run(): void
    {
        // Hapus data user yang ada sebelumnya untuk menghindari duplikasi
        // User::truncate(); // Opsional, gunakan jika Anda ingin tabel bersih setiap kali seeder dijalankan

        DB::table('users')->insert([
            // User 1: Admin
            [
                'name' => 'Admin Utama',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('password123'),
                'role' => 'admin', // Pastikan kolom 'role' ada di tabel 'users' Anda
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // User 2: Manajer
            [
                'name' => 'Budi Manajer',
                'email' => 'manajer@gmail.com',
                'password' => Hash::make('password123'),
                'role' => 'manajer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // User 3: Staff
            [
                'name' => 'Citra Staff',
                'email' => 'staff@gmail.com',
                'password' => Hash::make('password123'),
                'role' => 'staff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
