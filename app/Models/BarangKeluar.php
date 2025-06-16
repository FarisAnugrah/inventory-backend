<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangKeluar extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model ini.
     * Konvensinya adalah 'barang_keluar'.
     * @var string
     */
    protected $table = 'barang_keluar';

    /**
     * Atribut yang boleh diisi secara massal (mass assignable).
     *
     * @var array
     */
    protected $fillable = [
        'kode_keluar',
        'barang_id',
        'jumlah',
        'user_id',            // Staff yang membuat permintaan
        'gudang_id',
        'tujuan_pengeluaran',
        'tanggal_keluar',
        'status',
        'approved_by',        // Manajer yang menyetujui/menolak
        'approved_at'
    ];

    /**
     * Cast (mengubah) tipe data untuk atribut tertentu.
     * Ini memastikan tanggal dan angka diperlakukan dengan benar.
     * @var array
     */
    protected $casts = [
        'tanggal_keluar' => 'date',
        'approved_at' => 'datetime',
        'jumlah' => 'integer',
    ];

    /**
     * Mendefinisikan relasi "belongsTo" ke model Barang.
     * Setiap record barang keluar terhubung ke satu barang.
     */
    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    /**
     * Mendefinisikan relasi "belongsTo" ke model User.
     * Ini untuk mengidentifikasi Staff yang membuat permintaan.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Mendefinisikan relasi "belongsTo" ke model Gudang.
     */
    public function gudang()
    {
        return $this->belongsTo(Gudang::class);
    }

    /**
     * Mendefinisikan relasi "belongsTo" ke model User.
     * Ini untuk mengidentifikasi Manajer yang menyetujui permintaan.
     * Kita menamainya 'approver' agar tidak bentrok dengan relasi 'user'.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
