<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rename tabel, semua data lama tetap ada
        Schema::rename('hama_penyakit', 'edukasi');
    }

    public function down(): void
    {
        // Jika rollback, kembalikan nama semula
        Schema::rename('edukasi', 'hama_penyakit');
    }
};