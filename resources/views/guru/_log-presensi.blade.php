<!-- Log Presensi Terbaru -->
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h2 class="font-heading font-black text-xl text-black flex items-center gap-2">
            <span>⚡</span> Aktivitas Presensi Terbaru
        </h2>
        <span class="text-xs font-bold text-slate-500">10 Presensi Terakhir</span>
    </div>

    <div class="bg-white neo-box p-5">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 border-b-2 border-black text-xs font-black uppercase">
                    <tr>
                        <th class="p-2.5 border-r border-black">Siswa</th>
                        <th class="p-2.5 border-r border-black">Kelas</th>
                        <th class="p-2.5 border-r border-black text-center">Waktu</th>
                        <th class="p-2.5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black">
                    @forelse($recentAttendances as $att)
                    <tr class="hover:bg-slate-50 font-medium text-xs">
                        <td class="p-2.5 font-bold text-black border-r border-black">
                            <div>{{ $att->student->user->name ?? '-' }}</div>
                            <div class="text-[10px] text-slate-500 font-mono">{{ $att->student->nis ?? '' }}</div>
                        </td>
                        <td class="p-2.5 border-r border-black font-semibold text-slate-700">
                            {{ $att->student->schoolClass->name ?? '-' }}
                        </td>
                        <td class="p-2.5 border-r border-black text-center font-mono font-bold">
                            @if($att->check_in_time)
                                <span class="bg-slate-100 px-1.5 py-0.5 border border-slate-300 rounded">
                                    {{ \Carbon\Carbon::parse($att->check_in_time)->format('H:i') }}
                                </span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="p-2.5 text-center">
                            <span class="neo-badge text-[10px]
                                @if($att->status === 'hadir') bg-[#20C997] text-white 
                                @elseif($att->status === 'terlambat') bg-[#FFD43B] text-black 
                                @elseif($att->status === 'sakit') bg-[#74C0FC] text-blue-900 
                                @elseif($att->status === 'izin') bg-[#A5D8FF] text-blue-900 
                                @else bg-[#FF6B6B] text-white @endif">
                                {{ strtoupper($att->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-6 text-center text-slate-500 font-semibold">
                            Belum ada aktivitas presensi yang tercatat hari ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
