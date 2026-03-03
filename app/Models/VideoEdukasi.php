<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoEdukasi extends Model
{
    use HasFactory;

    protected $table = 'video_edukasi';

    protected $fillable = [
        'edukasi_id',
        'judul_video',
        'video',
        'tipe_video',
        'keterangan_video',
    ];

    // Relasi: video ini milik 1 edukasi
    public function edukasi()
    {
        return $this->belongsTo(Edukasi::class, 'edukasi_id');
    }
}