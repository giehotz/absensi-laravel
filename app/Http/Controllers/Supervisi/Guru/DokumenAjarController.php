<?php

namespace App\Http\Controllers\Supervisi\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supervisi\StoreDokumenAjarRequest;
use App\Models\Supervisi\SupervisiDokumenAjar;
use App\Models\Supervisi\SupervisiJadwal;
use App\Services\Supervisi\SupervisiCycleGuardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DokumenAjarController extends Controller
{
    public function __construct(
        private readonly SupervisiCycleGuardService $guard,
    ) {}

    public function index(Request $request): View
    {
        $teacherId = $request->user()->teacher->id;

        $jadwals = SupervisiJadwal::with(['academicYear', 'schoolClass', 'subject'])
            ->withCount('dokumenAjars')
            ->where('teacher_id', $teacherId)
            ->orderByDesc('tanggal')
            ->get();

        return view('supervisi.guru.dokumen_ajar_list', compact('jadwals'));
    }

    public function manage(Request $request, SupervisiJadwal $jadwal): View
    {
        $this->guard->ensureGuruOwnsJadwal($request->user(), $jadwal);
        $jadwal->load(['academicYear', 'schoolClass', 'subject', 'supervisorTeacher.user', 'supervisorUser', 'indikatorPmm', 'dokumenAjars']);

        return view('supervisi.guru.dokumen_ajar_manage', [
            'jadwal' => $jadwal,
            'dokumens' => $jadwal->dokumenAjars->sortByDesc('id'),
        ]);
    }

    public function store(StoreDokumenAjarRequest $request, SupervisiJadwal $jadwal): RedirectResponse
    {
        $this->guard->ensureGuruOwnsJadwal($request->user(), $jadwal);

        $data = $request->validated();
        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store("supervisi/dokumen-ajar/{$jadwal->id}", 'public');
        }

        $diferensiasi = $data['strategi_diferensiasi'] ?? [];

        SupervisiDokumenAjar::create([
            'jadwal_id' => $jadwal->id,
            'teacher_id' => $request->user()->teacher->id,
            'nama_dokumen' => $data['nama_dokumen'],
            'kategori_dokumen' => $data['kategori_dokumen'],
            'fase' => $data['fase'] ?? null,
            'tujuan_pembelajaran' => $data['tujuan_pembelajaran'] ?? null,
            'strategi_diferensiasi' => is_array($diferensiasi) ? implode(',', $diferensiasi) : $diferensiasi,
            'link_drive' => $data['link_drive'] ?? null,
            'link_eksternal' => $data['link_eksternal'] ?? null,
            'file_path' => $filePath,
            'keterangan' => $data['keterangan'] ?? null,
            'status' => SupervisiDokumenAjar::STATUS_PENDING,
        ]);

        return redirect()->route('supervisi.guru.dokumen-ajar.manage', $jadwal)
            ->with('success', 'Perangkat ajar berhasil disimpan dan menunggu telaah Supervisor.');
    }

    public function destroy(Request $request, SupervisiDokumenAjar $dokumen): RedirectResponse
    {
        $this->guard->ensureGuruOwnsJadwal($request->user(), $dokumen->jadwal);

        if ($dokumen->file_path && Storage::disk('public')->exists($dokumen->file_path)) {
            Storage::disk('public')->delete($dokumen->file_path);
        }

        $jadwalId = $dokumen->jadwal_id;
        $dokumen->delete();

        return redirect()->route('supervisi.guru.dokumen-ajar.manage', $jadwalId)
            ->with('success', 'Perangkat ajar berhasil dihapus.');
    }
}
