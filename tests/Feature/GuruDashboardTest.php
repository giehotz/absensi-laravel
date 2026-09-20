<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuruDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guru_dashboard_renders_with_all_components(): void
    {
        $guruUser = User::where('email', 'guru@sekolah.sch.id')->first();
        $this->actingAs($guruUser);

        $response = $this->get(route('guru.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Portal Tenaga Pengajar');
        $response->assertSee('DEWAN GURU');
        $response->assertSee('Hadir Hari Ini');
        $response->assertSee('Jadwal Mengajar Hari Ini');
        $response->assertSee('Ringkasan Kehadiran 7 Hari Terakhir');
        $response->assertSee('Aktivitas Presensi Terbaru');
    }

    public function test_guru_can_approve_leave_request_for_student_in_their_class(): void
    {
        $guruUser = User::where('email', 'guru@sekolah.sch.id')->first();
        $teacher = $guruUser->teacher;
        $class = SchoolClass::where('homeroom_teacher_id', $teacher->id)->first();
        $student = $class->students()->first();

        // Buat permohonan izin
        $leaveRequest = LeaveRequest::create([
            'student_id' => $student->id,
            'requested_by' => $student->user_id,
            'type' => 'sakit',
            'reason' => 'Demam tinggi dan flu',
            'date_from' => now()->toDateString(),
            'date_to' => now()->toDateString(),
            'status' => 'pending',
        ]);

        $this->actingAs($guruUser);

        $response = $this->patch(route('guru.leave-requests.approve', $leaveRequest));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'approved',
            'reviewed_by' => $guruUser->id,
        ]);

        // Verifikasi status presensi terupdate menjadi sakit
        $attendance = Attendance::where('student_id', $student->id)
            ->whereDate('date', now()->toDateString())
            ->first();

        $this->assertNotNull($attendance);
        $this->assertEquals('sakit', $attendance->status);
    }

    public function test_guru_can_reject_leave_request(): void
    {
        $guruUser = User::where('email', 'guru@sekolah.sch.id')->first();
        $teacher = $guruUser->teacher;
        $class = SchoolClass::where('homeroom_teacher_id', $teacher->id)->first();
        $student = $class->students()->first();

        $leaveRequest = LeaveRequest::create([
            'student_id' => $student->id,
            'requested_by' => $student->user_id,
            'type' => 'izin',
            'reason' => 'Acara keluarga mendadak',
            'date_from' => now()->addDays(2)->toDateString(),
            'date_to' => now()->addDays(2)->toDateString(),
            'status' => 'pending',
        ]);

        $this->actingAs($guruUser);

        $response = $this->patch(route('guru.leave-requests.reject', $leaveRequest));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'rejected',
            'reviewed_by' => $guruUser->id,
        ]);

        // Pastikan tidak dibuat attendance
        $attendance = Attendance::where('student_id', $student->id)
            ->whereDate('date', now()->addDays(2)->toDateString())
            ->first();

        $this->assertNull($attendance);
    }

    public function test_guru_cannot_approve_leave_request_for_student_outside_their_scope(): void
    {
        $guruUser = User::where('email', 'guru@sekolah.sch.id')->first();
        $teacher = $guruUser->teacher;

        // Kelas lain yang bukan binaan/ajar Guru ini
        $otherClass = SchoolClass::where('homeroom_teacher_id', '!=', $teacher->id)->first();

        $otherUser = User::factory()->create(['role' => 'siswa']);
        $otherStudent = Student::create([
            'user_id' => $otherUser->id,
            'school_class_id' => $otherClass->id,
            'nis' => '99999',
            'qr_code_identifier' => 'QR-STU-99999',
            'gender' => 'L',
        ]);

        $leaveRequest = LeaveRequest::create([
            'student_id' => $otherStudent->id,
            'requested_by' => $otherStudent->user_id,
            'type' => 'izin',
            'reason' => 'Pergi keluar kota',
            'date_from' => now()->toDateString(),
            'date_to' => now()->toDateString(),
            'status' => 'pending',
        ]);

        $this->actingAs($guruUser);

        $response = $this->patch(route('guru.leave-requests.approve', $leaveRequest));

        $response->assertStatus(403);
    }
}
