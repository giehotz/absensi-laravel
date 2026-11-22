<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ujian\PreviewPemetaanHariRequest;
use App\Http\Requests\Ujian\StorePeriodeKelompokRequest;
use App\Models\AcademicYear;
use App\Models\AttendanceSetting;
use App\Models\SchoolClass;
use App\Models\Ujian\UjianJenis;
use App\Models\Ujian\UjianPengaturanTtd;
use App\Models\Ujian\UjianPeriode;
use App\Models\Ujian\UjianPeriodeKelompok;
use App\Models\Ujian\UjianSesi;
use App\Services\Ujian\ClassLabelResolver;
use App\Services\Ujian\UjianKalenderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JenisUjianController extends Controller
{
    public function index(Request $request, UjianKalenderService $kalenderService): View
    {
        $activeTab = $request->query('tab', 'periode');
        if (! in_array($activeTab, ['periode', 'jenis', 'pemetaan-hari'])) {
            $activeTab = 'periode';
        }

        $jenisList = UjianJenis::withCount('periodes')->get();
        $periodes = UjianPeriode::with(['jenis', 'academicYear', 'kelompokConfigs'])->orderByDesc('id')->get();
        $academicYears = AcademicYear::orderByDesc('name')->get();

        $selectedPeriodeId = $request->query('periode_id')
            ?: ($periodes->firstWhere('is_active', true)?->id ?: $periodes->first()?->id);

        $selectedPeriode = $selectedPeriodeId
            ? $periodes->firstWhere('id', (int) $selectedPeriodeId)
            : null;

        $configs = $selectedPeriode?->kelompokConfigs->keyBy('kelompok') ?? collect();

        // Kelas bawah (1-2) dan kelas atas (3-6) berdasarkan ekstraksi nama kelas
        $allClasses = SchoolClass::ordered()->get();
        $kelasBawah = $allClasses->filter(fn ($c) => in_array(ClassLabelResolver::levelFromName($c->name), ['1', '2']))->values();
        $kelasAtas = $allClasses->filter(fn ($c) => in_array(ClassLabelResolver::levelFromName($c->name), ['3', '4', '5', '6']))->values();

        $defaultJamSesi = UjianSesi::where('is_active', true)->orderBy('sesi_ke')->get();

        $configBawah = $configs->get('bawah');
        $configAtas = $configs->get('atas');

        $previewBawah = $configBawah ? $kalenderService->getPreview($configBawah) : null;
        $previewAtas = $configAtas ? $kalenderService->getPreview($configAtas) : null;

        return view('ujian.jenis.index', compact(
            'activeTab',
            'selectedPeriodeId',
            'selectedPeriode',
            'configs',
            'kelasBawah',
            'kelasAtas',
            'previewBawah',
            'previewAtas',
            'defaultJamSesi',
            'jenisList',
            'periodes',
            'academicYears'
        ));
    }

    public function storePemetaanHari(StorePeriodeKelompokRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $periode = UjianPeriode::findOrFail($validated['ujian_periode_id']);

        $defaults = $validated['kelompok'] === 'bawah'
            ? ['tingkat_list' => ['1', '2'], 'mode_jadwal' => 'per_rombel']
            : ['tingkat_list' => ['3', '4', '5', '6'], 'mode_jadwal' => 'per_tingkat'];

        UjianPeriodeKelompok::updateOrCreate(
            ['ujian_periode_id' => $periode->id, 'kelompok' => $validated['kelompok']],
            array_merge($validated, $defaults)
        );

        $label = $validated['kelompok'] === 'bawah' ? 'Kelas Bawah (1–2)' : 'Kelas Atas (3–6)';

        return redirect()->route('ujian.jenis.index', [
            'tab' => 'pemetaan-hari',
            'periode_id' => $periode->id,
        ])->with('success', "Pemetaan hari & sesi ujian untuk {$label} berhasil disimpan.");
    }

    public function previewPemetaanHari(PreviewPemetaanHariRequest $request, UjianKalenderService $service): JsonResponse
    {
        $validated = $request->validated();

        $defaults = $validated['kelompok'] === 'bawah'
            ? ['tingkat_list' => ['1', '2'], 'mode_jadwal' => 'per_rombel']
            : ['tingkat_list' => ['3', '4', '5', '6'], 'mode_jadwal' => 'per_tingkat'];

        $tempConfig = new UjianPeriodeKelompok(array_merge($validated, $defaults));
        $preview = $service->getPreview($tempConfig);

        return response()->json($preview);
    }

    public function storeJenis(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_jenis' => ['required', 'string', 'max:30', 'unique:ujian_jenis,kode_jenis'],
            'nama_jenis' => ['required', 'string', 'max:150'],
            'kategori_kurikulum' => ['required', 'string', 'max:100'],
            'fase_sasaran' => ['required', 'string', 'max:50'],
            'judul_kop_kartu' => ['nullable', 'string', 'max:150'],
        ]);

        $validated['status_aktif'] = true;
        UjianJenis::create($validated);

        return back()->with('success', 'Jenis Asesmen Kurikulum Merdeka berhasil ditambahkan.');
    }

    public function updateJenis(Request $request, UjianJenis $jeni): RedirectResponse
    {
        $validated = $request->validate([
            'kode_jenis' => ['required', 'string', 'max:30', 'unique:ujian_jenis,kode_jenis,'.$jeni->id],
            'nama_jenis' => ['required', 'string', 'max:150'],
            'kategori_kurikulum' => ['required', 'string', 'max:100'],
            'fase_sasaran' => ['required', 'string', 'max:50'],
            'judul_kop_kartu' => ['nullable', 'string', 'max:150'],
            'status_aktif' => ['nullable', 'boolean'],
        ]);

        $validated['status_aktif'] = $request->has('status_aktif');
        $jeni->update($validated);

        return back()->with('success', 'Jenis Asesmen berhasil diperbarui.');
    }

    public function storePeriode(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ujian_jenis_id' => ['required', 'exists:ujian_jenis,id'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'nama_periode' => ['required', 'string', 'max:150'],
            'tahun_pelajaran' => ['required', 'string', 'max:30'],
            'semester' => ['required', 'in:Ganjil,Genap'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date'],
            'tempat_cetak' => ['nullable', 'string', 'max:100'],
            'tanggal_cetak_kartu' => ['nullable', 'date'],
        ]);

        $validated['tempat_cetak'] = $validated['tempat_cetak'] ?: 'Tanggamus';
        $validated['is_active'] = true;

        $periode = UjianPeriode::create($validated);

        // Otomatis salin pengaturan TTD dari AttendanceSetting jika belum ada
        $setting = AttendanceSetting::first();
        $schoolName = $setting?->school_name ?: 'MIN 2 TANGGAMUS';
        $principalName = $setting?->card_principal_name ?: ($setting?->headmasterTeacher?->user?->name ?: 'Sipulloh, M.Pd');
        $principalNip = $setting?->card_principal_nip ?: ($setting?->headmasterTeacher?->nip ?: '197005272007011022');
        $jenis = UjianJenis::find($validated['ujian_jenis_id']);

        UjianPengaturanTtd::create([
            'ujian_periode_id' => $periode->id,
            'nama_instansi' => $schoolName,
            'kop_baris_1' => 'KARTU PESERTA',
            'kop_baris_2' => strtoupper($jenis?->judul_kop_kartu ?: $jenis?->nama_jenis ?: 'ASESMEN SUMATIF'),
            'kop_baris_3' => $schoolName,
            'kop_baris_4' => 'TAHUN PELAJARAN '.$validated['tahun_pelajaran'],
            'jabatan_penandatangan' => 'Kepala Madrasah',
            'nama_kepala' => $principalName,
            'nip_kepala' => $principalNip,
            'tempat_penetapan' => $validated['tempat_cetak'],
            'tanggal_penetapan' => $validated['tanggal_cetak_kartu'] ?: now()->toDateString(),
            'path_logo_kiri' => $setting?->logo,
            'path_watermark' => $setting?->logo,
            'path_gambar_ttd' => $setting?->card_signature_image,
            'opacity_watermark' => 0.35,
            'is_active' => true,
        ]);

        return back()->with('success', 'Periode Pelaksanaan Ujian berhasil dibuat.');
    }

    public function updatePeriode(Request $request, UjianPeriode $periode): RedirectResponse
    {
        $validated = $request->validate([
            'ujian_jenis_id' => ['required', 'exists:ujian_jenis,id'],
            'nama_periode' => ['required', 'string', 'max:255'],
            'tahun_pelajaran' => ['required', 'string', 'max:20'],
            'semester' => ['required', 'in:Ganjil,Genap'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'tempat_cetak' => ['nullable', 'string', 'max:100'],
            'tanggal_cetak_kartu' => ['nullable', 'date'],
        ]);

        $validated['tempat_cetak'] = $validated['tempat_cetak'] ?: 'Tanggamus';

        $periode->update($validated);

        if ($periode->pengaturanTtd) {
            $ttdUpdates = [];
            if (! empty($validated['tempat_cetak'])) {
                $ttdUpdates['tempat_penetapan'] = $validated['tempat_cetak'];
            }
            if (! empty($validated['tanggal_cetak_kartu'])) {
                $ttdUpdates['tanggal_penetapan'] = $validated['tanggal_cetak_kartu'];
            }
            if (! empty($ttdUpdates)) {
                $periode->pengaturanTtd->update($ttdUpdates);
            }
        }

        return back()->with('success', "Periode '{$periode->nama_periode}' berhasil diperbarui.");
    }

    public function activatePeriode(UjianPeriode $periode): RedirectResponse
    {
        UjianPeriode::where('id', '!=', $periode->id)->update(['is_active' => false]);
        $periode->update(['is_active' => true]);

        return back()->with('success', "Periode '{$periode->nama_periode}' telah diaktifkan.");
    }
}
