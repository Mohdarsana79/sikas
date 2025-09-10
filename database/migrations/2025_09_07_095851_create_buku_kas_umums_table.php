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
            $table->foreignId('penganggaran_id')->constrained('penganggarans')->onDelete('cascade');
            $table->foreignId('kode_kegiatan_id')->constrained('kode_kegiatans')->onDelete('cascade');
            $table->foreignId('kode_rekening_id')->constrained('rekening_belanjas')->onDelete('cascade');
            $table->date('tanggal_transaksi');
            $table->enum('jenis_transaksi', ['tunai', 'non-tunai']);
            $table->string('id_transaksi'); // Nomor Nota
            $table->string('nama_penyedia_barang_jasa')->nullable();
            $table->string('nama_penerima_pembayaran')->nullable();
            $table->string('alamat')->nullable();
            $table->string('nomor_telepon')->nullable();
            $table->string('npwp')->nullable();
            $table->text('uraian'); // Lunas Bayar Belanja {uraian}
            $table->decimal('anggaran', 15, 2); // Total anggaran
            $table->decimal('dibelanjakan', 15, 2);
            $table->decimal('total_transaksi_kotor', 15, 2)->nullable(); // Tambahkan field ini
            $table->string('pajak')->nullable();
            $table->integer('persen_pajak')->nullable();
            $table->decimal('total_pajak', 15, 2)->nullable();
            $table->string('pajak_daerah')->nullable();
            $table->integer('persen_pajak_daerah')->nullable();
            $table->decimal('total_pajak_daerah', 15, 2)->nullable();
            $table->date('tanggal_lapor')->nullable();
            $table->string('ntpn')->nullable();
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
