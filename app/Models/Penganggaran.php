<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Penganggaran extends Model
{
    //
    use HasFactory;

    protected $table = 'penganggarans';
    protected $fillable = [
        'pagu_anggaran',
        'tahun_anggaran',
        'kepala_sekolah',
        'bendahara',
        'komite',
        'nip_kepala_sekolah',
        'nip_bendahara',
    ];
}
