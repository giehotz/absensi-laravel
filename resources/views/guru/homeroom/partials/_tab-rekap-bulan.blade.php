{{-- TAB 3: Rekap Kehadiran Bulan Berjalan --}}
<div id="tabContent-rekap-bulan" class="tab-pane hidden space-y-4">
    <div class="bg-white neo-box overflow-hidden">
        <div class="p-4 bg-slate-50 border-b-2 border-black flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <div>
                <h4 class="font-heading font-black text-sm text-black uppercase">
                    Akumulasi Kehadiran Siswa Bulan {{ \Carbon\Carbon::today()->translatedFormat('F Y') }}
                </h4>
                <p class="text-[11px] font-semibold text-slate-500">Rangkuman total status presensi siswa sepanjang bulan berjalan.</p>
            </div>
            <a href="{{ route('guru.reports.attendance', ['school_class_id' => $selectedClass->id]) }}" 
               class="neo-btn bg-black text-white text-xs font-bold px-3 py-1.5 flex items-center gap-1.5">
                Buka Laporan Penuh →
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 border-b-2 border-black text-xs font-black uppercase text-black">
                    <tr>
                        <th class="p-3 w-12 text-center border-r-2 border-black">No</th>
                        <th class="p-3 border-r-2 border-black">Siswa</th>
                        <th class="p-3 text-center border-r-2 border-black w-20 text-emerald-800">Hadir</th>
                        <th class="p-3 text-center border-r-2 border-black w-20 text-blue-800">Terlambat</th>
                        <th class="p-3 text-center border-r-2 border-black w-20 text-slate-700">Izin</th>
                        <th class="p-3 text-center border-r-2 border-black w-20 text-amber-800">Sakit</th>
                        <th class="p-3 text-center border-r-2 border-black w-20 text-rose-800">Alpa</th>
                        <th class="p-3 text-center border-r-2 border-black w-24">Total Logs</th>
                        <th class="p-3 text-center w-36">Tingkat Kehadiran</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black">
                    @foreach($students as $idx => $st)
                        @php
                            $present = $st->month_hadir + $st->month_terlambat;
                            $rate = $st->month_total > 0 ? round(($present / $st->month_total) * 100, 1) : 0;
                        @endphp
                        <tr class="hover:bg-slate-50 font-mono text-xs">
                            <td class="p-3 text-center font-bold border-r-2 border-black">{{ $idx + 1 }}</td>
                            <td class="p-3 border-r-2 border-black font-sans">
                                <div class="font-bold text-black">{{ $st->user->name ?? '-' }}</div>
                                <div class="text-[10px] text-slate-500 font-mono">NIS: {{ $st->nis }}</div>
                            </td>
                            <td class="p-3 text-center border-r-2 border-black font-bold text-emerald-700">{{ $st->month_hadir }}</td>
                            <td class="p-3 text-center border-r-2 border-black font-bold text-blue-700">{{ $st->month_terlambat }}</td>
                            <td class="p-3 text-center border-r-2 border-black font-bold text-slate-700">{{ $st->month_izin }}</td>
                            <td class="p-3 text-center border-r-2 border-black font-bold text-amber-700">{{ $st->month_sakit }}</td>
                            <td class="p-3 text-center border-r-2 border-black font-bold text-rose-700">{{ $st->month_alpa }}</td>
                            <td class="p-3 text-center border-r-2 border-black font-bold text-black">{{ $st->month_total }}</td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <span class="font-bold {{ $rate >= 85 ? 'text-emerald-700' : ($rate >= 70 ? 'text-amber-700' : 'text-rose-700') }}">
                                        {{ $rate }}%
                                    </span>
                                    <span class="neo-badge text-[9px] px-1 py-0
                                        {{ $rate >= 85 ? 'bg-[#D3F9D8] text-emerald-950' : ($rate >= 70 ? 'bg-[#FFF3BF] text-amber-950' : 'bg-[#FFE3E3] text-rose-950') }}">
                                        {{ $rate >= 85 ? 'Baik' : ($rate >= 70 ? 'Cukup' : 'Perhatian') }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
