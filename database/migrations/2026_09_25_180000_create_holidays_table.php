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
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->date('holiday_date')->unique();
            $table->string('name');
            $table->boolean('is_national')->default(true);
            $table->boolean('is_cuti_bersama')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('source')->default('kemendesa_api');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['holiday_date', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
