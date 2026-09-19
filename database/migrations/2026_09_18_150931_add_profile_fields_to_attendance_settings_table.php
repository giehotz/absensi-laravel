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
        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->string('school_name')->default('SMP Negeri 1 Garuda')->after('tolerance_minutes');
            $table->string('npsn', 30)->nullable()->default('20102030')->after('school_name');
            $table->string('level', 20)->default('SMP')->after('npsn');
            $table->text('school_address')->nullable()->after('level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->dropColumn(['school_name', 'npsn', 'level', 'school_address']);
        });
    }
};
