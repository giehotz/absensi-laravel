# Blueprint 05 — Verifikasi Paritas Logika, Pengujian, Performa, & Keamanan (Fase 4)

> **Cakupan Verifikasi:**
> 1. Paritas logika bisnis terhadap GarudaCBT (`/var/www/cbt`).
> 2. Integritas & non-regresi terhadap modul eksisting (`/var/www/absensi-laravel`).
> 3. Suite PHPUnit (`phpunit ^12.5`), ketahanan performa ujian serentak, dan audit keamanan akses.

---

## 1. Checklist Audit Paritas Logika & Kompatibilitas Induk

### A. Paritas terhadap Source Code CI3 (`/var/www/cbt`)
| Area Logika | Acuan di `/var/www/cbt` | Kriteria Lulus di Laravel |
|---|---|---|
| **Skema & Serialisasi** | `assets/app/db/master.sql` | 0 composite concat PK (`id_siswa . '0' . id_jadwal` dihapus → PK `id` + unique constraint); 0 kolom `serialize()` (semua memakai `json` + `$casts`). |
| **5 Tipe Soal & Media** | `Cbtbanksoal`, `Cbt_model::saveBankSoal` | PG, PG Kompleks, Menjodohkan (`model` 1/2, `type` 1/2), Isian Singkat, Esai tersimpan rapi; gambar base64 terekstrak ke disk `public`; import Excel & `.docx` format AKM berjalan. |
| **Kelayakan & Urutan Jadwal** | `Siswa::cbt`, `cbt/data.php` | Status tombol identik (`BELUM DIMULAI`, `SUDAH BERAKHIR`, `SESI BELUM DIMULAI`, `SESI SUDAH BERAKHIR`, `MENUNGGU` bila `jam_ke` sebelumnya belum selesai, `KERJAKAN`, `LANJUTKAN`, `SUDAH SELESAI`); filter `religion_filter` (`soal_agama`) cocok dgn `students.religion`. |
| **Pengacakan & Kunci Alias** | `Siswa::createQueueNumber` | Soal diacak per tipe jika `shuffle_questions = true`; opsi PG diacak menjadi `option_aliases` & `answer_alias` jika `shuffle_options = true`; `max_point = weight / display`. |
| **Token, Timer, & Anti-Cheat** | `Cbttoken`, `Siswa::validasiSiswa`, `loadNomorSoal`, `leavecbt`, `Cbtstatus` | Token 6 huruf kapital + auto-refresh interval; UA `unknown` ditolak; device lock `reset_login_lock = true` + `visibilitychange` → log pelanggaran; `min_finish_minutes` (`jarak`) menahan tombol selesai; reset mode `1, 2, 3` & force finish bekerja persis. |
| **Penilaian, Katrol, & Analisis** | `Siswa::olahNilai`, `Cbtnilai`, `Cbtanalisis`, `Cbtrekap` | Rumus 5 tipe soal (termasuk parsial PG Kompleks & Menjodohkan), override koreksi guru (`is_manual_graded`), katrol linear `((ya-yb)/100)*total + yb`, Pearson $r$ + $r_{\text{tabel}}$, $P$, $D$, dan snapshot `cbt_recaps` akurat. |

### B. Kompatibilitas Non-Regresi terhadap `/var/www/absensi-laravel`
- Tidak mengubah skema atau memecah fungsionalitas tabel induk (`users`, `students`, `teachers`, `school_classes`, `subjects`, `academic_years`, `attendance_settings`, `ujian_*`).
- Seluruh test suite eksisting `/var/www/absensi-laravel` (`php artisan test`) tetap hijau **100% tanpa modifikasi atau penurunan asersi**.
- Middleware `EnsureRole`, `EnsurePanitiaUjian`, dan `EnsureKepalaMonitoring` tetap berjalan normal pada rute non-CBT dan juga melindungi rute `cbt.*` (`kepala` tetap strictly read-only).
- Tampilan halaman siswa CBT mengikuti layout Neobrutalism (`layouts/neobrutalism` / tema konsisten), dan tampilan manajemen mengikuti `layouts/ujian` / `layouts/admin` / `layouts/guru`.

---

## 2. Rencana Automated Tests (`tests/Feature/Cbt/*` & `tests/Unit/Cbt/*`)

Menggunakan **PHPUnit 12** (`php artisan test`) dengan database test terisolasi (`RefreshDatabase`):

### A. Unit Tests (`tests/Unit/Cbt/`)
1. **`ScoringServiceTest`:**
   - PG tunggal: benar sebagian → skor = `(benar / tampil) * bobot`, edge case `tampil = 0` tidak division-by-zero.
   - PG Kompleks: skor parsial berpenalti `max(0, (benar - salah) / jumlah_kunci)` (memastikan centang semua opsi tidak menghasilkan skor penuh), plus override manual `corrected_score` (`is_manual_graded = true`).
   - Menjodohkan: akurasi matriks baris-kolom berpenalti `max(0, (item_benar - item_salah) / items)` untuk mode 1-1 dan 1-banyak.
   - Isian Singkat & Esai: case-insensitive trim match + override koreksi guru.
   - Gabungan 5 tipe soal total tepat 100.00 saat semua benar.
2. **`KatrolAndItemAnalysisTest`:**
   - Rumus katrol `round((($ya - $yb) / 100) * $total + $yb, 2)` untuk nilai min/maks aktual vs target.
   - Korelasi Pearson $r$ (termasuk kasus varians 0 mengembalikan `-1`), tingkat kesukaran $P$, dan daya pembeda $D$.
3. **`QuestionMediaServiceTest`:**
   - Ekstraksi `data:image/png;base64,...` menjadi file di `Storage::fake('public')` & sanitasi tag `<script>`.

### B. Feature Tests (`tests/Feature/Cbt/`)
1. **`CbtAuthAndPolicyTest`:**
   - Rate limiter lockout setelah 3 percobaan login salah (terblokir selama 600s) & bisa di-unlock oleh admin.
   - Guru A tidak dapat melihat/mengubah/menghapus Bank Soal milik Guru B (`403 Forbidden`); Admin & Panitia Ujian dapat mengakses sesuai otorisasi; Kepala hanya dapat `GET` (ditolak `403` pada `POST/PUT/DELETE`).
   - Siswa tidak dapat mengakses endpoint `/cbt/manage/*`.
2. **`ExamQueueAndValidationTest`:**
   - Token salah ditolak; token benar (termasuk setelah auto-refresh) diterima.
   - Filter `religion_filter` hanya menampilkan soal/jadwal ke siswa beragama sesuai.
   - Antrean `jam_ke` menampilkan status `MENUNGGU` bila jadwal jam ke-1 belum berstatus selesai.
   - `ExamQueueService` menghasilkan `cbt_student_questions` idempoten (permintaan ganda bersamaan tidak menggandakan baris soal siswa berkat transaksi + unique constraint) dan memetakan `option_aliases` serta `answer_alias` dengan benar.
3. **`ExamRunnerAutoSaveAndAntiCheatTest`:**
   - Endpoint `loadQuestion` **tidak pernah** membocorkan `answer_key` / kunci jawaban ke JSON response siswa.
   - `saveAnswer` memperbarui `student_answer` untuk 5 tipe soal + mencatat `elapsed_seconds`.
   - `leaveExam` saat `reset_login_lock = true` mengunci akses login ulang dari sesi/perangkat berbeda sampai pengawas menjalankan `resetLogin`.
   - `finishExam` menolak submit sebelum `elapsed_seconds >= min_finish_minutes * 60` (kecuali waktu habis), lalu menghitung skor akhir.
   - Reset timer mode `1` (dari 0, jawaban aman), mode `2` (lanjutkan sisa waktu), mode `3` (hapus jawaban & acak ulang), dan `forceFinish` bekerja sesuai spesifikasi.
4. **`RecapAndExportTest`:**
   - Snapshot ke `cbt_recaps` & `cbt_recap_scores` menyimpan data meskipun jadwal/bank soal asli diubah sesudahnya.
   - Endpoint export Excel menghasilkan status `200` dengan `Content-Type` `.xlsx` valid.

---

## 3. Checklist Performa & Keamanan Produksi (Ujian Serentak)

### A. Kesiapan Performa (Prevent N+1 & Concurrency)
- [ ] **No N+1 Queries:** Gunakan `with([...])` pada halaman daftar jadwal siswa, monitoring status (`student.user`, `student.schoolClass`, `peserta`), dan rekap/koreksi nilai.
- [ ] **Composite Index Wajib:** Pastikan index `(cbt_schedule_id, student_id, question_type)` pada `cbt_student_questions` dan `(cbt_schedule_id, status)` pada `cbt_student_durations` terpasang.
- [ ] **Payload Soal Ringan:** `loadQuestion` memuat **1 butir soal per request** (bukan seluruh soal + gambar sekaligus) sehingga ringan di jaringan sekolah padat.
- [ ] **Atomic Locks & DB Transactions:** `ExamQueueService::createQueue` memakai `DB::transaction` + `lockForUpdate()`, dan `TokenService` auto-refresh memakai `Cache::lock` untuk menghindari race condition saat siswa login bersamaan.
- [ ] **Batasi Query Session:** Simpan data jadwal/konfigurasi statis ringan atau query hanya kolom yang dibutuhkan (`select([...])`) saat polling monitoring & auto-save.

### B. Kesiapan Keamanan
- [ ] **Zero Answer Leakage:** Gunakan Laravel API Resource / explicit field mapping pada controller siswa agar `answer_key`, `answer_alias`, dan `max_point` tidak pernah terkirim ke browser siswa selama ujian berlangsung.
- [ ] **Server-Side Timer Authority:** Hitung sisa waktu di server (`started_at + duration_minutes`), jangan percaya timestamp/sisa waktu yang dikirim client.
- [ ] **XSS & Upload Hardening:** Sanitasi HTML soal (blokir `<script>`, `javascript:`, inline `on*` event handlers), validasi ketat ekstensi & MIME attachment, simpan media di disk `public` dengan nama file acak.
- [ ] **CSRF & Ownership Verification:** Seluruh endpoint mutasi wajib CSRF (`@csrf` / `X-CSRF-TOKEN`) + verifikasi bahwa `cbt_student_questions.student_id === Auth::user()->student->id` agar siswa tidak bisa memanipulasi ID milik siswa lain (IDOR protection).
- [ ] **Monitoring Headmaster Lock:** Pastikan semua rute `cbt.*` tercover pembatasan read-only pada `EnsureKepalaMonitoring`.
