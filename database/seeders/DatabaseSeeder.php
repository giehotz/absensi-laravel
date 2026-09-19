<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\NotificationLog;
use App\Models\Parents;
use App\Models\QrToken;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $today = Carbon::today();
        $defaultPassword = Hash::make('password');

        // 1. Settings Absensi
        $setting = AttendanceSetting::firstOrCreate(
            ['id' => 1],
            [
                'mode' => 'daily',
                'tolerance_minutes' => 15,
            ]
        );

        // 2. Tahun Ajaran
        $academicYear = AcademicYear::firstOrCreate(
            ['name' => '2026/2027', 'semester' => 'ganjil'],
            [
                'start_date' => '2026-07-15',
                'end_date' => '2026-12-20',
                'is_active' => true,
            ]
        );

        // 3. Mata Pelajaran
        $subMtk = Subject::firstOrCreate(['code' => 'MTK'], ['name' => 'Matematika']);
        $subBin = Subject::firstOrCreate(['code' => 'BIN'], ['name' => 'Bahasa Indonesia']);
        $subIpa = Subject::firstOrCreate(['code' => 'IPA'], ['name' => 'Ilmu Pengetahuan Alam']);
        $subPai = Subject::firstOrCreate(['code' => 'PAI'], ['name' => 'Pendidikan Agama Islam']);

        // 4. Akun Admin
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@sekolah.sch.id'],
            [
                'name' => 'Administrator Sekolah',
                'password' => $defaultPassword,
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // 5. Guru 1 (Budi Santoso, Wali Kelas 7A)
        $guru1User = User::firstOrCreate(
            ['email' => 'guru@sekolah.sch.id'],
            [
                'name' => 'Budi Santoso, S.Pd.',
                'password' => $defaultPassword,
                'role' => 'guru',
                'is_active' => true,
            ]
        );
        $teacher1 = Teacher::firstOrCreate(
            ['user_id' => $guru1User->id],
            [
                'nip' => '198501012010011001',
                'phone' => '081234567801',
            ]
        );

        // Guru 2 (Siti Aminah, Guru IPA)
        $guru2User = User::firstOrCreate(
            ['email' => 'siti@sekolah.sch.id'],
            [
                'name' => 'Siti Aminah, M.Pd.',
                'password' => $defaultPassword,
                'role' => 'guru',
                'is_active' => true,
            ]
        );
        $teacher2 = Teacher::firstOrCreate(
            ['user_id' => $guru2User->id],
            [
                'nip' => '198803152012022002',
                'phone' => '081234567802',
            ]
        );

        // 6. Kelas Sekolah
        $class7A = SchoolClass::firstOrCreate(
            ['name' => 'Kelas 7A', 'academic_year_id' => $academicYear->id],
            [
                'level' => 'SMP',
                'homeroom_teacher_id' => $teacher1->id,
            ]
        );

        $class8B = SchoolClass::firstOrCreate(
            ['name' => 'Kelas 8B', 'academic_year_id' => $academicYear->id],
            [
                'level' => 'SMP',
                'homeroom_teacher_id' => $teacher2->id,
            ]
        );

        // 7. Siswa 1 (Ahmad Fauzi)
        $siswa1User = User::firstOrCreate(
            ['email' => 'siswa@sekolah.sch.id'],
            [
                'name' => 'Ahmad Fauzi',
                'password' => $defaultPassword,
                'role' => 'siswa',
                'is_active' => true,
            ]
        );
        $student1 = Student::firstOrCreate(
            ['user_id' => $siswa1User->id],
            [
                'school_class_id' => $class7A->id,
                'nis' => '12345',
                'nisn' => '0081234567',
                'qr_code_identifier' => 'QR-STU-12345',
                'gender' => 'L',
                'birth_date' => '2012-05-14',
                'phone' => '089876543210',
            ]
        );

        // Siswa 2 (Siti Rahmawati)
        $siswa2User = User::firstOrCreate(
            ['email' => 'rahma@sekolah.sch.id'],
            [
                'name' => 'Siti Rahmawati',
                'password' => $defaultPassword,
                'role' => 'siswa',
                'is_active' => true,
            ]
        );
        $student2 = Student::firstOrCreate(
            ['user_id' => $siswa2User->id],
            [
                'school_class_id' => $class7A->id,
                'nis' => '12346',
                'nisn' => '0081234568',
                'qr_code_identifier' => 'QR-STU-12346',
                'gender' => 'P',
                'birth_date' => '2012-08-21',
                'phone' => '089876543211',
            ]
        );

        // 8. Orang Tua (Joko Pratama, Orang Tua Ahmad Fauzi)
        $ortuUser = User::firstOrCreate(
            ['email' => 'ortu@sekolah.sch.id'],
            [
                'name' => 'Joko Pratama',
                'password' => $defaultPassword,
                'role' => 'orangtua',
                'is_active' => true,
            ]
        );
        $parent = Parents::firstOrCreate(
            ['user_id' => $ortuUser->id],
            [
                'phone' => '081234567890',
                'relation' => 'ayah',
            ]
        );
        $parent->students()->syncWithoutDetaching([$student1->id]);

        // 9. Jadwal Pelajaran Kelas 7A (hari ini dan hari lain)
        $currentDayOfWeek = (int) Carbon::now()->dayOfWeekIso; // 1 = Senin ... 7 = Minggu
        $scheduleToday = Schedule::firstOrCreate(
            [
                'school_class_id' => $class7A->id,
                'subject_id' => $subMtk->id,
                'day_of_week' => $currentDayOfWeek,
            ],
            [
                'teacher_id' => $teacher1->id,
                'start_time' => '07:30:00',
                'end_time' => '09:30:00',
            ]
        );

        Schedule::firstOrCreate(
            [
                'school_class_id' => $class7A->id,
                'subject_id' => $subIpa->id,
                'day_of_week' => $currentDayOfWeek,
            ],
            [
                'teacher_id' => $teacher2->id,
                'start_time' => '09:45:00',
                'end_time' => '11:45:00',
            ]
        );

        // 10. QR Token Aktif untuk Siswa 1
        QrToken::updateOrCreate(
            ['student_id' => $student1->id, 'is_used' => false],
            [
                'token' => 'TOKEN-FAUZI-'.Carbon::now()->format('Ymd').'-9981',
                'expires_at' => Carbon::now()->endOfDay(),
                'is_used' => false,
            ]
        );

        // 11. Data Absensi Hari Ini & Sebelumnya
        // Hari ini: Siswa 1 Hadir tepat waktu (07:12 WIB)
        $attendanceToday1 = Attendance::updateOrCreate(
            [
                'student_id' => $student1->id,
                'date' => $today->toDateString(),
            ],
            [
                'schedule_id' => null, // mode daily
                'check_in_time' => Carbon::parse($today->toDateString().' 07:12:00'),
                'check_out_time' => null,
                'status' => 'hadir',
                'method' => 'qr',
                'recorded_by' => $guru1User->id,
                'notes' => 'Tepat waktu melalui scan QR kartu',
            ]
        );

        // Hari ini: Siswa 2 Terlambat (07:38 WIB)
        Attendance::updateOrCreate(
            [
                'student_id' => $student2->id,
                'date' => $today->toDateString(),
            ],
            [
                'schedule_id' => null,
                'check_in_time' => Carbon::parse($today->toDateString().' 07:38:00'),
                'check_out_time' => null,
                'status' => 'terlambat',
                'method' => 'qr',
                'recorded_by' => $guru1User->id,
                'notes' => 'Terlambat 8 menit dari batas toleransi',
            ]
        );

        // Absensi Kemarin untuk riwayat
        $yesterday = Carbon::yesterday();
        Attendance::updateOrCreate(
            ['student_id' => $student1->id, 'date' => $yesterday->toDateString()],
            [
                'schedule_id' => null,
                'check_in_time' => Carbon::parse($yesterday->toDateString().' 07:10:00'),
                'check_out_time' => Carbon::parse($yesterday->toDateString().' 14:05:00'),
                'status' => 'hadir',
                'method' => 'qr',
                'recorded_by' => $guru1User->id,
                'notes' => 'Hadir penuh',
            ]
        );

        Attendance::updateOrCreate(
            ['student_id' => $student2->id, 'date' => $yesterday->toDateString()],
            [
                'schedule_id' => null,
                'check_in_time' => Carbon::parse($yesterday->toDateString().' 07:14:00'),
                'check_out_time' => Carbon::parse($yesterday->toDateString().' 14:00:00'),
                'status' => 'hadir',
                'method' => 'qr',
                'recorded_by' => $guru1User->id,
                'notes' => 'Hadir tepat waktu',
            ]
        );

        // 12. Log Notifikasi WhatsApp ke Orang Tua
        NotificationLog::updateOrCreate(
            [
                'parent_id' => $parent->id,
                'attendance_id' => $attendanceToday1->id,
            ],
            [
                'leave_request_id' => null,
                'channel' => 'whatsapp',
                'status' => 'sent',
                'message' => 'Yth. Bapak Joko Pratama, ananda Ahmad Fauzi telah tercatat HADIR di sekolah pada pukul 07:12 WIB.',
                'sent_at' => Carbon::parse($today->toDateString().' 07:12:30'),
            ]
        );
    }
}
