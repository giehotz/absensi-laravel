<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AttendanceSetting;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::where('email', 'admin@sekolah.sch.id')->first();
    }

    public function test_admin_can_view_teachers_list_and_create_teacher(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.teachers.index'));
        $response->assertStatus(200);
        $response->assertSee('Daftar Guru');

        $storeResponse = $this->post(route('admin.teachers.store'), [
            'name' => 'Drs. Supriyanto',
            'email' => 'supri@sekolah.sch.id',
            'nip' => '197912122005011003',
            'phone' => '081234567899',
            'password' => 'password',
        ]);

        $storeResponse->assertRedirect(route('admin.teachers.index'));
        $storeResponse->assertSessionHas('success');

        $this->assertDatabaseHas('teachers', ['nip' => '197912122005011003']);
        $this->assertDatabaseHas('users', ['email' => 'supri@sekolah.sch.id', 'role' => 'guru']);
    }

    public function test_admin_can_create_class(): void
    {
        $this->actingAs($this->admin);

        $teacher = Teacher::first();
        $academicYear = AcademicYear::first();

        // Level tidak perlu dikirim dari form, diambil otomatis dari pengaturan lembaga
        $response = $this->post(route('admin.classes.store'), [
            'name' => 'Kelas 9C',
            'academic_year_id' => $academicYear->id,
            'homeroom_teacher_id' => $teacher->id,
        ]);

        $response->assertRedirect(route('admin.classes.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('school_classes', [
            'name' => 'Kelas 9C',
            'level' => 'SMP',
        ]);
    }

    public function test_admin_can_update_and_delete_class(): void
    {
        $this->actingAs($this->admin);

        $schoolClass = SchoolClass::where('name', 'Kelas 8B')->first();

        $updateResponse = $this->put(route('admin.classes.update', $schoolClass), [
            'name' => 'Kelas 8B Unggulan',
            'academic_year_id' => $schoolClass->academic_year_id,
        ]);
        $updateResponse->assertRedirect(route('admin.classes.index'));
        $this->assertDatabaseHas('school_classes', ['id' => $schoolClass->id, 'name' => 'Kelas 8B Unggulan']);

        $deleteResponse = $this->delete(route('admin.classes.destroy', $schoolClass));
        $deleteResponse->assertRedirect(route('admin.classes.index'));
        $this->assertDatabaseMissing('school_classes', ['id' => $schoolClass->id]);
    }

    public function test_admin_can_view_class_students_page(): void
    {
        $this->actingAs($this->admin);

        $schoolClass = SchoolClass::first();

        $classesIndexResponse = $this->get(route('admin.classes.index'));
        $classesIndexResponse->assertStatus(200);
        $classesIndexResponse->assertSee('Lihat & Kelola Siswa Kelas Ini', false);
        $classesIndexResponse->assertSee('Edit Data Kelas');
        $classesIndexResponse->assertSee('Hapus Kelas');

        $studentsPageResponse = $this->get(route('admin.classes.students', $schoolClass));
        $studentsPageResponse->assertStatus(200);
        $studentsPageResponse->assertSee($schoolClass->name);
        $studentsPageResponse->assertSee('Daftar Anggota Rombel');
    }

    public function test_admin_can_download_class_students_template(): void
    {
        $this->actingAs($this->admin);

        $schoolClass = SchoolClass::first();

        $response = $this->get(route('admin.classes.students.template', $schoolClass));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_admin_can_import_students_to_class_via_excel(): void
    {
        $this->actingAs($this->admin);

        $schoolClass = SchoolClass::first();
        $initialCount = $schoolClass->students()->count();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Nama Lengkap Siswa');
        $sheet->setCellValue('C1', 'NIS');
        $sheet->setCellValue('D1', 'NISN');
        $sheet->setCellValue('E1', 'Jenis Kelamin (L/P)');
        $sheet->setCellValue('F1', 'Tanggal Lahir');
        $sheet->setCellValue('G1', 'No. Telepon');
        $sheet->setCellValue('H1', 'Email');

        $sheet->setCellValue('A2', 1);
        $sheet->setCellValue('B2', 'Siswa Rombel Import');
        $sheet->setCellValueExplicit('C2', '77889900', DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('D2', '0099887766', DataType::TYPE_STRING);
        $sheet->setCellValue('E2', 'P');
        $sheet->setCellValue('F2', '2010-06-12');
        $sheet->setCellValueExplicit('G2', '081299998888', DataType::TYPE_STRING);
        $sheet->setCellValue('H2', 'siswarombel@sekolah.sch.id');

        $tempPath = tempnam(sys_get_temp_dir(), 'test_class_students_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $file = new UploadedFile($tempPath, 'siswa_rombel.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->post(route('admin.classes.students.import', $schoolClass), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.classes.students', $schoolClass));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'nis' => '77889900',
            'school_class_id' => $schoolClass->id,
            'gender' => 'P',
        ]);
        $this->assertDatabaseHas('users', [
            'name' => 'Siswa Rombel Import',
            'email' => 'siswarombel@sekolah.sch.id',
            'role' => 'siswa',
        ]);

        $this->assertEquals($initialCount + 1, $schoolClass->fresh()->students()->count());

        @unlink($tempPath);
    }

    public function test_admin_can_create_student_with_auto_generated_qr(): void
    {
        $this->actingAs($this->admin);

        $schoolClass = SchoolClass::first();

        $response = $this->post(route('admin.students.store'), [
            'name' => 'Bambang Pamungkas',
            'nis' => '99881',
            'nisn' => '0089988111',
            'school_class_id' => $schoolClass->id,
            'gender' => 'L',
            'birth_date' => '2012-10-10',
            'phone' => '081234567800',
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');

        $student = Student::where('nis', '99881')->first();
        $this->assertNotNull($student);
        $this->assertStringStartsWith('QR-99881-', $student->qr_code_identifier);
        $this->assertDatabaseHas('users', ['email' => '99881@siswa.sekolah.sch.id', 'role' => 'siswa']);
    }

    public function test_admin_can_create_student_with_photo(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin);

        $schoolClass = SchoolClass::first();
        $photo = UploadedFile::fake()->image('avatar.jpg', 300, 400);

        $response = $this->post(route('admin.students.store'), [
            'name' => 'Foto Siswa Test',
            'nis' => '99882',
            'school_class_id' => $schoolClass->id,
            'gender' => 'L',
            'photo' => $photo,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');

        $student = Student::where('nis', '99882')->first();
        $this->assertNotNull($student);
        $this->assertNotNull($student->photo);
        Storage::disk('public')->assertExists($student->photo);
    }

    public function test_admin_can_update_student_photo_and_remove_old_photo(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin);

        $schoolClass = SchoolClass::first();
        $oldPhoto = UploadedFile::fake()->image('old.jpg');
        $oldPath = $oldPhoto->store('students/photos', 'public');

        $student = Student::first();
        $student->update(['photo' => $oldPath]);

        $newPhoto = UploadedFile::fake()->image('new.jpg');

        $response = $this->put(route('admin.students.update', $student), [
            'name' => $student->user->name,
            'nis' => $student->nis,
            'school_class_id' => $schoolClass->id,
            'gender' => $student->gender,
            'photo' => $newPhoto,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $student->refresh();

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($student->photo);
    }

    public function test_admin_can_remove_student_photo(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin);

        $photo = UploadedFile::fake()->image('to_remove.jpg');
        $photoPath = $photo->store('students/photos', 'public');

        $student = Student::first();
        $student->update(['photo' => $photoPath]);

        $response = $this->put(route('admin.students.update', $student), [
            'name' => $student->user->name,
            'nis' => $student->nis,
            'school_class_id' => $student->school_class_id,
            'gender' => $student->gender,
            'remove_photo' => '1',
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $student->refresh();

        $this->assertNull($student->photo);
        Storage::disk('public')->assertMissing($photoPath);
    }

    public function test_student_photo_is_deleted_when_student_is_destroyed(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin);

        $photo = UploadedFile::fake()->image('delete_me.jpg');
        $photoPath = $photo->store('students/photos', 'public');

        $student = Student::first();
        $student->update(['photo' => $photoPath]);

        $response = $this->delete(route('admin.students.destroy', $student));
        $response->assertRedirect(route('admin.students.index'));

        Storage::disk('public')->assertMissing($photoPath);
        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }

    public function test_admin_can_create_subject(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.subjects.store'), [
            'name' => 'Pendidikan Jasmani & Kesehatan',
            'code' => 'PJK',
        ]);

        $response->assertRedirect(route('admin.subjects.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('subjects', ['code' => 'PJK']);
    }

    public function test_admin_can_update_attendance_settings(): void
    {
        $this->actingAs($this->admin);

        $response = $this->put(route('admin.settings.update'), [
            'mode' => 'per_lesson',
            'tolerance_minutes' => 20,
            'school_name' => 'SMP Negeri 1 Unggulan',
            'npsn' => '20109999',
            'level' => 'MTs',
            'school_address' => 'Jl. Garuda No. 100',
        ]);

        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHas('success');

        $setting = AttendanceSetting::first();
        $this->assertEquals('per_lesson', $setting->mode);
        $this->assertEquals(20, $setting->tolerance_minutes);
        $this->assertEquals('SMP Negeri 1 Unggulan', $setting->school_name);
        $this->assertEquals('20109999', $setting->npsn);
        $this->assertEquals('MTs', $setting->level);
        $this->assertEquals('Jl. Garuda No. 100', $setting->school_address);

        // Memastikan kelas yang ada juga tersinkronisasi ke jenjang MTs sebagai sumber data utama
        $this->assertDatabaseHas('school_classes', ['level' => 'MTs']);
    }

    public function test_admin_can_download_teacher_excel_template(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.teachers.template'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_admin_can_import_teachers_via_csv(): void
    {
        $this->actingAs($this->admin);

        $csvContent = "No,Nama Lengkap,NIP,Email,No. Telepon,Password\n".
                      "1,Guru Baru Satu,198001012005011099,gurubaru1@sekolah.sch.id,081299990001,password\n".
                      "2,Guru Baru Dua,198102022006022099,gurubaru2@sekolah.sch.id,081299990002,password\n";

        $file = UploadedFile::fake()->createWithContent('template_guru.csv', $csvContent);

        $response = $this->post(route('admin.teachers.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.teachers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('teachers', [
            'nip' => '198001012005011099',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'gurubaru1@sekolah.sch.id',
            'role' => 'guru',
        ]);
    }

    public function test_admin_can_import_teachers_via_xlsx(): void
    {
        $this->actingAs($this->admin);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Nama Lengkap');
        $sheet->setCellValue('C1', 'NIP');
        $sheet->setCellValue('D1', 'Email');
        $sheet->setCellValue('E1', 'No. Telepon');
        $sheet->setCellValue('F1', 'Password');

        $sheet->setCellValue('A2', 1);
        $sheet->setCellValue('B2', 'Guru Excel Test');
        $sheet->setCellValueExplicit('C2', '198203032008011099', DataType::TYPE_STRING);
        $sheet->setCellValue('D2', 'guruexcel@sekolah.sch.id');
        $sheet->setCellValueExplicit('E2', '081288887777', DataType::TYPE_STRING);
        $sheet->setCellValue('F2', 'rahasia123');

        $tempPath = tempnam(sys_get_temp_dir(), 'test_excel_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $file = new UploadedFile($tempPath, 'guru_import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->post(route('admin.teachers.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.teachers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('teachers', [
            'nip' => '198203032008011099',
            'phone' => '081288887777',
        ]);
        $this->assertDatabaseHas('users', [
            'name' => 'Guru Excel Test',
            'email' => 'guruexcel@sekolah.sch.id',
        ]);

        @unlink($tempPath);
    }

    public function test_guru_cannot_access_admin_management_pages(): void
    {
        $guru = User::where('email', 'guru@sekolah.sch.id')->first();
        $this->actingAs($guru);

        $response = $this->get(route('admin.teachers.index'));
        $response->assertRedirect(route('guru.dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_admin_can_create_academic_year(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.academic-years.store'), [
            'name' => '2027/2028',
            'semester' => 'ganjil',
            'start_date' => '2027-07-15',
            'end_date' => '2027-12-20',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('academic_years', [
            'name' => '2027/2028',
            'semester' => 'ganjil',
            'is_active' => true,
        ]);

        // Memastikan tahun ajaran sebelumnya otomatis tidak aktif
        $this->assertEquals(1, AcademicYear::where('is_active', true)->count());
    }

    public function test_admin_can_update_academic_year(): void
    {
        $this->actingAs($this->admin);

        $academicYear = AcademicYear::first();

        $response = $this->put(route('admin.academic-years.update', $academicYear), [
            'name' => '2026/2027 Revisi',
            'semester' => 'genap',
            'start_date' => '2027-01-05',
            'end_date' => '2027-06-25',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHas('success');

        $academicYear->refresh();
        $this->assertEquals('2026/2027 Revisi', $academicYear->name);
        $this->assertEquals('genap', $academicYear->semester);
    }

    public function test_admin_can_toggle_academic_year_status(): void
    {
        $this->actingAs($this->admin);

        $newYear = AcademicYear::create([
            'name' => '2028/2029',
            'semester' => 'ganjil',
            'start_date' => '2028-07-15',
            'end_date' => '2028-12-20',
            'is_active' => false,
        ]);

        // Aktifkan tahun ajaran baru
        $response = $this->patch(route('admin.academic-years.toggle', $newYear));
        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHas('success');

        $newYear->refresh();
        $this->assertTrue($newYear->is_active);
        $this->assertEquals(1, AcademicYear::where('is_active', true)->count());

        // Nonaktifkan kembali
        $deactivateResponse = $this->patch(route('admin.academic-years.toggle', $newYear));
        $deactivateResponse->assertRedirect(route('admin.settings.index'));
        $newYear->refresh();
        $this->assertFalse($newYear->is_active);
    }

    public function test_admin_cannot_delete_academic_year_with_associated_classes(): void
    {
        $this->actingAs($this->admin);

        $academicYear = AcademicYear::whereHas('schoolClasses')->first();

        $response = $this->delete(route('admin.academic-years.destroy', $academicYear));
        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('academic_years', ['id' => $academicYear->id]);
    }

    public function test_admin_can_delete_unused_academic_year(): void
    {
        $this->actingAs($this->admin);

        $unusedYear = AcademicYear::create([
            'name' => '2029/2030',
            'semester' => 'ganjil',
            'start_date' => '2029-07-15',
            'end_date' => '2029-12-20',
            'is_active' => false,
        ]);

        $response = $this->delete(route('admin.academic-years.destroy', $unusedYear));
        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('academic_years', ['id' => $unusedYear->id]);
    }
}
