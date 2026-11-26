@extends('layouts.supervisi')
@section('title', 'Dashboard Supervisor')
@section('page_title', 'Dashboard Supervisor')

@section('content')
<div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
    <div class="bg-white border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000]"><div class="text-[11px] font-black text-slate-500">Total Binaan</div><div class="text-2xl font-black mt-1 tabular-nums">{{ $stats['total'] }}</div></div>
    <div class="bg-amber-100 border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000]"><div class="text-[11px] font-black text-amber-900">Pra-Observasi</div><div class="text-2xl font-black mt-1 tabular-nums">{{ $stats['menunggu_pra'] }}</div></div>
    <div class="bg-violet-100 border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000]"><div class="text-[11px] font-black text-violet-900">Siap Observasi</div><div class="text-2xl font-black mt-1 tabular-nums">{{ $stats['siap_observasi'] }}</div></div>
    <div class="bg-orange-100 border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000]"><div class="text-[11px] font-black text-orange-900">Tindak Lanjut</div><div class="text-2xl font-black mt-1 tabular-nums">{{ $stats['tindak_lanjut'] }}</div></div>
    <div class="bg-[#20C997] border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000]"><div class="text-[11px] font-black text-slate-900">Selesai</div><div class="text-2xl font-black mt-1 tabular-nums">{{ $stats['selesai'] }}</div></div>
</div>

<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-3">
    <h2 class="font-heading font-black text-sm">Jadwal Supervisi Saya</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-xs border-collapse border-2 border-black">
            <thead class="bg-slate-900 text-white">
                <tr>
                    <th class="p-2 border border-black text-left">Tanggal</th>
                    <th class="p-2 border border-black text-left">Guru</th>
                    <th class="p-2 border border-black text-left">Kelas / Mapel</th>
                    <th class="p-2 border border-black text-left">Status</th>
                    <th class="p-2 border border-black text-center">Aksi Cepat</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jadwals->take(8) as $j)
                    <tr class="odd:bg-white even:bg-slate-50">
                        <td class="p-2 border border-black font-bold">{{ $j->tanggal?->format('d/m/Y') }}</td>
                        <td class="p-2 border border-black font-black">{{ $j->teacher?->user?->name }}</td>
                        <td class="p-2 border border-black">{{ $j->schoolClass?->name ?? '-' }} / {{ $j->subject?->name ?? '-' }}</td>
                        <td class="p-2 border border-black"><span class="px-2 py-0.5 rounded border text-[11px] font-black {{ $j->statusEnum()->badgeClass() }}">{{ $j->statusEnum()->label() }}</span></td>
                        <td class="p-2 border border-black text-center">
                            <a href="{{ route('supervisi.supervisor.jadwal.show', $j) }}" class="px-2.5 py-1 bg-[#FFD43B] border border-black rounded font-black">Kelola Siklus</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-4 text-center text-slate-500">Belum ada jadwal supervisi yang ditugaskan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
