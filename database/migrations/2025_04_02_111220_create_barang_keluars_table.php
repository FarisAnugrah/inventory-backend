<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('barang_keluar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('barang');  // Foreign key ke tabel 'barangs'
            $table->foreignId('user_id')->constrained('users');      // Foreign key ke tabel 'users'
            $table->foreignId('gudang_id')->constrained('gudang');  // Foreign key ke tabel 'gudangs'
            $table->integer('jumlah');
            $table->timestamp('tanggal');
            $table->enum('status', ['pending', 'disetujui', 'ditolak']);
            $table->foreignId('approved_by')->nullable()->constrained('users');  // Foreign key ke tabel 'users'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_keluars');
    }
};
