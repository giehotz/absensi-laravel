{{-- TAB 3: Rekap Kehadiran Bulan Berjalan --}}
<div id="tabContent-rekap-bulan" class="tab-pane hidden space-y-4">
    <div class="bg-white neo-box overflow-hidden">
        <div class="p-4 bg-slate-50 border-b-2 border-black flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div>
                <h4 class="font-heading font-black text-sm text-black uppercase flex items-center gap-2">
                    <span>📊</span> Akumulasi Kehadiran Siswa Bulan {{ \Carbon\Carbon::today()->translatedFormat('F Y') }}
                </h4>
                <p class="text-[11px] font-semibold text-slate-500">Rangkuman total status presensi siswa sepanjang bulan berjalan.</p>
            </div>
            <a href="{{ route('guru.reports.attendance', ['school_class_id' => $selectedClass->id]) }}" 
               class="neo-btn bg-black hover:bg-slate-800 text-white text-xs font-bold px-3.5 py-2 flex items-center gap-1.5 self-stretch sm:self-auto justify-center min-h-[42px] transition-colors">
                <span>📑</span> Buka Laporan Penuh →
            </a>
        </div>

        {{-- Desktop Table View (>= 768px) --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 border-b-2 border-black text-xs font-black uppercase text-black">
                    <tr>
                        <th class="p-3 w-12 text-center border-r-2 border-black">No</th>
                        <th class="p-3 border-r-2 border-black min-w-[200px]">Siswa</th>
                        <th class="p-3 text-center border-r-2 border-black w-20 text-emerald-800">Hadir</th>
                        <th class="p-3 text-center border-r-2 border-black w-20 text-blue-800">Terlambat</th>
                        <th class="p-3 text-center border-r-2 border-black w-20 text-slate-700">Izin</th>
                        <th class="p-3 text-center border-r-2 border-black w-20 text-amber-800">Sakit</th>
                        <th class="p-3 text-center border-r-2 border-black w-20 text-rose-800">Alpa</th>
                        <th class="p-3 text-center border-r-2 border-black w-24">Total Logs</th>
                        <th class="p-3 text-center w-40">Tingkat Kehadiran</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black">
                    @foreach($students as $idx => $st)
                        @php
                            $present = $st->month_hadir + $st->month_terlambat;
                            $rate = $st->month_total > 0 ? round(($present / $st->month_total) * 100, 1) : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors font-mono text-xs">
                            <td class="p-3 text-center font-bold border-r-2 border-black bg-slate-50/50">{{ $idx + 1 }}</td>
                            <td class="p-3 border-r-2 border-black font-sans">
                                <div class="font-bold text-black">{{ $st->user->name ?? '-' }}</div>
                                <div class="text-[10px] text-slate-500 font-mono">NIS: {{ $st->nis }}</div>
                            </td>
                            <td class="p-3 text-center border-r-2 border-black font-bold text-emerald-700 bg-emerald-50/30">{{ $st->month_hadir }}</td>
                            <td class="p-3 text-center border-r-2 border-black font-bold text-blue-700 bg-blue-50/30">{{ $st->month_terlambat }}</td>
                            <td class="p-3 text-center border-r-2 border-black font-bold text-slate-700 bg-slate-50/30">{{ $st->month_izin }}</td>
                            <td class="p-3 text-center border-r-2 border-black font-bold text-amber-700 bg-amber-50/30">{{ $st->month_sakit }}</td>
                            <td class="p-3 text-center border-r-2 border-black font-bold text-rose-700 bg-rose-50/30">{{ $st->month_alpa }}</td>
                            <td class="p-3 text-center border-r-2 border-black font-bold text-black">{{ $st->month_total }}</td>
                            <td class="p-3 text-center font-sans">
                                <div class="flex items-center justify-center gap-2">
                                    <span class="font-mono font-black text-xs {{ $rate >= 85 ? 'text-emerald-700' : ($rate >= 70 ? 'text-amber-700' : 'text-rose-700') }}">
                                        {{ $rate }}%
                                    </span>
                                    <span class="neo-badge text-[9px] font-black px-1.5 py-0.5
                                        {{ $rate >= 85 ? 'bg-[#D3F9D8] text-emerald-950 border-emerald-900' : ($rate >= 70 ? 'bg-[#FFF3BF] text-amber-950 border-amber-900' : 'bg-[#FFE3E3] text-rose-950 border-rose-900') }}">
                                        {{ $rate >= 85 ? 'Baik' : ($rate >= 70 ? 'Cukup' : 'Perhatian') }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile Adaptive Cards (< 768px) --}}
        <div class="block md:hidden divide-y-2 divide-black">
            @forelse($students as $idx => $st)
                @php
                    $present = $st->month_hadir + $st->month_terlambat;
                    $rate = $st->month_total > 0 ? round(($present / $st->month_total) * 100, 1) : 0;
                @endphp
                <div class="p-4 space-y-3 bg-white hover:bg-slate-50/50 transition-colors">
                    {{-- Header Card --}}
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-6 h-6 rounded-full bg-black text-white font-mono text-[10px] font-black flex items-center justify-center shrink-0">
                                {{ $idx + 1 }}
                            </span>
                            <div class="min-w-0">
                                <h5 class="font-heading font-black text-xs text-black truncate">{{ $st->user->name ?? '-' }}</h5>
                                <p class="text-[10px] font-mono text-slate-500">NIS: {{ $st->nis }}</p>
                            </div>
                        </div>
                        <div class="shrink-0 flex items-center gap-1.5">
                            <span class="font-mono font-black text-xs {{ $rate >= 85 ? 'text-emerald-700' : ($rate >= 70 ? 'text-amber-700' : 'text-rose-700') }}">
                                {{ $rate }}%
                            </span>
                            <span class="neo-badge text-[9px] font-black px-1.5 py-0.5
                                {{ $rate >= 85 ? 'bg-[#D3F9D8] text-emerald-950 border-emerald-900' : ($rate >= 70 ? 'bg-[#FFF3BF] text-amber-950 border-amber-900' : 'bg-[#FFE3E3] text-rose-950 border-rose-900') }}">
                                {{ $rate >= 85 ? 'Baik' : ($rate >= 70 ? 'Cukup' : 'Perhatian') }}
                            </span>
                        </div>
                    </div>

                    {{-- 5 Status Breakdown Grid --}}
                    <div class="grid grid-cols-5 gap-1.5 text-center text-xs font-mono font-bold">
                        <div class="p-1.5 bg-[#D3F9D8] border border-black rounded-xs">
                            <div class="text-[9px] font-sans font-bold text-emerald-900">Hadir</div>
                            <div class="text-xs font-black text-emerald-950">{{ $st->month_hadir }}</div>
                        </div>
                        <div class="p-1.5 bg-[#FFF3BF] border border-black rounded-xs">
                            <div class="text-[9px] font-sans font-bold text-amber-900">Telat</div>
                            <div class="text-xs font-black text-amber-950">{{ $st->month_terlambat }}</div>
                        </div>
                        <div class="p-1.5 bg-[#E7F5FF] border border-black rounded-xs">
                            <div class="text-[9px] font-sans font-bold text-blue-900">Izin</div>
                            <div class="text-xs font-black text-blue-950">{{ $st->month_izin }}</div>
                        </div>
                        <div class="p-1.5 bg-[#FFE8CC] border border-black rounded-xs">
                            <div class="text-[9px] font-sans font-bold text-orange-900">Sakit</div>
                            <div class="text-xs font-black text-orange-950">{{ $st->month_sakit }}</div>
                        </div>
                        <div class="p-1.5 bg-[#FFE3E3] border border-black rounded-xs">
                            <div class="text-[9px] font-sans font-bold text-rose-900">Alpa</div>
                            <div class="text-xs font-black text-rose-950">{{ $st->month_alpa }}</div>
                        </div>
                    </div>

                    {{-- Progress Bar Kehadiran --}}
                    <div class="space-y-1 pt-1">
                        <div class="flex justify-between items-center text-[10px] font-semibold text-slate-500">
                            <span>Akumulasi Kehadiran:</span>
                            <span class="font-mono font-bold text-slate-700">{{ $present }} / {{ $st->month_total }} Pertemuan</span>
                        </div>
                        <div class="h-2 w-full bg-slate-100 border border-black rounded-full overflow-hidden">
                            <div class="h-full {{ $rate >= 85 ? 'bg-[#20C997]' : ($rate >= 70 ? 'bg-[#FFD43B]' : 'bg-[#FF6B6B]') }}"
                                 style="width: {{ min(100, $rate) }}%"></div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-500 font-semibold">
                    <div class="text-2xl mb-1">📊</div>
                    <div class="text-xs">Tidak ada data siswa untuk kelas ini.</div>
                </div>
            @endforelse
        </div>
    </div>
</div>
