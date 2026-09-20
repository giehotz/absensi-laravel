<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->enum('savings_scope', ['all', 'restricted'])->default('all')->after('is_savings_officer');
        });

        Schema::create('teacher_savings_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['teacher_id', 'school_class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_savings_classes');

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('savings_scope');
        });
    }
};
