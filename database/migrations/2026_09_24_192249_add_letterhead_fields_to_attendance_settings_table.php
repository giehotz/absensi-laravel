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
            $table->string('kop_government_name')->nullable()->after('favicon');
            $table->string('kop_institution_name')->nullable()->after('kop_government_name');
            $table->string('kop_school_name')->nullable()->after('kop_institution_name');
            $table->text('kop_address')->nullable()->after('kop_school_name');
            $table->string('kop_postal_code', 10)->nullable()->after('kop_address');
            $table->string('kop_phone', 50)->nullable()->after('kop_postal_code');
            $table->string('kop_email')->nullable()->after('kop_phone');
            $table->string('kop_website')->nullable()->after('kop_email');
            $table->string('kop_logo_left')->nullable()->after('kop_website');
            $table->string('kop_logo_right')->nullable()->after('kop_logo_left');
            $table->string('kop_border_style', 20)->default('double')->after('kop_logo_right');
            $table->boolean('kop_is_active')->default(true)->after('kop_border_style');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->dropColumn([
                'kop_government_name',
                'kop_institution_name',
                'kop_school_name',
                'kop_address',
                'kop_postal_code',
                'kop_phone',
                'kop_email',
                'kop_website',
                'kop_logo_left',
                'kop_logo_right',
                'kop_border_style',
                'kop_is_active',
            ]);
        });
    }
};
