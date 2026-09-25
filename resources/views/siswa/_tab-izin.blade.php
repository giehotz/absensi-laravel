<!-- ========================================================================= -->
<!-- TAB 4: PENGAJUAN IZIN / SAKIT (FORM & STATUS TRACKER) -->
<!-- ========================================================================= -->
<div id="tabContent-izin" class="tab-pane hidden space-y-4">
    
    <!-- Header & Action Button -->
    <div class="bg-[#FFF9DB] neo-box p-4 flex items-center justify-between gap-3">
        <div>
            <h3 class="font-heading font-black text-base text-black">Permohonan Izin / Sakit</h3>
            <p class="text-xs font-semibold text-slate-700">Ajukan surat izin atau bukti sakit ke wali kelas.</p>
        </div>
        <button type="button" onclick="openModal('modalLeaveRequest')" class="neo-btn bg-[#FF6B6B] text-white px-3.5 py-2 text-xs font-black uppercase flex items-center gap-1.5 shrink-0 shadow-[2px_2px_0px_0px_#000]">
            <span>➕</span> Ajukan Izin
        </button>
    </div>

    <!-- Leave Requests List -->
    <div class="space-y-3">
        <h4 class="font-heading font-black text-sm uppercase text-black flex items-center gap-1.5 px-1">
            <span>📋</span> Riwayat Permohonan Terkini
        </h4>

        @forelse($leaveRequests as $lr)
            <div class="bg-white neo-box p-4 space-y-2.5">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="neo-badge 
                                @if($lr->type === 'sakit') bg-[#FF6B6B] text-white @else bg-[#5294FF] text-white @endif text-[10px]">
                                {{ strtoupper($lr->type) }}
                            </span>
                            <span class="text-xs font-mono font-bold text-slate-700">
                                {{ $lr->date_from?->format('d/m/Y') }} 
                                @if($lr->date_from != $lr->date_to)
                                    s/d {{ $lr->date_to?->format('d/m/Y') }}
                                @endif
                            </span>
                        </div>
                        <p class="text-xs font-bold text-black mt-1.5">{{ $lr->reason }}</p>
                    </div>
                    <div class="shrink-0 text-right">
                        <span class="neo-badge 
                            @if($lr->status === 'approved') bg-[#20C997] text-white 
                            @elseif($lr->status === 'rejected') bg-[#FF6B6B] text-white 
                            @else bg-[#FFD43B] text-black @endif text-[10px]">
                            @if($lr->status === 'approved') Disetujui
                            @elseif($lr->status === 'rejected') Ditolak
                            @else Menunggu @endif
                        </span>
                    </div>
                </div>

                <!-- Attachment & Reviewer Footer -->
                <div class="pt-2 border-t border-slate-200 flex items-center justify-between text-[11px] text-slate-500 font-medium">
                    <div>
                        Diajukan: {{ $lr->created_at->format('d M Y, H:i') }}
                        @if($lr->reviewer)
                            • Oleh: <strong class="text-black">{{ $lr->reviewer->name }}</strong>
                        @endif
                    </div>
                    @if($lr->attachment_path)
                        <a href="{{ asset('storage/' . $lr->attachment_path) }}" target="_blank" class="text-blue-600 font-bold hover:underline flex items-center gap-1">
                            <span>📎</span> Lihat Bukti
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white neo-box p-6 text-center text-slate-500 space-y-2">
                <div class="text-3xl">📝</div>
                <div class="font-heading font-bold text-sm text-black">Belum Ada Pengajuan Izin</div>
                <p class="text-xs text-slate-600">Jika Anda berhalangan hadir karena sakit atau izin penting, tekan tombol "Ajukan Izin" di atas.</p>
            </div>
        @endforelse
    </div>
</div>
