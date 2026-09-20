<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GuruLeaveRequestManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guru_can_access_leave_requests_index_page(): void
    {
        $guruUser = User::where('email', 'guru@sekolah.sch.id')->first();
        $this->actingAs($guruUser);

        $response = $this->get(route('guru.leave-requests.index'));

        $response->assertStatus(200);
        $response->assertSee('PERIZINAN SISWA');
        $response->assertSee('Menunggu Verifikasi');
        $response->assertSee('Disetujui');
        $response->assertSee('Ditolak');
        $response->assertSee('Total Pengajuan');
        $response->assertSee('Catat Izin Manual');
        $response->assertSee('Unduh Excel (.xlsx)');
    }

    public function test_guru_can_store_manual_leave_request(): void
    {
        Storage::fake('public');

        $guruUser = User::where('email', 'guru@sekolah.sch.id')->first();
        $teacher = $guruUser->teacher;
        $class = SchoolClass::where('homeroom_teacher_id', $teacher->id)->first();
        $student = $class->students->first();

        $this->actingAs($guruUser);

        $fakeFile = UploadedFile::fake()->create('surat-dokter.pdf', 500, 'application/pdf');

        $response = $this->post(route('guru.leave-requests.store'), [
            'student_id' => $student->id,
            'type' => 'sakit',
            'date_from' => '2026-09-21',
            'date_to' => '2026-09-22',
            'reason' => 'Demam tinggi dan perlu istirahat berdasarkan resep dokter.',
            'attachment' => $fakeFile,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Pastikan record leave_requests tersimpan dengan status approved
        $leave = LeaveRequest::where('student_id', $student->id)
            ->where('requested_by', $guruUser->id)
            ->where('type', 'sakit')
            ->first();

        $this->assertNotNull($leave);
        $this->assertEquals('approved', $leave->status);
        $this->assertEquals($guruUser->id, $leave->reviewed_by);

        // Pastikan presensi otomatis tercipta untuk tanggal 2026-09-21 dan 2026-09-22
        $att1 = Attendance::where('student_id', $student->id)->whereDate('date', '2026-09-21')->first();
        $this->assertNotNull($att1);
        $this->assertEquals('sakit', $att1->status);
        $this->assertEquals('manual', $att1->method);
        $this->assertEquals($guruUser->id, $att1->recorded_by);

        $att2 = Attendance::where('student_id', $student->id)->whereDate('date', '2026-09-22')->first();
        $this->assertNotNull($att2);
        $this->assertEquals('sakit', $att2->status);
        $this->assertEquals('manual', $att2->method);
        $this->assertEquals($guruUser->id, $att2->recorded_by);
    }

    public function test_guru_cannot_store_leave_request_for_unauthorized_class(): void
    {
        $guruUser = User::where('email', 'guru@sekolah.sch.id')->first();

        $academicYear = AcademicYear::first();
        // Buat kelas lain dan siswa di dalamnya yang tidak diajar oleh guru ini
        $otherClass = SchoolClass::create([
            'academic_year_id' => $academicYear ? $academicYear->id : 1,
            'name' => 'Kelas Lain 99',
            'level' => 12,
            'homeroom_teacher_id' => null,
        ]);
        $otherUser = User::factory()->create(['role' => 'siswa']);
        $otherStudent = Student::create([
            'user_id' => $otherUser->id,
            'school_class_id' => $otherClass->id,
            'nis' => '99999',
            'nisn' => '9999999999',
            'gender' => 'L',
            'qr_code_identifier' => 'QR-99999',
        ]);

        $this->actingAs($guruUser);

        $response = $this->post(route('guru.leave-requests.store'), [
            'student_id' => $otherStudent->id,
            'type' => 'izin',
            'date_from' => '2026-09-21',
            'date_to' => '2026-09-21',
            'reason' => 'Izin keperluan keluarga',
        ]);

        $response->assertStatus(403);
    }

    public function test_guru_can_approve_pending_leave_request(): void
    {
        $guruUser = User::where('email', 'guru@sekolah.sch.id')->first();
        $teacher = $guruUser->teacher;
        $class = SchoolClass::where('homeroom_teacher_id', $teacher->id)->first();
        $student = $class->students->first();

        // Buat pengajuan pending dari orang tua / siswa
        $leaveRequest = LeaveRequest::create([
            'student_id' => $student->id,
            'requested_by' => $student->user_id,
            'type' => 'izin',
            'reason' => 'Acara keluarga di luar kota',
            'date_from' => '2026-09-25',
            'date_to' => '2026-09-25',
            'status' => 'pending',
        ]);

        $this->actingAs($guruUser);

        $response = $this->patch(route('guru.leave-requests.approve', $leaveRequest->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $leaveRequest->refresh();
        $this->assertEquals('approved', $leaveRequest->status);
        $this->assertEquals($guruUser->id, $leaveRequest->reviewed_by);

        // Presensi harus tercatat otomatis
        $att = Attendance::where('student_id', $student->id)->whereDate('date', '2026-09-25')->first();
        $this->assertNotNull($att);
        $this->assertEquals('izin', $att->status);
        $this->assertEquals('manual', $att->method);
        $this->assertEquals($guruUser->id, $att->recorded_by);
    }

    public function test_guru_can_reject_pending_leave_request(): void
    {
        $guruUser = User::where('email', 'guru@sekolah.sch.id')->first();
        $teacher = $guruUser->teacher;
        $class = SchoolClass::where('homeroom_teacher_id', $teacher->id)->first();
        $student = $class->students->first();

        $leaveRequest = LeaveRequest::create([
            'student_id' => $student->id,
            'requested_by' => $student->user_id,
            'type' => 'izin',
            'reason' => 'Alasan tidak jelas',
            'date_from' => '2026-09-28',
            'date_to' => '2026-09-28',
            'status' => 'pending',
        ]);

        $this->actingAs($guruUser);

        $response = $this->patch(route('guru.leave-requests.reject', $leaveRequest->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $leaveRequest->refresh();
        $this->assertEquals('rejected', $leaveRequest->status);
        $this->assertEquals($guruUser->id, $leaveRequest->reviewed_by);

        // Pastikan TIDAK membuat record attendance
        $att = Attendance::where('student_id', $student->id)->whereDate('date', '2026-09-28')->first();
        $this->assertNull($att);
    }

    public function test_guru_can_export_leave_requests_to_excel(): void
    {
        $guruUser = User::where('email', 'guru@sekolah.sch.id')->first();
        $this->actingAs($guruUser);

        $response = $this->get(route('guru.leave-requests.export'));

        $response->assertStatus(200);
        $this->assertStringContainsString(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $response->headers->get('Content-Type')
        );
        $this->assertStringContainsString('Rekap-Perizinan-Siswa', $response->headers->get('Content-Disposition'));
    }

    public function test_siswa_cannot_access_guru_leave_requests_page(): void
    {
        $siswaUser = User::where('email', 'siswa@sekolah.sch.id')->first();
        $this->actingAs($siswaUser);

        $response = $this->get(route('guru.leave-requests.index'));

        $response->assertRedirect();
    }
}
