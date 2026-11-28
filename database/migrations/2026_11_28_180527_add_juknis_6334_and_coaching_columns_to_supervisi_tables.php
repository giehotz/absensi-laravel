<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supervisi_supervisors', function (Blueprint $table): void {
            $table->string('nomor_sk', 100)->nullable()->after('jabatan');
        });

        Schema::table('supervisi_jadwal', function (Blueprint $table): void {
            $table->string('pendekatan', 30)->default('kolaboratif')->after('materi');
            $table->string('teknik_supervisi', 40)->default('kunjungan_kelas')->after('pendekatan');
            $table->string('sumber_slot', 35)->default('manual')->after('teknik_supervisi');
        });

        Schema::table('supervisi_detail_hasil', function (Blueprint $table): void {
            $table->text('rekomendasi')->nullable()->after('catatan');
        });

        Schema::table('supervisi_coaching_tirta', function (Blueprint $table): void {
            $table->text('catatan_coach')->nullable()->after('tanggung_jawab');
            $table->text('catatan_guru')->nullable()->after('catatan_coach');
            $table->string('status', 20)->default('draft')->after('catatan_guru');
            $table->timestamp('submitted_at')->nullable()->after('status');
            $table->timestamp('guru_confirmed_at')->nullable()->after('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::table('supervisi_coaching_tirta', function (Blueprint $table): void {
            $table->dropColumn(['catatan_coach', 'catatan_guru', 'status', 'submitted_at', 'guru_confirmed_at']);
        });

        Schema::table('supervisi_detail_hasil', function (Blueprint $table): void {
            $table->dropColumn('rekomendasi');
        });

        Schema::table('supervisi_jadwal', function (Blueprint $table): void {
            $table->dropColumn(['pendekatan', 'teknik_supervisi', 'sumber_slot']);
        });

        Schema::table('supervisi_supervisors', function (Blueprint $table): void {
            $table->dropColumn('nomor_sk');
        });
    }
};
