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
        Schema::create('ujian_periode_kelompok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_periode_id')->constrained('ujian_periode')->cascadeOnDelete();
            $table->string('kelompok', 20); // 'bawah' | 'atas'
            $table->json('tingkat_list'); // ["1","2"] | ["3","4","5","6"]
            $table->string('mode_jadwal', 20); // 'per_rombel' | 'per_tingkat'
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->boolean('kecualikan_minggu')->default(true);
            $table->json('tanggal_libur')->nullable();
            $table->unsignedTinyInteger('jumlah_sesi_per_hari')->default(2);
            $table->unsignedTinyInteger('sesi_hari_terakhir')->default(1);
            $table->json('jam_sesi')->nullable();
            $table->timestamps();

            $table->unique(['ujian_periode_id', 'kelompok'], 'ujian_periode_kelompok_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ujian_periode_kelompok');
    }
};
