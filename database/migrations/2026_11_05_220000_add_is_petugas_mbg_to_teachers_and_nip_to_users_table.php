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
        Schema::table('teachers', function (Blueprint $table) {
            $table->boolean('is_petugas_mbg')->default(false)->after('is_savings_officer');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('nip', 50)->nullable()->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('is_petugas_mbg');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('nip');
        });
    }
};
