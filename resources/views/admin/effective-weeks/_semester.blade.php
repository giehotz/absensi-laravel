@props([
    'semesterKey',
    'semesterData',
    'params',
])

@php
    $analysis = $semesterData['analysis'];
    $hours = $semesterData['hours'];
    $isGasal = $semesterKey === 'ganjil';
    $themeColor = $isGasal ? '#5294FF' : '#20C997';
@endphp

<div class="space-y-6">
    <!-- Stat Cards Overview -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-white neo-box p-3.5 border-2 border-black shadow-[3px_3px_0px_0px_#000]">
            <span class="text-[11px] font-black uppercase text-slate-500 tracking-wider">Total Minggu Kalender</span>
            <div class="flex items-baseline gap-1 mt-1">
                <span class="text-2xl font-heading font-black text-slate-900">{{ $analysis['total_weeks'] }}</span>
                <span class="text-xs font-bold text-slate-600">Minggu</span>
            </div>
            <p class="text-[10px] text-slate-500 mt-0.5">Rentang kalender 6 bulan</p>
        </div>

        <div class="bg-[#D3F9D8] neo-box p-3.5 border-2 border-black shadow-[3px_3px_0px_0px_#000]">
            <span class="text-[11px] font-black uppercase text-green-800 tracking-wider">Minggu Efektif KBM</span>
            <div class="flex items-baseline gap-1 mt-1">
                <span class="text-2xl font-heading font-black text-green-900">{{ $analysis['total_effective_weeks'] }}</span>
                <span class="text-xs font-bold text-green-800">Minggu</span>
            </div>
            <p class="text-[10px] text-green-700 mt-0.5">Aktif KBM tatap muka</p>
        </div>

        <div class="bg-[#FFE3E3] neo-box p-3.5 border-2 border-black shadow-[3px_3px_0px_0px_#000]">
            <span class="text-[11px] font-black uppercase text-red-800 tracking-wider">Minggu Tidak Efektif</span>
            <div class="flex items-baseline gap-1 mt-1">
                <span class="text-2xl font-heading font-black text-red-900">{{ $analysis['total_non_effective_weeks'] }}</span>
                <span class="text-xs font-bold text-red-800">Minggu</span>
            </div>
            <p class="text-[10px] text-red-700 mt-0.5">Libur, ujian, kegiatan</p>
        </div>

        <div class="bg-[#FFF4E6] neo-box p-3.5 border-2 border-black shadow-[3px_3px_0px_0px_#000]">
            <span class="text-[11px] font-black uppercase text-amber-800 tracking-wider">Total Jam KBM</span>
            <div class="flex items-baseline gap-1 mt-1">
                <span class="text-2xl font-heading font-black text-amber-900">{{ $hours['teaching_hours'] }}</span>
                <span class="text-xs font-bold text-amber-800">JP</span>
            </div>
            <p class="text-[10px] text-amber-700 mt-0.5">{{ $analysis['total_effective_weeks'] }} minggu &times; {{ $params['jp_per_week'] }} JP</p>
        </div>
    </div>

    <!-- Tabel Analisis Bulan -->
    <div class="bg-white neo-box border-2 border-black shadow-[4px_4px_0px_0px_#000] overflow-hidden">
        <div class="p-4 border-b-2 border-black flex items-center justify-between bg-slate-50">
            <div class="flex items-center gap-2">
                <span class="text-lg">📊</span>
                <h3 class="font-heading font-black text-sm uppercase tracking-wide text-slate-900">
                    Rekapitulasi Minggu Efektif Semester {{ $semesterData['label'] }}
                </h3>
            </div>
            <span class="text-xs font-bold text-slate-600 bg-white px-2.5 py-1 border border-black rounded shadow-[1px_1px_0px_0px_#000]">
                Syarat Min: {{ $params['min_effective_days'] }} Hari Aktif / Minggu
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="bg-black text-white font-heading font-black text-[11px] uppercase tracking-wider">
                        <th class="py-2.5 px-3 text-center border-r border-slate-700 w-12">No</th>
                        <th class="py-2.5 px-4 border-r border-slate-700">Nama Bulan</th>
                        <th class="py-2.5 px-3 text-center border-r border-slate-700 w-32">Jml Minggu</th>
                        <th class="py-2.5 px-3 text-center border-r border-slate-700 w-32 bg-emerald-900 text-emerald-200">Minggu Efektif</th>
                        <th class="py-2.5 px-3 text-center border-r border-slate-700 w-36 bg-rose-950 text-rose-200">Minggu Tdk Efektif</th>
                        <th class="py-2.5 px-4">Keterangan Kegiatan / Libur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/10 font-medium text-slate-800">
                    @php $idx = 1; @endphp
                    @foreach($analysis['months'] as $m)
                        <tr class="hover:bg-amber-50/50 transition-colors {{ $m['effective_weeks'] == 0 ? 'bg-rose-50/40' : '' }}">
                            <td class="py-3 px-3 text-center font-bold border-r border-black/10">{{ $idx++ }}</td>
                            <td class="py-3 px-4 font-black text-slate-900 border-r border-black/10">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full border border-black" style="background-color: {{ $m['effective_weeks'] > 0 ? '#20C997' : '#FF6B6B' }};"></span>
                                    <span>{{ $m['month_name'] }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-3 text-center font-bold border-r border-black/10 text-slate-700">
                                {{ $m['total_weeks'] }}
                            </td>
                            <td class="py-3 px-3 text-center font-black border-r border-black/10 text-emerald-700 bg-emerald-50/40">
                                {{ $m['effective_weeks'] }}
                            </td>
                            <td class="py-3 px-3 text-center font-black border-r border-black/10 text-rose-700 bg-rose-50/40">
                                {{ $m['non_effective_weeks'] }}
                            </td>
                            <td class="py-3 px-4">
                                @if(!empty($m['reasons']))
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($m['reasons'] as $r)
                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold bg-[#FFE3E3] text-rose-900 px-2 py-0.5 rounded border border-rose-400">
                                                <span>📌</span> {{ $r }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">- KBM Berjalan Penuh -</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-[#FFF9DB] font-heading font-black text-black border-t-2 border-black text-xs">
                        <td colspan="2" class="py-3 px-4 text-center uppercase tracking-wider border-r border-black">
                            Jumlah Total
                        </td>
                        <td class="py-3 px-3 text-center border-r border-black text-sm">
                            {{ $analysis['total_weeks'] }}
                        </td>
                        <td class="py-3 px-3 text-center border-r border-black text-emerald-800 text-sm bg-[#D3F9D8]">
                            {{ $analysis['total_effective_weeks'] }}
                        </td>
                        <td class="py-3 px-3 text-center border-r border-black text-rose-800 text-sm bg-[#FFE3E3]">
                            {{ $analysis['total_non_effective_weeks'] }}
                        </td>
                        <td class="py-3 px-4 text-[11px] text-slate-600 font-sans font-bold">
                            Total Minggu Kalender = Minggu Efektif + Minggu Tidak Efektif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Rincian Distribusi Alokasi Jam Pelajaran -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="bg-white neo-box p-5 border-2 border-black shadow-[4px_4px_0px_0px_#000] space-y-4">
            <div class="flex items-center gap-2 border-b-2 border-black pb-2.5">
                <span class="text-lg">⏱️</span>
                <h4 class="font-heading font-black text-sm uppercase text-slate-900">
                    Distribusi Alokasi Waktu KBM
                </h4>
            </div>

            <div class="space-y-2.5 text-xs font-semibold text-slate-800">
                <div class="flex items-center justify-between p-2.5 bg-slate-50 border border-black rounded">
                    <span>A. Jumlah Minggu Efektif KBM</span>
                    <span class="font-black font-heading text-sm text-emerald-700">{{ $analysis['total_effective_weeks'] }} Minggu</span>
                </div>

                <div class="flex items-center justify-between p-2.5 bg-slate-50 border border-black rounded">
                    <span>B. Jam Pelajaran per Minggu (JP)</span>
                    <span class="font-black font-heading text-sm text-slate-900">{{ $params['jp_per_week'] }} JP</span>
                </div>

                <div class="flex items-center justify-between p-2.5 bg-[#FFF4E6] border border-black rounded">
                    <span class="font-black text-amber-950">C. Total Jam Tatap Muka (A &times; B)</span>
                    <span class="font-black font-heading text-base text-amber-900">{{ $hours['teaching_hours'] }} JP</span>
                </div>
            </div>

            <p class="text-[11px] text-slate-500 italic">
                * Jam tatap muka dihitung berdasarkan alokasi kurikulum madrasah per rombel yang diajar.
            </p>
        </div>

        <div class="bg-white neo-box p-5 border-2 border-black shadow-[4px_4px_0px_0px_#000] space-y-4">
            <div class="flex items-center gap-2 border-b-2 border-black pb-2.5">
                <span class="text-lg">🎯</span>
                <h4 class="font-heading font-black text-sm uppercase text-slate-900">
                    Alokasi Jam Pembelajaran Efektif
                </h4>
            </div>

            <div class="space-y-2.5 text-xs font-semibold text-slate-800">
                <div class="flex items-center justify-between p-2.5 bg-[#D3F9D8] border border-black rounded">
                    <div class="flex flex-col">
                        <span class="font-black text-emerald-950">1. Alokasi Jam Materi / KD Pokok</span>
                        <span class="text-[10px] text-emerald-800 font-normal">Total Jam Tatap Muka - Ujian - Cadangan</span>
                    </div>
                    <span class="font-black font-heading text-lg text-emerald-900">{{ $hours['material_hours'] }} JP</span>
                </div>

                <div class="flex items-center justify-between p-2.5 bg-slate-50 border border-black rounded">
                    <span>2. Alokasi Penilaian / Asesmen Sumatif</span>
                    <span class="font-black font-heading text-sm text-slate-900">{{ $hours['exam_hours'] }} JP</span>
                </div>

                <div class="flex items-center justify-between p-2.5 bg-slate-50 border border-black rounded">
                    <span>3. Alokasi Jam Cadangan</span>
                    <span class="font-black font-heading text-sm text-slate-900">{{ $hours['reserve_hours'] }} JP</span>
                </div>
            </div>

            <div class="p-2.5 bg-blue-50 border border-blue-300 rounded text-[11px] text-blue-900 font-bold flex items-center justify-between">
                <span>Total Jam Alokasi Keseluruhan:</span>
                <span class="font-heading font-black">{{ $hours['material_hours'] + $hours['exam_hours'] + $hours['reserve_hours'] }} JP</span>
            </div>
        </div>
    </div>

    <!-- Accordion / Collapsible Rincian Minggu per Minggu -->
    <details class="group bg-white neo-box border-2 border-black shadow-[4px_4px_0px_0px_#000] overflow-hidden">
        <summary class="p-4 bg-slate-100 border-b-2 border-black cursor-pointer font-heading font-black text-xs uppercase flex items-center justify-between hover:bg-slate-200 select-none">
            <div class="flex items-center gap-2 text-slate-900">
                <span>🔍</span>
                <span>Lihat Detail Audit Kalender Per Pekan ({{ count($analysis['weeks']) }} Pekan)</span>
            </div>
            <span class="transition group-open:rotate-180 text-sm">▼</span>
        </summary>

        <div class="p-4 overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="bg-slate-800 text-white font-heading font-bold text-[11px] uppercase">
                        <th class="py-2 px-3 text-center border border-slate-700 w-12">Pekan</th>
                        <th class="py-2 px-4 border border-slate-700">Rentang Tanggal</th>
                        <th class="py-2 px-3 text-center border border-slate-700">Bulan Induk</th>
                        <th class="py-2 px-3 text-center border border-slate-700">Hari Aktif</th>
                        <th class="py-2 px-3 text-center border border-slate-700">Status</th>
                        <th class="py-2 px-4 border border-slate-700">Keterangan / Agenda</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/10 font-medium">
                    @foreach($analysis['weeks'] as $w)
                        <tr class="hover:bg-slate-50 {{ ! $w['is_effective'] ? 'bg-rose-50/50' : '' }}">
                            <td class="py-2 px-3 text-center font-bold border border-black/10">{{ $w['week_number'] }}</td>
                            <td class="py-2 px-4 font-mono font-bold border border-black/10 text-slate-900">
                                {{ \Carbon\Carbon::parse($w['start_date'])->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($w['end_date'])->translatedFormat('d M Y') }}
                            </td>
                            <td class="py-2 px-3 text-center font-bold border border-black/10 text-slate-700">{{ $w['month_name'] }}</td>
                            <td class="py-2 px-3 text-center font-black border border-black/10 text-slate-900">
                                {{ $w['effective_days'] }} / {{ $params['school_days'] }}
                            </td>
                            <td class="py-2 px-3 text-center border border-black/10">
                                @if($w['is_effective'])
                                    <span class="inline-block px-2 py-0.5 font-black text-[10px] bg-[#D3F9D8] text-green-900 border border-green-600 rounded">
                                        EFEKTIF
                                    </span>
                                @else
                                    <span class="inline-block px-2 py-0.5 font-black text-[10px] bg-[#FFE3E3] text-red-900 border border-red-600 rounded">
                                        TIDAK EFEKTIF
                                    </span>
                                @endif
                            </td>
                            <td class="py-2 px-4 border border-black/10 text-slate-700 text-[11px]">
                                {{ $w['reason'] ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </details>
</div>
