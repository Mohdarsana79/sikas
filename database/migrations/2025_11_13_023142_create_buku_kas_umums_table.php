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
            $table->foreignId('kode_kegiatan_id')->nullable()->constrained('kode_kegiatans')->onDelete('cascade');
            $table->foreignId('kode_rekening_id')->nullable()->constrained('rekening_belanjas')->onDelete('cascade');
            $table->date('tanggal_transaksi');
            $table->enum('jenis_transaksi', ['tunai', 'non-tunai'])->default('non-tunai');
            $table->string('id_transaksi'); // Nomor Nota
            $table->string('nama_penyedia_barang_jasa')->nullable();
            $table->string('nama_penerima_pembayaran')->nullable();
            $table->string('alamat')->nullable();
            $table->string('nomor_telepon')->nullable();
            $table->string('npwp')->nullable();
            $table->text('uraian'); // Lunas Bayar Belanja {uraian}
            $table->text('uraian_opsional')->nullable(); // Uraian tambahan dari user
            $table->decimal('anggaran', 15, 2)->default(0);
            $table->decimal('dibelanjakan', 15, 2)->default(0);
            $table->decimal('total_transaksi_kotor', 15, 2)->default(0);
            $table->string('pajak')->nullable();
            $table->integer('persen_pajak')->nullable();
            $table->decimal('total_pajak', 15, 2)->nullable();
            $table->string('pajak_daerah')->nullable();
            $table->integer('persen_pajak_daerah')->nullable();
            $table->decimal('total_pajak_daerah', 15, 2)->nullable();
            $table->date('tanggal_lapor')->nullable();
            $table->string('kode_masa_pajak')->nullable();
            $table->string('ntpn')->nullable();
            $table->decimal('bunga_bank', 15, 2)->nullable()->default(0);
            $table->decimal('pajak_bunga_bank', 15, 2)->nullable()->default(0);
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->boolean('is_bunga_record')->default(false); // Flag untuk menandai record bunga bank
            $table->boolean('closed_without_spending')->default(false);
            $table->string('status_bulan')->nullable();
            $table->timestamps();
            $table->index(['penganggaran_id']);
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
