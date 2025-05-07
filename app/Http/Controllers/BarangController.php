<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $query = Barang::query();

        // Filter berdasarkan nama_barang jika ada search
        if ($request->has('search')) {
            $query->where('nama_barang', 'like', '%' . $request->search . '%');
        }

        // Ambil data dengan paginasi 10 item per halaman
        $barang = $query->paginate(10);

        return response()->json($barang);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_barang' => 'required|string',
            'kategori_id' => 'required|exists:kategori,id',
            'satuan' => 'required|string',
            'merk' => 'required|string',
            'gudang_id' => 'required|exists:gudang,id',
            'stok_keseluruhan' => 'required|integer',
            'harga' => 'required|integer',
            'minimum_stok' => 'required|integer',
        ]);

        $barang = Barang::create($validated); // kode_barang otomatis di model

        return response()->json([
            'message' => 'Barang berhasil ditambahkan',
            'data' => $barang,
        ], 201);
    }

    public function show($id)
    {
        $barang = Barang::find($id);

        if (!$barang) {
            return response()->json(['error' => 'Barang tidak ditemukan'], 404);
        }

        return response()->json($barang);
    }

    public function update(Request $request, $id)
    {
        $barang = Barang::find($id);
        if (!$barang) {
            return response()->json(['error' => 'Barang tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'kode_barang' => 'required|string|unique:barang,kode_barang,' . $id,
            'nama_barang' => 'required|string',
            'kategori_id' => 'required|exists:kategori,id',
            'satuan' => 'required|string',
            'merk' => 'required|string',
            'gudang_id' => 'required|exists:gudang,id',
            'stok_keseluruhan' => 'required|integer',
            'harga' => 'required|integer',
            'minimum_stok' => 'required|integer',
        ]);

        $barang->update($validated);

        return response()->json([
            'message' => 'Barang berhasil diperbarui',
            'data' => $barang,
        ]);
    }

    public function destroy($id)
    {
        $barang = Barang::find($id);
        if (!$barang) {
            return response()->json(['error' => 'Barang tidak ditemukan'], 404);
        }

        $barang->delete();
        return response()->json(['message' => 'Barang berhasil dihapus']);
    }
}
