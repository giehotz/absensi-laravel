<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\SlotTemplate;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuruScheduleFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function createTeacher(): array
    {
        $user = User::factory()->create([
            'name' => 'Ustadz Ahmad Fauzi',
            'role' => 'guru',
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'nip' => '198801012015011001',
            'gender' => 'L',
        ]);

        return [$user, $teacher];
    }

    public function test_guru_can_view_jadwal_page_and_teaching_schedules(): void
    {
        [$user, $teacher] = $this->createTeacher();

        $academicYear = AcademicYear::create([
            'name' => '2025/2026',
            'semester' => 'ganjil',
            'start_date' => '2025-07-01',
            'end_date' => '2025-12-31',
            'is_active' => true,
        ]);

        $schoolClass = SchoolClass::create([
            'name' => 'VII-A',
            'level' => 7,
            'academic_year_id' => $academicYear->id,
            'homeroom_teacher_id' => $teacher->id,
        ]);

        $subject = Subject::create([
            'name' => 'Fiqih Ibadah',
            'code' => 'FQH-01',
        ]);

        Schedule::create([
            'school_class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'day_of_week' => 1,
            'start_time' => '07:30:00',
            'end_time' => '08:50:00',
        ]);

        $response = $this->actingAs($user)->get(route('guru.jadwal'));

        $response->assertStatus(200);
        $response->assertSee('Jadwal Pelajaran &amp; Mengajar', false);
        $response->assertSee('Fiqih Ibadah');
        $response->assertSee('VII-A');
        $response->assertSee('07:30');
        $response->assertSee('08:50');
    }

    public function test_guru_can_switch_to_class_schedule_tab(): void
    {
        [$user, $teacher] = $this->createTeacher();

        $academicYear = AcademicYear::create([
            'name' => '2025/2026',
            'semester' => 'ganjil',
            'start_date' => '2025-07-01',
            'end_date' => '2025-12-31',
            'is_active' => true,
        ]);

        $schoolClass = SchoolClass::create([
            'name' => 'VIII-B',
            'level' => 8,
            'academic_year_id' => $academicYear->id,
            'homeroom_teacher_id' => $teacher->id,
        ]);

        $subject = Subject::create([
            'name' => 'Bahasa Arab',
            'code' => 'ARB-01',
        ]);

        Schedule::create([
            'school_class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'day_of_week' => 2,
            'start_time' => '08:00:00',
            'end_time' => '09:20:00',
        ]);

        $response = $this->actingAs($user)->get(route('guru.jadwal', [
            'tab' => 'class',
            'school_class_id' => $schoolClass->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('VIII-B');
        $response->assertSee('Bahasa Arab');
        $response->assertSee('Ustadz Ahmad Fauzi');
    }

    public function test_siswa_dashboard_displays_schedule_and_activities(): void
    {
        [$teacherUser, $teacher] = $this->createTeacher();

        $academicYear = AcademicYear::create([
            'name' => '2025/2026',
            'semester' => 'ganjil',
            'start_date' => '2025-07-01',
            'end_date' => '2025-12-31',
            'is_active' => true,
        ]);

        $schoolClass = SchoolClass::create([
            'name' => 'IX-A',
            'level' => 9,
            'academic_year_id' => $academicYear->id,
            'homeroom_teacher_id' => $teacher->id,
        ]);

        $studentUser = User::factory()->create(['role' => 'siswa']);
        $studentUser->student()->create([
            'school_class_id' => $schoolClass->id,
            'nis' => '12345',
            'nisn' => '0012345678',
            'gender' => 'L',
            'qr_code_identifier' => 'QR-IXA-001',
        ]);

        $subject = Subject::create([
            'name' => 'Sejarah Kebudayaan Islam',
            'code' => 'SKI-01',
        ]);

        Schedule::create([
            'school_class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'day_of_week' => 1,
            'start_time' => '07:45:00',
            'end_time' => '09:05:00',
        ]);

        SlotTemplate::create([
            'academic_year_id' => $academicYear->id,
            'day_of_week' => 1,
            'jam_ke' => 0,
            'k_jadwal' => SlotTemplate::K_UPACARA,
            'name' => 'Upacara Bendera',
            'start_time' => '07:00:00',
            'end_time' => '07:45:00',
        ]);

        $response = $this->actingAs($studentUser)->get(route('siswa.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Sejarah Kebudayaan Islam');
        $response->assertSee('Upacara Bendera');
        $response->assertSee('IX-A');
    }
}
