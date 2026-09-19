<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $teacherUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::where('email', 'admin@sekolah.sch.id')->first();
        $this->teacherUser = User::where('role', 'guru')->first();
    }

    public function test_admin_can_access_attendance_report_page(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.reports.attendance'));

        $response->assertStatus(200);
        $response->assertSee('Laporan Kehadiran Siswa');
        $response->assertSee('Distribusi Status Kehadiran');
        $response->assertSee('Tren Kehadiran Harian');
        $response->assertSee('Rekapitulasi per Siswa');
        $response->assertSee('Jurnal Log Riwayat Harian');
        $response->assertSee('donutChart');
        $response->assertSee('dailyBarChart');
        $response->assertViewHasAll([
            'startDate',
            'endDate',
            'classes',
            'donutChartData',
            'barChartData',
            'students',
            'attendanceLogs',
        ]);
    }

    public function test_admin_can_filter_attendance_report(): void
    {
        $this->actingAs($this->admin);

        $class = SchoolClass::first();

        $response = $this->get(route('admin.reports.attendance', [
            'start_date' => now()->subDays(7)->toDateString(),
            'end_date' => now()->toDateString(),
            'school_class_id' => $class->id,
            'status' => 'hadir',
            'tab' => 'logs',
        ]));

        $response->assertStatus(200);
        $response->assertSee($class->name);
    }

    public function test_admin_can_export_attendance_report_to_excel(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.reports.attendance.export', [
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->toDateString(),
        ]));

        $response->assertStatus(200);
        $this->assertTrue(
            str_contains(
                $response->headers->get('Content-Disposition') ?? '',
                'laporan_kehadiran_siswa_'
            )
        );
        $this->assertEquals(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $response->headers->get('Content-Type')
        );
    }

    public function test_non_admin_cannot_access_attendance_report(): void
    {
        $this->actingAs($this->teacherUser);

        $response = $this->get(route('admin.reports.attendance'));
        $response->assertRedirect(route('guru.dashboard'));
        $response->assertSessionHas('error');
    }
}
