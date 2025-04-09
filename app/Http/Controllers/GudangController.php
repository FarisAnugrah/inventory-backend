<?php

namespace App\Http\Controllers;

use App\Models\Gudang;
use Illuminate\Http\Request;

class GudangController extends Controller
{
    public function index()
    {
        return response()->json(Gudang::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_gudang' => 'required|string',
            'lokasi' => 'required|string',
        ]);

        $gudang = Gudang::create($validated);
        return response()->json($gudang, 201);
    }

    public function show($id)
    {
        return response()->json(Gudang::find($id));
    }

    public function update(Request $request, $id)
    {
        $gudang = Gudang::find($id);
        if (!$gudang) {
            return response()->json(['error' => 'Gudang not found'], 404);
        }

        $validated = $request->validate([
            'nama_gudang' => 'required|string',
            'lokasi' => 'required|string',
        ]);

        $gudang->update($validated);

        return response()->json($gudang);
    }

    public function destroy($id)
    {
        $gudang = Gudang::find($id);
        if (!$gudang) {
            return response()->json(['error' => 'Gudang not found'], 404);
        }

        $gudang->delete();
        return response()->json(['message' => 'Gudang deleted successfully']);
    }
}

