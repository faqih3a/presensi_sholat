<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Santri;
use App\Models\Izin;
use App\Models\Presensi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IzinPresensiSyncTest extends TestCase
{
    use RefreshDatabase;

    protected $asatidz;
    protected $santriUser;
    protected $santri;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->asatidz = User::factory()->create([
            'role' => 'asatidz',
        ]);

        $this->santriUser = User::factory()->create([
            'role' => 'santri',
        ]);

        $this->santri = Santri::create([
            'user_id' => $this->santriUser->id,
            'nama' => 'Santri Testing',
            'kelas' => '7 MTs',
            'foto_referensi' => 'photo.jpg',
            'face_descriptor' => json_encode(array_fill(0, 128, 0.5)),
        ]);
    }

    public function test_approving_izin_creates_izin_presensi_records()
    {
        // Set test date to today = 2020-01-03 so 2020-01-01 & 02 are completely in the past
        \Carbon\Carbon::setTestNow(\Carbon\Carbon::parse('2020-01-03 12:00:00', 'Asia/Jakarta'));

        $izin = Izin::create([
            'user_id' => $this->santriUser->id,
            'jenis_izin' => 'Sakit',
            'waktu_sholat' => 'Full Day',
            'tanggal_mulai' => '2020-01-01',
            'tanggal_selesai' => '2020-01-02',
            'keterangan' => 'Sakit demam',
            'status' => 'Pending',
        ]);

        // Verify no presensi records exist yet
        $this->assertDatabaseMissing('presensis', [
            'santri_id' => $this->santri->id,
        ]);

        // Admin/Asatidz approves the izin
        $response = $this->actingAs($this->asatidz)->post(route('izin.update-status', $izin), [
            'status' => 'Disetujui',
            'keterangan_admin' => 'Approved',
        ]);

        $response->assertRedirect();
        
        // Should create Izin presensi records for all 5 prayers on both days (10 records in total)
        $this->assertDatabaseHas('presensis', [
            'santri_id' => $this->santri->id,
            'tanggal' => '2020-01-01',
            'waktu_sholat' => 'Subuh',
            'status' => 'Izin',
        ]);

        $this->assertDatabaseHas('presensis', [
            'santri_id' => $this->santri->id,
            'tanggal' => '2020-01-02',
            'waktu_sholat' => 'Isya',
            'status' => 'Izin',
        ]);

        $this->assertEquals(10, Presensi::where('santri_id', $this->santri->id)->where('status', 'Izin')->count());

        \Carbon\Carbon::setTestNow();
    }

    public function test_rejecting_previously_approved_izin_reverts_to_alfa()
    {
        // Set test date to 2026-06-08 12:00:00 (past relative to Subuh)
        \Carbon\Carbon::setTestNow(\Carbon\Carbon::parse('2026-06-08 12:00:00', 'Asia/Jakarta'));

        // Seed cache for 2026-06-08
        $address = 'Bogor, Kecamatan Cibeureum, Kp Joglo, Indonesia';
        $cacheKey = 'jadwal_sholat_' . md5($address) . '_2026-06-08';
        \Illuminate\Support\Facades\Cache::put($cacheKey, [
            'Fajr' => '04:30',
        ], 86400);

        $izin = Izin::create([
            'user_id' => $this->santriUser->id,
            'jenis_izin' => 'Sakit',
            'waktu_sholat' => 'Subuh',
            'tanggal_mulai' => '2026-06-08',
            'tanggal_selesai' => '2026-06-08',
            'keterangan' => 'Sakit gigi',
            'status' => 'Pending',
        ]);

        // Approve it first
        $this->actingAs($this->asatidz)->post(route('izin.update-status', $izin), [
            'status' => 'Disetujui',
            'keterangan_admin' => 'Approved',
        ]);

        $this->assertDatabaseHas('presensis', [
            'santri_id' => $this->santri->id,
            'tanggal' => '2026-06-08',
            'waktu_sholat' => 'Subuh',
            'status' => 'Izin',
        ]);

        // Re-call with rejected status
        $this->actingAs($this->asatidz)->post(route('izin.update-status', $izin), [
            'status' => 'Ditolak',
            'keterangan_admin' => 'Rejected after review',
        ]);

        // Should change to Alfa
        $this->assertDatabaseHas('presensis', [
            'santri_id' => $this->santri->id,
            'tanggal' => '2026-06-08',
            'waktu_sholat' => 'Subuh',
            'status' => 'Alfa',
        ]);

        \Carbon\Carbon::setTestNow();
    }

    public function test_future_izin_is_only_recorded_after_sync_runs()
    {
        // Set date to today = 2030-01-01
        $todayStr = '2030-01-01';
        $tomorrowStr = '2030-01-02';
        \Carbon\Carbon::setTestNow(\Carbon\Carbon::parse($todayStr . ' 08:00:00', 'Asia/Jakarta'));

        // Seed the cache with prayer times for today and tomorrow to avoid API calls
        $address = 'Bogor, Kecamatan Cibeureum, Kp Joglo, Indonesia';
        $cacheKeyToday = 'jadwal_sholat_' . md5($address) . '_' . $todayStr;
        \Illuminate\Support\Facades\Cache::put($cacheKeyToday, [
            'Fajr' => '04:30',
            'Dhuhr' => '12:00',
            'Asr' => '15:30',
            'Maghrib' => '18:00',
            'Isha' => '19:15'
        ], 86400);

        $cacheKeyTomorrow = 'jadwal_sholat_' . md5($address) . '_' . $tomorrowStr;
        \Illuminate\Support\Facades\Cache::put($cacheKeyTomorrow, [
            'Fajr' => '04:30',
            'Dhuhr' => '12:00',
            'Asr' => '15:30',
            'Maghrib' => '18:00',
            'Isha' => '19:15'
        ], 86400);

        // Create a permit for tomorrow
        $izin = Izin::create([
            'user_id' => $this->santriUser->id,
            'jenis_izin' => 'Sakit',
            'waktu_sholat' => 'Subuh',
            'tanggal_mulai' => $tomorrowStr,
            'tanggal_selesai' => $tomorrowStr,
            'keterangan' => 'Sakit gigi',
            'status' => 'Pending',
        ]);

        // Approve it today
        $response = $this->actingAs($this->asatidz)->post(route('izin.update-status', $izin), [
            'status' => 'Disetujui',
            'keterangan_admin' => 'Approved',
        ]);

        $response->assertRedirect();

        // Verify NO presence record is created yet for tomorrow
        $this->assertDatabaseMissing('presensis', [
            'santri_id' => $this->santri->id,
            'tanggal' => $tomorrowStr,
        ]);

        // Now travel to tomorrow 06:00:00 (which is after Subuh ending time: 04:30 + 10 mins = 04:40)
        \Carbon\Carbon::setTestNow(\Carbon\Carbon::parse($tomorrowStr . ' 06:00:00', 'Asia/Jakarta'));

        // Run the sync
        \Illuminate\Support\Facades\Artisan::call('app:sync-alfas');

        // Verify that Subuh is now marked as 'Izin'
        $this->assertDatabaseHas('presensis', [
            'santri_id' => $this->santri->id,
            'tanggal' => $tomorrowStr,
            'waktu_sholat' => 'Subuh',
            'status' => 'Izin',
        ]);

        // Verify that other prayers (like Dzuhur) are NOT marked yet since they haven't passed
        $this->assertDatabaseMissing('presensis', [
            'santri_id' => $this->santri->id,
            'tanggal' => $tomorrowStr,
            'waktu_sholat' => 'Dzuhur',
        ]);

        // Clean up Carbon testing mock
        \Carbon\Carbon::setTestNow();
    }
}
