<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GudangController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\BarangKeluarController;
use App\Http\Controllers\MutasiGudangController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\TransaksiController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\StaffMiddleware;
use App\Http\Middleware\ManajerMiddleware;
use App\Http\Controllers\AdminController;

// Rute untuk autentikasi (login, register, dll)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Middleware untuk autentikasi JWT
Route::middleware('auth:api')->group(function () {
    // Rute untuk mendapatkan informasi pengguna yang sedang login
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rute Admin (Admin hanya bisa mengakses ini)
    Route::middleware(AdminMiddleware::class)->group(function () {
        Route::resource('users', UserController::class);  // Mengelola Pengguna
        Route::resource('gudang', GudangController::class);  // Mengelola Gudang
        Route::resource('kategori', KategoriController::class);  // Mengelola Kategori Barang
        Route::resource('transaksi', TransaksiController::class);  // Melihat Laporan

        // Hanya Admin yang bisa membuat atau menghapus data
        Route::post('/admin/setting', [AdminController::class, 'settings']);
    });

    // Rute Staff (Staff hanya bisa mengakses ini)
    Route::middleware(StaffMiddleware::class)->group(function () {
        Route::resource('barang-masuk', BarangMasukController::class);  // Mencatat Barang Masuk
        Route::resource('barang-keluar', BarangKeluarController::class);  // Mencatat Barang Keluar

        // Jika mutasi gudang hanya untuk staff, aktifkan rute berikut
        // Route::resource('mutasi-gudang', MutasiGudangController::class);  // Melakukan Mutasi Barang
    });

    // Rute Manajer (Manajer hanya bisa mengakses ini)
    Route::middleware(ManajerMiddleware::class)->group(function () {
        // Hanya Manajer yang bisa menyetujui atau melihat transaksi tertentu
        Route::resource('barang-keluar', BarangKeluarController::class);  // Menyetujui Permintaan Barang Keluar
        Route::resource('notifikasi', NotifikasiController::class);  // Menerima Notifikasi Stok Habis
        Route::resource('transaksi', TransaksiController::class);  // Melihat Laporan Barang Masuk & Keluar
    });
});
