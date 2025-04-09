<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    public function index()
    {
        return response()->json(Transaksi::with('user')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'total_harga' => 'required|integer',
            'uang_pembayaran' => 'required|integer',
            'uang_kembalian' => 'required|integer',
            'tanggal' => 'required|date',
        ]);

        $transaksi = Transaksi::create($validated);

        return response()->json($transaksi, 201);
    }

    public function show($id)
    {
        $transaksi = Transaksi::with('user')->find($id);

        if (!$transaksi) {
            return response()->json(['error' => 'Transaksi not found'], 404);
        }

        return response()->json($transaksi);
    }

    public function update(Request $request, $id)
    {
        $transaksi = Transaksi::find($id);

        if (!$transaksi) {
            return response()->json(['error' => 'Transaksi not found'], 404);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'total_harga' => 'required|integer',
            'uang_pembayaran' => 'required|integer',
            'uang_kembalian' => 'required|integer',
            'tanggal' => 'required|date',
        ]);

        $transaksi->update($validated);

        return response()->json($transaksi);
    }

    public function destroy($id)
    {
        $transaksi = Transaksi::find($id);

        if (!$transaksi) {
            return response()->json(['error' => 'Transaksi not found'], 404);
        }

        $transaksi->delete();

        return response()->json(['message' => 'Transaksi deleted successfully']);
    }
}

