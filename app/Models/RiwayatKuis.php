<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatKuis extends Model
{
    use HasFactory;

    protected $table = 'riwayat_kuis';

    protected $fillable = [
        'user_id',
        'kategori',
        'skor',
        'jumlah_benar',
        'jumlah_salah',
        'exp_didapat',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}