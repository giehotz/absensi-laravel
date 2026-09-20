<?php

namespace Tests\Feature;

use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminScheduleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_can_access_schedules_index_page(): void
    {
        $admin = User::where('email', 'admin@sekolah.sch.id')->first();
        $this->actingAs($admin);

        $response = $this->get(route('admin.schedules.index'));

        $response->assertStatus(200);
        $response->assertSee('JADWAL KELAS MINGGUAN');
        $response->assertSee('Tambah Jadwal Baru');
        $response->assertSee('Template Jam Simpatika');
        $response->assertSee('Pilih Kelas:');
    }

    public function test_admin_can_store_valid_schedule(): void
    {
        $admin = User::where('email', 'admin@sekolah.sch.id')->first();
        $class = SchoolClass::first();
        $subject = Subject::first();
        $teacher = Teacher::first();

        $this->actingAs($admin);

        $response = $this->post(route('admin.schedules.store'), [
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'day_of_week' => 5, // Jumat
            'start_time' => '13:00',
            'end_time' => '14:30',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('schedules', [
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'day_of_week' => 5,
        ]);
    }

    public function test_admin_can_store_multi_hour_schedule_block(): void
    {
        $admin = User::where('email', 'admin@sekolah.sch.id')->first();
        $class = SchoolClass::first();
        $subject = Subject::first();
        $teacher = Teacher::first();

        $this->actingAs($admin);

        // Input 3 JP berturut-turut (Jam ke-1 s.d ke-3, 07:15 - 09:15)
        $response = $this->post(route('admin.schedules.store'), [
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'day_of_week' => 4, // Kamis
            'start_time' => '07:15',
            'end_time' => '09:15',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('schedules', [
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'day_of_week' => 4,
            'start_time' => '07:15',
            'end_time' => '09:15',
        ]);
    }

    public function test_admin_cannot_store_schedule_with_teacher_conflict(): void
    {
        $admin = User::where('email', 'admin@sekolah.sch.id')->first();
        $teacher = Teacher::first();
        $subject = Subject::first();
        $classes = SchoolClass::take(2)->get();
        $classA = $classes[0];
        $classB = $classes[1];

        // Buat jadwal untuk Guru ini di Kelas A pada hari Senin jam 08:00 - 09:30
        Schedule::create([
            'school_class_id' => $classA->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'day_of_week' => 1,
            'start_time' => '08:00',
            'end_time' => '09:30',
        ]);

        $this->actingAs($admin);

        // Coba buat jadwal untuk Guru yang sama di Kelas B pada hari Senin jam 09:00 - 10:30 (overlap 30 menit!)
        $response = $this->post(route('admin.schedules.store'), [
            'school_class_id' => $classB->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '10:30',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('conflict_error');
        $response->assertSessionHasErrors('conflict');

        // Pastikan jadwal bentrok tidak tersimpan
        $this->assertDatabaseMissing('schedules', [
            'school_class_id' => $classB->id,
            'start_time' => '09:00:00',
        ]);
    }

    public function test_admin_cannot_store_schedule_with_class_conflict(): void
    {
        $admin = User::where('email', 'admin@sekolah.sch.id')->first();
        $class = SchoolClass::first();
        $teachers = Teacher::take(2)->get();
        $teacherA = $teachers[0];
        $teacherB = $teachers[1];
        $subject = Subject::first();

        // Buat jadwal di Kelas ini bersama Guru A pada hari Rabu jam 10:00 - 11:30
        Schedule::create([
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacherA->id,
            'day_of_week' => 3,
            'start_time' => '10:00',
            'end_time' => '11:30',
        ]);

        $this->actingAs($admin);

        // Coba masukkan Guru B di Kelas yang sama pada hari Rabu jam 11:00 - 12:30 (overlap 30 menit!)
        $response = $this->post(route('admin.schedules.store'), [
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacherB->id,
            'day_of_week' => 3,
            'start_time' => '11:00',
            'end_time' => '12:30',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('conflict_error');
        $response->assertSessionHasErrors('conflict');

        // Pastikan jadwal bentrok tidak tersimpan
        $this->assertDatabaseMissing('schedules', [
            'teacher_id' => $teacherB->id,
            'start_time' => '11:00:00',
        ]);
    }

    public function test_admin_can_update_schedule(): void
    {
        $admin = User::where('email', 'admin@sekolah.sch.id')->first();
        $schedule = Schedule::first();

        $this->actingAs($admin);

        $response = $this->put(route('admin.schedules.update', $schedule->id), [
            'school_class_id' => $schedule->school_class_id,
            'subject_id' => $schedule->subject_id,
            'teacher_id' => $schedule->teacher_id,
            'day_of_week' => $schedule->day_of_week,
            'start_time' => '14:00',
            'end_time' => '15:30',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $schedule->refresh();
        $this->assertStringStartsWith('14:00', $schedule->start_time);
    }

    public function test_admin_can_delete_schedule(): void
    {
        $admin = User::where('email', 'admin@sekolah.sch.id')->first();
        $schedule = Schedule::first();

        $this->actingAs($admin);

        $response = $this->delete(route('admin.schedules.destroy', $schedule->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('schedules', [
            'id' => $schedule->id,
        ]);
    }

    public function test_guru_dashboard_displays_schedules(): void
    {
        $guru = User::where('email', 'guru@sekolah.sch.id')->first();
        $this->actingAs($guru);

        $response = $this->get(route('guru.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Jadwal Mengajar');
        $response->assertSee('Hari Ini');
        $response->assertSee('Mingguan');
    }

    public function test_non_admin_cannot_access_admin_schedules(): void
    {
        $siswa = User::where('email', 'siswa@sekolah.sch.id')->first();
        $this->actingAs($siswa);

        $response = $this->get(route('admin.schedules.index'));

        $response->assertRedirect();
    }
}
