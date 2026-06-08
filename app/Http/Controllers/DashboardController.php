<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\Santri;
use App\Models\Izin;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $this->syncAlfas();
        
        $waktuSholat = $request->waktu_sholat;
        $today = \Carbon\Carbon::now('Asia/Jakarta')->format('Y-m-d');
        
        $tanggal_mulai = $request->get('tanggal_mulai', $today);
        $tanggal_akhir = $request->get('tanggal_akhir', $today);
        
        // Hitung total santri
        $totalSantri = \App\Models\Santri::count();
        
        $startDate = $tanggal_mulai;
        $endDate = $tanggal_akhir;

        // Hitung santri yang Hadir, Alfa, Izin menggunakan query tunggal GROUP BY
        $countsQuery = \App\Models\Presensi::whereBetween('tanggal', [$startDate, $endDate]);
        if ($waktuSholat) {
            $countsQuery->where('waktu_sholat', $waktuSholat);
        }
        $statusCounts = $countsQuery->groupBy('status')
                                    ->selectRaw('status, count(distinct santri_id) as total')
                                    ->pluck('total', 'status')
                                    ->toArray();

        $hadirHariIni = $statusCounts['Hadir'] ?? 0;
        $totalAlfa = $statusCounts['Alfa'] ?? 0;
        $totalIzin = $statusCounts['Izin'] ?? 0;

        // Untuk tampilan dashboard, "Tidak Hadir" mencakup Alfa dan Izin
        $tidakHadir = $totalAlfa + $totalIzin;
        
        // Persentase kehadiran
        $persentase = $totalSantri > 0 ? round(($hadirHariIni / $totalSantri) * 100, 1) : 0;

        // Fetch absent santris (Alfa or Izin) dengan Eager Loading
        $absentRecords = \App\Models\Presensi::whereBetween('tanggal', [$startDate, $endDate])
                                            ->whereIn('status', ['Alfa', 'Izin'])
                                            ->with('santri');
        if ($waktuSholat) {
            $absentRecords->where('waktu_sholat', $waktuSholat);
        }
        
        $absentRecords = $absentRecords->get();
        $absentSantris = $absentRecords->map(function($record) {
            $santri = $record->santri;
            if ($santri) {
                $santri->current_status = $record->status;
            }
            return $santri;
        })->filter()->unique('id');

        // Data untuk grafik kehadiran (optimasi 31 query menjadi 1 query tunggal GROUP BY)
        $chartLabels = [];
        $chartData = [];
        
        $start = \Carbon\Carbon::parse($startDate, 'Asia/Jakarta');
        $end = \Carbon\Carbon::parse($endDate, 'Asia/Jakarta');
        
        // limit to 31 days for chart if range is too big
        if ($start->diffInDays($end) > 31) {
            $start = $end->copy()->subDays(30);
        }

        $presensiCounts = \App\Models\Presensi::whereBetween('tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->groupBy('tanggal')
            ->selectRaw('tanggal, count(distinct santri_id) as total')
            ->pluck('total', 'tanggal')
            ->toArray();

        while ($start->lte($end)) {
            $dateStr = $start->format('Y-m-d');
            $chartLabels[] = $start->format('d M');
            $chartData[] = $presensiCounts[$dateStr] ?? 0;
            $start->addDay();
        }

        // Ambil jadwal sholat untuk tanggal akhir range
        $jadwal = $this->getJadwalSholat(\Carbon\Carbon::parse($endDate, 'Asia/Jakarta'));

        // Fetch records in one query
        $bothRecords = \App\Models\Presensi::whereBetween('tanggal', [$startDate, $endDate])
                                            ->whereIn('status', ['Izin', 'Alfa'])
                                            ->with('santri')
                                            ->get();

        $izinTodayRecords = $bothRecords->where('status', 'Izin')->groupBy('santri_id');
        $alfaTodayRecords = $bothRecords->where('status', 'Alfa')->groupBy('santri_id');

        // Identify santris with approved permits covering the range
        $fullDayIzinSantriIds = \App\Models\Santri::whereIn('user_id', function($query) use ($startDate, $endDate) {
            $query->select('user_id')
                  ->from('izins')
                  ->where('status', 'Disetujui')
                  ->where(function($q) use ($startDate, $endDate) {
                      $q->whereBetween('tanggal_mulai', [$startDate, $endDate])
                        ->orWhereBetween('tanggal_selesai', [$startDate, $endDate])
                        ->orWhere(function($sq) use ($startDate, $endDate) {
                            $sq->where('tanggal_mulai', '<=', $startDate)
                               ->where('tanggal_selesai', '>=', $endDate);
                        });
                  });
        })->pluck('id')->toArray();

        // Data for status distribution chart (optimasi 3 queries menjadi 1 query tunggal GROUP BY)
        $distData = \App\Models\Presensi::whereBetween('tanggal', [$startDate, $endDate]);
        if ($waktuSholat) {
            $distData->where('waktu_sholat', $waktuSholat);
        }
        $distCounts = $distData->groupBy('status')
                               ->selectRaw('status, count(*) as total')
                               ->pluck('total', 'status')
                               ->toArray();
                               
        $statusData = [
            $distCounts['Hadir'] ?? 0,
            $distCounts['Izin'] ?? 0,
            $distCounts['Alfa'] ?? 0,
        ];

        // Fetch latest activities from database
        $activities = collect();

        // 1. Santri baru didaftarkan
        $recentSantris = \App\Models\Santri::latest()->take(5)->get();
        foreach ($recentSantris as $s) {
            $activities->push([
                'title' => 'Santri baru didaftarkan',
                'subtitle' => $s->nama . ' dari Kelas ' . ($s->kelas ?? '-'),
                'time' => $s->created_at,
            ]);
        }

        // 2. Presensi sholat dicatat
        $recentPresensis = \App\Models\Presensi::with('santri')
            ->where('status', 'Hadir')
            ->latest()
            ->take(5)
            ->get();
        foreach ($recentPresensis as $p) {
            if ($p->santri) {
                $activities->push([
                    'title' => 'Presensi ' . $p->waktu_sholat . ' dicatat',
                    'subtitle' => $p->santri->nama . ' hadir tepat waktu',
                    'time' => $p->created_at ?: \Carbon\Carbon::parse($p->tanggal . ' ' . $p->waktu_hadir),
                ]);
            }
        }

        // 3. Pengajuan izin
        $recentIzins = \App\Models\Izin::with('user')
            ->latest()
            ->take(5)
            ->get();
        foreach ($recentIzins as $i) {
            if ($i->user) {
                $activities->push([
                    'title' => 'Pengajuan Izin ' . $i->jenis_izin,
                    'subtitle' => 'Oleh ' . $i->user->name . ' (' . $i->status . ')',
                    'time' => $i->created_at,
                ]);
            }
        }

        // Sort by time descending and take 3
        $latestActivities = $activities->sortByDesc('time')->take(3)->values()->all();

        if (empty($latestActivities)) {
            $latestActivities = [
                [
                    'title' => 'Sistem Presensi Berjalan',
                    'subtitle' => 'Presensi sholat santri aktif',
                    'time' => now(),
                ]
            ];
        }

        return view('dashboard.index', compact(
            'totalSantri', 'hadirHariIni', 'tidakHadir', 'persentase', 
            'jadwal', 'chartLabels', 'chartData', 'waktuSholat', 
            'absentSantris', 'izinTodayRecords', 'alfaTodayRecords', 'fullDayIzinSantriIds',
            'statusData', 'tanggal_mulai', 'tanggal_akhir', 'latestActivities'
        ));
    }

    public function kehadiran(Request $request)
    {
        $this->syncAlfas();
        $today = \Carbon\Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $tanggal_mulai = $request->get('tanggal_mulai', $today);
        $tanggal_akhir = $request->get('tanggal_akhir', $today);
        $waktuSholat = $request->get('waktu_sholat');
        $status = $request->get('status');
        $search = $request->get('search');

        // Fetch real records from the database
        $query = Presensi::with('santri')
                         ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir]);
                         
        if ($waktuSholat) {
            $query->where('waktu_sholat', $waktuSholat);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($search) {
            $query->whereHas('santri', function($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%');
            });
        }
        
        $presensis = $query->latest('tanggal')
                            ->latest('waktu_hadir')
                            ->get();

        return view('dashboard.kehadiran', compact('presensis', 'tanggal_mulai', 'tanggal_akhir', 'waktuSholat', 'status'));
    }

    public function exportKehadiran(Request $request)
    {
        $today = \Carbon\Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $tanggal_mulai = $request->get('tanggal_mulai', $today);
        $tanggal_akhir = $request->get('tanggal_akhir', $today);
        $waktuSholat = $request->get('waktu_sholat');
        $status = $request->get('status');
        $search = $request->get('search');

        // Fetch real records from the database
        $query = Presensi::with('santri')
                         ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir]);
                         
        if ($waktuSholat) {
            $query->where('waktu_sholat', $waktuSholat);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($search) {
            $query->whereHas('santri', function($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%');
            });
        }
        
        $presensis = $query->latest('tanggal')
                            ->latest('waktu_hadir')
                            ->get();
        
        $filename = "rekap_kehadiran_" . date('Y-m-d_H-i-s') . ".csv";
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['No', 'Nama Santri', 'Kelas', 'Waktu Sholat', 'Tanggal', 'Waktu Hadir', 'Status'];
        
        $callback = function() use($presensis, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom =(chr(0xEF) . chr(0xBB) . chr(0xBF)));
            fputcsv($file, $columns);
            $no = 1;
            foreach ($presensis as $presensi) {
                fputcsv($file, [
                    $no++,
                    $presensi->santri->nama,
                    $presensi->santri->kelas,
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
                    // Sanitize timings to remove timezone suffixes like (WIB)
                    foreach ($timings as $key => $time) {
                        $timings[$key] = substr($time, 0, 5);
                    }
                    return $timings;
                }
            } catch (\Exception $e) {
                // Log error if needed
            }
            
            return null;
        });
    }
}
