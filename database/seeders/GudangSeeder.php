<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GudangSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('gudang')->insert([
            ['nama_gudang' => 'Gudang Pusat', 'lokasi' => 'Jakarta'],
            ['nama_gudang' => 'Gudang Cabang', 'lokasi' => 'Surabaya'],
        ]);
    }
}
