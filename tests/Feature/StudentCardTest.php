<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Services\QrCodeService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentCardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $teacher;

    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::where('email', 'admin@sekolah.sch.id')->first();
        $this->teacher = User::where('role', 'guru')->first();
        $this->student = Student::with(['user', 'schoolClass'])->first();
    }

    public function test_admin_can_access_student_cards_studio(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.students.cards'));

        $response->assertStatus(200);
        $response->assertSee('Studio Kartu Pelajar');
        $response->assertSee('Cetak Terpilih (Format A4)');
        $response->assertSee($this->student->nis);
        $response->assertSee($this->student->user->name);
        $response->assertViewHasAll(['students', 'classes', 'setting']);
    }

    public function test_admin_can_filter_cards_by_class(): void
    {
        $this->actingAs($this->admin);

        $class = SchoolClass::first();

        $response = $this->get(route('admin.students.cards', ['class_id' => $class->id]));

        $response->assertStatus(200);
        $response->assertViewHas('selectedClassId', (string) $class->id);
    }

    public function test_admin_can_open_print_preview_for_selected_students(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.students.cards.print'), [
            'student_ids' => [$this->student->id],
        ]);

        $response->assertStatus(200);
        $response->assertSee('Pratinjau Lembar Cetak Kartu Siswa (Format A4)');
        $response->assertSee($this->student->nis);
        $response->assertSee($this->student->user->name);
        $response->assertSee('data:image/svg+xml;base64,');
    }

    public function test_admin_can_view_single_student_card(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.students.card.single', $this->student));

        $response->assertStatus(200);
        $response->assertSee('Cetak Kartu Ini');
        $response->assertSee($this->student->nis);
        $response->assertSee($this->student->user->name);
        $response->assertSee('data:image/svg+xml;base64,');
    }

    public function test_student_card_displays_photo_when_available(): void
    {
        $this->actingAs($this->admin);

        $this->student->update(['photo' => 'students/photos/test_student.jpg']);

        $response = $this->get(route('admin.students.card.single', $this->student));
        $response->assertStatus(200);
        $response->assertSee('students/photos/test_student.jpg');
    }

    public function test_qr_code_service_generates_valid_qr(): void
    {
        $service = new QrCodeService;
        $dataUri = $service->generateDataUri('QR-TEST-IDENTIFIER');
        $svg = $service->generateSvg('QR-TEST-IDENTIFIER');

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $dataUri);
        $this->assertStringContainsString('<svg', $svg);
    }

    public function test_non_admin_cannot_access_student_cards(): void
    {
        $this->actingAs($this->teacher);

        $response = $this->get(route('admin.students.cards'));

        $response->assertRedirect(route('guru.dashboard'));
        $response->assertSessionHas('error');
    }
}
