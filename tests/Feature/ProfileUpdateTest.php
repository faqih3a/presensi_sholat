<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Santri;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_santri_can_update_profile_avatar_and_face_descriptor()
    {
        Storage::fake('public');

        // Create a user with role santri
        $user = User::factory()->create([
            'role' => 'santri',
            'email' => 'santri_test@thursina.id',
        ]);

        // Create the associated santri
        $santri = Santri::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'kelas' => '10 MA',
            'foto_referensi' => 'old_photo.jpg',
            'face_descriptor' => '[0.1, 0.2, 0.3]',
        ]);

        // Mock upload file
        $file = UploadedFile::fake()->create('new_avatar.jpg', 100, 'image/jpeg');
        $faceDescriptor = '[0.9, 0.8, 0.7]';

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Updated Santri Name',
            'email' => 'santri_test@thursina.id',
            'kelas' => '11 MA',
            'avatar' => $file,
            'face_descriptor' => $faceDescriptor,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Profil berhasil diperbarui.');

        // Refresh database state
        $santri->refresh();

        // Check if file is stored on fake storage disk 'public'
        Storage::disk('public')->assertExists('santri_fotos/' . $santri->foto_referensi);
        $this->assertNotEquals('old_photo.jpg', $santri->foto_referensi);
        $this->assertEquals($faceDescriptor, $santri->face_descriptor);
        $this->assertEquals('Updated Santri Name', $santri->nama);
        $this->assertEquals('11 MA', $santri->kelas);
    }

    public function test_santri_upload_avatar_without_face_descriptor_fails()
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'role' => 'santri',
            'email' => 'santri_test@thursina.id',
        ]);

        $santri = Santri::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'kelas' => '10 MA',
            'foto_referensi' => 'old_photo.jpg',
            'face_descriptor' => '[0.1, 0.2, 0.3]',
        ]);

        $file = UploadedFile::fake()->create('new_avatar.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Updated Santri Name',
            'email' => 'santri_test@thursina.id',
            'kelas' => '11 MA',
            'avatar' => $file,
        ]);

        $response->assertSessionHasErrors(['face_descriptor']);
    }

    public function test_non_santri_can_update_avatar_without_face_descriptor()
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'role' => 'asatidz',
            'email' => 'asatidz_test@thursina.id',
        ]);

        $file = UploadedFile::fake()->create('new_avatar.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Updated Asatidz Name',
            'email' => 'asatidz_test@thursina.id',
            'avatar' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Profil berhasil diperbarui.');

        $user->refresh();
        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists('avatars/' . $user->avatar);
    }
}
