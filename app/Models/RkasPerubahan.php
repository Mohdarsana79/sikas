<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class RkasPerubahan extends Model
{
    //
    use softDeletes;
    protected $table = 'rkas_perubahans';
    protected $fillable = [
        'penganggaran_id',
        'kode_id',
        'kode_rekening_id',
        'uraian',
        'harga_satuan',
        'bulan',
        'jumlah',
        'satuan',
        'rkas_id' // Kolom untuk relasi ke data RKAS asli
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
        'jumlah' => 'integer',
    ];

    protected $dates = ['deleted_at'];

    public function penganggaran()
    {
        return $this->belongsTo(Penganggaran::class);
    }

    // PERBAIKAN: Sesuaikan nama relasi dan foreign key
    public function kodeKegiatan()
    {
        return $this->belongsTo(KodeKegiatan::class, 'kode_id');
    }

    public function rekeningBelanja()
    {
        return $this->belongsTo(RekeningBelanja::class, 'kode_rekening_id');
    }

    /**
     * Relasi ke data RKAS asli.
     */
    public function rkasAsli()
    {
        return $this->belongsTo(Rkas::class, 'rkas_id');
    }

    /**
     * Menghitung total anggaran Tahap 1 untuk penganggaran tertentu.
     */
    public static function getTotalTahap1($penganggaranId)
    {
        $tahap1Months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'];
        return self::where('penganggaran_id', $penganggaranId)
            ->whereIn('bulan', $tahap1Months)
            ->sum(DB::raw('jumlah * harga_satuan'));
    }

    /**
     * Menghitung total anggaran Tahap 2 untuk penganggaran tertentu.
     */
    public static function getTotalTahap2($penganggaranId)
    {
        $tahap2Months = ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        return self::where('penganggaran_id', $penganggaranId)
            ->whereIn('bulan', $tahap2Months)
            ->sum(DB::raw('jumlah * harga_satuan'));
    }

    /**
     * Mendapatkan daftar bulan.
     */
    public static function getBulanList()
    {
        return [
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        ];
    }

    public static function getAllowedBulanList()
    {
        return [
            'September',
            'Oktober',
            'November',
            'Desember'
        ];
    }

    public static function getBulanTahap1()
    {
        return ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'];
    }

    /**
     * Static method untuk mendapatkan daftar bulan Tahap 2
     */
    public static function getBulanTahap2()
    {
        return ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    }

    /**
     * Scope untuk filter berdasarkan tahap.
     */
    public function scopeByTahap($query, $tahap, $penganggaranId = null)
    {
        $bulanTahap1 = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'];
        $bulanTahap2 = ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $query->whereIn('bulan', $tahap == 1 ? $bulanTahap1 : $bulanTahap2);

        if ($penganggaranId) {
            $query->where('penganggaran_id', $penganggaranId);
        }

        return $query;
    }

    /**
     * Accessor untuk menghitung total anggaran
     */
    public function getTotalAnggaranAttribute()
    {
        return $this->harga_satuan * $this->jumlah;
    }

    /**
     * Method untuk mendapatkan data perubahan dengan data asli
     */
    public static function getDataPerubahanWithAsli($penganggaranId)
    {
        return self::with(['rkasAsli', 'kodeKegiatan', 'rekeningBelanja'])
            ->where('penganggaran_id', $penganggaranId)
            ->get()
            ->map(function ($item) {
                $item->jumlah_asli = $item->rkasAsli ? $item->rkasAsli->jumlah : 0;
                $item->harga_satuan_asli = $item->rkasAsli ? $item->rkasAsli->harga_satuan : 0;
                $item->total_asli = $item->rkasAsli ? $item->rkasAsli->jumlah * $item->rkasAsli->harga_satuan : 0;
                $item->total_perubahan = $item->jumlah * $item->harga_satuan;
                $item->selisih = $item->total_perubahan - $item->total_asli;
                $item->bertambah = $item->selisih > 0 ? $item->selisih : 0;
                $item->berkurang = $item->selisih < 0 ? abs($item->selisih) : 0;

                return $item;
            });
    }

    /**
     * Scope untuk mendapatkan data termasuk yang sudah dihapus
     */
    public function scopeWithTrashedData($query)
    {
        return $query->withTrashed();
    }
}
