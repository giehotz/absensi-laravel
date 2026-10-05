<!-- Permohonan Izin & Sakit Pending (Adapted, Optimized, Colorized Neo-Brutalism) -->
<div id="leave-requests" class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-sm bg-black text-[#FF6B6B] flex items-center justify-center shrink-0 border border-black shadow-[1.5px_1.5px_0px_#000]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="font-heading font-black text-lg sm:text-xl text-black leading-tight">
                        Permohonan Izin Siswa
                    </h2>
                    @if($pendingLeaveRequests->isNotEmpty())
                        <span class="text-[10px] font-heading font-black uppercase px-2 py-0.5 bg-[#FF6B6B] text-white border border-black rounded-xs shadow-[1.5px_1.5px_0px_#000] animate-pulse">
                            {{ $pendingLeaveRequests->count() }} Menunggu
                        </span>
                    @endif
                </div>
                <div class="text-[11px] font-bold text-slate-500">
                    Menunggu Verifikasi & Persetujuan Guru
                </div>
            </div>
        </div>

        <a href="{{ route('guru.leave-requests.index') }}" 
           class="neo-btn bg-white hover:bg-slate-100 text-black text-xs font-heading font-black px-3.5 py-2 self-start sm:self-auto flex items-center gap-1.5 border-2 border-black shadow-[2px_2px_0px_0px_#000] transition-transform active:translate-x-0.5 active:translate-y-0.5">
            <span>Buka Modul Perizinan</span>
            <span>→</span>
        </a>
    </div>

    <div class="bg-white neo-box p-4 sm:p-5 border-2 border-black shadow-[3px_3px_0px_0px_#000]">
        @if($pendingLeaveRequests->isEmpty())
            <!-- Empty State -->
            <div class="py-8 px-4 text-center space-y-2">
                <div class="w-12 h-12 rounded-full bg-[#D3F9D8] border-2 border-black mx-auto flex items-center justify-center text-xl shadow-[2px_2px_0px_#000]">
                    ✅
                </div>
                <div class="font-heading font-black text-base text-black">
                    Semua Permohonan Terverifikasi
                </div>
                <p class="text-xs text-slate-500 max-w-md mx-auto font-medium">
                    Tidak ada pengajuan surat izin atau sakit siswa yang menunggu verifikasi dari Anda saat ini.
                </p>
            </div>
        @else
            <!-- Desktop Tabular View (hidden on mobile) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead class="bg-slate-100 border-b-2 border-black text-xs font-heading font-black uppercase text-black">
                        <tr>
                            <th class="p-3 border-r-2 border-black">Siswa & Kelas</th>
                            <th class="p-3 border-r-2 border-black text-center w-24">Jenis</th>
                            <th class="p-3 border-r-2 border-black w-36">Periode</th>
                            <th class="p-3 border-r-2 border-black">Alasan & Lampiran</th>
                            <th class="p-3 text-center w-48">Aksi Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-black">
                        @foreach($pendingLeaveRequests as $lr)
                            <tr class="hover:bg-slate-50 transition-colors text-xs">
                                <td class="p-3 border-r-2 border-black">
                                    <div class="font-heading font-black text-sm text-black">
                                        {{ $lr->student->user->name ?? '-' }}
                                    </div>
                                    <div class="text-[11px] text-slate-600 font-bold flex items-center gap-1.5 mt-0.5">
                                        <span class="bg-slate-100 border border-black/40 px-1 py-0.2 font-mono text-[10px]">
                                            {{ $lr->student->schoolClass->name ?? '-' }}
                                        </span>
                                        <span>•</span>
                                        <span>NIS: {{ $lr->student->nis ?? '-' }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-500 mt-1">
                                        Diajukan oleh: <strong class="text-slate-700">{{ $lr->requester->name ?? 'Orang Tua' }}</strong>
                                    </div>
                                </td>

                                <td class="p-3 border-r-2 border-black text-center">
                                    <span class="text-[10px] font-heading font-black uppercase px-2 py-1 rounded-xs border border-black shadow-[1px_1px_0px_#000] {{ $lr->type === 'sakit' ? 'bg-[#FFD43B] text-black' : 'bg-[#E7F5FF] text-blue-950' }}">
                                        {{ $lr->type }}
                                    </span>
                                </td>

                                <td class="p-3 border-r-2 border-black font-mono text-xs">
                                    <div class="font-bold text-black flex items-center gap-1">
                                        <span>📅</span>
                                        <span>{{ \Carbon\Carbon::parse($lr->date_from)->translatedFormat('d M Y') }}</span>
                                    </div>
                                    @if($lr->date_from != $lr->date_to)
                                        <div class="text-[10px] text-slate-500 mt-0.5 font-semibold">
                                            s/d {{ \Carbon\Carbon::parse($lr->date_to)->translatedFormat('d M Y') }}
                                        </div>
                                    @endif
                                </td>

                                <td class="p-3 border-r-2 border-black max-w-xs">
                                    <p class="text-slate-800 italic leading-relaxed">
                                        "{{ $lr->reason }}"
                                    </p>
                                    @if($lr->attachment_path)
                                        <a href="{{ asset('storage/' . $lr->attachment_path) }}" target="_blank" 
                                           class="inline-flex items-center gap-1.5 text-[11px] font-bold text-blue-700 hover:text-black bg-blue-50 border border-blue-300 px-2 py-0.5 rounded-xs mt-1.5 hover:underline">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                            </svg>
                                            <span>Lihat Berkas Lampiran</span>
                                        </a>
                                    @endif
                                </td>

                                <td class="p-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- Tombol Setujui -->
                                        <button type="button" 
                                                onclick="confirmApproveLeave('{{ $lr->id }}', '{{ addslashes($lr->student->user->name ?? 'Siswa') }}', '{{ strtoupper($lr->type) }}')" 
                                                class="neo-btn bg-[#20C997] hover:bg-emerald-400 text-black px-3 py-1.5 text-xs font-heading font-black cursor-pointer border border-black shadow-[1.5px_1.5px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 transition-transform"
                                                title="Setujui permohonan izin">
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
                                                class="neo-btn bg-[#FF6B6B] hover:bg-rose-600 text-white px-3 py-1.5 text-xs font-heading font-black cursor-pointer border border-black shadow-[1.5px_1.5px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 transition-transform"
                                                title="Tolak permohonan izin">
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
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Adaptive Card View (md:hidden) -->
            <div class="md:hidden space-y-3">
                @foreach($pendingLeaveRequests as $lr)
                    <div class="bg-slate-50 border-2 border-black rounded-lg p-3.5 space-y-3 shadow-[2px_2px_0px_#000]">
                        <!-- Card Header -->
                        <div class="flex items-start justify-between gap-2 pb-2 border-b border-black/10">
                            <div>
                                <div class="font-heading font-black text-sm text-black">
                                    {{ $lr->student->user->name ?? '-' }}
                                </div>
                                <div class="text-[11px] text-slate-600 font-bold flex items-center gap-1.5 mt-0.5">
                                    <span class="bg-white border border-black px-1.5 py-0.2 font-mono text-[10px]">
                                        {{ $lr->student->schoolClass->name ?? '-' }}
                                    </span>
                                    <span>NIS: {{ $lr->student->nis ?? '-' }}</span>
                                </div>
                            </div>
                            <span class="text-[10px] font-heading font-black uppercase px-2 py-0.5 rounded-xs border border-black shadow-[1px_1px_0px_#000] shrink-0 {{ $lr->type === 'sakit' ? 'bg-[#FFD43B] text-black' : 'bg-[#E7F5FF] text-blue-950' }}">
                                {{ $lr->type }}
                            </span>
                        </div>

                        <!-- Card Info -->
                        <div class="space-y-1.5 text-xs">
                            <div class="flex items-center gap-1.5 text-slate-700 font-mono font-bold">
                                <span>📅</span>
                                <span>{{ \Carbon\Carbon::parse($lr->date_from)->translatedFormat('d M Y') }}</span>
                                @if($lr->date_from != $lr->date_to)
                                    <span class="text-slate-400">s/d</span>
                                    <span>{{ \Carbon\Carbon::parse($lr->date_to)->translatedFormat('d M Y') }}</span>
                                @endif
                            </div>

                            <div class="p-2.5 bg-white border border-black rounded-xs text-xs italic text-slate-800">
                                "{{ $lr->reason }}"
                            </div>

                            @if($lr->attachment_path)
                                <a href="{{ asset('storage/' . $lr->attachment_path) }}" target="_blank" 
                                   class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-700 bg-blue-50 border border-blue-300 px-2 py-1 rounded-xs hover:underline">
                                    <span>📎</span> Lihat Lampiran Surat
                                </a>
                            @endif

                            <div class="text-[10px] text-slate-500 font-medium">
                                Pengaju: <span class="font-bold text-slate-700">{{ $lr->requester->name ?? 'Orang Tua' }}</span>
                            </div>
                        </div>

                        <!-- Mobile Action Buttons (Full-width dual buttons, min-h 44px) -->
                        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-black/10">
                            <button type="button" 
                                    onclick="confirmApproveLeave('{{ $lr->id }}', '{{ addslashes($lr->student->user->name ?? 'Siswa') }}', '{{ strtoupper($lr->type) }}')" 
                                    class="neo-btn bg-[#20C997] hover:bg-emerald-400 text-black py-2.5 min-h-[44px] text-xs font-heading font-black cursor-pointer flex items-center justify-center gap-1 border border-black shadow-[1.5px_1.5px_0px_#000] active:scale-95 transition-all">
                                <span>✓</span>
                                <span>Setujui</span>
                            </button>

                            <button type="button" 
                                    onclick="confirmRejectLeave('{{ $lr->id }}', '{{ addslashes($lr->student->user->name ?? 'Siswa') }}', '{{ strtoupper($lr->type) }}')" 
                                    class="neo-btn bg-[#FF6B6B] hover:bg-rose-600 text-white py-2.5 min-h-[44px] text-xs font-heading font-black cursor-pointer flex items-center justify-center gap-1 border border-black shadow-[1.5px_1.5px_0px_#000] active:scale-95 transition-all">
                                <span>✕</span>
                                <span>Tolak</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
