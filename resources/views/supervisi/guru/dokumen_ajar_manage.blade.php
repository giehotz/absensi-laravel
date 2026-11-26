@extends('layouts.supervisi')
@section('title', 'Kelola Perangkat Ajar')
@section('page_title', 'Unggah / Tautkan Perangkat Ajar')

@section('content')
<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
    <div>
        <h2 class="font-heading font-black text-base">{{ $jadwal->schoolClass?->name ?? '-' }} — {{ $jadwal->subject?->name ?? '-' }} ({{ $jadwal->tanggal?->format('d/m/Y') }})</h2>
        <p class="text-xs text-slate-600">Lampirkan tautan Google Drive atau unggah langsung berkas PDF/DOCX (maks. 10 MB).</p>
    </div>

    <form action="{{ route('supervisi.guru.dokumen-ajar.store', $jadwal) }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @csrf
        <div>
            <label for="nama_dokumen" class="block text-xs font-black mb-1">Nama Dokumen</label>
            <input id="nama_dokumen" type="text" name="nama_dokumen" required value="{{ old('nama_dokumen') }}" placeholder="Contoh: Modul Ajar Bab 2" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">
        </div>
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label for="kategori_dokumen" class="block text-xs font-black mb-1">Kategori</label>
                <select id="kategori_dokumen" name="kategori_dokumen" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">
                    <option value="modul_ajar">Modul Ajar</option>
                    <option value="atp">ATP</option>
                    <option value="rpp">RPP</option>
                    <option value="lkpd">LKPD</option>
                    <option value="lainnya">Lainnya</option>
                </select>
            </div>
            <div>
                <label for="fase" class="block text-xs font-black mb-1">Fase Kurmer</label>
                <select id="fase" name="fase" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg bg-white">
                    <option value="">-- Opsional --</option>
                    @foreach(['A','B','C','D','E','F'] as $f)
                        <option value="{{ $f }}">Fase {{ $f }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label for="link_drive" class="block text-xs font-black mb-1">Tautan Google Drive (Opsional jika unggah file)</label>
            <input id="link_drive" type="url" name="link_drive" value="{{ old('link_drive') }}" placeholder="https://drive.google.com/..." class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">
        </div>
        <div>
            <label for="file" class="block text-xs font-black mb-1">Unggah File PDF/DOCX (Opsional jika pakai tautan)</label>
            <input id="file" type="file" name="file" accept=".pdf,.doc,.docx" class="w-full px-3 py-1.5 text-xs border-2 border-black rounded-lg bg-white">
        </div>
        <div class="md:col-span-2">
            <label for="tujuan_pembelajaran" class="block text-xs font-black mb-1">Tujuan Pembelajaran</label>
            <textarea id="tujuan_pembelajaran" name="tujuan_pembelajaran" rows="2" class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">{{ old('tujuan_pembelajaran') }}</textarea>
        </div>
        <div class="md:col-span-2 flex justify-end">
            <button type="submit" class="px-5 py-2 bg-[#FFD43B] text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">+ Simpan Perangkat Ajar</button>
        </div>
    </form>
</div>

<div class="space-y-3">
    @forelse($dokumens as $dok)
        <div class="bg-white border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000] flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs font-black">{{ $dok->nama_dokumen }} <span class="px-2 py-0.5 border border-black rounded text-[10px] bg-amber-100 ml-1">{{ strtoupper($dok->status) }}</span></div>
                @if($dok->feedback)<div class="text-xs text-slate-600 mt-1">Catatan Supervisor: {{ $dok->feedback }}</div>@endif
            </div>
            <div class="flex items-center gap-2 text-xs">
                @if($dok->link_drive)<a href="{{ $dok->link_drive }}" target="_blank" rel="noopener noreferrer" class="px-2.5 py-1 bg-sky-100 border border-black rounded font-bold">Buka Link</a>@endif
                @if($dok->file_path)<a href="{{ asset('storage/'.$dok->file_path) }}" target="_blank" rel="noopener noreferrer" class="px-2.5 py-1 bg-emerald-100 border border-black rounded font-bold">Lihat File</a>@endif
                <form action="{{ route('supervisi.guru.dokumen-ajar.destroy', $dok) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-2.5 py-1 bg-rose-200 border border-black rounded font-bold cursor-pointer">Hapus</button>
                </form>
            </div>
        </div>
    @empty
        <div class="bg-white border-2 border-black rounded-xl p-5 text-center text-xs font-bold text-slate-500">Belum ada dokumen ajar untuk jadwal ini.</div>
    @endforelse
</div>
@endsection
