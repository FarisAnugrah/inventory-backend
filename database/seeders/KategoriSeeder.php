<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('kategori')->insert([
            ['nama_kategori' => 'Elektronik'],
            ['nama_kategori' => 'ATK (Alat Tulis Kantor)'],
            ['nama_kategori' => 'Perabotan'],
        ]);
    }
}
