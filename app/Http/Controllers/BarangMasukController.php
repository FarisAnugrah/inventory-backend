<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\User;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class BarangMasukController extends Controller
{
    /**
     * Daftar satuan yang valid untuk digunakan dalam validasi.
     * Didefinisikan sebagai properti agar mudah dikelola.
     */
    private $validSatuan = [
        'PCS', 'KRT', 'KDS', 'LSN', 'PAK', 'ROL', 'SET', 'BOT', 'DRM', 'BOX',
        'BAL', 'BKS', 'GLS', 'SHP', 'BAG', 'TIN', 'GRS', 'LTR', 'KG', 'G',
        'M', 'CM', 'MTR', 'AMP', 'CAP', 'TAB', 'TRAY'
    ];

    /**
     * Menampilkan daftar semua barang masuk.
     */
    public function index()
    {
        $data = BarangMasuk::with(['barang.kategori', 'gudang', 'user'])
            ->latest() // Mengurutkan berdasarkan 'created_at' terbaru
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'kode_masuk' => $item->kode_masuk,
                    'nama_barang' => $item->barang ? $item->barang->nama_barang : null,
                    'kategori'    => $item->barang && $item->barang->kategori ? $item->barang->kategori->nama_kategori : null,
                    'stok_masuk' => $item->jumlah,
                    'gudang'      => $item->gudang ? $item->gudang->nama_gudang : null,
                    'satuan' => $item->barang ? $item->barang->satuan : null,
                    'tanggal_masuk' => $item->created_at->format('Y-m-d'),
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

    /**
     * Menyimpan data barang masuk baru.
     * Akan memicu notifikasi jika stok akhir di bawah 30.
     */
    public function store(Request $request)
    {
        // Cek apakah user mengirim barang_id atau data barang baru
        if (!$request->has('barang_id')) {
            // Validasi data barang baru
            $validatedBarang = $request->validate([
                'nama_barang' => 'required|string|max:255',
                'kategori_id' => 'required|integer|exists:kategori,id',
                'satuan' => ['required', 'string', Rule::in($this->validSatuan)],
                'gudang_id' => 'required|integer|exists:gudang,id',
            ]);

            // Logika pembuatan kode barang yang aman dari duplikasi
            $lastBarang = Barang::orderBy('kode_barang', 'desc')->first();
            $nextNumber = 1;
            if ($lastBarang) {
                $lastNumber = (int) substr($lastBarang->kode_barang, 1);
                $nextNumber = $lastNumber + 1;
            }
            $kodeBarang = 'B' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

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

        // Validasi data transaksi barang masuk
        $validated = $request->validate([
            'gudang_id' => 'required|integer|exists:gudang,id',
            'jumlah' => 'required|integer|min:1',
        ]);

        // Generate kode_masuk
        $latest = BarangMasuk::latest('id')->first();
        $nextNumber = $latest ? (int)substr($latest->kode_masuk, 1) + 1 : 1;
        $kodeMasuk = 'M' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        // Simpan barang masuk
        $barangMasuk = BarangMasuk::create([
            'kode_masuk' => $kodeMasuk,
            'barang_id' => $barang_id,
            'gudang_id' => $validated['gudang_id'],
            'jumlah' => $validated['jumlah'],
            'user_id' => Auth::id(),
        ]);

        // Tambah stok barang & cek notifikasi
        $barang = Barang::find($barang_id);
        $barang->stok_keseluruhan += $validated['jumlah'];
        $barang->save();

        if ($barang->stok_keseluruhan < 30) {
            $this->kirimNotifikasiStokRendah($barang, 'penambahan');
        }

        return response()->json([
            'data' => $barangMasuk->load('barang'),
            'meta' => [ 'status' => 'success', 'message' => 'Barang masuk berhasil ditambahkan']
        ], 201);
    }

    /**
     * Menampilkan detail satu data barang masuk.
     */
    public function show($id)
    {
        $barangMasuk = BarangMasuk::with(['barang.kategori', 'gudang', 'user'])->findOrFail($id);
        return response()->json([
            'data' => $barangMasuk,
            'meta' => [ 'status' => 'success', 'message' => 'Data berhasil ditemukan']
        ]);
    }

    /**
     * Mengupdate data barang masuk dan detail barang terkait.
     * Akan memicu notifikasi jika stok akhir di bawah 30.
     */
    public function update(Request $request, $id)
    {
        $barangMasuk = BarangMasuk::findOrFail($id);
        $barang = $barangMasuk->barang;

        if (!$barang) {
            return response()->json(['meta' => ['status' => 'error', 'message' => 'Data barang terkait tidak ditemukan.']], 404);
        }

        $validatedData = $request->validate([
            'jumlah'        => 'required|integer|min:1',
            'nama_barang'   => 'sometimes|string|max:255',
            'kategori_id'   => 'sometimes|integer|exists:kategori,id',
            'satuan'        => ['sometimes', 'string', Rule::in($this->validSatuan)],
        ]);

        $jumlahLamaBarangMasuk = $barangMasuk->jumlah;
        $jumlahBaruBarangMasuk = $validatedData['jumlah'];

        if ($request->hasAny(['nama_barang', 'kategori_id', 'satuan'])) {
            $barang->fill($request->only(['nama_barang', 'kategori_id', 'satuan']));
        }

        // Update stok & cek notifikasi
        $barang->stok_keseluruhan = $barang->stok_keseluruhan - $jumlahLamaBarangMasuk + $jumlahBaruBarangMasuk;
        $barang->save();

        if ($barang->stok_keseluruhan < 30) {
            $this->kirimNotifikasiStokRendah($barang, 'penyesuaian');
        }

        // Update jumlah pada transaksi barang masuk
        $barangMasuk->update(['jumlah' => $jumlahBaruBarangMasuk]);

        return response()->json([
            'data' => $barangMasuk->load('barang'),
            'meta' => ['status' => 'success', 'message' => 'Data barang masuk berhasil diupdate.']
        ]);
    }

    /**
     * Menghapus data barang masuk dan mengembalikan stok.
     */
    public function destroy($id)
    {
        $barangMasuk = BarangMasuk::findOrFail($id);

        $barang = $barangMasuk->barang;
        if ($barang) {
            $barang->stok_keseluruhan -= $barangMasuk->jumlah;
            $barang->save();
        }

        $barangMasuk->delete();

        return response()->json([
            'meta' => [ 'status' => 'success', 'message' => 'Barang masuk berhasil dihapus']
        ]);
    }

    /**
     * Menampilkan laporan barang masuk.
     */
    public function laporan(Request $request)
    {
        $this->authorize('view-report');

        $query = BarangMasuk::with(['barang.kategori', 'gudang', 'user']);

        if ($request->has('from') && $request->has('to')) {
            $query->whereDate('created_at', '>=', $request->from)
                  ->whereDate('created_at', '<=', $request->to);
        }

        $data = $query->orderBy('created_at', 'desc')->get()->map(function ($item) {
            return [
                'nama_staff'    => $item->user ? $item->user->name : null,
                'kode_barang'   => $item->barang ? $item->barang->kode_barang : null,
                'kategori'      => $item->barang && $item->barang->kategori ? $item->barang->kategori->nama_kategori : null,
                'nama_barang'   => $item->barang ? $item->barang->nama_barang : null,
                'gudang'        => $item->gudang ? $item->gudang->nama_gudang : null,
                'stok_masuk'    => $item->jumlah,
                'tanggal_masuk' => $item->created_at->format('Y-m-d'),
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [ 'status' => 'success', 'message' => 'Laporan barang masuk berhasil diambil']
        ]);
    }

    /**
     * Method private untuk mengirim notifikasi stok rendah ke semua manajer.
     */
    private function kirimNotifikasiStokRendah(Barang $barang, string $jenisTransaksi): void
    {
        $manajerUsers = User::where('role', 'manajer')->get();
        $pesanAwal = $jenisTransaksi === 'penambahan' ? 'Info Stok: Stok untuk barang' : 'Info Stok: Stok untuk barang';

        foreach ($manajerUsers as $manajer) {
            Notifikasi::create([
                'user_id' => $manajer->id,
                'judul' => 'Info Stok Kritis',
                'pesan' => "{$pesanAwal} '{$barang->nama_barang}' (Kode: {$barang->kode_barang}) masih berada di level rendah setelah {$jenisTransaksi}. Sisa stok: {$barang->stok_keseluruhan} {$barang->satuan}.",
                'tipe' => 'info',
            ]);
        }
    }
}
