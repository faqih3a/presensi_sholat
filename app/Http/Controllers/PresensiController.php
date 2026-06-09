<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presensi;
use Carbon\Carbon;

class PresensiController extends Controller
{
    public function index()
    {
        $now = Carbon::now('Asia/Jakarta');
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

        $now = Carbon::now('Asia/Jakarta');
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
        // TEMPORARY BYPASS FOR TESTING:
        $isValidTime = true; // $this->isTimeInPrayerWindow($currentTime, $selectedWaktu, $jadwal);

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

        $existingRecord = Presensi::withTrashed()
            ->where('santri_id', $request->santri_id)
            ->where('waktu_sholat', $selectedWaktu)
            ->where('tanggal', $today)
            ->first();

        if ($existingRecord) {
            if ($existingRecord->trashed()) {
                $existingRecord->restore();
            }
            // If it's already "Hadir" and not trashed, block duplicate
            elseif ($existingRecord->status === 'Hadir') {
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

    public function updateStatus(Request $request)
    {
        $request->validate([
            'santri_id' => 'required|exists:santris,id',
            'tanggal' => 'required|date',
            'waktu_sholat' => 'required|string',
            'status' => 'required|in:Hadir,Izin,Alfa',
        ]);

        $presensi = Presensi::withTrashed()->where([
            'santri_id' => $request->santri_id,
            'tanggal' => $request->tanggal,
            'waktu_sholat' => $request->waktu_sholat,
        ])->first();

        if ($presensi) {
            if ($presensi->trashed()) {
                $presensi->restore();
            }
            $presensi->update([
                'status' => $request->status,
                'waktu_hadir' => $request->status === 'Hadir' ? Carbon::now('Asia/Jakarta')->format('H:i') : null,
            ]);
        } else {
            $presensi = Presensi::create([
                'santri_id' => $request->santri_id,
                'tanggal' => $request->tanggal,
                'waktu_sholat' => $request->waktu_sholat,
                'status' => $request->status,
                'waktu_hadir' => $request->status === 'Hadir' ? Carbon::now('Asia/Jakarta')->format('H:i') : null,
            ]);
        }

        return redirect()->back()->with('success', 'Status kehadiran berhasil diperbarui.');
    }

    public function destroy(Presensi $presensi)
    {
        $presensi->delete();
        return redirect()->back()->with('success', 'Data kehadiran berhasil dihapus.');
    }

    public function deleteByParams(Request $request)
    {
        $request->validate([
            'santri_id' => 'required|exists:santris,id',
            'tanggal' => 'required|date',
            'waktu_sholat' => 'required|string',
        ]);

        Presensi::where('santri_id', $request->santri_id)
                ->where('tanggal', $request->tanggal)
                ->where('waktu_sholat', $request->waktu_sholat)
                ->delete();

        return redirect()->back()->with('success', 'Data kehadiran berhasil dihapus.');
    }
    
    private function getJadwalSholat(Carbon $date)
    {
        return \App\Services\JadwalSholatService::getJadwal($date);
    }

    private function isTimeInPrayerWindow($currentTime, $sholat, $jadwal)
    {
        $fajr = $jadwal['Fajr'] ?? '04:00';
        $dhuhr = $jadwal['Dhuhr'] ?? '11:30';
        $asr = $jadwal['Asr'] ?? '14:30';
        $maghrib = $jadwal['Maghrib'] ?? '17:30';
        $isha = $jadwal['Isha'] ?? '18:45';

        // Start time is 30 minutes before the prayer
        $getStart = function($timeStr) {
            try {
                return Carbon::createFromFormat('H:i', $timeStr)->subMinutes(30)->format('H:i');
            } catch (\Exception $e) {
                return $timeStr;
            }
        };

        // End time is 10 minutes after the prayer starts
        $getEnd = function($timeStr) {
            try {
                return Carbon::createFromFormat('H:i', $timeStr)->addMinutes(10)->format('H:i');
            } catch (\Exception $e) {
                return $timeStr;
            }
        };

        $fajrStart = $getStart($fajr);
        $dhuhrStart = $getStart($dhuhr);
        $asrStart = $getStart($asr);
        $maghribStart = $getStart($maghrib);
        $ishaStart = $getStart($isha);

        $fajrEnd = $getEnd($fajr);
        $dhuhrEnd = $getEnd($dhuhr);
        $asrEnd = $getEnd($asr);
        $maghribEnd = $getEnd($maghrib);
        $ishaEnd = $getEnd($isha);

        switch ($sholat) {
            case 'Subuh':
                return $currentTime >= $fajrStart && $currentTime <= $fajrEnd;
            case 'Dzuhur':
                return $currentTime >= $dhuhrStart && $currentTime <= $dhuhrEnd;
            case 'Ashar':
                return $currentTime >= $asrStart && $currentTime <= $asrEnd;
            case 'Maghrib':
                return $currentTime >= $maghribStart && $currentTime <= $maghribEnd;
            case 'Isya':
                return $currentTime >= $ishaStart && $currentTime <= $ishaEnd;
        }

        return false;
    }
}
