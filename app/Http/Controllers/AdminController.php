<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    // Method untuk menangani pengaturan yang hanya bisa diakses oleh admin
    public function settings(Request $request)
    {
        // Logika pengaturan admin
        return response()->json(['message' => 'Settings berhasil diakses']);
    }
}
