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
        Schema::table('ujian_pengawas_mappings', function (Blueprint $table) {
            $table->string('mode_penugasan', 30)->default('rotasi')->after('assigned_levels');
            $table->json('assigned_subject_ids')->nullable()->after('mode_penugasan');
            $table->json('assigned_class_ids')->nullable()->after('assigned_subject_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ujian_pengawas_mappings', function (Blueprint $table) {
            $table->dropColumn([
                'mode_penugasan',
                'assigned_subject_ids',
                'assigned_class_ids',
            ]);
        });
    }
};
