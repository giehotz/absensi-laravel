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
        Schema::create('ujian_pengawas_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_periode_id')->constrained('ujian_periode')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->json('assigned_levels'); // e.g. ["1", "2", "3"]
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();

            $table->unique(['ujian_periode_id', 'teacher_id'], 'ujian_pengawas_map_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ujian_pengawas_mappings');
    }
};
