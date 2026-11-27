<?php

namespace App\Services\Supervisi;

use App\Enums\SupervisiStatus;
use App\Models\AcademicYear;
use App\Models\AttendanceSetting;
use App\Models\Supervisi\SupervisiAspekPenilaian;
use App\Models\Supervisi\SupervisiJadwal;
use App\Models\Supervisi\SupervisiJenisPenilaian;
use App\Models\Teacher;
use App\Services\QrCodeService;
use Illuminate\Support\Collection;

class SupervisiPrintService
{
    public function __construct(
        private readonly QrCodeService $qrCodeService,
        private readonly SupervisiSignatureService $signatureService,
        private readonly SupervisiScoringService $scoringService,
    ) {}

    /**
     * Siapkan payload lengkap untuk halaman pratinjau cetak dokumen supervisi.
     *
     * @return array<string, mixed>
     */
    public function buildJadwalPrintPayload(SupervisiJadwal $jadwal, bool $isPolos = false): array
    {
        $jadwal->loadMissing([
            'academicYear',
            'teacher.user',
            'supervisorTeacher.user',
            'supervisorUser',
            'schoolClass',
            'subject',
            'indikatorPmm.aspekPenilaians',
            'praObservasi',
            'dokumenAjars',
            'hasils.jenisPenilaian',
            'hasils.detailHasils.aspek',
            'fotoBuktis',
            'tindakLanjut',
            'coachingTirtas',
            'signatures',
        ]);

        $setting = AttendanceSetting::query()->with('headmasterTeacher.user')->first();
        $signatures = $this->signatureService->getSignaturesByJadwal($jadwal);
        $isFullySigned = $this->signatureService->isFullySigned($jadwal);

        $qrDataUris = [];
        foreach ($signatures as $role => $sig) {
            if ($sig && ! $isPolos) {
                $qrDataUris[$role] = $this->qrCodeService->generateDataUri(
                    route('supervisi.verifikasi', $sig->token_verifikasi),
                    130,
                    2
                );
            } else {
                $qrDataUris[$role] = null;
            }
        }

        $fokusDipilihIds = $jadwal->praObservasi?->fokus_perilaku_dipilih ?? [];
        $fokusDipilihList = ! empty($fokusDipilihIds)
            ? SupervisiAspekPenilaian::whereIn('id', $fokusDipilihIds)->orderBy('urutan')->get()
            : collect();

        $summary = $this->scoringService->recalculateJadwalSummary($jadwal);
        ['komponen' => $ringkasanKomponen, 'summary' => $ringkasanSummary, 'legend' => $kategoriSkorLegend] = $this->buildRingkasanNilai($jadwal);

        return [
            'jadwal' => $jadwal,
            'setting' => $setting,
            'signatures' => $signatures,
            'qrDataUris' => $qrDataUris,
            'isFullySigned' => $isFullySigned,
            'isDraft' => ! $isFullySigned && ! $isPolos,
            'isPolos' => $isPolos,
            'fokusDipilihList' => $fokusDipilihList,
            'summary' => $summary,
            'ringkasanKomponen' => $ringkasanKomponen,
            'ringkasanSummary' => $ringkasanSummary,
            'kategoriSkorLegend' => $kategoriSkorLegend,
            'kotaMadrasah' => $setting?->school_district ?: ($setting?->school_city ?: 'Madrasah'),
            'namaKepala' => $setting?->headmasterTeacher?->user?->name
                ?? $setting?->card_principal_name
                ?? 'Kepala Madrasah',
            'nipKepala' => $setting?->headmasterTeacher?->nip
                ?? $setting?->card_principal_nip
                ?? '-',
        ];
    }

    /**
     * Susun ringkasan nilai berdasarkan Master Instrumen & Aspek Penilaian yang berlaku pada jadwal.
     *
     * @return array{
     *     komponen: Collection<int, array<string, mixed>>,
     *     summary: array{total_skor: int, skor_maksimal: int, nilai_akhir: float, ketercapaian: string},
     *     legend: array<int, array{label: string, range: string, bg: string, color: string}>
     * }
     */
    private function buildRingkasanNilai(SupervisiJadwal $jadwal): array
    {
        $masterJenisList = SupervisiJenisPenilaian::with('aspekPenilaians')
            ->active()
            ->orderByRaw("CASE kategori_supervisi WHEN 'perangkat_ajar' THEN 1 WHEN 'observasi_kelas_pmm' THEN 2 ELSE 3 END")
            ->orderBy('id')
            ->get();

        if ($jadwal->isKurmer()) {
            $filteredJenis = $masterJenisList->filter(
                fn (SupervisiJenisPenilaian $jenis): bool => $jenis->kategori_supervisi === SupervisiJenisPenilaian::KATEGORI_PERANGKAT_AJAR
                    || (int) $jenis->id === (int) $jadwal->indikator_pmm_id
            )->values();
        } else {
            $filteredJenis = $masterJenisList->filter(
                fn (SupervisiJenisPenilaian $jenis): bool => $jenis->kategori_supervisi === SupervisiJenisPenilaian::KATEGORI_REGULER
            )->values();
        }

        $coveredIds = $filteredJenis->pluck('id')->all();
        foreach ($jadwal->hasils as $hasil) {
            if ($hasil->jenisPenilaian && ! in_array($hasil->jenis_penilaian_id, $coveredIds, true) && $hasil->detailHasils->isNotEmpty()) {
                $hasil->jenisPenilaian->loadMissing('aspekPenilaians');
                $filteredJenis->push($hasil->jenisPenilaian);
                $coveredIds[] = $hasil->jenis_penilaian_id;
            }
        }

        $hasilsByJenis = $jadwal->hasils->keyBy('jenis_penilaian_id');
        $totalSkor = 0;
        $totalMaksimal = 0;

        $komponen = $filteredJenis->values()->map(function (SupervisiJenisPenilaian $jenis, int $index) use ($hasilsByJenis, &$totalSkor, &$totalMaksimal): array {
            $hasil = $hasilsByJenis->get($jenis->id);
            $skorDiperoleh = $hasil ? (int) $hasil->total_skor : 0;

            $skorMaksimal = $jenis->aspekPenilaians->sum(
                fn (SupervisiAspekPenilaian $aspek): int => $this->scoringService->aspectMaxScore($aspek, $jenis)
            );
            if ($skorMaksimal === 0 && $hasil) {
                $skorMaksimal = (int) $hasil->skor_maksimal;
            }

            $persentase = $skorMaksimal > 0 ? round(($skorDiperoleh / $skorMaksimal) * 100, 2) : 0.0;
            $totalSkor += $skorDiperoleh;
            $totalMaksimal += $skorMaksimal;

            return [
                'tahap_no' => $index + 1,
                'label' => 'Tahap '.($index + 1).': '.$jenis->nama,
                'skor' => $skorDiperoleh,
                'skor_maksimal' => $skorMaksimal,
                'persentase' => $persentase,
            ];
        });

        $nilaiAkhir = $totalMaksimal > 0 ? round(($totalSkor / $totalMaksimal) * 100, 2) : 0.0;
        $ketercapaian = $totalMaksimal > 0 ? $this->scoringService->predikat($nilaiAkhir) : 'Kurang';

        $legend = [
            ['label' => 'Baik Sekali', 'range' => '86-100%', 'bg' => '#198754', 'color' => '#ffffff'],
            ['label' => 'Baik', 'range' => '70-85%', 'bg' => '#0891b2', 'color' => '#ffffff'],
            ['label' => 'Cukup', 'range' => '55-69%', 'bg' => '#eab308', 'color' => '#111827'],
            ['label' => 'Kurang', 'range' => '0-54%', 'bg' => '#dc3545', 'color' => '#ffffff'],
        ];

        return [
            'komponen' => $komponen,
            'summary' => [
                'total_skor' => $totalSkor,
                'skor_maksimal' => $totalMaksimal,
                'nilai_akhir' => $nilaiAkhir,
                'ketercapaian' => $ketercapaian,
            ],
            'legend' => $legend,
        ];
    }

    /**
     * Siapkan payload cetak 1 file PDF kolektif seluruh hasil supervisi berdasarkan Master Instrumen.
     *
     * @return array<string, mixed>
     */
    public function buildInstrumentRekapPayload(
        string $selectedYearId,
        string $selectedKategori,
        ?int $selectedJenisId,
        string $selectedStatus,
        string $backUrl
    ): array {
        $academicYears = AcademicYear::query()->orderByDesc('start_date')->get();

        $masterJenisList = SupervisiJenisPenilaian::with('aspekPenilaians')
            ->active()
            ->orderByRaw("CASE kategori_supervisi WHEN 'reguler' THEN 1 WHEN 'perangkat_ajar' THEN 2 ELSE 3 END")
            ->orderBy('id')
            ->get();

        $targetInstruments = $masterJenisList
            ->when(
                $selectedKategori !== 'all' && $selectedKategori !== '',
                fn (Collection $col): Collection => $col->where('kategori_supervisi', $selectedKategori)
            )
            ->when(
                $selectedJenisId !== null && $selectedJenisId > 0,
                fn (Collection $col): Collection => $col->where('id', $selectedJenisId)
            )
            ->values();

        $targetJenisIds = $targetInstruments->pluck('id')->all();

        $jadwals = SupervisiJadwal::with([
            'academicYear',
            'teacher.user',
            'supervisorTeacher.user',
            'supervisorUser',
            'schoolClass',
            'subject',
            'indikatorPmm',
            'hasils.jenisPenilaian',
            'hasils.detailHasils',
        ])
            ->whereHas('hasils', function ($query) use ($targetJenisIds): void {
                if (! empty($targetJenisIds)) {
                    $query->whereIn('jenis_penilaian_id', $targetJenisIds);
                }
            })
            ->when(
                $selectedYearId !== 'all' && $selectedYearId !== '',
                fn ($q) => $q->where('academic_year_id', (int) $selectedYearId)
            )
            ->when(
                $selectedStatus !== 'all' && $selectedStatus !== '',
                fn ($q) => $q->where('status', $selectedStatus)
            )
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        $predikatStats = [
            'Baik Sekali' => ['range' => '86-100%', 'count' => 0],
            'Baik' => ['range' => '70-85%', 'count' => 0],
            'Cukup' => ['range' => '55-69%', 'count' => 0],
            'Kurang' => ['range' => '0-54%', 'count' => 0],
        ];

        $matrixRows = $jadwals->map(function (SupervisiJadwal $jadwal) use ($targetInstruments, &$predikatStats): array {
            $hasilsByJenis = $jadwal->hasils->keyBy('jenis_penilaian_id');
            $nilaiPerJenis = [];
            $filteredTotalSkor = 0;
            $filteredMaxSkor = 0;

            foreach ($targetInstruments as $instrument) {
                $hasil = $hasilsByJenis->get($instrument->id);
                if ($hasil && (int) $hasil->skor_maksimal > 0) {
                    $nilaiPerJenis[$instrument->id] = (float) $hasil->nilai_akhir;
                    $filteredTotalSkor += (int) $hasil->total_skor;
                    $filteredMaxSkor += (int) $hasil->skor_maksimal;
                } else {
                    $nilaiPerJenis[$instrument->id] = null;
                }
            }

            $rowNilai = $filteredMaxSkor > 0
                ? round(($filteredTotalSkor / $filteredMaxSkor) * 100, 2)
                : (float) ($jadwal->nilai_akhir ?? 0);

            $rowPredikat = $filteredMaxSkor > 0
                ? $this->scoringService->predikat($rowNilai)
                : ($jadwal->ketercapaian ?: $this->scoringService->predikat($rowNilai));

            if (isset($predikatStats[$rowPredikat])) {
                $predikatStats[$rowPredikat]['count']++;
            }

            return [
                'jadwal' => $jadwal,
                'nilai_per_jenis' => $nilaiPerJenis,
                'nilai_akhir' => $rowNilai,
                'ketercapaian' => $rowPredikat,
            ];
        })->values();

        $instrumentSections = $targetInstruments->map(function (SupervisiJenisPenilaian $jenis) use ($jadwals): ?array {
            $rows = $jadwals->map(function (SupervisiJadwal $jadwal) use ($jenis): ?array {
                $hasil = $jadwal->hasils->firstWhere('jenis_penilaian_id', $jenis->id);
                if (! $hasil || $hasil->detailHasils->isEmpty()) {
                    return null;
                }

                return [
                    'jadwal' => $jadwal,
                    'hasil' => $hasil,
                    'detailsByAspek' => $hasil->detailHasils->keyBy('aspek_id'),
                ];
            })->filter()->values();

            if ($rows->isEmpty()) {
                return null;
            }

            $avgNilai = round((float) $rows->avg(fn (array $r): float => (float) $r['hasil']->nilai_akhir), 2);

            return [
                'jenis' => $jenis,
                'rows' => $rows,
                'avgNilai' => $avgNilai,
                'avgPredikat' => $this->scoringService->predikat($avgNilai),
            ];
        })->filter()->values();

        $totalGuru = $matrixRows->pluck('jadwal.teacher_id')->unique()->count();
        $totalJadwal = $matrixRows->count();
        $rataRata = $totalJadwal > 0
            ? round((float) $matrixRows->avg('nilai_akhir'), 2)
            : 0.0;

        $setting = AttendanceSetting::query()->with('headmasterTeacher.user')->first();
        $selectedYear = $selectedYearId !== 'all' && $selectedYearId !== ''
            ? $academicYears->firstWhere('id', (int) $selectedYearId)
            : null;
        $selectedJenis = $selectedJenisId ? $masterJenisList->firstWhere('id', $selectedJenisId) : null;

        return [
            'academicYears' => $academicYears,
            'masterJenisList' => $masterJenisList,
            'targetInstruments' => $targetInstruments,
            'matrixRows' => $matrixRows,
            'instrumentSections' => $instrumentSections,
            'predikatStats' => $predikatStats,
            'totalGuru' => $totalGuru,
            'totalJadwal' => $totalJadwal,
            'rataRata' => $rataRata,
            'rataRataPredikat' => $totalJadwal > 0 ? $this->scoringService->predikat($rataRata) : '-',
            'selectedYearId' => $selectedYearId,
            'selectedKategori' => $selectedKategori,
            'selectedJenisId' => $selectedJenisId ?? 'all',
            'selectedStatus' => $selectedStatus,
            'selectedYearLabel' => $selectedYear ? "{$selectedYear->name} (".ucfirst($selectedYear->semester).')' : 'Semua Tahun Ajaran',
            'selectedJenisLabel' => $selectedJenis ? "{$selectedJenis->kode} — {$selectedJenis->nama}" : match ($selectedKategori) {
                SupervisiJenisPenilaian::KATEGORI_REGULER => 'Semua Instrumen Reguler',
                SupervisiJenisPenilaian::KATEGORI_OBSERVASI_PMM => 'Semua Indikator Observasi PMM',
                SupervisiJenisPenilaian::KATEGORI_PERANGKAT_AJAR => 'Semua Telaah Perangkat Ajar',
                default => 'Semua Instrumen Penilaian',
            },
            'statusOptions' => SupervisiStatus::cases(),
            'setting' => $setting,
            'kotaMadrasah' => $setting?->school_district ?: ($setting?->school_city ?: 'Madrasah'),
            'namaKepala' => $setting?->headmasterTeacher?->user?->name
                ?? $setting?->card_principal_name
                ?? 'Kepala Madrasah',
            'nipKepala' => $setting?->headmasterTeacher?->nip
                ?? $setting?->card_principal_nip
                ?? '-',
            'backUrl' => $backUrl,
        ];
    }

    /**
     * Siapkan payload PDF Level 1: Rincian lengkap 1 Form/Instrumen pada 1 jadwal.
     *
     * @return array<string, mixed>
     */
    public function buildSingleInstrumentPrintPayload(
        SupervisiJadwal $jadwal,
        SupervisiJenisPenilaian $jenis,
        bool $isPolos = false
    ): array {
        $payload = $this->buildJadwalPrintPayload($jadwal, $isPolos);
        $jenis->loadMissing('aspekPenilaians');
        $hasil = $jadwal->hasils->firstWhere('jenis_penilaian_id', $jenis->id);
        $detailsByAspek = $hasil ? $hasil->detailHasils->keyBy('aspek_id') : collect();

        $skorMaksimal = (int) $jenis->aspekPenilaians->sum(
            fn (SupervisiAspekPenilaian $aspek): int => $this->scoringService->aspectMaxScore($aspek, $jenis)
        );
        $totalSkor = $hasil ? (int) $hasil->total_skor : 0;
        $nilaiAkhir = $skorMaksimal > 0 ? round(($totalSkor / $skorMaksimal) * 100, 2) : 0.0;

        return [
            ...$payload,
            'jenis' => $jenis,
            'hasil' => $hasil,
            'detailsByAspek' => $detailsByAspek,
            'instrumentSkor' => $totalSkor,
            'instrumentMaks' => $skorMaksimal,
            'instrumentNilai' => $nilaiAkhir,
            'instrumentPredikat' => $hasil && $skorMaksimal > 0 ? $this->scoringService->predikat($nilaiAkhir) : 'Belum Dinilai',
        ];
    }

    /**
     * Siapkan payload PDF Level 3: Portofolio Lengkap 14 Form (81 aspek) untuk 1 Guru dalam 1 Semester / 1 Tahun Pelajaran.
     *
     * @return array<string, mixed>
     */
    public function buildTeacherPortfolioPrintPayload(
        Teacher $teacher,
        ?AcademicYear $referenceYear,
        string $scope = 'semester',
        bool $isPolos = false
    ): array {
        $teacher->loadMissing('user');
        $academicYears = AcademicYear::query()->orderByDesc('start_date')->get();
        $setting = AttendanceSetting::query()->with('headmasterTeacher.user')->first();

        $portfolio = $referenceYear
            ? $this->scoringService->buildTeacherSemesterPortfolio($teacher, $referenceYear, $scope)
            : [
                'teacher' => $teacher,
                'referenceYear' => null,
                'scope' => $scope,
                'periodLabel' => '-',
                'forms' => collect(),
                'doneCount' => 0,
                'partialCount' => 0,
                'totalForms' => 0,
                'totalSkor' => 0,
                'skorMaksimal' => 0,
                'nilaiAkhir' => 0.0,
                'ketercapaian' => 'Belum Dinilai',
            ];

        return [
            ...$portfolio,
            'academicYears' => $academicYears,
            'setting' => $setting,
            'isPolos' => $isPolos,
            'kotaMadrasah' => $setting?->school_district ?: ($setting?->school_city ?: 'Madrasah'),
            'namaKepala' => $setting?->headmasterTeacher?->user?->name
                ?? $setting?->card_principal_name
                ?? 'Kepala Madrasah',
            'nipKepala' => $setting?->headmasterTeacher?->nip
                ?? $setting?->card_principal_nip
                ?? '-',
        ];
    }
}
