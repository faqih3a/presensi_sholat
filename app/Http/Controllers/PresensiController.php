<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presensi;
use Carbon\Carbon;

class PresensiController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $jadwal = $this->getJadwalSholat($now);
        $currentTime = $now->format('H:i');
        
        $suggestedSholat = null;
        if ($jadwal) {
            foreach (['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'] as $sholat) {
                if ($this->isTimeInPrayerWindow($currentTime, $sholat, $jadwal)) {
                    $suggestedSholat = $sholat;
                    // Note: In case of overlaps (e.g. 30 mins before next prayer), 
                    // we can decide which one to prioritize. 
                    // Here we take the first match.
                    break;
                }
            }
        }

        return view('presensi.scan', compact('suggestedSholat', 'jadwal'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'santri_id' => 'required|exists:santris,id',
            'waktu_sholat' => 'required|string|in:Subuh,Dzuhur,Ashar,Maghrib,Isya',
        ]);

        $now = Carbon::now();
        $currentTime = $now->format('H:i');
        $selectedWaktu = $request->waktu_sholat;

        // Ambil jadwal sholat hari ini
        $jadwal = $this->getJadwalSholat($now);

        if (!$jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data jadwal sholat. Silakan coba lagi.',
            ], 500);
        }

        // Validasi apakah waktu saat ini masuk dalam rentang waktu sholat yang dipilih
        $isValidTime = $this->isTimeInPrayerWindow($currentTime, $selectedWaktu, $jadwal);

        if (!$isValidTime) {
            $currentActiveSholat = "Tidak Ada";
            foreach (['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'] as $sholatName) {
                if ($this->isTimeInPrayerWindow($currentTime, $sholatName, $jadwal)) {
                    $currentActiveSholat = $sholatName;
                    break;
                }
            }

            return response()->json([
                'success' => false,
                'message' => "Sekarang bukan waktu sholat $selectedWaktu (Saat ini: $currentActiveSholat), Tidak dapat melakukan presensi.",
            ], 400);
        }

        $today = $now->format('Y-m-d');

        // Check if santri has an approved permit (Izin) for today
        $santri = \App\Models\Santri::find($request->santri_id);
        $hasIzin = \App\Models\Izin::where('user_id', $santri->user_id)
                                ->where('status', 'Disetujui')
                                ->whereDate('tanggal_mulai', '<=', $today)
                                ->whereDate('tanggal_selesai', '>=', $today)
                                ->exists();

        $status = $hasIzin ? 'Izin' : 'Hadir';

        $existingRecord = Presensi::where('santri_id', $request->santri_id)
            ->where('waktu_sholat', $selectedWaktu)
            ->where('tanggal', $today)
            ->first();

        if ($existingRecord) {
            // If it's already "Hadir", block duplicate
            if ($existingRecord->status === 'Hadir') {
                return response()->json([
                    'success' => false,
                    'message' => "Anda sudah melakukan presensi untuk sholat $selectedWaktu hari ini.",
                ], 400);
            }
            
            // If it was "Alfa" or "Izin" (from sync), and now they scan, we update it
            // but if they have an Izin, it STAYS "Izin" as per user request.
            $existingRecord->update([
                'waktu_hadir' => $currentTime,
                'status' => $status
            ]);
            $presensi = $existingRecord;
        } else {
            $presensi = Presensi::create([
                'santri_id' => $request->santri_id,
                'waktu_sholat' => $selectedWaktu,
                'tanggal' => $today,
                'waktu_hadir' => $currentTime,
                'status' => $status,
            ]);
        }

        $message = $hasIzin 
            ? "Presensi dicatat sebagai IZIN (Anda memiliki izin yang disetujui)." 
            : "Presensi sholat $selectedWaktu berhasil dicatat.";

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $presensi->load('santri')
        ]);
    }

    private function getJadwalSholat(Carbon $date)
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

    private function isTimeInPrayerWindow($currentTime, $sholat, $jadwal)
    {
        $fajr = $jadwal['Fajr'] ?? '04:00';
        $dhuhr = $jadwal['Dhuhr'] ?? '11:30';
        $asr = $jadwal['Asr'] ?? '14:30';
        $maghrib = $jadwal['Maghrib'] ?? '17:30';
        $isha = $jadwal['Isha'] ?? '18:45';

        // Helper untuk mengurangi 30 menit dari string HH:mm
        $getStart = function($timeStr) {
            try {
                return Carbon::createFromFormat('H:i', $timeStr)->subMinutes(30)->format('H:i');
            } catch (\Exception $e) {
                return $timeStr;
            }
        };

        $fajrStart = $getStart($fajr);
        $dhuhrStart = $getStart($dhuhr);
        $asrStart = $getStart($asr);
        $maghribStart = $getStart($maghrib);
        $ishaStart = $getStart($isha);

        // Logika rentang waktu: Mulai 30 menit sebelum waktu sholat masuk
        // hingga waktu sholat berikutnya tiba.
        switch ($sholat) {
            case 'Subuh':
                return $currentTime >= $fajrStart && $currentTime < $dhuhr;
            case 'Dzuhur':
                return $currentTime >= $dhuhrStart && $currentTime < $asr;
            case 'Ashar':
                return $currentTime >= $asrStart && $currentTime < $maghrib;
            case 'Maghrib':
                return $currentTime >= $maghribStart && $currentTime < $isha;
            case 'Isya':
                // Isya berlaku mulai 30 menit sebelum Isya hingga 30 menit sebelum Subuh hari berikutnya
                return $currentTime >= $ishaStart || $currentTime < $fajrStart;
        }

        return false;
    }
}
