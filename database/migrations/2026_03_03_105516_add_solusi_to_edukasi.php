<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('edukasi', function (Blueprint $table) {
            // Tambah kolom solusi setelah kolom deskripsi
            // nullable = boleh kosong, tidak wajib diisi
            $table->text('solusi')->nullable()->after('deskripsi');
        });
    }

    public function down(): void
    {
        Schema::table('edukasi', function (Blueprint $table) {
            $table->dropColumn('solusi');
        });
    }
};