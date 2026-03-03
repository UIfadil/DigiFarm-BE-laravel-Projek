<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Edukasi extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'edukasi';

    // Kolom yang boleh diisi
    protected $fillable = [
        'nama',
        'kategori',
        'deskripsi',
        'gambar',
        'solusi',
    ];

    // Relasi: 1 edukasi punya banyak video
    public function videoEdukasi()
    {
        return $this->hasMany(VideoEdukasi::class, 'edukasi_id');
    }
}