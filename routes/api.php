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

// 🔐 === AUTHENTICATION (Public Access) ===
// Rute ini bisa diakses tanpa token
Route::post('/register', [AuthController::class, 'register'])->name('register'); // Register user baru (admin/staff/manajer)
Route::post('/login', [AuthController::class, 'login'])->name('login'); // Login dan dapatkan JWT token

// 🛡️ === JWT AUTH PROTECTED ROUTES (Harus login dengan token JWT) ===
Route::middleware('auth.jwt')->group(function () {

    // 🔎 Info akun saat ini + logout
    Route::get('/me', [AuthController::class, 'me']); // Ambil data user yang sedang login
    Route::post('/logout', [AuthController::class, 'logout']); // Logout dan invalidate token

    // 🧑‍💼 === ADMIN ONLY ===
    // Semua route dalam blok ini hanya bisa diakses oleh user dengan role `admin`
    Route::middleware('admin')->group(function () {
        Route::resource('users', UserController::class); // CRUD user lain
        Route::resource('gudang', GudangController::class); // CRUD data gudang
        Route::resource('kategori', KategoriController::class); // CRUD kategori barang
        Route::resource('transaksi', TransaksiController::class); // Lihat laporan semua transaksi
        Route::post('/admin/setting', [AdminController::class, 'settings']); // Setting khusus admin
    });

    // 🧑‍🔧 === STAFF ONLY ===
    // Digunakan untuk staf gudang yang bertugas mencatat aktivitas barang
    Route::middleware('staff')->group(function () {
        Route::resource('barang-masuk', BarangMasukController::class); // Tambah atau lihat barang masuk
        Route::resource('barang-keluar', BarangKeluarController::class)->only(['index', 'store']); // Catat permintaan barang keluar

        // Jika mutasi antar gudang hanya dilakukan staff, bisa aktifkan ini
        // Route::resource('mutasi-gudang', MutasiGudangController::class); // Mutasi barang antar gudang
    });

    // 🧑‍💼 === MANAJER ONLY ===
    // Manajer hanya memantau dan menyetujui transaksi (tidak melakukan CRUD data utama)
    Route::middleware('manajer')->group(function () {
        Route::resource('barang-keluar', BarangKeluarController::class)->only(['update']); // Setujui permintaan barang keluar
        Route::resource('notifikasi', NotifikasiController::class); // Lihat notifikasi stok habis atau lainnya
        Route::resource('transaksi', TransaksiController::class); // Lihat laporan transaksi
    });
});
