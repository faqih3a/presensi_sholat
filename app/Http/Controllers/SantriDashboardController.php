<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Presensi;

class SantriDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Ensure user has a linked santri profile
        if (!$user->santri) {
            return redirect('/')->withErrors(['error' => 'Profil santri tidak ditemukan untuk akun ini.']);
        }

        // Sync alfas before getting data
        $this->syncAlfas();

        $waktuSholat = $request->waktu_sholat;
        $period = $request->get('period', 'today');

        $today = \Carbon\Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $startDate = $today;
        $endDate = $today;

        if ($period === 'week') {
            $startDate = \Carbon\Carbon::now('Asia/Jakarta')->subDays(6)->format('Y-m-d');
        } elseif ($period === 'month') {
            $startDate = \Carbon\Carbon::now('Asia/Jakarta')->subDays(29)->format('Y-m-d');
        }

        // Get personal presensi history
        $query = Presensi::where('santri_id', $user->santri->id)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal', 'desc')
            ->orderBy('waktu_hadir', 'desc');

        if ($waktuSholat) {
            $query->where('waktu_sholat', $waktuSholat);
        }

        $presensis = $query->get();

        // Calculate totals based on filtered results
        $totalHadir = $presensis->where('status', 'Hadir')->count();
        $totalAlfa = $presensis->where('status', 'Alfa')->count();

        return view('santri.dashboard', compact('presensis', 'user', 'totalHadir', 'totalAlfa', 'period', 'waktuSholat'));
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->santri) {
            return redirect('/')->withErrors(['error' => 'Profil santri tidak ditemukan untuk akun ini.']);
        }

        $waktuSholat = $request->waktu_sholat;
        $period = $request->get('period', 'today');

        $today = \Carbon\Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $startDate = $today;
        $endDate = $today;

        if ($period === 'week') {
            $startDate = \Carbon\Carbon::now('Asia/Jakarta')->subDays(6)->format('Y-m-d');
        } elseif ($period === 'month') {
            $startDate = \Carbon\Carbon::now('Asia/Jakarta')->subDays(29)->format('Y-m-d');
        }

        $query = Presensi::where('santri_id', $user->santri->id)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal', 'desc')
            ->orderBy('waktu_hadir', 'desc');
            
        if ($waktuSholat) {
            $query->where('waktu_sholat', $waktuSholat);
        }
        
        $presensis = $query->get();
        
        $filename = "rekap_kehadiran_saya_" . date('Y-m-d_H-i-s') . ".csv";
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['No', 'Waktu Sholat', 'Tanggal', 'Waktu Hadir', 'Status'];
        
        $callback = function() use($presensis, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom =(chr(0xEF) . chr(0xBB) . chr(0xBF)));
            fputcsv($file, $columns);
            
            $no = 1;
            foreach ($presensis as $presensi) {
                fputcsv($file, [
                    $no++,
                    $presensi->waktu_sholat,
                    \Carbon\Carbon::parse($presensi->tanggal)->format('d M Y'),
                    $presensi->waktu_hadir ? \Carbon\Carbon::parse($presensi->waktu_hadir)->format('H:i:s') : '-',
                    $presensi->status
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    private function syncAlfas()
    {
        $now = \Carbon\Carbon::now('Asia/Jakarta');
        $today = $now->format('Y-m-d');
        
        $jadwal = $this->getJadwalSholat($now);
        if (!$jadwal) return;

        $mapping = [
            'Fajr' => 'Subuh',
            'Dhuhr' => 'Dzuhur',
            'Asr' => 'Ashar',
            'Maghrib' => 'Maghrib',
            'Isha' => 'Isya'
        ];

        $times = [
            'Subuh' => \Carbon\Carbon::parse($today . ' ' . $jadwal['Dhuhr'], 'Asia/Jakarta'),
            'Dzuhur' => \Carbon\Carbon::parse($today . ' ' . $jadwal['Asr'], 'Asia/Jakarta'),
            'Ashar' => \Carbon\Carbon::parse($today . ' ' . $jadwal['Maghrib'], 'Asia/Jakarta'),
            'Maghrib' => \Carbon\Carbon::parse($today . ' ' . $jadwal['Isha'], 'Asia/Jakarta'),
            'Isya' => \Carbon\Carbon::parse($today . ' 23:59:59', 'Asia/Jakarta'),
        ];

        $user = Auth::user();
        if (!$user->santri) return;
        $santriId = $user->santri->id;

        foreach ($times as $sholat => $endTime) {
            if ($now->greaterThan($endTime)) {
                Presensi::firstOrCreate([
                    'santri_id' => $santriId,
                    'tanggal' => $today,
                    'waktu_sholat' => $sholat,
                ], [
                    'status' => 'Alfa',
                    'waktu_hadir' => null
                ]);
            }
        }
        
        // Sync yesterday
        $yesterday = $now->copy()->subDay()->format('Y-m-d');
        $cacheKey = 'sync_alfa_' . $yesterday . '_santri_' . $santriId;
        if (!\Illuminate\Support\Facades\Cache::get($cacheKey)) {
            foreach ($mapping as $apiName => $sysName) {
                Presensi::firstOrCreate([
                    'santri_id' => $santriId,
                    'tanggal' => $yesterday,
                    'waktu_sholat' => $sysName,
                ], [
                    'status' => 'Alfa',
                    'waktu_hadir' => null
                ]);
            }
            \Illuminate\Support\Facades\Cache::put($cacheKey, true, 86400);
        }
    }

    private function getJadwalSholat(\Carbon\Carbon $date)
    {
        $cacheKey = 'jadwal_sholat_' . $date->format('Y-m-d');

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 86400, function () use ($date) {
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(5)->get('https://api.aladhan.com/v1/timingsByAddress', [
                    'address' => 'Malang, Indonesia',
                    'method' => 20, // Kemenag RI
                    'date' => $date->format('d-m-Y')
                ]);

                if ($response->successful()) {
                    return $response->json('data.timings');
                }
            } catch (\Exception $e) {
            }
            return null;
        });
    }
}
