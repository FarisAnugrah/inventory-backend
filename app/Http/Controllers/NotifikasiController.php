<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    /**
     * Menampilkan notifikasi untuk user yang sedang login.
     * Secara default menampilkan yang belum dibaca.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Notifikasi::where('user_id', $user->id);

        // Filter: tampilkan semua notifikasi jika ada parameter ?tampilkan=semua
        if ($request->query('tampilkan') !== 'semua') {
            $query->whereNull('dibaca_pada');
        }

        $notifikasi = $query->latest()->paginate(20);

        return response()->json($notifikasi);
    }

    /**
     * Menandai satu notifikasi sebagai sudah dibaca.
     */
    public function tandaiSudahDibaca($id)
    {
        $user = Auth::user();
        $notifikasi = Notifikasi::where('id', $id)->where('user_id', $user->id)->firstOrFail();

        if (is_null($notifikasi->dibaca_pada)) {
            $notifikasi->dibaca_pada = now();
            $notifikasi->save();
        }

        return response()->json(['message' => 'Notifikasi berhasil ditandai sebagai sudah dibaca.']);
    }

    /**
     * Menandai semua notifikasi sebagai sudah dibaca.
     */
    public function tandaiSemuaSudahDibaca()
    {
        $user = Auth::user();
        Notifikasi::where('user_id', $user->id)
            ->whereNull('dibaca_pada')
            ->update(['dibaca_pada' => now()]);

        return response()->json(['message' => 'Semua notifikasi berhasil ditandai sebagai sudah dibaca.']);
    }
}
