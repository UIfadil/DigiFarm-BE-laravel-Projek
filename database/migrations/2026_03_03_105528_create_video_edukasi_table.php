<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_edukasi', function (Blueprint $table) {
            $table->id();

            // edukasi_id = foreign key ke tabel edukasi
            // onDelete cascade = jika edukasi dihapus, videonya ikut terhapus
            $table->foreignId('edukasi_id')
                  ->constrained('edukasi')
                  ->onDelete('cascade');

            $table->string('judul_video');
            $table->string('video');       // URL atau path file
            $table->string('tipe_video')->default('link'); // 'link' atau 'file'
            $table->text('keterangan_video')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_edukasi');
    }
};