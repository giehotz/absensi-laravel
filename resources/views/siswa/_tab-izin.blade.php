<!-- ========================================================================= -->
<!-- TAB 4: PENGAJUAN IZIN / SAKIT (PLAYFUL NEO-BRUTALISM) -->
<!-- ========================================================================= -->
<div id="tabContent-izin" class="tab-pane hidden space-y-4 sm:space-y-5">
    
    <!-- Header Banner & Action Button -->
    <div class="bg-[#FFF9DB] rounded-2xl border-2 sm:border-[2.5px] border-black p-4 sm:p-5 shadow-[4px_4px_0px_0px_#000] flex flex-col sm:flex-row sm:items-center justify-between gap-3.5">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-[#FFD43B] border-2 border-black flex items-center justify-center text-2xl shadow-[2px_2px_0px_0px_#000] shrink-0">
                📝
            </div>
            <div>
                <h3 class="font-heading font-black text-base sm:text-lg text-black">Permohonan Izin / Sakit</h3>
                <p class="text-xs font-bold text-slate-700 mt-0.5">
                    Ajukan surat izin atau bukti dokter secara resmi ke wali kelas.
                </p>
            </div>
        </div>
        <button type="button" onclick="openModal('modalLeaveRequest')" class="bg-[#FF6B6B] hover:bg-[#fa5252] text-white px-4 py-2.5 rounded-xl border-2 border-black shadow-[3px_3px_0px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[1px_1px_0px_0px_#000] transition-all text-xs sm:text-sm font-black uppercase tracking-wider flex items-center justify-center gap-2 cursor-pointer shrink-0">
            <span>➕ Ajukan Izin Baru</span>
        </button>
    </div>

    <!-- Leave Requests List -->
    <div class="space-y-3.5">
        <div class="flex items-center justify-between px-1">
            <h4 class="font-heading font-black text-xs sm:text-sm uppercase text-black flex items-center gap-2">
                <span>📋</span> Riwayat Permohonan Izin
            </h4>
            <span class="bg-slate-200 text-slate-800 text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full border border-black">
                {{ count($leaveRequests) }} Pengajuan
            </span>
        </div>

        @forelse($leaveRequests as $lr)
            <div class="bg-white rounded-2xl border-2 sm:border-[2.5px] border-black p-4 sm:p-5 space-y-3 shadow-[3.5px_3.5px_0px_0px_#000] transition-all">
                <div class="flex items-start justify-between gap-3 flex-wrap sm:flex-nowrap">
                    <div class="space-y-1.5 flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center px-3 py-0.5 rounded-full border border-black text-[10px] font-black uppercase tracking-wider shadow-[1.5px_1.5px_0px_0px_#000]
                                @if($lr->type === 'sakit') bg-[#FF6B6B] text-white @else bg-[#5294FF] text-white @endif">
                                {{ strtoupper($lr->type) }}
                            </span>
                            <span class="font-mono text-xs font-black text-black bg-slate-100 px-2.5 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000]">
                                📅 {{ $lr->date_from?->format('d/m/Y') }} 
                                @if($lr->date_from != $lr->date_to)
                                    s/d {{ $lr->date_to?->format('d/m/Y') }}
                                @endif
                            </span>
                        </div>
                        <div class="bg-slate-50 rounded-xl border border-black/20 p-3 mt-2">
                            <div class="text-[10px] font-black text-slate-500 uppercase tracking-wider mb-0.5">Alasan Permohonan:</div>
                            <p class="text-xs sm:text-sm font-bold text-black leading-relaxed">{{ $lr->reason }}</p>
                        </div>
                    </div>

                    <div class="shrink-0">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border-2 border-black text-xs font-black uppercase shadow-[2px_2px_0px_0px_#000]
                            @if($lr->status === 'approved') bg-[#20C997] text-white 
                            @elseif($lr->status === 'rejected') bg-[#FF6B6B] text-white 
                            @else bg-[#FFD43B] text-black @endif">
                            @if($lr->status === 'approved')
                                <span>✓</span> Disetujui
                            @elseif($lr->status === 'rejected')
                                <span>✗</span> Ditolak
                            @else
                                <span class="w-2 h-2 rounded-full bg-amber-800 animate-pulse"></span>
                                Menunggu
                            @endif
                        </span>
                    </div>
                </div>

                <!-- Attachment & Reviewer Details Footer -->
                <div class="pt-3 border-t-2 border-dashed border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-slate-600 font-semibold">
                    <div class="flex items-center gap-1.5">
                        <span>⏰ Diajukan: {{ $lr->created_at->format('d M Y, H:i') }} WIB</span>
                        @if($lr->reviewer)
                            <span>• Oleh: <strong class="text-black">{{ $lr->reviewer->name }}</strong></span>
                        @endif
                    </div>
                    @if($lr->attachment_path)
                        <a href="{{ asset('storage/' . $lr->attachment_path) }}" target="_blank" class="self-start sm:self-auto bg-[#E7F5FF] hover:bg-[#d0ebff] text-blue-900 border border-black px-3 py-1 rounded-lg shadow-[1.5px_1.5px_0px_0px_#000] font-black text-xs flex items-center gap-1.5 transition-all">
                            <span>📎</span> Lihat Bukti Lampiran
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border-2 border-dashed border-slate-300 p-8 text-center text-slate-500 space-y-2">
                <div class="text-4xl">📝</div>
                <div class="font-heading font-black text-base text-black">Belum Ada Pengajuan Izin / Sakit</div>
                <p class="text-xs font-medium text-slate-600 max-w-sm mx-auto">
                    Jika Anda berhalangan hadir ke sekolah karena sakit atau urusan keluarga mendesak, silakan ajukan secara resmi melalui tombol di atas.
                </p>
            </div>
        @endforelse
    </div>
</div>
