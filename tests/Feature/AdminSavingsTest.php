<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\SavingsTransaction;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSavingsTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser(): User
    {
        return User::factory()->create([
            'role' => 'admin',
        ]);
    }

    private function createTeacherUser(): User
    {
        return User::factory()->create([
            'role' => 'guru',
        ]);
    }

    private function createStudentWithSavings(string $className = '7A', float $balance = 50000): Student
    {
        $user = User::factory()->create(['role' => 'siswa']);

        $academicYear = AcademicYear::firstOrCreate(
            ['name' => '2025/2026'],
            [
                'semester' => 'ganjil',
                'start_date' => '2025-07-01',
                'end_date' => '2025-12-31',
                'is_active' => true,
            ]
        );

        $class = SchoolClass::firstOrCreate(
            ['name' => $className],
            [
                'level' => '7',
                'academic_year_id' => $academicYear->id,
            ]
        );

        $student = Student::create([
            'user_id' => $user->id,
            'school_class_id' => $class->id,
            'nis' => (string) rand(100000, 999999),
            'nisn' => '00'.rand(10000000, 99999999),
            'qr_code_identifier' => 'QR-'.uniqid(),
            'gender' => 'L',
        ]);

        $student->savingsAccount()->create([
            'account_number' => 'TAB-'.$student->nis,
            'balance' => $balance,
            'status' => 'active',
        ]);

        return $student;
    }

    public function test_admin_can_access_savings_monitoring_dashboard(): void
    {
        $admin = $this->createAdminUser();
        $student = $this->createStudentWithSavings('7A', 100000);

        $response = $this->actingAs($admin)->get(route('admin.savings.index'));

        $response->assertOk();
        $response->assertViewIs('admin.savings.index');
        $response->assertViewHasAll(['stats', 'classes', 'classBalances', 'savingsOfficers', 'transactions']);
        $response->assertSeeText('Monitoring & Audit Tabungan Siswa');
        $response->assertSee('7A');
    }

    public function test_guru_is_redirected_away_from_admin_savings(): void
    {
        $teacher = $this->createTeacherUser();

        $response = $this->actingAs($teacher)->get(route('admin.savings.index'));
        $response->assertRedirect(route('guru.dashboard'));
    }

    public function test_guest_is_redirected_to_login_when_accessing_admin_savings(): void
    {
        $response = $this->get(route('admin.savings.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_fetch_class_students_json(): void
    {
        $admin = $this->createAdminUser();
        $student = $this->createStudentWithSavings('7B', 75000);

        $response = $this->actingAs($admin)->getJson(route('admin.savings.class-students', $student->school_class_id));

        $response->assertOk();
        $response->assertJsonStructure([
            'class' => ['id', 'name', 'level', 'total_students', 'registered_count', 'total_balance'],
            'students' => [
                '*' => ['id', 'name', 'nis', 'nisn', 'is_registered', 'account_number', 'balance', 'formatted_balance'],
            ],
        ]);
        $this->assertEquals('7B', $response->json('class.name'));
        $this->assertEquals(75000, $response->json('students.0.balance'));
    }

    public function test_admin_can_export_savings_transactions_csv(): void
    {
        $admin = $this->createAdminUser();
        $student = $this->createStudentWithSavings('7A', 50000);

        // Buat satu transaksi
        SavingsTransaction::create([
            'savings_account_id' => $student->savingsAccount->id,
            'transaction_code' => 'TRX-TEST-001',
            'type' => 'deposit',
            'amount' => 50000,
            'balance_before' => 0,
            'balance_after' => 50000,
            'handled_by' => $admin->id,
            'description' => 'Setoran awal',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.savings.export'));

        $response->assertOk();
        $this->assertTrue(str_contains((string) $response->headers->get('content-disposition'), 'Rekap_Audit_Tabungan_Sekolah_'));
    }

    public function test_admin_can_view_transaction_receipt(): void
    {
        $admin = $this->createAdminUser();
        $student = $this->createStudentWithSavings('7A', 20000);

        $tx = SavingsTransaction::create([
            'savings_account_id' => $student->savingsAccount->id,
            'transaction_code' => 'TRX-RCPT-001',
            'type' => 'deposit',
            'amount' => 20000,
            'balance_before' => 0,
            'balance_after' => 20000,
            'handled_by' => $admin->id,
            'description' => 'Tes kuitansi admin',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.savings.receipt', $tx));

        $response->assertOk();
        $response->assertViewIs('guru.tabungan.receipt');
        $response->assertSee('TRX-RCPT-001');
    }
}
