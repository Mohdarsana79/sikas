<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BukuKasUmum extends Model
{
    use HasFactory;

    protected $table = 'buku_kas_umums';

    protected $fillable = [
        'penganggaran_id',
        'kode_kegiatan_id',
        'kode_rekening_id',
        'tanggal_transaksi',
        'jenis_transaksi',
        'id_transaksi',
        'nama_penyedia_barang_jasa',
        'nama_penerima_pembayaran',
        'alamat',
        'nomor_telepon',
        'npwp',
        'uraian',
        'anggaran',
        'dibelanjakan',
        'total_transaksi_kotor', // Tambahkan field ini
        'pajak',
        'persen_pajak',
        'total_pajak',
        'pajak_daerah',
        'persen_pajak_daerah',
        'total_pajak_daerah',
        'tanggal_lapor',
        'ntpn'
    ];

    protected $casts = [
        'tanggal_transaksi' => 'date',
        'anggaran' => 'decimal:2',
        'dibelanjakan' => 'decimal:2',
        'total_transaksi_kotor' => 'decimal:2', // Tambahkan casting
        'total_pajak' => 'decimal:2',
        'total_pajak_daerah' => 'decimal:2',
        'tanggal_lapor' => 'date',
    ];

    public function penganggaran()
    {
        return $this->belongsTo(Penganggaran::class);
    }

    public function kodeKegiatan()
    {
        return $this->belongsTo(KodeKegiatan::class, 'kode_kegiatan_id');
    }

    public function rekeningBelanja()
    {
        return $this->belongsTo(RekeningBelanja::class, 'kode_rekening_id');
    }

    // Method untuk menghitung pengaruh transaksi terhadap saldo
    public function getPengaruhSaldoAttribute()
    {
        return $this->dibelanjakan;
    }
}
