<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus baris duplikat jika sudah terlanjur ada, pertahankan record terbaru (id terbesar)
        $duplicates = DB::table('ujian_jadwal_pengawas')
            ->select('ujian_jadwal_id', 'ujian_ruang_id', DB::raw('MAX(id) as keep_id'))
            ->groupBy('ujian_jadwal_id', 'ujian_ruang_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            DB::table('ujian_jadwal_pengawas')
                ->where('ujian_jadwal_id', $dup->ujian_jadwal_id)
                ->where('ujian_ruang_id', $dup->ujian_ruang_id)
                ->where('id', '!=', $dup->keep_id)
                ->delete();
        }

        Schema::table('ujian_jadwal_pengawas', function (Blueprint $table) {
            $table->unique(
                ['ujian_jadwal_id', 'ujian_ruang_id'],
                'ujian_jadwal_pengawas_jadwal_ruang_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('ujian_jadwal_pengawas', function (Blueprint $table) {
            $table->dropUnique('ujian_jadwal_pengawas_jadwal_ruang_unique');
        });
    }
};
