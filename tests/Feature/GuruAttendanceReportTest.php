<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuruAttendanceReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guru_can_access_attendance_report_page(): void
    {
        $guruUser = User::where('email', 'guru@sekolah.sch.id')->first();
        $this->actingAs($guruUser);

        $response = $this->get(route('guru.reports.attendance'));

        $response->assertStatus(200);
        $response->assertSee('Rekap Kehadiran Siswa');
        $response->assertSee('Distribusi Status Kehadiran');
        $response->assertSee('Tren Kehadiran Harian');
        $response->assertSee('Rekapitulasi per Siswa');
        $response->assertSee('Jurnal Riwayat Harian');
        $response->assertSee('Export Excel (.xlsx)');
    }

    public function test_guru_report_dropdown_shows_teacher_classes(): void
    {
        $guruUser = User::where('email', 'guru@sekolah.sch.id')->first();
        $this->actingAs($guruUser);

        $response = $this->get(route('guru.reports.attendance'));

        $response->assertStatus(200);
        $response->assertSee('Kelas 7A');
    }

    public function test_guru_can_filter_report_by_date_and_class(): void
    {
        $guruUser = User::where('email', 'guru@sekolah.sch.id')->first();
        $class = SchoolClass::where('name', 'Kelas 7A')->first();
        $this->actingAs($guruUser);

        $response = $this->get(route('guru.reports.attendance', [
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->toDateString(),
            'school_class_id' => $class->id,
            'tab' => 'logs',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Kelas 7A');
    }

    public function test_guru_can_export_attendance_report_to_excel(): void
    {
        $guruUser = User::where('email', 'guru@sekolah.sch.id')->first();
        $this->actingAs($guruUser);

        $response = $this->get(route('guru.reports.attendance.export'));

        $response->assertStatus(200);
        $this->assertTrue(
            str_contains($response->headers->get('content-type'), 'spreadsheetml') ||
            str_contains($response->headers->get('content-disposition'), '.xlsx')
        );
    }

    public function test_siswa_cannot_access_guru_attendance_report(): void
    {
        $siswaUser = User::where('email', 'siswa@sekolah.sch.id')->first();
        $this->actingAs($siswaUser);

        $response = $this->get(route('guru.reports.attendance'));
        $response->assertRedirect();
    }
}
