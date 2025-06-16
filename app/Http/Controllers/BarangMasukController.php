<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule; //

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
                    'tanggal' => $item->created_at->format('Y-m-d'),
                    'nama_staff' => $item->user ? $item->user->name : null,
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
            $satuanValid = [
            'PCS', 'KRT', 'KDS', 'LSN', 'PAK', 'ROL', 'SET', 'BOT', 'DRM', 'BOX',
            'BAL', 'BKS', 'GLS', 'SHP', 'BAG', 'TIN', 'GRS', 'LTR', 'KG', 'G',
            'M', 'CM', 'MTR', 'AMP', 'CAP', 'TAB', 'TRAY'
        ];

            // Validasi data barang baru
            $validatedBarang = $request->validate([
                'nama_barang' => 'required|string',
                'kategori_id' => 'required|exists:kategori,id',
                'satuan' => ['required', 'string', Rule::in($satuanValid)],
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
                'message' => 'Barang masuk berhasil ditambahkan'
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
        // Opsional: Otorisasi menggunakan Gate jika Anda sudah mendefinisikannya
        // $this->authorize('update-barang-masuk-record'); // Ganti 'update-barang-masuk-record' dengan nama Gate Anda

        $barangMasuk = BarangMasuk::findOrFail($id);
        $barang = $barangMasuk->barang; // Ambil model Barang yang terkait

        // Jika karena suatu alasan barang terkait tidak ditemukan (seharusnya tidak terjadi dengan foreign key yang baik)
        if (!$barang) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Data barang inti yang terkait dengan barang masuk ini tidak ditemukan.'
                ]
            ], 404);
        }

        // Validasi input
        // Field untuk detail Barang (nama_barang, kategori_id, satuan) dibuat opsional
        // dengan 'sometimes'. Artinya, hanya akan divalidasi jika field tersebut dikirim dalam request.
        $validatedData = $request->validate([
            'jumlah'        => 'required|integer|min:1',          // Untuk BarangMasuk (jumlah barang yang masuk)
            'nama_barang'   => 'sometimes|string|max:255',        // Untuk Barang (opsional)
            'kategori_id'   => 'sometimes|integer|exists:kategori,id', // Untuk Barang (opsional, pastikan tabel 'kategori' ada)
            'satuan'        => [                                  // Untuk Barang (opsional, dengan pilihan terbatas)
                'sometimes',
                'string',
                Rule::in(['pcs', 'karton']), // Pilihan satuan seperti dropdown
            ],
        ]);

        // --- Proses Update ---

        // 1. Simpan jumlah lama dari barangMasuk untuk perhitungan stok
        $jumlahLamaBarangMasuk = $barangMasuk->jumlah;

        // 2. Update detail pada model Barang (jika ada dalam request)
        $barangAttributesToUpdate = [];
        if ($request->has('nama_barang') && isset($validatedData['nama_barang'])) {
            $barangAttributesToUpdate['nama_barang'] = $validatedData['nama_barang'];
        }
        if ($request->has('kategori_id') && isset($validatedData['kategori_id'])) {
            $barangAttributesToUpdate['kategori_id'] = $validatedData['kategori_id'];
        }
        if ($request->has('satuan') && isset($validatedData['satuan'])) {
            $barangAttributesToUpdate['satuan'] = $validatedData['satuan'];
        }

        // Jika ada atribut barang yang perlu diupdate, isi ke model Barang
        if (!empty($barangAttributesToUpdate)) {
            $barang->fill($barangAttributesToUpdate);
        }

        // 3. Sesuaikan stok_keseluruhan pada Barang
        // Logika: Stok Sekarang = Stok Lama - Jumlah Barang Masuk Lama + Jumlah Barang Masuk Baru
        $jumlahBaruBarangMasuk = $validatedData['jumlah'];
        $barang->stok_keseluruhan = $barang->stok_keseluruhan - $jumlahLamaBarangMasuk + $jumlahBaruBarangMasuk;

        // Simpan semua perubahan pada model Barang (nama, kategori, satuan, dan stok_keseluruhan)
        $barang->save();

        // 4. Update jumlah pada model BarangMasuk
        $barangMasuk->update([
            'jumlah' => $jumlahBaruBarangMasuk
        ]);

        // Muat ulang relasi untuk memastikan respons berisi data yang paling baru
        $barangMasuk->load(['barang.kategori', 'gudang', 'user']);

        return response()->json([
            'data' => [ // Sesuaikan format data respons jika perlu
                'id_barang_masuk' => $barangMasuk->id,
                'kode_masuk' => $barangMasuk->kode_masuk,
                'jumlah_masuk_baru' => $barangMasuk->jumlah,
                'barang_info' => [
                    'id_barang' => $barang->id,
                    'kode_barang' => $barang->kode_barang,
                    'nama_barang' => $barang->nama_barang,
                    'kategori' => $barang->kategori ? $barang->kategori->nama_kategori : null, // Asumsi kolom nama di tabel kategori adalah nama_kategori
                    'satuan' => $barang->satuan,
                    'stok_keseluruhan_sekarang' => $barang->stok_keseluruhan,
                ],
                'gudang' => $barangMasuk->gudang ? $barangMasuk->gudang->nama_gudang : null, // Asumsi kolom nama di tabel gudang adalah nama_gudang
                'dicatat_oleh' => $barangMasuk->user ? $barangMasuk->user->name : null,
                'tanggal_dicatat' => $barangMasuk->created_at->format('Y-m-d H:i:s'), // Menggunakan created_at
                'terakhir_diupdate' => $barangMasuk->updated_at->format('Y-m-d H:i:s'),
            ],
            'meta' => [
                'status' => 'success',
                'message' => 'Data barang masuk dan detail barang berhasil diupdate.'
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

        $data = $query->orderBy('created_at', 'desc')->get()->map(function ($item) {
            return [
                'nama_staff'    => $item->user->name,
                'kode_barang'   => $item->barang->kode_barang,
                'kategori'      => $item->barang->kategori ? $item->barang->kategori->nama_kategori : null,
                'nama_barang'   => $item->barang->nama_barang,
                'gudang'        => $item->gudang ? $item->gudang->nama_gudang : null,
                'stok_masuk'    => $item->jumlah,
                'tanggal_masuk' => $item->created_at->format('Y-m-d'),
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
