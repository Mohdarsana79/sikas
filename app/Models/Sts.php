<?php

namespace App\Models;

use App\Models\Penganggaran;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sts extends Model
{
    use HasFactory;

    protected $table = 'status_sts_giros';

    protected $fillable = [
        'penganggaran_id',
        'nomor_sts',
        'jumlah_sts',
        'tanggal_bayar',
        'jumlah_bayar',
    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
        'jumlah_sts' => 'decimal:2',
        'jumlah_bayar' => 'decimal:2',
    ];

    // Relasi ke tabel penganggaran
    public function penganggaran()
    {
        return $this->belongsTo(Penganggaran::class);
    }
}
