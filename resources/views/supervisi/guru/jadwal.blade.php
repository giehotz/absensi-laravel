@extends('layouts.supervisi')
@section('title', 'Jadwal Supervisi Saya')
@section('page_title', 'Daftar Jadwal Supervisi Guru')

@section('content')
<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-3 overflow-x-auto">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <h2 class="font-heading font-black text-sm">Jadwal &amp; Checklist Pengisian Supervisi</h2>
        <div class="flex flex-wrap items-center gap-2 text-[11px] font-bold">
            <span class="px-2 py-0.5 rounded border border-black bg-emerald-200 text-emerald-950">✓ Hijau: Selesai Diisi</span>
            <span class="px-2 py-0.5 rounded border border-black bg-[#FFD43B] text-black">⏳ Kuning: Belum Lengkap</span>
            <span class="px-2 py-0.5 rounded border border-black bg-rose-200 text-rose-950">✕ Merah: Belum Diisi</span>
        </div>
    </div>

    <table class="w-full text-xs border-collapse border-2 border-black">
        <thead class="bg-slate-900 text-white">
            <tr>
                <th class="p-2 border border-black text-left">Tanggal</th>
                <th class="p-2 border border-black text-left">Supervisor</th>
                <th class="p-2 border border-black text-left">Kelas / Mapel</th>
                <th class="p-2 border border-black text-left">Status Siklus</th>
                <th class="p-2 border border-black text-center">Checklist Step Pengisian</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jadwals as $j)
                @php $states = $stepStatesMap[$j->id] ?? []; @endphp
                <tr class="odd:bg-white even:bg-slate-50">
                    <td class="p-2 border border-black font-bold">{{ $j->tanggal?->format('d/m/Y') }} ({{ $j->hari }})</td>
                    <td class="p-2 border border-black font-black">{{ $j->supervisor_display_name }}</td>
                    <td class="p-2 border border-black">{{ $j->schoolClass?->name ?? '-' }} / {{ $j->subject?->name ?? '-' }}</td>
                    <td class="p-2 border border-black"><span class="px-2 py-0.5 rounded border text-[11px] font-black {{ $j->statusEnum()->badgeClass() }}">{{ $j->statusEnum()->label() }}</span></td>
                    <td class="p-2 border border-black text-center">
                        <div class="inline-flex flex-wrap gap-1.5 justify-center items-center">
                            @foreach($states as $st)
                                <a href="{{ route('supervisi.guru.siklus.show', ['jadwal' => $j, 'step' => $st['step']]) }}"
                                    title="{{ $st['title'] }}: {{ $st['status_label'] }}"
                                    class="px-2 py-1 border border-black rounded text-[11px] font-black {{ $st['badge_class'] }}">
                                    {{ $st['icon'] }} {{ $st['short_title'] }}
                                </a>
                            @endforeach
                            <a href="{{ route('supervisi.guru.siklus.show', $j) }}"
                                class="px-2.5 py-1 bg-slate-900 hover:bg-slate-700 text-white border border-black rounded text-[11px] font-black">
                                📋 Isi Step Form
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="p-4 text-center text-slate-500">Belum ada jadwal supervisi.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
