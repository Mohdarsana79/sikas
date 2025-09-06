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
        Schema::create('buku_kas_umums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penerimaan_dana_id')->constrained('penerimaan_danas')->onDelete('cascade');
            $table->foreignId('kode_kegiatan_id')->constrained('kode_kegiatans')->onDelete('cascade');
            $table->foreignId('kode_rekening_id')->constrained('rekening_belanjas')->onDelete('cascade');
            $table->foreignId('rkas_id')->constrained('rkas')->onDelete('cascade');
            $table->foreignId('rkas_perubahan_id')->constrained('rkas_perubahans')->onDelete('cascade');
            $table->string('id_transaksi')->nullable();
            $table->enum('jenis_transaksi', ['tunai', 'non-tunai']);
            $table->bigInteger('anggaran');
            $table->bigInteger('dibelanjakan');
            $table->enum('pajak', ['pph21', 'pph22', 'pph23', 'ppn', 'pb1'])->nullable();
            $table->integer('persen_pajak')->nullable();
            $table->date('tanggal_lapor')->nullable();
            $table->string('ntpn')->nullable();
            $table->date('tanggal_transaksi');
            $table->string('nama_penyedia_barang_jasa');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buku_kas_umums');
    }
};
