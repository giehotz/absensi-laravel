<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supervisi_coaching_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('tahap', 30)->unique();
            $table->text('pertanyaan_tujuan');
            $table->text('pertanyaan_identifikasi');
            $table->text('pertanyaan_rencana');
            $table->text('pertanyaan_tanggung_jawab');
            $table->text('template_tujuan')->nullable();
            $table->text('template_identifikasi')->nullable();
            $table->text('template_rencana')->nullable();
            $table->text('template_tanggung_jawab')->nullable();
            $table->text('template_catatan_coach')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervisi_coaching_templates');
    }
};
