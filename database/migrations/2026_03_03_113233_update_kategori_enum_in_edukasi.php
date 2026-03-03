<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('edukasi', function (Blueprint $table) {
            $table->enum('kategori', ['hama', 'penyakit', 'pertanian_dasar'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('edukasi', function (Blueprint $table) {
            $table->enum('kategori', ['hama', 'penyakit'])->change();
        });
    }
};