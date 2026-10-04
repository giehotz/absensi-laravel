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
        Schema::create('assessment_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedTinyInteger('kktp_default')->default(75);
            $table->enum('status', ['draft', 'locked'])->default('draft');
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['academic_year_id', 'school_class_id', 'subject_id'], 'pkg_yr_cls_sbj_unique');
        });

        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_package_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('sheet_number');
            $table->string('sheet_name', 20);
            $table->text('materi')->nullable();
            $table->unsignedTinyInteger('kktp')->default(75);
            $table->unsignedSmallInteger('max_score')->default(100);
            $table->timestamps();

            $table->unique(['assessment_package_id', 'sheet_number']);
        });

        Schema::create('assessment_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 5, 2)->nullable();
            $table->enum('status', ['tuntas', 'remedial', 'belum_dinilai'])->default('belum_dinilai');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['assessment_id', 'student_id']);
        });

        Schema::create('assessment_upload_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('filename');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('success_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->json('error_log')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_upload_batches');
        Schema::dropIfExists('assessment_scores');
        Schema::dropIfExists('assessments');
        Schema::dropIfExists('assessment_packages');
    }
};
