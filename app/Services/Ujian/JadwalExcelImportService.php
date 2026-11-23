<?php

namespace App\Services\Ujian;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Ujian\UjianJadwal;
use App\Models\Ujian\UjianPeriode;
use App\Models\Ujian\UjianSesi;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JadwalExcelImportService
{
    public function __construct(
        protected JadwalConflictService $conflictService
    ) {}

    /**
     * Memproses import file Excel jadwal ujian multi-format.
     *
     * @return array{imported: int, skipped: array<int, string>, format: string}
     */
    public function import(UploadedFile $file, int $periodeId): array
    {
        $periode = UjianPeriode::findOrFail($periodeId);
        $spreadsheet = IOFactory::load($file->getRealPath());

        // 1. Cek sheet _Meta jika ada
        $metaSheet = $spreadsheet->getSheetByName('_Meta');
        $format = null;

        if ($metaSheet !== null) {
            $metaData = [];
            $highestRow = $metaSheet->getHighestRow();
            for ($r = 1; $r <= $highestRow; $r++) {
                $k = trim((string) $metaSheet->getCell("A{$r}")->getValue());
                $v = trim((string) $metaSheet->getCell("B{$r}")->getValue());
                if ($k !== '') {
                    $metaData[$k] = $v;
                }
            }

            if (($metaData['template_version'] ?? '') === 'v2') {
                $metaPeriodeId = (int) ($metaData['ujian_periode_id'] ?? 0);
                if ($metaPeriodeId > 0 && $metaPeriodeId !== $periodeId) {
                    throw new \InvalidArgumentException(
                        "File template ini dibuat untuk Periode ID #{$metaPeriodeId} ({$metaData['ujian_periode_nama']}), namun Anda mengunggahnya ke Periode ID #{$periodeId} ({$periode->nama_periode}). Pastikan periode sesuai."
                    );
                }

                $modeJadwal = $metaData['mode_jadwal'] ?? '';
                if ($modeJadwal === 'per_rombel') {
                    $format = 'v2_per_rombel';
                } elseif ($modeJadwal === 'per_tingkat') {
                    $format = 'v2_per_tingkat';
                } else {
                    throw new \InvalidArgumentException("Mode jadwal '{$modeJadwal}' pada metadata template tidak dikenali.");
                }
            }
        }

        // 2. Fallback deteksi header jika _Meta tidak ada
        $sheet = $spreadsheet->getActiveSheet();
        if ($format === null) {
            $format = $this->detectFormatFromHeader($sheet);
        }

        // 3. Eksekusi parser sesuai format
        return match ($format) {
            'v2_per_rombel' => $this->importPerRombel($sheet, $periodeId),
            'v2_per_tingkat' => $this->importPerTingkat($sheet, $periodeId),
            'legacy' => $this->importLegacy($sheet, $periodeId),
            default => throw new \InvalidArgumentException('Format file Excel tidak dikenali.'),
        };
    }

    /**
     * Menentukan cakupan target (kelas/tingkat) dari baris file yang valid.
     *
     * @param  Collection<int, SchoolClass>  $allClasses
     * @param  array<string, SchoolClass>  $columnClassMap
     * @return array{class_ids: array<int, int>, levels: array<int, string>}
     */
    protected function resolveImportScope(Worksheet $sheet, string $format, Collection $allClasses, array $columnClassMap = []): array
    {
        $classIds = [];
        $levels = [];
        $highestRow = $sheet->getHighestRow();

        for ($row = 2; $row <= $highestRow; $row++) {
            if (! $this->parseDate($sheet->getCell("C{$row}")->getValue())) {
                continue;
            }

            if ($format === 'v2_per_rombel') {
                foreach ($columnClassMap as $colLetter => $class) {
                    if (trim((string) $sheet->getCell("{$colLetter}{$row}")->getValue()) !== '') {
                        $classIds[$class->id] = $class->id;
                    }
                }

                continue;
            }

            $mapelCol = $format === 'v2_per_tingkat' ? 'F' : 'B';
            $targetCol = $format === 'v2_per_tingkat' ? 'G' : 'E';

            if (trim((string) $sheet->getCell("{$mapelCol}{$row}")->getValue()) === '') {
                continue;
            }

            $targetRaw = trim((string) $sheet->getCell("{$targetCol}{$row}")->getValue());
            $matchedClass = ClassLabelResolver::resolveClassByLabel($targetRaw, $allClasses);

            if ($matchedClass) {
                $classIds[$matchedClass->id] = $matchedClass->id;

                continue;
            }

            if ($format === 'v2_per_tingkat' && $targetRaw === '') {
                $targetRaw = '3, 4, 5, 6';
            }

            foreach ($this->parseTargetLevels($targetRaw) as $level) {
                $levels[$level] = $level;
            }
        }

        return [
            'class_ids' => array_values($classIds),
            'levels' => array_values($levels),
        ];
    }

    /**
     * Mengubah kode tingkat/kelas menjadi daftar angka tingkat.
     *
     * @return array<int, string>
     */
    protected function parseTargetLevels(string $raw): array
    {
        $clean = mb_strtolower(trim($raw));

        if (in_array($clean, ['123456', '1-6', 'semua', 'semua kelas', 'semua tingkat'], true)) {
            return ['1', '2', '3', '4', '5', '6'];
        }

        if (in_array($clean, ['123', '1-3', 'rendah'], true)) {
            return ['1', '2', '3'];
        }

        if (in_array($clean, ['456', '4-6', 'atas'], true)) {
            return ['4', '5', '6'];
        }

        preg_match_all('/[1-6]/', $clean, $digits);

        $unique = array_values(array_unique($digits[0] ?? []));
        sort($unique, SORT_NATURAL);

        return $unique;
    }

    /**
     * Menghapus/menyesuaikan jadwal lama yang tercakup target import.
     *
     * @param  array<int, int>  $classIds
     * @param  array<int, string>  $levels
     * @param  Collection<int, SchoolClass>  $allClasses
     */
    protected function clearExistingSchedules(int $periodeId, array $classIds, array $levels, Collection $allClasses): int
    {
        $classesById = $allClasses->keyBy('id');

        $classLevels = [];
        foreach ($classIds as $classId) {
            $class = $classesById->get($classId);
            $level = $class ? ClassLabelResolver::levelFromName($class->name) : null;
            if ($level !== null) {
                $classLevels[$level] = $level;
            }
        }

        $affectedLevels = array_values(array_unique(array_merge($levels, array_values($classLevels))));

        if ($classIds === [] && $affectedLevels === []) {
            return 0;
        }

        $replaced = 0;
        $existing = UjianJadwal::where('ujian_periode_id', $periodeId)->get(['id', 'school_class_id', 'tingkat']);

        foreach ($existing as $jadwal) {
            if ($jadwal->school_class_id !== null) {
                $class = $classesById->get($jadwal->school_class_id);
                $level = $class ? ClassLabelResolver::levelFromName($class->name) : null;

                if (in_array((int) $jadwal->school_class_id, $classIds, true)
                    || ($level !== null && in_array($level, $levels, true))) {
                    $jadwal->delete();
                    $replaced++;
                }

                continue;
            }

            $existingLevels = $jadwal->getTargetLevels();
            $remaining = array_values(array_diff($existingLevels, $affectedLevels));

            if ($remaining === $existingLevels) {
                continue;
            }

            if ($remaining === []) {
                $jadwal->delete();
            } else {
                $jadwal->update(['tingkat' => implode(', ', $remaining)]);
            }

            $replaced++;
        }

        return $replaced;
    }

    /**
     * Mendeteksi format template dari header baris 1 sheet utama.
     */
    protected function detectFormatFromHeader(Worksheet $sheet): string
    {
        $h1 = mb_strtolower(trim((string) $sheet->getCell('A1')->getValue()));
        $h2 = mb_strtolower(trim((string) $sheet->getCell('B1')->getValue()));
        $h3 = mb_strtolower(trim((string) $sheet->getCell('C1')->getValue()));
        $h4 = mb_strtolower(trim((string) $sheet->getCell('D1')->getValue()));
        $h5 = mb_strtolower(trim((string) $sheet->getCell('E1')->getValue()));
        $h6 = mb_strtolower(trim((string) $sheet->getCell('F1')->getValue()));

        // Pola v2: No, Hari, Tanggal, Sesi, Jam
        $isV2Base = str_starts_with($h1, 'no')
            && str_contains($h2, 'hari')
            && str_contains($h3, 'tanggal')
            && str_contains($h4, 'sesi')
            && str_contains($h5, 'jam');

        if ($isV2Base) {
            if (str_contains($h6, 'mata pelajaran') || $h6 === 'mapel') {
                return 'v2_per_tingkat';
            }

            // Jika kolom F berisi nama rombel (mis. 1A atau nama kelas)
            $classes = SchoolClass::all();
            $rawF1 = (string) $sheet->getCell('F1')->getValue();
            if (ClassLabelResolver::resolveClassByLabel($rawF1, $classes) || preg_match('/^[1-6][a-zA-Z]/', trim($rawF1))) {
                return 'v2_per_rombel';
            }

            throw new \InvalidArgumentException(
                "Header file terdeteksi sebagai template v2 (kolom A-E), namun kolom F ('{$rawF1}') tidak dikenali sebagai 'Mata Pelajaran' maupun rombel kelas."
            );
        }

        // Pola Legacy 5 kolom: No, Matapelajaran, tanggal, sesi, Kelas
        $isLegacy = str_starts_with($h1, 'no')
            && (str_contains($h2, 'mata') || str_contains($h2, 'mapel'))
            && str_contains($h3, 'tanggal')
            && str_contains($h4, 'sesi')
            && str_contains($h5, 'kelas');

        if ($isLegacy) {
            return 'legacy';
        }

        throw new \InvalidArgumentException(
            'Format header tidak dikenali. Gunakan template resmi (v2) atau template lama 5 kolom.'
        );
    }

    /**
     * Parser Template v2: Kelas Bawah (Mode Per Rombel).
     */
    protected function importPerRombel(Worksheet $sheet, int $periodeId): array
    {
        $importedCount = 0;
        $replacedCount = 0;
        $skipped = [];

        $allClasses = SchoolClass::all();
        $allSubjects = Subject::all();
        $activeSessions = UjianSesi::where('is_active', true)->orderBy('sesi_ke')->get()->keyBy('sesi_ke');

        $highestColumn = $sheet->getHighestColumn();
        $highestColIndex = Coordinate::columnIndexFromString($highestColumn);
        $highestRow = $sheet->getHighestRow();

        // Petakan kolom F+ ke Model SchoolClass
        $columnClassMap = [];
        for ($col = 6; $col <= $highestColIndex; $col++) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $rawHeader = trim((string) $sheet->getCell([$col, 1])->getValue());
            if ($rawHeader === '') {
                continue;
            }

            $matchedClass = ClassLabelResolver::resolveClassByLabel($rawHeader, $allClasses);
            if ($matchedClass) {
                $columnClassMap[$colLetter] = $matchedClass;
            } else {
                $skipped[] = "Header kolom {$colLetter} ('{$rawHeader}'): Kelas tidak ditemukan di sistem, dilewati.";
            }
        }

        $scope = $this->resolveImportScope($sheet, 'v2_per_rombel', $allClasses, $columnClassMap);

        DB::transaction(function () use (
            $sheet,
            $periodeId,
            $highestRow,
            $columnClassMap,
            $allClasses,
            $allSubjects,
            $activeSessions,
            $scope,
            &$replacedCount,
            &$importedCount,
            &$skipped
        ) {
            $replacedCount = $this->clearExistingSchedules($periodeId, $scope['class_ids'], $scope['levels'], $allClasses);

            for ($row = 2; $row <= $highestRow; $row++) {
                $noRaw = trim((string) $sheet->getCell("A{$row}")->getValue());
                $tanggalRaw = $sheet->getCell("C{$row}")->getValue();
                $sesiRaw = trim((string) $sheet->getCell("D{$row}")->getValue());
                $jamRaw = trim((string) $sheet->getCell("E{$row}")->getValue());

                // Lewati baris kosong
                if ($noRaw === '' && $tanggalRaw === null) {
                    continue;
                }

                $tanggalUjian = $this->parseDate($tanggalRaw);
                if (! $tanggalUjian) {
                    $skipped[] = "Baris {$row}: Format tanggal '{$tanggalRaw}' tidak valid.";

                    continue;
                }

                $sesiKe = max(1, (int) preg_replace('/\D/', '', $sesiRaw));
                [$jamMulai, $jamSelesai, $durasiMenit] = $this->parseJam($jamRaw, $sesiKe, $activeSessions);

                // Proses setiap rombel pada baris ini
                foreach ($columnClassMap as $colLetter => $class) {
                    $mapelCell = trim((string) $sheet->getCell("{$colLetter}{$row}")->getValue());
                    if ($mapelCell === '') {
                        continue;
                    }

                    $matchedSubject = $allSubjects->first(function ($s) use ($mapelCell) {
                        return strcasecmp($s->name, $mapelCell) === 0 || strcasecmp($s->code ?? '', $mapelCell) === 0;
                    });

                    $subjectId = $matchedSubject?->id;
                    $namaMapelKustom = $matchedSubject ? null : $mapelCell;
                    $tingkat = ClassLabelResolver::levelFromName($class->name) ?? '1';

                    // Cek Bentrok Spesifik Rombel
                    $conflict = $this->conflictService->checkClassScheduleConflict(
                        $periodeId,
                        $class->id,
                        null,
                        $tanggalUjian->format('Y-m-d'),
                        $jamMulai,
                        $jamSelesai
                    );

                    $classLabel = ClassLabelResolver::displayLabel($class->name);

                    if ($conflict) {
                        throw new DomainException("Impor dibatalkan: Baris {$row} [{$classLabel}] bentrok secara internal dengan baris jadwal lain di file Excel ({$conflict->nama_mapel} pada {$tanggalUjian->format('d/m/Y')} {$jamMulai}-{$jamSelesai}).");
                    }

                    UjianJadwal::create([
                        'ujian_periode_id' => $periodeId,
                        'subject_id' => $subjectId,
                        'nama_mapel_kustom' => $namaMapelKustom,
                        'school_class_id' => $class->id,
                        'tingkat' => $tingkat,
                        'tanggal_ujian' => $tanggalUjian->format('Y-m-d'),
                        'jam_mulai' => $jamMulai,
                        'jam_selesai' => $jamSelesai,
                        'sesi_ke' => $sesiKe,
                        'durasi_menit' => $durasiMenit,
                        'jenis_soal' => 'Tertulis',
                        'catatan' => 'Import Template Excel v2 (Bawah)',
                    ]);

                    $importedCount++;
                }
            }
        });

        return [
            'imported' => $importedCount,
            'replaced' => $replacedCount,
            'skipped' => $skipped,
            'format' => 'Template v2 Kelas Bawah',
        ];
    }

    /**
     * Parser Template v2: Kelas Atas (Mode Seragam Tingkat).
     */
    protected function importPerTingkat(Worksheet $sheet, int $periodeId): array
    {
        $importedCount = 0;
        $replacedCount = 0;
        $skipped = [];

        $allClasses = SchoolClass::all();
        $allSubjects = Subject::all();
        $activeSessions = UjianSesi::where('is_active', true)->orderBy('sesi_ke')->get()->keyBy('sesi_ke');
        $highestRow = $sheet->getHighestRow();

        $scope = $this->resolveImportScope($sheet, 'v2_per_tingkat', $allClasses);

        DB::transaction(function () use (
            $sheet,
            $periodeId,
            $highestRow,
            $allClasses,
            $allSubjects,
            $activeSessions,
            $scope,
            &$replacedCount,
            &$importedCount,
            &$skipped
        ) {
            $replacedCount = $this->clearExistingSchedules($periodeId, $scope['class_ids'], $scope['levels'], $allClasses);

            for ($row = 2; $row <= $highestRow; $row++) {
                $noRaw = trim((string) $sheet->getCell("A{$row}")->getValue());
                $tanggalRaw = $sheet->getCell("C{$row}")->getValue();
                $sesiRaw = trim((string) $sheet->getCell("D{$row}")->getValue());
                $jamRaw = trim((string) $sheet->getCell("E{$row}")->getValue());
                $mapelRaw = trim((string) $sheet->getCell("F{$row}")->getValue());
                $tingkatRaw = trim((string) $sheet->getCell("G{$row}")->getValue());
                $jenisSoalRaw = trim((string) $sheet->getCell("H{$row}")->getValue());

                if ($noRaw === '' && $mapelRaw === '') {
                    continue;
                }

                if ($mapelRaw === '') {
                    $skipped[] = "Baris {$row}: Kolom Mata Pelajaran kosong, dilewati.";

                    continue;
                }

                $tanggalUjian = $this->parseDate($tanggalRaw);
                if (! $tanggalUjian) {
                    $skipped[] = "Baris {$row}: Format tanggal '{$tanggalRaw}' tidak valid.";

                    continue;
                }

                $sesiKe = max(1, (int) preg_replace('/\D/', '', $sesiRaw));
                [$jamMulai, $jamSelesai, $durasiMenit] = $this->parseJam($jamRaw, $sesiKe, $activeSessions);

                $matchedSubject = $allSubjects->first(function ($s) use ($mapelRaw) {
                    return strcasecmp($s->name, $mapelRaw) === 0 || strcasecmp($s->code ?? '', $mapelRaw) === 0;
                });

                $subjectId = $matchedSubject?->id;
                $namaMapelKustom = $matchedSubject ? null : $mapelRaw;

                // Resolusi Target Tingkat
                $schoolClassId = null;
                $tingkat = null;

                $matchedClass = ClassLabelResolver::resolveClassByLabel($tingkatRaw, $allClasses);
                if ($matchedClass) {
                    $schoolClassId = $matchedClass->id;
                    $tingkat = null;
                } else {
                    $tingkat = $tingkatRaw !== '' ? $tingkatRaw : '3, 4, 5, 6';
                }

                $jenisSoal = in_array(strtolower($jenisSoalRaw), ['praktik', 'lisan', 'tertulis'])
                    ? ucfirst(strtolower($jenisSoalRaw))
                    : 'Tertulis';

                // Cek Bentrok
                $conflict = $this->conflictService->checkClassScheduleConflict(
                    $periodeId,
                    $schoolClassId,
                    $tingkat,
                    $tanggalUjian->format('Y-m-d'),
                    $jamMulai,
                    $jamSelesai
                );

                if ($conflict) {
                    $targetStr = $conflict->schoolClass?->name ?? ($conflict->tingkat ? "Tingkat {$conflict->tingkat}" : 'Semua');
                    throw new DomainException("Impor dibatalkan: Baris {$row} bentrok secara internal dengan baris jadwal lain di file Excel ({$conflict->nama_mapel} - {$targetStr} pada {$tanggalUjian->format('d/m/Y')} {$jamMulai}-{$jamSelesai}).");
                }

                UjianJadwal::create([
                    'ujian_periode_id' => $periodeId,
                    'subject_id' => $subjectId,
                    'nama_mapel_kustom' => $namaMapelKustom,
                    'school_class_id' => $schoolClassId,
                    'tingkat' => $tingkat,
                    'tanggal_ujian' => $tanggalUjian->format('Y-m-d'),
                    'jam_mulai' => $jamMulai,
                    'jam_selesai' => $jamSelesai,
                    'sesi_ke' => $sesiKe,
                    'durasi_menit' => $durasiMenit,
                    'jenis_soal' => $jenisSoal,
                    'catatan' => 'Import Template Excel v2 (Atas)',
                ]);

                $importedCount++;
            }
        });

        return [
            'imported' => $importedCount,
            'replaced' => $replacedCount,
            'skipped' => $skipped,
            'format' => 'Template v2 Kelas Atas',
        ];
    }

    /**
     * Parser Template Legacy 5 Kolom.
     */
    protected function importLegacy(Worksheet $sheet, int $periodeId): array
    {
        $importedCount = 0;
        $replacedCount = 0;
        $skipped = [];

        $allClasses = SchoolClass::all();
        $allSubjects = Subject::all();
        $activeSessions = UjianSesi::where('is_active', true)->orderBy('sesi_ke')->get()->keyBy('sesi_ke');
        $highestRow = $sheet->getHighestRow();

        $scope = $this->resolveImportScope($sheet, 'legacy', $allClasses);

        DB::transaction(function () use (
            $sheet,
            $periodeId,
            $highestRow,
            $allClasses,
            $allSubjects,
            $activeSessions,
            $scope,
            &$replacedCount,
            &$importedCount,
            &$skipped
        ) {
            $replacedCount = $this->clearExistingSchedules($periodeId, $scope['class_ids'], $scope['levels'], $allClasses);

            for ($row = 2; $row <= $highestRow; $row++) {
                $noRaw = trim((string) $sheet->getCell("A{$row}")->getValue());
                $mapelName = trim((string) $sheet->getCell("B{$row}")->getValue());
                $tanggalRaw = $sheet->getCell("C{$row}")->getValue();
                $sesiRaw = trim((string) $sheet->getCell("D{$row}")->getValue());
                $kelasRaw = trim((string) $sheet->getCell("E{$row}")->getValue());

                if ($noRaw === '' && $mapelName === '') {
                    continue;
                }

                if ($mapelName === '') {
                    $skipped[] = "Baris {$row}: Nama mata pelajaran kosong.";

                    continue;
                }

                $tanggalUjian = $this->parseDate($tanggalRaw);
                if (! $tanggalUjian) {
                    $skipped[] = "Baris {$row}: Format tanggal '{$tanggalRaw}' tidak valid (gunakan format dd-mm-yyyy).";

                    continue;
                }

                $sesiKe = max(1, (int) preg_replace('/\D/', '', $sesiRaw));
                $sesiObj = $activeSessions->get($sesiKe) ?? $activeSessions->first();
                $jamMulai = $sesiObj ? substr($sesiObj->jam_mulai, 0, 5) : '07:30';
                $jamSelesai = $sesiObj ? substr($sesiObj->jam_selesai, 0, 5) : '09:30';
                $durasiMenit = $sesiObj ? (int) $sesiObj->durasi_menit : 120;

                $matchedSubject = $allSubjects->first(function ($s) use ($mapelName) {
                    return strcasecmp($s->name, $mapelName) === 0 || strcasecmp($s->code ?? '', $mapelName) === 0;
                });
                $subjectId = $matchedSubject?->id;
                $namaMapelKustom = $matchedSubject ? null : $mapelName;

                $schoolClassId = null;
                $tingkat = null;

                $matchedClass = $allClasses->first(function ($c) use ($kelasRaw) {
                    return strcasecmp($c->name, $kelasRaw) === 0;
                });

                if (! $matchedClass) {
                    $matchedClass = ClassLabelResolver::resolveClassByLabel($kelasRaw, $allClasses);
                }

                if ($matchedClass) {
                    $schoolClassId = $matchedClass->id;
                    $tingkat = null;
                } else {
                    $cleanStr = strtolower(trim($kelasRaw));
                    if (in_array($cleanStr, ['123456', 'semua', 'semua kelas', '1-6'], true)) {
                        $tingkat = '1, 2, 3, 4, 5, 6';
                    } elseif (in_array($cleanStr, ['123', '1-3', 'rendah'], true)) {
                        $tingkat = '1, 2, 3';
                    } elseif (in_array($cleanStr, ['456', '4-6', 'atas'], true)) {
                        $tingkat = '4, 5, 6';
                    } else {
                        preg_match_all('/[1-6]/', $cleanStr, $digits);
                        $uniqueDigits = array_unique($digits[0] ?? []);
                        sort($uniqueDigits, SORT_NATURAL);
                        $tingkat = ! empty($uniqueDigits) ? implode(', ', $uniqueDigits) : $kelasRaw;
                    }
                }

                $conflict = $this->conflictService->checkClassScheduleConflict(
                    $periodeId,
                    $schoolClassId,
                    $tingkat,
                    $tanggalUjian->format('Y-m-d'),
                    $jamMulai,
                    $jamSelesai
                );

                if ($conflict) {
                    $targetStr = $conflict->schoolClass?->name ?? ($conflict->tingkat ? "Tingkat {$conflict->tingkat}" : 'Semua');
                    throw new DomainException("Impor dibatalkan: Baris {$row} bentrok secara internal dengan baris jadwal lain di file Excel ({$conflict->nama_mapel} - {$targetStr} pada {$tanggalUjian->format('d/m/Y')} ({$jamMulai}-{$jamSelesai}).");
                }

                UjianJadwal::create([
                    'ujian_periode_id' => $periodeId,
                    'subject_id' => $subjectId,
                    'nama_mapel_kustom' => $namaMapelKustom,
                    'school_class_id' => $schoolClassId,
                    'tingkat' => $tingkat,
                    'tanggal_ujian' => $tanggalUjian->format('Y-m-d'),
                    'jam_mulai' => $jamMulai,
                    'jam_selesai' => $jamSelesai,
                    'sesi_ke' => $sesiKe,
                    'durasi_menit' => $durasiMenit,
                    'jenis_soal' => 'Tertulis',
                    'catatan' => 'Import Template Excel',
                ]);

                $importedCount++;
            }
        });

        return [
            'imported' => $importedCount,
            'replaced' => $replacedCount,
            'skipped' => $skipped,
            'format' => 'Template Excel Legacy',
        ];
    }

    /**
     * Parsing tanggal dari berbagai format Excel.
     */
    protected function parseDate(mixed $raw): ?Carbon
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        try {
            if (is_numeric($raw)) {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($raw))->startOfDay();
            }

            $clean = trim((string) $raw);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $clean)) {
                return Carbon::createFromFormat('Y-m-d', $clean)->startOfDay();
            }

            if (preg_match('/^\d{2}[-\/]\d{2}[-\/]\d{4}$/', $clean)) {
                $clean = str_replace('/', '-', $clean);

                return Carbon::createFromFormat('d-m-Y', $clean)->startOfDay();
            }

            return Carbon::parse($clean)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Parsing rentang jam ujian (contoh: "07:30 - 09:00").
     */
    protected function parseJam(string $jamRaw, int $sesiKe, $activeSessions): array
    {
        if (preg_match('/(\d{1,2}:\d{2})\s*[-–]\s*(\d{1,2}:\d{2})/', $jamRaw, $m)) {
            $mulai = str_pad($m[1], 5, '0', STR_PAD_LEFT);
            $selesai = str_pad($m[2], 5, '0', STR_PAD_LEFT);
            $durasi = 90;
            try {
                $t1 = Carbon::createFromFormat('H:i', $mulai);
                $t2 = Carbon::createFromFormat('H:i', $selesai);
                if ($t2->gt($t1)) {
                    $durasi = $t1->diffInMinutes($t2);
                }
            } catch (\Throwable) {
                $durasi = 90;
            }

            return [$mulai, $selesai, $durasi];
        }

        // Fallback ke master sesi aktif
        $sesiObj = $activeSessions->get($sesiKe) ?? $activeSessions->first();
        if ($sesiObj) {
            $mulai = substr($sesiObj->jam_mulai, 0, 5);
            $selesai = substr($sesiObj->jam_selesai, 0, 5);
            $durasi = (int) $sesiObj->durasi_menit ?: 90;

            return [$mulai, $selesai, $durasi];
        }

        // Fallback default
        return match ($sesiKe) {
            1 => ['07:30', '09:30', 120],
            2 => ['10:00', '12:00', 120],
            default => ['08:00', '09:30', 90],
        };
    }
}
