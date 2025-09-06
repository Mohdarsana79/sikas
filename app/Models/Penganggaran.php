<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Penganggaran extends Model
{
    use HasFactory;

    protected $table = 'penganggarans';

    protected $fillable = [
        'sekolah_id', // Tambahkan foreign key jika belum ada
        'pagu_anggaran',
        'tahun_anggaran',
        'kepala_sekolah',
        'bendahara',
        'komite',
        'nip_kepala_sekolah',
        'nip_bendahara',
        'tanggal_cetak',
        'tanggal_perubahan'
    ];

    protected $casts = [
        'pagu_anggaran' => 'decimal:2',
        'tahun_anggaran' => 'integer',
    ];

    // Relasi dengan model Sekolah
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }

    // Relasi dengan model Rkas
    public function rkas()
    {
        return $this->hasMany(Rkas::class);
    }

    public function getFormatTanggalCetakAttribute()
    {
        if ($this->tanggal_cetak) {
            return $this->formatTanggalIndonesia($this->tanggal_cetak);
        }
        return 'Belum diisi';
    }

    public function getFormatTanggalPerubahanAttribute()
    {
        if ($this->tanggal_perubahan) {
            return $this->formatTanggalIndonesia($this->tanggal_perubahan);
        }
        return 'Belum diisi';
    }

    // Relasi Model Rkas Perubahan
    public function rkasPerubahan()
    {
        return $this->hasMany(RkasPerubahan::class);
    }

    private function formatTanggalIndonesia($date)
    {
        // Daftar nama bulan dalam bahasa Indonesia
        $bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        $date = Carbon::parse($date);
        return $date->day . ' ' . $bulan[$date->month] . ' ' . $date->year;
    }

    public function penerimaanDanas()
    {
        return $this->hasMany(PenerimaanDana::class, 'penganggaran_id');
    }
}
