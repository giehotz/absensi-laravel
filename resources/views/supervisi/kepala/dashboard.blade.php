@extends('layouts.supervisi')
@section('title', 'Dashboard Kepala Madrasah')
@section('page_title', 'Dashboard Eksekutif Kepala Madrasah')

@section('content')
<div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
    <div class="bg-white border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000]"><div class="text-[11px] font-black text-slate-500">Total Guru</div><div class="text-2xl font-black mt-1 tabular-nums">{{ $totalGuru }}</div></div>
    <div class="bg-[#FFD43B] border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000]"><div class="text-[11px] font-black text-slate-800">Supervisor</div><div class="text-2xl font-black mt-1 tabular-nums">{{ $totalSupervisor }}</div></div>
    <div class="bg-amber-100 border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000]"><div class="text-[11px] font-black text-amber-900">Pra-Observasi</div><div class="text-2xl font-black mt-1 tabular-nums">{{ $stageCounts['pra_observasi'] }}</div></div>
    <div class="bg-violet-100 border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000]"><div class="text-[11px] font-black text-violet-900">Observasi</div><div class="text-2xl font-black mt-1 tabular-nums">{{ $stageCounts['observasi'] }}</div></div>
    <div class="bg-[#20C997] border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000]"><div class="text-[11px] font-black text-slate-900">Selesai</div><div class="text-2xl font-black mt-1 tabular-nums">{{ $stageCounts['selesai'] }}</div></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-3">
        <h2 class="font-heading font-black text-sm">Capaian 8 Indikator Praktik Pembelajaran (PMM)</h2>
        @foreach($pmmChart as $item)
            <div class="space-y-1">
                <div class="flex justify-between text-xs font-bold"><span>{{ $item['nama'] }}</span><span class="tabular-nums">{{ $item['skor'] }}%</span></div>
                <div class="h-2.5 bg-slate-200 border border-black rounded overflow-hidden"><div class="h-full bg-[#20C997]" style="width: {{ min(100, $item['skor']) }}%"></div></div>
            </div>
        @endforeach
    </div>

    <div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-3">
        <h2 class="font-heading font-black text-sm">Rekap Kebutuhan Dukungan Kombel</h2>
        @forelse($rekapDukunganKombel as $row)
            <div class="flex items-center justify-between text-xs border-b border-slate-200 pb-1.5">
                <span class="font-bold">{{ $row->kebutuhan_dukungan }}</span>
                <span class="px-2 py-0.5 rounded border border-black bg-amber-100 font-black">{{ $row->total }}</span>
            </div>
        @empty
            <p class="text-xs text-slate-500">Belum ada data dukungan Kombel.</p>
        @endforelse
    </div>
</div>

<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-3">
    <div class="flex items-center justify-between">
        <h2 class="font-heading font-black text-sm">Hasil Supervisi Terbaru (Menunggu / Selesai Pengesahan)</h2>
        <a href="{{ route('supervisi.kepala.hasil.index') }}" class="text-xs font-black text-blue-700 underline">Semua Hasil →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-xs border-collapse border-2 border-black">
            <thead class="bg-slate-900 text-white">
                <tr>
                    <th class="p-2 border border-black text-left">Tanggal</th>
                    <th class="p-2 border border-black text-left">Guru</th>
                    <th class="p-2 border border-black text-left">Supervisor</th>
                    <th class="p-2 border border-black text-center">Nilai</th>
                    <th class="p-2 border border-black text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentResults as $r)
                    <tr class="odd:bg-white even:bg-slate-50">
                        <td class="p-2 border border-black font-bold">{{ $r->tanggal?->format('d/m/Y') }}</td>
                        <td class="p-2 border border-black font-black">{{ $r->teacher?->user?->name }}</td>
                        <td class="p-2 border border-black">{{ $r->supervisor_display_name }}</td>
                        <td class="p-2 border border-black text-center font-black tabular-nums">{{ $r->nilai_akhir ?? '-' }} ({{ $r->ketercapaian ?? '-' }})</td>
                        <td class="p-2 border border-black text-center">
                            <a href="{{ route('supervisi.kepala.hasil.show', $r) }}" class="px-2.5 py-1 bg-[#20C997] border border-black rounded font-black">Lihat & Sahkan</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-4 text-center text-slate-500">Belum ada hasil supervisi selesai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
