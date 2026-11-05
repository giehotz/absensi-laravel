<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Ujian\UjianPengaturanTtd;
use App\Models\Ujian\UjianPeriode;
use App\Models\Ujian\UjianPeserta;
use App\Models\Ujian\UjianRuang;
use App\Services\Ujian\NomorPesertaGeneratorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PesertaKartuUjianController extends Controller
{
    public function __construct(
        protected NomorPesertaGeneratorService $pesertaService
    ) {}

    public function index(Request $request): View
    {
        $periodes = UjianPeriode::orderByDesc('id')->get();
        $selectedPeriodeId = $request->query('periode_id', $periodes->firstWhere('is_active', true)?->id ?? $periodes->first()?->id);
        $activePeriode = $periodes->firstWhere('id', $selectedPeriodeId);

        $selectedClassId = $request->query('class_id');
        $selectedRuangId = $request->query('ruang_id');
        $search = $request->query('search');

        // Sinkronisasi cerdas: jika memilih rombel tertentu dan ruang kelas lain yang tidak cocok, sesuaikan secara otomatis
        if ($selectedClassId && $selectedClassId !== 'SEMUA' && $selectedRuangId) {
            $selectedRuang = UjianRuang::find($selectedRuangId);
            if ($selectedRuang && $selectedRuang->isKelas() && $selectedRuang->school_class_id != $selectedClassId) {
                $matchingRuang = UjianRuang::where('school_class_id', $selectedClassId)->where('tipe_ruang', 'kelas')->first();
                $selectedRuangId = $matchingRuang?->id;
            }
        }

        $pesertas = collect();
        if ($activePeriode) {
            $query = UjianPeserta::with(['student', 'schoolClass', 'ruang'])
                ->where('ujian_periode_id', $activePeriode->id);

            if ($selectedClassId && $selectedClassId !== 'SEMUA') {
                $query->where('school_class_id', $selectedClassId);
            }

            if ($selectedRuangId) {
                $query->where('ujian_ruang_id', $selectedRuangId);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_peserta', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%")
                        ->orWhere('nis_lokal', 'like', "%{$search}%")
                        ->orWhere('nomor_peserta', 'like', "%{$search}%");
                });
            }

            $pesertas = $query->orderBy('nama_kelas')->orderBy('nama_peserta')->paginate(50)->withQueryString();
        }

        $classes = SchoolClass::orderBy('level')->orderBy('name')->get();
        $ruangs = UjianRuang::orderBy('kode_ruang')->get();

        return view('ujian.kartu.index', compact(
            'periodes',
            'activePeriode',
            'pesertas',
            'classes',
            'ruangs',
            'selectedClassId',
            'selectedRuangId',
            'search'
        ));
    }

    public function sync(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ujian_periode_id' => ['required', 'exists:ujian_periode,id'],
            'school_class_ids' => ['nullable', 'array'],
            'school_class_ids.*' => ['exists:school_classes,id'],
        ]);

        $periode = UjianPeriode::findOrFail($validated['ujian_periode_id']);
        $result = $this->pesertaService->syncStudentsFromMaster($periode, $validated['school_class_ids'] ?? null);

        return back()->with('success', "Sinkronisasi selesai: {$result['total_synced']} siswa aktif diproses ({$result['created']} baru, {$result['updated']} diperbarui).");
    }

    public function generateNomor(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ujian_periode_id' => ['required', 'exists:ujian_periode,id'],
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
            'tahun_prefix' => ['nullable', 'string', 'max:20'],
            'semester' => ['nullable', 'string', 'max:10'],
            'format_kelas' => ['nullable', 'in:tingkat,nama_rombel_lower,nama_rombel_upper,kode_rombel'],
            'separator_1' => ['nullable', 'string', 'max:5'],
            'separator_2' => ['nullable', 'string', 'max:5'],
            'separator_3' => ['nullable', 'string', 'max:5'],
            'digit_urut' => ['nullable', 'integer', 'min:2', 'max:5'],
        ]);

        $periode = UjianPeriode::findOrFail($validated['ujian_periode_id']);
        $count = $this->pesertaService->generateStructuredNomorPeserta($periode, $validated['school_class_id'] ?? null, $validated);

        return back()->with('success', "Nomor Peserta Ujian berhasil di-generate untuk {$count} peserta.");
    }

    public function allocateRooms(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ujian_periode_id' => ['required', 'exists:ujian_periode,id'],
        ]);

        $periode = UjianPeriode::findOrFail($validated['ujian_periode_id']);
        $count = $this->pesertaService->autoAllocateRooms($periode);

        return back()->with('success', "Berhasil mendistribusikan {$count} peserta ke ruang ujian.");
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ujian_periode_id' => ['required', 'exists:ujian_periode,id'],
            'nama_peserta' => ['required', 'string', 'max:150'],
            'nama_kelas' => ['required', 'string', 'max:50'],
            'nis_lokal' => ['nullable', 'string', 'max:50'],
            'nisn' => ['nullable', 'string', 'max:50'],
            'nomor_peserta' => ['required', 'string', 'max:50'],
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'ujian_ruang_id' => ['nullable', 'exists:ujian_ruang,id'],
            'nomor_kursi' => ['nullable', 'integer', 'min:1'],
        ]);

        UjianPeserta::create($validated);

        return back()->with('success', 'Peserta Ujian berhasil ditambahkan.');
    }

    public function update(Request $request, UjianPeserta $peserta): RedirectResponse
    {
        $validated = $request->validate([
            'nama_peserta' => ['required', 'string', 'max:150'],
            'nama_kelas' => ['required', 'string', 'max:50'],
            'nis_lokal' => ['nullable', 'string', 'max:50'],
            'nisn' => ['nullable', 'string', 'max:50'],
            'nomor_peserta' => ['required', 'string', 'max:50'],
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'ujian_ruang_id' => ['nullable', 'exists:ujian_ruang,id'],
            'nomor_kursi' => ['nullable', 'integer', 'min:1'],
        ]);

        $peserta->update($validated);

        return back()->with('success', 'Data peserta ujian berhasil diperbarui.');
    }

    public function destroy(UjianPeserta $peserta): RedirectResponse
    {
        $peserta->delete();

        return back()->with('success', 'Peserta Ujian berhasil dihapus.');
    }

    public function printCards(Request $request): View
    {
        $periodeId = $request->query('periode_id');
        if (! $periodeId) {
            $periode = UjianPeriode::where('is_active', true)->first()
                ?? UjianPeriode::latest('id')->firstOrFail();
        } else {
            $periode = UjianPeriode::with(['jenis', 'pengaturanTtd'])->findOrFail($periodeId);
        }

        $classId = $request->query('class_id');
        $ruangId = $request->query('ruang_id');
        $selectedIds = $request->query('selected_ids'); // koma atau array

        // Sinkronisasi filter rombel & ruang pada proses cetak
        if ($classId && $classId !== 'SEMUA' && $ruangId) {
            $selectedRuang = UjianRuang::find($ruangId);
            if ($selectedRuang && $selectedRuang->isKelas() && $selectedRuang->school_class_id != $classId) {
                $matchingRuang = UjianRuang::where('school_class_id', $classId)->where('tipe_ruang', 'kelas')->first();
                $ruangId = $matchingRuang?->id;
            }
        }

        $query = UjianPeserta::with(['student', 'schoolClass', 'ruang'])
            ->where('ujian_periode_id', $periode->id)
            ->where('is_eligible', true);

        if (! empty($selectedIds)) {
            $ids = is_array($selectedIds) ? $selectedIds : explode(',', $selectedIds);
            $query->whereIn('id', $ids);
        } else {
            if ($classId && $classId !== 'SEMUA') {
                $query->where('school_class_id', $classId);
            }
            if ($ruangId) {
                $query->where('ujian_ruang_id', $ruangId);
            }
        }

        $pesertaList = $query->orderBy('nama_kelas')->orderBy('nama_peserta')->get();

        $isPreview = $request->boolean('preview');
        if ($isPreview) {
            $pesertaList = $pesertaList->take(1);
            if ($pesertaList->isEmpty()) {
                $sampleStudent = new UjianPeserta([
                    'nama_peserta' => 'ARCIO RAZIQ DANISH SYAHPUTRA',
                    'tempat_lahir' => 'PRINGSEWU',
                    'tanggal_lahir' => '2019-11-17',
                    'nisn' => '3192770807',
                    'nomor_peserta' => '26-0101-001',
                    'nama_kelas' => 'Kelas 1 a',
                    'nomor_kursi' => 1,
                ]);
                $pesertaList = collect([$sampleStudent]);
            }
        }

        $pengaturan = $periode->pengaturanTtd ?? UjianPengaturanTtd::where('ujian_periode_id', $periode->id)->first();

        return view('ujian.kartu.print-a4', compact('periode', 'pesertaList', 'pengaturan', 'isPreview'));
    }
}
