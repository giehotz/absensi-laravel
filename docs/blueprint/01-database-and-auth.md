# Blueprint 01 — Database, Integrasi Eksisting, Eloquent Models, & Auth Multi-Role (Fase 2)

> **Target Arsitektur:** Modular Monolith terintegrasi ke `/var/www/absensi-laravel`
> **Namespace Terisolasi:** `App\Http\Controllers\Cbt\*`, `App\Models\Cbt\*`, `App\Services\Cbt\*`, `routes/cbt.php`
> **Aturan Keras:** Jangan menduplikasi tabel master/auth/`ujian_*` yang sudah dimiliki `/var/www/absensi-laravel`. Semua tabel baru wajib ber-prefix `cbt_*`, menggunakan `bigIncrements('id')` + `unique()` constraint (tanpa composite string ID CI3), dan mengganti seluruh `PHP serialize()` dengan kolom `json`.

---

## 1. Hasil Audit Singkat `/var/www/absensi-laravel`

| Aspek | Temuan Aktual di `/var/www/absensi-laravel` | Implikasi Integrasi CBT |
|---|---|---|
| **Runtime & Framework** | PHP `^8.3`, Laravel `^13.17`, PHPUnit `^12.5` (`composer.json`) | Semua kode CBT memakai fitur PHP 8.3 & konvensi Laravel 13 (`casts(): array`, `#[Fillable]` / `$fillable`, FormRequest). |
| **Stack Frontend & CSS** | Vite `^8.0`, Tailwind CSS `^4.0` (`resources/css/app.css`), Blade layouts (`admin.blade.php`, `guru.blade.php`, `neobrutalism.blade.php` untuk siswa, `ujian.blade.php` untuk panitia), Vanilla JS + SweetAlert2 (`vendor/sweetalert2`) | **Tanpa Vue/Inertia**. UI CBT dibangun dengan Blade + Tailwind CSS v4 bergaya Neobrutalism (`.neo-box`, `#FFD43B`, `#5294FF`, `#20C997`, `#FF6B6B`, `border-black`) + Vanilla JS modular via Vite/Blade `@push('scripts')`. |
| **Library Export/Cetak** | `phpoffice/phpspreadsheet: ^5.10`, `endroid/qr-code: ^6.1`; cetak memakai `<x-print-preview-layout>` berbasis HTML/CSS `@media print` (bukan DomPDF) | Cetak hasil/kartu/berita acara menyelaraskan ke `<x-print-preview-layout>` dan `PhpSpreadsheet`. |
| **Autentikasi & Role** | `AuthController` (**Universal Login**: `email` / `nip` / `nuptk` / `nis` / `nisn`) + `users.role` enum (`admin`, `guru`, `siswa`, `orangtua`, `petugas_mbg`, `kepala`) + `users.is_active` | Gunakan `users` dan sesi Laravel yang sudah ada. Tambahkan rate-limiter lockout `3` percobaan / `600` detik + akses siswa CBT pada portal siswa. |
| **Middleware Eksisting** | `role` (`EnsureRole`), `EnsurePanitiaUjian` (`isAdmin \|\| isPanitiaUjian \|\| isKepala`), `EnsureKepalaMonitoring` (read-only bagi `kepala`) | Modul CBT memanfaatkan `auth`, `role:*`, `EnsurePanitiaUjian`, dan `EnsureKepalaMonitoring`, plus Policy kepemilikan bank soal untuk guru. |
| **Modul Ujian Eksisting** | Sudah memiliki modul administrasi ujian offline lengkap: `ujian_jenis`, `ujian_periode`, `ujian_panitia`, `ujian_ruang`, `ujian_sesi`, `ujian_peserta`, `ujian_jadwal`, `ujian_jadwal_pengawas`, `ujian_pengawas_mappings`, `ujian_berita_acara`, `ujian_pengaturan_ttd` | **Tidak boleh membuat ulang** `cbt_jenis`, `cbt_ruang`, `cbt_sesi`, `cbt_nomor_peserta`, `cbt_sesi_siswa`, `cbt_pengawas`, maupun `cbt_kop_*`. Mesin CBT online cukup mereferensikan entitas `ujian_*` yang sudah ada. |

---

## 2. Matriks Pemetaan Tabel (GarudaCBT CI3 → `absensi-laravel`)

### A. Tabel GarudaCBT yang **Di-Reuse** dari `/var/www/absensi-laravel` (TIDAK Dibuat Ulang)
| Tabel CI3 (`/var/www/cbt`) | Tabel/Kolom Eksisting di `/var/www/absensi-laravel` | Catatan Integrasi |
|---|---|---|
| `users`, `groups`, `users_groups`, `users_profile` | `users` (`role`, `is_active`, `nip`, `password`), `sessions` | Role dari kolom `users.role` (`admin`, `guru`, `siswa`, `kepala`) + jabatan panitia di `ujian_panitia`. |
| `login_attempts` | Laravel `RateLimiter` / cache/DB throttle (`config/cache.php`) | Lockout 3 percobaan gagal selama 600 detik per `identifier` (tanpa IP) menggunakan key `cbt_login:{identifier}` agar admin/panitia bisa langsung melakukan `RateLimiter::clear()` lewat `POST /cbt/users/unlock` berdasarkan identifier siswa tanpa perlu tahu IP perangkatnya. |
| `master_tp`, `master_smt` | `academic_years` (`name`, `semester` `'ganjil'\|'genap'`, `is_active`) | `AcademicYear::activeSemester()` menggantikan query `master_tp` & `master_smt` aktif. |
| `master_mapel`, `master_kelompok_mapel` | `subjects` (`id`, `name`, `code`) | Langsung relasi ke `subjects.id`. |
| `master_guru`, `jabatan_guru`, `level_guru` | `teachers` (`id`, `user_id`, `nip`, `nuptk`, `staff_type`), `teacher_assignments` (`teacher_id`, `subject_id`, `school_class_id`) | Guru pembuat bank soal & pengawas diambil dari `teachers.id`. |
| `master_kelas`, `level_kelas`, `kelas_siswa`, `master_jurusan` | `school_classes` (`id`, `academic_year_id`, `name`, `level`, `homeroom_teacher_id`) | Tingkat/level kelas mengikuti `school_classes.level` & angka tingkat pada nama kelas. |
| `master_siswa`, `buku_induk` | `students` (`id`, `user_id`, `school_class_id`, `nis`, `nisn`, `gender`, `religion`, `photo`, `deleted_at`) | Kolom `students.religion` sudah tersedia untuk fitur filter `soal_agama`. |
| `setting` | `attendance_settings` (`school_name`, `level`, `logo`, `favicon`, `kop_*`, `headmaster_teacher_id`, `card_principal_*`) | Identitas sekolah & kop memakai `AttendanceSetting::first()`. |
| `cbt_jenis` | `ujian_jenis` (`id`, `kode_jenis`, `nama_jenis`, `status_aktif`) | Jenis ujian CBT merujuk ke `ujian_jenis.id`. |
| `cbt_ruang`, `cbt_kelas_ruang` | `ujian_ruang` (`id`, `kode_ruang`, `nama_ruang`, `kapasitas_maksimal`, `tipe_ruang`, `school_class_id`) | Ruang kelas (`tipe_ruang='kelas'`) & manual sudah tersedia. |
| `cbt_sesi` | `ujian_sesi` (`id`, `sesi_ke`, `nama_sesi`, `jam_mulai`, `jam_selesai`, `durasi_menit`, `is_active`) | Jam sesi aktif memakai `ujian_sesi`. |
| `cbt_nomor_peserta`, `cbt_sesi_siswa` | `ujian_peserta` (`id`, `ujian_periode_id`, `student_id`, `school_class_id`, `ujian_ruang_id`, `sesi`, `nomor_kursi`, `nomor_peserta`, `is_eligible`) | Nomor peserta, ruang, dan sesi tiap siswa per periode dibaca dari `ujian_peserta`. |
| `cbt_pengawas` | `ujian_jadwal_pengawas` (`ujian_jadwal_id`, `ujian_ruang_id`, `pengawas_1_id`, `pengawas_2_id`, status check-in) | Penugasan pengawas ruang menggunakan modul pengawas yang sudah ada. |
| `cbt_kop_kartu`, `cbt_kop_absensi`, `cbt_kop_berita` | `ujian_pengaturan_ttd` & `ujian_berita_acara` | Cetak kartu peserta, daftar hadir, dan berita acara memakai infrastruktur cetak `ujian.*` yang diperluas bila perlu. |

### B. Tabel Baru Wajib Dibuat (Prefix `cbt_*` — Online CBT Engine)
| No | Tabel CI3 | Tabel Baru Laravel | Perubahan Kunci & Normalisasi |
|---|---|---|---|
| 1 | `cbt_bank_soal` | `cbt_question_banks` | PK `id`, FK ke `academic_years`, `ujian_jenis`, `subjects`, `teachers`; `bank_kelas` (`serialize()`) → `target_class_ids` (`json`) + `tingkat` (`string`). |
| 2 | `cbt_soal` | `cbt_questions` | PK `id`, FK `bank_id` → `cbt_question_banks`; `opsi_a..e`, `jawaban`, `file` (`serialize()`) → `options` (`json`), `answer_key` (`json`), `attachments` (`json`). |
| 3 | `cbt_jadwal` | `cbt_schedules` | PK `id`, FK `bank_id` (index biasa bersama `ujian_jenis_id` & `academic_year_id`, bukan unique agar bank soal dapat dipakai ulang untuk ujian susulan/remedial, serta opsional nullable `ujian_jadwal_id`), tanpa composite ID. |
| 4 | `cbt_token` | `cbt_tokens` | PK `id`, nullable FK `ujian_periode_id`, 6-char uppercase `token`, `is_auto`, `interval_minutes`, `refreshed_at`. |
| 5 | `cbt_durasi_siswa` | `cbt_student_durations` | `id_durasi` concat → PK `id` + `unique(['cbt_schedule_id', 'student_id'])`. |
| 6 | `cbt_soal_siswa` | `cbt_student_questions` | `id_soal_siswa` concat → PK `id` + `unique(['cbt_schedule_id', 'student_id', 'cbt_question_id'])`; jawaban/alias ke `json`. |
| 7 | `cbt_nilai` | `cbt_student_scores` | `id_nilai` concat → PK `id` + `unique(['cbt_schedule_id', 'student_id'])`. |
| 8 | `log_ujian` | `cbt_exam_logs` | `id_log` concat → PK `id` + `unique(['cbt_schedule_id', 'student_id', 'log_type'])`. |
| 9 | `cbt_rekap` | `cbt_recaps` | PK `id`, FK ke `cbt_schedules` (`nullOnDelete`) / `academic_years`, `bank_kelas` & `nama_kelas` (`serialize()`) → `classes_snapshot` (`json`). |
| 10 | `cbt_rekap_nilai` | `cbt_recap_scores` | PK `id`, FK `cbt_recap_id` → `cbt_recaps`, `student_id` → `students` (`nullOnDelete`), `unique(['cbt_recap_id', 'student_id'])`. |

> **Catatan:** Modul E-Learning (`post*`, `kelas_materi`, `log_materi`, dll.) dan Rapor (`rapor_*`) **diabaikan sepenuhnya** sesuai kesepakatan cakupan Core CBT.

---

## 3. Spesifikasi Skema Database Final (`cbt_*`)

### 3.1 `cbt_question_banks`
- `id`: `bigIncrements` (PK)
- `academic_year_id`: `foreignId` → `academic_years.id` (`cascadeOnDelete`)
- `ujian_jenis_id`: `foreignId` → `ujian_jenis.id` (`restrictOnDelete`)
- `subject_id`: `foreignId` → `subjects.id` (`restrictOnDelete`)
- `teacher_id`: `foreignId` → `teachers.id` (`restrictOnDelete`) *(pembuat/pengampu bank soal)*
- `bank_code`: `string(100)` (`unique`)
- `level`: `string(30)` *(target tingkat kelas atau `'Semua Tingkat'`)*
- `target_class_ids`: `json` *(array ID `school_classes.id`, menggantikan `bank_kelas` serialized)*
- `option_count`: `unsignedTinyInteger` default `5` *(opsi `2..5` menyesuaikan jenjang)*
- `religion_filter`: `string(50)->default('-')` *(`'-'` / `'0'` = umum semua agama, atau `'Islam'`, `'Kristen'`, dll. sesuai `students.religion`)*
- `pg_total`, `pg_display`, `pg_weight`: `unsignedSmallInteger` / `decimal(5,2)` *(tipe 1)*
- `kompleks_total`, `kompleks_display`, `kompleks_weight`: `unsignedSmallInteger` / `decimal(5,2)` *(tipe 2)*
- `jodohkan_total`, `jodohkan_display`, `jodohkan_weight`: `unsignedSmallInteger` / `decimal(5,2)` *(tipe 3)*
- `isian_total`, `isian_display`, `isian_weight`: `unsignedSmallInteger` / `decimal(5,2)` *(tipe 4)*
- `esai_total`, `esai_display`, `esai_weight`: `unsignedSmallInteger` / `decimal(5,2)` *(tipe 5)*
- `kkm`: `decimal(5,2)->default(75.00)`
- `is_active`: `boolean->default(true)` *(`status`)*
- `is_questions_ready`: `boolean->default(false)` *(`status_soal`: `0=belum selesai`, `1=sudah selesai`)*
- `timestamps()`, `softDeletes()`
- **Index:** `index(['academic_year_id', 'subject_id', 'is_active'])`, `index(['teacher_id', 'is_active'])`

### 3.2 `cbt_questions`
- `id`: `bigIncrements` (PK)
- `cbt_question_bank_id`: `foreignId` → `cbt_question_banks.id` (`cascadeOnDelete`)
- `question_type`: `unsignedTinyInteger` *(`1=PG, 2=PG Kompleks, 3=Menjodohkan, 4=Isian Singkat, 5=Esai`)*
- `question_number`: `unsignedSmallInteger`
- `question_html`: `longText` *(teks soal + path gambar ter-ekstrak + KaTeX)*
- `options`: `json->nullable()` *(PG `a..e`, Kompleks map `a..z`, Menjodohkan `{model, type, rows, cols}`)*
- `answer_key`: `json->nullable()` *(PG `"A"`, Kompleks `["a","c"]`, Menjodohkan matriks 2D / pairs, Isian/Esai `["jawaban"]`)*
- `attachments`: `json->nullable()` *(menggantikan `file` serialized)*
- `difficulty`: `unsignedTinyInteger->default(5)` *(`1..10`)*
- `timer_enabled`: `boolean->default(false)`
- `timer_minutes`: `unsignedSmallInteger->default(0)`
- `is_displayed`: `boolean->default(true)` *(`tampilkan`)*
- `timestamps()`
- **Constraint & Index:** `unique(['cbt_question_bank_id', 'question_type', 'question_number'], 'uq_cbt_q_bank_type_num')`, `index(['cbt_question_bank_id', 'question_type', 'is_displayed'], 'idx_cbt_q_active')`

### 3.3 `cbt_schedules`
- `id`: `bigIncrements` (PK)
- `academic_year_id`: `foreignId` → `academic_years.id` (`cascadeOnDelete`)
- `ujian_periode_id`: `foreignId->nullable()` → `ujian_periode.id` (`nullOnDelete`) *(penghubung ke periode administrasi ujian `absensi-laravel`)*
- `ujian_jadwal_id`: `foreignId->nullable()` → `ujian_jadwal.id` (`nullOnDelete`) *(opsional tautan ke jadwal ruang/pengawas eksisting)*
- `ujian_jenis_id`: `foreignId` → `ujian_jenis.id` (`restrictOnDelete`)
- `cbt_question_bank_id`: `foreignId` → `cbt_question_banks.id` (`restrictOnDelete`)
- `start_date`: `dateTime` *(`tgl_mulai`)*
- `end_date`: `dateTime` *(`tgl_selesai`)*
- `duration_minutes`: `unsignedSmallInteger` *(`durasi_ujian`)*
- `min_finish_minutes`: `unsignedSmallInteger->default(0)` *(`jarak`: menit minimum sebelum tombol Selesai aktif)*
- `jam_ke`: `unsignedTinyInteger->default(1)` *(mengatur antrean urutan ujian harian: `MENUNGGU` jika `jam_ke` sebelumnya belum selesai)*
- `shuffle_questions`: `boolean->default(true)` *(`acak_soal`)*
- `shuffle_options`: `boolean->default(true)` *(`acak_opsi`)*
- `show_result`: `boolean->default(false)` *(`hasil_tampil`)*
- `require_token`: `boolean->default(true)` *(`token`)*
- `reset_login_lock`: `boolean->default(false)` *(`reset_login`: kunci perangkat & deteksi pindah tab)*
- `allow_repeat`: `boolean->default(false)` *(`ulang`)*
- `is_active`: `boolean->default(true)` *(`status`)*
- `is_recapped`: `boolean->default(false)` *(`rekap`)*
- `timestamps()`
- **Constraint & Index:** `index(['cbt_question_bank_id', 'ujian_jenis_id', 'academic_year_id'], 'idx_cbt_sched_bank_jenis_yr')` *(index biasa agar Bank Soal yang sama tetap dapat dipakai ulang untuk jadwal Ujian Susulan atau Remedial)*, `index(['is_active', 'start_date', 'end_date'], 'idx_cbt_sched_window')`, `index(['ujian_periode_id', 'jam_ke'], 'idx_cbt_sched_periode_jam')`

### 3.4 `cbt_tokens`
- `id`: `bigIncrements` (PK)
- `ujian_periode_id`: `foreignId->nullable()` → `ujian_periode.id` (`cascadeOnDelete`)
- `token`: `string(10)` *(6 huruf kapital `A-Z`)*
- `is_auto`: `boolean->default(false)`
- `interval_minutes`: `unsignedSmallInteger->default(15)` *(`jarak`)*
- `refreshed_at`: `timestamp`
- `timestamps()`

### 3.5 `cbt_student_durations`
- `id`: `bigIncrements` (PK)
- `cbt_schedule_id`: `foreignId` → `cbt_schedules.id` (`cascadeOnDelete`)
- `student_id`: `foreignId` → `students.id` (`cascadeOnDelete`)
- `status`: `unsignedTinyInteger->default(0)` *(`0=belum, 1=sedang, 2=selesai`)*
- `elapsed_time`: `time->default('00:00:00')` *(`lama_ujian`)*
- `elapsed_seconds`: `unsignedInteger->default(0)` *(tambahan akurat utk perhitungan timer & `jarak` tanpa parsing string terus-menerus)*
- `started_at`: `timestamp->nullable()` *(`mulai`)*
- `finished_at`: `timestamp->nullable()` *(`selesai`)*
- `reset_mode`: `unsignedTinyInteger->default(0)` *(`0=normal, 1=reset dari 0, 2=reset lanjutkan sisa waktu, 3=ulangi hapus jawaban`)*
- `timestamps()`
- **Constraint & Index:** `unique(['cbt_schedule_id', 'student_id'], 'uq_cbt_dur_sched_student')`, `index(['cbt_schedule_id', 'status'], 'idx_cbt_dur_status')`

### 3.6 `cbt_student_questions`
- `id`: `bigIncrements` (PK)
- `cbt_schedule_id`: `foreignId` → `cbt_schedules.id` (`cascadeOnDelete`)
- `student_id`: `foreignId` → `students.id` (`cascadeOnDelete`)
- `cbt_question_bank_id`: `foreignId` → `cbt_question_banks.id` (`cascadeOnDelete`)
- `cbt_question_id`: `foreignId` → `cbt_questions.id` (`cascadeOnDelete`)
- `question_type`: `unsignedTinyInteger` *(`1..5`)*
- `original_number`: `unsignedSmallInteger` *(`no_soal_asli`)*
- `alias_number`: `unsignedSmallInteger` *(`no_soal_alias` urutan tampil pada siswa)*
- `option_aliases`: `json->nullable()` *(peta `{"a":"c","b":"a",...}` untuk PG)*
- `answer_alias`: `string(10)->nullable()` *(huruf alias untuk kunci PG)*
- `answer_key`: `json->nullable()` *(snapshot kunci `jawaban_benar`)*
- `student_answer`: `json->nullable()` *(jawaban siswa `jawaban_siswa`)*
- `max_point`: `decimal(6,2)->default(0)` *(`point_soal = bobot / tampil`)*
- `corrected_score`: `decimal(6,2)->nullable()` *(`nilai_koreksi`)*
- `is_manual_graded`: `boolean->default(false)` *(`nilai_otomatis`: `false=otomatis, true=manual guru`)*
- `is_doubtful`: `boolean->default(false)` *(`ragu-ragu` / flag penanda)*
- `is_finished_flag`: `boolean->default(false)` *(`soal_end`)*
- `timestamps()`
- **Constraint & Index:**
  - `unique(['cbt_schedule_id', 'student_id', 'cbt_question_id'], 'uq_cbt_sq_sched_stu_q')`
  - `unique(['cbt_schedule_id', 'student_id', 'alias_number'], 'uq_cbt_sq_sched_stu_alias')`
  - `index(['cbt_schedule_id', 'student_id', 'question_type'], 'idx_cbt_sq_lookup')` *(mencegah full scan saat load nomor soal & auto-save)*

### 3.7 `cbt_student_scores`
- `id`: `bigIncrements` (PK)
- `cbt_schedule_id`: `foreignId` → `cbt_schedules.id` (`cascadeOnDelete`)
- `student_id`: `foreignId` → `students.id` (`cascadeOnDelete`)
- `pg_correct`: `unsignedSmallInteger->default(0)`
- `pg_score`: `decimal(6,2)->default(0)`
- `kompleks_score`: `decimal(6,2)->default(0)`
- `jodohkan_score`: `decimal(6,2)->default(0)`
- `isian_score`: `decimal(6,2)->default(0)`
- `esai_score`: `decimal(6,2)->default(0)`
- `total_score`: `decimal(6,2)->default(0)`
- `katrol_score`: `decimal(6,2)->nullable()`
- `is_corrected`: `boolean->default(false)` *(`dikoreksi`)*
- `timestamps()`
- **Constraint:** `unique(['cbt_schedule_id', 'student_id'], 'uq_cbt_score_sched_stu')`

### 3.8 `cbt_exam_logs`
- `id`: `bigIncrements` (PK)
- `cbt_schedule_id`: `foreignId` → `cbt_schedules.id` (`cascadeOnDelete`)
- `student_id`: `foreignId` → `students.id` (`cascadeOnDelete`)
- `log_type`: `unsignedTinyInteger` *(`1=Memulai Ujian`, `2=Menyelesaikan Ujian`)*
- `ip_address`: `string(45)->nullable()`
- `user_agent`: `string(255)->nullable()`
- `device`: `string(120)->nullable()`
- `is_reset`: `boolean->default(false)` *(`reset` oleh pengawas/admin)*
- `violation_reason`: `string(100)->nullable()` *(mis. `'tab_switch'` / `'illegal_device'`)*
- `logged_at`: `timestamp`
- `timestamps()`
- **Constraint:** `unique(['cbt_schedule_id', 'student_id', 'log_type'], 'uq_cbt_log_sched_stu_type')`

### 3.9 `cbt_recaps` & `cbt_recap_scores`
- **`cbt_recaps`:**
  - `id`, `cbt_schedule_id` (`nullable`, `nullOnDelete`), `academic_year_id`, `ujian_jenis_id`, `subject_id`, `teacher_id`, `bank_code`, `exam_title`, `exam_date`, `bank_config_snapshot` (`json` bobot/tampil), `classes_snapshot` (`json`), `questions_snapshot` (`json` opsional arsip butir soal), `recapped_by` (`users.id`), `timestamps()`.
- **`cbt_recap_scores`:**
  - `id`, `cbt_recap_id` (`cascadeOnDelete`), `student_id` (`nullable`, `nullOnDelete`), `school_class_name`, `student_name`, `participant_number`, `pg_correct`, `pg_score`, `kompleks_score`, `jodohkan_score`, `isian_score`, `esai_score`, `total_score`, `katrol_score`, `timestamps()`.
  - `unique(['cbt_recap_id', 'student_id'], 'uq_cbt_recap_stu')`

---

## 4. Daftar Eloquent Models (`App\Models\Cbt\*`) & Relasi

| Model (`App\Models\Cbt\`) | Tabel | Relasi Utama | `$casts` Penting |
|---|---|---|---|
| `CbtQuestionBank` | `cbt_question_banks` | `belongsTo(AcademicYear)`, `belongsTo(UjianJenis)`, `belongsTo(Subject)`, `belongsTo(Teacher)`, `hasMany(CbtQuestion)`, `hasMany(CbtSchedule)` | `target_class_ids => 'array'`, `is_active => 'boolean'`, `is_questions_ready => 'boolean'`, `*_weight => 'float'` |
| `CbtQuestion` | `cbt_questions` | `belongsTo(CbtQuestionBank)` | `options => 'array'`, `answer_key => 'array'`, `attachments => 'array'`, `timer_enabled => 'boolean'`, `is_displayed => 'boolean'` |
| `CbtSchedule` | `cbt_schedules` | `belongsTo(AcademicYear)`, `belongsTo(UjianPeriode)`, `belongsTo(UjianJadwal)`, `belongsTo(UjianJenis)`, `belongsTo(CbtQuestionBank)`, `hasMany(CbtStudentDuration)`, `hasMany(CbtStudentQuestion)`, `hasMany(CbtStudentScore)`, `hasMany(CbtExamLog)` | `start_date => 'datetime'`, `end_date => 'datetime'`, `shuffle_questions => 'boolean'`, `shuffle_options => 'boolean'`, `show_result => 'boolean'`, `require_token => 'boolean'`, `reset_login_lock => 'boolean'`, `allow_repeat => 'boolean'`, `is_active => 'boolean'`, `is_recapped => 'boolean'` |
| `CbtToken` | `cbt_tokens` | `belongsTo(UjianPeriode)` | `is_auto => 'boolean'`, `refreshed_at => 'datetime'` |
| `CbtStudentDuration` | `cbt_student_durations` | `belongsTo(CbtSchedule)`, `belongsTo(Student)` | `started_at => 'datetime'`, `finished_at => 'datetime'` |
| `CbtStudentQuestion` | `cbt_student_questions` | `belongsTo(CbtSchedule)`, `belongsTo(Student)`, `belongsTo(CbtQuestionBank)`, `belongsTo(CbtQuestion)` | `option_aliases => 'array'`, `answer_key => 'array'`, `student_answer => 'array'`, `is_manual_graded => 'boolean'`, `is_doubtful => 'boolean'`, `is_finished_flag => 'boolean'` |
| `CbtStudentScore` | `cbt_student_scores` | `belongsTo(CbtSchedule)`, `belongsTo(Student)` | `is_corrected => 'boolean'`, semua kolom `*_score => 'float'` |
| `CbtExamLog` | `cbt_exam_logs` | `belongsTo(CbtSchedule)`, `belongsTo(Student)` | `is_reset => 'boolean'`, `logged_at => 'datetime'` |
| `CbtRecap` | `cbt_recaps` | `belongsTo(CbtSchedule)`, `belongsTo(AcademicYear)`, `belongsTo(Subject)`, `belongsTo(Teacher)`, `hasMany(CbtRecapScore)` | `exam_date => 'date'`, `bank_config_snapshot => 'array'`, `classes_snapshot => 'array'`, `questions_snapshot => 'array'` |
| `CbtRecapScore` | `cbt_recap_scores` | `belongsTo(CbtRecap)`, `belongsTo(Student)` | semua kolom `*_score => 'float'` |

*Extension kecil pada model induk (opsional non-breaking):* Menambahkan helper method/relasi baca (`cbtQuestionBanks()`, `cbtScores()`, `cbtDurations()`) pada `Teacher` dan `Student` atau mengakses via scope service agar model induk tetap ramping.

---

## 5. Rancangan Integrasi Auth, Role, Lockout, & Policy

1. **Login Admin, Guru, & Siswa (Reuse `AuthController`):**
   - Admin & Guru login lewat `/login` menggunakan Universal Login (`email / NIP / NUPTK / NIS / NISN` + `password` bcrypt) milik `absensi-laravel`.
   - Penambahan **Rate Limiter Lockout 3x / 600s** pada `AuthController::login()` menggunakan key `cbt_login:{identifier}` (tanpa IP, dinormalisasi huruf kecil; `RateLimiter::tooManyAttempts($key, 3)`, `RateLimiter::hit($key, 600)`), ditambah endpoint `POST /cbt/users/unlock` untuk `admin` dan `panitia ujian` agar bisa langsung menghapus lockout (`RateLimiter::clear`) berdasarkan identifier siswa/guru saat hari ujian tanpa perlu tahu IP perangkatnya.
   - Penambahan opsional toggle **Mode CBT-Only** di `/login`: bila dicentang oleh siswa (atau via URL `/cbt/login` alias ke form login), setelah login role `siswa` langsung diarahkan ke `route('cbt.siswa.index')` alih-alih `siswa.dashboard`, dan navigasi siswa tetap punya tombol **Portal Ujian CBT** pada tab Beranda (`siswa/_tab-beranda.blade.php`).
2. **Pemetaan Role & Middleware `routes/cbt.php`:**
   - **Panel Manajemen CBT (`/cbt/manage/*`):** Middleware `['auth', EnsurePanitiaUjianOrGuruCbt, EnsureKepalaMonitoring]`
     - `admin` & panitia (`ujian_panitia` aktif) → akses penuh jadwal, token, monitoring status, reset login/timer, rekap, dan seluruh bank soal.
     - `guru` → akses Bank Soal miliknya (`CbtQuestionBankPolicy`), Monitoring Status/Reset & Penilaian/Analisis khusus jadwal dari bank soal miliknya atau ruang yang dia awasi di `ujian_jadwal_pengawas`.
     - `kepala` → mode monitoring read-only otomatis lewat `EnsureKepalaMonitoring` (ditambah daftar prefix `cbt.*` di pengecualian mutasi).
   - **Portal Ujian Siswa (`/cbt/siswa/*`):** Middleware `['auth', 'role:siswa']` + verifikasi status `students` tidak di-soft-delete (`!is_null($user->student)`) dan peserta `ujian_peserta.is_eligible = true` untuk periode ujian terkait.
3. **`CbtQuestionBankPolicy`:**
   - `viewAny`: `admin`, `kepala`, `guru` (termasuk panitia).
   - `view`, `update`, `delete`: `true` jika `$user->isAdmin()`, atau `$user->isPanitiaUjian()` *(sesuai kebijakan sekolah: hanya baca atau kelola)*, atau `$user->teacher?->id === $bank->teacher_id`.
   - Menutup celah CI3 di mana `Cbtbanksoal` & `Siswa` hanya mengecek `logged_in()` di `__construct` tanpa verifikasi role/kepemilikan di level controller.
