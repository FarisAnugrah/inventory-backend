<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function index()
    {
        return response()->json(Notifikasi::with('barang', 'manajer')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'barang_id' => 'required|exists:barang,id',
            'manajer_id' => 'required|exists:users,id',
            'stok_saat_ini' => 'required|integer',
            'aksi' => 'required|in:belum direspons,restock,abaikan',
            'status' => 'required|in:baru,dibaca',
            'tanggal' => 'required|date',
        ]);

        $notifikasi = Notifikasi::create($validated);

        return response()->json($notifikasi, 201);
    }

    public function show($id)
    {
        $notifikasi = Notifikasi::with('barang', 'manajer')->find($id);

        if (!$notifikasi) {
            return response()->json(['error' => 'Notifikasi not found'], 404);
        }

        return response()->json($notifikasi);
    }

    public function update(Request $request, $id)
    {
        $notifikasi = Notifikasi::find($id);

        if (!$notifikasi) {
            return response()->json(['error' => 'Notifikasi not found'], 404);
        }

        $validated = $request->validate([
            'barang_id' => 'required|exists:barang,id',
            'manajer_id' => 'required|exists:users,id',
            'stok_saat_ini' => 'required|integer',
            'aksi' => 'required|in:belum direspons,restock,abaikan',
            'status' => 'required|in:baru,dibaca',
            'tanggal' => 'required|date',
        ]);

        $notifikasi->update($validated);

        return response()->json($notifikasi);
    }

    public function destroy($id)
    {
        $notifikasi = Notifikasi::find($id);

        if (!$notifikasi) {
            return response()->json(['error' => 'Notifikasi not found'], 404);
        }

        $notifikasi->delete();

        return response()->json(['message' => 'Notifikasi deleted successfully']);
    }
}
