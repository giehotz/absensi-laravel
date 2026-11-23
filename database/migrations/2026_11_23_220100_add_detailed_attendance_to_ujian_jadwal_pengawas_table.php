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
            $table->string('status_p1', 30)->default('Terjadwal')->after('pengawas_2_id');
            $table->string('status_p2', 30)->default('Terjadwal')->after('status_p1');
            $table->timestamp('check_in_p1_at')->nullable()->after('status_p2');
            $table->timestamp('check_in_p2_at')->nullable()->after('check_in_p1_at');
            $table->string('pengawas_pengganti_1', 150)->nullable()->after('pengawas_pengganti');
            $table->string('pengawas_pengganti_2', 150)->nullable()->after('pengawas_pengganti_1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ujian_jadwal_pengawas', function (Blueprint $table) {
            $table->dropColumn([
                'status_p1',
                'status_p2',
                'check_in_p1_at',
                'check_in_p2_at',
                'pengawas_pengganti_1',
                'pengawas_pengganti_2',
            ]);
        });
    }
};
