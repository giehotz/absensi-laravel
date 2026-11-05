<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ujian_sesi', function (Blueprint $table) {
            $table->id();
            $table->integer('sesi_ke')->unique();
            $table->string('nama_sesi', 100);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->integer('durasi_menit')->default(120);
            $table->string('keterangan', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed data bawaan: Sesi 1 (07:30 - 09:30) & Sesi 2 (10:00 - 12:00)
        DB::table('ujian_sesi')->insert([
            [
                'sesi_ke' => 1,
                'nama_sesi' => 'Sesi 1',
                'jam_mulai' => '07:30:00',
                'jam_selesai' => '09:30:00',
                'durasi_menit' => 120,
                'keterangan' => 'Sesi Pagi Utama',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sesi_ke' => 2,
                'nama_sesi' => 'Sesi 2',
                'jam_mulai' => '10:00:00',
                'jam_selesai' => '12:00:00',
                'durasi_menit' => 120,
                'keterangan' => 'Sesi Siang',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ujian_sesi');
    }
};
