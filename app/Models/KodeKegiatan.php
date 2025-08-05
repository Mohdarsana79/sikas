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
}
