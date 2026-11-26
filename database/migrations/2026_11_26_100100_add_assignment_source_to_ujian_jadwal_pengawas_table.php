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
        Schema::table('ujian_jadwal_pengawas', function (Blueprint $table) {
            $table->string('assignment_source', 30)->default('auto_rotation')->after('pengawas_2_id');
            $table->boolean('is_locked')->default(false)->after('assignment_source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ujian_jadwal_pengawas', function (Blueprint $table) {
            $table->dropColumn([
                'assignment_source',
                'is_locked',
            ]);
        });
    }
};
