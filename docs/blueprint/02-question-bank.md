# Blueprint 02 — Bank Soal & 5 Tipe Soal (Fase 3A)

> **Controller Target:** `App\Http\Controllers\Cbt\BankSoalController`, `App\Http\Controllers\Cbt\QuestionController`
> **Service Target:** `App\Services\Cbt\QuestionBankService`, `App\Services\Cbt\QuestionMediaService`, `App\Services\Cbt\QuestionImportService`
> **Layout UI:** Mengikuti layout admin/guru Neobrutalism `/var/www/absensi-laravel` (Tailwind CSS v4) + Policy kepemilikan guru (`CbtQuestionBankPolicy`).

---

## 1. Alur Integrasi Bank Soal dengan Master Eksisting `absensi-laravel`

1. **Pemilihan Tahun Pelajaran & Semester (`academic_years`):**
   - Bank soal baru otomatis terikat ke `AcademicYear::activeSemester()`, dengan filter tahun/semester pada halaman daftar bank soal.
2. **Pemilihan Mata Pelajaran & Guru (`subjects`, `teachers`, `teacher_assignments`):**
   - Jika login sebagai **`admin`**, dropdown Mapel menampilkan seluruh `Subject::orderBy('name')`, dan dropdown Guru menampilkan seluruh `Teacher` aktif (`teachers.staff_type = 'guru'`). Ketika guru dipilih, filter mapel dapat menyorot mapel dari `teacher_assignments`.
   - Jika login sebagai **`guru`**, `teacher_id` terkunci ke `$user->teacher->id` dan pilihan mapel diprioritaskan dari `TeacherAssignment::where('teacher_id', ...)`.
3. **Pemilihan Jenis Ujian (`ujian_jenis`):**
   - Dropdown mengambil dari `UjianJenis::active()` eksisting (mis. Formatif, Sumatif Lingkup Materi, SAS, SAT, UM) tanpa menduplikasi tabel jenis.
4. **Target Tingkat & Kelas (`school_classes`):**
   - Saat guru memilih `level` (misal `1..6` atau `SD/MI/SMP/MA` sesuai `AttendanceSetting::first()->level`), AJAX endpoint `GET /cbt/manage/bank-soal/classes-by-level` mengembalikan `SchoolClass::currentAcademicYear()` yang cocok untuk disimpan ke kolom JSON `target_class_ids`.
   - `option_count` (jumlah opsi PG) default menyesuaikan jenjang di `AttendanceSetting::first()->level`: `SD/MI` = `3` atau `4`, `SMP/MTs` = `4`, `SMA/MA` = `5` (tetap bisa diubah manual `2..5`).
5. **Pengaturan Kuota & Bobot 5 Tipe Soal:**
   - Mengikuti GarudaCBT (`Cbt_model::saveBankSoal`): setiap bank menyimpan jumlah tampil (`*_display`) dan bobot persentase (`*_weight`) per tipe soal, dengan validasi total bobot disarankan `= 100` dan tombol **Selesaikan / Publikasikan Bank Soal** (`is_questions_ready = true`) hanya aktif jika jumlah butir aktif (`is_displayed = true`) >= kuota tampil tiap tipe.

---

## 2. Desain Detail 5 Tipe Soal & Contoh Struktur JSON

Seluruh `PHP serialize()` pada `cbt_soal` CI3 diganti menjadi struktur `json` yang tervalidasi ketat oleh FormRequest.

### Tipe 1 — Pilihan Ganda Tunggal (`question_type = 1`)
- Jumlah opsi mengikuti `cbt_question_banks.option_count` (`a..e`).
- **`options` (JSON):**
```json
{
  "a": "<p>Jakarta</p>",
  "b": "<p>Bandung</p>",
  "c": "<p>Surabaya</p>",
  "d": "<p>Medan</p>"
}
```
- **`answer_key` (JSON):**
```json
{
  "correct": "A"
}
```
*(Disimpan huruf kapital konsisten; saat acak opsi `shuffle_options = true`, dicocokkan terhadap peta alias siswa.)*

### Tipe 2 — Pilihan Ganda Kompleks / Multi-Jawaban (`question_type = 2`)
- Guru dapat menambah opsi dinamis (`a`, `b`, `c`, ..., hingga `z` / mengikuti ASCII `97..116` pada CI3 `Cbtbanksoal::saveSoal`) dan mencentang lebih dari satu kunci benar.
- **`options` (JSON):**
```json
{
  "a": "<p>Bilangan prima terkecil adalah 2</p>",
  "b": "<p>Semua bilangan ganjil adalah prima</p>",
  "c": "<p>5 termasuk bilangan prima</p>",
  "d": "<p>9 termasuk bilangan prima</p>"
}
```
- **`answer_key` (JSON):**
```json
{
  "correct": ["a", "c"]
}
```
*(Array huruf kecil unik terurut untuk perhitungan kredit parsial.)*

### Tipe 3 — Menjodohkan (`question_type = 3`)
- Mengacu langsung ke komponen `linker-list.js` GarudaCBT (`Cbtbanksoal::saveSoal` & `ujian.php`):
  - `model`: `'1'` = List Penghubung (garis SVG antar item kiri & kanan), `'2'` = Tabel Matriks (grid baris × kolom).
  - `type`: `'2'` = 1 baris 1 jawaban (radio), `'1'` = 1 baris banyak jawaban (checkbox).
- **`options` (JSON):**
```json
{
  "model": "1",
  "type": "2",
  "rows": [
    { "key": "r1", "html": "<p>Jawa Barat</p>" },
    { "key": "r2", "html": "<p>Jawa Timur</p>" },
    { "key": "r3", "html": "<p>Bali</p>" }
  ],
  "cols": [
    { "key": "c1", "html": "<p>Surabaya</p>" },
    { "key": "c2", "html": "<p>Bandung</p>" },
    { "key": "c3", "html": "<p>Denpasar</p>" }
  ]
}
```
- **`answer_key` (JSON):**
```json
{
  "pairs": {
    "r1": ["c2"],
    "r2": ["c1"],
    "r3": ["c3"]
  }
}
```
> **Catatan Kompatibilitas Rumus CI3:** Pada CI3, `jawaban` bertipe matriks 2D (`row 0` header kolom, `row 1..N` isi `"0"`/`"1"`). Representasi `{rows, cols, pairs}` jauh lebih bersih, mudah di-render di Alpine/Vanilla JS (baik mode List SVG maupun Tabel Matriks), dan secara matematis ekuivalen 1:1 untuk menghitung `items` (jumlah `"1"` diharapkan) dan `item_benar` (jumlah irisan benar).

### Tipe 4 — Isian Singkat (`question_type = 4`)
- **`options` (JSON):** `null`
- **`answer_key` (JSON):**
```json
{
  "exact": "Soekarno",
  "case_sensitive": false
}
```
*(Perbandingan default mengikuti CI3: `mb_strtolower(trim($studentAnswer)) === mb_strtolower(trim($key))`, plus opsi mendukung sinonim/alternatif jawaban terpisah koma jika diinginkan.)*

### Tipe 5 — Esai / Uraian (`question_type = 5`)
- **`options` (JSON):** `null`
- **`answer_key` (JSON):**
```json
{
  "rubric_html": "<p>Jelaskan proses fotosintesis meliputi reaksi terang dan gelap.</p>"
}
```
*(Penilaian utama via koreksi manual guru `corrected_score`, dengan fallback auto-check kesamaan teks persis `strtolower` sesuai CI3 jika belum dikoreksi.)*

### Lampiran Media Soal (`attachments` JSON)
Menggantikan kolom `file` (`serialize()`) pada `cbt_soal`:
```json
[
  {
    "name": "audio_listening_1.mp3",
    "path": "cbt/bank_soal/12/audio_listening_1_a1b2c3.mp3",
    "mime": "audio/mpeg",
    "size": 428190
  }
]
```

---

## 3. Ekstraksi Gambar Base64, KaTeX/Equation, & Import Soal

### A. Ekstraksi Gambar Base64 (`QuestionMediaService`)
Mengadaptasi `Cbtbanksoal` + `DOMDocument` dari CI3 menjadi service yang aman di Laravel:
1. Saat menyimpan `question_html` atau konten HTML di `options` / `answer_key` (seperti gambar di baris/kolom Menjodohkan), `QuestionMediaService::extractBase64Images(string $html, int $bankId, int $type, int $number)` mem-parsing HTML dengan `DOMDocument` (`LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD`).
2. Setiap `<img src="data:image/{ext};base64,...">`:
   - Validasi MIME whitelist (`png`, `jpg`, `jpeg`, `gif`, `webp`; `jpeg` dinormalisasi ke `jpg`).
   - Tolak payload melebihi batas ukuran (mis. 2 MB per gambar).
   - Simpan via `Storage::disk('public')->put("cbt/bank_soal/{$bankId}/img_{$type}_{$number}_{$random}.{$ext}", $binary)`.
   - Ganti atribut `src` menjadi path relatif publik `/storage/cbt/bank_soal/...`.
3. Sanitasi tag berbahaya (`<script>`, `onerror`, `onload`) sebelum disimpan agar aman dari XSS di halaman siswa & guru.
4. Saat soal/bank dihapus, file gambar & attachment terkait ikut dibersihkan dari disk `public`.

### B. Dukungan Rich Text & KaTeX Equation
- Editor soal menggunakan Rich Text Editor ringan (mis. Quill/TinyMCE self-hosted atau Summernote yang ringan) ditambah tombol **Sisipkan Rumus KaTeX** (`katex.min.js` + auto-render `$$...$$` / `\(...\)` lokal di `public/vendor/katex/`) agar rumus matematika, fisika, dan kimia tampil tajam baik saat penginputan oleh guru maupun di Exam Runner siswa tanpa koneksi internet eksternal.

### C. Format Import Soal Excel & Word (`QuestionImportService`)
Mendukung dua jalur import dari halaman **Import Soal** (`Cbtbanksoal::importsoal`):
1. **Import Template Excel (`.xlsx` via `PhpSpreadsheet ^5.10` eksisting):**
   - Menyediakan endpoint download template `.xlsx` berisi sheet panduan & sheet soal dengan kolom terstruktur:
     `No | Jenis (1-5) | Soal | Opsi A | Opsi B | Opsi C | Opsi D | Opsi E | Kunci Jawaban | Kesulitan (1-10) | Tampilkan (1/0)`
   - Mendukung pratinjau sebelum simpan (pola Preview → Publish seperti `AssessmentController` & `TeachingJournalController` di `absensi-laravel`) + ekstraksi gambar tertanam (`getDrawingCollection()`) dari worksheet Excel.
2. **Import Format Word (`.docx` — Kompatibel Template AKM GarudaCBT `format_soal_akm.docx`):**
   - Pada CI3 (`views/cbt/banksoal/import.php`), file `.docx` di-parse di browser menggunakan `mammoth.browser.min.js` menjadi tabel HTML lalu dikirim sebagai payload JSON ke endpoint import (`POST /cbt/manage/bank-soal/{bank}/import-preview`).
   - Di Laravel, pertahankan dukungan parser tabel Word (`.docx`) sehingga bank soal lama dari format `format_soal_akm.docx` GarudaCBT (yang sudah memuat soal tipe 1–5 + gambar inline) dapat langsung di-import tanpa guru harus mengetik ulang, sekaligus gambar base64 hasil konversi `mammoth` otomatis diekstrak ke `Storage::disk('public')` oleh `QuestionMediaService`.
