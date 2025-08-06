<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sekolah extends Model
{
    use HasFactory;

    protected $table = 'sekolahs';

    protected $fillable = [
        'nama_sekolah',
        'npsn',
        'kelurahan_desa',
        'kecamatan',
        'kabupaten_kota',
        'provinsi',
        'alamat',
    ];

    // Relasi dengan model Penganggaran
    public function penganggarans()
    {
        return $this->hasMany(Penganggaran::class);
    }

    // Alias untuk relasi tunggal (jika diperlukan)
    public function penganggaran()
    {
        return $this->hasOne(Penganggaran::class)->orderBy('tahun_anggaran', 'desc');
    }
}
