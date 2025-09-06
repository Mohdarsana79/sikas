<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BukuKasUmum extends Model
{
    use HasFactory;

    protected $table = 'buku_kas_umums';

    protected $fillable = [
        'penerimaan_dana_id',
        'kode_kegiatan_id',
        'kode_rekening_id',
        'rkas_id',
        'rkas_perubahan_id',
        'id_transaksi',
        'jenis_transaksi',
        'anggaran',
        'dibelanjakan',
        'pajak',
        'persen_pajak',
        'tanggal_lapor',
        'ntpn',
        'tanggal_transaksi',
        'nama_penyedia_barang_jasa'
    ];

    protected $casts = [
        'anggaran' => 'decimal:2',
        'dibelanjakan' => 'decimal:2',
        'tanggal_lapor' => 'date',
        'tanggal_transaksi' => 'date',
    ];

    public function penerimaanDana()
    {
        return $this->belongsTo(PenerimaanDana::class, 'penerimaan_dana_id');
    }

    public function kodeKegiatan()
    {
        return $this->belongsTo(KodeKegiatan::class, 'kode_kegiatan_id');
    }

    public function rekeningBelanja()
    {
        return $this->belongsTo(RekeningBelanja::class, 'kode_rekening_id');
    }

    public function rkas()
    {
        return $this->belongsTo(Rkas::class, 'rkas_id');
    }

    public function rkasPerubahan()
    {
        return $this->belongsTo(RkasPerubahan::class, 'rkas_perubahan_id');
    }
}
