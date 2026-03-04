<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soal_kuis', function (Blueprint $table) {
            $table->id();
            $table->text('pertanyaan');
            $table->string('gambar')->nullable(); // tidak semua soal ada gambar
            $table->enum('kategori', ['hama', 'penyakit', 'pertanian_dasar']);
            $table->string('opsi_a');
            $table->string('opsi_b');
            $table->string('opsi_c');
            $table->string('opsi_d');
            $table->enum('jawaban_benar', ['a', 'b', 'c', 'd']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soal_kuis');
    }
};