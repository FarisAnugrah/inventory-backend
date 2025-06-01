<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Tambahkan ini di atas class controller
use Illuminate\Support\Facades\Gate;

class BarangMasukController extends Controller
{
    public function index()
    {
        $data = BarangMasuk::with(['barang.kategori', 'gudang', 'user'])
            ->latest()
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'kode_masuk' => $item->kode_masuk,
                    'nama_barang' => $item->barang->nama_barang,
                    'kategori'      => $item->barang->kategori ? $item->barang->kategori->nama_kategori : null,
                    'stok_masuk' => $item->jumlah,
                    'gudang'        => $item->gudang ? $item->gudang->nama_gudang : null,
                    'satuan' => $item->barang->satuan,
                    'tanggal' => $item->tanggal,
                    'nama_staff' => $item->user->name,
                ];
            });

        return response()->json([
            'data' => $data,
            'meta' => [
                'status' => 'success',
                'message' => 'List barang masuk berhasil diambil'
            ]
        ]);
    }

    public function store(Request $request)
    {
        // Cek apakah user mengirim barang_id atau data barang baru
        if (!$request->has('barang_id')) {
            // Validasi data barang baru
            $validatedBarang = $request->validate([
                'nama_barang' => 'required|string',
                'kategori_id' => 'required|exists:kategori,id',
                'satuan' => 'required|string',
                'gudang_id' => 'required|exists:gudang,id',
            ]);

            // Buat kode_barang otomatis
            $latestBarang = Barang::latest()->first();
            $nextNumber = $latestBarang ? ((int)substr($latestBarang->kode_barang, 1) + 1) : 1;
            $kodeBarang = 'M' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            // Buat barang baru
            $barang = Barang::create([
                'kode_barang' => $kodeBarang,
                'nama_barang' => $validatedBarang['nama_barang'],
                'kategori_id' => $validatedBarang['kategori_id'],
                'satuan' => $validatedBarang['satuan'],
                'gudang_id' => $validatedBarang['gudang_id'],
                'stok_keseluruhan' => 0,
            ]);

            $barang_id = $barang->id;
        } else {
            $barang_id = $request->input('barang_id');
        }

        // Validasi barang masuk
        $validated = $request->validate([
            'gudang_id' => 'required|exists:gudang,id',
            'jumlah' => 'required|integer|min:1',
        ]);

        // Generate kode_masuk
        $latest = BarangMasuk::latest()->first();
        $nextNumber = $latest ? (int)substr($latest->kode_masuk, 2) + 1 : 1;
        $kodeMasuk = 'M' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        // Simpan barang masuk
        $barangMasuk = BarangMasuk::create([
            'kode_masuk' => $kodeMasuk,
            'barang_id' => $barang_id,
            'gudang_id' => $validated['gudang_id'],
            'jumlah' => $validated['jumlah'],
            'tanggal' => now()->format('Y-m-d'),
            'user_id' => Auth::id(),
        ]);

        // Tambah stok barang
        $barang = Barang::find($barang_id);
        $barang->stok_keseluruhan += $validated['jumlah'];
        $barang->save();

        return response()->json([
            'data' => $barangMasuk,
            'meta' => [
                'status' => 'success',
                'message' => 'Barang masuk berhasil ditambahkan (dan barang dibuat jika perlu)'
            ]
        ], 201);
    }


    public function show($id)
    {
        $barangMasuk = BarangMasuk::with(['barang.kategori', 'gudang', 'user'])->find($id);

        if (!$barangMasuk) {
            return response()->json(['meta' => ['status' => 'error', 'message' => 'Data tidak ditemukan']], 404);
        }

        return response()->json([
            'data' => $barangMasuk,
            'meta' => [
                'status' => 'success',
                'message' => 'Data berhasil ditemukan'
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $barangMasuk = BarangMasuk::findOrFail($id);

        $validated = $request->validate([
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date'
        ]);

        // Ambil data barang terkait
        $barang = $barangMasuk->barang;

        // Sesuaikan stok_keseluruhan pada barang
        // Kurangi dulu dengan jumlah lama dari barangMasuk, lalu tambahkan dengan jumlah baru dari validasi
        $barang->stok_keseluruhan = $barang->stok_keseluruhan - $barangMasuk->jumlah + $validated['jumlah'];
        $barang->save();

        // Update data barangMasuk itu sendiri
        // Pastikan 'jumlah' dan 'tanggal' ada di $fillable model BarangMasuk
        $barangMasuk->update($validated);

        return response()->json([
            'data' => $barangMasuk,
            'meta' => [
                'status' => 'success',
                'message' => 'Barang masuk berhasil diupdate'
            ]
        ]);
    }

    public function destroy($id)
    {
        $barangMasuk = BarangMasuk::findOrFail($id);

        // Kurangi stok barang
        $barang = $barangMasuk->barang;
        $barang->stok_keseluruhan -= $barangMasuk->jumlah; // Gunakan stok_keseluruhan
        $barang->save();

        $barangMasuk->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Barang masuk berhasil dihapus'
            ]
        ]);
    }

    public function laporan(Request $request)
    {
        $this->authorize('view-report'); // Menggunakan Gate

        $query = BarangMasuk::with(['barang.kategori', 'gudang', 'user']);

        if ($request->has('from') && $request->has('to')) {
            $query->whereBetween('tanggal', [$request->from, $request->to]);
        }

        $data = $query->orderBy('tanggal', 'desc')->get()->map(function ($item) {
            return [
                'nama_staff'    => $item->user->name,
                'kode_barang'   => $item->barang->kode_barang,
                'kategori'      => $item->barang->kategori ? $item->barang->kategori->nama_kategori : null,
                'nama_barang'   => $item->barang->nama_barang,
                'gudang'        => $item->gudang ? $item->gudang->nama_gudang : null,
                'stok_masuk'    => $item->jumlah,
                'tanggal_masuk' => $item->tanggal,
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'status' => 'success',
                'message' => 'Laporan barang masuk berhasil diambil'
            ]
        ]);
    }
}
