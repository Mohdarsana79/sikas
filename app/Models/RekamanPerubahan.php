<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekamanPerubahan extends Model
{
    //
    protected $table = 'rekaman_perubahans';
    protected $fillable = ['rkas_perubahan_id', 'action', 'changes', 'user_id'];

    public function rkasPerubahan()
    {
        return $this->belongsTo(RkasPerubahan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
