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
        Schema::table('ujian_pengaturan_ttd', function (Blueprint $table) {
            $table->longText('konten_tata_tertib')->nullable()->after('is_active');
            $table->longText('konten_sk_panitia')->nullable()->after('konten_tata_tertib');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ujian_pengaturan_ttd', function (Blueprint $table) {
            $table->dropColumn(['konten_tata_tertib', 'konten_sk_panitia']);
        });
    }
};
