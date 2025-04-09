<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function index()
    {
        return response()->json(Kategori::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|string',
        ]);

        $kategori = Kategori::create($validated);
        return response()->json($kategori, 201);
    }

    public function show($id)
    {
        return response()->json(Kategori::find($id));
    }

    public function update(Request $request, $id)
    {
        $kategori = Kategori::find($id);
        if (!$kategori) {
            return response()->json(['error' => 'Kategori not found'], 404);
        }

        $validated = $request->validate([
            'nama_kategori' => 'required|string',
        ]);

        $kategori->update($validated);

        return response()->json($kategori);
    }

    public function destroy($id)
    {
        $kategori = Kategori::find($id);
        if (!$kategori) {
            return response()->json(['error' => 'Kategori not found'], 404);
        }

        $kategori->delete();
        return response()->json(['message' => 'Kategori deleted successfully']);
    }
}
    
