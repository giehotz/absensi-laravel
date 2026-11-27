<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supervisi_aspek_penilaian', function (Blueprint $table): void {
            $table->text('fokus_perilaku')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('supervisi_aspek_penilaian', function (Blueprint $table): void {
            $table->string('fokus_perilaku')->nullable()->change();
        });
    }
};
