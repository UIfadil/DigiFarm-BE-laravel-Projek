<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller {
    public function register(Request $request) {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user', // Otomatis jadi user biasa saat daftar
        ]);
        return response()->json(['message' => 'Berhasil Daftar'], 201);
    }

    public function login(Request $request) {
        // 1. Validasi input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Cari user di database
        $user = \App\Models\User::where('email', $request->email)->first();

        // 3. Cek password (menggunakan Hash::check)
        if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah!'
            ], 401);
        }

        // 4. Buat Token (PENTING)
        $token = $user->createToken('auth_token')->plainTextToken;

        // 5. Kirim respon ke Expo
        return response()->json([
            'message' => 'Login sukses!',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'role' => $user->role, // Kirim role agar Expo tahu harus ke mana
            'user' => $user
        ]);
    }
}