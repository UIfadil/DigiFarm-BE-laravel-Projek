<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserExp extends Model
{
    use HasFactory;

    protected $table = 'user_exp';

    protected $fillable = [
        'user_id',
        'total_exp',
        'level',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper: hitung level berdasarkan total EXP
    public static function hitungLevel(int $totalExp): int
    {
        if ($totalExp >= 1000) return 5; // Master
        if ($totalExp >= 500)  return 4; // Ahli
        if ($totalExp >= 250)  return 3; // Mahir
        if ($totalExp >= 100)  return 2; // Pelajar
        return 1;                        // Pemula
    }

    // Helper: nama level
    public static function namaLevel(int $level): string
    {
        return match($level) {
            1 => 'Pemula',
            2 => 'Pelajar',
            3 => 'Mahir',
            4 => 'Ahli',
            5 => 'Master',
            default => 'Pemula',
        };
    }

    // Helper: EXP minimum tiap level
    public static function expUntukLevel(int $level): int
    {
        return match($level) {
            1 => 0,
            2 => 100,
            3 => 250,
            4 => 500,
            5 => 1000,
            default => 0,
        };
    }
}