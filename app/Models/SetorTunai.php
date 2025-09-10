<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SetorTunai extends Model
{
    protected $fillable = [
        'penganggaran_id',
        'tanggal_setor',
        'jumlah_setor',
    ];

    protected $casts = [
        'tanggal_setor' => 'date',
        'jumlah_setor' => 'decimal:2'
    ];

    public function penganggaran(): BelongsTo
    {
        return $this->belongsTo(Penganggaran::class);
    }
}
