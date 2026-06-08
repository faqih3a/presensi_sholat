<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Izin;
use Illuminate\Support\Facades\Storage;

class IzinController extends Controller
{
    public function index()
    {
        $izins = Izin::where('user_id', auth()->id())->latest()->get();
        return view('izin.index', compact('izins'));
    }

    public function create()
    {
        return view('izin.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis_izin' => 'required|in:Sakit,Izin,Kegiatan Luar',
            'waktu_sholat' => 'nullable|string|in:Full Day,Subuh,Dzuhur,Ashar,Maghrib,Isya',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'required|string',
            'lampiran' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only(['jenis_izin', 'waktu_sholat', 'tanggal_mulai', 'tanggal_selesai', 'keterangan']);
        $data['user_id'] = auth()->id();

        if ($request->hasFile('lampiran')) {
            $path = $request->file('lampiran')->store('lampiran_izin', 'public');
            $data['lampiran'] = $path;
        }

        $izin = Izin::create($data);

        // Send WhatsApp Notification to Asatidz
        try {
            $asatidz = \App\Models\User::where('role', 'asatidz')
                                      ->whereNotNull('wa_number')
                                      ->get();
            
            if ($asatidz->count() > 0) {
                $message = \App\Services\WhatsAppService::formatIzinNotification($izin);
                foreach ($asatidz as $ustadz) {
                    \App\Services\WhatsAppService::sendMessage($ustadz->wa_number, $message);
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send WA notification: ' . $e->getMessage());
        }

        return redirect()->route('izin.index')->with('success', 'Permohonan izin berhasil diajukan.');
    }

    public function manage(Request $request)
    {
        // Only for Asatidz
        if (auth()->user()->role !== 'asatidz') {
            abort(403);
        }

        $today = \Carbon\Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $tanggal_mulai = $request->get('tanggal_mulai', $today);
        $tanggal_akhir = $request->get('tanggal_akhir', $today);

        $izins = Izin::with('user.santri')
                    ->where(function($query) use ($tanggal_mulai, $tanggal_akhir) {
                        $query->whereBetween('tanggal_mulai', [$tanggal_mulai, $tanggal_akhir])
                              ->orWhereBetween('tanggal_selesai', [$tanggal_mulai, $tanggal_akhir])
                              ->orWhere(function($q) use ($tanggal_mulai, $tanggal_akhir) {
                                  $q->where('tanggal_mulai', '<=', $tanggal_mulai)
                                    ->where('tanggal_selesai', '>=', $tanggal_akhir);
                              });
                    })
                    ->latest()
                    ->get();

        return view('izin.manage', compact('izins', 'tanggal_mulai', 'tanggal_akhir'));
    }

    public function updateStatus(Request $request, Izin $izin)
    {
        if (auth()->user()->role !== 'asatidz') {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:Disetujui,Ditolak',
            'keterangan_admin' => 'nullable|string',
        ]);

        $izin->update([
            'status' => $request->status,
            'keterangan_admin' => $request->keterangan_admin,
        ]);

        // Sinkronisasi status presensi santri jika izin disetujui atau ditolak
        $santri = \App\Models\Santri::where('user_id', $izin->user_id)->first();
        if ($santri) {
            $start = \Carbon\Carbon::parse($izin->tanggal_mulai);
            $end = \Carbon\Carbon::parse($izin->tanggal_selesai);
            
            $prayers = $izin->waktu_sholat === 'Full Day' 
                ? ['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'] 
                : [$izin->waktu_sholat];

            if ($request->status === 'Disetujui') {
                $address = 'Bogor, Kecamatan Cibeureum, Kp Joglo, Indonesia';
                $now = \Carbon\Carbon::now('Asia/Jakarta');
                $todayStr = $now->format('Y-m-d');
                $cacheKey = 'jadwal_sholat_' . md5($address) . '_' . $todayStr;
                $jadwal = \Illuminate\Support\Facades\Cache::get($cacheKey);

                if (!$jadwal) {
                    try {
                        $response = \Illuminate\Support\Facades\Http::timeout(5)->get('https://api.aladhan.com/v1/timingsByAddress', [
                            'address' => $address,
                            'method' => 20,
                            'date' => $now->format('d-m-Y')
                        ]);
                        if ($response->successful()) {
                            $timings = $response->json('data.timings');
                            foreach ($timings as $key => $time) {
                                $timings[$key] = substr($time, 0, 5);
                            }
                            $jadwal = $timings;
                            \Illuminate\Support\Facades\Cache::put($cacheKey, $jadwal, 86400);
                        }
                    } catch (\Exception $e) {
                        // Ignore
                    }
                }

                $fajr = $jadwal['Fajr'] ?? '04:00';
                $dhuhr = $jadwal['Dhuhr'] ?? '11:30';
                $asr = $jadwal['Asr'] ?? '14:30';
                $maghrib = $jadwal['Maghrib'] ?? '17:30';
                $isha = $jadwal['Isha'] ?? '18:45';

                $prayerEndOffsets = [
                    'Subuh' => $fajr,
                    'Dzuhur' => $dhuhr,
                    'Ashar' => $asr,
                    'Maghrib' => $maghrib,
                    'Isya' => $isha
                ];

                for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                    $dateStr = $date->format('Y-m-d');
                    $isPastDate = $dateStr < $todayStr;
                    $isToday = $dateStr === $todayStr;

                    if (!$isPastDate && !$isToday) {
                        // Future date: skip immediate recording
                        continue;
                    }

                    foreach ($prayers as $prayer) {
                        if ($isToday) {
                            $prayerTimeStr = $prayerEndOffsets[$prayer] ?? '12:00';
                            $endTime = \Carbon\Carbon::parse($todayStr . ' ' . $prayerTimeStr, 'Asia/Jakarta')->addMinutes(10);
                            if ($now->lessThanOrEqualTo($endTime)) {
                                // Has not passed yet: skip immediate recording
                                continue;
                            }
                        }

                        $existing = \App\Models\Presensi::where([
                            'santri_id' => $santri->id,
                            'tanggal' => $dateStr,
                            'waktu_sholat' => $prayer,
                        ])->first();

                        if ($existing) {
                            if ($existing->status !== 'Hadir') {
                                $existing->update([
                                    'status' => 'Izin',
                                    'waktu_hadir' => null
                                ]);
                            }
                        } else {
                            \App\Models\Presensi::create([
                                'santri_id' => $santri->id,
                                'tanggal' => $dateStr,
                                'waktu_sholat' => $prayer,
                                'status' => 'Izin',
                                'waktu_hadir' => null
                            ]);
                        }
                    }
                }
            } elseif ($request->status === 'Ditolak') {
                for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                    $dateStr = $date->format('Y-m-d');
                    foreach ($prayers as $prayer) {
                        $existing = \App\Models\Presensi::where([
                            'santri_id' => $santri->id,
                            'tanggal' => $dateStr,
                            'waktu_sholat' => $prayer,
                        ])->first();

                        if ($existing && $existing->status === 'Izin') {
                            $existing->update([
                                'status' => 'Alfa',
                                'waktu_hadir' => null
                            ]);
                        }
                    }
                }
            }
        }

        return redirect()->back()->with('success', 'Status permohonan izin berhasil diperbarui.');
    }
}
