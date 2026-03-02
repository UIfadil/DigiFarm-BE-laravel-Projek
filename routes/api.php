<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\HamaPenyakitController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    // ─── Manajemen User ───
    Route::get('/admin/users', [UserController::class, 'index']);
    Route::put('/admin/users/{id}/role', [UserController::class, 'updateRole']);
    Route::delete('/admin/users/{id}', [UserController::class, 'destroy']);

    // ─── Manajemen Hama & Penyakit ───
    Route::get('/admin/hama-penyakit', [HamaPenyakitController::class, 'index']);
    Route::post('/admin/hama-penyakit', [HamaPenyakitController::class, 'store']);
    Route::post('/admin/hama-penyakit/{id}/update', [HamaPenyakitController::class, 'update']);
    Route::delete('/admin/hama-penyakit/{id}', [HamaPenyakitController::class, 'destroy']);

});