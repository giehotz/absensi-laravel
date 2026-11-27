@extends('layouts.supervisi')
@section('title', 'Jadwal Supervisi Kepala')
@section('page_title', 'Kelola Jadwal Supervisi (Kepala Madrasah)')

@section('content')
<div class="bg-white border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000] flex flex-wrap justify-between items-center gap-3">
    <h2 class="font-heading font-black text-sm">Seluruh Jadwal Supervisi ({{ $jadwals->count() }})</h2>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('supervisi.kepala.jadwal.create') }}" class="px-3.5 py-2 bg-[#FFD43B] text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000]">+ Tambah Jadwal</a>
        <a href="{{ route('supervisi.kepala.jadwal.export-excel') }}" class="px-3.5 py-2 bg-amber-100 text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000]">📊 Export Excel</a>
        <a href="{{ route('supervisi.kepala.jadwal.print') }}" target="_blank" class="px-3.5 py-2 bg-[#20C997] text-black text-xs font-black rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000]">🖨️ Cetak Jadwal</a>
    </div>
</div>

<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] overflow-x-auto">
    <table class="w-full text-xs border-collapse border-2 border-black">
        <thead class="bg-slate-900 text-white">
            <tr>
                <th class="p-2 border border-black text-left">Tanggal</th>
                <th class="p-2 border border-black text-left">Guru</th>
                <th class="p-2 border border-black text-left">Supervisor</th>
                <th class="p-2 border border-black text-left">Kelas / Mapel</th>
                <th class="p-2 border border-black text-left">Status</th>
                <th class="p-2 border border-black text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @php $kepalaTeacherId = auth()->user()?->teacher?->id; @endphp
            @forelse($jadwals as $j)
                @php
                    $isAssignedToKepala = ($kepalaTeacherId && (int) $j->supervisor_teacher_id === (int) $kepalaTeacherId)
                        || (int) $j->supervisor_user_id === (int) auth()->id()
                        || (! $j->supervisor_teacher_id && ! $j->supervisor_user_id);
                @endphp
                <tr class="odd:bg-white even:bg-slate-50">
                    <td class="p-2 border border-black font-bold">{{ $j->tanggal?->format('d/m/Y') }}</td>
                    <td class="p-2 border border-black font-black">{{ $j->teacher?->user?->name }}</td>
                    <td class="p-2 border border-black">{{ $j->supervisor_display_name }}</td>
                    <td class="p-2 border border-black">{{ $j->schoolClass?->name ?? '-' }} / {{ $j->subject?->name ?? '-' }}</td>
                    <td class="p-2 border border-black"><span class="px-2 py-0.5 rounded border text-[11px] font-black {{ $j->statusEnum()->badgeClass() }}">{{ $j->statusEnum()->label() }}</span></td>
                    <td class="p-2 border border-black text-center">
                        <div class="inline-flex flex-wrap justify-center gap-1">
                            @if($j->isKurmer())
                                <a href="{{ route('supervisi.kepala.pra-observasi.show', $j) }}" class="px-2 py-1 bg-purple-100 border border-black rounded font-bold">Form A</a>
                            @endif
                            @if($isAssignedToKepala)
                                <a href="{{ route('supervisi.kepala.penilaian.edit', $j) }}" class="px-2 py-1 bg-sky-100 border border-black rounded font-bold">Nilai Langsung</a>
                            @else
                                <a href="{{ route('supervisi.kepala.penilaian.edit', $j) }}" title="Jadwal ini ditugaskan ke {{ $j->supervisor_display_name }}" class="px-2 py-1 bg-slate-100 text-slate-700 border border-black rounded font-bold">Nilai (Sup. Lain)</a>
                            @endif
                            @if($j->isKurmer() && $j->statusEnum()->canAccessPascaObservasi(true))
                                <a href="{{ route('supervisi.kepala.tindak-lanjut.show', $j) }}" class="px-2 py-1 bg-emerald-100 border border-black rounded font-bold">Form C/D</a>
                            @endif
                            <a href="{{ route('supervisi.kepala.jadwal.show', $j) }}" class="px-2 py-1 bg-amber-100 border border-black rounded font-bold">Detail</a>
                            <a href="{{ route('supervisi.kepala.jadwal.edit', $j) }}" class="px-2 py-1 bg-white border border-black rounded font-bold">Edit</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="p-4 text-center text-slate-500">Belum ada jadwal supervisi.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
