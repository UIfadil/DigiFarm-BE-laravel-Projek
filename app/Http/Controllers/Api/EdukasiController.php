<?php
// ============================================================
//  FILE BARU: app/Http/Controllers/Api/EdukasiController.php
//
//  PERHATIKAN: ini di folder Api/ bukan Api/Admin/
//  Jadi ada 2 file EdukasiController:
//    - app/Http/Controllers/Api/Admin/EdukasiController.php  ← sudah ada (admin)
//    - app/Http/Controllers/Api/EdukasiController.php        ← BUAT INI (user)
// ============================================================

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Edukasi;

class EdukasiController extends Controller
{
    // GET /api/edukasi?kategori=hama
    public function index()
    {
        $kategori = request('kategori');

        $query = Edukasi::with('videoEdukasi')->orderBy('nama');

        if ($kategori) {
            $query->where('kategori', $kategori);
        }

        // ✅ append(null) agar response JSON memakai key snake_case
        // sesuai dengan yang dibaca frontend (video_edukasi bukan videoEdukasi)
        return response()->json($query->get()->map(function ($item) {
            return [
                'id'           => $item->id,
                'nama'         => $item->nama,
                'kategori'     => $item->kategori,
                'deskripsi'    => $item->deskripsi,
                'gambar'       => $item->gambar,
                'solusi'       => $item->solusi,
                // ✅ Key ini yang dibaca frontend: video_edukasi
                'video_edukasi' => $item->videoEdukasi->map(function ($v) {
                    return [
                        'id'               => $v->id,
                        'edukasi_id'       => $v->edukasi_id,
                        'judul_video'      => $v->judul_video,
                        'video'            => $v->video,
                        'tipe_video'       => $v->tipe_video,
                        'keterangan_video' => $v->keterangan_video,
                    ];
                }),
            ];
        }));
    }

    // GET /api/edukasi/{id}
    public function show($id)
    {
        $item = Edukasi::with('videoEdukasi')->findOrFail($id);

        return response()->json([
            'id'           => $item->id,
            'nama'         => $item->nama,
            'kategori'     => $item->kategori,
            'deskripsi'    => $item->deskripsi,
            'gambar'       => $item->gambar,
            'solusi'       => $item->solusi,
            // ✅ Key ini yang dibaca frontend: video_edukasi
            'video_edukasi' => $item->videoEdukasi->map(function ($v) {
                return [
                    'id'               => $v->id,
                    'edukasi_id'       => $v->edukasi_id,
                    'judul_video'      => $v->judul_video,
                    'video'            => $v->video,
                    'tipe_video'       => $v->tipe_video,
                    'keterangan_video' => $v->keterangan_video,
                ];
            }),
        ]);
    }
}