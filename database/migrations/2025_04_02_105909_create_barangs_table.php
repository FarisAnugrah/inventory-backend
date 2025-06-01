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
        Schema::create('barang', function (Blueprint $table) {
            $table->id();  // Kolom ID dengan bigIncrements (bigInteger)
            $table->string('kode_barang')->nullable()->unique();
            $table->string('nama_barang');
            $table->foreignId('kategori_id')->constrained('kategori');  // Foreign key ke kategori
            $table->string('satuan');
            $table->foreignId('gudang_id')->constrained('gudang');  // Foreign key ke gudang
            $table->integer('stok_keseluruhan');
            $table->timestamp('deleted_at')->nullable(); // Soft delete
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};
