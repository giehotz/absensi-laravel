# Blueprint 04 — Penilaian, Katrol, Analisis Butir Soal, Rekap, & Cetak (Fase 3C)

> **Controller Target (`App\Http\Controllers\Cbt\`):** `NilaiController`, `KoreksiController`, `AnalisisSoalController`, `RekapController`, `CetakController`
> **Service Target (`App\Services\Cbt\`):** `ScoringService`, `KatrolService`, `ItemAnalysisService`, `RecapService`, `ExcelExportService`
> **Konvensi Cetak:** Memakai komponen `<x-print-preview-layout>` + `ujian_pengaturan_ttd` milik `/var/www/absensi-laravel` dan `PhpOffice\PhpSpreadsheet ^5.10`.

---

## 1. Spesifikasi `ScoringService` (Rumus Penilaian Otomatis & Koreksi Manual)

Rumus pada `ScoringService` menjaga paritas penuh terhadap `Siswa::olahNilai`, `Cbtnilai`, dan `Cbtanalisis::generateNilaiUjian` di `/var/www/cbt`.

### A. Rumus Penilaian Otomatis 5 Tipe Soal

Misalkan untuk setiap tipe $t \in \{1..5\}$, bank soal memiliki kuota tampil $T_t$ dan bobot $W_t$:

1. **Tipe 1 — Pilihan Ganda Tunggal (`pg_score`):**
   - Bandingkan `student_answer['selected']` dengan `answer_alias` (atau jawaban asli terpetakan).
   - Hitung jumlah benar `pg_correct`.
   - Jika $T_1 > 0$:
     $$\text{pg\_score} = \left(\frac{\text{pg\_correct}}{T_1}\right) \times W_1$$
   *(Ekuivalen secara matematis dengan ekspresi CI3 `(benar_pg / (tampil_pg / 100)) * (bobot_pg / 100)` dan aman terhadap presisi floating-point sebelum dibulatkan 2 desimal.)*

2. **Tipe 2 — Pilihan Ganda Kompleks (`kompleks_score`):**
   - Per butir soal $q$: jika kunci benar berisi $K_q$ opsi ($|K_q| > 0$), pilihan siswa adalah $S_q$, jumlah pilihan benar adalah $\text{benar} = |S_q \cap K_q|$, dan jumlah pilihan salah adalah $\text{salah} = |S_q \setminus K_q|$:
     - Terapkan rumus penalti pilihan salah agar siswa tidak bisa mencentang semua opsi untuk memperoleh skor penuh:
       $$p_q = \max\left(0,\; \frac{\text{benar} - \text{salah}}{|K_q|}\right)$$
     - Jumlahkan menjadi $\text{benar\_pg2} = \sum p_q$.
     - Jika guru mengisi koreksi manual (`is_manual_graded = true`), nilai butir diambil dari `corrected_score`; jika tidak, pakai $(p_q / T_2) \times W_2$.
   - Jika tidak ada koreksi per soal dan terdapat override manual pada kolom `cbt_student_scores.kompleks_score` saat `is_corrected = true`, pertahankan nilai koreksi.

3. **Tipe 3 — Menjodohkan (`jodohkan_score`):**
   - Pada struktur `pairs` (ekuivalen matriks 2D CI3):
     - Hitung `items` = total pasangan benar yang diharapkan pada `answer_key['pairs']`.
     - Hitung `item_benar` = total pasangan siswa yang cocok dengan `answer_key['pairs']`.
     - Hitung `item_salah` = total pasangan yang dipilih siswa tetapi **tidak** ada pada `answer_key['pairs']`.
     - Terapkan rumus penalti pilihan salah per butir soal *(bila `items > 0`)*:
       $$\text{benar\_jod} += \max\left(0,\; \frac{\text{item\_benar} - \text{item\_salah}}{\text{items}}\right)$$
   - Jika $T_3 > 0$:
     $$\text{jodohkan\_score} = \left(\frac{\text{benar\_jod}}{T_3}\right) \times W_3$$
   - Mendukung override koreksi manual per soal (`corrected_score`, `is_manual_graded = true`) sebagaimana tipe 2.

4. **Tipe 4 — Isian Singkat (`isian_score`):**
   - Untuk setiap soal tanpa `is_manual_graded = true`: bandingkan `mb_strtolower(trim($studentAnswer)) === mb_strtolower(trim($expected))` *(bila cocok bernilai `1`, selainnya `0`)*, lalu kalikan $\frac{W_4}{T_4}$.
   - Jika guru mengoreksi manual (`corrected_score`), gunakan `corrected_score`.

5. **Tipe 5 — Esai / Uraian (`esai_score`):**
   - Nilai awal otomatis: cek kecocokan eksak `mb_strtolower(trim($studentAnswer)) === mb_strtolower(trim($expected))` (menghasilkan `0` bila esai berupa uraian panjang).
   - **Alur Koreksi Guru (`KoreksiController`):**
     - Guru membuka daftar siswa per jadwal, melihat jawaban uraian/isian/kompleks/jodohkan per siswa, lalu menginput `corrected_score` (maksimal `max_point = W_t / T_t`) per nomor soal.
     - Menyimpan koreksi mengatur `is_manual_graded = true` pada `cbt_student_questions`, menghitung ulang `cbt_student_scores`, dan menandai `is_corrected = true` *(identik dengan `Cbtnilai::simpanKoreksi` & `simpanKoreksiEssai` di CI3)*.

6. **Skor Total (`total_score`):**
   $$\text{total\_score} = \text{round}(\text{pg\_score} + \text{kompleks\_score} + \text{jodohkan\_score} + \text{isian\_score} + \text{esai\_score}, 2)$$

---

## 2. Rumus Katrol Nilai (`KatrolService`)

Mengikuti `Cbtnilai::index` & `Cbtrekap::olahNilai` di CI3:
- Guru/Admin memasukkan target rentang nilai baru: batas bawah target $y_b$ (mis. `75`) dan batas atas target $y_a$ (mis. `95`), sambil menampilkan statistik nilai aktual minimum $x_b$ dan maksimum $x_a$.
- Rumus skala linear yang digunakan GarudaCBT:
  $$\text{katrol\_score} = \text{round}\left(\frac{y_a - y_b}{100} \times \text{total\_score} + y_b,\; 2\right)$$
- Menyediakan aksi **Terapkan & Simpan Katrol** ke `cbt_student_scores.katrol_score` atau hanya pratinjau/export Excel.

---

## 3. Analisis Butir Soal (`ItemAnalysisService` — Parity `Cbtanalisis`)

Untuk jadwal ujian yang dipilih, `ItemAnalysisService` memproses seluruh `cbt_student_questions` milik siswa yang telah selesai (`status = 2`):

1. **Kelompok Atas & Bawah:**
   - Urutkan siswa berdasarkan skor total menurun.
   - Bagi kelompok atas dan bawah (`bagi_daya`, mis. 27% atau 50% atas & bawah sesuai implementasi `Cbtanalisis::index` di `/var/www/cbt`).
2. **Tingkat Kesukaran ($P$):**
   $$P = \frac{\text{jumlah\_benar}}{N}$$
   - Klasifikasi: `mudah`, `sedang`, atau `sukar` berdasarkan ambang batas $P$.
3. **Daya Pembeda ($D$):**
   $$D = \frac{\text{benar\_atas}}{\text{bagi\_daya}} - \frac{\text{benar\_bawah}}{\text{bagi\_daya}}$$
   - Klasifikasi status daya pembeda (`Baik`, `Cukup`, `Jelek`, dll.).
4. **Validitas Butir ($r$ Pearson):**
   - Vektor $X$: skor siswa pada butir soal ke-$i$ (`0/1` atau parsial).
   - Vektor $Y$: skor total ujian masing-masing siswa.
   - Hitung korelasi Pearson $r = \frac{\sum (x_k - \bar{x})(y_k - \bar{y})}{\sqrt{\sum (x_k - \bar{x})^2 \sum (y_k - \bar{y})^2}}$ *(return `-1` jika penyebut `= 0`)*.
   - Bandingkan terhadap `nilaiSignifikansi(N)` (tabel $r$ kritis 5% berdasarkan jumlah responden $N$): **`Valid`** jika $r \ge r_{\text{tabel}}$, selainnya **`Tidak valid`**.

---

## 4. Snapshot Rekap Nilai (`RecapService` — `cbt_recaps` & `cbt_recap_scores`)

Agar data nilai historis aman ketika bank soal, kelas, atau siswa di semester berikutnya berubah/naik kelas:
1. Tombol **Rekap Ujian** (setara `Cbtrekap::rekap`) menyalin metadata jadwal, mapel, guru, jenis ujian, konfigurasi bobot/tampil, serta daftar kelas ke **`cbt_recaps`** (dalam `classes_snapshot` JSON & `bank_config_snapshot` JSON).
2. Seluruh nilai siswa pada jadwal tersebut disalin ke **`cbt_recap_scores`** (termasuk snapshot nama siswa, nama kelas, nomor peserta, skor per tipe, `total_score`, dan `katrol_score`), lalu `cbt_schedules.is_recapped = true`.
3. Halaman Rekap mendukung hapus rekap, filter per TP/Semester/Jenis/Kelas, katrol massal di rekap, serta export rekap nilai.

---

## 5. Rancangan Cetak & Export (`CetakController` & `ExcelExportService`)

Selaras dengan konvensi `/var/www/absensi-laravel` (yang **tidak** memakai DomPDF maupun JS `tableToExcel`/`htmlDocx` klien yang rapuh):

### A. Dokumen Cetak yang Sudah Ada di `absensi-laravel` (Diperkaya, Tidak Diduplikasi)
- **Kartu Peserta Ujian (`ujian.kartu.print`):** Sudah ada di `PesertaKartuUjianController` (`kartu/print-a4.blade.php` dengan kop, watermark, TTD + stempel dari `ujian_pengaturan_ttd`, dan QR validasi). Penambahan minimal: pastikan bila siswa ikut CBT, kartu dapat menampilkan kredensial/username login (`NIS`/`NISN`) + ruang/sesi dari `ujian_peserta`.
- **Daftar Hadir Peserta, Berita Acara, Tata Tertib, SK Panitia (`ujian.administrasi.*`):** Sudah ada di `AdministrasiUjianController` memakai `<x-print-preview-layout>`.
- **Jadwal & Daftar Hadir Pengawas (`ujian.pengawas.*`):** Sudah ada di `JadwalPengawasController` & `PengawasAbsensiAdminController`.

### B. Dokumen Cetak & Export Baru Khusus CBT (`/cbt/manage/cetak/*` & `/cbt/manage/rekap/*`)
1. **Berita Acara & Daftar Hadir CBT per Jadwal Online (`@media print` via `<x-print-preview-layout>`):**
   - Menarik data peserta otomatis dari `ujian_peserta` + status selesai/pelanggaran dari `cbt_student_durations` & `cbt_exam_logs` untuk dicetak langsung per ruang & sesi.
2. **Laporan Nilai, Analisis Butir Soal, & Rekap Nilai (Cetak HTML + Export `.xlsx` via `PhpSpreadsheet ^5.10`):**
   - `ExcelExportService` menghasilkan file `.xlsx` rapi (header berformat, merge cell kop, nilai per tipe PG/Kompleks/Jodohkan/Isian/Esai, Total, Katrol, dan Statistik Analisis $P/D/r$ Pearson) mengikuti pola `AssessmentExcelService.php` & `StudentController` di `absensi-laravel`.
   - Dukungan cetak PDF/Print browser A4 Portrait/Landscape via `<x-print-preview-layout>` untuk lembar koreksi jawaban siswa, rekap nilai kelas, dan laporan analisis butir soal.
