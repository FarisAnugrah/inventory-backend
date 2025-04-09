<?php

namespace App\Http\Controllers;

use App\Models\BarangMasuk;
use Illuminate\Http\Request;

class BarangMasukController extends Controller
{
    public function index()
    {
        return response()->json(BarangMasuk::with('barang', 'user', 'gudang')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'barang_id' => 'required|exists:barang,id',
            'user_id' => 'required|exists:users,id',
            'gudang_id' => 'required|exists:gudang,id',
            'jumlah' => 'required|integer',
            'tanggal' => 'required|date',
        ]);

        $barangMasuk = BarangMasuk::create($validated);

        return response()->json($barangMasuk, 201);
    }

    public function show($id)
    {
        $barangMasuk = BarangMasuk::with('barang', 'user', 'gudang')->find($id);

        if (!$barangMasuk) {
            return response()->json(['error' => 'Barang Masuk not found'], 404);
        }

        return response()->json($barangMasuk);
    }

    public function update(Request $request, $id)
    {
        $barangMasuk = BarangMasuk::find($id);

        if (!$barangMasuk) {
            return response()->json(['error' => 'Barang Masuk not found'], 404);
        }

        $validated = $request->validate([
            'barang_id' => 'required|exists:barang,id',
            'user_id' => 'required|exists:users,id',
            'gudang_id' => 'required|exists:gudang,id',
            'jumlah' => 'required|integer',
            'tanggal' => 'required|date',
        ]);

        $barangMasuk->update($validated);

        return response()->json($barangMasuk);
    }

    public function destroy($id)
    {
        $barangMasuk = BarangMasuk::find($id);

        if (!$barangMasuk) {
            return response()->json(['error' => 'Barang Masuk not found'], 404);
        }

        $barangMasuk->delete();

        return response()->json(['message' => 'Barang Masuk deleted successfully']);
    }
}
