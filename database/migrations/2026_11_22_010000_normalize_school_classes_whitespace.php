<?php

use App\Models\SchoolClass;
use App\Models\Ujian\UjianPeserta;
use App\Models\Ujian\UjianRuang;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SchoolClass::all()->each(function (SchoolClass $class) {
            $normalized = trim(preg_replace('/\s+/', ' ', $class->name));
            if ($normalized !== $class->name) {
                $class->name = $normalized;
                $class->saveQuietly();
            }
        });

        UjianPeserta::all()->each(function (UjianPeserta $peserta) {
            $normalized = trim(preg_replace('/\s+/', ' ', $peserta->nama_kelas));
            if ($normalized !== $peserta->nama_kelas) {
                $peserta->nama_kelas = $normalized;
                $peserta->saveQuietly();
            }
        });

        UjianRuang::all()->each(function (UjianRuang $ruang) {
            $normalized = trim(preg_replace('/\s+/', ' ', $ruang->nama_ruang));
            if ($normalized !== $ruang->nama_ruang) {
                $ruang->nama_ruang = $normalized;
                $ruang->saveQuietly();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data normalization is irreversible and non-destructive.
    }
};
