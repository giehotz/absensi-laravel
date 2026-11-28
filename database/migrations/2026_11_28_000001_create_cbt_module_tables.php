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
        Schema::create('cbt_question_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('ujian_jenis_id')->constrained('ujian_jenis')->restrictOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->restrictOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->restrictOnDelete();
            $table->string('bank_code', 100)->unique();
            $table->string('level', 30);
            $table->json('target_class_ids');
            $table->unsignedTinyInteger('option_count')->default(5);
            $table->string('religion_filter', 50)->default('-');
            $table->unsignedSmallInteger('pg_total')->default(0);
            $table->unsignedSmallInteger('pg_display')->default(0);
            $table->decimal('pg_weight', 5, 2)->default(0);
            $table->unsignedSmallInteger('kompleks_total')->default(0);
            $table->unsignedSmallInteger('kompleks_display')->default(0);
            $table->decimal('kompleks_weight', 5, 2)->default(0);
            $table->unsignedSmallInteger('jodohkan_total')->default(0);
            $table->unsignedSmallInteger('jodohkan_display')->default(0);
            $table->decimal('jodohkan_weight', 5, 2)->default(0);
            $table->unsignedSmallInteger('isian_total')->default(0);
            $table->unsignedSmallInteger('isian_display')->default(0);
            $table->decimal('isian_weight', 5, 2)->default(0);
            $table->unsignedSmallInteger('esai_total')->default(0);
            $table->unsignedSmallInteger('esai_display')->default(0);
            $table->decimal('esai_weight', 5, 2)->default(0);
            $table->decimal('kkm', 5, 2)->default(75.00);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_questions_ready')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['academic_year_id', 'subject_id', 'is_active'], 'idx_cbt_qb_yr_subj_act');
            $table->index(['teacher_id', 'is_active'], 'idx_cbt_qb_teacher_act');
        });

        Schema::create('cbt_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cbt_question_bank_id')->constrained('cbt_question_banks')->cascadeOnDelete();
            $table->unsignedTinyInteger('question_type');
            $table->unsignedSmallInteger('question_number');
            $table->longText('question_html');
            $table->json('options')->nullable();
            $table->json('answer_key')->nullable();
            $table->json('attachments')->nullable();
            $table->unsignedTinyInteger('difficulty')->default(5);
            $table->boolean('timer_enabled')->default(false);
            $table->unsignedSmallInteger('timer_minutes')->default(0);
            $table->boolean('is_displayed')->default(true);
            $table->timestamps();

            $table->unique(['cbt_question_bank_id', 'question_type', 'question_number'], 'uq_cbt_q_bank_type_num');
            $table->index(['cbt_question_bank_id', 'question_type', 'is_displayed'], 'idx_cbt_q_active');
        });

        Schema::create('cbt_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('ujian_periode_id')->nullable()->constrained('ujian_periode')->nullOnDelete();
            $table->foreignId('ujian_jadwal_id')->nullable()->constrained('ujian_jadwal')->nullOnDelete();
            $table->foreignId('ujian_jenis_id')->constrained('ujian_jenis')->restrictOnDelete();
            $table->foreignId('cbt_question_bank_id')->constrained('cbt_question_banks')->restrictOnDelete();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->unsignedSmallInteger('duration_minutes');
            $table->unsignedSmallInteger('min_finish_minutes')->default(0);
            $table->unsignedTinyInteger('jam_ke')->default(1);
            $table->boolean('shuffle_questions')->default(true);
            $table->boolean('shuffle_options')->default(true);
            $table->boolean('show_result')->default(false);
            $table->boolean('require_token')->default(true);
            $table->boolean('reset_login_lock')->default(false);
            $table->boolean('allow_repeat')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_recapped')->default(false);
            $table->timestamps();

            $table->index(['cbt_question_bank_id', 'ujian_jenis_id', 'academic_year_id'], 'idx_cbt_sched_bank_jenis_yr');
            $table->index(['is_active', 'start_date', 'end_date'], 'idx_cbt_sched_window');
            $table->index(['ujian_periode_id', 'jam_ke'], 'idx_cbt_sched_periode_jam');
        });

        Schema::create('cbt_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_periode_id')->nullable()->constrained('ujian_periode')->cascadeOnDelete();
            $table->string('token', 10);
            $table->boolean('is_auto')->default(false);
            $table->unsignedSmallInteger('interval_minutes')->default(15);
            $table->timestamp('refreshed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('cbt_student_durations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cbt_schedule_id')->constrained('cbt_schedules')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->unsignedTinyInteger('status')->default(0);
            $table->time('elapsed_time')->default('00:00:00');
            $table->unsignedInteger('elapsed_seconds')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedTinyInteger('reset_mode')->default(0);
            $table->timestamps();

            $table->unique(['cbt_schedule_id', 'student_id'], 'uq_cbt_dur_sched_student');
            $table->index(['cbt_schedule_id', 'status'], 'idx_cbt_dur_status');
        });

        Schema::create('cbt_student_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cbt_schedule_id')->constrained('cbt_schedules')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('cbt_question_bank_id')->constrained('cbt_question_banks')->cascadeOnDelete();
            $table->foreignId('cbt_question_id')->constrained('cbt_questions')->cascadeOnDelete();
            $table->unsignedTinyInteger('question_type');
            $table->unsignedSmallInteger('original_number');
            $table->unsignedSmallInteger('alias_number');
            $table->json('option_aliases')->nullable();
            $table->string('answer_alias', 10)->nullable();
            $table->json('answer_key')->nullable();
            $table->json('student_answer')->nullable();
            $table->decimal('max_point', 6, 2)->default(0);
            $table->decimal('corrected_score', 6, 2)->nullable();
            $table->boolean('is_manual_graded')->default(false);
            $table->boolean('is_doubtful')->default(false);
            $table->boolean('is_finished_flag')->default(false);
            $table->timestamps();

            $table->unique(['cbt_schedule_id', 'student_id', 'cbt_question_id'], 'uq_cbt_sq_sched_stu_q');
            $table->unique(['cbt_schedule_id', 'student_id', 'alias_number'], 'uq_cbt_sq_sched_stu_alias');
            $table->index(['cbt_schedule_id', 'student_id', 'question_type'], 'idx_cbt_sq_lookup');
        });

        Schema::create('cbt_student_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cbt_schedule_id')->constrained('cbt_schedules')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->unsignedSmallInteger('pg_correct')->default(0);
            $table->decimal('pg_score', 6, 2)->default(0);
            $table->decimal('kompleks_score', 6, 2)->default(0);
            $table->decimal('jodohkan_score', 6, 2)->default(0);
            $table->decimal('isian_score', 6, 2)->default(0);
            $table->decimal('esai_score', 6, 2)->default(0);
            $table->decimal('total_score', 6, 2)->default(0);
            $table->decimal('katrol_score', 6, 2)->nullable();
            $table->boolean('is_corrected')->default(false);
            $table->timestamps();

            $table->unique(['cbt_schedule_id', 'student_id'], 'uq_cbt_score_sched_stu');
        });

        Schema::create('cbt_exam_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cbt_schedule_id')->constrained('cbt_schedules')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->unsignedTinyInteger('log_type');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->string('device', 120)->nullable();
            $table->boolean('is_reset')->default(false);
            $table->string('violation_reason', 100)->nullable();
            $table->timestamp('logged_at')->nullable();
            $table->timestamps();

            $table->unique(['cbt_schedule_id', 'student_id', 'log_type'], 'uq_cbt_log_sched_stu_type');
        });

        Schema::create('cbt_recaps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cbt_schedule_id')->nullable()->constrained('cbt_schedules')->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('ujian_jenis_id')->constrained('ujian_jenis')->restrictOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->restrictOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->restrictOnDelete();
            $table->string('bank_code', 100);
            $table->string('exam_title', 255);
            $table->date('exam_date');
            $table->json('bank_config_snapshot');
            $table->json('classes_snapshot');
            $table->json('questions_snapshot')->nullable();
            $table->foreignId('recapped_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('cbt_recap_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cbt_recap_id')->constrained('cbt_recaps')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->string('school_class_name', 100);
            $table->string('student_name', 150);
            $table->string('participant_number', 50)->nullable();
            $table->unsignedSmallInteger('pg_correct')->default(0);
            $table->decimal('pg_score', 6, 2)->default(0);
            $table->decimal('kompleks_score', 6, 2)->default(0);
            $table->decimal('jodohkan_score', 6, 2)->default(0);
            $table->decimal('isian_score', 6, 2)->default(0);
            $table->decimal('esai_score', 6, 2)->default(0);
            $table->decimal('total_score', 6, 2)->default(0);
            $table->decimal('katrol_score', 6, 2)->nullable();
            $table->timestamps();

            $table->unique(['cbt_recap_id', 'student_id'], 'uq_cbt_recap_stu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbt_recap_scores');
        Schema::dropIfExists('cbt_recaps');
        Schema::dropIfExists('cbt_exam_logs');
        Schema::dropIfExists('cbt_student_scores');
        Schema::dropIfExists('cbt_student_questions');
        Schema::dropIfExists('cbt_student_durations');
        Schema::dropIfExists('cbt_tokens');
        Schema::dropIfExists('cbt_schedules');
        Schema::dropIfExists('cbt_questions');
        Schema::dropIfExists('cbt_question_banks');
    }
};
