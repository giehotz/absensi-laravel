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
        // 1. Make email in users table nullable for teachers who will fill it independently
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });

        // 2. Add comprehensive profile fields to teachers table
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('nuptk', 50)->nullable()->unique()->after('user_id');
            $table->enum('gender', ['L', 'P'])->nullable()->after('nip');
            $table->string('birth_place', 100)->nullable()->after('gender');
            $table->date('birth_date')->nullable()->after('birth_place');
            $table->string('last_education', 50)->nullable()->after('birth_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn([
                'nuptk',
                'gender',
                'birth_place',
                'birth_date',
                'last_education',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });
    }
};
