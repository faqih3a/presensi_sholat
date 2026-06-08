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
        $period = $request->get('period', 'day');

        $today = \Carbon\Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $tanggal_mulai = $request->get('tanggal_mulai', $today);
        $tanggal_akhir = $request->get('tanggal_akhir', $today);

        $startDate = $tanggal_mulai;
        $endDate = $tanggal_akhir;

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

        return view('santri.dashboard', compact(
            'presensis', 'user', 'totalHadir', 'totalAlfa', 
            'period', 'waktuSholat', 'tanggal_mulai', 'tanggal_akhir'
        ));
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->santri) {
            return redirect('/')->withErrors(['error' => 'Profil santri tidak ditemukan untuk akun ini.']);
        }

        $waktuSholat = $request->waktu_sholat;
        $period = $request->get('period', 'day');

        $today = \Carbon\Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $tanggal_mulai = $request->get('tanggal_mulai', $today);
        $tanggal_akhir = $request->get('tanggal_akhir', $today);

        $startDate = $tanggal_mulai;
        $endDate = $tanggal_akhir;

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
        \Illuminate\Support\Facades\Artisan::call('app:sync-alfas');
    }

    private function getJadwalSholat(\Carbon\Carbon $date)
    {
        $address = 'Bogor, Kecamatan Cibeureum, Kp Joglo, Indonesia';
        $cacheKey = 'jadwal_sholat_' . md5($address) . '_' . $date->format('Y-m-d');

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 86400, function () use ($date, $address) {
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(5)->get('https://api.aladhan.com/v1/timingsByAddress', [
                    'address' => $address,
                    'method' => 20, // Kemenag RI
                    'date' => $date->format('d-m-Y')
                ]);

                if ($response->successful()) {
                    $timings = $response->json('data.timings');
                    foreach ($timings as $key => $time) {
                        $timings[$key] = substr($time, 0, 5);
                    }
                    return $timings;
                }
            } catch (\Exception $e) {
            }
            return null;
        });
    }
}
