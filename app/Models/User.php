<?php

namespace App\Models;

// 1. Pastikan baris ini ada di bagian atas
use Laravel\Sanctum\HasApiTokens; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    // 2. Tambahkan HasApiTokens di dalam baris use ini
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Pastikan role juga ada di sini jika Anda menggunakannya
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}