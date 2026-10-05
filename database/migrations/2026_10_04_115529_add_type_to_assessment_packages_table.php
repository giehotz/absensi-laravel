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
        Schema::table('assessment_packages', function (Blueprint $table) {
            if (! Schema::hasColumn('assessment_packages', 'type')) {
                $table->string('type', 20)->default('materi')->after('title');
            }
            $table->unique(['academic_year_id', 'school_class_id', 'subject_id', 'type'], 'pkg_yr_cls_sbj_type_unique');
            $table->dropUnique('pkg_yr_cls_sbj_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessment_packages', function (Blueprint $table) {
            $table->unique(['academic_year_id', 'school_class_id', 'subject_id'], 'pkg_yr_cls_sbj_unique');
            $table->dropUnique('pkg_yr_cls_sbj_type_unique');
            if (Schema::hasColumn('assessment_packages', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
