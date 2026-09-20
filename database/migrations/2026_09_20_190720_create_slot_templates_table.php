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
        Schema::create('slot_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 1 = Senin, ..., 7 = Minggu
            $table->unsignedTinyInteger('jam_ke'); // 1 s.d 12
            $table->unsignedTinyInteger('k_jadwal')->default(0); // 0 = KBM, 3 = Upacara, 4 = Istirahat, 5 = Senam, 6 = Pembiasaan, 7 = Religi
            $table->string('name')->nullable(); // misal 'Upacara Bendera', 'Istirahat 1', 'KBM Jam ke-1'
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();

            $table->unique(['academic_year_id', 'day_of_week', 'jam_ke'], 'unique_slot_per_day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slot_templates');
    }
};
