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
        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->string('card_school_name')->nullable()->after('kop_is_active');
            $table->string('card_title')->default('KARTU SISWA')->after('card_school_name');
            $table->string('card_logo')->nullable()->after('card_title');
            $table->string('card_validity_text')->default('BERLAKU SELAMA MENJADI SISWA')->after('card_logo');
            $table->text('card_back_instructions')->nullable()->after('card_validity_text');
            $table->decimal('card_width_cm', 4, 2)->default(8.70)->after('card_back_instructions');
            $table->decimal('card_height_cm', 4, 2)->default(5.40)->after('card_width_cm');
            $table->string('card_theme_color', 20)->default('#20C997')->after('card_height_cm');
            $table->boolean('card_show_back_token')->default(true)->after('card_theme_color');
            $table->boolean('card_show_signature')->default(false)->after('card_show_back_token');
            $table->string('card_principal_name')->nullable()->after('card_show_signature');
            $table->string('card_principal_nip')->nullable()->after('card_principal_name');
            $table->string('card_signature_image')->nullable()->after('card_principal_nip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->dropColumn([
                'card_school_name',
                'card_title',
                'card_logo',
                'card_validity_text',
                'card_back_instructions',
                'card_width_cm',
                'card_height_cm',
                'card_theme_color',
                'card_show_back_token',
                'card_show_signature',
                'card_principal_name',
                'card_principal_nip',
                'card_signature_image',
            ]);
        });
    }
};
