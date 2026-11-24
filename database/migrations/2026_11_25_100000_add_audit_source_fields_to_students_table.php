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
            $table->string('data_source', 20)->default('legacy')->index()->after('guardian_address');
            $table->string('api_external_id', 100)->nullable()->index()->after('data_source');
            $table->timestamp('last_synced_at')->nullable()->after('api_external_id');
            $table->foreignId('created_by')->nullable()->after('last_synced_at')->constrained('users')->nullOnDelete();
            $table->string('audit_status', 20)->default('pending')->index()->after('created_by');
            $table->string('audit_note', 255)->nullable()->after('audit_status');
            $table->foreignId('audited_by')->nullable()->after('audit_note')->constrained('users')->nullOnDelete();
            $table->timestamp('audited_at')->nullable()->after('audited_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['audited_by']);
            $table->dropColumn([
                'data_source',
                'api_external_id',
                'last_synced_at',
                'created_by',
                'audit_status',
                'audit_note',
                'audited_by',
                'audited_at',
            ]);
        });
    }
};
