@extends('layouts.supervisi')
@section('title', 'Instrumen Penilaian')
@section('page_title', 'Master Instrumen & Aspek Penilaian')

@section('content')
<div class="flex flex-wrap gap-2">
    <a href="{{ route('supervisi.admin.instrumen.index', ['tab' => 'jenis']) }}" class="px-4 py-2 rounded-lg border-2 border-black text-xs font-black {{ $tab === 'jenis' ? 'bg-[#FFD43B]' : 'bg-white' }}">Jenis Penilaian ({{ $jenisList->count() }})</a>
    <a href="{{ route('supervisi.admin.instrumen.index', ['tab' => 'aspek']) }}" class="px-4 py-2 rounded-lg border-2 border-black text-xs font-black {{ $tab === 'aspek' ? 'bg-[#FFD43B]' : 'bg-white' }}">Aspek &amp; Keterangan ({{ $aspekList->count() }})</a>
</div>

@if($tab === 'jenis')
    <div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
        <h2 class="font-heading font-black text-sm">Tambah Jenis Penilaian</h2>
        <form action="{{ route('supervisi.admin.instrumen.jenis.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-3">
            @csrf
            <input type="text" name="kode" placeholder="Kode unik (mis. REG-CUSTOM)" required class="px-3 py-2 text-xs border-2 border-black rounded-lg">
            <input type="text" name="nama" placeholder="Nama Instrumen / Indikator" required class="px-3 py-2 text-xs border-2 border-black rounded-lg">
            <select name="kategori_supervisi" required class="px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">
                <option value="reguler">Reguler</option>
                <option value="observasi_kelas_pmm">Observasi Kelas PMM</option>
                <option value="perangkat_ajar">Telaah Perangkat Ajar</option>
            </select>
            <input type="text" name="dimensi_pmm" placeholder="Dimensi PMM (opsional)" class="px-3 py-2 text-xs border-2 border-black rounded-lg">
            <select name="skor_maksimal" required class="px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">
                <option value="4">Skala 1–4</option>
                <option value="3">Skala 1–3 (PMM)</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-[#FFD43B] font-black text-xs rounded-lg border-2 border-black cursor-pointer">+ Tambah Jenis</button>
        </form>
    </div>

    <div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] overflow-x-auto">
        <table class="w-full text-xs border-collapse border-2 border-black">
            <thead class="bg-slate-900 text-white">
                <tr>
                    <th class="p-2 border border-black text-left">Kode</th>
                    <th class="p-2 border border-black text-left">Nama</th>
                    <th class="p-2 border border-black text-left">Kategori</th>
                    <th class="p-2 border border-black text-center">Skala Maks</th>
                    <th class="p-2 border border-black text-center">Jumlah Aspek</th>
                    <th class="p-2 border border-black text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jenisList as $jenis)
                    <tr class="odd:bg-white even:bg-slate-50">
                        <td class="p-2 border border-black font-mono font-bold">{{ $jenis->kode }}</td>
                        <td class="p-2 border border-black font-black">{{ $jenis->nama }}</td>
                        <td class="p-2 border border-black">{{ $jenis->kategori_supervisi }} @if($jenis->dimensi_pmm) ({{ $jenis->dimensi_pmm }}) @endif</td>
                        <td class="p-2 border border-black text-center font-black">{{ $jenis->skor_maksimal }}</td>
                        <td class="p-2 border border-black text-center font-black tabular-nums">{{ $jenis->aspek_penilaians_count }}</td>
                        <td class="p-2 border border-black text-center">
                            <div class="inline-flex gap-1">
                                <a href="{{ route('supervisi.admin.instrumen.index', ['tab' => 'aspek', 'jenis_id' => $jenis->id]) }}" class="px-2 py-1 bg-sky-100 border border-black rounded font-bold">Lihat Aspek</a>
                                <form action="{{ route('supervisi.admin.instrumen.jenis.destroy', $jenis) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2 py-1 bg-rose-200 border border-black rounded font-bold cursor-pointer">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-heading font-black text-sm">Tambah Aspek &amp; Keterangan Indikator</h2>
            <form method="GET" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="tab" value="aspek">
                <select name="jenis_id" aria-label="Filter Jenis Instrumen" class="px-3 py-1.5 text-xs border-2 border-black rounded-lg bg-white font-bold max-w-xs">
                    <option value="all" {{ (string) $selectedJenisId === 'all' ? 'selected' : '' }}>Semua Jenis Instrumen ({{ $jenisList->count() }})</option>
                    @foreach($jenisList as $jenis)
                        <option value="{{ $jenis->id }}" {{ (string) $selectedJenisId === (string) $jenis->id ? 'selected' : '' }}>{{ $jenis->kode }} — {{ $jenis->nama }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-3 py-1.5 bg-slate-900 text-white text-xs font-black rounded-lg border-2 border-black cursor-pointer">Filter</button>
            </form>
        </div>

        <form action="{{ route('supervisi.admin.instrumen.aspek.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-3">
            @csrf
            <select name="jenis_penilaian_id" required class="px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">
                @foreach($jenisList as $jenis)
                    <option value="{{ $jenis->id }}" {{ (string) $selectedJenisId === (string) $jenis->id ? 'selected' : '' }}>{{ $jenis->nama }}</option>
                @endforeach
            </select>
            <input type="text" name="nama_aspek" placeholder="Nama Aspek" required class="px-3 py-2 text-xs border-2 border-black rounded-lg">
            <select name="tipe_skala" required class="px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">
                <option value="skor_1_4">Skala 1–4 (Reguler / Perangkat)</option>
                <option value="perilaku_pmm_1_3">Skala 1–3 (PMM)</option>
            </select>
            <input type="text" name="fokus_perilaku" placeholder="Keterangan / Fokus Perilaku Aspek" maxlength="500" class="md:col-span-2 px-3 py-2 text-xs border-2 border-black rounded-lg">
            <div class="flex gap-2">
                <input type="number" name="urutan" value="1" min="1" required class="w-24 px-3 py-2 text-xs border-2 border-black rounded-lg">
                <button type="submit" class="flex-1 px-4 py-2 bg-[#FFD43B] font-black text-xs rounded-lg border-2 border-black cursor-pointer">+ Tambah Aspek</button>
            </div>
        </form>
    </div>

    <div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] overflow-x-auto">
        <table class="w-full text-xs border-collapse border-2 border-black">
            <thead class="bg-slate-900 text-white">
                <tr>
                    <th class="p-2 border border-black text-left">Jenis</th>
                    <th class="p-2 border border-black text-center w-14">Urut</th>
                    <th class="p-2 border border-black text-left">Nama Aspek &amp; Keterangan</th>
                    <th class="p-2 border border-black text-center">Skala</th>
                    <th class="p-2 border border-black text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($aspekList as $aspek)
                    <tr class="odd:bg-white even:bg-slate-50">
                        <td class="p-2 border border-black font-semibold">{{ $aspek->jenisPenilaian?->nama }}</td>
                        <td class="p-2 border border-black text-center font-black">{{ $aspek->urutan }}</td>
                        <td class="p-2 border border-black">
                            <div class="font-black">{{ $aspek->nama_aspek }}</div>
                            @if($aspek->fokus_perilaku)<div class="text-[11px] text-slate-700 mt-0.5">{{ $aspek->fokus_perilaku }}</div>@endif
                        </td>
                        <td class="p-2 border border-black text-center font-mono">{{ $aspek->tipe_skala }}</td>
                        <td class="p-2 border border-black text-center">
                            <div class="inline-flex gap-1">
                                <button type="button"
                                    data-edit-aspek-url="{{ route('supervisi.admin.instrumen.aspek.update', $aspek) }}"
                                    data-jenis-id="{{ $aspek->jenis_penilaian_id }}"
                                    data-nama="{{ $aspek->nama_aspek }}"
                                    data-fokus="{{ $aspek->fokus_perilaku ?? '' }}"
                                    data-anjuran="{{ $aspek->perilaku_dianjurkan ?? '' }}"
                                    data-hindari="{{ $aspek->perilaku_dihindari ?? '' }}"
                                    data-skala="{{ $aspek->tipe_skala }}"
                                    data-urutan="{{ $aspek->urutan }}"
                                    class="px-2 py-1 bg-amber-100 border border-black rounded font-bold cursor-pointer">
                                    Edit
                                </button>
                                <form action="{{ route('supervisi.admin.instrumen.aspek.destroy', $aspek) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2 py-1 bg-rose-200 border border-black rounded font-bold cursor-pointer">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <dialog id="dialogEditAspek" aria-labelledby="dialogEditAspekTitle" class="m-auto w-full max-w-lg rounded-xl border-4 border-black bg-white p-5 shadow-[6px_6px_0px_0px_#000]">
        <form id="form-edit-aspek" method="POST" class="space-y-3">
            @csrf
            @method('PUT')
            <h3 id="dialogEditAspekTitle" class="font-heading font-black text-base">Edit Aspek &amp; Keterangan Indikator</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="md:col-span-2">
                    <label for="edit_jenis_penilaian_id" class="block text-xs font-black mb-1">Jenis Instrumen</label>
                    <select id="edit_jenis_penilaian_id" name="jenis_penilaian_id" required class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">
                        @foreach($jenisList as $jenis)
                            <option value="{{ $jenis->id }}">{{ $jenis->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="edit_nama_aspek" class="block text-xs font-black mb-1">Nama Aspek</label>
                    <input id="edit_nama_aspek" type="text" name="nama_aspek" required class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label for="edit_tipe_skala" class="block text-xs font-black mb-1">Skala</label>
                        <select id="edit_tipe_skala" name="tipe_skala" required class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">
                            <option value="skor_1_4">1–4</option>
                            <option value="perilaku_pmm_1_3">1–3 (PMM)</option>
                        </select>
                    </div>
                    <div>
                        <label for="edit_urutan" class="block text-xs font-black mb-1">Urutan</label>
                        <input id="edit_urutan" type="number" name="urutan" min="1" required class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label for="edit_fokus_perilaku" class="block text-xs font-black mb-1">Keterangan / Fokus Perilaku Indikator</label>
                    <textarea id="edit_fokus_perilaku" name="fokus_perilaku" rows="2" maxlength="500" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg"></textarea>
                </div>
                <div class="md:col-span-2">
                    <label for="edit_perilaku_dianjurkan" class="block text-xs font-black mb-1">Indikator Keterpenuhan / Dianjurkan</label>
                    <textarea id="edit_perilaku_dianjurkan" name="perilaku_dianjurkan" rows="2" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg"></textarea>
                </div>
                <div class="md:col-span-2">
                    <label for="edit_perilaku_dihindari" class="block text-xs font-black mb-1">Hal yang Dihindari (Opsional)</label>
                    <textarea id="edit_perilaku_dihindari" name="perilaku_dihindari" rows="2" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t-2 border-slate-200">
                <button type="button" id="btn-cancel-edit-aspek" class="px-4 py-2 rounded-lg border-2 border-black bg-white text-xs font-black cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg border-2 border-black bg-[#FFD43B] text-xs font-black cursor-pointer">Simpan Perubahan</button>
            </div>
        </form>
    </dialog>
@endif
@endsection

@push('scripts')
<script>
(() => {
    const dialog = document.getElementById('dialogEditAspek');
    const form = document.getElementById('form-edit-aspek');
    if (!dialog || !form) {
        return;
    }

    document.querySelectorAll('[data-edit-aspek-url]').forEach((btn) => {
        btn.addEventListener('click', () => {
            form.action = btn.dataset.editAspekUrl;
            document.getElementById('edit_jenis_penilaian_id').value = btn.dataset.jenisId || '';
            document.getElementById('edit_nama_aspek').value = btn.dataset.nama || '';
            document.getElementById('edit_fokus_perilaku').value = btn.dataset.fokus || '';
            document.getElementById('edit_perilaku_dianjurkan').value = btn.dataset.anjuran || '';
            document.getElementById('edit_perilaku_dihindari').value = btn.dataset.hindari || '';
            document.getElementById('edit_tipe_skala').value = btn.dataset.skala || 'skor_1_4';
            document.getElementById('edit_urutan').value = btn.dataset.urutan || '1';
            dialog.showModal();
        });
    });

    document.getElementById('btn-cancel-edit-aspek')?.addEventListener('click', () => {
        dialog.close();
    });
})();
</script>
@endpush
