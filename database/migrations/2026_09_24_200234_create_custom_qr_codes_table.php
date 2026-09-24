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
        Schema::create('custom_qr_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('target_url');
            $table->unsignedSmallInteger('size')->default(300);
            $table->string('qr_color', 10)->default('#000000');
            $table->string('bg_color', 10)->default('#ffffff');
            $table->string('logo_type', 20)->default('none'); // none, default, custom
            $table->string('custom_logo_path')->nullable();
            $table->string('frame_style', 30)->default('default'); // default, neo_brutalism, scan_me
            $table->boolean('show_label')->default(false);
            $table->longText('svg_content')->nullable();
            $table->string('png_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_qr_codes');
    }
};
