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
        $period = $request->get('period', 'today'); // default: today
        
        // Hitung total santri
        $totalSantri = \App\Models\Santri::count();
        
        $today = \Carbon\Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $startDate = $today;
        $endDate = $today;

        if ($period === 'week') {
            $startDate = \Carbon\Carbon::now('Asia/Jakarta')->subDays(6)->format('Y-m-d');
        } elseif ($period === 'month') {
            $startDate = \Carbon\Carbon::now('Asia/Jakarta')->subDays(29)->format('Y-m-d');
        }

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
        $daysCount = ($period === 'month') ? 30 : 7;
        
        for ($i = $daysCount - 1; $i >= 0; $i--) {
            $date = \Carbon\Carbon::now('Asia/Jakarta')->subDays($i);
            $chartLabels[] = $date->format('d M');
            
            $count = \App\Models\Presensi::where('tanggal', $date->format('Y-m-d'))
                                         ->distinct('santri_id')
                                         ->count('santri_id');
            $chartData[] = $count;
        }

        // Ambil jadwal sholat hari ini
        $jadwal = $this->getJadwalSholat(\Carbon\Carbon::now('Asia/Jakarta'));

        // Fetch specifically for Today's Izin and Alfa cards
        $todayStr = \Carbon\Carbon::now('Asia/Jakarta')->format('Y-m-d');
        
        $izinTodayRecords = \App\Models\Presensi::where('tanggal', $todayStr)
                                            ->where('status', 'Izin')
                                            ->with('santri')
                                            ->get()
                                            ->groupBy('santri_id');

        $alfaTodayRecords = \App\Models\Presensi::where('tanggal', $todayStr)
                                            ->where('status', 'Alfa')
                                            ->with('santri')
                                            ->get()
                                            ->groupBy('santri_id');

        // Identify santris with approved permits covering today (Full Day)
        $fullDayIzinSantriIds = \App\Models\Santri::whereIn('user_id', function($query) use ($todayStr) {
            $query->select('user_id')
                  ->from('izins')
                  ->where('status', 'Disetujui')
                  ->whereDate('tanggal_mulai', '<=', $todayStr)
                  ->whereDate('tanggal_selesai', '>=', $todayStr);
        })->pluck('id')->toArray();

        return view('dashboard.index', compact(
            'totalSantri', 'hadirHariIni', 'tidakHadir', 'persentase', 
            'jadwal', 'chartLabels', 'chartData', 'waktuSholat', 
            'absentSantris', 'period', 'izinTodayRecords', 'alfaTodayRecords', 'fullDayIzinSantriIds'
        ));
    }

    public function kehadiran(Request $request)
    {
        $this->syncAlfas();
        $tanggal = $request->get('tanggal', date('Y-m-d'));
        $waktuSholat = $request->get('waktu_sholat');
        $period = $request->get('period', 'today');
        $status = $request->get('status');
        $search = $request->get('search');

        if ($period === 'today') {
            $santriQuery = Santri::orderBy('nama', 'asc');
            if ($search) {
                $santriQuery->where('nama', 'like', '%' . $search . '%');
            }
            $santris = $santriQuery->get();
            
            $query = Presensi::where('tanggal', $tanggal);
            if ($waktuSholat) {
                $query->where('waktu_sholat', $waktuSholat);
            }
            $presensiHariIni = $query->get()->groupBy(['santri_id', 'waktu_sholat']);

            $sholats = ['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'];
            $sholatList = $waktuSholat ? [$waktuSholat] : $sholats;
            
            $allPresensis = collect();
            
            foreach ($santris as $santri) {
                foreach ($sholatList as $s) {
                    if (isset($presensiHariIni[$santri->id][$s])) {
                        $p = $presensiHariIni[$santri->id][$s][0];
                        $p->setRelation('santri', $santri);
                        $allPresensis->push($p);
                    } else {
                        $hasIzin = Izin::where('user_id', $santri->user_id)
                                                ->where('status', 'Disetujui')
                                                ->whereDate('tanggal_mulai', '<=', $tanggal)
                                                ->whereDate('tanggal_selesai', '>=', $tanggal)
                                                ->exists();

                        $virtualStatus = $hasIzin ? 'Izin' : 'Alfa';
                        
                        if (!$status || $status === $virtualStatus) {
                            $allPresensis->push((object) [
                                'santri' => $santri,
                                'santri_id' => $santri->id,
                                'waktu_sholat' => $s,
                                'tanggal' => $tanggal,
                                'waktu_hadir' => null,
                                'status' => $virtualStatus
                            ]);
                        }
                    }
                }
            }
            $presensis = $status ? $allPresensis->filter(fn($p) => $p->status === $status) : $allPresensis;

        } else {
            // week/month logic
            $query = Presensi::with('santri');
            if ($search) {
                $query->whereHas('santri', function($q) use ($search) {
                    $q->where('nama', 'like', '%' . $search . '%');
                });
            }

            $today = date('Y-m-d');
            if ($period === 'week') {
                $startDate = \Carbon\Carbon::now('Asia/Jakarta')->subDays(6)->format('Y-m-d');
                $endDate = \Carbon\Carbon::now('Asia/Jakarta')->format('Y-m-d');
                $query->whereBetween('tanggal', [$startDate, $endDate]);
            } else { // month
                $startDate = \Carbon\Carbon::now('Asia/Jakarta')->subDays(29)->format('Y-m-d');
                $endDate = \Carbon\Carbon::now('Asia/Jakarta')->format('Y-m-d');
                $query->whereBetween('tanggal', [$startDate, $endDate]);
            }

            if ($waktuSholat) $query->where('waktu_sholat', $waktuSholat);
            if ($status) $query->where('status', $status);

            $realRecords = $query->get();
            $realRecordsGrouped = $realRecords->where('tanggal', $today)->groupBy(['santri_id', 'waktu_sholat']);

            // Synthesize missing records for TODAY only within the week/month view
            $synthesizedToday = collect();
            $sholats = ['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'];
            $sholatList = $waktuSholat ? [$waktuSholat] : $sholats;

            $santriQuery = Santri::query();
            if ($search) $santriQuery->where('nama', 'like', '%' . $search . '%');
            $santris = $santriQuery->get();

            foreach ($santris as $santri) {
                foreach ($sholatList as $s) {
                    if (!isset($realRecordsGrouped[$santri->id][$s])) {
                        $hasIzin = Izin::where('user_id', $santri->user_id)
                                                ->where('status', 'Disetujui')
                                                ->whereDate('tanggal_mulai', '<=', $today)
                                                ->whereDate('tanggal_selesai', '>=', $today)
                                                ->exists();

                        $virtualStatus = $hasIzin ? 'Izin' : 'Alfa';
                        if (!$status || $status === $virtualStatus) {
                            $synthesizedToday->push((object) [
                                'santri' => $santri,
                                'santri_id' => $santri->id,
                                'waktu_sholat' => $s,
                                'tanggal' => $today,
                                'waktu_hadir' => null,
                                'status' => $virtualStatus
                            ]);
                        }
                    }
                }
            }

            $presensis = $realRecords->concat($synthesizedToday)
                                    ->sortByDesc('waktu_hadir')
                                    ->sortByDesc('tanggal');
        }

        return view('dashboard.kehadiran', compact('presensis', 'tanggal', 'waktuSholat', 'period', 'status'));
    }

    public function exportKehadiran(Request $request)
    {
        $tanggal = $request->get('tanggal', date('Y-m-d'));
        $waktuSholat = $request->get('waktu_sholat');
        $period = $request->get('period', 'today');
        $status = $request->get('status');
        $search = $request->get('search');

        if ($period === 'today') {
            $santriQuery = \App\Models\Santri::orderBy('nama', 'asc');
            if ($search) {
                $santriQuery->where('nama', 'like', '%' . $search . '%');
            }
            $santris = $santriQuery->get();
            
            $query = Presensi::where('tanggal', $tanggal);
            if ($waktuSholat) {
                $query->where('waktu_sholat', $waktuSholat);
            }
            $presensiHariIni = $query->get()->groupBy(['santri_id', 'waktu_sholat']);

            $sholats = ['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'];
            $sholatList = $waktuSholat ? [$waktuSholat] : $sholats;
            
            $allPresensis = collect();
            
            foreach ($santris as $santri) {
                foreach ($sholatList as $s) {
                    if (isset($presensiHariIni[$santri->id][$s])) {
                        $p = $presensiHariIni[$santri->id][$s][0];
                        $p->setRelation('santri', $santri);
                        $allPresensis->push($p);
                    } else {
                        $hasIzin = \App\Models\Izin::where('user_id', $santri->user_id)
                                                ->where('status', 'Disetujui')
                                                ->whereDate('tanggal_mulai', '<=', $tanggal)
                                                ->whereDate('tanggal_selesai', '>=', $tanggal)
                                                ->exists();

                        $virtualStatus = $hasIzin ? 'Izin' : 'Alfa';
                        
                        if (!$status || $status === $virtualStatus) {
                            $allPresensis->push((object) [
                                'santri' => $santri,
                                'santri_id' => $santri->id,
                                'waktu_sholat' => $s,
                                'tanggal' => $tanggal,
                                'waktu_hadir' => null,
                                'status' => $virtualStatus
                            ]);
                        }
                    }
                }
            }
            
            $presensis = $status ? $allPresensis->filter(fn($p) => $p->status === $status) : $allPresensis;

        } else {
            $query = Presensi::with('santri');
            if ($search) {
                $query->whereHas('santri', function($q) use ($search) {
                    $q->where('nama', 'like', '%' . $search . '%');
                });
            }

            $today = date('Y-m-d');
            if ($period === 'week') {
                $startDate = \Carbon\Carbon::now('Asia/Jakarta')->subDays(6)->format('Y-m-d');
                $endDate = \Carbon\Carbon::now('Asia/Jakarta')->format('Y-m-d');
                $query->whereBetween('tanggal', [$startDate, $endDate]);
            } else { // month
                $startDate = \Carbon\Carbon::now('Asia/Jakarta')->subDays(29)->format('Y-m-d');
                $endDate = \Carbon\Carbon::now('Asia/Jakarta')->format('Y-m-d');
                $query->whereBetween('tanggal', [$startDate, $endDate]);
            }

            if ($waktuSholat) $query->where('waktu_sholat', $waktuSholat);
            if ($status) $query->where('status', $status);

            $realRecords = $query->get();
            $realRecordsGrouped = $realRecords->where('tanggal', $today)->groupBy(['santri_id', 'waktu_sholat']);

            $synthesizedToday = collect();
            $sholats = ['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'];
            $sholatList = $waktuSholat ? [$waktuSholat] : $sholats;

            $santriQuery = \App\Models\Santri::query();
            if ($search) $santriQuery->where('nama', 'like', '%' . $search . '%');
            $santris = $santriQuery->get();

            foreach ($santris as $santri) {
                foreach ($sholatList as $s) {
                    if (!isset($realRecordsGrouped[$santri->id][$s])) {
                        $hasIzin = \App\Models\Izin::where('user_id', $santri->user_id)
                                                ->where('status', 'Disetujui')
                                                ->whereDate('tanggal_mulai', '<=', $today)
                                                ->whereDate('tanggal_selesai', '>=', $today)
                                                ->exists();

                        $virtualStatus = $hasIzin ? 'Izin' : 'Alfa';
                        if (!$status || $status === $virtualStatus) {
                            $synthesizedToday->push((object) [
                                'santri' => $santri,
                                'santri_id' => $santri->id,
                                'waktu_sholat' => $s,
                                'tanggal' => $today,
                                'waktu_hadir' => null,
                                'status' => $virtualStatus
                            ]);
                        }
                    }
                }
            }

            $presensis = $realRecords->concat($synthesizedToday)
                                    ->sortByDesc('waktu_hadir')
                                    ->sortByDesc('tanggal');
        }
        
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
            
            // Tambahkan BOM untuk UTF-8 agar excel mengenali karakter dengan baik
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
        $sholats = [
            'Subuh' => $jadwal['Fajr'],
            'Dzuhur' => $jadwal['Dhuhr'],
            'Ashar' => $jadwal['Asr'],
            'Maghrib' => $jadwal['Maghrib'],
            'Isya' => $jadwal['Isha']
        ];

        // Tambahkan buffer waktu (misal 30 menit setelah waktu sholat masuk baru dianggap Alfa jika tidak hadir)
        // Atau lebih tepatnya, Alfa dicatat jika sudah masuk waktu sholat berikutnya.
        $times = [
            'Subuh' => \Carbon\Carbon::parse($today . ' ' . $jadwal['Dhuhr'], 'Asia/Jakarta'),
            'Dzuhur' => \Carbon\Carbon::parse($today . ' ' . $jadwal['Asr'], 'Asia/Jakarta'),
            'Ashar' => \Carbon\Carbon::parse($today . ' ' . $jadwal['Maghrib'], 'Asia/Jakarta'),
            'Maghrib' => \Carbon\Carbon::parse($today . ' ' . $jadwal['Isha'], 'Asia/Jakarta'),
            'Isya' => \Carbon\Carbon::parse($today . ' 23:59:59', 'Asia/Jakarta'), // Batas akhir hari
        ];

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
                // Log error if needed
            }
            
            return null;
        });
    }
}
