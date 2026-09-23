<!-- TAB 2: Pengajuan Izin / Sakit Khusus Siswa Kelas Binaan -->
<div id="tabContent-izin" class="tab-pane hidden space-y-4">
    <div class="bg-white neo-box overflow-hidden">
        <div class="p-4 bg-slate-50 border-b-2 border-black flex items-center justify-between">
            <div>
                <h4 class="font-heading font-black text-sm text-black uppercase">Permohonan Izin / Sakit Siswa Kelas Binaan</h4>
                <p class="text-[11px] font-semibold text-slate-500">Tinjau dan verifikasi pengajuan ketidakhadiran dari siswa atau wali murid.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 border-b-2 border-black text-xs font-black uppercase text-black">
                    <tr>
                        <th class="p-3 w-12 text-center border-r-2 border-black">No</th>
                        <th class="p-3 border-r-2 border-black">Siswa</th>
                        <th class="p-3 text-center border-r-2 border-black w-24">Jenis</th>
                        <th class="p-3 border-r-2 border-black min-w-[180px]">Rentang Tanggal</th>
                        <th class="p-3 border-r-2 border-black min-w-[200px]">Alasan & Bukti</th>
                        <th class="p-3 text-center border-r-2 border-black w-28">Status</th>
                        <th class="p-3 text-center min-w-[140px]">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black">
                    @forelse($leaveRequests as $idx => $req)
                        <tr class="hover:bg-slate-50">
                            <td class="p-3 text-center font-mono font-bold text-xs border-r-2 border-black">
                                {{ $idx + 1 }}
                            </td>
                            <td class="p-3 border-r-2 border-black">
                                <div class="font-bold text-black text-xs">{{ $req->student->user->name ?? '-' }}</div>
                                <div class="text-[10px] font-mono text-slate-500">NIS: {{ $req->student->nis ?? '-' }}</div>
                            </td>
                            <td class="p-3 text-center border-r-2 border-black">
                                <span class="neo-badge text-[10px] {{ $req->type === 'izin' ? 'bg-[#868E96] text-white' : 'bg-[#FFD43B] text-black' }}">
                                    {{ strtoupper($req->type) }}
                                </span>
                            </td>
                            <td class="p-3 border-r-2 border-black text-xs font-mono">
                                <div class="font-bold">{{ \Carbon\Carbon::parse($req->date_from)->translatedFormat('d M Y') }}</div>
                                <div class="text-slate-500">s/d {{ \Carbon\Carbon::parse($req->date_to)->translatedFormat('d M Y') }}</div>
                            </td>
                            <td class="p-3 border-r-2 border-black text-xs">
                                <div class="font-medium text-slate-800">{{ $req->reason }}</div>
                                @if($req->attachment)
                                    <a href="{{ asset('storage/' . $req->attachment) }}" target="_blank" 
                                       class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-600 underline mt-1">
                                        <span>📎</span> Lihat Surat/Bukti
                                    </a>
                                @endif
                            </td>
                            <td class="p-3 text-center border-r-2 border-black">
                                <span class="neo-badge text-[10px]
                                    @if($req->status === 'approved') bg-[#20C997] text-white 
                                    @elseif($req->status === 'rejected') bg-[#FF6B6B] text-white 
                                    @else bg-amber-400 text-black @endif">
                                    {{ strtoupper($req->status) }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                @if($req->status === 'pending')
                                    <div class="flex items-center justify-center gap-1.5">
                                        <form action="{{ route('guru.leave-requests.approve', $req->id) }}" method="POST" onsubmit="return confirmAction(event, 'Setujui Pengajuan?', 'Presensi siswa akan otomatis dicatat sebagai {{ $req->type }} pada tanggal tersebut.')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="neo-btn bg-[#20C997] text-white text-[11px] font-bold px-2.5 py-1">
                                                ✓ Setujui
                                            </button>
                                        </form>
                                        <form action="{{ route('guru.leave-requests.reject', $req->id) }}" method="POST" onsubmit="return confirmAction(event, 'Tolak Pengajuan?', 'Pengajuan izin/sakit ini akan ditandai ditolak.')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="neo-btn bg-[#FF6B6B] text-white text-[11px] font-bold px-2.5 py-1">
                                                ✕ Tolak
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 font-semibold italic">Selesai Diverifikasi</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-500 font-semibold">
                                Belum ada data permohonan izin atau sakit untuk siswa di kelas binaan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
