# Blueprint Teknis Integrasi CBT → Laravel (`/var/www/absensi-laravel`)

> **Status:** Rancangan Teknis Lengkap Siap Review (Belum ada kode/migration/artisan yang dijalankan)
> **Sumber Referensi (Read-Only):**
> 1. `/var/www/cbt` — GarudaCBT (CodeIgniter 3)
> 2. `/var/www/absensi-laravel` — Proyek Induk Laravel 13 + Tailwind CSS v4 (Neobrutalism)
> **Arsitektur:** Modular Monolith (`App\Http\Controllers\Cbt\*`, `App\Models\Cbt\*`, `App\Services\Cbt\*`, `routes/cbt.php`, prefix tabel `cbt_*`), fokus **Core CBT** saja (E-Learning & Rapor diabaikan).

---

## Daftar Dokumen Blueprint

| Dokumen | Fase | Isi Utama |
|---|---|---|
| [`01-database-and-auth.md`](./01-database-and-auth.md) | **Fase 2** | Hasil audit `/var/www/absensi-laravel`, matriks tabel reuse vs baru (`cbt_*`), skema DB final (auto-increment PK + unique constraint + `json`), Eloquent Models, Auth/RateLimiter/Policy |
| [`02-question-bank.md`](./02-question-bank.md) | **Fase 3A** | Integrasi Bank Soal dengan `subjects`/`teachers`/`school_classes`, detail JSON 5 tipe soal (PG, PG Kompleks, Menjodohkan, Isian, Esai), ekstraksi base64 & KaTeX, import Excel/Word |
| [`03-exam-management-and-runner.md`](./03-exam-management-and-runner.md) | **Fase 3B** | Penghubung ke modul `ujian_*` eksisting, manajemen Token/Jadwal CBT, Exam Runner Siswa (state machine tombol, `soal_agama`, acak soal/opsi, Fetch auto-save, timer server, anti-cheat & panel reset pengawas) |
| [`04-scoring-analytics-and-print.md`](./04-scoring-analytics-and-print.md) | **Fase 3C** | `ScoringService` 5 tipe soal + koreksi manual, Katrol linear, Analisis Butir Soal (Pearson $r$, $P$, $D$), Snapshot `cbt_recaps`, integrasi `<x-print-preview-layout>` & export `PhpSpreadsheet` |
| [`05-verification-and-testing.md`](./05-verification-and-testing.md) | **Fase 4** | Checklist paritas logika CI3, non-regresi `absensi-laravel`, rencana PHPUnit Unit & Feature tests, serta checklist performa & keamanan ujian serentak |

---

## Urutan Checklist Eksekusi (Fase 2 → Fase 4)

### Fase 2 — Fondasi & Auth (mengacu `01-database-and-auth.md`)
- [x] Pastikan target workspace/branch integrasi pada `/var/www/absensi-laravel` (branch `feat/cbt-module`).
- [x] Buat Migrations tabel `cbt_question_banks`, `cbt_questions`, `cbt_schedules`, `cbt_tokens`, `cbt_student_durations`, `cbt_student_questions`, `cbt_student_scores`, `cbt_exam_logs`, `cbt_recaps`, `cbt_recap_scores`.
- [x] Buat Models `App\Models\Cbt\*` beserta relasi ke `AcademicYear`, `Subject`, `Teacher`, `SchoolClass`, `Student`, `UjianJenis`, `UjianPeriode`, `UjianJadwal`.
- [x] Implementasikan `CbtQuestionBankPolicy`, middleware akses CBT, rate limiter login `3x / 600s` + fitur unlock di panel admin/panitia, dan registrasi `routes/cbt.php`.

### Fase 3A — Bank Soal & 5 Tipe Soal (mengacu `02-question-bank.md`)
- [ ] Buat `QuestionMediaService` (ekstraksi gambar base64 + sanitasi HTML) dan `QuestionBankService`.
- [ ] Bangun `BankSoalController` & `QuestionController` beserta view Blade Neobrutalism untuk editor 5 tipe soal + KaTeX lokal.
- [ ] Implementasikan `QuestionImportService` (Template `.xlsx` via `PhpSpreadsheet` + parser `.docx` kompatibel `format_soal_akm.docx`).

### Fase 3B — Manajemen Ujian & Exam Runner (mengacu `03-exam-management-and-runner.md`)
- [ ] Bangun `JadwalCbtController`, `TokenController` (`TokenService`), dan `MonitoringStatusController` (reset login, reset timer mode 1/2/3, force finish).
- [ ] Bangun `ExamEligibilityService` (state machine tombol, sesi `ujian_peserta`, `jam_ke`, `religion_filter`) & `ExamQueueService` (`createQueue` atomic + acak soal/opsi).
- [ ] Bangun `SiswaExamController` + halaman UI Exam Runner (`ujian.blade.php` versi Tailwind v4 Neobrutalism + `linker-list` JS modern + Fetch auto-save + server timer sync + `visibilitychange` anti-cheat).

### Fase 3C — Penilaian, Analisis, Rekap, & Cetak (mengacu `04-scoring-analytics-and-print.md`)
- [ ] Bangun `ScoringService`, `KoreksiController`, `KatrolService`, `ItemAnalysisService`, dan `RecapService`.
- [ ] Bangun `ExcelExportService` (`PhpSpreadsheet`) dan view cetak `<x-print-preview-layout>` yang tersambung ke `ujian_pengaturan_ttd`.

### Fase 4 — Verifikasi & Uji Coba (mengacu `05-verification-and-testing.md`)
- [ ] Tulis dan jalankan seluruh PHPUnit Unit & Feature tests untuk modul CBT + verifikasi seluruh test eksisting `absensi-laravel` tetap hijau.
- [ ] Audit final IDOR, kebocoran kunci jawaban, `EnsureKepalaMonitoring`, dan query N+1 sebelum dinyatakan siap produksi.
