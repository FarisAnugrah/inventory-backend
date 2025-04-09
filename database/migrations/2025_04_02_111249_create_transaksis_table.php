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
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi');
            $table->foreignId('user_id')->constrained();
            $table->bigInteger('total_harga');
            $table->bigInteger('uang_pembayaran');
            $table->bigInteger('uang_kembalian');
            $table->timestamp('tanggal');
            $table->timestamp('deleted_at')->nullable(); // Soft delete
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
