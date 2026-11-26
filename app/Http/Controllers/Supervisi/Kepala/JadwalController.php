<?php

namespace App\Http\Controllers\Supervisi\Kepala;

use App\Enums\SupervisiStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Supervisi\StoreJadwalRequest;
use App\Models\AcademicYear;
use App\Models\AttendanceSetting;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Supervisi\SupervisiJadwal;
use App\Models\Supervisi\SupervisiJenisPenilaian;
use App\Models\Supervisi\SupervisiSupervisor;
use App\Models\Teacher;
use App\Services\Supervisi\SupervisiExcelExportService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        private readonly SupervisiExcelExportService $excelService,
    ) {}

    public function index(): View
    {
        $jadwals = SupervisiJadwal::with(['academicYear', 'teacher.user', 'supervisorTeacher.user', 'supervisorUser', 'schoolClass', 'subject', 'indikatorPmm', 'signatures'])
            ->orderByDesc('tanggal')
            ->get();

        return view('supervisi.kepala.jadwal.index', compact('jadwals'));
    }

    public function create(): View
    {
        return view('supervisi.kepala.jadwal.form', $this->formOptions());
    }

    public function store(StoreJadwalRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $dayIso = Carbon::parse($data['tanggal'])->dayOfWeekIso;

        if (empty($data['supervisor_teacher_id'])) {
            $data['supervisor_teacher_id'] = $request->user()->teacher?->id;
            $data['supervisor_user_id'] = $request->user()->id;
        }

        SupervisiJadwal::create([
            ...$data,
            'hari' => self::HARI_MAP[$dayIso] ?? 'Senin',
            'status' => SupervisiStatus::Terjadwal->value,
        ]);

        return redirect()->route('supervisi.kepala.jadwal.index')
            ->with('success', 'Jadwal supervisi oleh Kepala Madrasah berhasil ditambahkan.');
    }

    public function show(SupervisiJadwal $jadwal): View
    {
        $jadwal->load(['academicYear', 'teacher.user', 'supervisorTeacher.user', 'supervisorUser', 'schoolClass', 'subject', 'indikatorPmm', 'praObservasi', 'dokumenAjars', 'hasils.jenisPenilaian', 'fotoBuktis', 'tindakLanjut', 'signatures']);

        return view('supervisi.kepala.jadwal.show', compact('jadwal'));
    }

    public function edit(SupervisiJadwal $jadwal): View
    {
        return view('supervisi.kepala.jadwal.form', [
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

        return redirect()->route('supervisi.kepala.jadwal.index')
            ->with('success', 'Jadwal supervisi berhasil diperbarui.');
    }

    public function destroy(SupervisiJadwal $jadwal): RedirectResponse
    {
        if ($jadwal->hasils()->exists()) {
            return redirect()->route('supervisi.kepala.jadwal.index')
                ->with('error', 'Jadwal yang sudah memiliki hasil supervisi tidak dapat dihapus.');
        }

        $jadwal->delete();

        return redirect()->route('supervisi.kepala.jadwal.index')
            ->with('success', 'Jadwal supervisi berhasil dihapus.');
    }

    public function exportExcel(): StreamedResponse
    {
        $jadwals = SupervisiJadwal::with(['academicYear', 'teacher.user', 'supervisorTeacher.user', 'supervisorUser', 'schoolClass', 'subject'])
            ->orderByDesc('tanggal')
            ->get();

        return $this->excelService->exportRekap($jadwals, 'Seluruh Jadwal (Kepala Madrasah)', 'jadwal-supervisi-kepala-'.now()->format('Y-m-d').'.xlsx');
    }

    public function print(Request $request): View
    {
        $jadwals = SupervisiJadwal::with(['academicYear', 'teacher.user', 'supervisorTeacher.user', 'supervisorUser', 'schoolClass', 'subject'])
            ->orderByDesc('tanggal')
            ->get();

        $setting = AttendanceSetting::query()->with('headmasterTeacher.user')->first();

        return view('supervisi.pdf.jadwal', [
            'jadwals' => $jadwals,
            'setting' => $setting,
            'backUrl' => route('supervisi.kepala.jadwal.index'),
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
