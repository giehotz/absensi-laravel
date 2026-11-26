@extends('layouts.supervisi')
@section('title', 'Dashboard Admin Supervisi')
@section('page_title', 'Dashboard Administrator Supervisi')

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000]">
        <div class="text-[11px] font-black uppercase text-slate-500">Total Guru</div>
        <div class="text-2xl font-black mt-1 tabular-nums">{{ $totalGuru }}</div>
    </div>
    <div class="bg-[#FFD43B] border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000]">
        <div class="text-[11px] font-black uppercase text-slate-800">Supervisor Aktif</div>
        <div class="text-2xl font-black mt-1 tabular-nums">{{ $totalSupervisor }}</div>
    </div>
    <div class="bg-white border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000]">
        <div class="text-[11px] font-black uppercase text-slate-500">Jadwal {{ $activeYear?->name ?? '' }}</div>
        <div class="text-2xl font-black mt-1 tabular-nums">{{ $totalJadwal }}</div>
    </div>
    <div class="bg-[#20C997] border-2 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000]">
        <div class="text-[11px] font-black uppercase text-slate-900">Keterlaksanaan</div>
        <div class="text-2xl font-black mt-1 tabular-nums">{{ $completionRate }}% ({{ $jadwalSelesai }})</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-3">
        <h2 class="font-heading font-black text-sm">Progres Siklus Kurikulum Merdeka</h2>
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 text-center text-xs">
            @foreach(['terjadwal' => 'Terjadwal', 'pra_observasi' => 'Form A', 'observasi' => 'Observasi', 'pasca_observasi' => 'Form C/D', 'selesai' => 'Selesai'] as $k => $lbl)
                <div class="p-3 rounded-lg border-2 border-black bg-slate-50">
                    <div class="font-black text-lg tabular-nums">{{ $stageCounts[$k] ?? 0 }}</div>
                    <div class="text-[11px] font-bold text-slate-600">{{ $lbl }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-3">
        <h2 class="font-heading font-black text-sm">Rekap Kebutuhan Dukungan Kombel (Form C)</h2>
        @forelse($rekapDukunganKombel as $row)
            <div class="flex items-center justify-between text-xs border-b border-slate-200 pb-1.5">
                <span class="font-bold">{{ $row->kebutuhan_dukungan }}</span>
                <span class="px-2 py-0.5 rounded border border-black bg-amber-100 font-black tabular-nums">{{ $row->total }}</span>
            </div>
        @empty
            <p class="text-xs text-slate-500">Belum ada data kebutuhan dukungan tindak lanjut.</p>
        @endforelse
    </div>
</div>

<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-3">
    <h2 class="font-heading font-black text-sm">Capaian Rata-rata 8 Indikator Praktik Kinerja PMM</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @foreach($pmmChart as $item)
            <div class="space-y-1">
                <div class="flex justify-between text-xs font-bold">
                    <span class="truncate">{{ $item['nama'] }}</span>
                    <span class="tabular-nums">{{ $item['skor'] }}%</span>
                </div>
                <div class="h-3 w-full bg-slate-200 border border-black rounded overflow-hidden">
                    <div class="h-full bg-[#5294FF]" style="width: {{ min(100, $item['skor']) }}%"></div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="bg-white border-2 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000] space-y-3">
    <div class="flex items-center justify-between">
        <h2 class="font-heading font-black text-sm">Jadwal Supervisi Terdekat</h2>
        <a href="{{ route('supervisi.admin.jadwal.index') }}" class="text-xs font-black text-blue-700 underline">Lihat Semua →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-xs border-collapse border-2 border-black">
            <thead class="bg-slate-900 text-white">
                <tr>
                    <th class="p-2 border border-black text-left">Tanggal</th>
                    <th class="p-2 border border-black text-left">Guru</th>
                    <th class="p-2 border border-black text-left">Supervisor</th>
                    <th class="p-2 border border-black text-left">Kelas / Mapel</th>
                    <th class="p-2 border border-black text-left">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($upcomingJadwals as $j)
                    <tr class="odd:bg-white even:bg-slate-50">
                        <td class="p-2 border border-black font-bold">{{ $j->tanggal?->format('d/m/Y') }}</td>
                        <td class="p-2 border border-black font-black">{{ $j->teacher?->user?->name }}</td>
                        <td class="p-2 border border-black">{{ $j->supervisor_display_name }}</td>
                        <td class="p-2 border border-black">{{ $j->schoolClass?->name ?? '-' }} / {{ $j->subject?->name ?? '-' }}</td>
                        <td class="p-2 border border-black"><span class="px-2 py-0.5 rounded border text-[11px] font-black {{ $j->statusEnum()->badgeClass() }}">{{ $j->statusEnum()->label() }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-4 text-center text-slate-500">Tidak ada jadwal terdekat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
