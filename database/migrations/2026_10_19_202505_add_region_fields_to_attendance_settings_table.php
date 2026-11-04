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
            $table->string('school_province', 100)->nullable()->after('school_address');
            $table->string('school_city', 100)->nullable()->after('school_province');
            $table->string('school_district', 100)->nullable()->after('school_city');
            $table->string('school_village', 100)->nullable()->after('school_district');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->dropColumn(['school_province', 'school_city', 'school_district', 'school_village']);
        });
    }
};
