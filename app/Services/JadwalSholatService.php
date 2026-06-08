<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class JadwalSholatService
{
    public static function getJadwal(Carbon $date)
    {
        $address = 'Bogor, Kecamatan Cibeureum, Kp Joglo, Indonesia';
        $cacheKey = 'jadwal_sholat_' . md5($address) . '_' . $date->format('Y-m-d');

        // Check if cached first
        $jadwal = Cache::get($cacheKey);
        if ($jadwal && is_array($jadwal)) {
            return $jadwal;
        }

        try {
            $response = Http::timeout(5)->get('https://api.aladhan.com/v1/timingsByAddress', [
                'address' => $address,
                'method' => 20, // Kemenag RI
                'date' => $date->format('d-m-Y')
            ]);

            if ($response->successful()) {
                $timings = $response->json('data.timings');
                if (is_array($timings)) {
                    foreach ($timings as $key => $time) {
                        $timings[$key] = substr($time, 0, 5);
                    }
                    Cache::put($cacheKey, $timings, 86400);
                    return $timings;
                }
            }
        } catch (\Exception $e) {
            // Ignore API exceptions and fall back
        }

        // Fallback timings for Bogor, Indonesia if API fails/timeouts
        return [
            'Fajr' => '04:35',
            'Dhuhr' => '11:55',
            'Asr' => '15:15',
            'Maghrib' => '17:50',
            'Isha' => '19:05'
        ];
    }
}
