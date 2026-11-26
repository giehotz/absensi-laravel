@extends('layouts.supervisi')
@section('title', 'Dashboard Guru Supervisi')
@section('page_title', 'Portal Supervisi Guru')

@section('content')
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
    <div class="bg-white border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000]"><div class="text-[11px] font-black text-slate-500">Total Jadwal</div><div class="text-2xl font-black mt-1 tabular-nums">{{ $stats['total'] }}</div></div>
    <div class="bg-[#FFD43B] border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000]"><div class="text-[11px] font-black text-slate-800">Berlangsung</div><div class="text-2xl font-black mt-1 tabular-nums">{{ $stats['mendatang'] }}</div></div>
    <div class="bg-[#20C997] border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000]"><div class="text-[11px] font-black text-slate-900">Selesai</div><div class="text-2xl font-black mt-1 tabular-nums">{{ $stats['selesai'] }}</div></div>
    <div class="bg-sky-100 border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000]"><div class="text-[11px] font-black text-sky-950">Rata-rata Nilai</div><div class="text-2xl font-black mt-1 tabular-nums">{{ number_format((float) $stats['rata_rata'], 2) }}</div></div>
</div>

<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-3">
    <h2 class="font-heading font-black text-sm">Jadwal Supervisi Saya</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-xs border-collapse border-2 border-black">
            <thead class="bg-slate-900 text-white">
                <tr>
                    <th class="p-2 border border-black text-left">Tanggal</th>
                    <th class="p-2 border border-black text-left">Supervisor</th>
                    <th class="p-2 border border-black text-left">Kelas / Mapel</th>
                    <th class="p-2 border border-black text-left">Status</th>
                    <th class="p-2 border border-black text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jadwals->take(6) as $j)
                    <tr class="odd:bg-white even:bg-slate-50">
                        <td class="p-2 border border-black font-bold">{{ $j->tanggal?->format('d/m/Y') }}</td>
                        <td class="p-2 border border-black font-black">{{ $j->supervisor_display_name }}</td>
                        <td class="p-2 border border-black">{{ $j->schoolClass?->name ?? '-' }} / {{ $j->subject?->name ?? '-' }}</td>
                        <td class="p-2 border border-black"><span class="px-2 py-0.5 rounded border text-[11px] font-black {{ $j->statusEnum()->badgeClass() }}">{{ $j->statusEnum()->label() }}</span></td>
                        <td class="p-2 border border-black text-center">
                            <div class="inline-flex flex-wrap gap-1 justify-center">
                                <a href="{{ route('supervisi.guru.dokumen-ajar.manage', $j) }}" class="px-2 py-1 bg-slate-100 border border-black rounded font-bold">Perangkat</a>
                                <a href="{{ route('supervisi.guru.pra-observasi.edit', $j) }}" class="px-2 py-1 bg-amber-100 border border-black rounded font-bold">Form A</a>
                                @if(in_array($j->status, ['pasca_observasi', 'selesai'], true) || !$j->isKurmer())
                                    <a href="{{ route('supervisi.guru.tindak-lanjut.edit', $j) }}" class="px-2 py-1 bg-orange-100 border border-black rounded font-bold">Form C/D</a>
                                @endif
                                @if($j->status === 'selesai')
                                    <a href="{{ route('supervisi.guru.hasil.show', $j) }}" class="px-2 py-1 bg-emerald-200 border border-black rounded font-black">Hasil & TTD</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-4 text-center text-slate-500">Belum ada jadwal supervisi untuk Anda.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
