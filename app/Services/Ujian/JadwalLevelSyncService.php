<?php

namespace App\Services\Ujian;

use App\Models\SchoolClass;
use App\Models\Ujian\UjianJadwal;
use App\Models\Ujian\UjianPeriode;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class JadwalLevelSyncService
{
    public function __construct(
        protected JadwalConflictService $conflictService
    ) {}

    /**
     * Mengambil jadwal pada periode yang berlaku untuk tingkat tertentu.
     *
     * @return Collection<int, UjianJadwal>
     */
    public function getSchedulesForLevel(UjianPeriode $periode, int $level): Collection
    {
        return UjianJadwal::with(['subject', 'schoolClass'])
            ->where('ujian_periode_id', $periode->id)
            ->get()
            ->filter(function (UjianJadwal $j) use ($level) {
                return in_array((string) $level, $j->getTargetLevels(), true);
            })
            ->sortBy(function (UjianJadwal $j) {
                $tgl = $j->tanggal_ujian instanceof Carbon ? $j->tanggal_ujian->format('Y-m-d') : (string) $j->tanggal_ujian;

                return $tgl.' '.$j->jam_mulai;
            })
            ->values();
    }

    /**
     * Preview penyalinan jadwal dari tingkat asal ke tingkat-tingkat tujuan.
     *
     * @param  array<int, int>  $targetLevels
     * @return array<string, mixed>
     */
    public function getSyncPreview(UjianPeriode $periode, int $sourceLevel, array $targetLevels): array
    {
        $sourceSchedules = $this->getSchedulesForLevel($periode, $sourceLevel);

        $previewItems = [];

        foreach ($targetLevels as $targetLevel) {
            $targetLevel = (int) $targetLevel;
            if ($targetLevel === $sourceLevel) {
                continue;
            }

            $existingTargetSchedules = $this->getSchedulesForLevel($periode, $targetLevel);

            foreach ($sourceSchedules as $source) {
                $tglStr = $source->tanggal_ujian instanceof Carbon ? $source->tanggal_ujian->format('Y-m-d') : (string) $source->tanggal_ujian;

                // Cek apakah ada jadwal target di slot waktu yang sama
                $conflicting = $existingTargetSchedules->first(function (UjianJadwal $ex) use ($tglStr, $source) {
                    $exTgl = $ex->tanggal_ujian instanceof Carbon ? $ex->tanggal_ujian->format('Y-m-d') : (string) $ex->tanggal_ujian;
                    if ($exTgl !== $tglStr) {
                        return false;
                    }

                    return $this->conflictService->isTimeOverlapping(
                        $source->jam_mulai,
                        $source->jam_selesai,
                        $ex->jam_mulai,
                        $ex->jam_selesai
                    );
                });

                $status = 'new';
                $conflictInfo = null;

                if ($conflicting) {
                    $isSameSubject = ($conflicting->subject_id && $conflicting->subject_id === $source->subject_id)
                        || ($conflicting->nama_mapel_kustom && $conflicting->nama_mapel_kustom === $source->nama_mapel_kustom);

                    $status = $isSameSubject ? 'duplicate' : 'conflict';
                    $conflictInfo = [
                        'id' => $conflicting->id,
                        'nama_mapel' => $conflicting->nama_mapel,
                        'jam_mulai' => $conflicting->jam_mulai,
                        'jam_selesai' => $conflicting->jam_selesai,
                    ];
                }

                $previewItems[] = [
                    'source_id' => $source->id,
                    'nama_mapel' => $source->nama_mapel,
                    'tanggal' => $tglStr,
                    'jam_mulai' => $source->jam_mulai,
                    'jam_selesai' => $source->jam_selesai,
                    'sesi_ke' => $source->sesi_ke,
                    'jenis_soal' => $source->jenis_soal,
                    'source_level' => $sourceLevel,
                    'target_level' => $targetLevel,
                    'status' => $status,
                    'conflict_with' => $conflictInfo,
                ];
            }
        }

        return [
            'source_level' => $sourceLevel,
            'source_count' => $sourceSchedules->count(),
            'target_levels' => array_values(array_filter($targetLevels, fn ($l) => (int) $l !== $sourceLevel)),
            'items' => $previewItems,
        ];
    }

    /**
     * Menyalin jadwal dari satu tingkat ke tingkat tujuan dengan mode 'skip' atau 'replace'.
     *
     * @param  array<int, int>  $targetLevels
     * @return array{copied: int, replaced: int, skipped: int}
     */
    public function syncFromLevel(
        UjianPeriode $periode,
        int $sourceLevel,
        array $targetLevels,
        string $mode = 'skip'
    ): array {
        $sourceSchedules = $this->getSchedulesForLevel($periode, $sourceLevel);

        $copied = 0;
        $replaced = 0;
        $skipped = 0;

        $targetLevels = array_unique(array_map('intval', $targetLevels));

        DB::transaction(function () use (
            $periode,
            $sourceLevel,
            $targetLevels,
            $sourceSchedules,
            $mode,
            &$copied,
            &$replaced,
            &$skipped
        ) {
            // Ambil semua rombel di tingkat tujuan untuk mapping paralel jika sumber spesifik rombel
            $targetClasses = SchoolClass::whereIn('level', $targetLevels)->get();

            foreach ($targetLevels as $targetLevel) {
                if ($targetLevel === $sourceLevel) {
                    continue;
                }

                if ($mode === 'replace') {
                    // Hapus seluruh jadwal eksisting milik tingkat tujuan agar menjadi 100% klon tingkat sumber
                    $existingTargetSchedules = $this->getSchedulesForLevel($periode, $targetLevel);
                    foreach ($existingTargetSchedules as $ex) {
                        $ex->pengawas()->delete();
                        $ex->delete();
                        $replaced++;
                    }
                }

                foreach ($sourceSchedules as $source) {
                    $tglStr = $source->tanggal_ujian instanceof Carbon ? $source->tanggal_ujian->format('Y-m-d') : (string) $source->tanggal_ujian;

                    if ($mode === 'skip') {
                        // Cari jadwal target yang sedang ada di slot waktu ini
                        $currentSchedules = $this->getSchedulesForLevel($periode, $targetLevel);
                        $conflicting = $currentSchedules->first(function (UjianJadwal $ex) use ($tglStr, $source) {
                            $exTgl = $ex->tanggal_ujian instanceof Carbon ? $ex->tanggal_ujian->format('Y-m-d') : (string) $ex->tanggal_ujian;
                            if ($exTgl !== $tglStr) {
                                return false;
                            }

                            return $this->conflictService->isTimeOverlapping(
                                $source->jam_mulai,
                                $source->jam_selesai,
                                $ex->jam_mulai,
                                $ex->jam_selesai
                            );
                        });

                        if ($conflicting) {
                            $skipped++;

                            continue;
                        }
                    }

                    // Tentukan school_class_id atau tingkat target
                    $assignedClassId = null;
                    if ($source->school_class_id && $source->schoolClass) {
                        $sourceClassName = trim($source->schoolClass->name);
                        // Ambil huruf rombel terakhir, e.g. 'A' dari '4A' atau '4 A'
                        preg_match('/[A-Za-z]+$/', $sourceClassName, $suffixMatches);
                        $suffix = $suffixMatches[0] ?? '';

                        $matchedClass = $targetClasses->first(function ($tc) use ($targetLevel, $suffix) {
                            if ((int) $tc->level !== $targetLevel) {
                                return false;
                            }
                            if (empty($suffix)) {
                                return true;
                            }

                            return str_ends_with(strtoupper(trim($tc->name)), strtoupper($suffix));
                        });

                        $assignedClassId = $matchedClass?->id;
                    }

                    // Buat jadwal baru di tingkat sasaran
                    UjianJadwal::create([
                        'ujian_periode_id' => $periode->id,
                        'subject_id' => $source->subject_id,
                        'nama_mapel_kustom' => $source->nama_mapel_kustom,
                        'school_class_id' => $assignedClassId,
                        'tingkat' => $assignedClassId ? null : (string) $targetLevel,
                        'tanggal_ujian' => $source->tanggal_ujian,
                        'jam_mulai' => $source->jam_mulai,
                        'jam_selesai' => $source->jam_selesai,
                        'sesi_ke' => $source->sesi_ke,
                        'durasi_menit' => $source->durasi_menit,
                        'jenis_soal' => $source->jenis_soal,
                        'catatan' => $source->catatan,
                    ]);

                    $copied++;
                }
            }
        });

        return [
            'copied' => $copied,
            'replaced' => $replaced,
            'skipped' => $skipped,
        ];
    }
}
