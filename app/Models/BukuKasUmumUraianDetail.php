<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BukuKasUmumUraianDetail extends Model
{
    use HasFactory;

    protected $table = 'bku_uraian_details';

    protected $fillable = [
        'buku_kas_umum_id',
        'penganggaran_id',
        'kode_kegiatan_id',
        'kode_rekening_id',
        'rkas_id',
        'rkas_perubahan_id',
        'uraian',
        'satuan',
        'harga_satuan',
        'volume',
        'subtotal',
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function bukuKasUmum()
    {
        return $this->belongsTo(BukuKasUmum::class, 'buku_kas_umum_id');
    }

    public function penganggaran()
    {
        return $this->belongsTo(Penganggaran::class, 'penganggaran_id');
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

    // PERBAIKAN: Tambahkan relationship dengan Kwitansi
    public function kwitansi()
    {
        return $this->hasOne(Kwitansi::class, 'bku_uraian_detail_id');
    }

    // Relationship untuk cek apakah sudah memiliki kwitansi
    public function hasKwitansi()
    {
        return $this->kwitansi()->exists();
    }

    public function getHargaSatuanFormattedAttribute()
    {
        return 'Rp ' . number_format($this->harga_satuan, 0, ',', '.');
    }

    public function getSubtotalFormattedAttribute()
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    public function getVolumeFormattedAttribute()
    {
        return number_format($this->volume, 0) . ' ' . ($this->satuan ?? '');
    }

    // Di file BukuKasUmumUraianDetail.php
    // public static function getVolumeSudahDibelanjakan($penganggaran_id, $kegiatan_id, $rekening_id, $uraian, $bulan = null)
    // {
    //     $query = self::where('penganggaran_id', $penganggaran_id)
    //         ->where('kode_kegiatan_id', $kegiatan_id)
    //         ->where('kode_rekening_id', $rekening_id)
    //         ->where('uraian', 'LIKE', '%' . $uraian . '%');

    //     if ($bulan) {
    //         $bulanAngka = \App\Models\BukuKasUmum::convertBulanToNumber($bulan);
    //         $query->whereHas('bukuKasUmum', function ($q) use ($bulanAngka) {
    //             $q->whereMonth('tanggal_transaksi', $bulanAngka);
    //         });
    //     }

    //     return $query->sum('volume');
    // }
}
