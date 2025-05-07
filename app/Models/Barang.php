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
        'nama_barang',
        'kategori_id',
        'satuan',
        'merk',
        'gudang_id',
        'stok_keseluruhan',
        'harga',
        'minimum_stok',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($barang) {
            $lastId = self::withTrashed()->max('id') ?? 0;
            $barang->kode_barang = 'BRG' . str_pad($lastId + 1, 3, '0', STR_PAD_LEFT);
        });
    }


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
