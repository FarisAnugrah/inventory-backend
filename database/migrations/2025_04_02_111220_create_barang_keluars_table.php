<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ini akan MEMBUAT tabel 'barang_keluar' dengan semua kolom yang diperlukan.
     */
    public function up(): void
    {
        // Menggunakan Schema::create untuk membuat tabel baru
        Schema::create('barang_keluar', function (Blueprint $table) {
            $table->id();
            $table->string('kode_keluar')->unique();
            $table->foreignId('barang_id')->constrained('barang')->onDelete('cascade');
            $table->foreignId('gudang_id')->constrained('gudang')->onDelete('cascade');
            $table->integer('jumlah');
            $table->text('tujuan_pengeluaran')->nullable();
            $table->date('tanggal_keluar');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            // Kolom untuk approval oleh Manajer
            $table->foreignId('user_id')->comment('ID Staff yang membuat permintaan')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->comment('ID Manajer')->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();

            // Kolom timestamp otomatis dari Laravel
            $table->timestamps(); // Membuat created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     * Ini akan menghapus tabel jika migrasi dibatalkan.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_keluar');
    }
};
