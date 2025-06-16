<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Jalankan semua seeder untuk database aplikasi Anda.
     *
     * Method ini akan memanggil semua seeder lain dengan urutan yang benar
     * untuk menghindari error foreign key.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            GudangSeeder::class,
            KategoriSeeder::class,
            BarangSeeder::class
        ]);
    }
}
