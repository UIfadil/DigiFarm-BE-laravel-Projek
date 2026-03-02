<?php
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\UserController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/admin/users', [UserController::class, 'index']); // Ambil semua user
    Route::put('/admin/users/{id}/role', [UserController::class, 'updateRole']); // Ubah role
    Route::delete('/admin/users/{id}', [UserController::class, 'destroy']); // Hapus user
});