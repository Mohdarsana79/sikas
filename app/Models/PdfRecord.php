<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PdfRecord extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'tanggal_cetak',
        'total_tahap1',
        'total_tahap2',
        'keterangan'
    ];

    protected $casts = [
        'tanggal_cetak' => 'date',
        'total_tahap1' => 'decimal:2',
        'total_tahap2' => 'decimal:2'
    ];
}
