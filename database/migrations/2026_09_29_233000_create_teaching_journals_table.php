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
        Schema::create('teaching_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained('schedules')->nullOnDelete();
            $table->date('date');
            $table->string('day_name', 20); // e.g. "Senin", "Selasa"
            $table->unsignedInteger('meeting_number')->default(1); // Pertemuan Ke-
            $table->text('learning_objective'); // Tujuan Pembelajaran
            $table->text('teaching_activity'); // Kegiatan Belajar Mengajar
            $table->text('teaching_problem')->nullable(); // Permasalahan Dalam Proses KBM
            $table->unsignedSmallInteger('total_students')->default(0);
            $table->unsignedSmallInteger('count_hadir')->default(0);
            $table->unsignedSmallInteger('count_sakit')->default(0);
            $table->unsignedSmallInteger('count_izin')->default(0);
            $table->unsignedSmallInteger('count_alpa')->default(0);
            $table->unsignedSmallInteger('count_terlambat')->default(0);
            $table->decimal('attendance_percentage', 5, 2)->default(0.00); // 0.00 - 100.00 %
            $table->boolean('is_shared_with_students')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for fast searching & filtering
            $table->index(['teacher_id', 'date']);
            $table->index(['school_class_id', 'date']);
            $table->index(['subject_id', 'school_class_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teaching_journals');
    }
};
