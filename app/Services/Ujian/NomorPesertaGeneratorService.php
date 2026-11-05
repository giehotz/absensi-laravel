<?php

namespace App\Services\Ujian;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Ujian\UjianPeriode;
use App\Models\Ujian\UjianPeserta;
use App\Models\Ujian\UjianRuang;

class NomorPesertaGeneratorService
{
    /**
     * Sinkronisasi data siswa dari tabel students ke ujian_peserta.
     *
     * @param  array<int>|null  $schoolClassIds  Jika kosong, seluruh siswa aktif ditarik.
     * @return array{total_synced: int, created: int, updated: int}
     */
    public function syncStudentsFromMaster(UjianPeriode $periode, ?array $schoolClassIds = null): array
    {
        $query = Student::with(['user', 'schoolClass'])
            ->whereHas('user', fn ($q) => $q->where('is_active', true));

        if (! empty($schoolClassIds)) {
            $query->whereIn('school_class_id', $schoolClassIds);
        }

        $students = $query->get();

        $created = 0;
        $updated = 0;

        foreach ($students as $student) {
            $namaSiswa = $student->user?->name ?? 'Siswa';
            $namaKelas = $student->schoolClass?->name ?? 'Tanpa Rombel';

            // Generate nomor peserta default jika belum ada (Pola: 26-27.1-1.001)
            $tahunPrefix = $this->extractTahunPrefix($periode->tahun_pelajaran);
            $semester = $this->extractSemesterDigit($periode->semester);
            $level = $student->schoolClass?->level ?? '1';
            $defaultNomor = sprintf('%s.%s-%s.%03d', $tahunPrefix, $semester, $level, ($student->id % 999) ?: 1);

            $peserta = UjianPeserta::firstOrNew([
                'ujian_periode_id' => $periode->id,
                'student_id' => $student->id,
            ]);

            $isNew = ! $peserta->exists;

            $peserta->fill([
                'school_class_id' => $student->school_class_id,
                'nama_kelas' => $namaKelas,
                'nama_peserta' => $namaSiswa,
                'nis_lokal' => $student->nis,
                'nisn' => $student->nisn,
                'jenis_kelamin' => $student->gender,
                'tempat_lahir' => $student->birth_place,
                'tanggal_lahir' => $student->birth_date,
                'foto_peserta_path' => $student->photo,
                'is_eligible' => true,
            ]);

            if ($isNew || empty($peserta->nomor_peserta)) {
                $peserta->nomor_peserta = $defaultNomor;
            }

            // Hubungkan otomatis jika sudah ada ruang kelas yang sesuai
            $matchingRuang = UjianRuang::where('school_class_id', $student->school_class_id)
                ->where('tipe_ruang', 'kelas')
                ->first();
            if ($matchingRuang) {
                $peserta->ujian_ruang_id = $matchingRuang->id;
            }

            $peserta->save();

            if ($isNew) {
                $created++;
            } else {
                $updated++;
            }
        }

        return [
            'total_synced' => $students->count(),
            'created' => $created,
            'updated' => $updated,
        ];
    }

    /**
     * Ekstrak awalan tahun pelajaran menjadi format YY-YY (contoh: 2026/2027 => 26-27).
     */
    public function extractTahunPrefix(?string $tahunPelajaran): string
    {
        if (empty($tahunPelajaran)) {
            return date('y');
        }

        if (preg_match('/\b(?:\d{2})?(\d{2})\s*[\/\-]\s*(?:\d{2})?(\d{2})\b/', $tahunPelajaran, $matches)) {
            return $matches[1].'-'.$matches[2];
        }

        return substr($tahunPelajaran, -2);
    }

    /**
     * Ekstrak digit semester (Ganjil => 1, Genap => 2).
     */
    public function extractSemesterDigit(?string $semester): string
    {
        if (empty($semester)) {
            return '1';
        }

        return str_contains(strtolower($semester), 'genap') || $semester === '2' ? '2' : '1';
    }

    /**
     * Ekstrak angka tingkat kelas (1 - 12) dari nama atau level kelas.
     */
    public function extractGradeLevel(SchoolClass $class, int $fallbackIdx = 1): string
    {
        if (preg_match('/(\d+)/', $class->name, $m)) {
            return $m[1];
        }

        if (is_numeric($class->level)) {
            return (string) (int) $class->level;
        }

        if (preg_match('/\b(VI|IV|V|III|II|I)\b/i', $class->name, $rm)) {
            $romans = ['I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4, 'V' => 5, 'VI' => 6];
            $rUpper = strtoupper($rm[1]);
            if (isset($romans[$rUpper])) {
                return (string) $romans[$rUpper];
            }
        }

        return (string) $fallbackIdx;
    }

    /**
     * Ekstrak kode nama rombel bersih (contoh: 'Kelas  1 a' => '1a').
     */
    public function extractRombelCode(SchoolClass $class): string
    {
        $cleaned = preg_replace('/[^0-9a-zA-Z]/', '', str_ireplace('kelas', '', $class->name));

        return ! empty($cleaned) ? $cleaned : $this->extractGradeLevel($class);
    }

    /**
     * Generate nomor peserta terstruktur secara massal per rombel dan urut abjad nama siswa.
     * Pola Default: 26-27.1-1.001 ({tahun}.{semester}-{kelas}.{urut})
     *
     * @param  array{
     *     tahun_prefix?: string|null,
     *     semester?: string|null,
     *     format_kelas?: string|null,
     *     separator_1?: string|null,
     *     separator_2?: string|null,
     *     separator_3?: string|null,
     *     digit_urut?: int|string|null
     * }  $options
     */
    public function generateStructuredNomorPeserta(UjianPeriode $periode, ?int $schoolClassId = null, array $options = []): int
    {
        $tahunPrefix = ! empty($options['tahun_prefix'])
            ? trim($options['tahun_prefix'])
            : $this->extractTahunPrefix($periode->tahun_pelajaran);

        $semester = ! empty($options['semester'])
            ? trim($options['semester'])
            : $this->extractSemesterDigit($periode->semester);

        $formatKelas = $options['format_kelas'] ?? 'tingkat';
        $sep1 = $options['separator_1'] ?? '.';
        $sep2 = $options['separator_2'] ?? '-';
        $sep3 = $options['separator_3'] ?? '.';
        $digitUrut = max(2, min(5, (int) ($options['digit_urut'] ?? 3)));
        $formatUrut = '%0'.$digitUrut.'d';

        $classesQuery = SchoolClass::query();
        if ($schoolClassId) {
            $classesQuery->where('id', $schoolClassId);
        }
        $classes = $classesQuery->orderBy('name')->get();

        $counter = 0;

        foreach ($classes as $classIdx => $class) {
            $gradeLevel = $this->extractGradeLevel($class, $classIdx + 1);
            $rombelCode = $this->extractRombelCode($class);

            $classPart = match ($formatKelas) {
                'nama_rombel_lower' => strtolower($rombelCode),
                'nama_rombel_upper' => strtoupper($rombelCode),
                'kode_rombel' => sprintf('%02d', ($classIdx + 1) % 99),
                default => $gradeLevel, // 'tingkat' -> 1, 2, 3...
            };

            // Urutkan siswa berdasarkan nama peserta (abjad A-Z) sesuai standar urut absen
            $pesertas = UjianPeserta::where('ujian_periode_id', $periode->id)
                ->where('school_class_id', $class->id)
                ->orderBy('nama_peserta', 'asc')
                ->get();

            foreach ($pesertas as $idx => $peserta) {
                $urut = $idx + 1;
                $nomor = sprintf('%s%s%s%s%s%s'.$formatUrut, $tahunPrefix, $sep1, $semester, $sep2, $classPart, $sep3, $urut);

                $peserta->nomor_peserta = $nomor;
                $peserta->nomor_kursi = $urut;
                $peserta->save();

                $counter++;
            }
        }

        return $counter;
    }

    /**
     * Distribusikan peserta secara otomatis ke ruang-ruang ujian berdasarkan kapasitas ruang.
     */
    public function autoAllocateRooms(UjianPeriode $periode): int
    {
        $allocatedCount = 0;

        // 1. Alokasikan untuk Ruang Kelas Eksisting (Tipe 'kelas' terikat school_class_id)
        $ruangKelas = UjianRuang::where('tipe_ruang', 'kelas')
            ->whereNotNull('school_class_id')
            ->get();

        $processedPesertaIds = [];

        foreach ($ruangKelas as $ruang) {
            $pesertas = UjianPeserta::where('ujian_periode_id', $periode->id)
                ->where('school_class_id', $ruang->school_class_id)
                ->orderBy('nama_peserta')
                ->get();

            $kursi = 1;
            foreach ($pesertas as $peserta) {
                $peserta->ujian_ruang_id = $ruang->id;
                $peserta->nomor_kursi = $kursi;
                $peserta->save();

                $processedPesertaIds[] = $peserta->id;
                $kursi++;
                $allocatedCount++;
            }
        }

        // 2. Alokasikan sisa peserta (misal kelas 6 untuk UAM atau rombel tanpa ruang kelas) ke Ruang Manual
        $sisaPesertas = UjianPeserta::where('ujian_periode_id', $periode->id)
            ->whereNotIn('id', $processedPesertaIds)
            ->orderBy('nama_kelas')
            ->orderBy('nama_peserta')
            ->get();

        if ($sisaPesertas->isNotEmpty()) {
            $ruangManuals = UjianRuang::where('tipe_ruang', 'manual')->orderBy('kode_ruang')->get();
            if ($ruangManuals->isEmpty()) {
                $ruangManuals = UjianRuang::orderBy('kode_ruang')->get();
            }

            if ($ruangManuals->isNotEmpty()) {
                $ruangIndex = 0;
                $currentKursi = 1;

                foreach ($sisaPesertas as $peserta) {
                    $currentRuang = $ruangManuals[$ruangIndex];

                    if ($currentKursi > $currentRuang->kapasitas_maksimal) {
                        $ruangIndex++;
                        if (! isset($ruangManuals[$ruangIndex])) {
                            $ruangIndex = 0;
                        }
                        $currentRuang = $ruangManuals[$ruangIndex];
                        $currentKursi = 1;
                    }

                    $peserta->ujian_ruang_id = $currentRuang->id;
                    $peserta->nomor_kursi = $currentKursi;
                    $peserta->save();

                    $currentKursi++;
                    $allocatedCount++;
                }
            }
        }

        return $allocatedCount;
    }
}
