<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $table = 'barang';

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'kategori_id',
        'gudang_id',
        'stok_keseluruhan', 
        'satuan'

    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function gudang()
    {
        return $this->belongsTo(Gudang::class);
    }

    // Di Barang.php
    public function barangMasuk()
    {
        return $this->hasMany(BarangMasuk::class);
    }
}
