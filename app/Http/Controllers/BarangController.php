<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    public function index()
    {
        return response()->json(Barang::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_barang' => 'required|string',
            'kategori_id' => 'required|exists:kategori,id',
            'gudang_id' => 'required|exists:gudang,id',
            'stok_kesuluruhan' => 'required|integer',
            'harga' => 'required|integer',
            'minimum_stok' => 'required|integer',
        ]);
        dd($validated);
        $barang = Barang::create($validated);

        return response()->json($barang, 201);
    }

    public function show($id)
    {
        $barang = Barang::find($id);

        // Jika barang tidak ditemukan, kirimkan pesan error dengan status 404
        if (!$barang) {
            return response()->json(['error' => 'Barang not found'], 404);
        }

        // Jika barang ditemukan, kembalikan data barang
        return response()->json($barang);
    }


    public function update(Request $request, $id)
    {
        $barang = Barang::find($id);
        if (!$barang) {
            return response()->json(['error' => 'Barang not found'], 404);
        }

        $validated = $request->validate([
            'nama_barang' => 'required|string',
            'kategori_id' => 'required|exists:kategori,id',
            'gudang_id' => 'required|exists:gudang,id',
            'stok_kesuluruhan' => 'required|integer',
            'harga' => 'required|integer',
            'minimum_stok' => 'required|integer',
        ]);

        $barang->update($validated);

        return response()->json($barang);
    }

    public function destroy($id)
    {
        $barang = Barang::find($id);
        if (!$barang) {
            return response()->json(['error' => 'Barang not found'], 404);
        }

        $barang->delete();
        return response()->json(['message' => 'Barang deleted successfully']);
    }
}
