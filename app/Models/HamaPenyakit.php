<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HamaPenyakit extends Model
{
    use HasFactory;

    protected $table = 'hama_penyakit'; // Nama tabel di DB

    protected $fillable = [
        'nama',
        'kategori',
        'deskripsi',
        'gambar',
    ];
}