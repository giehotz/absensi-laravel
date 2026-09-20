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
        Schema::create('student_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->enum('category', ['kedisiplinan', 'prestasi', 'kesehatan', 'pembinaan', 'umum'])->default('umum');
            $table->string('title')->nullable();
            $table->text('content');
            $table->string('follow_up')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'date']);
            $table->index(['category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_notes');
    }
};
