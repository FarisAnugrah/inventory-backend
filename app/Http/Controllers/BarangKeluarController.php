<?php

namespace App\Http\Controllers;

use App\Models\BarangKeluar;
use Illuminate\Http\Request;

class BarangKeluarController extends Controller
{
    public function index()
    {
        return response()->json(BarangKeluar::with('barang', 'user', 'gudang')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'barang_id' => 'required|exists:barang,id',
            'user_id' => 'required|exists:users,id',
            'gudang_id' => 'required|exists:gudang,id',
            'jumlah' => 'required|integer',
            'status' => 'required|in:pending,disetujui,ditolak',
            'tanggal' => 'required|date',
        ]);

        $barangKeluar = BarangKeluar::create($validated);

        return response()->json($barangKeluar, 201);
    }

    public function show($id)
    {
        $barangKeluar = BarangKeluar::with('barang', 'user', 'gudang')->find($id);

        if (!$barangKeluar) {
            return response()->json(['error' => 'Barang Keluar not found'], 404);
        }

        return response()->json($barangKeluar);
    }

    public function update(Request $request, $id)
    {
        $barangKeluar = BarangKeluar::find($id);

        if (!$barangKeluar) {
            return response()->json(['error' => 'Barang Keluar not found'], 404);
        }

        $validated = $request->validate([
            'barang_id' => 'required|exists:barang,id',
            'user_id' => 'required|exists:users,id',
            'gudang_id' => 'required|exists:gudang,id',
            'jumlah' => 'required|integer',
            'status' => 'required|in:pending,disetujui,ditolak',
            'tanggal' => 'required|date',
        ]);

        $barangKeluar->update($validated);

        return response()->json($barangKeluar);
    }

    public function destroy($id)
    {
        $barangKeluar = BarangKeluar::find($id);

        if (!$barangKeluar) {
            return response()->json(['error' => 'Barang Keluar not found'], 404);
        }

        $barangKeluar->delete();

        return response()->json(['message' => 'Barang Keluar deleted successfully']);
    }
}

