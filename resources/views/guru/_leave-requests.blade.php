<!-- Permohonan Izin & Sakit (Pending) -->
<div id="leave-requests" class="space-y-4">
    <div class="flex items-center justify-between flex-wrap gap-2">
        <h2 class="font-heading font-black text-xl text-black flex items-center gap-2">
            <span>📝</span> Permohonan Izin Siswa Menunggu Verifikasi
        </h2>
        <div class="flex items-center gap-2">
            @if($pendingLeaveRequests->isNotEmpty())
                <span class="neo-badge bg-[#FF6B6B] text-white animate-pulse">
                    {{ $pendingLeaveRequests->count() }} Menunggu
                </span>
            @endif
            <a href="{{ route('guru.leave-requests.index') }}" class="neo-btn bg-white text-black text-xs font-bold px-3 py-1.5 flex items-center gap-1.5 hover:bg-slate-100">
                Buka Halaman Perizinan →
            </a>
        </div>
    </div>

    <div class="bg-white neo-box p-5 space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 border-b-2 border-black text-xs font-black uppercase">
                    <tr>
                        <th class="p-2.5 border-r border-black">Siswa & Kelas</th>
                        <th class="p-2.5 border-r border-black text-center">Jenis</th>
                        <th class="p-2.5 border-r border-black">Periode</th>
                        <th class="p-2.5 border-r border-black">Alasan / Lampiran</th>
                        <th class="p-2.5 text-center">Aksi Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black">
                    @forelse($pendingLeaveRequests as $lr)
                    <tr class="hover:bg-slate-50 font-medium text-xs">
                        <td class="p-2.5 font-bold text-black border-r border-black">
                            <div class="text-sm font-black">{{ $lr->student->user->name ?? '-' }}</div>
                            <div class="text-[11px] text-slate-500 font-normal">
                                {{ $lr->student->schoolClass->name ?? '-' }} • NIS: {{ $lr->student->nis ?? '-' }}
                            </div>
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                Diajukan oleh: {{ $lr->requester->name ?? 'Orang Tua' }}
                            </div>
                        </td>

                        <td class="p-2.5 border-r border-black text-center">
                            <span class="neo-badge text-[10px] uppercase font-bold
                                {{ $lr->type === 'sakit' ? 'bg-[#74C0FC] text-blue-900' : 'bg-[#FFF3BF] text-amber-900' }}">
                                {{ $lr->type }}
                            </span>
                        </td>

                        <td class="p-2.5 border-r border-black font-mono text-xs">
                            <div class="font-bold text-black">
                                {{ \Carbon\Carbon::parse($lr->date_from)->translatedFormat('d M Y') }}
                            </div>
                            @if($lr->date_from != $lr->date_to)
                                <div class="text-[10px] text-slate-500">
                                    s/d {{ \Carbon\Carbon::parse($lr->date_to)->translatedFormat('d M Y') }}
                                </div>
                            @endif
                        </td>

                        <td class="p-2.5 border-r border-black max-w-xs">
                            <p class="text-slate-800 italic line-clamp-2">"{{ $lr->reason }}"</p>
                            @if($lr->attachment_path)
                                <a href="{{ asset('storage/' . $lr->attachment_path) }}" target="_blank" 
                                   class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-600 hover:underline mt-1">
                                    <span>📎</span> Lihat Surat / Lampiran
                                </a>
                            @endif
                        </td>

                        <td class="p-2.5 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <!-- Tombol Setujui -->
                                <button type="button" 
                                        onclick="confirmApproveLeave('{{ $lr->id }}', '{{ addslashes($lr->student->user->name ?? 'Siswa') }}', '{{ strtoupper($lr->type) }}')" 
                                        class="neo-btn bg-[#20C997] text-black px-2.5 py-1.5 text-xs font-bold cursor-pointer hover:bg-emerald-400 transition-colors"
                                        title="Setujui permohonan">
                                    ✓ Setujui
                                </button>
                                <form id="form-approve-{{ $lr->id }}" 
                                      action="{{ route('guru.leave-requests.approve', $lr) }}" 
                                      method="POST" class="hidden">
                                    @csrf
                                    @method('PATCH')
                                </form>

                                <!-- Tombol Tolak -->
                                <button type="button" 
                                        onclick="confirmRejectLeave('{{ $lr->id }}', '{{ addslashes($lr->student->user->name ?? 'Siswa') }}', '{{ strtoupper($lr->type) }}')" 
                                        class="neo-btn bg-[#FF6B6B] text-white px-2.5 py-1.5 text-xs font-bold cursor-pointer hover:bg-rose-600 transition-colors"
                                        title="Tolak permohonan">
                                    ✕ Tolak
                                </button>
                                <form id="form-reject-{{ $lr->id }}" 
                                      action="{{ route('guru.leave-requests.reject', $lr) }}" 
                                      method="POST" class="hidden">
                                    @csrf
                                    @method('PATCH')
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-slate-500 font-semibold space-y-1">
                            <div class="text-xl">✨</div>
                            <div>Tidak ada permohonan izin/sakit yang menunggu persetujuan saat ini.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
