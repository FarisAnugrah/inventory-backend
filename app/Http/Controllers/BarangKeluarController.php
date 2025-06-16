<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangKeluar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class BarangKeluarController extends Controller
{
    /**
     * Menampilkan daftar permintaan barang keluar.
     * Bisa difilter berdasarkan status.
     * Akses: Siapapun yang terautentikasi dan memiliki akses ke route ini (misal: Staff).
     */
    public function index(Request $request)
    {
        $query = BarangKeluar::with(['barang', 'user', 'gudang', 'approver'])
            ->latest('id'); // Urutkan berdasarkan yang terbaru dibuat

        // Tambahkan filter opsional berdasarkan status
        if ($request->has('status') && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $request->status);
        }

        $barangKeluar = $query->paginate(15); // Gunakan paginasi untuk performa lebih baik

        return response()->json([
            'data' => $barangKeluar,
            'meta' => [
                'status' => 'success',
                'message' => 'Daftar barang keluar berhasil diambil.'
            ]
        ]);
    }

    /**
     * [STAFF] Membuat permintaan barang keluar baru.
     * Status otomatis 'pending', stok belum dikurangi.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'barang_id' => 'required|integer|exists:barang,id',
            'gudang_id' => 'required|integer|exists:gudang,id',
            'jumlah' => 'required|integer|min:1',
            'tujuan_pengeluaran' => 'nullable|string|max:255', // Opsional
        ]);

        $barang = Barang::findOrFail($validatedData['barang_id']);

        // Pengecekan awal: Pastikan stok mencukupi sebelum membuat permintaan
        if ($barang->stok_keseluruhan < $validatedData['jumlah']) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Stok barang tidak mencukupi untuk jumlah yang diminta.',
                    'stok_tersedia' => $barang->stok_keseluruhan,
                ]
            ], 422);
        }

        // Generate kode_keluar otomatis
        $latest = BarangKeluar::latest('id')->first();
        $nextNumber = $latest ? ((int)substr($latest->kode_keluar, 1)) + 1 : 1;
        $kodeKeluar = 'K' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $barangKeluar = BarangKeluar::create([
                'kode_keluar' => $kodeKeluar,
                'status' => 'pending',         // Status otomatis 'pending'
                'user_id' => Auth::id(),         // ID Staff dari yang login
                'barang_id' => $validatedData['barang_id'],
                'gudang_id' => $validatedData['gudang_id'],
                'jumlah' => $validatedData['jumlah'],
                'tujuan_pengeluaran' => $request->tujuan_pengeluaran,
                'tanggal_keluar' => now()->format('Y-m-d'), // Tanggal otomatis saat request
            ]);

            DB::commit();

            return response()->json([
                'data' => $barangKeluar,
                'meta' => [
                    'status' => 'success',
                    'message' => 'Permintaan barang keluar berhasil dibuat dan menunggu persetujuan.'
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['meta' => ['status' => 'error', 'message' => 'Gagal membuat permintaan: ' . $e->getMessage()]], 500);
        }
    }

    /**
     * [MANAJER] Menyetujui atau menolak permintaan barang keluar.
     * Ini adalah fungsi approval, bukan update data biasa.
     */
    public function update(Request $request, $id)
    {
        // Hanya cari permintaan yang statusnya masih 'pending'
        $barangKeluar = BarangKeluar::where('id', $id)->where('status', 'pending')->firstOrFail();

        // Manajer hanya perlu mengirimkan status ('approved' atau 'rejected') dan catatan (opsional)
        $validatedData = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected'])]
        ]);

        $barang = $barangKeluar->barang;

        DB::beginTransaction();
        try {
            // Jika statusnya 'approved', maka kurangi stok barang.
            if ($validatedData['status'] === 'approved') {
                // Pengecekan ulang stok saat akan diapprove (sangat penting!)
                if ($barang->stok_keseluruhan < $barangKeluar->jumlah) {
                    DB::rollBack();
                    return response()->json([
                        'meta' => ['status' => 'error', 'message' => 'Gagal menyetujui: Stok barang sudah tidak mencukupi.', 'stok_tersedia' => $barang->stok_keseluruhan,]
                    ], 422);
                }
                // Kurangi stok barang
                $barang->stok_keseluruhan -= $barangKeluar->jumlah;
                $barang->save();
            }

            // Update status dan catat siapa & kapan approval terjadi
            $barangKeluar->status = $validatedData['status'];
            $barangKeluar->approved_by = Auth::id(); // ID Manajer yang login
            $barangKeluar->approved_at = now();
            $barangKeluar->save();

            DB::commit();

            return response()->json([
                'data' => $barangKeluar->load(['barang', 'user', 'approver']),
                'meta' => ['status' => 'success', 'message' => 'Status barang keluar berhasil diupdate menjadi: ' . $validatedData['status']]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['meta' => ['status' => 'error', 'message' => 'Gagal mengupdate status: ' . $e->getMessage()]], 500);
        }
    }

    /**
     * Menampilkan detail satu permintaan barang keluar.
     */
    public function show($id)
    {
        $barangKeluar = BarangKeluar::with('barang', 'user', 'gudang', 'approver')->findOrFail($id);

        return response()->json($barangKeluar);
    }

    /**
     * Menghapus permintaan barang keluar.
     * Sebaiknya hanya untuk permintaan yang belum disetujui (status 'pending').
     */
    public function destroy($id)
    {
        $barangKeluar = BarangKeluar::findOrFail($id);

        // Aturan bisnis tambahan: mungkin hanya bisa menghapus jika status masih 'pending'.
        if ($barangKeluar->status !== 'pending') {
            return response()->json(['meta' => ['status' => 'error', 'message' => 'Hanya permintaan dengan status pending yang bisa dihapus.']], 403);
        }

        $barangKeluar->delete();

        return response()->json(['meta' => ['status' => 'success', 'message' => 'Permintaan barang keluar berhasil dihapus.']]);
    }

    /**
     * Menampilkan laporan barang keluar yang sudah disetujui.
     * Akses: Staff, Manajer, Admin (via Gate)
     */
    public function laporan(Request $request)
    {
        $this->authorize('view-report'); // Menggunakan Gate untuk otorisasi

        $query = BarangKeluar::with(['barang.kategori', 'gudang', 'user', 'approver'])
            ->where('status', 'approved'); // Laporan hanya untuk yang 'approved'

        if ($request->has('from_date') && $request->has('to_date')) {
            $query->whereBetween('tanggal_keluar', [$request->from_date, $request->to_date]);
        }

        $data = $query->orderBy('tanggal_keluar', 'desc')->get()->map(function ($item) {
            return [
                'kode_keluar' => $item->kode_keluar,
                'nama_barang' => $item->barang ? $item->barang->nama_barang : null,
                'kategori' => $item->barang && $item->barang->kategori ? $item->barang->kategori->nama_kategori : null,
                'jumlah_keluar' => $item->jumlah,
                'tujuan_pengeluaran' => $item->tujuan_pengeluaran,
                'tanggal_keluar' => $item->tanggal_keluar->format('Y-m-d'),
                'dicatat_oleh_staff' => $item->user ? $item->user->name : null,
                'disetujui_oleh_manajer' => $item->approver ? $item->approver->name : null,
            ];
        });

        return response()->json(['data' => $data, 'meta' => ['status' => 'success', 'message' => 'Laporan barang keluar berhasil diambil.']]);
    }
}
