<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Presensi;
use App\Models\Santri;
use App\Models\Izin;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:sync-alfas', function () {
    $now = Carbon::now('Asia/Jakarta');
    $today = $now->format('Y-m-d');
    $yesterday = $now->copy()->subDay()->format('Y-m-d');
    
    // Get prayer schedule
    $address = 'Bogor, Kecamatan Cibeureum, Kp Joglo, Indonesia';
    $cacheKey = 'jadwal_sholat_' . md5($address) . '_' . $now->format('Y-m-d');
    $jadwal = Cache::remember($cacheKey, 86400, function () use ($now, $address) {
        try {
            $response = Http::timeout(5)->get('https://api.aladhan.com/v1/timingsByAddress', [
                'address' => $address,
                'method' => 20, // Kemenag RI
                'date' => $now->format('d-m-Y')
            ]);
            if ($response->successful()) {
                $timings = $response->json('data.timings');
                foreach ($timings as $key => $time) {
                    $timings[$key] = substr($time, 0, 5);
                }
                return $timings;
            }
        } catch (\Exception $e) {
            // Log or ignore
        }
        return null;
    });

    if (!$jadwal) {
        $this->error('Failed to retrieve prayer times.');
        return;
    }

    $mapping = [
        'Fajr' => 'Subuh',
        'Dhuhr' => 'Dzuhur',
        'Asr' => 'Ashar',
        'Maghrib' => 'Maghrib',
        'Isha' => 'Isya'
    ];

    $times = [
        'Subuh' => Carbon::parse($today . ' ' . $jadwal['Fajr'], 'Asia/Jakarta')->addMinutes(10),
        'Dzuhur' => Carbon::parse($today . ' ' . $jadwal['Dhuhr'], 'Asia/Jakarta')->addMinutes(10),
        'Ashar' => Carbon::parse($today . ' ' . $jadwal['Asr'], 'Asia/Jakarta')->addMinutes(10),
        'Maghrib' => Carbon::parse($today . ' ' . $jadwal['Maghrib'], 'Asia/Jakarta')->addMinutes(10),
        'Isya' => Carbon::parse($today . ' ' . $jadwal['Isha'], 'Asia/Jakarta')->addMinutes(10),
    ];

    $santris = Santri::all();
    $activeIzins = Izin::where('status', 'Disetujui')
        ->where(function($q) use ($today, $yesterday) {
            $q->whereDate('tanggal_mulai', '<=', $today)
              ->whereDate('tanggal_selesai', '>=', $yesterday);
        })
        ->get();
    $activeIzinsGrouped = $activeIzins->groupBy('user_id');

    foreach ($times as $sholat => $endTime) {
        if ($now->greaterThan($endTime)) {
            $syncCacheKey = 'sync_alfa_' . $today . '_' . $sholat;
            if (Cache::has($syncCacheKey)) {
                continue;
            }

            $presentSantriIds = Presensi::withTrashed()
                                        ->where('tanggal', $today)
                                        ->where('waktu_sholat', $sholat)
                                        ->pluck('santri_id')
                                        ->toArray();

            foreach ($santris as $santri) {
                if (!in_array($santri->id, $presentSantriIds)) {
                    $userIzins = $activeIzinsGrouped->get($santri->user_id) ?? collect();
                    $hasIzin = $userIzins->contains(function ($izin) use ($today) {
                        return $today >= $izin->tanggal_mulai && $today <= $izin->tanggal_selesai;
                    });
                    
                    $status = $hasIzin ? 'Izin' : 'Alfa';

                    Presensi::firstOrCreate([
                        'santri_id' => $santri->id,
                        'tanggal' => $today,
                        'waktu_sholat' => $sholat,
                    ], [
                        'status' => $status,
                        'waktu_hadir' => null
                    ]);
                }
            }

            Cache::put($syncCacheKey, true, 86400);
        }
    }

    // Check yesterday's sync as well
    $hasYesterdaySync = Cache::get('sync_alfa_' . $yesterday);
    if (!$hasYesterdaySync) {
        foreach ($mapping as $apiName => $sysName) {
            $presentSantriIds = Presensi::withTrashed()
                                        ->where('tanggal', $yesterday)
                                        ->where('waktu_sholat', $sysName)
                                        ->pluck('santri_id')
                                        ->toArray();

            foreach ($santris as $santri) {
                if (!in_array($santri->id, $presentSantriIds)) {
                    $userIzins = $activeIzinsGrouped->get($santri->user_id) ?? collect();
                    $hasIzin = $userIzins->contains(function ($izin) use ($yesterday) {
                        return $yesterday >= $izin->tanggal_mulai && $yesterday <= $izin->tanggal_selesai;
                    });
                    
                    $status = $hasIzin ? 'Izin' : 'Alfa';

                    Presensi::firstOrCreate([
                        'santri_id' => $santri->id,
                        'tanggal' => $yesterday,
                        'waktu_sholat' => $sysName,
                    ], [
                        'status' => $status,
                        'waktu_hadir' => null
                    ]);
                }
            }
        }
        Cache::put('sync_alfa_' . $yesterday, true, 86400);
    }

    $this->info('Absent sync completed successfully.');
})->purpose('Sync absent santris as Alfa or Izin status');

// Schedule to run the command every 5 minutes in background
Schedule::command('app:sync-alfas')->everyFiveMinutes();
