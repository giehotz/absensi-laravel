# Rencana Pengembangan: Kios Scanner Presensi QR Siswa (Webcam & USB Barcode)

Dokumen ini berisi spesifikasi teknis dan rencana implementasi fitur **Kios Scanner Presensi QR Siswa** yang dirancang berdasarkan hasil diskusi dan kebutuhan sistem absensi madrasah/sekolah.

---

## 1. Ikhtisar Fitur (Overview)

Fitur ini berfungsi sebagai stasiun/pos presensi mandiri di gerbang atau pintu masuk madrasah. Siswa menempelkan kartu tanda pelajar (yang sudah memuat kode QR identitas) ke arah kamera laptop/HP atau menggunakan scanner tembak USB barcode. Sistem secara otomatis mencatat kehadiran, mengecek keterlambatan, memberikan salam audio, dan memperbarui riwayat kehadiran secara real-time.

---

## 2. Spesifikasi Kebutuhan & Desain Teknis

### A. Metode Pemindaian (Dual-Mode Input)
1. **Kamera Webcam / Laptop / Smartphone**:
   - Memanfaatkan library browser mandiri: [`public/vendor/html5-qrcode/html5-qrcode.min.js`](file:///d:/absensi-laravel/public/vendor/html5-qrcode/html5-qrcode.min.js) (sudah tersimpan secara lokal di proyek dan dapat berjalan 100% offline).
   - Dilengkapi pemilihan perangkat kamera (kamera depan / belakang / USB webcam eksternal).
2. **USB Barcode / QR Scanner Tembak (Hardware Scanner)**:
   - Kotak input *auto-focus continuous listening* yang otomatis siap menerima input teks kode QR/Barcode dari alat scanner fisik tanpa perlu menyalakan kamera video.

### B. Logika Sesi Masuk vs Pulang
1. **Otomatis Berdasarkan Waktu**:
   - Jam < 11:00 WIB: Default sesi **Presensi Masuk**.
   - Jam ≥ 11:00 WIB: Default sesi **Presensi Pulang**.
2. **Manual Override**:
   - Tombol toggle mode pada layar: `[⚡ Otomatis]`, `[🟢 Presensi Masuk]`, `[🔵 Presensi Pulang]`.
3. **Kalkulasi Keterlambatan**:
   - Membandingkan waktu scan dengan `school_start_time` (contoh: 07:00) ditambah `tolerance_minutes` (contoh: 15 menit) dari tabel `attendance_settings`.
   - Jika scan $\le$ 07:15 $\rightarrow$ Status: **`hadir`**.
   - Jika scan $>$ 07:15 $\rightarrow$ Status: **`terlambat`** (mencatat catatan keterlambatan, misal: *Terlambat 12 menit*).
4. **Validasi Anti-Duplikasi**:
   - Jika siswa sudah tercatat masuk pada hari tersebut dan memindai lagi pada sesi masuk, sistem menampilkan status peringatan: *"Sudah tercatat masuk pada pukul HH:MM"*.
   - Saat sesi kepulangan, sistem memperbarui kolom `check_out_time`.

### C. Feedback Interaktif & Multimedia
1. **Audio Synth Beep**:
   - Menggunakan Web Audio API murni (nada tinggi ceria untuk sukses, nada ganda untuk peringatan/gagal).
2. **Text-to-Speech (TTS) Bahasa Indonesia**:
   - Menggunakan browser Web Speech API (`window.speechSynthesis`) dengan logat Indonesia (`id-ID`).
   - Contoh ucapan: *"Selamat pagi [Nama Siswa], kehadiran tercatat tepat waktu."* atau *"Selamat siang [Nama Siswa], presensi kepulangan tercatat."*
3. **Kartu Identitas Terpindai (Visual Pop-up/Card)**:
   - Menampilkan foto siswa, nama lengkap, NIS/NISN, kelas, badge status warna Neobrutalism (hijau/kuning/merah), dan jam presensi.
4. **Live Feed Riwayat Harian**:
   - Tabel 10 pemindaian terakhir yang diperbarui secara dinamis tanpa me-reload halaman.
5. **Counter Statistik Real-Time**:
   - Total Hadir Tepat Waktu, Terlambat, dan Pulang pada hari berjalan.

### D. Hak Akses & Penempatan Menu
- **Administrator**: Rute `/admin/attendances/scanner` (di Sidebar grup *Presensi & Laporan*).
- **Guru / Guru Piket**: Rute `/guru/scanner` (di Sidebar guru untuk petugas piket gerbang).

---

## 3. Rincian File & Arsitektur yang Akan Dibuat / Dimodifikasi

### A. Controller
- **`app/Http/Controllers/AttendanceScannerController.php`**:
  - `index(Request $request)`: Menampilkan antarmuka scanner, ringkasan statistik hari ini, konfigurasi jam masuk & toleransi dari `AttendanceSetting`, serta daftar riwayat scan hari ini.
  - `scan(Request $request)`: Menerima payload POST `qr_content` dan `mode`:
    1. Mencari siswa berdasarkan `qr_code_identifier`, token dinamis `QrToken`, atau `nis`/`nisn`.
    2. Menentukan sesi masuk atau pulang.
    3. Menyimpan/memperbarui data ke tabel `attendances`.
    4. Mengembalikan respons JSON lengkap (data siswa, status, waktu, dan teks ucapan TTS).

### B. Routing
- **`routes/admin.php`**:
  ```php
  Route::get('attendances/scanner', [AttendanceScannerController::class, 'index'])->name('attendances.scanner');
  Route::post('attendances/scanner/process', [AttendanceScannerController::class, 'scan'])->name('attendances.scanner.process');
  ```
- **`routes/guru.php`**:
  ```php
  Route::get('scanner', [AttendanceScannerController::class, 'index'])->name('scanner');
  Route::post('scanner/process', [AttendanceScannerController::class, 'scan'])->name('scanner.process');
  ```

### C. Views
- **`resources/views/admin/attendances/scanner.blade.php`**:
  - Halaman antarmuka Kios Scanner bergaya Neobrutalism responsif.
  - Memuat script lokal `public/vendor/html5-qrcode/html5-qrcode.min.js`.
  - Panel tombol mode, viewport kamera, kartu hasil scan, audio synthesizer, dan tabel live feed.
- **`resources/views/layouts/admin.blade.php`**:
  - Menambahkan item menu `Kios Scanner QR` di bawah grup *Presensi & Laporan*.
- **`resources/views/layouts/guru.blade.php`**:
  - Menambahkan item menu `Kios Scanner QR` untuk guru piket.

### D. Automated Testing
- **`tests/Feature/AttendanceScannerTest.php`**:
  - Menguji aksesibilitas rute untuk admin dan guru.
  - Menguji pemindaian QR masuk tepat waktu (`hadir`).
  - Menguji pemindaian QR masuk terlambat (`terlambat`).
  - Menguji pemindaian QR pulang (`check_out_time`).
  - Menguji penolakan scan ganda pada sesi yang sama.
  - Menguji respons jika kode QR tidak dikenali/invalid.

---

## 4. Status Rencana
- **Status Saat Ini**: Disimpan sebagai referensi arsitektur di dokumentasi proyek.
- **Aset Pendukung**: Library `html5-qrcode.min.js` sudah siap di `public/vendor/html5-qrcode/`.
- **Rekomendasi Waktu Eksekusi**: Dapat diimplementasikan kapan saja ketika pihak madrasah/sekolah siap mengoperasikan pos pemindaian kartu di gerbang sekolah.
