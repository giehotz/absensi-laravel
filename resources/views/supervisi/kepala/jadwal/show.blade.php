@extends('layouts.supervisi')
@section('title', 'Detail Jadwal Kepala')
@section('page_title', 'Detail Jadwal Supervisi')

@section('content')
@php
    $kepalaTeacherId = auth()->user()?->teacher?->id;
    $isAssignedToKepala = ($kepalaTeacherId && (int) $jadwal->supervisor_teacher_id === (int) $kepalaTeacherId)
        || (int) $jadwal->supervisor_user_id === (int) auth()->id()
        || (! $jadwal->supervisor_teacher_id && ! $jadwal->supervisor_user_id);
@endphp
<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h2 class="font-heading font-black text-lg">{{ $jadwal->teacher?->user?->name }}</h2>
            <p class="text-xs font-semibold text-slate-600">
                {{ $jadwal->schoolClass?->name ?? '-' }} · {{ $jadwal->subject?->name ?? '-' }} · {{ $jadwal->tanggal?->format('d/m/Y') }}
                · Supervisor: <strong>{{ $jadwal->supervisor_display_name }}</strong>
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($jadwal->isKurmer())
                <a href="{{ route('supervisi.kepala.pra-observasi.show', $jadwal) }}" class="px-3 py-2 bg-purple-100 border-2 border-black rounded-lg text-xs font-black">📋 Form A (Pra-Observasi)</a>
            @endif
            <a href="{{ route('supervisi.kepala.dokumen-ajar.index', $jadwal) }}" class="px-3 py-2 bg-slate-100 border-2 border-black rounded-lg text-xs font-black">📂 Telaah Perangkat</a>
            @if($isAssignedToKepala)
                <a href="{{ route('supervisi.kepala.penilaian.edit', $jadwal) }}" class="px-3 py-2 bg-[#FFD43B] border-2 border-black rounded-lg text-xs font-black">📝 Nilai Langsung</a>
            @else
                <a href="{{ route('supervisi.kepala.penilaian.edit', $jadwal) }}" class="px-3 py-2 bg-amber-100 border-2 border-black rounded-lg text-xs font-black">⚠️ Nilai (Milik {{ $jadwal->supervisor_display_name }})</a>
            @endif
            @if($jadwal->isKurmer() && $jadwal->statusEnum()->canAccessPascaObservasi(true))
                <a href="{{ route('supervisi.kepala.tindak-lanjut.show', $jadwal) }}" class="px-3 py-2 bg-sky-100 border-2 border-black rounded-lg text-xs font-black">🔁 Form C &amp; D (Tindak Lanjut)</a>
            @endif
            <a href="{{ route('supervisi.kepala.hasil.show', $jadwal) }}" class="px-3 py-2 bg-[#20C997] border-2 border-black rounded-lg text-xs font-black">✍️ Hasil & TTD Kepala</a>
        </div>
    </div>

    @if(!$isAssignedToKepala)
        <div class="p-3 rounded-lg border-2 border-black bg-amber-50 text-xs font-semibold text-amber-950">
            Catatan: Jadwal ini ditugaskan kepada supervisor penilai <strong>{{ $jadwal->supervisor_display_name }}</strong>. Perubahan nilai pada menu Kepala akan menimpa penilaian supervisor terkait.
        </div>
    @endif
</div>
@endsection
