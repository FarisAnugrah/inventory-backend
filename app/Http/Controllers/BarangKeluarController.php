<?php

namespace App\Http\Controllers;

use App\Models\BarangKeluar;
use Illuminate\Http\Request;

class BarangKeluarController extends Controller
{
    public function index()
    {
        $data = BarangKeluar::with('barang', 'user', 'gudang')->latest()->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'barang' => $item->barang->nama_barang ?? null,
                'user' => $item->user->name ?? null,
                'gudang' => $item->gudang->nama_gudang ?? null,
                'jumlah' => $item->jumlah,
                'status' => $item->status,
                'tanggal' => $item->tanggal,
            ];
        });

        return response()->json($data);
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
