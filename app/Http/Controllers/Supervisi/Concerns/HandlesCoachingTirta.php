<?php

namespace App\Http\Controllers\Supervisi\Concerns;

use App\Enums\SupervisiStatus;
use App\Http\Requests\Supervisi\SaveCoachingTemplateRequest;
use App\Http\Requests\Supervisi\SaveCoachingTirtaRequest;
use App\Models\AcademicYear;
use App\Models\Supervisi\SupervisiCoachingTemplate;
use App\Models\Supervisi\SupervisiCoachingTirta;
use App\Models\Supervisi\SupervisiJadwal;
use App\Services\Supervisi\SupervisiCycleGuardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

trait HandlesCoachingTirta
{
    protected function renderCoachingIndex(Request $request, string $portalPrefix): View
    {
        $academicYears = AcademicYear::query()->orderByDesc('start_date')->get();
        $activeYear = AcademicYear::activeSemester();
        $selectedYearId = $request->query('academic_year_id');
        if ($selectedYearId === null) {
            $selectedYearId = $activeYear?->id ? (string) $activeYear->id : 'all';
        }

        $statusFilter = (string) $request->query('status_tirta', 'all');
        $search = trim((string) $request->query('q', ''));
        $teacherId = $request->user()->teacher?->id;
        $userId = $request->user()->id;

        $allJadwals = SupervisiJadwal::with([
            'academicYear',
            'teacher.user',
            'supervisorTeacher.user',
            'supervisorUser',
            'schoolClass',
            'subject',
            'coachingTirtas',
            'signatures',
        ])
            ->withCount(['hasils as scored_components_count' => fn ($q) => $q->whereHas('detailHasils')])
            ->where('status', '!=', SupervisiStatus::Batal->value)
            ->when($portalPrefix === 'supervisor', function ($q) use ($teacherId, $userId): void {
                $q->where(function ($sq) use ($teacherId, $userId): void {
                    if ($teacherId) {
                        $sq->where('supervisor_teacher_id', $teacherId)
                            ->orWhere('supervisor_user_id', $userId);
                    } else {
                        $sq->where('supervisor_user_id', $userId);
                    }
                });
            })
            ->when($selectedYearId !== 'all' && $selectedYearId !== '', fn ($q) => $q->where('academic_year_id', (int) $selectedYearId))
            ->when($search !== '', function ($q) use ($search): void {
                $q->where(function ($sq) use ($search): void {
                    $sq->whereHas('teacher.user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('subject', fn ($subQ) => $subQ->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $summary = [
            'total' => $allJadwals->count(),
            'pra_terisi' => $allJadwals->filter(fn (SupervisiJadwal $j): bool => $j->coachingTirtas->contains('tahap', SupervisiCoachingTirta::TAHAP_PRA))->count(),
            'pasca_terisi' => $allJadwals->filter(fn (SupervisiJadwal $j): bool => $j->coachingTirtas->contains('tahap', SupervisiCoachingTirta::TAHAP_PASCA))->count(),
            'confirmed' => $allJadwals->filter(fn (SupervisiJadwal $j): bool => $j->coachingTirtas->contains('status', SupervisiCoachingTirta::STATUS_CONFIRMED))->count(),
        ];

        $jadwals = $allJadwals->filter(function (SupervisiJadwal $jadwal) use ($statusFilter): bool {
            if ($statusFilter === 'all' || $statusFilter === '') {
                return true;
            }

            if ($statusFilter === 'none') {
                return $jadwal->coachingTirtas->isEmpty();
            }

            return $jadwal->coachingTirtas->contains('status', $statusFilter);
        })->values();

        $tirtaTemplates = SupervisiCoachingTemplate::forAllStages();

        return view('supervisi.shared.coaching_index', compact(
            'jadwals',
            'academicYears',
            'selectedYearId',
            'statusFilter',
            'search',
            'summary',
            'tirtaTemplates',
            'portalPrefix',
        ));
    }

    protected function renderCoachingEdit(Request $request, SupervisiJadwal $jadwal, string $portalPrefix, SupervisiCycleGuardService $guard): View|RedirectResponse
    {
        if ($jadwal->status === SupervisiStatus::Batal->value) {
            return redirect()->route("supervisi.{$portalPrefix}.coaching.index")
                ->with('error', 'Jadwal berstatus batal tidak memiliki sesi Coaching TIRTA.');
        }

        $jadwal->load([
            'academicYear',
            'teacher.user',
            'supervisorTeacher.user',
            'supervisorUser',
            'schoolClass',
            'subject',
            'indikatorPmm',
            'praObservasi',
            'hasils.jenisPenilaian',
            'hasils.detailHasils',
            'tindakLanjut',
            'coachingTirtas',
            'signatures',
        ]);

        $coachingPra = $jadwal->coachingTirtas->firstWhere('tahap', SupervisiCoachingTirta::TAHAP_PRA);
        $coachingPasca = $jadwal->coachingTirtas->firstWhere('tahap', SupervisiCoachingTirta::TAHAP_PASCA);
        $scoredComponents = $jadwal->hasils->filter(fn ($hasil): bool => $hasil->detailHasils->isNotEmpty());
        $hasFormBScores = $scoredComponents->isNotEmpty();
        $isLocked = $guard->isLocked($jadwal);
        $tirtaTemplates = SupervisiCoachingTemplate::forAllStages();

        $requestedTahap = (string) $request->query('tahap', '');
        $activeTahap = in_array($requestedTahap, [SupervisiCoachingTirta::TAHAP_PRA, SupervisiCoachingTirta::TAHAP_PASCA], true)
            ? $requestedTahap
            : ($hasFormBScores ? SupervisiCoachingTirta::TAHAP_PASCA : SupervisiCoachingTirta::TAHAP_PRA);

        return view('supervisi.shared.coaching_edit', compact(
            'jadwal',
            'coachingPra',
            'coachingPasca',
            'scoredComponents',
            'hasFormBScores',
            'isLocked',
            'activeTahap',
            'tirtaTemplates',
            'portalPrefix',
        ));
    }

    protected function handleSaveCoaching(
        SaveCoachingTirtaRequest $request,
        SupervisiJadwal $jadwal,
        string $portalPrefix,
        SupervisiCycleGuardService $guard
    ): RedirectResponse {
        if ($jadwal->status === SupervisiStatus::Batal->value) {
            return redirect()->route("supervisi.{$portalPrefix}.coaching.index")
                ->with('error', 'Jadwal berstatus batal tidak dapat diperbarui.');
        }

        $guard->ensureNotLocked($jadwal);

        $validated = $request->validated();
        $tahap = (string) $validated['tahap'];

        if ($tahap === SupervisiCoachingTirta::TAHAP_PASCA && ! $jadwal->hasils()->whereHas('detailHasils')->exists()) {
            return redirect()
                ->route("supervisi.{$portalPrefix}.coaching.edit", ['jadwal' => $jadwal, 'tahap' => $tahap])
                ->withInput()
                ->with('error', 'Isi minimal satu komponen observasi Form B terlebih dahulu sebelum menyusun Coaching TIRTA Pasca-Observasi.');
        }

        $coaching = $this->upsertCoachingStage($jadwal, $tahap, $validated);
        $tahapLabel = $tahap === SupervisiCoachingTirta::TAHAP_PRA ? 'Pra-Observasi' : 'Pasca-Observasi';
        $message = $coaching->status === SupervisiCoachingTirta::STATUS_DRAFT
            ? "Draf Coaching TIRTA ({$tahapLabel}) berhasil disimpan."
            : "Coaching TIRTA ({$tahapLabel}) berhasil disimpan dan dikirim.";

        return redirect()
            ->route("supervisi.{$portalPrefix}.coaching.edit", ['jadwal' => $jadwal, 'tahap' => $tahap])
            ->with('success', $message);
    }

    protected function handleUpdateTemplate(SaveCoachingTemplateRequest $request, string $portalPrefix): RedirectResponse
    {
        $validated = $request->validated();
        $tahap = (string) $validated['tahap'];

        SupervisiCoachingTemplate::updateOrCreate(
            ['tahap' => $tahap],
            [
                'pertanyaan_tujuan' => $validated['pertanyaan_tujuan'],
                'pertanyaan_identifikasi' => $validated['pertanyaan_identifikasi'],
                'pertanyaan_rencana' => $validated['pertanyaan_rencana'],
                'pertanyaan_tanggung_jawab' => $validated['pertanyaan_tanggung_jawab'],
                'template_tujuan' => $validated['template_tujuan'] ?? null,
                'template_identifikasi' => $validated['template_identifikasi'] ?? null,
                'template_rencana' => $validated['template_rencana'] ?? null,
                'template_tanggung_jawab' => $validated['template_tanggung_jawab'] ?? null,
                'template_catatan_coach' => $validated['template_catatan_coach'] ?? null,
                'updated_by' => $request->user()?->id,
            ]
        );

        $tahapLabel = $tahap === SupervisiCoachingTirta::TAHAP_PRA ? 'Pra-Observasi' : 'Pasca-Observasi';

        return redirect()
            ->route("supervisi.{$portalPrefix}.coaching.index", ['template_tahap' => $tahap])
            ->with('success', "Pertanyaan global & template sistem Coaching TIRTA ({$tahapLabel}) berhasil diperbarui untuk semua guru.");
    }

    protected function handleApplyTemplateBatch(Request $request, string $portalPrefix): RedirectResponse
    {
        $validated = $request->validate([
            'tahap' => ['required', Rule::in([SupervisiCoachingTirta::TAHAP_PRA, SupervisiCoachingTirta::TAHAP_PASCA])],
            'academic_year_id' => ['nullable', 'string'],
            'aksi' => ['nullable', Rule::in([SupervisiCoachingTirta::STATUS_DRAFT, SupervisiCoachingTirta::STATUS_SUBMITTED])],
        ]);

        $tahap = (string) $validated['tahap'];
        $template = SupervisiCoachingTemplate::forAllStages()->get($tahap);

        if (! $template || blank($template->template_tujuan)) {
            return redirect()
                ->route("supervisi.{$portalPrefix}.coaching.index", ['template_tahap' => $tahap])
                ->with('error', 'Isi minimal kolom Template T — Tujuan terlebih dahulu sebelum menerapkan ke jadwal secara massal.');
        }

        $selectedYearId = (string) ($validated['academic_year_id'] ?? 'all');
        $teacherId = $request->user()->teacher?->id;
        $userId = $request->user()->id;

        $jadwals = SupervisiJadwal::with('coachingTirtas')
            ->where('status', '!=', SupervisiStatus::Batal->value)
            ->when($portalPrefix === 'supervisor', function ($q) use ($teacherId, $userId): void {
                $q->where(function ($sq) use ($teacherId, $userId): void {
                    if ($teacherId) {
                        $sq->where('supervisor_teacher_id', $teacherId)
                            ->orWhere('supervisor_user_id', $userId);
                    } else {
                        $sq->where('supervisor_user_id', $userId);
                    }
                });
            })
            ->when($selectedYearId !== 'all' && $selectedYearId !== '', fn ($q) => $q->where('academic_year_id', (int) $selectedYearId))
            ->whereDoesntHave('signatures', fn ($q) => $q->where('dokumen_type', 'supervisi'))
            ->when($tahap === SupervisiCoachingTirta::TAHAP_PASCA, fn ($q) => $q->whereHas('hasils', fn ($hq) => $hq->whereHas('detailHasils')))
            ->get();

        $appliedCount = 0;
        $skippedCount = 0;

        DB::transaction(function () use ($jadwals, $tahap, $template, $validated, &$appliedCount, &$skippedCount): void {
            $payload = [
                'tujuan_coaching' => $template->template_tujuan,
                'identifikasi_kondisi' => $template->template_identifikasi,
                'rencana_aksi' => $template->template_rencana,
                'tanggung_jawab' => $template->template_tanggung_jawab,
                'catatan_coach' => $template->template_catatan_coach,
                'aksi' => $validated['aksi'] ?? SupervisiCoachingTirta::STATUS_DRAFT,
            ];

            foreach ($jadwals as $jadwal) {
                $existing = $jadwal->coachingTirtas->firstWhere('tahap', $tahap);
                if ($existing && $existing->status !== SupervisiCoachingTirta::STATUS_DRAFT) {
                    $skippedCount++;

                    continue;
                }

                $this->upsertCoachingStage($jadwal, $tahap, $payload);
                $appliedCount++;
            }
        });

        $tahapLabel = $tahap === SupervisiCoachingTirta::TAHAP_PRA ? 'Pra-Observasi' : 'Pasca-Observasi';

        return redirect()
            ->route("supervisi.{$portalPrefix}.coaching.index", [
                'academic_year_id' => $selectedYearId,
                'template_tahap' => $tahap,
            ])
            ->with('success', "Template Coaching TIRTA ({$tahapLabel}) diterapkan ke {$appliedCount} jadwal ({$skippedCount} jadwal dilewati karena sudah dikirim/dikonfirmasi).");
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    protected function upsertCoachingStage(SupervisiJadwal $jadwal, string $tahap, array $validated): SupervisiCoachingTirta
    {
        $aksi = ($validated['aksi'] ?? SupervisiCoachingTirta::STATUS_SUBMITTED) === SupervisiCoachingTirta::STATUS_DRAFT
            ? SupervisiCoachingTirta::STATUS_DRAFT
            : SupervisiCoachingTirta::STATUS_SUBMITTED;

        $existing = SupervisiCoachingTirta::where('jadwal_id', $jadwal->id)
            ->where('tahap', $tahap)
            ->first();

        $nextStatus = $aksi;
        if ($existing && $existing->status === SupervisiCoachingTirta::STATUS_CONFIRMED && $aksi === SupervisiCoachingTirta::STATUS_SUBMITTED) {
            $nextStatus = SupervisiCoachingTirta::STATUS_CONFIRMED;
        }

        return SupervisiCoachingTirta::updateOrCreate(
            [
                'jadwal_id' => $jadwal->id,
                'tahap' => $tahap,
            ],
            [
                'tujuan_coaching' => $validated['tujuan_coaching'],
                'identifikasi_kondisi' => $validated['identifikasi_kondisi'] ?? null,
                'rencana_aksi' => $validated['rencana_aksi'] ?? null,
                'tanggung_jawab' => $validated['tanggung_jawab'] ?? null,
                'catatan_coach' => $validated['catatan_coach'] ?? null,
                'status' => $nextStatus,
                'submitted_at' => $nextStatus !== SupervisiCoachingTirta::STATUS_DRAFT
                    ? ($existing?->submitted_at ?? now())
                    : $existing?->submitted_at,
            ]
        );
    }
}
