<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenarikanTunai extends Model
{
    protected $fillable = [
        'penganggaran_id',
        'tanggal_penarikan',
        'jumlah_penarikan',
    ];

    protected $casts = [
        'tanggal_penarikan' => 'date',
        'jumlah_penarikan' => 'decimal:2'
    ];

    public function penganggaran(): BelongsTo
    {
        return $this->belongsTo(Penganggaran::class);
    }
}
