<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Santri;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SantriManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        // Create an admin user to perform management actions
        $this->admin = User::factory()->create([
            'role' => 'super_admin',
        ]);
    }

    public function test_admin_cannot_create_santri_with_duplicate_face()
    {
        Storage::fake('public');

        // Create an existing santri with a specific face descriptor
        $existingUser = User::factory()->create(['role' => 'santri']);
        $existingDescriptor = array_fill(0, 128, 0.2);
        Santri::create([
            'user_id' => $existingUser->id,
            'nama' => 'Existing Santri',
            'kelas' => '10 MA',
            'foto_referensi' => 'existing.jpg',
            'face_descriptor' => json_encode($existingDescriptor),
        ]);

        // Try to create a new santri with a very similar/same face descriptor
        $file = UploadedFile::fake()->create('new_santri.jpg', 100, 'image/jpeg');
        $duplicateDescriptor = array_fill(0, 128, 0.21); // distance is ~0.11 (under 0.45 threshold)

        $response = $this->actingAs($this->admin)->post(route('santri.store'), [
            'nama' => 'Duplicate Santri',
            'kelas' => '10 MA',
            'email' => 'duplicate@thursina.id',
            'password' => 'password123',
            'foto_referensi' => $file,
            'face_descriptor' => json_encode($duplicateDescriptor),
        ]);

        $response->assertSessionHasErrors(['face_descriptor']);
        $this->assertDatabaseMissing('users', ['email' => 'duplicate@thursina.id']);
    }

    public function test_admin_cannot_update_santri_with_duplicate_face()
    {
        Storage::fake('public');

        // Create an existing santri with a specific face descriptor
        $existingUser = User::factory()->create(['role' => 'santri']);
        $existingDescriptor = array_fill(0, 128, 0.2);
        Santri::create([
            'user_id' => $existingUser->id,
            'nama' => 'Existing Santri',
            'kelas' => '10 MA',
            'foto_referensi' => 'existing.jpg',
            'face_descriptor' => json_encode($existingDescriptor),
        ]);

        // Create a target santri to update
        $targetUser = User::factory()->create(['role' => 'santri']);
        $targetSantri = Santri::create([
            'user_id' => $targetUser->id,
            'nama' => 'Target Santri',
            'kelas' => '10 MA',
            'foto_referensi' => 'target.jpg',
            'face_descriptor' => json_encode(array_fill(0, 128, 0.9)), // different inicialmente
        ]);

        // Try to update target santri with a face descriptor close to the existing santri
        $file = UploadedFile::fake()->create('updated_santri.jpg', 100, 'image/jpeg');
        $duplicateDescriptor = array_fill(0, 128, 0.215); // distance is ~0.17 (under 0.45 threshold)

        $response = $this->actingAs($this->admin)->put(route('santri.update', $targetSantri), [
            'nama' => 'Target Santri Updated',
            'kelas' => '10 MA',
            'foto_referensi' => $file,
            'face_descriptor' => json_encode($duplicateDescriptor),
        ]);

        $response->assertSessionHasErrors(['face_descriptor']);
        $targetSantri->refresh();
        $this->assertNotEquals('Target Santri Updated', $targetSantri->nama);
    }
}
