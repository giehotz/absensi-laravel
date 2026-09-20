<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\StudentNote;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuruHomeroomClassTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guru_as_homeroom_can_access_kelas_binaan_page(): void
    {
        $guruUser = User::where('email', 'guru@sekolah.sch.id')->first();
        $this->actingAs($guruUser);

        $response = $this->get(route('guru.classes.binaan'));

        $response->assertStatus(200);
        $response->assertSee('KELAS BINAAN (WALI KELAS)');
        $response->assertSee('Direktori Siswa');
        $response->assertSee('Pengajuan Izin / Sakit');
        $response->assertSee('Catatan Khusus Siswa');
        $response->assertSee('Input Presensi Hari Ini');
        $response->assertSee('Unduh Excel (.xlsx)');
        $response->assertSee('Detail Siswa');
    }

    public function test_guru_not_homeroom_sees_empty_state(): void
    {
        $plainTeacherUser = User::factory()->create([
            'role' => 'guru',
            'is_active' => true,
        ]);
        Teacher::create([
            'user_id' => $plainTeacherUser->id,
            'nip' => 'TEACHER-NON-HOMEROOM',
            'phone' => '08999999999',
        ]);

        $this->actingAs($plainTeacherUser);

        $response = $this->get(route('guru.classes.binaan'));

        $response->assertStatus(200);
        $response->assertSee('Anda Belum Ditugaskan Sebagai Wali Kelas');
    }

    public function test_guru_can_export_homeroom_students_to_excel(): void
    {
        $guruUser = User::where('email', 'guru@sekolah.sch.id')->first();
        $teacher = $guruUser->teacher;
        $class = SchoolClass::where('homeroom_teacher_id', $teacher->id)->first();

        $this->actingAs($guruUser);

        $response = $this->get(route('guru.classes.binaan.export', [
            'school_class_id' => $class->id,
        ]));

        $response->assertStatus(200);
        $this->assertStringContainsString(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $response->headers->get('Content-Type')
        );
        $this->assertStringContainsString('Daftar-Siswa-Binaan', $response->headers->get('Content-Disposition'));
    }

    public function test_siswa_cannot_access_guru_homeroom_page(): void
    {
        $siswaUser = User::where('email', 'siswa@sekolah.sch.id')->first();
        $this->actingAs($siswaUser);

        $response = $this->get(route('guru.classes.binaan'));

        $response->assertRedirect();
    }

    public function test_guru_can_store_student_note(): void
    {
        $guruUser = User::where('email', 'guru@sekolah.sch.id')->first();
        $teacher = $guruUser->teacher;
        $class = SchoolClass::where('homeroom_teacher_id', $teacher->id)->first();
        $student = $class->students->first();

        $this->actingAs($guruUser);

        $response = $this->post(route('guru.classes.binaan.notes.store'), [
            'student_id' => $student->id,
            'date' => now()->toDateString(),
            'category' => 'kedisiplinan',
            'title' => 'Terlambat Masuk Jam Pertama',
            'content' => 'Siswa terlambat 15 menit dan telah diberikan bimbingan.',
            'follow_up' => 'Diberikan teguran lisan.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('student_notes', [
            'student_id' => $student->id,
            'category' => 'kedisiplinan',
            'title' => 'Terlambat Masuk Jam Pertama',
        ]);
    }

    public function test_guru_can_delete_student_note(): void
    {
        $guruUser = User::where('email', 'guru@sekolah.sch.id')->first();
        $teacher = $guruUser->teacher;
        $class = SchoolClass::where('homeroom_teacher_id', $teacher->id)->first();
        $student = $class->students->first();

        $note = StudentNote::create([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'date' => now()->toDateString(),
            'category' => 'prestasi',
            'title' => 'Juara Catur',
            'content' => 'Meraih juara 1 lomba catur antar kelas.',
        ]);

        $this->actingAs($guruUser);

        $response = $this->delete(route('guru.classes.binaan.notes.destroy', $note->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('student_notes', [
            'id' => $note->id,
        ]);
    }
}
