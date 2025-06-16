<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarangSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('barang')->insert([
            [
                'kode_barang' => 'B0001',
                'nama_barang' => 'Laptop Pro 14 inch',
                'kategori_id' => 1, // Elektronik
                'satuan' => 'PCS',
                'gudang_id' => 1, // Gudang Pusat
                'stok_keseluruhan' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_barang' => 'B0002',
                'nama_barang' => 'Kertas A4 70gr Rim',
                'kategori_id' => 2, // ATK
                'satuan' => 'ROL', // Contoh menggunakan 'ROL'
                'gudang_id' => 1, // Gudang Pusat
                'stok_keseluruhan' => 200,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_barang' => 'B0003',
                'nama_barang' => 'Meja Kerja Kantor',
                'kategori_id' => 3, // Perabotan
                'satuan' => 'PCS',
                'gudang_id' => 2, // Gudang Cabang
                'stok_keseluruhan' => 25,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
