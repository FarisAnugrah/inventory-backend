    <?php

    use Illuminate\Support\Facades\Route;
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
    use App\Http\Controllers\AdminController;

    // === PUBLIC ===
    Route::post('/login', [AuthController::class, 'login']);

    // === PROTECTED ===
    Route::middleware('auth.jwt')->group(function () {

        // === ME & LOGOUT ===
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        // === ADMIN ONLY ===
        Route::middleware('admin')->group(function () {
            // Users
            Route::get('/users', [UserController::class, 'index']);
            Route::post('/users', [UserController::class, 'store']);
            Route::get('/users/{id}', [UserController::class, 'show']);
            Route::put('/users/{id}', [UserController::class, 'update']);
            Route::delete('/users/{id}', [UserController::class, 'destroy']);

            // Gudang
            Route::get('/gudang', [GudangController::class, 'index']);
            Route::post('/gudang', [GudangController::class, 'store']);
            Route::get('/gudang/{id}', [GudangController::class, 'show']);
            Route::put('/gudang/{id}', [GudangController::class, 'update']);
            Route::delete('/gudang/{id}', [GudangController::class, 'destroy']);

            // Kategori
            Route::get('/kategori', [KategoriController::class, 'index']);
            Route::post('/kategori', [KategoriController::class, 'store']);
            Route::get('/kategori/{id}', [KategoriController::class, 'show']);
            Route::put('/kategori/{id}', [KategoriController::class, 'update']);
            Route::delete('/kategori/{id}', [KategoriController::class, 'destroy']);

            // Transaksi (lihat laporan)
            Route::get('/transaksi', [TransaksiController::class, 'index']);
            Route::get('/transaksi/{id}', [TransaksiController::class, 'show']);

            // Admin Settings
            Route::post('/admin/setting', [AdminController::class, 'settings']);
        });

        // === STAFF ONLY ===
        Route::middleware('staff')->group(function () {
            // Barang
            Route::get('/barang', [BarangController::class, 'index']);
            Route::get('/barang/{id}', [BarangController::class, 'show']);

            // Barang Masuk
            Route::get('/barang-masuk', [BarangMasukController::class, 'index']);
            Route::post('/barang-masuk', [BarangMasukController::class, 'store']);
            Route::get('/barang-masuk/{id}', [BarangMasukController::class, 'show']);
            Route::put('/barang-masuk/{id}', [BarangMasukController::class, 'update']);
            Route::delete('/barang-masuk/{id}', [BarangMasukController::class, 'destroy']);

            // Barang Keluar
            Route::get('/barang-keluar', [BarangKeluarController::class, 'index']);
            Route::post('/barang-keluar', [BarangKeluarController::class, 'store']);

            // Mutasi Gudang
            // Route::get('/mutasi-gudang', [MutasiGudangController::class, 'index']);
            // Route::post('/mutasi-gudang', [MutasiGudangController::class, 'store']);
        });

        // === MANAJER ONLY ===
        Route::middleware('manajer')->group(function () {
            // Approve Barang Keluar
            Route::put('/barang-keluar/{id}', [BarangKeluarController::class, 'update']);

            // Notifikasi
            Route::get('/notifikasi', [NotifikasiController::class, 'index']);
            Route::get('/notifikasi/{id}', [NotifikasiController::class, 'show']);
        });

        // === AKSES STAFF ATAU MANAJER ===
        Route::middleware('staffOrManajer')->group(function () { // Menggunakan middleware baru
            Route::get('/laporan/barang-masuk', [BarangMasukController::class, 'laporan']);
            Route::get('/laporan/barang-keluar', [BarangKeluarController::class, 'laporan']);
        });
    });
