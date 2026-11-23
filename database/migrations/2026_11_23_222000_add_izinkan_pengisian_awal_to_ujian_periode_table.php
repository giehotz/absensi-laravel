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
        Schema::table('ujian_periode', function (Blueprint $table) {
            $table->boolean('izinkan_pengisian_awal')->default(false)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ujian_periode', function (Blueprint $table) {
            $table->dropColumn('izinkan_pengisian_awal');
        });
    }
};
