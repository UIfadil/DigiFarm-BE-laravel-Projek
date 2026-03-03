<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\VideoEdukasi;
use App\Models\Edukasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoEdukasiController extends Controller
{
    // GET /admin/edukasi/{id}/video
    // Ambil semua video milik 1 edukasi
    public function index($edukasi_id)
    {
        Edukasi::findOrFail($edukasi_id);
        return response()->json(
            VideoEdukasi::where('edukasi_id', $edukasi_id)->get()
        );
    }

    // POST /admin/edukasi/{id}/video
    // Tambah video baru
    public function store(Request $request, $edukasi_id)
    {
        Edukasi::findOrFail($edukasi_id);

        $request->validate([
            'judul_video'      => 'required|string|max:255',
            'tipe_video'       => 'required|in:link,file',
            'keterangan_video' => 'nullable|string',
        ]);

        if ($request->tipe_video === 'file') {
            $request->validate([
                'video' => 'required|file|mimes:mp4,mov,avi|max:51200'
            ]);
            $videoValue = $request->file('video')
                                  ->store('video_edukasi', 'public');
        } else {
            $request->validate(['video' => 'required|string']);
            $videoValue = $request->video; // simpan URL langsung
        }

        $data = VideoEdukasi::create([
            'edukasi_id'       => $edukasi_id,
            'judul_video'      => $request->judul_video,
            'video'            => $videoValue,
            'tipe_video'       => $request->tipe_video,
            'keterangan_video' => $request->keterangan_video,
        ]);

        return response()->json($data, 201);
    }

    // POST /admin/video-edukasi/{id}/update
    // Edit video
    public function update(Request $request, $id)
    {
        $data = VideoEdukasi::findOrFail($id);

        $request->validate([
            'judul_video'      => 'required|string|max:255',
            'tipe_video'       => 'required|in:link,file',
            'keterangan_video' => 'nullable|string',
        ]);

        $updateData = [
            'judul_video'      => $request->judul_video,
            'tipe_video'       => $request->tipe_video,
            'keterangan_video' => $request->keterangan_video,
        ];

        if ($request->tipe_video === 'file' && $request->hasFile('video')) {
            if ($data->tipe_video === 'file') {
                Storage::disk('public')->delete($data->video);
            }
            $updateData['video'] = $request->file('video')
                                           ->store('video_edukasi', 'public');
        } elseif ($request->tipe_video === 'link' && $request->video) {
            $updateData['video'] = $request->video;
        }

        $data->update($updateData);
        return response()->json($data);
    }

    // DELETE /admin/video-edukasi/{id}
    // Hapus video
    public function destroy($id)
    {
        $data = VideoEdukasi::findOrFail($id);
        if ($data->tipe_video === 'file') {
            Storage::disk('public')->delete($data->video);
        }
        $data->delete();
        return response()->json(['message' => 'Video berhasil dihapus']);
    }
}