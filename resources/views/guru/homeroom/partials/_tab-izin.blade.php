<!-- TAB 2: Pengajuan Izin / Sakit Khusus Siswa Kelas Binaan -->
<div id="tabContent-izin" class="tab-pane hidden space-y-4">
    <div class="bg-white neo-box overflow-hidden">
        <div class="p-4 bg-slate-50 border-b-2 border-black flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h4 class="font-heading font-black text-sm text-black uppercase flex items-center gap-2">
                    <span>📝</span> Permohonan Izin / Sakit Siswa
                </h4>
                <p class="text-[11px] font-semibold text-slate-500">Tinjau dan verifikasi pengajuan ketidakhadiran dari siswa atau wali murid.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="neo-badge bg-[#E7F5FF] text-blue-900 text-[11px] font-black border-2 border-black px-2.5 py-1">
                    {{ $leaveRequests->count() }} Pengajuan Total
                </span>
            </div>
        </div>

        {{-- Desktop Table View (>= 768px) --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 border-b-2 border-black text-xs font-black uppercase text-black">
                    <tr>
                        <th class="p-3 w-12 text-center border-r-2 border-black">No</th>
                        <th class="p-3 border-r-2 border-black min-w-[180px]">Siswa</th>
                        <th class="p-3 text-center border-r-2 border-black w-24">Jenis</th>
                        <th class="p-3 border-r-2 border-black min-w-[180px]">Rentang Tanggal</th>
                        <th class="p-3 border-r-2 border-black min-w-[220px]">Alasan & Bukti</th>
                        <th class="p-3 text-center border-r-2 border-black w-32">Status</th>
                        <th class="p-3 text-center min-w-[160px]">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black">
                    @forelse($leaveRequests as $idx => $req)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-3 text-center font-mono font-bold text-xs border-r-2 border-black bg-slate-50/50">
                                {{ $idx + 1 }}
                            </td>
                            <td class="p-3 border-r-2 border-black">
                                <div class="font-bold text-black text-xs">{{ $req->student->user->name ?? '-' }}</div>
                                <div class="text-[10px] font-mono text-slate-500">NIS: {{ $req->student->nis ?? '-' }}</div>
                            </td>
                            <td class="p-3 text-center border-r-2 border-black">
                                <span class="neo-badge text-[10px] font-black px-2.5 py-0.5 {{ $req->type === 'izin' ? 'bg-[#E7F5FF] text-blue-900 border-blue-900' : 'bg-[#FFF3BF] text-amber-950 border-amber-900' }}">
                                    {{ strtoupper($req->type) }}
                                </span>
                            </td>
                            <td class="p-3 border-r-2 border-black text-xs font-mono">
                                <div class="font-bold text-black">{{ \Carbon\Carbon::parse($req->date_from)->translatedFormat('d M Y') }}</div>
                                <div class="text-slate-500 text-[11px]">s/d {{ \Carbon\Carbon::parse($req->date_to)->translatedFormat('d M Y') }}</div>
                            </td>
                            <td class="p-3 border-r-2 border-black text-xs">
                                <div class="font-medium text-slate-800">{{ $req->reason }}</div>
                                @if($req->attachment)
                                    <a href="{{ asset('storage/' . $req->attachment) }}" target="_blank" 
                                       class="inline-flex items-center gap-1.5 text-[11px] font-bold text-blue-700 hover:text-blue-900 underline mt-1.5 bg-blue-50 px-2 py-0.5 border border-blue-300 rounded-xs">
                                        <span>📎</span> Lihat Surat/Bukti
                                    </a>
                                @endif
                            </td>
                            <td class="p-3 text-center border-r-2 border-black">
                                <span class="neo-badge text-[10px] font-black px-2.5 py-0.5
                                    @if($req->status === 'approved') bg-[#D3F9D8] text-emerald-950 border-emerald-900 
                                    @elseif($req->status === 'rejected') bg-[#FFE3E3] text-rose-950 border-rose-900 
                                    @else bg-[#FFF3BF] text-amber-950 border-amber-900 @endif">
                                    {{ strtoupper($req->status) }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                @if($req->status === 'pending')
                                    <div class="flex items-center justify-center gap-1.5">
                                        <form action="{{ route('guru.leave-requests.approve', $req->id) }}" method="POST" onsubmit="return confirmAction(event, 'Setujui Pengajuan?', 'Presensi siswa akan otomatis dicatat sebagai {{ $req->type }} pada rentang tanggal tersebut.')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="neo-btn bg-[#20C997] hover:bg-emerald-400 text-black text-[11px] font-black px-2.5 py-1.5 flex items-center gap-1 cursor-pointer">
                                                ✓ Setujui
                                            </button>
                                        </form>
                                        <form action="{{ route('guru.leave-requests.reject', $req->id) }}" method="POST" onsubmit="return confirmAction(event, 'Tolak Pengajuan?', 'Pengajuan izin/sakit ini akan ditandai ditolak.')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="neo-btn bg-[#FF6B6B] hover:bg-rose-500 text-white text-[11px] font-bold px-2.5 py-1.5 flex items-center gap-1 cursor-pointer">
                                                ✕ Tolak
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 font-semibold italic">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-500 font-semibold">
                                <div class="text-2xl mb-1">📭</div>
                                <div>Belum ada data permohonan izin atau sakit untuk siswa di kelas binaan ini.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Adaptive Cards (< 768px) --}}
        <div class="block md:hidden divide-y-2 divide-black">
            @forelse($leaveRequests as $idx => $req)
                <div class="p-4 space-y-3 bg-white hover:bg-slate-50/50 transition-colors">
                    {{-- Header Card --}}
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-black text-white font-mono text-[10px] font-black flex items-center justify-center shrink-0">
                                {{ $idx + 1 }}
                            </span>
                            <div>
                                <h5 class="font-heading font-black text-xs text-black">{{ $req->student->user->name ?? '-' }}</h5>
                                <p class="text-[10px] font-mono text-slate-500">NIS: {{ $req->student->nis ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <span class="neo-badge text-[10px] font-black px-2 py-0.5 {{ $req->type === 'izin' ? 'bg-[#E7F5FF] text-blue-900 border-blue-900' : 'bg-[#FFF3BF] text-amber-950 border-amber-900' }}">
                                {{ strtoupper($req->type) }}
                            </span>
                            <span class="neo-badge text-[10px] font-black px-2 py-0.5
                                @if($req->status === 'approved') bg-[#D3F9D8] text-emerald-950 border-emerald-900 
                                @elseif($req->status === 'rejected') bg-[#FFE3E3] text-rose-950 border-rose-900 
                                @else bg-[#FFF3BF] text-amber-950 border-amber-900 @endif">
                                {{ strtoupper($req->status) }}
                            </span>
                        </div>
                    </div>

                    {{-- Detail Rentang Tanggal & Alasan --}}
                    <div class="bg-slate-50 border border-black/20 p-2.5 rounded-sm space-y-1.5 text-xs">
                        <div class="flex items-center gap-1.5 font-mono text-[11px] text-slate-700">
                            <span>📅</span>
                            <span class="font-bold text-black">{{ \Carbon\Carbon::parse($req->date_from)->translatedFormat('d M Y') }}</span>
                            <span class="text-slate-400">s/d</span>
                            <span class="font-bold text-black">{{ \Carbon\Carbon::parse($req->date_to)->translatedFormat('d M Y') }}</span>
                        </div>
                        <div class="text-slate-800 font-medium text-xs leading-relaxed pt-1 border-t border-slate-200">
                            <span class="font-bold text-slate-600">Alasan:</span> {{ $req->reason }}
                        </div>
                        @if($req->attachment)
                            <div class="pt-1">
                                <a href="{{ asset('storage/' . $req->attachment) }}" target="_blank" 
                                   class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-700 hover:text-blue-900 underline bg-blue-50 px-2 py-1 border border-blue-300 rounded-xs min-h-[36px]">
                                    <span>📎</span> Buka Lampiran Surat / Bukti
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- Actions on Mobile --}}
                    @if($req->status === 'pending')
                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <form action="{{ route('guru.leave-requests.approve', $req->id) }}" method="POST" onsubmit="return confirmAction(event, 'Setujui Pengajuan?', 'Presensi siswa akan otomatis dicatat sebagai {{ $req->type }} pada rentang tanggal tersebut.')">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full neo-btn bg-[#20C997] hover:bg-emerald-400 text-black text-xs font-black min-h-[44px] flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span>✓</span> Setujui
                                </button>
                            </form>
                            <form action="{{ route('guru.leave-requests.reject', $req->id) }}" method="POST" onsubmit="return confirmAction(event, 'Tolak Pengajuan?', 'Pengajuan izin/sakit ini akan ditandai ditolak.')">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full neo-btn bg-[#FF6B6B] hover:bg-rose-500 text-white text-xs font-bold min-h-[44px] flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span>✕</span> Tolak
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="text-right text-[11px] text-slate-400 font-semibold italic">
                            ✓ Telah diproses ({{ strtoupper($req->status) }})
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-8 text-center text-slate-500 font-semibold">
                    <div class="text-2xl mb-1">📭</div>
                    <div class="text-xs">Belum ada data permohonan izin atau sakit untuk siswa di kelas binaan ini.</div>
                </div>
            @endforelse
        </div>
    </div>
</div>
