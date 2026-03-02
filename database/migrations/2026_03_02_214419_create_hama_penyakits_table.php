<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
        public function up(): void
    {
        Schema::create('hama_penyakit', function (Blueprint $table) { // Nama tabel manual
            $table->id();
            $table->string('nama');
            $table->enum('kategori', ['hama', 'penyakit']);
            $table->text('deskripsi');
            $table->string('gambar'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    // Pastikan nama tabel sama dengan yang ada di fungsi up()
    Schema::dropIfExists('hama_penyakit'); 
}
};
