<!-- TAB 2: JURNAL LOG HARIAN -->
<div id="tab-panel-logs" class="{{ $activeTab === 'logs' ? '' : 'hidden' }}">
    <div class="bg-white border-2 border-black rounded-lg shadow-[4px_4px_0px_0px_#000] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-slate-100 border-b-2 border-black uppercase font-black text-black">
                    <tr>
                        <th class="p-3 text-center border-r-2 border-black w-10">No</th>
                        <th class="p-3 text-center border-r-2 border-black w-24">Tanggal</th>
                        <th class="p-3 text-center border-r-2 border-black w-24">NIS</th>
                        <th class="p-3 border-r-2 border-black">Nama Siswa</th>
                        <th class="p-3 text-center border-r-2 border-black w-24">Kelas</th>
                        <th class="p-3 text-center border-r-2 border-black w-24">Waktu Masuk</th>
                        <th class="p-3 text-center border-r-2 border-black w-20">Metode</th>
                        <th class="p-3 text-center border-r-2 border-black w-24">Status</th>
                        <th class="p-3">Catatan / Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black font-medium">
                    @forelse($attendanceLogs as $index => $log)
                        <tr class="hover:bg-slate-50">
                            <td class="p-3 text-center font-mono font-bold border-r-2 border-black">
                                {{ $attendanceLogs->firstItem() + $index }}
                            </td>
                            <td class="p-3 text-center font-mono font-bold border-r-2 border-black">
                                {{ \Carbon\Carbon::parse($log->date)->translatedFormat('d/m/Y') }}
                            </td>
                            <td class="p-3 text-center font-mono font-bold border-r-2 border-black">
                                {{ $log->student->nis ?? '-' }}
                            </td>
                            <td class="p-3 font-bold text-black border-r-2 border-black">
                                {{ $log->student->user->name ?? '-' }}
                            </td>
                            <td class="p-3 text-center font-semibold text-slate-700 border-r-2 border-black">
                                {{ $log->student->schoolClass->name ?? '-' }}
                            </td>
                            <td class="p-3 text-center font-mono font-bold border-r-2 border-black">
                                @if($log->check_in_time)
                                    <span class="bg-slate-100 px-1.5 py-0.5 border border-slate-300 rounded">
                                        {{ \Carbon\Carbon::parse($log->check_in_time)->format('H:i') }}
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="p-3 text-center border-r-2 border-black">
                                <span class="neo-badge bg-white text-black text-[9px] border border-black">
                                    {{ strtoupper($log->method ?? 'manual') }}
                                </span>
                            </td>
                            <td class="p-3 text-center border-r-2 border-black">
                                <span class="neo-badge text-[10px]
                                    @if($log->status === 'hadir') bg-[#20C997] text-white 
                                    @elseif($log->status === 'terlambat') bg-[#FFD43B] text-black 
                                    @elseif($log->status === 'izin') bg-[#74C0FC] text-blue-950 
                                    @elseif($log->status === 'sakit') bg-[#A5D8FF] text-blue-950 
                                    @else bg-[#FF6B6B] text-white @endif">
                                    {{ strtoupper($log->status ?? '-') }}
                                </span>
                            </td>
                            <td class="p-3 text-slate-700 italic">
                                {{ $log->notes ?: '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-slate-500 font-semibold">
                                Tidak ada log presensi ditemukan untuk periode dan filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Log -->
        @if($attendanceLogs->hasPages())
            <div class="p-4 border-t-2 border-black bg-slate-50">
                {{ $attendanceLogs->appends(request()->except('log_page'))->links() }}
            </div>
        @endif
    </div>
</div>
