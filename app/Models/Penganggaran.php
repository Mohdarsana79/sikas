<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
}
