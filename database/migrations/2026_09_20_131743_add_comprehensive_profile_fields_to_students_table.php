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
        Schema::table('students', function (Blueprint $table) {
            $table->string('birth_place')->nullable()->after('gender');
            $table->text('address')->nullable()->after('phone');
            $table->string('religion')->nullable()->after('address');
            $table->string('family_status')->nullable()->after('religion');
            $table->string('child_number')->nullable()->after('family_status');
            $table->string('previous_school')->nullable()->after('child_number');
            $table->date('admission_date')->nullable()->after('previous_school');
            $table->string('entry_grade')->nullable()->after('admission_date');
            $table->string('father_name')->nullable()->after('entry_grade');
            $table->string('mother_name')->nullable()->after('father_name');
            $table->string('father_job')->nullable()->after('mother_name');
            $table->string('mother_job')->nullable()->after('father_job');
            $table->text('parent_address')->nullable()->after('mother_job');
            $table->string('guardian_name')->nullable()->after('parent_address');
            $table->string('guardian_job')->nullable()->after('guardian_name');
            $table->text('guardian_address')->nullable()->after('guardian_job');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'birth_place',
                'address',
                'religion',
                'family_status',
                'child_number',
                'previous_school',
                'admission_date',
                'entry_grade',
                'father_name',
                'mother_name',
                'father_job',
                'mother_job',
                'parent_address',
                'guardian_name',
                'guardian_job',
                'guardian_address',
            ]);
        });
    }
};
