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
        Schema::create('histories', function (Blueprint $table) {
            $table->id();

            $table->string('foto')->nullable();
            $table->string('bbox_image')->nullable();

            $table->json('bbox')->nullable();

            $table->string('disease_name');
            $table->string('latin_name');
            $table->float('accuracy');
            $table->string('status');
            $table->string('kategori');

            $table->text('description');

            $table->json('actions');

            $table->date('tanggal');
            $table->string('waktu');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('histories');
    }
};