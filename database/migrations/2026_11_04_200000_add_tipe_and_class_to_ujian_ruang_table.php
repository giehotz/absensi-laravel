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
        Schema::table('ujian_ruang', function (Blueprint $table) {
            $table->string('tipe_ruang', 30)->default('manual')->after('kapasitas_maksimal'); // 'kelas' atau 'manual'
            $table->foreignId('school_class_id')->nullable()->after('tipe_ruang')->constrained('school_classes')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ujian_ruang', function (Blueprint $table) {
            $table->dropForeign(['school_class_id']);
            $table->dropColumn(['tipe_ruang', 'school_class_id']);
        });
    }
};
