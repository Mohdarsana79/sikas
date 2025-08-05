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
}
