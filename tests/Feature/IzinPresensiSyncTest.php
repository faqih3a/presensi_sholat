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
        $izin = Izin::create([
            'user_id' => $this->santriUser->id,
            'jenis_izin' => 'Sakit',
            'waktu_sholat' => 'Full Day',
            'tanggal_mulai' => '2026-06-08',
            'tanggal_selesai' => '2026-06-09',
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
            'tanggal' => '2026-06-08',
            'waktu_sholat' => 'Subuh',
            'status' => 'Izin',
        ]);

        $this->assertDatabaseHas('presensis', [
            'santri_id' => $this->santri->id,
            'tanggal' => '2026-06-09',
            'waktu_sholat' => 'Isya',
            'status' => 'Izin',
        ]);

        $this->assertEquals(10, Presensi::where('santri_id', $this->santri->id)->where('status', 'Izin')->count());
    }

    public function test_rejecting_previously_approved_izin_reverts_to_alfa()
    {
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
    }
}
