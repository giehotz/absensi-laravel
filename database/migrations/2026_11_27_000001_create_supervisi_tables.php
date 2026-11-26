<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supervisi_supervisors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->unique()->constrained('teachers')->cascadeOnDelete();
            $table->string('jabatan', 100)->nullable();
            $table->date('tanggal_penugasan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('supervisi_jenis_penilaian', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kode', 50)->unique();
            $table->string('kategori_supervisi', 50)->default('reguler'); // reguler, observasi_kelas_pmm, perangkat_ajar
            $table->string('dimensi_pmm', 50)->nullable(); // manajemen_kelas, dukungan_psikologis, metode_pembelajaran
            $table->unsignedTinyInteger('skor_maksimal')->default(4);
            $table->json('kategori_skor')->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('supervisi_aspek_penilaian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_penilaian_id')->constrained('supervisi_jenis_penilaian')->cascadeOnDelete();
            $table->string('nama_aspek');
            $table->string('fokus_perilaku')->nullable();
            $table->text('perilaku_dianjurkan')->nullable();
            $table->text('perilaku_dihindari')->nullable();
            $table->string('tipe_skala', 30)->default('skor_1_4'); // skor_1_4, perilaku_pmm_1_3
            $table->unsignedSmallInteger('urutan')->default(1);
            $table->timestamps();

            $table->index(['jenis_penilaian_id', 'urutan']);
        });

        Schema::create('supervisi_jadwal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('supervisor_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->foreignId('supervisor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('indikator_pmm_id')->nullable()->constrained('supervisi_jenis_penilaian')->nullOnDelete();
            $table->date('tanggal');
            $table->string('hari', 15)->nullable();
            $table->string('jam_ke', 10)->nullable();
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->string('materi')->nullable();
            $table->string('status', 30)->default('terjadwal');
            $table->decimal('nilai_akhir', 5, 2)->nullable();
            $table->string('ketercapaian', 30)->nullable();
            $table->dateTime('tanggal_pra_observasi')->nullable();
            $table->dateTime('tanggal_pasca_observasi')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['academic_year_id', 'tanggal']);
            $table->index(['supervisor_teacher_id', 'tanggal']);
            $table->index(['teacher_id', 'status']);
        });

        Schema::create('supervisi_pra_observasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->unique()->constrained('supervisi_jadwal')->cascadeOnDelete();
            $table->json('fokus_perilaku_dipilih')->nullable();
            $table->text('upaya_mempelajari')->nullable();
            $table->text('tujuan_pembelajaran_sesi')->nullable();
            $table->text('area_pengembangan')->nullable();
            $table->text('strategi_persiapan')->nullable();
            $table->text('catatan_supervisor_pra')->nullable();
            $table->string('status_kesepakatan', 30)->default('draft_guru'); // draft_guru, diajukan, disepakati
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('disepakati_pada')->nullable();
            $table->timestamps();
        });

        Schema::create('supervisi_dokumen_ajar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->constrained('supervisi_jadwal')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->string('nama_dokumen');
            $table->string('kategori_dokumen', 50)->default('modul_ajar');
            $table->string('fase', 5)->nullable();
            $table->text('tujuan_pembelajaran')->nullable();
            $table->string('strategi_diferensiasi')->nullable();
            $table->string('link_drive', 500)->nullable();
            $table->string('link_eksternal', 500)->nullable();
            $table->string('file_path')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('status', 20)->default('pending'); // pending, disetujui, ditolak
            $table->text('feedback')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();

            $table->index(['jadwal_id', 'status']);
        });

        Schema::create('supervisi_hasil', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->constrained('supervisi_jadwal')->cascadeOnDelete();
            $table->foreignId('jenis_penilaian_id')->constrained('supervisi_jenis_penilaian')->cascadeOnDelete();
            $table->unsignedInteger('total_skor')->default(0);
            $table->unsignedInteger('skor_maksimal')->default(0);
            $table->decimal('nilai_akhir', 5, 2)->default(0);
            $table->string('ketercapaian', 30)->nullable();
            $table->text('rekomendasi')->nullable();
            $table->timestamps();

            $table->unique(['jadwal_id', 'jenis_penilaian_id']);
        });

        Schema::create('supervisi_detail_hasil', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hasil_id')->constrained('supervisi_hasil')->cascadeOnDelete();
            $table->foreignId('aspek_id')->constrained('supervisi_aspek_penilaian')->cascadeOnDelete();
            $table->unsignedTinyInteger('skor');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['hasil_id', 'aspek_id']);
        });

        Schema::create('supervisi_foto_bukti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->constrained('supervisi_jadwal')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('keterangan')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('supervisi_tindak_lanjut', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->unique()->constrained('supervisi_jadwal')->cascadeOnDelete();
            $table->text('refleksi_guru')->nullable();
            $table->string('pilihan_belajar_pmm')->nullable();
            $table->text('pilihan_belajar_lainnya')->nullable();
            $table->text('tujuan_tindak_lanjut')->nullable();
            $table->text('upaya_tindak_lanjut')->nullable();
            $table->string('periode_tindak_lanjut', 100)->nullable();
            $table->string('kebutuhan_dukungan', 100)->nullable();
            $table->text('detail_dukungan')->nullable();
            $table->string('respon_supervisor_c', 100)->nullable();
            $table->text('catatan_supervisor_c')->nullable();
            $table->text('inspirasi_baru')->nullable();
            $table->text('perubahan_praktik')->nullable();
            $table->text('kendala_tindak_lanjut')->nullable();
            $table->text('rencana_perbaikan_berkelanjutan')->nullable();
            $table->string('penilaian_supervisor_d', 100)->nullable();
            $table->text('catatan_supervisor_d')->nullable();
            $table->string('status_tindak_lanjut', 40)->default('belum_dimulai');
            $table->timestamps();
        });

        Schema::create('supervisi_coaching_tirta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->constrained('supervisi_jadwal')->cascadeOnDelete();
            $table->string('tahap', 30)->default('pra_observasi'); // pra_observasi, pasca_observasi
            $table->text('tujuan_coaching')->nullable();
            $table->text('identifikasi_kondisi')->nullable();
            $table->text('rencana_aksi')->nullable();
            $table->text('tanggung_jawab')->nullable();
            $table->timestamps();

            $table->index(['jadwal_id', 'tahap']);
        });

        Schema::create('supervisi_dokumen_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->constrained('supervisi_jadwal')->cascadeOnDelete();
            $table->string('dokumen_type', 50)->default('supervisi');
            $table->string('signer_role', 20); // guru, supervisor, kepala
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('signer_nama', 150);
            $table->string('signer_nip', 50)->nullable();
            $table->string('token_verifikasi', 64)->unique();
            $table->date('tanggal_ttd');
            $table->dateTime('signed_at');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->unique(['jadwal_id', 'dokumen_type', 'signer_role'], 'supervisi_signatures_unique_role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervisi_dokumen_signatures');
        Schema::dropIfExists('supervisi_coaching_tirta');
        Schema::dropIfExists('supervisi_tindak_lanjut');
        Schema::dropIfExists('supervisi_foto_bukti');
        Schema::dropIfExists('supervisi_detail_hasil');
        Schema::dropIfExists('supervisi_hasil');
        Schema::dropIfExists('supervisi_dokumen_ajar');
        Schema::dropIfExists('supervisi_pra_observasi');
        Schema::dropIfExists('supervisi_jadwal');
        Schema::dropIfExists('supervisi_aspek_penilaian');
        Schema::dropIfExists('supervisi_jenis_penilaian');
        Schema::dropIfExists('supervisi_supervisors');
    }
};
