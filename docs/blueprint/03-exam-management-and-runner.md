# Blueprint 03 — Manajemen Ujian & Exam Runner Siswa (Fase 3B)

> **Controller Target:**
> - Manajemen (`App\Http\Controllers\Cbt\`): `JadwalCbtController`, `TokenController`, `MonitoringStatusController`, `AlokasiWaktuController`
> - Siswa (`App\Http\Controllers\Cbt\`): `SiswaExamController`
> **Service Target:** `App\Services\Cbt\ExamEligibilityService`, `App\Services\Cbt\ExamQueueService`, `App\Services\Cbt\TokenService`, `App\Services\Cbt\ExamTimerService`
> **Frontend Runner:** Blade (`resources/views/cbt/siswa/*.blade.php`) + Tailwind CSS v4 (Neobrutalism) + Vanilla JS (Fetch API + Fullscreen API + KaTeX lokal).

---

## 1. Integrasi Modul Ujian Eksisting `/var/www/absensi-laravel` vs Modul CBT

Karena `/var/www/absensi-laravel` **sudah memiliki** modul administrasi ujian fisik/umum (`routes/ujian.php`), modul CBT tidak membuat ulang master ruang/sesi/peserta/pengawas, melainkan **menyambung langsung** sebagai layer pelaksanaan ujian berbasis komputer:

| Kebutuhan di GarudaCBT CI3 | Sumber di `absensi-laravel` (REUSE) | Komponen Baru CBT (`cbt_*`) |
|---|---|---|
| **Jenis Ujian** (`Cbtjenis`) | `ujian_jenis` (`UjianJenis`) + `ujian_periode` (`UjianPeriode`) | `cbt_question_banks.ujian_jenis_id`, `cbt_schedules.ujian_jenis_id`, `cbt_schedules.ujian_periode_id` |
| **Ruang & Sesi** (`Cbtruang`, `Cbtsesi`) | `ujian_ruang` (`UjianRuang`) & `ujian_sesi` (`UjianSesi` — `jam_mulai`, `jam_selesai`) | Siswa CBT divalidasi terhadap slot `ujian_peserta.sesi` ↔ `ujian_sesi` |
| **Nomor Peserta & Penempatan Ruang/Sesi** (`Cbtnomorpeserta`, `Cbtsesisiswa`) | `ujian_peserta` (`PesertaKartuUjianController`, `NomorPesertaGeneratorService`) | Dibaca dari `UjianPeserta::where('ujian_periode_id', ...)->where('student_id', ...)` *(fallback jika jadwal tidak terikat periode: kelas siswa aktif)* |
| **Penugasan Pengawas** (`Cbtpengawas`) | `ujian_jadwal` & `ujian_jadwal_pengawas` (`JadwalPengawasController`) | Pengawas yang bertugas pada ruang & jadwal aktif dapat mengakses Monitoring Status & Reset Login siswa di ruang tersebut |
| **Jadwal Pelaksanaan CBT, Alokasi Jam, & Opsi Ujian** (`Cbtjadwal`, `Cbtalokasi`) | Opsional terhubung ke `ujian_jadwal.id` | **`cbt_schedules`** (`tgl_mulai`, `tgl_selesai`, `durasi_ujian`, `min_finish_minutes` / `jarak`, `jam_ke`, `acak_soal`, `acak_opsi`, `hasil_tampil`, `require_token`, `reset_login_lock`) |
| **Token Ujian** (`Cbttoken`) | *(Baru)* | **`cbt_tokens`** + endpoint generate token 6 huruf kapital `A-Z` & mode `is_auto` berdasarkan `interval_minutes` |
| **Status & Reset Peserta** (`Cbtstatus`) | *(Baru)* | `MonitoringStatusController` membaca `cbt_student_durations` & `cbt_exam_logs` |

### Alur Token (`TokenService`)
- Format token: 6 karakter huruf kapital acak (`A-Z`, mengecualikan karakter ambigu bila perlu atau `[A-Z]{6}` identik dengan GarudaCBT).
- **Mode Manual:** Admin/Panitia klik tombol *Generate Token* → memperbarui `cbt_tokens.token` & `refreshed_at = now()`.
- **Mode Auto-Refresh:** Bila `is_auto = true`, setiap pengecekan token (`getValidToken()`) atau scheduler/AJAX polling membandingkan `now()->diffInMinutes($token->refreshed_at) >= $token->interval_minutes`; jika lewat, sistem melakukan *atomic regenerate* dengan `Cache::lock('cbt_token_refresh', 5)` agar tidak terjadi race condition token ganda saat ratusan siswa login bersamaan.

---

## 2. State Machine Status Ujian Siswa & Filter `soal_agama`

### A. Filter Jadwal per Siswa (`ExamEligibilityService`)
Saat siswa membuka `GET /cbt/siswa` (`cbt.siswa.index`), jadwal `cbt_schedules` (`is_active = true`, `academic_year_id = aktif`) difilter dengan aturan berurutan:
1. `in_array($student->school_class_id, $bank->target_class_ids)`.
2. **Kelayakan Peserta (`ujian_peserta`):** Jika jadwal terikat `ujian_periode_id`, siswa harus tercatat pada `ujian_peserta` dengan `is_eligible = true` (sekaligus mengambil `nomor_peserta`, `ujian_ruang`, dan `sesi`).
3. **Filter Agama (`soal_agama` / `religion_filter`):**
   - Jika `religion_filter` bernilai `'-'`, `'0'`, atau kosong → tampil untuk semua siswa.
   - Selain itu → tampil hanya jika `mb_strtolower(trim($student->religion)) === mb_strtolower(trim($bank->religion_filter))` *(kolom `students.religion` sudah tersedia di `absensi-laravel`)*.

### B. State Machine Status Tombol Ujian (`cbt/data.php` Parity)
Urutan evaluasi status per jadwal (`ExamEligibilityService::resolveButtonState()`):
1. Jika sudah ada `cbt_student_durations` dengan `status == 2` → **`SUDAH SELESAI`** *(tampilkan tombol Lihat Hasil bila `show_result = true`)*.
2. Jika `now()->lt($schedule->start_date)` → **`BELUM DIMULAI`**.
3. Jika `now()->gt($schedule->end_date)` dan belum pernah mulai (`status == 0` / belum ada record) → **`SUDAH BERAKHIR`**.
4. Cek jam sesi siswa dari `ujian_sesi` (berdasarkan `ujian_peserta.sesi`):
   - Jika waktu sekarang `< jam_mulai` sesi → **`SESI BELUM DIMULAI`**.
   - Jika waktu sekarang `> jam_selesai` sesi dan belum selesai → **`SESI SUDAH BERAKHIR`**.
5. Cek urutan `jam_ke` pada tanggal/periode yang sama:
   - Jika terdapat jadwal aktif lain untuk siswa pada hari yang sama dengan `jam_ke < $schedule->jam_ke` yang `status != 2` → **`MENUNGGU`** *(menunggu jam ujian sebelumnya selesai)*.
6. Jika `cbt_student_durations.status == 1` → **`LANJUTKAN`**.
7. Jika belum mulai dan memenuhi seluruh syarat waktu & sesi → **`KERJAKAN`**.

---

## 3. Logika Pengacakan & Pembuatan Antrean Soal (`ExamQueueService::createQueue`)

Saat siswa lolos validasi masuk (`POST /cbt/siswa/ujian/{schedule}/validate`) untuk pertama kali (atau setelah reset mode `3 = ulangi semua`), proses dijalankan dalam **`DB::transaction()`** + **`lockForUpdate()`** pada baris `cbt_student_durations`:

1. Ambil semua soal aktif (`is_displayed = true`) dari `cbt_question_bank_id`, dikelompokkan per `question_type` (`1..5`).
2. Batasi jumlah per tipe sesuai kuota `pg_display`, `kompleks_display`, `jodohkan_display`, `isian_display`, `esai_display`.
3. **Acak Soal (`shuffle_questions = true`):** Acak koleksi soal per tipe (atau sesuai urutan tipe 1→2→3→4→5 seperti `Siswa::createQueueNumber`), lalu beri nomor urut tampil `alias_number = 1..total`.
4. **Acak Opsi PG (`shuffle_options = true` & `question_type = 1`):**
   - Ambil key opsi sesuai `option_count` (mis. `['a','b','c','d']`).
   - Acak urutan huruf sumber ke slot tampilan `a, b, c, d`, lalu simpan peta ke `option_aliases` (`{"a":"c","b":"a","c":"d","d":"b"}`).
   - Hitung `answer_alias`: huruf tampilan yang memetakan ke kunci asli `answer_key['correct']` (mis. jika kunci asli `C` dan slot `a` menunjuk ke `c`, maka `answer_alias = 'A'`).
5. **Hitung Bobot Per Butir (`max_point`):**
   - Untuk setiap tipe soal $t$ dengan kuota $display_t > 0$: $\text{max\_point} = \frac{\text{weight}_t}{\text{display}_t}$.
6. **Batch Insert:** Simpan semua baris sekaligus via `CbtStudentQuestion::insert(...)` dan buat/update `cbt_student_durations` (`status = 1`, `started_at = now()`, `elapsed_seconds = 0`, `reset_mode = 0`).

---

## 4. Kontrak Endpoint AJAX & Mekanisme Exam Runner

Halaman konfirmasi (`GET /cbt/siswa/ujian/{schedule}/konfirmasi`) menampilkan identitas peserta, info mapel/durasi/pengawas, serta input Token (jika `require_token = true`). Setelah validasi sukses, redirect ke Exam Runner (`GET /cbt/siswa/ujian/{schedule}/kerjakan`).

### Kontrak API Ringan (JSON, CSRF `X-CSRF-TOKEN`, Rate-Limited)

| Method & URI | Controller Action | Payload Request | Response JSON & Perilaku |
|---|---|---|---|
| `POST /cbt/siswa/ujian/{schedule}/validate` | `validasiSiswa` | `{ token? }` | Validasi token (`cbt_tokens`), cek UA (`unknown` ditolak), cek device lock (`cbt_exam_logs`), cek sisa waktu, panggil `createQueue` bila belum ada. Return `{ ok, redirect_url }`. |
| `GET /cbt/siswa/ujian/{schedule}/question/{aliasNumber}` | `loadQuestion` | *(query: none)* | Mengembalikan detail soal bernomor `aliasNumber` (TANPA mengirim `answer_key` ke client!), peta `option_aliases`, jawaban tersimpan `student_answer`, daftar status terisi semua nomor (`modal_grid`), serta hasil **`checkTimer`**: `{ remaining_seconds, elapsed_seconds, min_finish_seconds, is_time_up }`. |
| `POST /cbt/siswa/ujian/{schedule}/answer` | `saveAnswer` | `{ student_question_id, answer, is_doubtful?, client_elapsed_seconds? }` | Menyimpan `student_answer` (sesuai format tipe 1–5) & `is_doubtful` ke `cbt_student_questions`, memperbarui `elapsed_seconds` & `elapsed_time` di `cbt_student_durations` berdasarkan selisih waktu server (`started_at` atau akumulasi tervalidasi). |
| `POST /cbt/siswa/ujian/{schedule}/leave` | `leaveExam` | `{ reason: 'tab_switch' }` | Aktif hanya jika `reset_login_lock = true`. Menandai `cbt_exam_logs` (`log_type=1`): `user_agent = 'illegal agent'`, `device = 'illegal device'`, `violation_reason = 'tab_switch'`, `is_reset = false`. |
| `POST /cbt/siswa/ujian/{schedule}/finish` | `finishExam` | `{}` | Cek `elapsed_seconds >= min_finish_minutes * 60` (kecuali `is_time_up = true`). Set `status = 2`, `finished_at = now()`, catat `log_type = 2`, jalankan `ScoringService::calculateForStudent()`, return `{ ok, redirect_url }`. |

### Fitur UI Exam Runner (Blade + Tailwind CSS v4 + Vanilla JS)
- **Header Ringkas Bebas Distraksi:** Menampilkan Nama Siswa, Mapel, Zoom font slider (`A- / A+`), Countdown Timer real-time (`HH:MM:SS` hasil selisih server `remaining_seconds`), dan tombol Daftar Soal (grid nomor).
- **Render 5 Tipe Soal:**
  1. PG: radio button A–E sesuai `option_aliases`.
  2. PG Kompleks: checkbox multi-pilihan (tanpa membeberkan jumlah kunci benar).
  3. Menjodohkan: komponen Vanilla JS modern setara `linker-list.js` yang mendukung `model = '1'` (SVG line connector responsif) dan `model = '2'` (tabel radio/checkbox).
  4. Isian Singkat: input teks bebas XSS.
  5. Esai: textarea / editor ringan + render KaTeX.
- **Auto-Save & UX:** Jawaban otomatis tersimpan saat berpindah nomor (Next / Prev / pilih nomor di modal) **dan** *debounced auto-save* (mis. 800ms setelah siswa mengetik isian/esai) dengan indikator status *"Tersimpan"* / *"Menyimpan..."* serta retry otomatis jika koneksi sesaat putus.
- **Aturan `min_finish_minutes` (`jarak`):** Tombol *Selesai Ujian* terkunci/disabled hingga timer melewati `min_finish_minutes` (menit) untuk mencegah siswa asal klik selesai terlalu cepat.

---

## 5. Mekanisme Anti-Cheat & Panel Monitoring Pengawas (`MonitoringStatusController`)

### A. Anti-Cheat Client & Server
1. **Device & Session Lock (`reset_login_lock = true`):**
   - Saat siswa masuk pertama kali (`validasiSiswa`), `cbt_exam_logs` (`log_type = 1`) merekam `ip_address`, `user_agent`, dan fingerprint `device`.
   - Jika siswa mencoba membuka ujian dari perangkat/browser lain sementara `is_reset = false`, sistem **menolak** dan meminta siswa menghubungi pengawas ruang untuk di-reset.
2. **Deteksi Pindah Tab / Kehilangan Fokus (`visibilitychange`):**
   - Pada Exam Runner, ketika `document.hidden === true` dan `reset_login_lock = true`, client langsung mengirim `navigator.sendBeacon` / `fetch(..., { keepalive: true })` ke `POST .../leave`, mencatat `'illegal agent'` / `'illegal device'` pada `cbt_exam_logs`, lalu me-lock sesi ujian siswa hingga pengawas mengizinkan kembali.
3. **Hardening Browser Ringan:** Blokir klik kanan pada area soal, shortcut `Escape`, print screen / `Ctrl+P`, serta tombol ajakan **Mode Layar Penuh (Fullscreen API)** saat ujian dimulai.

### B. Panel Monitoring & Aksi Pengawas/Panitia (`/cbt/manage/monitoring`)
Filter per Jadwal, Ruang (`ujian_ruang`), Sesi (`ujian_sesi`), dan Kelas (`school_classes`), dengan status real-time (`BELUM`, `SEDANG`, `SELESAI`, `TERKUNCI/PELANGGARAN`):
1. **Reset Login (`resetLogin`):** Set `cbt_exam_logs.is_reset = true`, `user_agent = null`, `device = null`, `violation_reason = null` sehingga siswa dapat login ulang tanpa menghapus jawaban.
2. **Reset Timer Mode 1 (`reset_mode = 1` — Reset dari 0):** Set `status = 1`, `started_at = now()`, `elapsed_seconds = 0` (jawaban tetap ada).
3. **Reset Timer Mode 2 (`reset_mode = 2` — Lanjutkan Sisa Waktu):** Hitung ulang `started_at = now()->subSeconds($duration->elapsed_seconds)` dengan mengambil nilai `elapsed_seconds` terakhir yang tersimpan pada aktivitas/auto-save terakhir siswa (bukan menghitung waktu idle selama siswa terputus atau terkunci).
4. **Ulangi Ujian Mode 3 (`reset_mode = 3` — Hapus Jawaban & Acak Ulang):** Hapus `cbt_student_questions`, `cbt_student_scores`, reset `cbt_student_durations` ke `status = 0`, sehingga siswa mengulang dari awal.
5. **Paksa Selesai (`forceFinish`):** Menutup paksa ujian siswa (`status = 2`), merekam `log_type = 2`, dan menghitung nilai dari jawaban terakhir. *(Dukungan aksi massal checkbox seperti `Cbtstatus::applyAction` di CI3.)*
