<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KodeKegiatan extends Model
{
    //
    use HasFactory;
    protected $table = 'kode_kegiatans';
    protected $fillable = [
        'kode',
        'program',
        'sub_program',
        'uraian',
    ];

    public function rkas()
    {
        return $this->hasMany(Rkas::class, 'kode_id');
    }

    public function rkasPerubahan()
    {
        return $this->hasMany(RkasPerubahan::class, 'kode_id');
    }
}
