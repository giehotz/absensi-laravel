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
        Schema::create('mbg_distribution_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mbg_distribution_id')->constrained('mbg_distributions')->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->unsignedInteger('target_portions')->default(0);
            $table->unsignedInteger('distributed_portions')->default(0);
            $table->unsignedInteger('leftover_portions')->default(0);
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['mbg_distribution_id', 'school_class_id'], 'mbg_dist_class_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mbg_distribution_classes');
    }
};
