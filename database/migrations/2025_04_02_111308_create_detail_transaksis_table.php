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
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();  // Kolom ID menggunakan bigIncrements (bigInteger)
            $table->foreignId('user_id')->constrained('users');  // Foreign key ke tabel 'users'
            $table->bigInteger('total_harga');
            $table->bigInteger('uang_pembayaran');
            $table->bigInteger('uang_kembalian');
            $table->timestamp('tanggal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_transaksis');
    }
};
