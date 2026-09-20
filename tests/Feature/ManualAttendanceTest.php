<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guru_can_access_manual_attendance_page(): void
    {
        $guruUser = User::where('email', 'guru@sekolah.sch.id')->first();
        $this->actingAs($guruUser);

        $response = $this->get(route('guru.attendance.manual'));

        $response->assertStatus(200);
        $response->assertSee('INPUT PRESENSI MANUAL');
        $response->assertSee('Aksi Cepat');
        $response->assertSee('Semua Hadir');
        $response->assertSee('Semua Alpa');
        $response->assertSee('Simpan Presensi');
    }

    public function test_admin_can_access_manual_attendance_page(): void
    {
        $adminUser = User::where('email', 'admin@sekolah.sch.id')->first();
        $this->actingAs($adminUser);

        $response = $this->get(route('admin.attendances.manual'));

        $response->assertStatus(200);
        $response->assertSee('INPUT PRESENSI MANUAL SISWA');
        $response->assertSee('Pilih Kelas');
        $response->assertSee('Simpan Presensi');
    }

    public function test_guru_can_store_bulk_attendance_for_their_class(): void
    {
        $guruUser = User::where('email', 'guru@sekolah.sch.id')->first();
        $teacher = $guruUser->teacher;
        $class = SchoolClass::where('homeroom_teacher_id', $teacher->id)->first();
        $students = $class->students;

        $this->actingAs($guruUser);

        $date = now()->toDateString();
        $payload = [
            'date' => $date,
            'school_class_id' => $class->id,
            'attendances' => [
                [
                    'student_id' => $students[0]->id,
                    'status' => 'hadir',
                    'notes' => 'Hadir tepat waktu',
                ],
                [
                    'student_id' => $students[1]->id,
                    'status' => 'sakit',
                    'notes' => 'Surat dokter terlampir',
                ],
            ],
        ];

        $response = $this->post(route('guru.attendance.manual.store'), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verifikasi di database
        $this->assertDatabaseHas('attendances', [
            'student_id' => $students[0]->id,
            'status' => 'hadir',
            'method' => 'manual',
            'notes' => 'Hadir tepat waktu',
        ]);

        $this->assertDatabaseHas('attendances', [
            'student_id' => $students[1]->id,
            'status' => 'sakit',
            'method' => 'manual',
            'notes' => 'Surat dokter terlampir',
        ]);
    }

    public function test_guru_cannot_store_attendance_for_class_they_do_not_teach(): void
    {
        $guruUser = User::where('email', 'guru@sekolah.sch.id')->first();
        $teacher = $guruUser->teacher;

        // Cari kelas lain yang bukan binaan/ajar guru ini
        $otherClass = SchoolClass::where('homeroom_teacher_id', '!=', $teacher->id)->first();
        $otherUser = User::factory()->create(['role' => 'siswa']);
        $otherStudent = Student::create([
            'user_id' => $otherUser->id,
            'school_class_id' => $otherClass->id,
            'nis' => '88888',
            'qr_code_identifier' => 'QR-STU-88888',
            'gender' => 'L',
        ]);

        $this->actingAs($guruUser);

        $payload = [
            'date' => now()->toDateString(),
            'school_class_id' => $otherClass->id,
            'attendances' => [
                [
                    'student_id' => $otherStudent->id,
                    'status' => 'alpa',
                    'notes' => null,
                ],
            ],
        ];

        $response = $this->post(route('guru.attendance.manual.store'), $payload);

        $response->assertStatus(403);
    }

    public function test_admin_can_store_attendance_for_any_class(): void
    {
        $adminUser = User::where('email', 'admin@sekolah.sch.id')->first();
        $class = SchoolClass::first();
        $student = $class->students()->first();

        $this->actingAs($adminUser);

        $payload = [
            'date' => now()->toDateString(),
            'school_class_id' => $class->id,
            'attendances' => [
                [
                    'student_id' => $student->id,
                    'status' => 'izin',
                    'notes' => 'Izin keperluan keluarga',
                ],
            ],
        ];

        $response = $this->post(route('admin.attendances.manual.store'), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'status' => 'izin',
            'method' => 'manual',
            'notes' => 'Izin keperluan keluarga',
        ]);
    }

    public function test_siswa_cannot_access_manual_attendance(): void
    {
        $siswaUser = User::where('email', 'siswa@sekolah.sch.id')->first();
        $this->actingAs($siswaUser);

        $responseGuru = $this->get(route('guru.attendance.manual'));
        $responseGuru->assertRedirect();

        $responseAdmin = $this->get(route('admin.attendances.manual'));
        $responseAdmin->assertRedirect();
    }
}
