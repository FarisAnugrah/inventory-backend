<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $query = Barang::with(['kategori', 'gudang']);

        // Filter berdasarkan nama_barang jika ada search
        if ($request->has('search')) {
            $query->where('nama_barang', 'like', '%' . $request->search . '%');
        }

        // Ambil data dengan paginasi 10 item per halaman
        $barang = $query->paginate(10);

        return response()->json([
            'data' => $barang,
            'meta' => [
                'status' => 'success',
                'message' => 'Daftar barang berhasil diambil'
            ]
        ]);
    }

    public function show($id)
    {
        $barang = Barang::with(['kategori', 'gudang'])->find($id);

        if (!$barang) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Barang tidak ditemukan'
                ]
            ], 404);
        }

        return response()->json([
            'data' => $barang,
            'meta' => [
                'status' => 'success',
                'message' => 'Detail barang berhasil diambil'
            ]
        ]);
    }
}
