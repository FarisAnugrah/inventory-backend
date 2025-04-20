<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gudang extends Model
{
    use HasFactory;

    protected $table = 'gudang'; // pastikan sama dengan nama tabel
    protected $fillable = [
        'nama_gudang',
        'lokasi',
    ];

    public function barang()
    {
        return $this->hasMany(Barang::class);
    }
}
