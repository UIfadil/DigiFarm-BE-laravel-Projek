<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_kuis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->enum('kategori', ['semua', 'hama', 'penyakit', 'pertanian_dasar']);
            $table->integer('skor');           // 0 - 100
            $table->integer('jumlah_benar');   // 0 - 10
            $table->integer('jumlah_salah');   // 0 - 10
            $table->integer('exp_didapat');    // jumlah_benar * 10
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_kuis');
    }
};