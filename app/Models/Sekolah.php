<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sekolah extends Model
{
    //
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
}
