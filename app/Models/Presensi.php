<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    protected $fillable = [
        'santri_id',
        'waktu_sholat',
        'tanggal',
        'waktu_hadir',
        'status',
    ];

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }
}
