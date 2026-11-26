<?php

namespace App\Services\Ujian;

use App\Models\Teacher;
use App\Models\Ujian\UjianJadwal;
use App\Models\Ujian\UjianJadwalPengawas;
use App\Models\Ujian\UjianPanitia;
use App\Models\Ujian\UjianPengawasMapping;
use App\Models\Ujian\UjianPengawasSwapHistory;
use App\Models\Ujian\UjianPeriode;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PengawasMappingService
{
    public function __construct(
        protected JadwalConflictService $conflictService,
        protected PengawasTeachingResolverService $teachingResolver
    ) {}

    /**
     * Menyimpan atau memperbarui pemetaan tingkat kelas dan mode penugasan untuk seorang guru.
     */
    public function saveMapping(
        int $periodeId,
        int $teacherId,
        array $levels,
        ?string $keterangan = null,
        string $modePenugasan = 'rotasi',
        ?array $assignedSubjectIds = null,
        ?array $assignedClassIds = null
    ): UjianPengawasMapping {
        $teacher = Teacher::with('user')->findOrFail($teacherId);

        if ($teacher->isPanitiaUjian($periodeId)) {
            throw ValidationException::withMessages([
                'teacher_id' => "Guru {$teacher->user?->name} terdaftar sebagai Panitia Ujian aktif dan tidak dapat dipetakan sebagai pengawas.",
            ]);
        }

        $cleanedLevels = array_values(array_unique(array_filter(array_map('strval', $levels))));
        $normalizedMode = in_array($modePenugasan, ['rotasi', 'pengampu_mapel', 'wali_kelas'], true) ? $modePenugasan : 'rotasi';
        $cleanedSubjects = ! empty($assignedSubjectIds) ? array_values(array_unique(array_map('intval', $assignedSubjectIds))) : null;
        $cleanedClasses = ! empty($assignedClassIds) ? array_values(array_unique(array_map('intval', $assignedClassIds))) : null;

        return UjianPengawasMapping::updateOrCreate(
            [
                'ujian_periode_id' => $periodeId,
                'teacher_id' => $teacherId,
            ],
            [
                'assigned_levels' => $cleanedLevels,
                'mode_penugasan' => $normalizedMode,
                'assigned_subject_ids' => $cleanedSubjects,
                'assigned_class_ids' => $cleanedClasses,
                'keterangan' => $keterangan,
            ]
        );
    }

    /**
     * Menetapkan pemetaan otomatis berbasis data wali kelas dan penugasan mengajar guru pada tingkat target (default Kelas 1-2).
     * Mengecualikan guru yang merupakan panitia ujian aktif.
     */
    public function syncMappingFromTeaching(
        int $periodeId,
        array $levels = ['1', '2'],
        ?array $subjectIds = null,
        bool $includeHomeroom = true,
        bool $lockHomeroomOnly = false
    ): int {
        $periode = UjianPeriode::findOrFail($periodeId);
        $cleanedLevels = array_values(array_unique(array_filter(array_map('strval', $levels))));
        if (empty($cleanedLevels)) {
            return 0;
        }

        $cleanedSubjectIds = ! empty($subjectIds)
            ? array_values(array_unique(array_map('intval', $subjectIds)))
            : null;

        $teachingSummary = $this->teachingResolver->buildTeacherTeachingSummary(
            $periode->academic_year_id,
            $cleanedLevels
        );
        $homeroomMap = $includeHomeroom
            ? $this->teachingResolver->buildHomeroomMap($periode->academic_year_id, $cleanedLevels)
            : [];

        $candidateTeacherIds = array_values(array_unique(array_merge(
            array_keys($teachingSummary),
            array_keys($homeroomMap)
        )));

        if (empty($candidateTeacherIds)) {
            return 0;
        }

        $excludedPanitiaIds = UjianPanitia::where('ujian_periode_id', $periodeId)
            ->where('is_active', true)
            ->pluck('teacher_id')
            ->all();

        $activeTeacherIds = Teacher::whereIn('id', $candidateTeacherIds)
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->whereNotIn('id', $excludedPanitiaIds)
            ->pluck('id')
            ->all();

        $existingMappings = UjianPengawasMapping::where('ujian_periode_id', $periodeId)
            ->whereIn('teacher_id', $activeTeacherIds)
            ->get()
            ->keyBy('teacher_id');

        $count = 0;
        foreach ($activeTeacherIds as $teacherId) {
            $homeroom = $homeroomMap[$teacherId] ?? null;
            $summary = $teachingSummary[$teacherId] ?? null;

            $isHomeroomTeacher = $homeroom && ! empty($homeroom['levels']);
            if (! $isHomeroomTeacher && (! $summary || empty($summary['levels']))) {
                continue;
            }

            if (! $isHomeroomTeacher && $cleanedSubjectIds !== null && empty(array_intersect($summary['subject_ids'], $cleanedSubjectIds))) {
                continue;
            }

            $existingLevels = array_map('strval', $existingMappings->get($teacherId)?->assigned_levels ?? []);
            $newLevels = array_merge($homeroom['levels'] ?? [], $summary['levels'] ?? []);
            $mergedLevels = array_values(array_unique(array_merge($existingLevels, $newLevels)));
            sort($mergedLevels, SORT_NUMERIC);

            UjianPengawasMapping::updateOrCreate(
                [
                    'ujian_periode_id' => $periodeId,
                    'teacher_id' => $teacherId,
                ],
                [
                    'assigned_levels' => $mergedLevels,
                    'mode_penugasan' => $isHomeroomTeacher ? 'wali_kelas' : 'pengampu_mapel',
                    'assigned_subject_ids' => $isHomeroomTeacher ? null : $cleanedSubjectIds,
                    'assigned_class_ids' => ($isHomeroomTeacher && $lockHomeroomOnly) ? $homeroom['class_ids'] : null,
                ]
            );
            $count++;
        }

        return $count;
    }

    /**
     * Menetapkan tingkat kelas secara bulk untuk banyak guru sekaligus.
     * Otomatis mengecualikan guru yang merupakan panitia ujian aktif.
     */
    public function bulkAssignLevels(int $periodeId, array $teacherIds, array $levels): int
    {
        $cleanedLevels = array_values(array_unique(array_filter(array_map('strval', $levels))));
        $teachers = Teacher::whereIn('id', $teacherIds)->get();

        $count = 0;
        foreach ($teachers as $teacher) {
            // Abaikan jika guru terdaftar sebagai panitia ujian
            if ($teacher->isPanitiaUjian($periodeId)) {
                continue;
            }

            UjianPengawasMapping::updateOrCreate(
                [
                    'ujian_periode_id' => $periodeId,
                    'teacher_id' => $teacher->id,
                ],
                [
                    'assigned_levels' => $cleanedLevels,
                ]
            );
            $count++;
        }

        return $count;
    }

    /**
     * Menghapus pemetaan pengawas secara bulk.
     */
    public function bulkDelete(int $periodeId, array $teacherIds): int
    {
        return UjianPengawasMapping::where('ujian_periode_id', $periodeId)
            ->whereIn('teacher_id', $teacherIds)
            ->delete();
    }

    /**
     * Memeriksa kelayakan guru untuk mengawas jadwal ujian tertentu berdasarkan:
     * 1. Status aktif akun guru
     * 2. Pengecualian panitia ujian
     * 3. Pemetaan tingkat kelas yang sesuai
     *
     * @return array{eligible: bool, reason: ?string}
     */
    public function isTeacherEligibleForJadwal(int $teacherId, UjianJadwal $jadwal): array
    {
        $teacher = Teacher::with(['user', 'pengawasMappings'])->find($teacherId);
        if (! $teacher) {
            return ['eligible' => false, 'reason' => 'Data guru tidak ditemukan.'];
        }

        if (! ($teacher->user?->is_active ?? true)) {
            return ['eligible' => false, 'reason' => "Guru {$teacher->user?->name} berstatus tidak aktif."];
        }

        if ($teacher->isPanitiaUjian($jadwal->ujian_periode_id)) {
            $panitia = UjianPanitia::where('ujian_periode_id', $jadwal->ujian_periode_id)
                ->where('teacher_id', $teacher->id)
                ->where('is_active', true)
                ->first();

            if (! $panitia || ! $panitia->ikut_mengawas) {
                $jabatan = $teacher->activePanitiaUjianRole($jadwal->ujian_periode_id) ?? 'Panitia Ujian';

                return [
                    'eligible' => false,
                    'reason' => "Guru {$teacher->user?->name} adalah {$jabatan} pada periode ini dan tidak diizinkan bertugas sebagai pengawas (ikut_mengawas = false).",
                ];
            }
        }

        $mapping = $teacher->getPengawasMapping($jadwal->ujian_periode_id);
        if (! $mapping || empty($mapping->assigned_levels)) {
            return [
                'eligible' => false,
                'reason' => "Guru {$teacher->user?->name} belum memiliki pemetaan kelompok kelas untuk periode ini.",
            ];
        }

        $targetLevels = $jadwal->getTargetLevels();
        $assignedLevels = array_map('strval', $mapping->assigned_levels);

        $hasMatch = false;
        foreach ($targetLevels as $tl) {
            if (in_array((string) $tl, $assignedLevels, true)) {
                $hasMatch = true;
                break;
            }
        }

        if (! $hasMatch) {
            $formattedTarget = implode(', ', $targetLevels);

            return [
                'eligible' => false,
                'reason' => "Guru {$teacher->user?->name} tidak dipetakan untuk mengawas tingkat kelas ini ({$formattedTarget}). Pemetaannya saat ini: {$mapping->formatted_levels}.",
            ];
        }

        return ['eligible' => true, 'reason' => null];
    }

    /**
     * Mengambil daftar kandidat penugasan yang valid untuk saling bertukar jadwal secara dua arah.
     */
    public function getEligibleSwapCandidates(int $sourceAssignmentId, string $sourceSlot = 'pengawas_1'): Collection
    {
        $sourceAssignment = UjianJadwalPengawas::with(['jadwal.subject', 'jadwal.schoolClass', 'ruang', 'pengawas1.user', 'pengawas2.user'])
            ->find($sourceAssignmentId);

        if (! $sourceAssignment || ! $sourceAssignment->jadwal) {
            return collect();
        }

        $sourceTeacherId = $sourceSlot === 'pengawas_2' ? $sourceAssignment->pengawas_2_id : $sourceAssignment->pengawas_1_id;
        if (! $sourceTeacherId) {
            return collect();
        }

        $periodeId = $sourceAssignment->jadwal->ujian_periode_id;

        // Ambil penugasan lain dalam periode yang sama
        $otherAssignments = UjianJadwalPengawas::with(['jadwal.subject', 'jadwal.schoolClass', 'ruang', 'pengawas1.user', 'pengawas2.user'])
            ->whereHas('jadwal', fn ($q) => $q->where('ujian_periode_id', $periodeId))
            ->where('id', '!=', $sourceAssignmentId)
            ->get();

        $candidates = collect();

        foreach ($otherAssignments as $target) {
            foreach (['pengawas_1', 'pengawas_2'] as $targetSlot) {
                $targetTeacherId = $targetSlot === 'pengawas_2' ? $target->pengawas_2_id : $target->pengawas_1_id;
                if (! $targetTeacherId || $targetTeacherId === $sourceTeacherId) {
                    continue;
                }

                // Cek agar tidak menjadikan guru yang sama sebagai Pengawas 1 sekaligus Pengawas 2 di ruang yang sama
                $sourceOtherId = $sourceSlot === 'pengawas_2' ? $sourceAssignment->pengawas_1_id : $sourceAssignment->pengawas_2_id;
                $targetOtherId = $targetSlot === 'pengawas_2' ? $target->pengawas_1_id : $target->pengawas_2_id;

                if ($sourceOtherId === $targetTeacherId || $targetOtherId === $sourceTeacherId) {
                    continue;
                }

                // Cek kelayakan dua arah
                $check1 = $this->isTeacherEligibleForJadwal($sourceTeacherId, $target->jadwal);
                if (! $check1['eligible']) {
                    continue;
                }

                $check2 = $this->isTeacherEligibleForJadwal($targetTeacherId, $sourceAssignment->jadwal);
                if (! $check2['eligible']) {
                    continue;
                }

                // Cek bentrok jadwal untuk Guru Asal pada jadwal Target (abaikan source assignment)
                $conflictSource = $this->conflictService->checkTeacherSupervisorConflict(
                    $sourceTeacherId,
                    $target->ujian_jadwal_id,
                    $sourceAssignment->id
                );
                if ($conflictSource && $conflictSource->id !== $target->id) {
                    continue;
                }

                // Cek bentrok jadwal untuk Guru Target pada jadwal Asal (abaikan target assignment)
                $conflictTarget = $this->conflictService->checkTeacherSupervisorConflict(
                    $targetTeacherId,
                    $sourceAssignment->ujian_jadwal_id,
                    $target->id
                );
                if ($conflictTarget && $conflictTarget->id !== $sourceAssignment->id) {
                    continue;
                }

                $targetTeacher = $targetSlot === 'pengawas_2' ? $target->pengawas2 : $target->pengawas1;

                $candidates->push([
                    'assignment_id' => $target->id,
                    'slot' => $targetSlot,
                    'slot_label' => $targetSlot === 'pengawas_2' ? 'Pengawas 2' : 'Pengawas 1',
                    'teacher_id' => $targetTeacherId,
                    'teacher_name' => $targetTeacher?->user?->name ?? 'Pengawas',
                    'teacher_nip' => $targetTeacher?->nip ?? '-',
                    'mapel' => $target->jadwal?->nama_mapel,
                    'tanggal' => $target->jadwal?->tanggal_ujian?->format('d/m/Y'),
                    'jam' => substr($target->jadwal?->jam_mulai ?? '', 0, 5).' - '.substr($target->jadwal?->jam_selesai ?? '', 0, 5),
                    'ruang' => $target->ruang?->nama_ruang ?? '-',
                    'target_kelas' => $target->jadwal?->target_kelas,
                ]);
            }
        }

        return $candidates;
    }

    /**
     * Mengeksekusi pertukaran jadwal pengawas antarguru dalam transaksi database.
     *
     * @return array{success: bool, message: string}
     */
    public function swapSupervisors(
        int $sourceAssignmentId,
        string $sourceSlot,
        int $targetAssignmentId,
        string $targetSlot,
        int $userId,
        ?string $alasan = null
    ): array {
        return DB::transaction(function () use (
            $sourceAssignmentId,
            $sourceSlot,
            $targetAssignmentId,
            $targetSlot,
            $userId,
            $alasan
        ) {
            $source = UjianJadwalPengawas::with(['jadwal', 'ruang'])->lockForUpdate()->findOrFail($sourceAssignmentId);
            $target = UjianJadwalPengawas::with(['jadwal', 'ruang'])->lockForUpdate()->findOrFail($targetAssignmentId);

            $sourceColumn = $sourceSlot === 'pengawas_2' ? 'pengawas_2_id' : 'pengawas_1_id';
            $targetColumn = $targetSlot === 'pengawas_2' ? 'pengawas_2_id' : 'pengawas_1_id';

            $teacher1Id = $source->{$sourceColumn};
            $teacher2Id = $target->{$targetColumn};

            if (! $teacher1Id || ! $teacher2Id) {
                throw new \InvalidArgumentException('Salah satu atau kedua slot penugasan tidak memiliki guru pengawas.');
            }

            if ($teacher1Id === $teacher2Id) {
                throw new \InvalidArgumentException('Tidak dapat menukar jadwal dengan diri sendiri.');
            }

            // Cek agar tidak ada guru ganda di satu ruangan
            $sourceOtherId = $sourceSlot === 'pengawas_2' ? $source->pengawas_1_id : $source->pengawas_2_id;
            $targetOtherId = $targetSlot === 'pengawas_2' ? $target->pengawas_1_id : $target->pengawas_2_id;

            if ($sourceOtherId === $teacher2Id || $targetOtherId === $teacher1Id) {
                throw new \InvalidArgumentException(
                    'Pertukaran ditolak: guru tidak boleh menjadi Pengawas 1 sekaligus Pengawas 2 di ruang yang sama.'
                );
            }

            // 1 & 2 & 3. Validasi kelayakan dua arah (aktif, bukan panitia, mapping kelas)
            $check1 = $this->isTeacherEligibleForJadwal($teacher1Id, $target->jadwal);
            if (! $check1['eligible']) {
                throw new \InvalidArgumentException("Pertukaran ditolak: {$check1['reason']}");
            }

            $check2 = $this->isTeacherEligibleForJadwal($teacher2Id, $source->jadwal);
            if (! $check2['eligible']) {
                throw new \InvalidArgumentException("Pertukaran ditolak: {$check2['reason']}");
            }

            // 4. Validasi bentrok waktu pasca pertukaran
            $conflict1 = $this->conflictService->checkTeacherSupervisorConflict(
                $teacher1Id,
                $target->ujian_jadwal_id,
                $source->id
            );
            if ($conflict1 && $conflict1->id !== $target->id) {
                $teacher1Name = Teacher::find($teacher1Id)?->user?->name;
                throw new \InvalidArgumentException("Pertukaran ditolak: {$teacher1Name} mengalami bentrok jam tugas di ruang {$conflict1->ruang?->nama_ruang}.");
            }

            $conflict2 = $this->conflictService->checkTeacherSupervisorConflict(
                $teacher2Id,
                $source->ujian_jadwal_id,
                $target->id
            );
            if ($conflict2 && $conflict2->id !== $source->id) {
                $teacher2Name = Teacher::find($teacher2Id)?->user?->name;
                throw new \InvalidArgumentException("Pertukaran ditolak: {$teacher2Name} mengalami bentrok jam tugas di ruang {$conflict2->ruang?->nama_ruang}.");
            }

            // Eksekusi pertukaran nilai kolom & kunci penugasan agar tidak tertimpa auto-plot non-reset
            $source->{$sourceColumn} = $teacher2Id;
            $source->assignment_source = 'swapped';
            $source->is_locked = true;
            $target->{$targetColumn} = $teacher1Id;
            $target->assignment_source = 'swapped';
            $target->is_locked = true;

            $source->save();
            $target->save();

            // Simpan riwayat audit pertukaran
            UjianPengawasSwapHistory::create([
                'ujian_periode_id' => $source->jadwal->ujian_periode_id,
                'source_pengawas_id' => $source->id,
                'target_pengawas_id' => $target->id,
                'teacher_1_id' => $teacher1Id,
                'teacher_2_id' => $teacher2Id,
                'jadwal_1_id' => $source->ujian_jadwal_id,
                'jadwal_2_id' => $target->ujian_jadwal_id,
                'slot_position_1' => $sourceSlot,
                'slot_position_2' => $targetSlot,
                'alasan' => $alasan,
                'processed_by_user_id' => $userId,
                'status' => 'Berhasil',
            ]);

            return [
                'success' => true,
                'message' => 'Pertukaran tugas pengawas ujian berhasil dilakukan dan dicatat pada riwayat audit.',
            ];
        });
    }
}
