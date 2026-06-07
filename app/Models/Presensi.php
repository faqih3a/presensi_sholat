<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Presensi extends Model
{
    use SoftDeletes;

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
