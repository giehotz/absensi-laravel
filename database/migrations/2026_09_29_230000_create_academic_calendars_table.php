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
        Schema::create('academic_calendars', function (Blueprint $table) {
            $table->id();
            $table->string('academic_year_name', 50)->index(); // Contoh: '2026/2027'
            $table->enum('semester', ['ganjil', 'genap'])->index();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('day_name', 100); // e.g. "Senin" atau "Senin - Rabu"
            $table->string('description', 500);
            $table->string('category', 50)->default('kegiatan'); // kegiatan, libur, ujian, rapat, umum
            $table->string('color', 30)->nullable();
            $table->timestamps();

            $table->index(['academic_year_name', 'semester', 'start_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_calendars');
    }
};
