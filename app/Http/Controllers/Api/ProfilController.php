<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class ProfilController extends Controller
{
    // ── GET /api/profil ──
    // Ambil data profil user yang sedang login
    public function show(Request $request)
    {
        return response()->json($request->user());
    }

    // ── POST /api/profil/update ──
    // Update nama dan/atau foto profil
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'foto_profil' => 'sometimes|nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Update nama jika dikirim
        if ($request->filled('name')) {
            $user->name = $request->name;
        }

        // Update foto jika ada file baru
        if ($request->hasFile('foto_profil')) {
            // Hapus foto lama jika ada
            if ($user->foto_profil) {
                Storage::disk('public')->delete($user->foto_profil);
            }
            // Simpan foto baru di storage/app/public/profil/
            $path = $request->file('foto_profil')->store('profil', 'public');
            $user->foto_profil = $path;
        }

        $user->save();

        return response()->json($user);
    }

    // ── POST /api/profil/hapus-foto ──
    // Hapus foto profil (kembali ke default)
    public function hapusFoto(Request $request)
    {
        $user = $request->user();

        if ($user->foto_profil) {
            Storage::disk('public')->delete($user->foto_profil);
            $user->foto_profil = null;
            $user->save();
        }

        return response()->json($user);
    }
}