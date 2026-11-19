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
        Schema::create('ujian_pengawas_swap_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_periode_id')->constrained('ujian_periode')->cascadeOnDelete();
            $table->foreignId('source_pengawas_id')->nullable()->constrained('ujian_jadwal_pengawas')->nullOnDelete();
            $table->foreignId('target_pengawas_id')->nullable()->constrained('ujian_jadwal_pengawas')->nullOnDelete();
            $table->foreignId('teacher_1_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('teacher_2_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('jadwal_1_id')->constrained('ujian_jadwal')->cascadeOnDelete();
            $table->foreignId('jadwal_2_id')->constrained('ujian_jadwal')->cascadeOnDelete();
            $table->string('slot_position_1', 30)->default('pengawas_1');
            $table->string('slot_position_2', 30)->default('pengawas_1');
            $table->text('alasan')->nullable();
            $table->foreignId('processed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 50)->default('Berhasil');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ujian_pengawas_swap_histories');
    }
};
