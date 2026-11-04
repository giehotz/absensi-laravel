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
        Schema::create('mbg_distributions', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('menu_name');
            $table->unsignedInteger('target_students')->default(0);
            $table->unsignedInteger('distributed_students')->default(0);
            $table->unsignedInteger('target_teachers')->default(0);
            $table->unsignedInteger('distributed_teachers')->default(0);
            $table->unsignedInteger('leftover_portions')->default(0);
            $table->string('status', 30)->default('selesai');
            $table->text('notes')->nullable();
            $table->string('documentation_image')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mbg_distributions');
    }
};
