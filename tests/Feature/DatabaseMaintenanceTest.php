<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\AttendanceArchive;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseMaintenanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $teacher;

    protected AcademicYear $pastAcademicYear;

    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::where('email', 'admin@sekolah.sch.id')->first();
        $this->teacher = User::where('role', 'guru')->first();
        $this->student = Student::first();

        // Buat Tahun Ajaran Lampau (Non-Aktif)
        $this->pastAcademicYear = AcademicYear::create([
            'name' => '2024/2025',
            'semester' => 'genap',
            'start_date' => '2025-01-01',
            'end_date' => '2025-06-30',
            'is_active' => false,
        ]);

        // Buat beberapa data presensi di tahun ajaran lampau tersebut
        Attendance::create([
            'student_id' => $this->student->id,
            'date' => '2025-02-10',
            'check_in_time' => '2025-02-10 07:15:00',
            'status' => 'hadir',
            'method' => 'qr',
        ]);
        Attendance::create([
            'student_id' => $this->student->id,
            'date' => '2025-02-11',
            'check_in_time' => '2025-02-11 07:35:00',
            'status' => 'terlambat',
            'method' => 'manual',
        ]);
    }

    public function test_admin_can_preview_archive_data(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.database.preview', $this->pastAcademicYear));

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $this->pastAcademicYear->id,
            'name' => '2024/2025',
            'semester' => 'genap',
            'active_count' => 2,
            'archived_count' => 0,
            'can_archive' => true,
        ]);
    }

    public function test_admin_can_archive_past_academic_year(): void
    {
        $this->actingAs($this->admin);

        $this->assertEquals(2, Attendance::whereDate('date', '>=', '2025-01-01')->whereDate('date', '<=', '2025-06-30')->count());
        $this->assertEquals(0, AttendanceArchive::count());

        $response = $this->post(route('admin.database.archive'), [
            'academic_year_id' => $this->pastAcademicYear->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Data harus pindah ke attendances_archive
        $this->assertEquals(0, Attendance::whereDate('date', '>=', '2025-01-01')->whereDate('date', '<=', '2025-06-30')->count());
        $this->assertEquals(2, AttendanceArchive::where('academic_year_id', $this->pastAcademicYear->id)->count());
    }

    public function test_admin_cannot_archive_active_academic_year(): void
    {
        $this->actingAs($this->admin);

        $activeYear = AcademicYear::where('is_active', true)->first();

        $response = $this->post(route('admin.database.archive'), [
            'academic_year_id' => $activeYear->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_admin_can_restore_archived_data(): void
    {
        $this->actingAs($this->admin);

        // Arsipkan terlebih dahulu
        $this->post(route('admin.database.archive'), [
            'academic_year_id' => $this->pastAcademicYear->id,
        ]);

        $this->assertEquals(2, AttendanceArchive::where('academic_year_id', $this->pastAcademicYear->id)->count());

        // Jalankan restore
        $restoreResponse = $this->post(route('admin.database.restore'), [
            'academic_year_id' => $this->pastAcademicYear->id,
        ]);

        $restoreResponse->assertRedirect();
        $restoreResponse->assertSessionHas('success');

        // Data harus kembali ke tabel attendances aktif
        $this->assertEquals(0, AttendanceArchive::where('academic_year_id', $this->pastAcademicYear->id)->count());
        $this->assertEquals(2, Attendance::whereDate('date', '>=', '2025-01-01')->whereDate('date', '<=', '2025-06-30')->count());
    }

    public function test_admin_can_optimize_database(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.database.optimize'));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_non_admin_cannot_access_database_maintenance(): void
    {
        $this->actingAs($this->teacher);

        $response = $this->post(route('admin.database.archive'), [
            'academic_year_id' => $this->pastAcademicYear->id,
        ]);

        $response->assertRedirect(route('guru.dashboard'));
        $response->assertSessionHas('error');
    }
}
