# 🏫 Sistem Presensi Digital & Portal Siswa (Absensi Laravel)

Sistem Informasi Manajemen Presensi Sekolah & Portal Siswa Digital berbasis QR Code, dibangun menggunakan **Laravel 12**, **Tailwind CSS v4**, dan konsep antarmuka **Neobrutalism UI**.

Aplikasi ini mencakup multi-peran pengguna:
- 👨‍💼 **Administrator**: Manajemen data master, tahun ajaran, jadwal kelas, template kegiatan, dan laporan presensi.
- 👨‍🏫 **Guru / Wali Kelas**: Presensi kelas, evaluasi & persetujuan izin/sakit siswa, serta catatan pembinaan siswa (BK).
- 🎒 **Siswa**: Portal mobile-first dengan Kartu Pelajar Digital, Live QR Token, Jadwal Pelajaran terintegrasi kegiatan ibadah/non-KBM, pengajuan izin sakit mandiri, dan Buku Tabungan Pelajar (SIMPEL).

---

## 📋 Prasyarat Sistem (System Requirements)

Sebelum melakukan instalasi, pastikan perangkat lokal Anda telah memenuhi spesifikasi perangkat lunak berikut:

### 1. PHP Runtime
- **PHP Version**: Minimal **PHP 8.3** atau **PHP 8.4** (Sangat disarankan PHP 8.4).
- **Ekstensi PHP Wajib Aktif** (cek pada file `php.ini`):
  - `pdo_sqlite` *(jika menggunakan database default SQLite)* atau `pdo_mysql`
  - `gd` *(Wajib untuk pembuatan kode QR gambar & manipulasi foto)*
  - `zip` *(Wajib untuk ekspor/impor spreadsheet Excel)*
  - `fileinfo` *(Wajib untuk validasi berkas upload lampiran izin & foto)*
  - `mbstring`
  - `openssl`
  - `curl`
  - `bcmath`
  - `xml`

> **Tips Memeriksa Versi & Ekstensi:**
> ```bash
> php -v
> php -m | findstr /i "gd zip pdo_sqlite fileinfo"   # Windows PowerShell / CMD
> php -m | grep -E "gd|zip|pdo_sqlite|fileinfo"      # Linux / macOS
> ```

### 2. Dependency Managers & Runtime
- **Composer**: Versi **2.5.0** ke atas ([Unduh Composer](https://getcomposer.org/)).
- **Node.js & NPM**: Minimal **Node.js v18.x** atau **v20.x+ LTS** dan **NPM v9.x+** ([Unduh Node.js](https://nodejs.org/)).
- **Git**: Versi terbaru untuk clone repository.

### 3. Database Engine
- **SQLite 3** *(Bawaan, tanpa konfigurasi server database tambahan — default proyek)*, atau
- **MySQL 8.0+** / **MariaDB 10.4+** *(Opsional)*.

---

## 🚀 Panduan Instalasi (Step-by-Step)

Ikuti langkah-langkah berikut secara berurutan untuk memasang proyek di komputer lokal:

### 1. Clone Repository
Buka terminal (Git Bash / PowerShell / Command Prompt / Terminal), lalu jalankan:

```bash
git clone https://github.com/giehotz/absensi-laravel.git
cd absensi-laravel
```

---

### 2. Pasang Dependensi Backend (Composer)
Unduh seluruh pustaka pihak ketiga PHP:

```bash
composer install
```

---

### 3. Pasang Dependensi Frontend (NPM)
Unduh paket Node.js untuk kompilasi aset Tailwind CSS dan Vite:

```bash
npm install
```

---

### 4. Konfigurasi Environment File
Salin file konfigurasi `.env.example` menjadi `.env`:

**Windows (PowerShell / CMD):**
```powershell
copy .env.example .env
```

**Linux / macOS / Git Bash:**
```bash
cp .env.example .env
```

---

### 5. Generate Application Encryption Key
Buat kunci unik keamanan aplikasi Laravel:

```bash
php artisan key:generate
```

---

### 6. Siapkan Database & Jalankan Migrasi Data Dummy
Secara default, aplikasi menggunakan database **SQLite**.

1. **Buat file database SQLite** (jika belum ada):
   - Windows PowerShell:
     ```powershell
     New-Item -ItemType File -Path database\database.sqlite -Force
     ```
   - Linux / macOS / Git Bash:
     ```bash
     touch database/database.sqlite
     ```

2. **Jalankan Migrasi & Database Seeder**:
   ```bash
   php artisan migrate --seed
   ```
   *Perintah ini akan membuat semua tabel dan mengisi data awal (Tahun Ajaran, Pengaturan Presensi, Mata Pelajaran, Akun Uji Coba Admin, Guru, dan Siswa).*

---

### 7. Buat Symbolic Link Direktori Storage
Hubungkan direktori publik agar foto siswa dan bukti surat izin dapat diakses oleh browser:

```bash
php artisan storage:link
```

---

### 8. Kompilasi Aset Frontend
Kompilasi aset CSS dan JavaScript:

```bash
npm run build
```

> **Untuk Pengembangan (Live Reload):**
> Anda dapat menjalankan `npm run dev` pada terminal terpisah agar perubahan CSS/Blade otomatis diperbarui di browser.

---

### 9. Jalankan Server Aplikasi
Jalankan server lokal Laravel:

```bash
php artisan serve
```

Aplikasi siap diakses melalui browser pada alamat:
👉 **[http://127.0.0.1:8000](http://127.0.0.1:8000)** atau **[http://localhost:8000](http://localhost:8000)**

---

## ⚡ Metode Instalasi Singkat (Otomatis)

Jika Composer dan Node.js sudah terpasang dengan benar, Anda juga dapat menggunakan perintah skrip otomatis yang sudah disiapkan:

```bash
git clone https://github.com/giehotz/absensi-laravel.git
cd absensi-laravel
composer run setup
php artisan db:seed
php artisan storage:link
php artisan serve
```

---

## 🔑 Akun Uji Coba Default (Demo Credentials)

Hasil eksekusi `php artisan migrate --seed` menyediakan akun-akun pengujian berikut untuk masing-masing peran:

| Peran (Role) | Alamat Email | Kata Sandi | Deskripsi |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@sekolah.sch.id` | `password` | Akses penuh dashboard administrasi sekolah |
| **Guru / Wali Kelas** | `guru@sekolah.sch.id` | `password` | Budi Santoso, S.Pd. (Wali Kelas 7A) |
| **Guru Mata Pelajaran** | `siti@sekolah.sch.id` | `password` | Siti Aminah, M.Pd. (Guru IPA) |
| **Siswa (Utama)** | `siswa@sekolah.sch.id` | `password` | Ahmad Fauzi (Kelas 7A, NIS: 12345) |
| **Siswa 2** | `rahma@sekolah.sch.id` | `password` | Siti Rahmawati (Kelas 8B) |

---

## 🛠️ Perintah Berguna (Useful Commands)

| Kebutuhan | Perintah |
| :--- | :--- |
| **Menjalankan Dev Server Sekaligus** (PHP + Vite) | `composer run dev` |
| **Reset Total Database & Seeder Ulang** | `php artisan migrate:fresh --seed` |
| **Menjalankan Unit & Feature Test** | `php artisan test` |
| **Format Standar Kode PHP (Laravel Pint)** | `vendor/bin/pint --format agent` |
| **Membersihkan Cache Aplikasi** | `php artisan optimize:clear` |
| **Melihat Daftar Rute URL** | `php artisan route:list` |

---

## 🌐 Panduan Deployment ke Hosting Langsung

Aplikasi ini dapat di-deploy ke berbagai jenis hosting, baik **Shared Hosting (cPanel / DirectAdmin)** maupun **Cloud Server / VPS**.

> ⚠️ **Catatan Penting Keamanan & SSL:**
> Fitur kamera pemindai QR dan Progressive Web App (PWA) **mewajibkan protokol HTTPS**. Pastikan SSL/HTTPS sudah aktif di domain hosting Anda (misalnya melalui Let's Encrypt atau AutoSSL cPanel).

---

### Opsi A: Deployment ke Shared Hosting / cPanel (Paling Populer)

Metode ini memisahkan berkas inti aplikasi (core) agar berada di luar `public_html` demi keamanan database dan file `.env`.

#### 1. Persiapan Berkas di Komputer Lokal
Sebelum mengunggah, kompilasi aset frontend dan bersihkan dependensi dev:

```bash
# 1. Kompilasi aset frontend untuk production
npm run build

# 2. Pasang dependensi PHP tanpa require-dev untuk mempercepat performa
composer install --optimize-autoloader --no-dev
```

Kompres seluruh isi folder proyek menjadi berkas `.zip` (kecuali folder `node_modules`).

---

#### 2. Konfigurasi Versi & Ekstensi PHP di cPanel
1. Buka cPanel ➔ masuk ke menu **Select PHP Version** (atau **MultiPHP Manager**).
2. Pilih versi **PHP 8.3** atau **PHP 8.4**.
3. Di tab **Extensions**, pastikan ekstensi berikut sudah dicentang/aktif:
   - `pdo_mysql`
   - `gd` *(Wajib untuk QR Code & Pengolahan Gambar)*
   - `zip` *(Wajib untuk impor/ekspor Excel)*
   - `fileinfo`
   - `mbstring`
   - `openssl`
   - `curl`
   - `bcmath`
   - `xml`

---

#### 3. Unggah & Tata Struktur Direktori (Best Practice Keamanan)
1. Buka menu **File Manager** di cPanel.
2. Buat folder baru di luar `public_html`, misalnya bernama `absensi-core` (path: `/home/username/absensi-core`).
3. Ekstrak file zip proyek Anda ke dalam folder `/home/username/absensi-core`.
4. Pindahkan **seluruh isi** yang ada di dalam folder `/home/username/absensi-core/public/` ke dalam folder `/home/username/public_html/`.
5. Buka dan edit berkas `/home/username/public_html/index.php`, sesuaikan path autoload dan bootstrap (sekitar baris 19 dan 34):

```php
// Ganti:
// require __DIR__.'/../vendor/autoload.php';
// $app = require_once __DIR__.'/../bootstrap/app.php';

// Menjadi:
require __DIR__.'/../absensi-core/vendor/autoload.php';
$app = require_once __DIR__.'/../absensi-core/bootstrap/app.php';
```

---

#### 4. Pembuatan Database MySQL di cPanel
1. Buka cPanel ➔ masuk ke menu **MySQL Databases**.
2. Buat database baru, misalnya: `usercpanel_absensidb`.
3. Buat pengguna database baru beserta kata sandi yang kuat, misalnya: `usercpanel_dbuser`.
4. Tambahkan pengguna tersebut ke database dengan memberikan hak akses penuh (**ALL PRIVILEGES**).

---

#### 5. Konfigurasi Berkas `.env` di Hosting
Buka file `/home/username/absensi-core/.env` di File Manager cPanel, sesuaikan nilainya:

```env
APP_NAME="Absensi Siswa"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://namadomainsekolah.sch.id

# Konfigurasi Database MySQL Hosting
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=usercpanel_absensidb
DB_USERNAME=usercpanel_dbuser
DB_PASSWORD=PasswordKuatAnda

# Session & Cache
SESSION_DRIVER=database
CACHE_STORE=database
```

---

#### 6. Jalankan Migrasi & Seeder di Hosting
- **Cara 1 (Jika ada menu Terminal di cPanel):**
  ```bash
  cd ~/absensi-core
  php artisan migrate --seed --force
  ```
- **Cara 2 (Jika tidak ada akses Terminal):**
  Ekspor database lokal Anda dari phpMyAdmin lokal / SQLite, lalu impor berkas `.sql` ke dalam database hosting melalui menu **phpMyAdmin** di cPanel.

---

#### 7. Membuat Symbolic Link Storage di Hosting
Agar berkas foto siswa dan lampiran izin dapat diakses publik:

- **Jika ada menu Terminal cPanel:**
  ```bash
  ln -s /home/username/absensi-core/storage/app/public /home/username/public_html/storage
  ```
- **Alternatif via Script PHP Sementara:**
  Buat file `link.php` di dalam `public_html` dengan isi:
  ```php
  <?php
  symlink('/home/username/absensi-core/storage/app/public', __DIR__.'/storage');
  echo "Storage linked successfully!";
  ```
  Akses `https://namadomainsekolah.sch.id/link.php` sekali di browser, lalu **segera hapus file `link.php`** tersebut.

---

#### 8. Optimasi Performa Production (Cache Configuration)
Jalankan perintah berikut melalui Terminal cPanel untuk mempercepat loading aplikasi:

```bash
cd ~/absensi-core
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

#### 9. Pengaturan Cron Job Otomatis (Scheduler Presensi)
Agar tugas otomatis (seperti penutupan sesi presensi harian atau reset token) berjalan tepat waktu:
1. Buka cPanel ➔ masuk ke menu **Cron Jobs**.
2. Pada bagian waktu, pilih **Once Per Minute** (`* * * * *`).
3. Masukkan perintah:
   ```bash
   /usr/local/bin/php /home/username/absensi-core/artisan schedule:run >> /dev/null 2>&1
   ```

---

### Opsi B: Deployment ke VPS / Cloud Server (Ubuntu / Debian + Nginx)

Jika Anda menggunakan VPS (seperti DigitalOcean, Linode, AWS EC2, atau VPS lokal):

1. **Clone & Atur Izin Berkas:**
   ```bash
   cd /var/www
   git clone https://github.com/giehotz/absensi-laravel.git
   cd absensi-laravel
   composer install --optimize-autoloader --no-dev
   npm install && npm run build
   cp .env.example .env
   php artisan key:generate
   php artisan storage:link

   # Atur ownership web server
   sudo chown -R www-data:www-data storage bootstrap/cache
   sudo chmod -R 775 storage bootstrap/cache
   ```

2. **Konfigurasi Virtual Host Nginx (`/etc/nginx/sites-available/absensi`):**
   ```nginx
   server {
       listen 80;
       server_name absensi.namasekolah.sch.id;
       root /var/www/absensi-laravel/public;

       add_header X-Frame-Options "SAMEORIGIN";
       add_header X-Content-Type-Options "nosniff";

       index index.php;
       charset utf-8;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location = /favicon.ico { access_log off; log_not_found off; }
       location = /robots.txt  { access_log off; log_not_found off; }

       error_page 404 /index.php;

       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
           fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
           include fastcgi_params;
       }

       location ~ /\.(?!well-known).* {
           deny all;
       }
   }
   ```

3. **Pasang SSL Gratis dengan Certbot:**
   ```bash
   sudo certbot --nginx -d absensi.namasekolah.sch.id
   ```

---

## ❓ Solusi Masalah Umum (Troubleshooting)

1. **`Call to undefined function imagecreatefromjpeg()` atau kendala QR Code:**
   - **Penyebab**: Ekstensi `gd` pada PHP belum aktif.
   - **Solusi**: Buka file `php.ini`, cari baris `;extension=gd`, hapus tanda titik koma (`;`) di depannya sehingga menjadi `extension=gd`, lalu restart server/terminal Anda.

2. **`Vite manifest not found`:**
   - **Penyebab**: Berkas build frontend belum dikompilasi.
   - **Solusi**: Jalankan perintah `npm run build` atau biarkan `npm run dev` aktif di terminal.

3. **Gambar foto profil / lampiran tidak tampil (404 Not Found):**
   - **Penyebab**: Symbolic link storage belum dibuat.
   - **Solusi**: Jalankan `php artisan storage:link`.

4. **Izin folder `storage` & `bootstrap/cache` di Linux / macOS:**
   - **Solusi**:
     ```bash
     chmod -R 775 storage bootstrap/cache
     ```

---

## 📄 Lisensi

Aplikasi ini dilisensikan di bawah lisensi open-source [MIT License](LICENSE).
