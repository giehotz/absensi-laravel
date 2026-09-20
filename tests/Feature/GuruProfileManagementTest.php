<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GuruProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guru_can_access_profile_page(): void
    {
        $guruUser = User::where('email', 'guru@sekolah.sch.id')->first();
        $this->actingAs($guruUser);

        $response = $this->get(route('guru.profile.index'));

        $response->assertStatus(200);
        $response->assertSee('PROFIL SAYA');
        $response->assertSee('Pengaturan Biodata Guru');
        $response->assertSee('Keamanan');
        $response->assertSee('Kata Sandi');
        $response->assertSee('Jadwal Mengajar');
        $response->assertSee('DEWAN GURU');
    }

    public function test_guru_can_update_biodata(): void
    {
        $guruUser = User::where('email', 'guru@sekolah.sch.id')->first();
        $this->actingAs($guruUser);

        $response = $this->put(route('guru.profile.update'), [
            'name' => 'Budi Santoso, S.Pd., M.Kom.',
            'email' => 'budi.santoso@sekolah.sch.id',
            'phone' => '081299998888',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $guruUser->id,
            'name' => 'Budi Santoso, S.Pd., M.Kom.',
            'email' => 'budi.santoso@sekolah.sch.id',
        ]);

        $this->assertDatabaseHas('teachers', [
            'user_id' => $guruUser->id,
            'phone' => '081299998888',
        ]);
    }

    public function test_guru_cannot_use_already_taken_email(): void
    {
        $guruUser = User::where('email', 'guru@sekolah.sch.id')->first();
        $adminUser = User::where('email', 'admin@sekolah.sch.id')->first();

        $this->actingAs($guruUser);

        $response = $this->put(route('guru.profile.update'), [
            'name' => 'Guru Name',
            'email' => $adminUser->email,
            'phone' => '081234567890',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_guru_can_change_password_with_correct_current_password(): void
    {
        $guruUser = User::where('email', 'guru@sekolah.sch.id')->first();
        $this->actingAs($guruUser);

        $response = $this->put(route('guru.profile.password'), [
            'current_password' => 'password',
            'password' => 'newSecretPass123',
            'password_confirmation' => 'newSecretPass123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $guruUser->refresh();
        $this->assertTrue(Hash::check('newSecretPass123', $guruUser->password));
    }

    public function test_guru_cannot_change_password_with_wrong_current_password(): void
    {
        $guruUser = User::where('email', 'guru@sekolah.sch.id')->first();
        $this->actingAs($guruUser);

        $response = $this->put(route('guru.profile.password'), [
            'current_password' => 'wrongOldPassword',
            'password' => 'newSecretPass123',
            'password_confirmation' => 'newSecretPass123',
        ]);

        $response->assertSessionHasErrors('current_password');
    }

    public function test_siswa_cannot_access_guru_profile_page(): void
    {
        $siswaUser = User::where('email', 'siswa@sekolah.sch.id')->first();
        $this->actingAs($siswaUser);

        $response = $this->get(route('guru.profile.index'));

        $response->assertRedirect();
    }

    public function test_guru_can_upload_and_update_photo(): void
    {
        Storage::fake('public');

        $guruUser = User::where('email', 'guru@sekolah.sch.id')->first();
        $this->actingAs($guruUser);

        $file = UploadedFile::fake()->image('guru_avatar.jpg', 400, 400);

        $response = $this->put(route('guru.profile.update'), [
            'name' => $guruUser->name,
            'email' => $guruUser->email,
            'phone' => '081234567890',
            'photo' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $teacher = $guruUser->fresh()->teacher;
        $this->assertNotNull($teacher->photo);
        Storage::disk('public')->assertExists($teacher->photo);
    }

    public function test_guru_can_upload_cropped_photo_base64(): void
    {
        Storage::fake('public');

        $guruUser = User::where('email', 'guru@sekolah.sch.id')->first();
        $this->actingAs($guruUser);

        $fakeImage = UploadedFile::fake()->image('cropped.jpg', 200, 200);
        $base64Data = 'data:image/jpeg;base64,'.base64_encode(file_get_contents($fakeImage->getRealPath()));

        $response = $this->put(route('guru.profile.update'), [
            'name' => $guruUser->name,
            'email' => $guruUser->email,
            'phone' => '081234567890',
            'photo_cropped' => $base64Data,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $teacher = $guruUser->fresh()->teacher;
        $this->assertNotNull($teacher->photo);
        $this->assertStringStartsWith('teachers/crop_', $teacher->photo);
        Storage::disk('public')->assertExists($teacher->photo);
    }

    public function test_guru_can_remove_photo(): void
    {
        Storage::fake('public');

        $guruUser = User::where('email', 'guru@sekolah.sch.id')->first();
        $this->actingAs($guruUser);

        // Upload first
        $file = UploadedFile::fake()->image('avatar.jpg');
        $path = $file->store('teachers', 'public');
        $guruUser->teacher->update(['photo' => $path]);

        // Request removal
        $response = $this->put(route('guru.profile.update'), [
            'name' => $guruUser->name,
            'email' => $guruUser->email,
            'phone' => '081234567890',
            'remove_photo' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertNull($guruUser->fresh()->teacher->photo);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_photo_validation_rejects_non_image_or_oversized_file(): void
    {
        Storage::fake('public');

        $guruUser = User::where('email', 'guru@sekolah.sch.id')->first();
        $this->actingAs($guruUser);

        $invalidFile = UploadedFile::fake()->create('document.pdf', 500);

        $response = $this->put(route('guru.profile.update'), [
            'name' => $guruUser->name,
            'email' => $guruUser->email,
            'phone' => '081234567890',
            'photo' => $invalidFile,
        ]);

        $response->assertSessionHasErrors('photo');
    }
}
