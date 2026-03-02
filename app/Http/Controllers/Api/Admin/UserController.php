<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; // <--- WAJIB TAMBAHKAN INI

class UserController extends Controller
{
    public function index() {
        // Sekarang bisa langsung panggil User::
        return response()->json(User::all());
    }

    public function updateRole(Request $request, $id) {
        $user = User::findOrFail($id);
        $user->role = $request->role;
        $user->save();
        return response()->json(['message' => 'Role berhasil diperbarui']);
    }

    public function destroy($id) {
        User::destroy($id);
        return response()->json(['message' => 'User berhasil dihapus']);
    }
}