<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\HamaPenyakit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HamaPenyakitController extends Controller
{
    public function index() {
        return response()->json(HamaPenyakit::latest()->get());
    }

    public function store(Request $request) {
        $request->validate([
            'nama'      => 'required|string',
            'kategori'  => 'required|in:hama,penyakit',
            'deskripsi' => 'required|string',
            'gambar'    => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $path = $request->file('gambar')->store('hama_penyakit', 'public');

        $data = HamaPenyakit::create([
            'nama'      => $request->nama,
            'kategori'  => $request->kategori,
            'deskripsi' => $request->deskripsi,
            'gambar'    => $path,
        ]);

        return response()->json($data, 201);
    }

    public function update(Request $request, $id) {
        $data = HamaPenyakit::findOrFail($id);

        $request->validate([
            'nama'      => 'required|string',
            'kategori'  => 'required|in:hama,penyakit',
            'deskripsi' => 'required|string',
            'gambar'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $updateData = [
            'nama'      => $request->nama,
            'kategori'  => $request->kategori,
            'deskripsi' => $request->deskripsi,
        ];

        if ($request->hasFile('gambar')) {
            Storage::disk('public')->delete($data->gambar);
            $updateData['gambar'] = $request->file('gambar')->store('hama_penyakit', 'public');
        }

        $data->update($updateData);
        return response()->json($data);
    }

    public function destroy($id) {
        $data = HamaPenyakit::findOrFail($id);
        Storage::disk('public')->delete($data->gambar);
        $data->delete();
        return response()->json(['message' => 'Data berhasil dihapus']);
    }
}