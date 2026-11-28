<?php

namespace App\Services\Supervisi;

use App\Enums\SupervisiStatus;
use App\Models\AcademicYear;
use App\Models\Supervisi\SupervisiAspekPenilaian;
use App\Models\Supervisi\SupervisiDetailHasil;
use App\Models\Supervisi\SupervisiHasil;
use App\Models\Supervisi\SupervisiJadwal;
use App\Models\Supervisi\SupervisiJenisPenilaian;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;

class SupervisiScoringService
{
    public function aspectMaxScore(SupervisiAspekPenilaian $aspek, ?SupervisiJenisPenilaian $jenis = null): int
    {
        if ($aspek->tipe_skala === SupervisiAspekPenilaian::SKALA_PMM) {
            return 3;
        }

        if ($aspek->tipe_skala === SupervisiAspekPenilaian::SKALA_REGULER) {
            return 4;
        }

        return ($jenis?->skor_maksimal ?? 4) === 3 ? 3 : 4;
    }

    public function predikat(float $nilaiAkhir): string
    {
        if ($nilaiAkhir >= 86) {
            return 'Baik Sekali';
        }

        if ($nilaiAkhir >= 70) {
            return 'Baik';
        }

        if ($nilaiAkhir >= 55) {
            return 'Cukup';
        }

        return 'Kurang';
    }

    /**
     * Simpan skor dan catatan untuk satu komponen jenis penilaian lalu hitung ulang agregasi.
     *
     * @param  array<int|string, int|string|null>  $scores
     * @param  array<int|string, string|null>  $notes
     */
    public function saveKomponen(
        SupervisiJadwal $jadwal,
        SupervisiJenisPenilaian $jenis,
        array $scores,
        array $notes = [],
        ?string $rekomendasi = null,
        array $aspectRecommendations = []
    ): SupervisiHasil {
        return DB::transaction(function () use ($jadwal, $jenis, $scores, $notes, $rekomendasi, $aspectRecommendations): SupervisiHasil {
            $aspeks = $jenis->aspekPenilaians()->get();

            $hasil = SupervisiHasil::firstOrCreate(
                [
                    'jadwal_id' => $jadwal->id,
                    'jenis_penilaian_id' => $jenis->id,
                ],
                [
                    'total_skor' => 0,
                    'skor_maksimal' => 0,
                    'nilai_akhir' => 0,
                    'ketercapaian' => null,
                    'rekomendasi' => $rekomendasi,
                ]
            );

            $totalSkor = 0;
            $skorMaksimal = 0;

            SupervisiDetailHasil::where('hasil_id', $hasil->id)->delete();

            $rows = [];
            $now = now();

            foreach ($aspeks as $aspek) {
                $maxAspect = $this->aspectMaxScore($aspek, $jenis);
                $skorMaksimal += $maxAspect;

                $rawSkor = $scores[$aspek->id] ?? null;
                if ($rawSkor === null || $rawSkor === '') {
                    continue;
                }

                $skor = max(1, min($maxAspect, (int) $rawSkor));
                $totalSkor += $skor;

                $rows[] = [
                    'hasil_id' => $hasil->id,
                    'aspek_id' => $aspek->id,
                    'skor' => $skor,
                    'catatan' => $notes[$aspek->id] ?? null,
                    'rekomendasi' => $aspectRecommendations[$aspek->id] ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (! empty($rows)) {
                SupervisiDetailHasil::insert($rows);
            }

            $nilaiAkhir = $skorMaksimal > 0 ? round(($totalSkor / $skorMaksimal) * 100, 2) : 0.0;

            $hasil->update([
                'total_skor' => $totalSkor,
                'skor_maksimal' => $skorMaksimal,
                'nilai_akhir' => $nilaiAkhir,
                'ketercapaian' => $skorMaksimal > 0 ? $this->predikat($nilaiAkhir) : null,
                'rekomendasi' => $rekomendasi ?? $hasil->rekomendasi,
            ]);

            $this->recalculateJadwalSummary($jadwal);

            return $hasil->fresh(['detailHasils.aspek', 'jenisPenilaian']);
        });
    }

    /**
     * Hitung ulang ringkasan nilai akhir & ketercapaian pada jadwal dari seluruh komponen hasil.
     *
     * @return array{total_skor: int, skor_maksimal: int, nilai_akhir: float, ketercapaian: string}
     */
    public function recalculateJadwalSummary(SupervisiJadwal $jadwal): array
    {
        $hasils = $jadwal->hasils()->with(['detailHasils.aspek', 'jenisPenilaian.aspekPenilaians'])->get();

        $grandTotalSkor = 0;
        $grandSkorMaks = 0;

        foreach ($hasils as $hasil) {
            if ($hasil->detailHasils->isEmpty()) {
                continue;
            }

            $jenisTotal = (int) $hasil->detailHasils->sum('skor');
            $masterAspeks = $hasil->jenisPenilaian?->aspekPenilaians ?? collect();

            if ($masterAspeks->isNotEmpty()) {
                $jenisMax = (int) $masterAspeks->sum(
                    fn (SupervisiAspekPenilaian $aspek): int => $this->aspectMaxScore($aspek, $hasil->jenisPenilaian)
                );
            } else {
                $jenisMax = 0;
                foreach ($hasil->detailHasils as $detail) {
                    $jenisMax += $detail->aspek
                        ? $this->aspectMaxScore($detail->aspek, $hasil->jenisPenilaian)
                        : (($hasil->jenisPenilaian?->skor_maksimal ?? 4) === 3 ? 3 : 4);
                }
            }

            $jenisNilai = $jenisMax > 0 ? round(($jenisTotal / $jenisMax) * 100, 2) : 0.0;

            if ((int) $hasil->total_skor !== $jenisTotal || (int) $hasil->skor_maksimal !== $jenisMax) {
                $hasil->update([
                    'total_skor' => $jenisTotal,
                    'skor_maksimal' => $jenisMax,
                    'nilai_akhir' => $jenisNilai,
                    'ketercapaian' => $this->predikat($jenisNilai),
                ]);
            }

            $grandTotalSkor += $jenisTotal;
            $grandSkorMaks += $jenisMax;
        }

        $nilaiAkhir = $grandSkorMaks > 0 ? round(($grandTotalSkor / $grandSkorMaks) * 100, 2) : 0.0;
        $ketercapaian = $grandSkorMaks > 0 ? $this->predikat($nilaiAkhir) : 'Kurang';

        $jadwal->update([
            'nilai_akhir' => $grandSkorMaks > 0 ? $nilaiAkhir : null,
            'ketercapaian' => $grandSkorMaks > 0 ? $ketercapaian : null,
        ]);

        return [
            'total_skor' => $grandTotalSkor,
            'skor_maksimal' => $grandSkorMaks,
            'nilai_akhir' => $nilaiAkhir,
            'ketercapaian' => $ketercapaian,
        ];
    }

    /**
     * Tentukan daftar ID academic_year untuk scope 1 semester atau 1 tahun pelajaran penuh.
     *
     * @return array<int, int>
     */
    public function resolvePeriodYearIds(AcademicYear $referenceYear, string $scope = 'semester'): array
    {
        if ($scope === 'tahun') {
            $ids = AcademicYear::query()
                ->where('name', $referenceYear->name)
                ->pluck('id')
                ->map(fn (mixed $id): int => (int) $id)
                ->all();

            return ! empty($ids) ? $ids : [(int) $referenceYear->id];
        }

        return [(int) $referenceYear->id];
    }

    /**
     * Hitung ringkasan progres 14 form standar seluruh guru dalam 2 query batch (tanpa N+1).
     *
     * @param  array<int, int>  $teacherIds
     * @return array<int, array{done_count: int, partial_count: int, total_forms: int, avg_nilai: float, badge_class: string, label: string}>
     */
    public function buildPortfolioProgressMap(array $teacherIds, ?AcademicYear $referenceYear, string $scope = 'semester'): array
    {
        return $this->buildProgressMapByScope($teacherIds, $referenceYear, $scope, kmaOnly: false);
    }

    /**
     * Hitung ringkasan progres 3 instrumen resmi SK 6334/2021 dalam 2 query batch (terpisah dari 14 form).
     *
     * @param  array<int, int>  $teacherIds
     * @return array<int, array{done_count: int, partial_count: int, total_forms: int, avg_nilai: float, badge_class: string, label: string}>
     */
    public function buildKmaProgressMap(array $teacherIds, ?AcademicYear $referenceYear, string $scope = 'semester'): array
    {
        return $this->buildProgressMapByScope($teacherIds, $referenceYear, $scope, kmaOnly: true);
    }

    /**
     * @param  array<int, int>  $teacherIds
     * @return array<int, array{done_count: int, partial_count: int, total_forms: int, avg_nilai: float, badge_class: string, label: string}>
     */
    private function buildProgressMapByScope(
        array $teacherIds,
        ?AcademicYear $referenceYear,
        string $scope,
        bool $kmaOnly
    ): array {
        $activeJenis = SupervisiJenisPenilaian::withCount('aspekPenilaians')
            ->active()
            ->when(
                $kmaOnly,
                fn ($q) => $q->where('kategori_supervisi', SupervisiJenisPenilaian::KATEGORI_KMA_6334),
                fn ($q) => $q->where('kategori_supervisi', '!=', SupervisiJenisPenilaian::KATEGORI_KMA_6334)
            )
            ->orderBy('id')
            ->get();

        $totalForms = $activeJenis->count();
        $requiredByJenis = $activeJenis->pluck('aspek_penilaians_count', 'id');

        $progress = [];
        foreach ($teacherIds as $tid) {
            $progress[(int) $tid] = [
                'done_count' => 0,
                'partial_count' => 0,
                'total_forms' => $totalForms,
                'avg_nilai' => 0.0,
                'badge_class' => 'bg-rose-200 text-rose-950 border-black',
                'label' => "✕ 0/{$totalForms} Form",
            ];
        }

        if (empty($teacherIds) || ! $referenceYear || $totalForms === 0) {
            return $progress;
        }

        $yearIds = $this->resolvePeriodYearIds($referenceYear, $scope);

        $latestRows = SupervisiHasil::query()
            ->select([
                'supervisi_hasil.id',
                'supervisi_hasil.jenis_penilaian_id',
                'supervisi_hasil.nilai_akhir',
                'supervisi_jadwal.teacher_id as pivot_teacher_id',
            ])
            ->join('supervisi_jadwal', 'supervisi_jadwal.id', '=', 'supervisi_hasil.jadwal_id')
            ->whereIn('supervisi_jadwal.teacher_id', $teacherIds)
            ->whereIn('supervisi_jadwal.academic_year_id', $yearIds)
            ->where('supervisi_jadwal.status', '!=', SupervisiStatus::Batal->value)
            ->withCount('detailHasils')
            ->orderByDesc('supervisi_jadwal.tanggal')
            ->orderByDesc('supervisi_jadwal.id')
            ->get();

        $pickedPerTeacherJenis = [];
        foreach ($latestRows as $row) {
            $tid = (int) $row->pivot_teacher_id;
            $jid = (int) $row->jenis_penilaian_id;

            // Sudah terurut tanggal terbaru & id terbesar -> ambil kemunculan pertama saja
            if (! isset($pickedPerTeacherJenis[$tid][$jid]) && $row->detail_hasils_count > 0) {
                $pickedPerTeacherJenis[$tid][$jid] = $row;
            }
        }

        foreach ($teacherIds as $rawTid) {
            $tid = (int) $rawTid;
            $items = $pickedPerTeacherJenis[$tid] ?? [];

            $doneCount = 0;
            $partialCount = 0;
            $nilaiSum = 0.0;
            $scoredCount = 0;

            foreach ($activeJenis as $jenis) {
                $picked = $items[(int) $jenis->id] ?? null;
                if (! $picked) {
                    continue;
                }

                $required = (int) ($requiredByJenis[$jenis->id] ?? 0);
                if ($required > 0 && (int) $picked->detail_hasils_count >= $required) {
                    $doneCount++;
                } else {
                    $partialCount++;
                }

                $nilaiSum += (float) $picked->nilai_akhir;
                $scoredCount++;
            }

            $avg = $scoredCount > 0 ? round($nilaiSum / $scoredCount, 2) : 0.0;
            if ($doneCount >= $totalForms && $totalForms > 0) {
                $badge = 'bg-emerald-200 text-emerald-950 border-black';
                $label = "✓ {$doneCount}/{$totalForms} Form";
            } elseif ($doneCount > 0 || $partialCount > 0) {
                $badge = 'bg-[#FFD43B] text-black border-black';
                $label = "⏳ {$doneCount}/{$totalForms} Form";
            } else {
                $badge = 'bg-rose-200 text-rose-950 border-black';
                $label = "✕ 0/{$totalForms} Form";
            }

            $progress[$tid] = [
                'done_count' => $doneCount,
                'partial_count' => $partialCount,
                'total_forms' => $totalForms,
                'avg_nilai' => $avg,
                'badge_class' => $badge,
                'label' => $label,
            ];
        }

        return $progress;
    }

    /**
     * Susun portofolio lengkap 14 Form (81 aspek) untuk 1 guru pada 1 semester atau 1 tahun pelajaran.
     *
     * @return array<string, mixed>
     */
    public function buildTeacherSemesterPortfolio(Teacher $teacher, AcademicYear $referenceYear, string $scope = 'semester'): array
    {
        $yearIds = $this->resolvePeriodYearIds($referenceYear, $scope);

        $activeJenisList = SupervisiJenisPenilaian::with('aspekPenilaians')
            ->active()
            ->where('kategori_supervisi', '!=', SupervisiJenisPenilaian::KATEGORI_KMA_6334)
            ->orderByRaw("CASE kategori_supervisi WHEN 'perangkat_ajar' THEN 1 WHEN 'reguler' THEN 2 ELSE 3 END")
            ->orderBy('id')
            ->get();

        $allResults = SupervisiHasil::query()
            ->select('supervisi_hasil.*')
            ->join('supervisi_jadwal', 'supervisi_jadwal.id', '=', 'supervisi_hasil.jadwal_id')
            ->where('supervisi_jadwal.teacher_id', $teacher->id)
            ->whereIn('supervisi_jadwal.academic_year_id', $yearIds)
            ->where('supervisi_jadwal.status', '!=', SupervisiStatus::Batal->value)
            ->with([
                'jadwal.supervisorTeacher.user',
                'jadwal.supervisorUser',
                'jadwal.schoolClass',
                'jadwal.subject',
                'detailHasils',
            ])
            ->orderByDesc('supervisi_jadwal.tanggal')
            ->orderByDesc('supervisi_jadwal.id')
            ->get();

        $latestByJenis = [];
        foreach ($allResults as $hasil) {
            $jid = (int) $hasil->jenis_penilaian_id;
            if (! isset($latestByJenis[$jid]) && $hasil->detailHasils->isNotEmpty()) {
                $latestByJenis[$jid] = $hasil;
            }
        }

        $doneCount = 0;
        $partialCount = 0;
        $grandSkor = 0;
        $grandMax = 0;

        $forms = $activeJenisList->values()->map(function (SupervisiJenisPenilaian $jenis, int $idx) use (
            $latestByJenis,
            &$doneCount,
            &$partialCount,
            &$grandSkor,
            &$grandMax
        ): array {
            /** @var SupervisiHasil|null $hasil */
            $hasil = $latestByJenis[(int) $jenis->id] ?? null;
            $requiredCount = $jenis->aspekPenilaians->count();
            $filledCount = $hasil ? $hasil->detailHasils->count() : 0;

            $formMax = (int) $jenis->aspekPenilaians->sum(
                fn (SupervisiAspekPenilaian $aspek): int => $this->aspectMaxScore($aspek, $jenis)
            );
            $formSkor = $hasil ? (int) $hasil->total_skor : 0;
            $formNilai = $formMax > 0 && $hasil ? round(($formSkor / $formMax) * 100, 2) : 0.0;

            if (! $hasil || $filledCount === 0) {
                $state = 'empty';
                $icon = '✕';
                $statusLabel = 'Belum Dinilai';
                $badgeClass = 'bg-rose-200 text-rose-950 border-black';
            } elseif ($requiredCount > 0 && $filledCount < $requiredCount) {
                $state = 'partial';
                $icon = '⏳';
                $statusLabel = "Sebagian ({$filledCount}/{$requiredCount})";
                $badgeClass = 'bg-[#FFD43B] text-black border-black';
                $partialCount++;
            } else {
                $state = 'done';
                $icon = '✓';
                $statusLabel = 'Lengkap';
                $badgeClass = 'bg-emerald-200 text-emerald-950 border-black';
                $doneCount++;
            }

            $grandSkor += $formSkor;
            $grandMax += $formMax;

            return [
                'nomor' => $idx + 1,
                'jenis' => $jenis,
                'hasil' => $hasil,
                'jadwal' => $hasil?->jadwal,
                'details_by_aspek' => $hasil ? $hasil->detailHasils->keyBy('aspek_id') : collect(),
                'state' => $state,
                'icon' => $icon,
                'status_label' => $statusLabel,
                'badge_class' => $badgeClass,
                'filled_count' => $filledCount,
                'required_count' => $requiredCount,
                'total_skor' => $formSkor,
                'skor_maksimal' => $formMax,
                'nilai_akhir' => $formNilai,
                'ketercapaian' => $hasil ? $this->predikat($formNilai) : '-',
            ];
        });

        $nilaiAkhir = $grandMax > 0 ? round(($grandSkor / $grandMax) * 100, 2) : 0.0;
        $periodLabel = $scope === 'tahun'
            ? "Tahun Pelajaran {$referenceYear->name} (Semester Ganjil & Genap)"
            : "Tahun Ajaran {$referenceYear->name} — Semester ".ucfirst((string) $referenceYear->semester);

        return [
            'teacher' => $teacher,
            'referenceYear' => $referenceYear,
            'scope' => $scope,
            'periodLabel' => $periodLabel,
            'forms' => $forms,
            'doneCount' => $doneCount,
            'partialCount' => $partialCount,
            'totalForms' => $activeJenisList->count(),
            'totalSkor' => $grandSkor,
            'skorMaksimal' => $grandMax,
            'nilaiAkhir' => $nilaiAkhir,
            'ketercapaian' => $grandMax > 0 && ($doneCount > 0 || $partialCount > 0) ? $this->predikat($nilaiAkhir) : 'Belum Dinilai',
        ];
    }
}
