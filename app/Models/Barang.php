<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Barang extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'barang'; // Nama tabel biar eksplisit

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'kategori_id',
        'gudang_id',
        'stok_kesuluruhan',
        'harga',
        'minimum_stok',
    ];

    // Relasi ke Kategori
    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    // Relasi ke Gudang
    public function gudang()
    {
        return $this->belongsTo(Gudang::class);
    }
}
