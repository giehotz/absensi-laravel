# MIGRATION PLAN: GarudaCBT (CodeIgniter 3) → Laravel

> **Status:** Fase 1 — Audit Source Code (Selesai)
> **Sumber (Read-Only):** `/var/www/cbt`
> **Target Workspace:** `/var/www/cbtrewirite`
> **Metode:** Analisis statis source code CI3 (`application/` + `assets/app/db/master.sql` + `composer.json`), tanpa eksekusi aplikasi/URL sumber.

---

## 1. Ringkasan Arsitektur Sumber (`/var/www/cbt`)

| Komponen | Kondisi di CodeIgniter 3 (`/var/www/cbt`) | Rencana di Laravel (`/var/www/cbtrewirite`) |
|---|---|---|
| Framework & PHP | CodeIgniter 3, `composer.json` mensyaratkan PHP `>= 7.3` | Laravel (modern PHP 8.2+) + Blade/Tailwind CSS *(atau Inertia + Vue 3 sesuai konfirmasi)* |
| Routing | `application/config/routes.php`: default `auth`, `login => Auth/index`, `logout => Auth/logout`, `404_override => auth`, `translate_uri_dashes => FALSE`; sisanya auto-routing CI3 (`Controller/method/params`) | Definisi route eksplisit di `routes/web.php` dengan prefix & middleware role (`auth`, `role:admin`, `role:guru`, `role:siswa`) |
| Session & Timezone | `Asia/Jakarta`, `language = indonesian`, `sess_driver = files`, `ci_session` expire `86400s`, `csrf_protection = TRUE` (exclude `kelasmateri/saveMateri`, `uploadfile`, `deletefile`) | `APP_TIMEZONE=Asia/Jakarta`, Laravel database/file session, CSRF token bawaan Laravel |
| Auth Library | `Ion_auth` (`users`, `groups`, `users_groups`, `login_attempts`), `identity = username`, `hash_method = bcrypt` (cost `10`/`12`), `maximum_login_attempts = 3`, `lockout_time = 600s` | Laravel Auth (`SessionGuard` / custom `Username` login), `Hash::make` (bcrypt), RateLimiter 3 percobaan / 600 detik, tabel/kolom role & user ternormalisasi |
| Obfuskasi Source CI3 | Controller & Model di `/var/www/cbt/application/` memakai string-escape + `goto` flattening (View & Config plain PHP) | Rekonstruksi penuh menjadi Laravel Controllers, FormRequests, Models, Services bersih tanpa obfuskasi |
| Dependensi Eksternal (`composer.json`) | `phpoffice/phpspreadsheet: 1.*`, `phpoffice/phpword: ^0.18.1`, `alhimik1986/php-excel-templator: ^1.0`, `dompdf/dompdf: ^1.0` | `phpoffice/phpspreadsheet` (atau `maatwebsite/excel`), `phpoffice/phpword`, `barryvdh/laravel-dompdf` |
| Inventaris Aplikasi | `340` file di `application/` (termasuk `55` controller, `12` model, library `Ion_auth`, dan view), plus JS client-side (`htmlDocx`, `tableToExcel.js`, `linker-list.js`, Summernote, KaTeX) | Struktur MVC Laravel terorganisir + Vite/Tailwind CSS |

---

## 2. Pemetaan Database (`assets/app/db/master.sql` — Total 74 Tabel)

Berdasarkan `assets/app/db/master.sql`, terdapat **74 tabel** yang terbagi ke dalam 5 domain:

### A. Auth & Sistem (10 Tabel)
| Tabel CI3 | Fungsi di CI3 | Rencana Migrasi Laravel |
|---|---|---|
| `users` | Akun login utama (`username`, bcrypt `password`, `email`) | `users` (tambahkan `username` unique, relasi role/`userable` atau `role` enum/tabel) |
| `groups` | 3 grup utama: `1=admin`, `2=guru`, `3=siswa` | Tabel `roles` / Spatie Permission atau enum + tabel `groups`/`roles` |
| `users_groups` | Pivot user ↔ group | Pivot `role_user` / kolom `role` pada `users` |
| `users_profile` | Profil admin (`getProfileAdmin`) | Gabungkan ke profil admin / `users_profile` |
| `login_attempts` | Pencatat percobaan gagal login per IP & login (lockout 3x/600s, bisa di-reset admin) | Tabel `login_attempts` / Laravel RateLimiter + panel reset admin |
| `log` | Log aktivitas umum sistem | `activity_logs` |
| `setting` | Identitas sekolah (`logo_kiri`, kop sekolah, jenjang `1/2/3`, nama aplikasi, dll.) | `settings` (cached model/setting) |
| `running_text` | Teks berjalan pada dashboard | `running_texts` |
| `api_setting` | Konfigurasi integrasi API | `api_settings` |
| `api_token` | Token API eksternal | `api_tokens` |

### B. Master Data & Akademik (19 Tabel)
| Tabel CI3 | Fungsi & Catatan Schema CI3 | Rencana Migrasi Laravel |
|---|---|---|
| `master_tp` | Tahun pelajaran (seed `1..4`, flag aktif) | `academic_years` (`master_tp`) |
| `master_smt` | Semester (`1=Ganjil`, `2=Genap`, flag aktif) | `semesters` (`master_smt`) |
| `master_hari_efektif` | Jumlah hari efektif belajar | `effective_days` (`master_hari_efektif`) |
| `hari`, `bulan` | Tabel referensi nama hari dan bulan | Reference tables / config/enum atau dipertahankan |
| `level_kelas` | Tingkat kelas (`1..12`) | `class_levels` (`level_kelas`) |
| `level_guru` | Jabatan guru: `1=Kepala Sekolah`, `2=Wakil Kepala Sekolah`, `3=Bimbingan Konseling`, `4=Walikelas`, `5=Guru` | `teacher_levels` (`level_guru`) |
| `master_jurusan` | Jurusan/peminatan (seed `0..4`) | `majors` (`master_jurusan`) |
| `master_kelompok_mapel` | Kelompok mapel (`A, B, C, MULOK, C1, PAI`) | `subject_groups` (`master_kelompok_mapel`) |
| `master_mapel` | Mata pelajaran (42 data seed awal) | `subjects` (`master_mapel`) |
| `master_ekstra` | Ekstrakurikuler (3 data seed awal) | `extracurriculars` (`master_ekstra`) |
| `master_guru` | Biodata guru + menyimpan `username` & password awal sebelum aktivasi akun ke `users` | `teachers` (`master_guru`), relasi `belongsTo(User::class)` |
| `jabatan_guru` | Penugasan guru per TP/Smt; kolom `mapel_kelas` & `ekstra_kelas` menggunakan **PHP `serialize()`** | `teacher_assignments` (`jabatan_guru`) — ubah `mapel_kelas` & `ekstra_kelas` ke **JSON** / tabel pivot |
| `master_kelas` | Data kelas per TP/Smt; `jumlah_siswa` memakai **PHP `serialize()`** | `classes` (`master_kelas`) — `jumlah_siswa` ke JSON atau dihitung via relasi |
| `kelas_siswa` | Penempatan siswa ke kelas per TP/Smt | `class_students` (`kelas_siswa`) dengan FK `student_id`, `class_id`, `tp_id`, `smt_id` |
| `kelas_struktur` | Struktur pengurus kelas | `class_structures` (`kelas_struktur`) |
| `kelas_ekstra` | Anggota ekstrakurikuler kelas | `class_extracurriculars` (`kelas_ekstra`) |
| `master_siswa` | Biodata siswa (`agama`, dll.) + `username` & password awal sebelum aktivasi ke `users` | `students` (`master_siswa`), relasi `belongsTo(User::class)` |
| `buku_induk` | Arsip buku induk siswa | `student_ledgers` (`buku_induk`) |

### C. CBT Core (20 Tabel)
| Tabel CI3 | Fungsi, Key Unik, & Legacy Quirks di CI3 | Rencana Migrasi Laravel |
|---|---|---|
| `cbt_jenis` | Jenis ujian (7 seed: `PH, PTS, PAS, PAT, UMBK, TO, SIML`) | `exam_types` (`cbt_jenis`) |
| `cbt_ruang` | Ruang ujian (5 seed) | `exam_rooms` (`cbt_ruang`) |
| `cbt_sesi` | Sesi ujian (3 seed; `waktu_mulai`, `waktu_akhir`, `aktif`) | `exam_sessions` (`cbt_sesi`) |
| `cbt_kelas_ruang` | Pemetaan kelas ke ruang/sesi; PK gabungan string `id_kelas . id_tp . id_smt` | `exam_class_rooms` dengan PK `id` auto-increment + unique `(class_id, tp_id, smt_id)` |
| `cbt_sesi_siswa` | Penempatan ruang & sesi per siswa; PK `siswa_id` (1 ruang + 1 sesi per siswa) | `exam_student_sessions` dengan FK `student_id` (unique) -> `room_id`, `session_id` |
| `cbt_nomor_peserta` | Nomor peserta ujian; PK string `id_nomor = id_siswa . id_tp` (`id_tp`, `id_smt`, `nomor_peserta`) | `exam_participant_numbers` dengan unique `(student_id, tp_id)` |
| `cbt_pengawas` | Penugasan pengawas ruang/sesi; PK string `id_tp . id_smt . id_jadwal . id_ruang . id_sesi`, `id_guru` dipisah koma (`comma-separated`) | `exam_proctors` dengan FK terpisah + `proctor_teachers` (JSON atau pivot) |
| `cbt_token` | Token ujian global (`id_token`, `token` 6 huruf kapital `A-Z`, `auto`, `jarak` menit, `updated`) | `exam_tokens` (`cbt_token`) |
| `cbt_bank_soal` | Bank soal (`id_bank`, `id_tp`, `id_smt`, `bank_kode` UNIQUE, `bank_jenis_id`, `bank_mapel_id`, `bank_level`, `bank_guru_id`, `opsi` `2..5`, `jml_*`, `tampil_*`, `bobot_*` utk 5 tipe, `kkm`, `soal_agama` (`'-'`, `'0'`, atau nama agama), `status` `0/1`, `status_soal` `0/1`); `bank_kelas` memakai **PHP `serialize()`** `[['kelas_id'=>...]]` | `question_banks` (`cbt_bank_soal`) dengan kolom `target_classes` (**JSON** atau pivot `bank_class`) |
| `cbt_soal` | Butir soal (`jenis` `1..5`, `opsi_a..e`, `tampilkan` `0/1`, `kesulitan` `1-10`, `timer`, `timer_menit`); `file` (`serialize()`), `jawaban` (string utk tipe `1/4/5`, `serialize()` array huruf kecil utk tipe `2`, `serialize(['model','type','jawaban'=>2D matrix])` utk tipe `3`) | `questions` (`cbt_soal`) dengan cast **JSON** untuk `attachments` (`file`) dan struktur jawaban tipe 2 & 3 |
| `cbt_jadwal` | Jadwal ujian (`id_bank` UNIQUE di schema SQL; `saveJadwalUjian` juga cek `id_bank + id_jenis`), `tgl_mulai`, `tgl_selesai`, `durasi_ujian` (menit), `pengawas`, `acak_soal`, `acak_opsi`, `hasil_tampil`, `token`, `status`, `ulang`, `reset_login`, `rekap`, `jam_ke`, `jarak` | `exam_schedules` (`cbt_jadwal`) dengan FK `bank_id`, `exam_type_id` |
| `cbt_durasi_siswa` | State timer siswa; `id_durasi = id_siswa . '0' . id_jadwal`, `status` (`0=belum`, `1=sedang`, `2=sudah`), `lama_ujian` (`TIME`), `mulai`, `selesai`, `reset` (`0..3`) | `student_exam_durations` dengan PK `id` + unique `(student_id, schedule_id)` |
| `cbt_soal_siswa` | Soal & jawaban acak per siswa; `id_soal_siswa = id_siswa . '0' . id_jadwal . id_bank . no_soal_alias`, `no_soal_alias`, `opsi_alias_a..e`, `jawaban_alias`, `jawaban_siswa` (plain `1/4/5`, `serialize()` `2/3`), `jawaban_benar`, `point_soal`, `nilai_koreksi`, `nilai_otomatis` (`0/1`), `soal_end` | `student_exam_questions` dengan PK `id` + unique `(student_id, schedule_id, question_id)` dan cast **JSON** untuk `student_answer` |
| `cbt_nilai` | Rekap nilai ujian siswa; `id_nilai = id_siswa . '0' . id_jadwal`, `pg_benar`, `pg_nilai`, `kompleks_nilai`, `jodohkan_nilai`, `isian_nilai`, `essai_nilai`, `dikoreksi` (`0/1`) | `student_exam_scores` dengan PK `id` + unique `(student_id, schedule_id)` |
| `cbt_kop_kartu` | Header cetak kartu peserta (`id_set_kartu=123456`, `header_1..4`, `tanggal`) | `exam_print_cards` / JSON config |
| `cbt_kop_absensi` | Header cetak daftar hadir (`id_kop=123456`, `header_1..4`, `proktor`, `pengawas_1`, `pengawas_2`) | `exam_print_attendances` / JSON config |
| `cbt_kop_berita` | Header cetak berita acara (`id_kop=123456`, `header_1..4`) | `exam_print_minutes` / JSON config |
| `cbt_rekap` | Arsip snapshot jadwal/ujian (`bank_kelas` & `nama_kelas` **PHP `serialize()`**) | `exam_recaps` (cast JSON) |
| `cbt_rekap_nilai` | Arsip snapshot nilai siswa per rekap | `exam_recap_scores` |
| `log_ujian` | Log mulai/selesai & anti-cheat device lock; `id_log = id_siswa . '0' . id_jadwal . log_type` (`1=Memulai Ujian`, `2=Menyelesaikan Ujian`), `address`, `agent`, `device`, `reset` | `exam_logs` dengan FK `(student_id, schedule_id, log_type)` |

### D. E-Learning & Komunikasi (10 Tabel — Sekunder / Non-CBT Core)
`post`, `post_comments`, `post_reply`, `kelas_jadwal_kbm`, `kelas_jadwal_mapel`, `kelas_jadwal_materi`, `kelas_materi`, `log_materi`, `kelas_catatan_mapel`, `kelas_catatan_wali`.
*(Catatan: `Pengumuman` memakai tabel `post`, `post_comments`, `post_reply` dan `running_text`.)*

### E. Rapor (15 Tabel — Sekunder / Non-CBT Core)
`rapor_admin_setting`, `rapor_catatan_wali`, `rapor_data_catatan`, `rapor_data_fisik`, `rapor_data_sikap`, `rapor_fisik`, `rapor_kikd`, `rapor_kkm`, `rapor_naik`, `rapor_nilai_akhir`, `rapor_nilai_ekstra`, `rapor_nilai_harian`, `rapor_nilai_pts`, `rapor_nilai_sikap`, `rapor_prestasi`.

> **Rekomendasi Desain Schema Laravel:**
> 1. **Ganti seluruh Composite String ID (`id_siswa . '0' . id_jadwal`, dll.)** menjadi primary key `bigIncrements('id')` standar + `unique()` constraint pada foreign key yang tepat agar terhindar dari tabrakan ID bila panjang digit ID berubah.
> 2. **Ganti seluruh `PHP serialize()`** (`bank_kelas`, `opsi_*`, `file`, `jawaban`, `jawaban_siswa`, `mapel_kelas`, `ekstra_kelas`, `jumlah_siswa`, `nama_kelas`) menjadi kolom `json` dengan Eloquent `$casts = ['...' => 'array']`.

---

## 3. Role Pengguna, Alur Autentikasi, & Keamanan Akses

### A. Struktur Role
1. **`admin` (`groups.id = 1`)**: Akses penuh ke seluruh master data, manajemen user (`Useradmin`, `Userguru`, `Usersiswa`), aktivasi/nonaktif login, reset `login_attempts`, pengaturan sekolah, DB backup/clear, serta seluruh modul CBT.
2. **`guru` (`groups.id = 2`)**: Terhubung ke `master_guru` & `jabatan_guru`:
   - Sub-role struktural dari `level_guru`: `1=Kepala Sekolah`, `2=Wakil Kepala Sekolah`, `3=Bimbingan Konseling`, `4=Walikelas`, `5=Guru`.
   - Pada sidebar (`members/guru/templates/sidebar.php`), menu khusus wali kelas tampil jika `jabatan === '4'`.
   - Dalam CBT, guru dapat mengelola `Cbtbanksoal` (miliknya), `Cbtstatus`, `Cbtnilai` (koreksi & lihat nilai), `Cbtrekap`, `Cbtanalisis`, serta bertugas sebagai pengawas di `cbt_pengawas`.
3. **`siswa` (`groups.id = 3`)**: Terhubung ke `master_siswa`, `kelas_siswa`, `cbt_sesi_siswa`, `cbt_nomor_peserta`. Hanya mengakses dashboard siswa, ujian CBT (`Siswa::cbt`, `konfirmasi`, `penilaian`), hasil (jika `hasil_tampil == '1'`), dan catatan.

### B. Alur Login, Session, Lockout, & Mode CBT
1. Form login (`views/auth/login.php`) mengirim `POST auth/cek_login` dengan `username` & `password`.
2. **Mode CBT-Only (`cbt-only`):** Checkbox default aktif (`value='1'`) menyimpan `localStorage.setItem('garudaCBT.login', '1'|'0')`. Jika aktif dan `data.role === 'siswa'`, redirect ke `siswa/cbt`; selain itu ke `data.url` (`dashboard`). Pada sidebar admin/guru, item bertanda `cbt: '0'` (E-Learning/Rapor) disembunyikan ketika mode CBT aktif (`'1'`).
3. **Aktivasi Akun Terpisah:** Guru (`master_guru`) dan siswa (`master_siswa`) yang baru diinput/diimpor menyimpan `username` dan password plaintext awal di tabel master, namun **belum bisa login** sampai diaktifkan melalui `Userguru` / `Usersiswa` (yang membuat entri `users` + `users_groups` group `2`/`3` dengan email `@guru.com` / `@siswa.com`).
4. **Brute-force Lockout:** `Auth` melacak gagal login di `login_attempts` (maksimal `3` kali, lockout `600` detik berdasarkan IP & identity); admin dapat membuka kunci via aksi reset login attempts.
5. **Celah Keamanan CI3 yang Harus Diperketat di Laravel:** Pada beberapa controller CI3 (seperti `Cbtbanksoal` dan `Siswa`), `__construct` hanya mengecek `logged_in()` tanpa verifikasi grup yang ketat di semua method. Di Laravel, wajib terapkan middleware role eksplisit (`role:admin`, `role:admin,guru`, `role:siswa`) + Policy kepemilikan bank soal untuk guru.

---

## 4. Pemetaan Lengkap Fitur, Controller, & Route CBT

CI3 memakai konvensi `Controller/method/arg` (default `Auth/index`). Berikut pemetaan seluruh modul utama ke target Laravel:

### Tahap 1 — Master Data & User Management
| Controller CI3 | Fitur Utama | Target Controller Laravel |
|---|---|---|
| `Auth`, `Dashboard`, `Guruview` | Login/logout, change password, lupa/reset password, switch TP/Smt aktif, widget box dashboard, ganti foto/profil guru | `AuthController`, `DashboardController`, `TeacherProfileController` |
| `Useradmin`, `Userguru`, `Usersiswa`, `Users` | CRUD admin, aktivasi massal/satuan akun guru & siswa ke `users`, nonaktifkan akun, reset password, reset lockout `login_attempts` | `AdminUserController`, `TeacherUserController`, `StudentUserController` |
| `Datatahun`, `Datamapel`, `Datajurusan`, `Dataekstra` | CRUD Tahun Pelajaran & Semester aktif, Mapel & Kelompok Mapel, Jurusan, Ekstrakurikuler | `AcademicYearController`, `SubjectController`, `MajorController`, `ExtracurricularController` |
| `Datakelas`, `Datasiswa`, `Dataguru` | CRUD Kelas, penempatan siswa per TP/Smt (`copyFromSmt1`, `copySiswaFromSmt1`, `kenaikan`/`naikKelas`), CRUD & Import Excel Guru/Siswa (`PhpSpreadsheet`) | `ClassroomController`, `StudentController`, `TeacherController` |
| `Settings`, `Pengumuman`, `Dbmanager`, `Dbclear` | Profil sekolah & logo, Pengumuman (`post` + komentar + `running_text`), Backup DB/ZIP & truncate tabel | `SettingController`, `AnnouncementController`, `DatabaseManagerController` |

### Tahap 2 — Bank Soal (`Cbtbanksoal`)
- **CRUD Bank Soal (`Cbt_model::saveBankSoal`):** Pengaturan `bank_kode` (unik), jenis ujian, mapel, level, daftar kelas (`bank_kelas`), guru pembuat, filter agama (`soal_agama`), jumlah opsi (`2..5`), kuota `tampil_*` dan `bobot_*` untuk masing-masing 5 tipe soal.
- **Editor 5 Tipe Soal (`cbt_soal`):**
  1. `1 = PG` (Pilihan Ganda tunggal, `opsi_a..e`, kunci `A..E`)
  2. `2 = PG Kompleks` (Multi-jawaban, kunci array huruf kecil)
  3. `3 = Menjodohkan` (Matriks relasi 2D `['model', 'type', 'jawaban' => ...]` di UI via `linker-list.js`)
  4. `4 = Isian Singkat` (Teks singkat)
  5. `5 = Uraian / Esai` (Rich text via Summernote + KaTeX)
- **Media & Import:** Ekstraksi gambar base64 dari HTML editor ke file fisik (`uploads/bank_soal/img_{id_bank}{jenis}{nomor}_{hex}.{ext}` melalui `DOMDocument`), lampiran file, tingkat kesulitan (`1-10`), serta import soal dari Excel/Word.

### Tahap 3 — Manajemen Ujian & Persiapan CBT
| Controller CI3 | Fitur Utama |
|---|---|
| `Cbtjenis`, `Cbtruang`, `Cbtsesi` | Master Jenis Ujian (`PH, PTS, ...`), Ruang, dan Sesi (jam mulai/akhir & status aktif) |
| `Cbtsesisiswa`, `Cbtnomorpeserta`, `Cbtalokasi` | Atur siswa ke ruang & sesi (`cbt_sesi_siswa`), generate nomor peserta per TP (`cbt_nomor_peserta`), atur `jam_ke` / alokasi waktu ujian per jadwal |
| `Cbtjadwal` | CRUD Jadwal Ujian (`tgl_mulai..tgl_selesai`, `durasi_ujian`, `jarak` minimal menit selesai, `acak_soal`, `acak_opsi`, `token`, `hasil_tampil`, `reset_login`, `status`) |
| `Cbtpengawas` | Penugasan pengawas guru per jadwal, ruang, dan sesi |
| `Cbttoken` | Generate token 6 karakter huruf kapital `A-Z`, mode `auto` refresh berdasarkan `jarak` menit (`cbt_token`) |
| `Cbtstatus` | Monitoring status peserta real-time (`BELUM`, `SEDANG`, `SELESAI`) + aksi pengawas/admin: **Reset Login/Device Lock**, **Reset Timer** (`0/1/2/3`), dan **Paksa Selesai / Ulangi Ujian** |

### Tahap 4 — Halaman Pengerjaan Ujian Siswa (`Siswa.php` & `views/members/siswa/cbt/`)
1. **Daftar Jadwal (`Siswa::cbt`, `cbt/data.php`):**
   - Menampilkan info peserta (`no_peserta`, `nama_ruang`, `nama_sesi`, `waktu_mulai`, `waktu_akhir`).
   - Status tombol dinamis: `BELUM DIMULAI`, `SUDAH BERAKHIR`, `SESI BELUM DIMULAI`, `SESI SUDAH BERAKHIR`, `MENUNGGU` (jika `jam_ke` sebelumnya belum selesai), `KERJAKAN`, `LANJUTKAN`, `SUDAH SELESAI`.
   - Filter soal agama (`soal_agama`) menyesuaikan agama siswa atau umum (`'-'` / `'0'`).
2. **Konfirmasi & Validasi Masuk (`Siswa::konfirmasi`, `Siswa::validasiSiswa`):**
   - Verifikasi token ujian (bila `jadwal->token == '1'`).
   - Deteksi user agent (tolak `'unknown'`).
   - **Device / Login Lock (`reset_login == '1'`):** Cek `log_ujian` (`log_type = 1`); tolak masuk jika browser/device berbeda saat `log->reset == 0`.
   - Cek sisa waktu (`mulai` vs `durasi_ujian`) dan flag `cbt_durasi_siswa.reset` (`1=reset dari 0`, `2=reset dari sisa waktu`, `3=ulangi semua`).
   - Masuk pertama kali memanggil `createQueueNumber`: mengacak urutan soal (`acak_soal`), mengacak alias opsi PG (`acak_opsi` ke `opsi_alias_a..e`), menghitung `point_soal = bobot / tampil`, dan batch-insert ke `cbt_soal_siswa`.
3. **Exam Runner (`Siswa::penilaian`, `cbt/ujian.php`):**
   - Render 5 tipe soal + `linker-list.js` + Summernote + KaTeX + zoom slider + fullscreen prompt.
   - **Anti-Cheat:** Blokir tombol `Esc`, `Win`, klik kanan, serta listener `visibilitychange` — jika tab kehilangan fokus saat `infoJadwal.reset_login === '1'`, panggil `siswa/leavecbt/{id_jadwal}/{id_siswa}` yang menandai `log_ujian.agent = 'illegal agent'` dan `device = 'illegal device'`.
   - **Timer & Minimum Jarak Selesai:** `createTimerCountdown` sinkron dengan server (`checkTimer` pada setiap `loadNomorSoal`), dan `setTimerSelesai` menahan tombol selesai sebelum `elapsed >= infoJadwal.jarak` menit.
   - **Auto-Save Jawaban:** Setiap perpindahan soal (Next/Prev/Modal nomor) memanggil `POST siswa/savejawaban` (simpan jawaban + `lama_ujian`) lalu `siswa/loadnomorsoal`; selesai ujian memanggil `POST siswa/selesaiujian`.

### Tahap 5 — Penilaian, Analisis, Rekap, & Cetak
1. **Rumus Penilaian Otomatis & Koreksi (`Siswa::olahNilai`, `Cbtnilai`, `Cbtanalisis::generateNilaiUjian`):**
   - **Tipe 1 (PG):** $\text{skor\_pg} = \frac{\text{benar\_pg}}{\text{tampil\_pg}} \times \text{bobot\_pg}$ (atau ditulis di kode `(benar_pg / (tampil_pg / 100)) * (bobot_pg / 100)`).
   - **Tipe 2 (PG Kompleks):** Kredit parsial per soal $= \frac{|\text{jawaban\_cocok}|}{|\text{jawaban\_benar}|}$, dijumlahkan menjadi `benar_pg2`, lalu $\text{s\_pg2} = \frac{\text{benar\_pg2}}{\text{tampil\_kompleks}} \times \text{bobot\_kompleks}$ (dapat ditimpa nilai koreksi guru jika `nilai_otomatis != 0` atau manual `cbt_nilai.kompleks_nilai != 0`).
   - **Tipe 3 (Menjodohkan):** Total relasi matriks diharapkan `items`, total irisan benar per baris `item_benar`, akumulasi `benar_jod += item_benar / items`, lalu $\text{s\_jod} = \frac{\text{benar\_jod}}{\text{tampil\_jodohkan}} \times \text{bobot\_jodohkan}$ (dengan override `nilai_otomatis` / `jodohkan_nilai`).
   - **Tipe 4 & 5 (Isian Singkat & Esai):** Pencocokan otomatis `strtolower(siswa) == strtolower(kunci)` menghasilkan `s_is` / `s_es`, yang dapat ditimpa oleh nilai koreksi guru per soal (`simpanKoreksi` -> mengisi `nilai_koreksi`, set `nilai_otomatis = 1`) atau `simpanKoreksiEssai`.
2. **Katrol Nilai (`Cbtnilai::index` / `Cbtrekap::olahNilai`):**
   - Rumus penskalaan linear: $\text{skor\_katrol} = \frac{ya - yb}{100} \times \text{total} + yb$ (dengan $xa$ nilai maks aktual, $xb$ min aktual, $ya$ target maks, $yb$ target min).
3. **Analisis Butir Soal (`Cbtanalisis`):**
   - Validitas butir (korelasi Pearson $r$ + lookup signifikansi r-tabel), tingkat kesukaran ($P = \text{benar} / N$), daya pembeda ($D = \frac{\text{atas}}{\text{bagi}} - \frac{\text{bawah}}{\text{bagi}}$).
4. **Hasil, Rekap, & Cetak (`Hasilujian`, `Cbtrekap`, `Cbtcetak`):**
   - `Cbtrekap` membuat snapshot permanen ujian dan nilai ke `cbt_rekap` dan `cbt_rekap_nilai`.
   - `Cbtcetak` + view `cbt/cetak/*`: Pengaturan kop (`kop.php`), Kartu Peserta (`kartu.php`), Daftar Hadir (`absen.php`), Berita Acara (`beritaacara.php`), Daftar Pengawas (`pengawas.php`), dan Daftar Peserta (`peserta.php`, `pesertaujian.php`). Output menggunakan browser print (`window.print()`), export Word (`htmlDocx`), dan Excel (`tableToExcel.js`). Di Laravel akan didukung tampilan print Tailwind `@media print`, PDF (`dompdf`), serta Excel (`PhpSpreadsheet`).

---

## 5. Urutan Rencana Fase Berikutnya & Pertanyaan Konfirmasi Sebelum Fase 2

1. **Fase 2 — Fondasi Laravel:** Inisialisasi project Laravel di `/var/www/cbtrewirite`, konfigurasi `.env` (DB MySQL/MariaDB, timezone `Asia/Jakarta`), tulis seluruh Migrations, Eloquent Models & relasi, Seeders awal (role/group, TP, Smt, level, jenis/ruang/sesi CBT, setting), serta sistem Auth multi-role + rate-limiter lockout + CBT-only toggle.
2. **Fase 3 — Rebuild Modul Inti:** Master Data → Bank Soal (5 tipe + upload media + import) → Manajemen Ujian (Ruang/Sesi/Alokasi/Nomor Peserta/Jadwal/Pengawas/Token) → Exam Runner (Timer, Auto-save, Anti-cheat `reset_login`) → Penilaian/Koreksi/Katrol/Analisis/Rekap → Cetak/Export.
3. **Fase 4 — Verifikasi Logika:** Audit silang route, validasi, aturan akses, dan rumus perhitungan nilai terhadap source CI3 di `/var/www/cbt`.

### Mohon Konfirmasi Anda Sebelum Memulai Fase 2:
1. **Cakupan Modul:** Apakah rebuild difokuskan ke **Core CBT + Master Data + Auth/Settings/Pengumuman** terlebih dahulu, ataukah modul **E-Learning** (`kelas_materi`, `jadwal_kbm`, dll.) dan **Rapor** (`rapor_*`) juga ikut dibangun ulang?
2. **Stack Frontend:** Apakah Anda memilih **Laravel Blade + Tailwind CSS (+ Alpine.js)** *(disarankan untuk kesederhanaan & kompatibilitas cetak/exam runner)* atau **Inertia.js + Vue 3 (+ Nuxt UI / Tailwind)**?
