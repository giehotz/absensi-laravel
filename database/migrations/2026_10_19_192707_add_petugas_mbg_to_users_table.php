<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'guru', 'siswa', 'orangtua', 'petugas_mbg') NOT NULL DEFAULT 'siswa'");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role', 30)->default('siswa')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'guru', 'siswa', 'orangtua') NOT NULL DEFAULT 'siswa'");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role', 30)->default('siswa')->change();
            });
        }
    }
};
