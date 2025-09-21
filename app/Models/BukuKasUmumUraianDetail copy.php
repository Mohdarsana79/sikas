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
        'subtotal'
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
        'volume' => 'decimal:2',
        'subtotal' => 'decimal:2'
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
}
