@extends('layouts.supervisi')
@section('title', 'Detail Jadwal Kepala')
@section('page_title', 'Detail Jadwal Supervisi')

@section('content')
<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h2 class="font-heading font-black text-lg">{{ $jadwal->teacher?->user?->name }}</h2>
            <p class="text-xs font-semibold text-slate-600">{{ $jadwal->schoolClass?->name ?? '-' }} · {{ $jadwal->subject?->name ?? '-' }} · {{ $jadwal->tanggal?->format('d/m/Y') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('supervisi.kepala.dokumen-ajar.index', $jadwal) }}" class="px-3 py-2 bg-slate-100 border-2 border-black rounded-lg text-xs font-black">📂 Telaah Perangkat</a>
            <a href="{{ route('supervisi.kepala.penilaian.edit', $jadwal) }}" class="px-3 py-2 bg-[#FFD43B] border-2 border-black rounded-lg text-xs font-black">📝 Nilai Langsung</a>
            <a href="{{ route('supervisi.kepala.hasil.show', $jadwal) }}" class="px-3 py-2 bg-[#20C997] border-2 border-black rounded-lg text-xs font-black">✍️ Hasil & TTD Kepala</a>
        </div>
    </div>
</div>
@endsection
