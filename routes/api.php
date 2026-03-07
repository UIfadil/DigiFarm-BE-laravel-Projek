<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\EdukasiController;
use App\Http\Controllers\Api\Admin\VideoEdukasiController;
use App\Http\Controllers\Api\Admin\SoalKuisController;
use App\Http\Controllers\Api\KuisController;
use App\Http\Controllers\Api\EdukasiController as UserEdukasiController;
use App\Http\Controllers\Api\ProfilController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    // Manajemen User
    Route::get('/admin/users', [UserController::class, 'index']);
    Route::put('/admin/users/{id}/role', [UserController::class, 'updateRole']);
    Route::delete('/admin/users/{id}', [UserController::class, 'destroy']);

    // Manajemen Edukasi
    Route::get('/admin/edukasi', [EdukasiController::class, 'index']);
    Route::post('/admin/edukasi', [EdukasiController::class, 'store']);
    Route::post('/admin/edukasi/{id}/update', [EdukasiController::class, 'update']);
    Route::delete('/admin/edukasi/{id}', [EdukasiController::class, 'destroy']);

    // Manajemen Video Edukasi
    Route::get('/admin/edukasi/{id}/video', [VideoEdukasiController::class, 'index']);
    Route::post('/admin/edukasi/{id}/video', [VideoEdukasiController::class, 'store']);
    Route::post('/admin/video-edukasi/{id}/update', [VideoEdukasiController::class, 'update']);
    Route::delete('/admin/video-edukasi/{id}', [VideoEdukasiController::class, 'destroy']);

    // ─── Manajemen Soal Kuis ───
    Route::get('/admin/soal-kuis', [SoalKuisController::class, 'index']);
    Route::post('/admin/soal-kuis', [SoalKuisController::class, 'store']);
    Route::post('/admin/soal-kuis/{id}/update', [SoalKuisController::class, 'update']);
    Route::delete('/admin/soal-kuis/{id}', [SoalKuisController::class, 'destroy']);

    // ─── Fitur Kuis (User) ───
    Route::get('/kuis/soal', [KuisController::class, 'getSoal']);           // ambil soal
    Route::post('/kuis/selesai', [KuisController::class, 'selesai']);        // simpan hasil
    Route::get('/kuis/ranking', [KuisController::class, 'ranking']);         // papan peringkat
    Route::get('/kuis/profil-exp', [KuisController::class, 'profilExp']);    // data exp user

    // ── Edukasi (user) ──
    Route::get('/edukasi', [UserEdukasiController::class, 'index']);
    Route::get('/edukasi/{id}', [UserEdukasiController::class, 'show']);



    // ── Profil (user) ──
    Route::get('/profil', [ProfilController::class, 'show']);
    Route::post('/profil/update', [ProfilController::class, 'update']);
    Route::post('/profil/hapus-foto', [ProfilController::class, 'hapusFoto']);

});