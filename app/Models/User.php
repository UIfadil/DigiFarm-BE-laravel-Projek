<?php
namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'foto_profil', // ✅ tambah ini
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // Jika foto ada  → kembalikan full URL storage
    // Jika foto null → kembalikan null (frontend pakai avatar default)
    public function getFotoProfilUrlAttribute(): ?string
    {
        if (!$this->foto_profil) return null;
        
        // ✅ Pakai URL dari request yang masuk, bukan APP_URL
        $baseUrl = request()->getSchemeAndHttpHost();
        return $baseUrl . '/storage/' . $this->foto_profil;
    }
    protected $with = [];

    protected $appends = ['foto_profil_url'];
}