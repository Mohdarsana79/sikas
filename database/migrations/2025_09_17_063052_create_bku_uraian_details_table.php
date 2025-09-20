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
        Schema::create('bku_uraian_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buku_kas_umum_id')->constrained('buku_kas_umums')->onDelete('cascade');
            $table->foreignId('penganggaran_id')->constrained('penganggarans')->onDelete('cascade');
            $table->foreignId('kode_kegiatan_id')->constrained('kode_kegiatans')->onDelete('cascade');
            $table->foreignId('kode_rekening_id')->constrained('rekening_belanjas')->onDelete('cascade');
            $table->foreignId('rkas_id')->nullable()->constrained('rkas')->onDelete('set null');
            $table->foreignId('rkas_perubahan_id')->nullable()->constrained('rkas_perubahans')->onDelete('set null');
            $table->string('uraian');
            $table->string('satuan')->nullable();
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('volume', 10, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();

            $table->index(['buku_kas_umum_id', 'penganggaran_id']);
            $table->index(['kode_kegiatan_id', 'kode_rekening_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bku_uraian_details');
    }
};
