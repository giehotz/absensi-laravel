<?php

namespace App\Http\Controllers\Supervisi\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supervisi\SupervisiAspekPenilaian;
use App\Models\Supervisi\SupervisiJenisPenilaian;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InstrumenController extends Controller
{
    public function index(Request $request): View
    {
        $tab = in_array($request->query('tab'), ['jenis', 'aspek'], true) ? $request->query('tab') : 'jenis';

        $jenisList = SupervisiJenisPenilaian::withCount('aspekPenilaians')
            ->orderBy('kategori_supervisi')
            ->orderBy('id')
            ->get();

        $aspekList = SupervisiAspekPenilaian::with('jenisPenilaian')
            ->orderBy('jenis_penilaian_id')
            ->orderBy('urutan')
            ->get();

        return view('supervisi.admin.instrumen.index', compact('tab', 'jenisList', 'aspekList'));
    }

    public function storeJenis(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kode' => ['required', 'string', 'max:50', 'unique:supervisi_jenis_penilaian,kode'],
            'kategori_supervisi' => ['required', 'in:reguler,observasi_kelas_pmm,perangkat_ajar'],
            'dimensi_pmm' => ['nullable', 'string', 'max:50'],
            'skor_maksimal' => ['required', 'integer', 'in:3,4'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        SupervisiJenisPenilaian::create($validated);

        return redirect()->route('supervisi.admin.instrumen.index', ['tab' => 'jenis'])
            ->with('success', 'Jenis instrumen penilaian berhasil ditambahkan.');
    }

    public function updateJenis(Request $request, SupervisiJenisPenilaian $jenis): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kode' => ['required', 'string', 'max:50', Rule::unique('supervisi_jenis_penilaian', 'kode')->ignore($jenis->id)],
            'kategori_supervisi' => ['required', 'in:reguler,observasi_kelas_pmm,perangkat_ajar'],
            'dimensi_pmm' => ['nullable', 'string', 'max:50'],
            'skor_maksimal' => ['required', 'integer', 'in:3,4'],
            'deskripsi' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $jenis->update($validated);

        return redirect()->route('supervisi.admin.instrumen.index', ['tab' => 'jenis'])
            ->with('success', 'Jenis instrumen penilaian berhasil diperbarui.');
    }

    public function destroyJenis(SupervisiJenisPenilaian $jenis): RedirectResponse
    {
        if ($jenis->aspekPenilaians()->exists() || $jenis->hasilSupervisis()->exists()) {
            return redirect()->route('supervisi.admin.instrumen.index', ['tab' => 'jenis'])
                ->with('error', 'Jenis penilaian tidak dapat dihapus karena masih memiliki aspek atau data hasil.');
        }

        $jenis->delete();

        return redirect()->route('supervisi.admin.instrumen.index', ['tab' => 'jenis'])
            ->with('success', 'Jenis instrumen penilaian berhasil dihapus.');
    }

    public function storeAspek(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'jenis_penilaian_id' => ['required', 'exists:supervisi_jenis_penilaian,id'],
            'nama_aspek' => ['required', 'string', 'max:255'],
            'fokus_perilaku' => ['nullable', 'string', 'max:255'],
            'perilaku_dianjurkan' => ['nullable', 'string'],
            'perilaku_dihindari' => ['nullable', 'string'],
            'tipe_skala' => ['required', 'in:skor_1_4,perilaku_pmm_1_3'],
            'urutan' => ['required', 'integer', 'min:1'],
        ]);

        SupervisiAspekPenilaian::create($validated);

        return redirect()->route('supervisi.admin.instrumen.index', ['tab' => 'aspek'])
            ->with('success', 'Aspek / fokus perilaku berhasil ditambahkan.');
    }

    public function updateAspek(Request $request, SupervisiAspekPenilaian $aspek): RedirectResponse
    {
        $validated = $request->validate([
            'jenis_penilaian_id' => ['required', 'exists:supervisi_jenis_penilaian,id'],
            'nama_aspek' => ['required', 'string', 'max:255'],
            'fokus_perilaku' => ['nullable', 'string', 'max:255'],
            'perilaku_dianjurkan' => ['nullable', 'string'],
            'perilaku_dihindari' => ['nullable', 'string'],
            'tipe_skala' => ['required', 'in:skor_1_4,perilaku_pmm_1_3'],
            'urutan' => ['required', 'integer', 'min:1'],
        ]);

        $aspek->update($validated);

        return redirect()->route('supervisi.admin.instrumen.index', ['tab' => 'aspek'])
            ->with('success', 'Aspek penilaian berhasil diperbarui.');
    }

    public function destroyAspek(SupervisiAspekPenilaian $aspek): RedirectResponse
    {
        if ($aspek->detailHasils()->exists()) {
            return redirect()->route('supervisi.admin.instrumen.index', ['tab' => 'aspek'])
                ->with('error', 'Aspek tidak dapat dihapus karena sudah dipakai dalam hasil penilaian.');
        }

        $aspek->delete();

        return redirect()->route('supervisi.admin.instrumen.index', ['tab' => 'aspek'])
            ->with('success', 'Aspek penilaian berhasil dihapus.');
    }
}
