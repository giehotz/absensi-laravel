<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('savings_transactions', function (Blueprint $table) {
            $table->boolean('is_corrected')->default(false)->after('description');
            $table->decimal('original_amount', 15, 2)->nullable()->after('is_corrected');
            $table->string('correction_reason')->nullable()->after('original_amount');
            $table->foreignId('corrected_by')->nullable()->after('correction_reason')->constrained('users')->nullOnDelete();
            $table->dateTime('corrected_at')->nullable()->after('corrected_by');
        });
    }

    public function down(): void
    {
        Schema::table('savings_transactions', function (Blueprint $table) {
            $table->dropForeign(['corrected_by']);
            $table->dropColumn([
                'is_corrected',
                'original_amount',
                'correction_reason',
                'corrected_by',
                'corrected_at',
            ]);
        });
    }
};
