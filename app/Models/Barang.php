<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    // Tentukan kolom yang dapat diisi secara massal
    protected $fillable = [
        'nama_barang', 'kategori_id', 'gudang_id', 'stok_kesuluruhan', 'harga', 'minimum_stok',
    ];
}

