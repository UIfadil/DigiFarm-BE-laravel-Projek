<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SoalKuis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SoalKuisController extends Controller
{
    // GET /admin/soal-kuis
    // Ambil semua soal, bisa filter by kategori
    public function index(Request $request)
    {
        $query = SoalKuis::latest();

        if ($request->kategori && $request->kategori !== 'semua') {
            $query->where('kategori', $request->kategori);
        }

        return response()->json($query->get());
    }

    // POST /admin/soal-kuis
    // Tambah soal baru
    public function store(Request $request)
    {
        $request->validate([
            'pertanyaan'    => 'required|string',
            'gambar'        => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'kategori'      => 'required|in:hama,penyakit,pertanian_dasar',
            'opsi_a'        => 'required|string',
            'opsi_b'        => 'required|string',
            'opsi_c'        => 'required|string',
            'opsi_d'        => 'required|string',
            'jawaban_benar' => 'required|in:a,b,c,d',
        ]);

        $data = [
            'pertanyaan'    => $request->pertanyaan,
            'kategori'      => $request->kategori,
            'opsi_a'        => $request->opsi_a,
            'opsi_b'        => $request->opsi_b,
            'opsi_c'        => $request->opsi_c,
            'opsi_d'        => $request->opsi_d,
            'jawaban_benar' => $request->jawaban_benar,
        ];

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('soal_kuis', 'public');
        }

        $soal = SoalKuis::create($data);
        return response()->json($soal, 201);
    }

    // POST /admin/soal-kuis/{id}/update
    // Edit soal
    public function update(Request $request, $id)
    {
        $soal = SoalKuis::findOrFail($id);

        $request->validate([
            'pertanyaan'    => 'required|string',
            'gambar'        => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'kategori'      => 'required|in:hama,penyakit,pertanian_dasar',
            'opsi_a'        => 'required|string',
            'opsi_b'        => 'required|string',
            'opsi_c'        => 'required|string',
            'opsi_d'        => 'required|string',
            'jawaban_benar' => 'required|in:a,b,c,d',
        ]);

        $updateData = [
            'pertanyaan'    => $request->pertanyaan,
            'kategori'      => $request->kategori,
            'opsi_a'        => $request->opsi_a,
            'opsi_b'        => $request->opsi_b,
            'opsi_c'        => $request->opsi_c,
            'opsi_d'        => $request->opsi_d,
            'jawaban_benar' => $request->jawaban_benar,
        ];

        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada
            if ($soal->gambar) {
                Storage::disk('public')->delete($soal->gambar);
            }
            $updateData['gambar'] = $request->file('gambar')->store('soal_kuis', 'public');
        }

        // Hapus gambar jika diminta
        if ($request->hapus_gambar === 'true' && $soal->gambar) {
            Storage::disk('public')->delete($soal->gambar);
            $updateData['gambar'] = null;
        }

        $soal->update($updateData);
        return response()->json($soal);
    }

    // DELETE /admin/soal-kuis/{id}
    public function destroy($id)
    {
        $soal = SoalKuis::findOrFail($id);
        if ($soal->gambar) {
            Storage::disk('public')->delete($soal->gambar);
        }
        $soal->delete();
        return response()->json(['message' => 'Soal berhasil dihapus']);
    }
}