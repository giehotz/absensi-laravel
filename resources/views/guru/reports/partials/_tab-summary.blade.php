<!-- TAB 1: REKAPITULASI PER SISWA -->
<div id="tab-panel-summary" class="{{ $activeTab === 'summary' ? '' : 'hidden' }}">
    <div class="bg-white border-2 border-black rounded-lg shadow-[4px_4px_0px_0px_#000] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-slate-100 border-b-2 border-black uppercase font-black text-black">
                    <tr>
                        <th class="p-3 text-center border-r-2 border-black w-10">No</th>
                        <th class="p-3 text-center border-r-2 border-black w-24">NIS</th>
                        <th class="p-3 border-r-2 border-black">Nama Siswa</th>
                        <th class="p-3 border-r-2 border-black text-center w-28">Kelas</th>
                        <th class="p-3 text-center border-r-2 border-black w-14 bg-[#D3F9D8] text-emerald-950">Hadir</th>
                        <th class="p-3 text-center border-r-2 border-black w-14 bg-[#FFF3BF] text-amber-950">Telat</th>
                        <th class="p-3 text-center border-r-2 border-black w-14 bg-[#D0EBFF] text-blue-950">Izin</th>
                        <th class="p-3 text-center border-r-2 border-black w-14 bg-[#F3D9FA] text-purple-950">Sakit</th>
                        <th class="p-3 text-center border-r-2 border-black w-14 bg-[#FFE3E3] text-rose-950">Alpa</th>
                        <th class="p-3 text-center border-r-2 border-black w-16">Total</th>
                        <th class="p-3 text-center border-r-2 border-black w-24">% Kehadiran</th>
                        <th class="p-3 text-center w-24">Predikat</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black font-medium">
                    @forelse($students as $index => $stu)
                        @php
                            $totalPresent = $stu->count_hadir + $stu->count_terlambat;
                            $rate = $stu->count_total > 0 
                                ? round(($totalPresent / $stu->count_total) * 100, 1) 
                                : 0;

                            $predikatBadge = 'bg-[#20C997] text-white';
                            $predikatText = 'Sangat Baik';
                            if ($rate < 75) {
                                $predikatBadge = 'bg-[#FF6B6B] text-white';
                                $predikatText = 'Perhatian';
                            } elseif ($rate < 85) {
                                $predikatBadge = 'bg-[#FFD43B] text-black';
                                $predikatText = 'Cukup';
                            } elseif ($rate < 95) {
                                $predikatBadge = 'bg-[#5294FF] text-white';
                                $predikatText = 'Baik';
                            }
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="p-3 text-center font-mono font-bold border-r-2 border-black">
                                {{ $students->firstItem() + $index }}
                            </td>
                            <td class="p-3 text-center font-mono font-bold border-r-2 border-black">
                                {{ $stu->nis ?? '-' }}
                            </td>
                            <td class="p-3 font-bold text-black border-r-2 border-black">
                                <div>{{ $stu->user->name ?? '-' }}</div>
                                <div class="text-[10px] font-normal text-slate-500 font-mono">NISN: {{ $stu->nisn ?? '-' }}</div>
                            </td>
                            <td class="p-3 text-center font-semibold text-slate-700 border-r-2 border-black">
                                {{ $stu->schoolClass->name ?? '-' }}
                            </td>
                            <td class="p-3 text-center font-mono font-bold border-r-2 border-black bg-emerald-50/50 text-emerald-950">
                                {{ $stu->count_hadir }}
                            </td>
                            <td class="p-3 text-center font-mono font-bold border-r-2 border-black bg-amber-50/50 text-amber-950">
                                {{ $stu->count_terlambat }}
                            </td>
                            <td class="p-3 text-center font-mono font-bold border-r-2 border-black bg-blue-50/50 text-blue-950">
                                {{ $stu->count_izin }}
                            </td>
                            <td class="p-3 text-center font-mono font-bold border-r-2 border-black bg-purple-50/50 text-purple-950">
                                {{ $stu->count_sakit }}
                            </td>
                            <td class="p-3 text-center font-mono font-bold border-r-2 border-black bg-rose-50/50 text-rose-950">
                                {{ $stu->count_alpa }}
                            </td>
                            <td class="p-3 text-center font-mono font-bold border-r-2 border-black bg-slate-50">
                                {{ $stu->count_total }}
                            </td>
                            <td class="p-3 text-center font-mono font-black border-r-2 border-black">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span>{{ $rate }}%</span>
                                </div>
                            </td>
                            <td class="p-3 text-center">
                                <span class="neo-badge {{ $predikatBadge }} text-[10px]">
                                    {{ $predikatText }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="p-8 text-center text-slate-500 font-semibold">
                                Tidak ada data siswa ditemukan untuk kriteria filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Siswa -->
        @if($students->hasPages())
            <div class="p-4 border-t-2 border-black bg-slate-50">
                {{ $students->appends(request()->except('student_page'))->links() }}
            </div>
        @endif
    </div>
</div>
