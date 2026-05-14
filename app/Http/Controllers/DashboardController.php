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

        // Hitung santri yang hadir (status Hadir) dalam periode tersebut
        $hadirQuery = \App\Models\Presensi::whereBetween('tanggal', [$startDate, $endDate])->where('status', 'Hadir');
        if ($waktuSholat) {
            $hadirQuery->where('waktu_sholat', $waktuSholat);
        }
        $hadirHariIni = $hadirQuery->distinct('santri_id')->count('santri_id');
        
        // Hitung santri yang Alfa (status Alfa) dalam periode tersebut
        $alfaQuery = \App\Models\Presensi::whereBetween('tanggal', [$startDate, $endDate])->where('status', 'Alfa');
        if ($waktuSholat) {
            $alfaQuery->where('waktu_sholat', $waktuSholat);
        }
        $totalAlfa = $alfaQuery->distinct('santri_id')->count('santri_id');

        // Hitung santri yang Izin (status Izin) dalam periode tersebut
        $izinQuery = \App\Models\Presensi::whereBetween('tanggal', [$startDate, $endDate])->where('status', 'Izin');
        if ($waktuSholat) {
            $izinQuery->where('waktu_sholat', $waktuSholat);
        }
        $totalIzin = $izinQuery->distinct('santri_id')->count('santri_id');

        // Untuk tampilan dashboard, "Tidak Hadir" mencakup Alfa dan Izin
        $tidakHadir = $totalAlfa + $totalIzin;
        
        // Persentase kehadiran
        $persentase = $totalSantri > 0 ? round(($hadirHariIni / $totalSantri) * 100, 1) : 0;

        // Fetch absent santris (Alfa or Izin)
        $absentSantris = collect();
        $absentRecords = \App\Models\Presensi::whereBetween('tanggal', [$startDate, $endDate])
                                            ->whereIn('status', ['Alfa', 'Izin']);
        if ($waktuSholat) {
            $absentRecords->where('waktu_sholat', $waktuSholat);
        }
        
        $absentRecords = $absentRecords->get();
        $absentSantriIds = $absentRecords->pluck('santri_id')->unique();
        $santriModels = \App\Models\Santri::whereIn('id', $absentSantriIds)->get()->keyBy('id');

        $absentSantris = $absentRecords->map(function($record) use ($santriModels) {
            $santri = $santriModels->get($record->santri_id);
            if ($santri) {
                $santri->current_status = $record->status;
            }
            return $santri;
        })->filter()->unique('id');

        // Data untuk grafik kehadiran
        $chartLabels = [];
        $chartData = [];
        
        $start = \Carbon\Carbon::parse($startDate, 'Asia/Jakarta');
        $end = \Carbon\Carbon::parse($endDate, 'Asia/Jakarta');
        
        // limit to 31 days for chart if range is too big
        if ($start->diffInDays($end) > 31) {
            $start = $end->copy()->subDays(30);
        }

        while ($start->lte($end)) {
            $dateStr = $start->format('Y-m-d');
            $chartLabels[] = $start->format('d M');
            
            $count = \App\Models\Presensi::where('tanggal', $dateStr)
                                         ->distinct('santri_id')
                                         ->count('santri_id');
            $chartData[] = $count;
            $start->addDay();
        }

        // Ambil jadwal sholat untuk tanggal akhir range
        $jadwal = $this->getJadwalSholat(\Carbon\Carbon::parse($endDate, 'Asia/Jakarta'));

        // Fetch specifically for the range's Izin and Alfa lists
        $izinTodayRecords = \App\Models\Presensi::whereBetween('tanggal', [$startDate, $endDate])
                                            ->where('status', 'Izin')
                                            ->with('santri')
                                            ->get()
                                            ->groupBy('santri_id');

        $alfaTodayRecords = \App\Models\Presensi::whereBetween('tanggal', [$startDate, $endDate])
                                            ->where('status', 'Alfa')
                                            ->with('santri')
                                            ->get()
                                            ->groupBy('santri_id');

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

        // Data for status distribution chart
        $distQuery = \App\Models\Presensi::whereBetween('tanggal', [$startDate, $endDate]);
        if ($waktuSholat) {
            $distQuery->where('waktu_sholat', $waktuSholat);
        }
        
        $statusData = [
            (clone $distQuery)->where('status', 'Hadir')->count(),
            (clone $distQuery)->where('status', 'Izin')->count(),
            (clone $distQuery)->where('status', 'Alfa')->count(),
        ];

        return view('dashboard.index', compact(
            'totalSantri', 'hadirHariIni', 'tidakHadir', 'persentase', 
            'jadwal', 'chartLabels', 'chartData', 'waktuSholat', 
            'absentSantris', 'izinTodayRecords', 'alfaTodayRecords', 'fullDayIzinSantriIds',
            'statusData', 'tanggal_mulai', 'tanggal_akhir'
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

        $now = \Carbon\Carbon::now('Asia/Jakarta');
        
        // Fetch real records
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
        
        $realRecords = $query->get();
        $realRecordsGrouped = $realRecords->groupBy(['tanggal', 'santri_id', 'waktu_sholat']);

        // Synthesize missing records for the entire range
        $synthesized = collect();
        $sholats = ['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'];
        $sholatList = $waktuSholat ? [$waktuSholat] : $sholats;

        $santriQuery = Santri::query();
        if ($search) $santriQuery->where('nama', 'like', '%' . $search . '%');
        $santris = $santriQuery->get();

        $currentDate = \Carbon\Carbon::parse($tanggal_mulai, 'Asia/Jakarta');
        $endRangeDate = \Carbon\Carbon::parse($tanggal_akhir, 'Asia/Jakarta');

        while ($currentDate->lte($endRangeDate)) {
            $dateStr = $currentDate->format('Y-m-d');
            $jadwal = $this->getJadwalSholat($currentDate);
            $times = $jadwal ? $this->getPrayerEndTimes($dateStr, $jadwal) : [];

            foreach ($santris as $santri) {
                foreach ($sholatList as $s) {
                    if (!isset($realRecordsGrouped[$dateStr][$santri->id][$s])) {
                        $hasIzin = Izin::where('user_id', $santri->user_id)
                                                ->where('status', 'Disetujui')
                                                ->whereDate('tanggal_mulai', '<=', $dateStr)
                                                ->whereDate('tanggal_selesai', '>=', $dateStr)
                                                ->exists();

                        $virtualStatus = $hasIzin ? 'Izin' : 'Alfa';
                        
                        if (!$status || $status === $virtualStatus) {
                            // Check if time has passed for this prayer
                            if ($dateStr === $now->format('Y-m-d') && $jadwal && isset($times[$s])) {
                                if ($now->lessThan($times[$s])) {
                                    continue; // Skip if it's not yet time to be Alfa today
                                }
                            } elseif ($currentDate->greaterThan($now)) {
                                continue; // Skip future dates
                            }

                            $synthesized->push((object) [
                                'santri' => $santri,
                                'santri_id' => $santri->id,
                                'waktu_sholat' => $s,
                                'tanggal' => $dateStr,
                                'waktu_hadir' => null,
                                'status' => $virtualStatus
                            ]);
                        }
                    }
                }
            }
            $currentDate->addDay();
        }

        $presensis = $realRecords->concat($synthesized)
                                ->sortByDesc('waktu_hadir')
                                ->sortByDesc('tanggal');

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

        $now = \Carbon\Carbon::now('Asia/Jakarta');
        
        // Fetch real records
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
        
        $realRecords = $query->get();
        $realRecordsGrouped = $realRecords->groupBy(['tanggal', 'santri_id', 'waktu_sholat']);

        // Synthesize missing records
        $synthesized = collect();
        $sholats = ['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'];
        $sholatList = $waktuSholat ? [$waktuSholat] : $sholats;

        $santriQuery = Santri::query();
        if ($search) $santriQuery->where('nama', 'like', '%' . $search . '%');
        $santris = $santriQuery->get();

        $currentDate = \Carbon\Carbon::parse($tanggal_mulai, 'Asia/Jakarta');
        $endRangeDate = \Carbon\Carbon::parse($tanggal_akhir, 'Asia/Jakarta');

        while ($currentDate->lte($endRangeDate)) {
            $dateStr = $currentDate->format('Y-m-d');
            $jadwal = $this->getJadwalSholat($currentDate);
            $times = $jadwal ? $this->getPrayerEndTimes($dateStr, $jadwal) : [];

            foreach ($santris as $santri) {
                foreach ($sholatList as $s) {
                    if (!isset($realRecordsGrouped[$dateStr][$santri->id][$s])) {
                        $hasIzin = Izin::where('user_id', $santri->user_id)
                                                ->where('status', 'Disetujui')
                                                ->whereDate('tanggal_mulai', '<=', $dateStr)
                                                ->whereDate('tanggal_selesai', '>=', $dateStr)
                                                ->exists();

                        $virtualStatus = $hasIzin ? 'Izin' : 'Alfa';
                        
                        if (!$status || $status === $virtualStatus) {
                            if ($dateStr === $now->format('Y-m-d') && $jadwal && isset($times[$s])) {
                                if ($now->lessThan($times[$s])) {
                                    continue;
                                }
                            } elseif ($currentDate->greaterThan($now)) {
                                continue;
                            }

                            $synthesized->push((object) [
                                'santri' => $santri,
                                'santri_id' => $santri->id,
                                'waktu_sholat' => $s,
                                'tanggal' => $dateStr,
                                'waktu_hadir' => null,
                                'status' => $virtualStatus
                            ]);
                        }
                    }
                }
            }
            $currentDate->addDay();
        }

        $presensis = $realRecords->concat($synthesized)
                                ->sortByDesc('waktu_hadir')
                                ->sortByDesc('tanggal');
        
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
        $now = \Carbon\Carbon::now('Asia/Jakarta');
        $today = $now->format('Y-m-d');
        
        // Ambil jadwal sholat hari ini
        $jadwal = $this->getJadwalSholat($now);
        if (!$jadwal) return;

        // Mapping nama sholat di API ke nama sholat di sistem
        $mapping = [
            'Fajr' => 'Subuh',
            'Dhuhr' => 'Dzuhur',
            'Asr' => 'Ashar',
            'Maghrib' => 'Maghrib',
            'Isha' => 'Isya'
        ];

        // Tentukan batas waktu sholat (misal: sholat dianggap selesai saat waktu sholat berikutnya tiba)
        // Kecuali Isya yang kita beri batas misal jam 23:59 atau Fajr besok.
        $times = $this->getPrayerEndTimes($today, $jadwal);

        $santris = \App\Models\Santri::all();

        foreach ($times as $sholat => $endTime) {
            if ($now->greaterThan($endTime)) {
                // Cari santri yang TIDAK punya record presensi untuk sholat ini hari ini
                $presentSantriIds = Presensi::where('tanggal', $today)
                                            ->where('waktu_sholat', $sholat)
                                            ->pluck('santri_id')
                                            ->toArray();

                foreach ($santris as $santri) {
                    if (!in_array($santri->id, $presentSantriIds)) {
                        // Cek apakah santri punya izin yang disetujui hari ini
                        $hasIzin = \App\Models\Izin::where('user_id', $santri->user_id)
                                                ->where('status', 'Disetujui')
                                                ->whereDate('tanggal_mulai', '<=', $today)
                                                ->whereDate('tanggal_selesai', '>=', $today)
                                                ->exists();
                        
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
            }
        }
        
        // Opsional: Cek juga hari kemarin jika ada yang tertinggal
        $yesterday = $now->copy()->subDay()->format('Y-m-d');
        $hasYesterdaySync = \Illuminate\Support\Facades\Cache::get('sync_alfa_' . $yesterday);
        if (!$hasYesterdaySync) {
            foreach ($mapping as $apiName => $sysName) {
                $presentSantriIds = Presensi::where('tanggal', $yesterday)
                                            ->where('waktu_sholat', $sysName)
                                            ->pluck('santri_id')
                                            ->toArray();

                foreach ($santris as $santri) {
                    if (!in_array($santri->id, $presentSantriIds)) {
                        $hasIzin = \App\Models\Izin::where('user_id', $santri->user_id)
                                                ->where('status', 'Disetujui')
                                                ->whereDate('tanggal_mulai', '<=', $yesterday)
                                                ->whereDate('tanggal_selesai', '>=', $yesterday)
                                                ->exists();
                        
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
            \Illuminate\Support\Facades\Cache::put('sync_alfa_' . $yesterday, true, 86400);
        }
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

    private function getPrayerEndTimes($date, $jadwal)
    {
        return [
            'Subuh' => \Carbon\Carbon::parse($date . ' ' . $jadwal['Fajr'], 'Asia/Jakarta')->addMinutes(15),
            'Dzuhur' => \Carbon\Carbon::parse($date . ' ' . $jadwal['Dhuhr'], 'Asia/Jakarta')->addMinutes(15),
            'Ashar' => \Carbon\Carbon::parse($date . ' ' . $jadwal['Asr'], 'Asia/Jakarta')->addMinutes(15),
            'Maghrib' => \Carbon\Carbon::parse($date . ' ' . $jadwal['Maghrib'], 'Asia/Jakarta')->addMinutes(10),
            'Isya' => \Carbon\Carbon::parse($date . ' ' . $jadwal['Isha'], 'Asia/Jakarta')->addMinutes(15),
        ];
    }
}
