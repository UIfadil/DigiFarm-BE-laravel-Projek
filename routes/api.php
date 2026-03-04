<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\EdukasiController;
use App\Http\Controllers\Api\Admin\VideoEdukasiController;
use App\Http\Controllers\Api\Admin\SoalKuisController;

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

});