<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekeningBelanja extends Model
{
    // 
    protected $table = 'rekening_belanjas';
    protected $fillable = [
        'kode_rekening',
        'rincian_objek',
        'kategori'
    ];

    public function rkas()
    {
        return $this->hasMany(Rkas::class, 'kode_rekening_id');
    }

    public function rkasPerubahan()
    {
        return $this->hasMany(RkasPerubahan::class, 'kode_rekening_id');
    }

    public function bukuKasUmum()
    {
        return $this->hasMany(BukuKasUmum::class, 'kode_rekening_id');
    }

    public function tandaTerimas()
    {
        return $this->hasMany(TandaTerima::class, 'kode_rekening_id');
    }
}
