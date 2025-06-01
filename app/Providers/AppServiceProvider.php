<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate; // <-- TAMBAHKAN IMPORT INI
use App\Models\User;  

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
     public function boot(): void
    {
        // Definisikan Gate untuk melihat laporan di sini
        Gate::define('view-report', function (User $user) {
            // Logika untuk memeriksa peran user. SESUAIKAN DENGAN IMPLEMENTASI ANDA!
            // Contoh 1: Jika User model memiliki properti 'role'
            // (misalnya 'staff', 'manajer', 'admin')
            if (isset($user->role)) { // Pastikan properti 'role' ada
                 return $user->role === 'staff' || $user->role === 'manajer';
            }

            // Contoh 2: Jika Anda menggunakan method hasRole() pada model User
            // if (method_exists($user, 'hasRole')) {
            //     return $user->hasRole('staff') || $user->hasRole('manajer');
            // }

            // Contoh 3: Jika Anda menggunakan method hasAnyRole()
            // if (method_exists($user, 'hasAnyRole')) {
            //     return $user->hasAnyRole(['staff', 'manajer']);
            // }

            // Default: tolak akses jika tidak ada kondisi yang terpenuhi
            return false;
        });
    }
}
