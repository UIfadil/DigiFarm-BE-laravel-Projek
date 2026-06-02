<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $fillable = [
        'user_id',
        'foto',
        'bbox_image',
        'bbox',
        'detections',

        'disease_name',
        'latin_name',
        'accuracy',
        'status',
        'kategori',
        'description',
        'actions',
        'tanggal',
        'waktu',
    ];

    protected $casts = [
        'actions'    => 'array',
        'bbox'       => 'array',
        'detections' => 'array', // ← cast JSON → PHP array otomatis
    ];
}