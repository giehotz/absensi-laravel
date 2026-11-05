<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Master Jenis Asesmen Kurikulum Merdeka
        Schema::create('ujian_jenis', function (Blueprint $table) {
            $table->id();
            $table->string('kode_jenis', 30)->unique();
            $table->string('nama_jenis', 150);
            $table->string('kategori_kurikulum', 100); // Formatif, Sumatif Lingkup Materi, SAS, SAT, UM, dll.
            $table->string('fase_sasaran', 50)->default('Semua Fase'); // Fase A, B, C, Semua Fase
            $table->string('judul_kop_kartu', 150)->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });

        // 2. Periode Pelaksanaan Ujian
        Schema::create('ujian_periode', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_jenis_id')->constrained('ujian_jenis')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->string('nama_periode', 150);
            $table->string('tahun_pelajaran', 30);
            $table->string('semester', 20)->default('Ganjil'); // Ganjil / Genap
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->string('tempat_cetak', 100)->default('Tanggamus');
            $table->date('tanggal_cetak_kartu')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Penugasan Guru sebagai Panitia Ujian
        Schema::create('ujian_panitia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_periode_id')->constrained('ujian_periode')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->string('jabatan', 50); // ketua, sekretaris, bendahara, anggota
            $table->string('sk_nomor', 100)->nullable();
            $table->string('keterangan', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['ujian_periode_id', 'teacher_id']);
        });

        // 4. Master Ruang Ujian
        Schema::create('ujian_ruang', function (Blueprint $table) {
            $table->id();
            $table->string('kode_ruang', 30)->unique();
            $table->string('nama_ruang', 100);
            $table->integer('kapasitas_maksimal')->default(20);
            $table->string('lokasi_gedung', 100)->nullable();
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();
        });

        // 5. Jadwal Pelaksanaan Ujian
        Schema::create('ujian_jadwal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_periode_id')->constrained('ujian_periode')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->string('nama_mapel_kustom', 150)->nullable();
            $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->string('tingkat', 30)->nullable(); // e.g. '1', '2', ..., 'Semua Tingkat'
            $table->date('tanggal_ujian');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->integer('sesi_ke')->default(1);
            $table->integer('durasi_menit')->default(90);
            $table->string('jenis_soal', 50)->default('Tertulis');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // 6. Penugasan Pengawas Ruang
        Schema::create('ujian_jadwal_pengawas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_jadwal_id')->constrained('ujian_jadwal')->cascadeOnDelete();
            $table->foreignId('ujian_ruang_id')->constrained('ujian_ruang')->cascadeOnDelete();
            $table->foreignId('pengawas_1_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->foreignId('pengawas_2_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->string('status_kehadiran', 30)->default('Terjadwal'); // Terjadwal, Hadir, Digantikan, Izin
            $table->string('pengawas_pengganti', 150)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // 7. Peserta Ujian & Penomoran Kartu
        Schema::create('ujian_peserta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_periode_id')->constrained('ujian_periode')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->foreignId('ujian_ruang_id')->nullable()->constrained('ujian_ruang')->nullOnDelete();
            $table->integer('nomor_kursi')->nullable();
            $table->integer('sesi')->default(1);
            $table->string('nis_lokal', 50)->nullable();
            $table->string('nisn', 50)->nullable();
            $table->string('nama_peserta', 150);
            $table->string('jenis_kelamin', 10)->nullable();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('nama_kelas', 50);
            $table->string('nomor_peserta', 50);
            $table->string('foto_peserta_path', 255)->nullable();
            $table->boolean('is_eligible')->default(true);
            $table->timestamps();

            $table->index(['ujian_periode_id', 'nomor_peserta']);
            $table->index(['ujian_periode_id', 'school_class_id']);
        });

        // 8. Pengaturan Kop, Tanda Tangan, Stempel & Watermark Kartu
        Schema::create('ujian_pengaturan_ttd', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_periode_id')->nullable()->constrained('ujian_periode')->cascadeOnDelete();
            $table->string('nama_instansi', 150)->default('MIN 2 TANGGAMUS');
            $table->string('kop_baris_1', 150)->default('KARTU PESERTA');
            $table->string('kop_baris_2', 150)->nullable();
            $table->string('kop_baris_3', 150)->default('MIN 2 TANGGAMUS');
            $table->string('kop_baris_4', 150)->nullable();
            $table->string('jabatan_penandatangan', 100)->default('Kepala Madrasah');
            $table->string('nama_kepala', 150)->default('Sipulloh, M.Pd');
            $table->string('nip_kepala', 50)->nullable()->default('197005272007011022');
            $table->string('tempat_penetapan', 100)->default('Tanggamus');
            $table->date('tanggal_penetapan')->nullable();
            $table->string('path_logo_kiri', 255)->nullable();
            $table->string('path_watermark', 255)->nullable();
            $table->decimal('opacity_watermark', 3, 2)->default(0.35);
            $table->string('path_gambar_ttd', 255)->nullable();
            $table->string('path_stempel', 255)->nullable();
            $table->decimal('lebar_ttd_cm', 4, 2)->default(3.00);
            $table->decimal('tinggi_ttd_cm', 4, 2)->default(3.00);
            $table->decimal('offset_atas_ttd_cm', 4, 2)->default(0.00);
            $table->decimal('offset_kanan_ttd_cm', 4, 2)->default(0.00);
            $table->boolean('tampilkan_stempel')->default(true);
            $table->boolean('tampilkan_qrcode_validasi')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ujian_pengaturan_ttd');
        Schema::dropIfExists('ujian_peserta');
        Schema::dropIfExists('ujian_jadwal_pengawas');
        Schema::dropIfExists('ujian_jadwal');
        Schema::dropIfExists('ujian_ruang');
        Schema::dropIfExists('ujian_panitia');
        Schema::dropIfExists('ujian_periode');
        Schema::dropIfExists('ujian_jenis');
    }
};
