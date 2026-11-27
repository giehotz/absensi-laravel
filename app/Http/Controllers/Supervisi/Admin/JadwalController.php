<?php

namespace App\Http\Controllers\Supervisi\Admin;

use App\Enums\SupervisiStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Supervisi\DeleteSupervisiJadwalRequest;
use App\Http\Requests\Supervisi\GenerateJadwalRequest;
use App\Http\Requests\Supervisi\StoreJadwalRequest;
use App\Models\AcademicYear;
use App\Models\AttendanceSetting;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Supervisi\SupervisiJadwal;
use App\Models\Supervisi\SupervisiJenisPenilaian;
use App\Models\Supervisi\SupervisiSupervisor;
use App\Models\Teacher;
use App\Services\Supervisi\SupervisiJadwalGeneratorService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class JadwalController extends Controller
{
    private const HARI_MAP = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    public function __construct(
        private readonly SupervisiJadwalGeneratorService $generatorService,
    ) {}

    public function index(Request $request): View
    {
        $academicYears = AcademicYear::query()->orderByDesc('start_date')->get();
        $activeYear = AcademicYear::activeSemester();
        $selectedYearId = $request->query('academic_year_id');
        if ($selectedYearId === null) {
            $selectedYearId = $activeYear?->id ? (string) $activeYear->id : 'all';
        }

        $jadwals = SupervisiJadwal::with([
            'academicYear',
            'teacher.user',
            'supervisorTeacher.user',
            'supervisorUser',
            'schoolClass',
            'subject',
            'indikatorPmm',
            'signatures',
        ])
            ->withCount('hasils')
            ->when($selectedYearId !== 'all' && $selectedYearId !== '', fn ($q) => $q->where('academic_year_id', (int) $selectedYearId))
            ->when($request->filled('status') && $request->query('status') !== 'all', fn ($q) => $q->where('status', $request->query('status')))
            ->orderBy('tanggal')
            ->get();

        $deleteConfirmToken = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $request->session()->put('supervisi_admin_delete_token', $deleteConfirmToken);

        return view('supervisi.admin.jadwal.index', compact(
            'jadwals',
            'academicYears',
            'selectedYearId',
            'activeYear',
            'deleteConfirmToken'
        ));
    }

    public function create(): View
    {
        return view('supervisi.admin.jadwal.form', $this->formOptions());
    }

    public function store(StoreJadwalRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $dayIso = Carbon::parse($data['tanggal'])->dayOfWeekIso;

        SupervisiJadwal::create([
            ...$data,
            'hari' => self::HARI_MAP[$dayIso] ?? 'Senin',
            'status' => SupervisiStatus::Terjadwal->value,
        ]);

        return redirect()->route('supervisi.admin.jadwal.index')
            ->with('success', 'Jadwal supervisi berhasil ditambahkan.');
    }

    public function edit(SupervisiJadwal $jadwal): View
    {
        return view('supervisi.admin.jadwal.form', [
            ...$this->formOptions(),
            'jadwal' => $jadwal,
        ]);
    }

    public function update(StoreJadwalRequest $request, SupervisiJadwal $jadwal): RedirectResponse
    {
        $data = $request->validated();
        $dayIso = Carbon::parse($data['tanggal'])->dayOfWeekIso;

        $jadwal->update([
            ...$data,
            'hari' => self::HARI_MAP[$dayIso] ?? $jadwal->hari,
        ]);

        return redirect()->route('supervisi.admin.jadwal.index')
            ->with('success', 'Jadwal supervisi berhasil diperbarui.');
    }

    public function destroy(DeleteSupervisiJadwalRequest $request, SupervisiJadwal $jadwal): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $result = $this->deleteJadwalsWithAssets([$jadwal->id]);
        $request->session()->forget('supervisi_admin_delete_token');

        $message = $result['with_results'] > 0
            ? 'Jadwal supervisi beserta data hasil penilaian terkait berhasil dihapus permanen.'
            : 'Jadwal supervisi berhasil dihapus.';

        return redirect()->route('supervisi.admin.jadwal.index')
            ->with('success', $message);
    }

    public function bulkDestroy(DeleteSupervisiJadwalRequest $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $ids = array_values(array_unique(array_map('intval', (array) $request->validated('selected_ids', []))));
        if (empty($ids)) {
            return redirect()->route('supervisi.admin.jadwal.index')
                ->with('error', 'Pilih minimal satu jadwal yang ingin dihapus.');
        }

        $result = $this->deleteJadwalsWithAssets($ids);
        $request->session()->forget('supervisi_admin_delete_token');

        $msg = $result['with_results'] > 0
            ? "Berhasil menghapus permanen {$result['deleted']} jadwal supervisi ({$result['with_results']} di antaranya beserta hasil penilaian terkait)."
            : "Berhasil menghapus {$result['deleted']} jadwal supervisi.";

        return redirect()->route('supervisi.admin.jadwal.index')->with('success', $msg);
    }

    /**
     * Hapus jadwal beserta seluruh relasi dan file unggahan fisiknya.
     *
     * @param  array<int, int>  $ids
     * @return array{deleted: int, with_results: int}
     */
    private function deleteJadwalsWithAssets(array $ids): array
    {
        $jadwals = SupervisiJadwal::with(['dokumenAjars', 'fotoBuktis'])
            ->withCount('hasils')
            ->whereIn('id', $ids)
            ->get();

        $filePaths = [];
        foreach ($jadwals as $jadwal) {
            foreach ($jadwal->dokumenAjars as $dok) {
                if ($dok->file_path) {
                    $filePaths[] = $dok->file_path;
                }
            }
            foreach ($jadwal->fotoBuktis as $foto) {
                if ($foto->file_path) {
                    $filePaths[] = $foto->file_path;
                }
            }
        }

        DB::transaction(function () use ($ids): void {
            SupervisiJadwal::whereIn('id', $ids)->delete();
        });

        foreach (array_unique($filePaths) as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        return [
            'deleted' => $jadwals->count(),
            'with_results' => $jadwals->filter(fn (SupervisiJadwal $j): bool => $j->hasils_count > 0)->count(),
        ];
    }

    public function generateForm(): View
    {
        $activeYear = AcademicYear::activeSemester() ?? AcademicYear::query()->latest('id')->first();
        $academicYears = AcademicYear::query()->orderByDesc('start_date')->get();

        $supervisors = SupervisiSupervisor::with('teacher.user')
            ->active()
            ->get();

        $scheduledTeacherIds = $activeYear
            ? SupervisiJadwal::where('academic_year_id', $activeYear->id)->pluck('teacher_id')->unique()->all()
            : [];

        $teachers = Teacher::with('user')
            ->excludeKepala()
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->get()
            ->sortBy(fn (Teacher $t) => $t->user?->name ?? '')
            ->values();

        $pmmIndikators = SupervisiJenisPenilaian::query()
            ->byKategori(SupervisiJenisPenilaian::KATEGORI_OBSERVASI_PMM)
            ->active()
            ->orderBy('id')
            ->get();

        return view('supervisi.admin.jadwal.generate', compact(
            'activeYear',
            'academicYears',
            'supervisors',
            'teachers',
            'scheduledTeacherIds',
            'pmmIndikators'
        ));
    }

    public function generatePreview(GenerateJadwalRequest $request): View
    {
        $result = $this->generatorService->preview($request->validated());
        $academicYear = AcademicYear::findOrFail($request->integer('academic_year_id'));

        $classes = SchoolClass::query()
            ->where('academic_year_id', $academicYear->id)
            ->ordered()
            ->get();

        $supervisors = SupervisiSupervisor::with('teacher.user')->active()->get();

        return view('supervisi.admin.jadwal.generate_preview', [
            'academicYear' => $academicYear,
            'generatedJadwals' => $result['generated'],
            'unscheduledTeachers' => $result['unscheduled_teachers'],
            'workingDaysCount' => $result['working_days_count'],
            'classes' => $classes,
            'supervisors' => $supervisors,
        ]);
    }

    public function generateStore(Request $request): RedirectResponse
    {
        $items = json_decode((string) $request->input('jadwals_json'), true);
        if (! is_array($items) || empty($items)) {
            return redirect()->route('supervisi.admin.jadwal.generate')
                ->with('error', 'Data pratinjau jadwal tidak valid atau kosong.');
        }

        try {
            $count = $this->generatorService->storeBatch(
                $items,
                (array) $request->input('overrides', []),
                (array) $request->input('exclude_indices', [])
            );
        } catch (\DomainException $e) {
            return redirect()->route('supervisi.admin.jadwal.generate')
                ->with('error', $e->getMessage());
        }

        return redirect()->route('supervisi.admin.jadwal.index')
            ->with('success', "Berhasil menyimpan {$count} jadwal supervisi otomatis.");
    }

    public function print(Request $request): View
    {
        $activeYear = AcademicYear::activeSemester();
        $selectedYearId = $request->query('academic_year_id', $activeYear?->id ? (string) $activeYear->id : 'all');

        $jadwals = SupervisiJadwal::with(['academicYear', 'teacher.user', 'supervisorTeacher.user', 'supervisorUser', 'schoolClass', 'subject'])
            ->when($selectedYearId !== 'all' && $selectedYearId !== '', fn ($q) => $q->where('academic_year_id', (int) $selectedYearId))
            ->when($request->filled('status') && $request->query('status') !== 'all', fn ($q) => $q->where('status', $request->query('status')))
            ->orderBy('tanggal')
            ->get();

        $setting = AttendanceSetting::query()->with('headmasterTeacher.user')->first();

        return view('supervisi.pdf.jadwal', [
            'jadwals' => $jadwals,
            'setting' => $setting,
            'backUrl' => route('supervisi.admin.jadwal.index'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        $activeYear = AcademicYear::activeSemester();
        $academicYears = AcademicYear::query()->orderByDesc('start_date')->get();
        $teachers = Teacher::with('user')->excludeKepala()->get()->sortBy(fn (Teacher $t) => $t->user?->name ?? '')->values();
        $supervisors = SupervisiSupervisor::with('teacher.user')->active()->get();
        $classes = SchoolClass::query()->when($activeYear, fn ($q) => $q->where('academic_year_id', $activeYear->id))->ordered()->get();
        if ($classes->isEmpty()) {
            $classes = SchoolClass::query()->ordered()->get();
        }
        $subjects = Subject::query()->orderBy('name')->get();
        $pmmIndikators = SupervisiJenisPenilaian::byKategori(SupervisiJenisPenilaian::KATEGORI_OBSERVASI_PMM)->active()->orderBy('id')->get();

        return compact('activeYear', 'academicYears', 'teachers', 'supervisors', 'classes', 'subjects', 'pmmIndikators');
    }
}
