<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SoalKuis;
use App\Models\UserExp;
use App\Models\RiwayatKuis;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KuisController extends Controller
{
    // GET /kuis/soal?kategori=semua|hama|penyakit|pertanian_dasar
    // Ambil 10 soal acak
    public function getSoal(Request $request)
    {
        $kategori = $request->kategori ?? 'semua';

        $query = SoalKuis::query();

        if ($kategori !== 'semua') {
            $query->where('kategori', $kategori);
        }

        $soal = $query->inRandomOrder()->limit(10)->get();

        if ($soal->count() < 10) {
            return response()->json([
                'message' => 'Soal tidak cukup, minimal 10 soal per kategori'
            ], 422);
        }

        return response()->json($soal);
    }

    // POST /kuis/selesai
    // Simpan hasil kuis & update EXP user
    public function selesai(Request $request)
    {
        $request->validate([
            'kategori'      => 'required|in:semua,hama,penyakit,pertanian_dasar',
            'jumlah_benar'  => 'required|integer|min:0|max:10',
            'jumlah_salah'  => 'required|integer|min:0|max:10',
        ]);

        $user          = Auth::user();
        $jumlahBenar   = $request->jumlah_benar;
        $jumlahSalah   = $request->jumlah_salah;
        $expDidapat    = $jumlahBenar * 5;         // 1 benar = 10 EXP
        $skor          = ($jumlahBenar / 10) * 100; // skor 0-100

        // Simpan riwayat
        RiwayatKuis::create([
            'user_id'       => $user->id,
            'kategori'      => $request->kategori,
            'skor'          => $skor,
            'jumlah_benar'  => $jumlahBenar,
            'jumlah_salah'  => $jumlahSalah,
            'exp_didapat'   => $expDidapat,
        ]);

        // Update atau buat user_exp
        $userExp = UserExp::firstOrCreate(
            ['user_id' => $user->id],
            ['total_exp' => 0, 'level' => 1]
        );

        $userExp->total_exp += $expDidapat;
        $userExp->level      = UserExp::hitungLevel($userExp->total_exp);
        $userExp->save();

        return response()->json([
            'skor'          => $skor,
            'jumlah_benar'  => $jumlahBenar,
            'jumlah_salah'  => $jumlahSalah,
            'exp_didapat'   => $expDidapat,
            'total_exp'     => $userExp->total_exp,
            'level'         => $userExp->level,
            'nama_level'    => UserExp::namaLevel($userExp->level),
        ]);
    }

    // GET /kuis/ranking
    // Ambil papan peringkat global
    public function ranking()
    {
        $user = Auth::user();
        

        // Ambil semua user_exp join dengan users, urutkan by total_exp
        $allRankings = UserExp::with('user')
            ->orderByDesc('total_exp')
            ->get()
            ->map(function ($item, $index) {
                return [
                    'rank'       => $index + 1,
                    'user_id'    => $item->user_id,
                    'nama'       => $item->user->name ?? 'Unknown',
                    'total_exp'  => $item->total_exp,
                    'level'      => $item->level,
                    'nama_level' => UserExp::namaLevel($item->level),
                ];
            });

        // Cari posisi user yang sedang login
        $myRank = $allRankings->firstWhere('user_id', $user->id);

        return response()->json([
            'ranking' => $allRankings,
            'my_rank' => $myRank,
            'nama' => $user->name,
        ]);
    }

    // GET /kuis/profil-exp
    // Ambil data EXP & level user yang login
    public function profilExp()
    {
        $user    = Auth::user();
        $userExp = UserExp::firstOrCreate(
            ['user_id' => $user->id],
            ['total_exp' => 0, 'level' => 1]
        );

        $level        = $userExp->level;
        $totalExp     = $userExp->total_exp;
        $expLevelIni  = UserExp::expUntukLevel($level);
        $expLevelNext = UserExp::expUntukLevel($level + 1);

        // Progress bar ke level berikutnya
        $progressExp  = $totalExp - $expLevelIni;
        $targetExp    = $expLevelNext - $expLevelIni;
        $progressPct  = $level >= 5 ? 100 : round(($progressExp / $targetExp) * 100);

        return response()->json([
            'user_id'      => $user->id,
            'nama'         => $user->name,
            'total_exp'    => $totalExp,
            'level'        => $level,
            'nama_level'   => UserExp::namaLevel($level),
            'progress_pct' => $progressPct,        // untuk progress bar
            'exp_level_ini'  => $expLevelIni,      // EXP awal level sekarang
            'exp_level_next' => $expLevelNext,     // EXP target level berikutnya
        ]);
    }
}