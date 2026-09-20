<?php

namespace Tests\Feature;

use App\Models\LeaveRequest;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiswaDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_siswa_can_access_dashboard_and_see_mobile_first_components(): void
    {
        $siswaUser = User::where('email', 'siswa@sekolah.sch.id')->first();
        $this->actingAs($siswaUser);

        $response = $this->get(route('siswa.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Beranda');
        $response->assertSee('QR Presensi');
        $response->assertSee('Jadwal');
        $response->assertSee('Izin');
        $response->assertSee('Riwayat');
        $response->assertSee('modalFullscreenQr');
        $response->assertSee('modalLeaveRequest');
        $response->assertSee('STATUS PRESENSI HARI INI');
    }

    public function test_siswa_can_submit_leave_request_with_valid_data(): void
    {
        $siswaUser = User::where('email', 'siswa@sekolah.sch.id')->first();
        $this->actingAs($siswaUser);

        $student = $siswaUser->student;

        $response = $this->post(route('siswa.leave-requests.store'), [
            'type' => 'sakit',
            'date_from' => now()->toDateString(),
            'date_to' => now()->addDay()->toDateString(),
            'reason' => 'Sakit demam tinggi dan butuh istirahat',
        ]);

        $response->assertRedirect(route('siswa.dashboard', ['tab' => 'izin']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('leave_requests', [
            'student_id' => $student->id,
            'requested_by' => $siswaUser->id,
            'type' => 'sakit',
            'status' => 'pending',
            'reason' => 'Sakit demam tinggi dan butuh istirahat',
        ]);
    }

    public function test_siswa_can_submit_leave_request_with_attachment(): void
    {
        Storage::fake('public');

        $siswaUser = User::where('email', 'siswa@sekolah.sch.id')->first();
        $this->actingAs($siswaUser);

        $student = $siswaUser->student;
        $fakeFile = UploadedFile::fake()->image('surat_dokter.jpg', 600, 800);

        $response = $this->post(route('siswa.leave-requests.store'), [
            'type' => 'izin',
            'date_from' => now()->toDateString(),
            'date_to' => now()->toDateString(),
            'reason' => 'Menghadiri acara keluarga di luar kota',
            'attachment' => $fakeFile,
        ]);

        $response->assertRedirect(route('siswa.dashboard', ['tab' => 'izin']));
        $response->assertSessionHas('success');

        $leaveRequest = LeaveRequest::where('student_id', $student->id)->latest()->first();
        $this->assertNotNull($leaveRequest->attachment_path);
        Storage::disk('public')->assertExists($leaveRequest->attachment_path);
    }

    public function test_siswa_leave_request_validation(): void
    {
        $siswaUser = User::where('email', 'siswa@sekolah.sch.id')->first();
        $this->actingAs($siswaUser);

        // 1. Missing required fields
        $response = $this->post(route('siswa.leave-requests.store'), []);
        $response->assertSessionHasErrors(['type', 'date_from', 'date_to', 'reason']);

        // 2. date_to precedes date_from
        $responseDate = $this->post(route('siswa.leave-requests.store'), [
            'type' => 'izin',
            'date_from' => '2026-09-25',
            'date_to' => '2026-09-20',
            'reason' => 'Alasan izin',
        ]);
        $responseDate->assertSessionHasErrors(['date_to']);

        // 3. Invalid file type
        $invalidFile = UploadedFile::fake()->create('script.exe', 100);
        $responseFile = $this->post(route('siswa.leave-requests.store'), [
            'type' => 'izin',
            'date_from' => '2026-09-20',
            'date_to' => '2026-09-21',
            'reason' => 'Alasan izin',
            'attachment' => $invalidFile,
        ]);
        $responseFile->assertSessionHasErrors(['attachment']);
    }

    public function test_guest_cannot_access_siswa_dashboard(): void
    {
        $response = $this->get(route('siswa.dashboard'));
        $response->assertRedirect(route('login'));
    }
}
