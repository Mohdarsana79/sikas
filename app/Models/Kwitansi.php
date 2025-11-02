<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kwitansi extends Model
{
    protected $fillable = [
        'sekolah_id',
        'penganggaran_id',
        'kode_kegiatan_id',
        'kode_rekening_id',
        'penerimaan_dana_id',
        'buku_kas_umum_id',
        'bku_uraian_detail_id',
    ];

    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function penganggaran()
    {
        return $this->belongsTo(Penganggaran::class);
    }

    public function kodeKegiatan()
    {
        return $this->belongsTo(KodeKegiatan::class);
    }

    public function rekeningBelanja()
    {
        return $this->belongsTo(RekeningBelanja::class, 'kode_rekening_id');
    }

    public function penerimaanDana()
    {
        return $this->belongsTo(PenerimaanDana::class);
    }

    public function bukuKasUmum()
    {
        return $this->belongsTo(BukuKasUmum::class);
    }

    public function bkuUraianDetail()
    {
        return $this->belongsTo(BukuKasUmumUraianDetail::class, 'bku_uraian_detail_id');
    }
}
