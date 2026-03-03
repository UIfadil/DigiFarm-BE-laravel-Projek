<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Edukasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EdukasiController extends Controller
{
    // GET /admin/edukasi
    // Ambil semua data edukasi beserta videonya
    public function index()
    {
        return response()->json(
            Edukasi::with('videoEdukasi')->latest()->get()
        );
    }

    // POST /admin/edukasi
    // Tambah data edukasi baru
    public function store(Request $request)
    {
        $request->validate([
            'nama'      => 'required|string',
            'kategori'  => 'required|in:hama,penyakit,pertanian_dasar',
            'deskripsi' => 'required|string',
            'gambar'    => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'solusi'    => 'nullable|string',
        ]);

        $path = $request->file('gambar')->store('edukasi', 'public');

        $data = Edukasi::create([
            'nama'      => $request->nama,
            'kategori'  => $request->kategori,
            'deskripsi' => $request->deskripsi,
            'gambar'    => $path,
            'solusi'    => $request->solusi,
        ]);

        return response()->json($data, 201);
    }

    // POST /admin/edukasi/{id}/update
    // Edit data edukasi
    public function update(Request $request, $id)
    {
        $data = Edukasi::findOrFail($id);

        $request->validate([
            'nama'      => 'required|string',
            'kategori' => 'required|in:hama,penyakit,pertanian_dasar',
            'deskripsi' => 'required|string',
            'gambar'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'solusi'    => 'nullable|string',
        ]);

        $updateData = [
            'nama'      => $request->nama,
            'kategori'  => $request->kategori,
            'deskripsi' => $request->deskripsi,
            'solusi'    => $request->solusi,
        ];

        if ($request->hasFile('gambar')) {
            Storage::disk('public')->delete($data->gambar);
            $updateData['gambar'] = $request->file('gambar')
                                            ->store('edukasi', 'public');
        }

        $data->update($updateData);
        return response()->json($data);
    }

    // DELETE /admin/edukasi/{id}
    // Hapus data edukasi (video ikut terhapus otomatis karena cascade)
    public function destroy($id)
    {
        $data = Edukasi::findOrFail($id);
        Storage::disk('public')->delete($data->gambar);
        $data->delete();
        return response()->json(['message' => 'Data berhasil dihapus']);
    }
}