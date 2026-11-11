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
        Schema::create('student_duplicate_conflicts', function (Blueprint $table) {
            $table->id();
            $table->string('duplicate_nis', 50)->index()->comment('Nomor NIS yang kembar dari SIS-EMIS');
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('conflict_with_student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->string('assigned_temp_nis', 50)->nullable()->comment('NIS sementara yang diberikan sistem di database Absensi');
            $table->string('nisn', 30)->nullable()->index();
            $table->string('nik', 30)->nullable()->index();
            $table->string('source', 30)->default('sync_api')->comment('sync_api atau system_scan');
            $table->string('status', 20)->default('pending')->index()->comment('pending atau resolved');
            $table->text('resolution_notes')->nullable()->comment('Catatan verifikasi atau penyesuaian dari admin');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_duplicate_conflicts');
    }
};
