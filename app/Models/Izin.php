<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Izin extends Model
{
    protected $fillable = [
        'user_id',
        'jenis_izin',
        'waktu_sholat',
        'tanggal_mulai',
        'tanggal_selesai',
        'keterangan',
        'lampiran',
        'status',
        'keterangan_admin',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
