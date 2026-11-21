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
        Schema::table('ujian_panitia', function (Blueprint $table) {
            $table->boolean('ikut_mengawas')->default(false)->after('jabatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ujian_panitia', function (Blueprint $table) {
            $table->dropColumn('ikut_mengawas');
        });
    }
};
