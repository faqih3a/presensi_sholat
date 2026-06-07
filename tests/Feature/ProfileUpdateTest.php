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

        $santri = Santri::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'kelas' => '10 MA',
            'foto_referensi' => 'old_photo.jpg',
            'face_descriptor' => json_encode(array_fill(0, 128, 0.1)),
        ]);

        // Mock upload file
        $file = UploadedFile::fake()->create('new_avatar.jpg', 100, 'image/jpeg');
        $faceDescriptor = json_encode(array_fill(0, 128, 0.9));

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
            'face_descriptor' => json_encode(array_fill(0, 128, 0.1)),
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

    public function test_duplicate_face_descriptor_is_rejected()
    {
        Storage::fake('public');

        // Create first user (existing)
        $existingUser = User::factory()->create(['role' => 'santri']);
        $existingDescriptor = array_fill(0, 128, 0.1);
        Santri::create([
            'user_id' => $existingUser->id,
            'nama' => 'Existing Santri',
            'kelas' => '10 MA',
            'foto_referensi' => 'existing.jpg',
            'face_descriptor' => json_encode($existingDescriptor),
        ]);

        // Create second user (trying to register/update with same face)
        $newUser = User::factory()->create(['role' => 'santri']);
        $santri = Santri::create([
            'user_id' => $newUser->id,
            'nama' => 'New Santri',
            'kelas' => '10 MA',
            'foto_referensi' => 'new.jpg',
            'face_descriptor' => json_encode(array_fill(0, 128, 0.5)), // different initially
        ]);

        // Try to update new user's profile with a face descriptor close to the existing user's face (duplicate)
        $file = UploadedFile::fake()->create('duplicate_avatar.jpg', 100, 'image/jpeg');
        $duplicateDescriptor = array_fill(0, 128, 0.12); // very close to 0.1, Euclidean distance is ~0.226 (under 0.45 threshold)

        $response = $this->actingAs($newUser)->put(route('profile.update'), [
            'name' => 'New Santri',
            'email' => $newUser->email,
            'kelas' => '10 MA',
            'avatar' => $file,
            'face_descriptor' => json_encode($duplicateDescriptor),
        ]);

        $response->assertSessionHasErrors(['face_descriptor']);
    }
}

