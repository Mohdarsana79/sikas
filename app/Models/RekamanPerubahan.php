<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RekamanPerubahan extends Model
{
    use SoftDeletes;

    protected $table = 'rekaman_perubahans';
    protected $fillable = ['rkas_perubahan_id', 'action', 'changes', 'user_id'];

    protected $casts = [
        'changes' => 'array'
    ];

    public function rkasPerubahan()
    {
        return $this->belongsTo(RkasPerubahan::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
