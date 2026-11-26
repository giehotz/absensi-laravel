@extends('layouts.supervisi')
@section('title', 'Verifikasi Perangkat Ajar')
@section('page_title', 'Telaah & Verifikasi Perangkat Ajar')

@section('content')
<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000]">
    <h2 class="font-heading font-black text-base">{{ $jadwal->teacher?->user?->name }} — {{ $jadwal->schoolClass?->name ?? '-' }} ({{ $jadwal->subject?->name ?? '-' }})</h2>
    <p class="text-xs font-semibold text-slate-600 mt-1">Tanggal Jadwal: {{ $jadwal->tanggal?->format('d/m/Y') }}</p>
</div>

<div class="space-y-4">
    @forelse($dokumens as $dok)
        <div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded border border-black bg-amber-100">{{ $dok->kategori_dokumen }} @if($dok->fase) · Fase {{ $dok->fase }} @endif</span>
                    <h3 class="font-heading font-black text-sm mt-1">{{ $dok->nama_dokumen }}</h3>
                </div>
                <span class="px-2.5 py-1 rounded-lg border-2 border-black text-xs font-black {{ $dok->status === 'disetujui' ? 'bg-emerald-200' : ($dok->status === 'ditolak' ? 'bg-rose-200' : 'bg-slate-100') }}">
                    {{ strtoupper($dok->status) }}
                </span>
            </div>

            @if($dok->tujuan_pembelajaran)
                <p class="text-xs text-slate-700"><strong>Tujuan Pembelajaran:</strong> {{ $dok->tujuan_pembelajaran }}</p>
            @endif
            @if($dok->strategi_diferensiasi)
                <p class="text-xs text-slate-700"><strong>Diferensiasi:</strong> {{ $dok->strategi_diferensiasi }}</p>
            @endif

            <div class="flex flex-wrap gap-2 text-xs font-bold">
                @if($dok->link_drive)
                    <a href="{{ $dok->link_drive }}" target="_blank" rel="noopener noreferrer" class="px-3 py-1.5 rounded-lg border-2 border-black bg-sky-100 hover:bg-sky-200">🔗 Buka Tautan Drive</a>
                @endif
                @if($dok->file_path)
                    <a href="{{ asset('storage/' . $dok->file_path) }}" target="_blank" rel="noopener noreferrer" class="px-3 py-1.5 rounded-lg border-2 border-black bg-emerald-100 hover:bg-emerald-200">📄 Unduh File Perangkat</a>
                @endif
            </div>

            <form action="{{ route("supervisi.{$portalPrefix}.dokumen-ajar.verify", $dok) }}" method="POST" class="pt-3 border-t-2 border-slate-200 flex flex-wrap items-end gap-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold mb-1">Status Verifikasi</label>
                    <select name="status" class="px-3 py-2 text-xs border-2 border-black rounded-lg bg-white font-bold">
                        <option value="pending" {{ $dok->status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="disetujui" {{ $dok->status === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="ditolak" {{ $dok->status === 'ditolak' ? 'selected' : '' }}>Perlu Revisi / Ditolak</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-xs font-bold mb-1">Catatan Telaah</label>
                    <input type="text" name="feedback" value="{{ $dok->feedback }}" placeholder="Berikan masukan telaah perangkat ajar..." class="w-full px-3 py-2 text-xs border-2 border-black rounded-lg">
                </div>
                <button type="submit" class="px-4 py-2 bg-[#FFD43B] text-black font-black text-xs rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                    Simpan Verifikasi
                </button>
            </form>
        </div>
    @empty
        <div class="bg-white border-2 border-black rounded-xl p-6 text-center text-xs font-bold text-slate-500 shadow-[4px_4px_0px_0px_#000]">
            Guru belum mengunggah atau menautkan perangkat ajar untuk jadwal ini.
        </div>
    @endforelse
</div>
@endsection
