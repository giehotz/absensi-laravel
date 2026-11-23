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
        Schema::create('ujian_berita_acara', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_periode_id')->constrained('ujian_periode')->cascadeOnDelete();
            $table->foreignId('ujian_jadwal_id')->constrained('ujian_jadwal')->cascadeOnDelete();
            $table->foreignId('ujian_ruang_id')->constrained('ujian_ruang')->cascadeOnDelete();
            $table->foreignId('pengawas_pengisi_id')->constrained('teachers')->cascadeOnDelete();
            $table->integer('peserta_seharusnya');
            $table->integer('peserta_hadir');
            $table->integer('peserta_tidak_hadir');
            $table->integer('naskah_soal_diterima')->default(0);
            $table->integer('naskah_soal_digunakan')->default(0);
            $table->integer('naskah_soal_sisa')->default(0);
            $table->integer('naskah_soal_rusak')->default(0);
            $table->text('catatan_kejadian')->nullable();
            $table->json('daftar_absen_siswa')->nullable();
            $table->string('status', 30)->default('submitted'); // submitted, verified
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['ujian_jadwal_id', 'ujian_ruang_id'], 'uq_berita_acara_jadwal_ruang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ujian_berita_acara');
    }
};
