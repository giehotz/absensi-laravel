@extends('layouts.supervisi')
@section('title', 'Instrumen Penilaian')
@section('page_title', 'Master Instrumen & Aspek Penilaian')

@section('content')
<div class="flex gap-2">
    <a href="{{ route('supervisi.admin.instrumen.index', ['tab' => 'jenis']) }}" class="px-4 py-2 rounded-lg border-2 border-black text-xs font-black {{ $tab === 'jenis' ? 'bg-[#FFD43B]' : 'bg-white' }}">Jenis Penilaian ({{ $jenisList->count() }})</a>
    <a href="{{ route('supervisi.admin.instrumen.index', ['tab' => 'aspek']) }}" class="px-4 py-2 rounded-lg border-2 border-black text-xs font-black {{ $tab === 'aspek' ? 'bg-[#FFD43B]' : 'bg-white' }}">Aspek & Fokus Perilaku ({{ $aspekList->count() }})</a>
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
                            <form action="{{ route('supervisi.admin.instrumen.jenis.destroy', $jenis) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2 py-1 bg-rose-200 border border-black rounded font-bold cursor-pointer">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
        <h2 class="font-heading font-black text-sm">Tambah Aspek / Fokus Perilaku</h2>
        <form action="{{ route('supervisi.admin.instrumen.aspek.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-3">
            @csrf
            <select name="jenis_penilaian_id" required class="px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">
                @foreach($jenisList as $jenis)
                    <option value="{{ $jenis->id }}">{{ $jenis->nama }}</option>
                @endforeach
            </select>
            <input type="text" name="nama_aspek" placeholder="Nama Aspek" required class="px-3 py-2 text-xs border-2 border-black rounded-lg">
            <select name="tipe_skala" required class="px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">
                <option value="skor_1_4">Skala 1–4 (Reguler)</option>
                <option value="perilaku_pmm_1_3">Skala 1–3 (PMM)</option>
            </select>
            <input type="text" name="fokus_perilaku" placeholder="Fokus Perilaku (opsional)" class="px-3 py-2 text-xs border-2 border-black rounded-lg">
            <input type="number" name="urutan" value="1" min="1" required class="px-3 py-2 text-xs border-2 border-black rounded-lg">
            <button type="submit" class="px-4 py-2 bg-[#FFD43B] font-black text-xs rounded-lg border-2 border-black cursor-pointer">+ Tambah Aspek</button>
        </form>
    </div>

    <div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] overflow-x-auto">
        <table class="w-full text-xs border-collapse border-2 border-black">
            <thead class="bg-slate-900 text-white">
                <tr>
                    <th class="p-2 border border-black text-left">Jenis</th>
                    <th class="p-2 border border-black text-center w-14">Urut</th>
                    <th class="p-2 border border-black text-left">Nama Aspek & Fokus Perilaku</th>
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
                            @if($aspek->fokus_perilaku)<div class="text-[11px] text-slate-600">{{ $aspek->fokus_perilaku }}</div>@endif
                        </td>
                        <td class="p-2 border border-black text-center font-mono">{{ $aspek->tipe_skala }}</td>
                        <td class="p-2 border border-black text-center">
                            <form action="{{ route('supervisi.admin.instrumen.aspek.destroy', $aspek) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2 py-1 bg-rose-200 border border-black rounded font-bold cursor-pointer">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
