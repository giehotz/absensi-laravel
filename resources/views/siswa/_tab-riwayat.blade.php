<!-- ========================================================================= -->
<!-- TAB 5: RIWAYAT & CATATAN GURU (SUB-TAB: PRESENSI & CATATAN BK) -->
<!-- ========================================================================= -->
<div id="tabContent-riwayat" class="tab-pane hidden space-y-4">
    
    <!-- Sub-tab Switcher -->
    <div class="grid grid-cols-2 gap-2 bg-slate-200 border-2 border-black p-1">
        <button type="button" onclick="switchHistorySubTab('presensi')" id="btnSubTab-presensi" 
            class="py-2 text-xs font-black uppercase text-center border-2 border-black bg-[#FFD43B] shadow-[2px_2px_0px_0px_#000] cursor-pointer">
            📅 Riwayat Kehadiran (30 Hari)
        </button>
        <button type="button" onclick="switchHistorySubTab('catatan')" id="btnSubTab-catatan" 
            class="py-2 text-xs font-black uppercase text-center border-2 border-transparent hover:border-black text-slate-700 cursor-pointer">
            📝 Catatan Pembinaan Guru ({{ count($studentNotes) }})
        </button>
    </div>

    <!-- Sub-Tab Content 1: Riwayat Kehadiran -->
    <div id="historySubPane-presensi" class="space-y-2.5">
        @forelse($history as $h)
            <div class="bg-white neo-box p-3.5 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-heading font-black text-sm text-black">
                            {{ $h->date->translatedFormat('l, d M Y') }}
                        </span>
                    </div>
                    <div class="text-xs font-mono font-bold text-slate-600 mt-0.5">
                        Masuk: <span class="text-black">{{ $h->check_in_time ? $h->check_in_time->format('H:i') . ' WIB' : '-' }}</span>
                        @if($h->check_out_time)
                            • Pulang: <span class="text-black">{{ $h->check_out_time->format('H:i') . ' WIB' }}</span>
                        @endif
                    </div>
                    @if($h->notes)
                        <div class="text-[11px] font-semibold text-slate-500 mt-1 truncate">
                            Catatan: {{ $h->notes }}
                        </div>
                    @endif
                </div>
                <div class="shrink-0 text-right">
                    <span class="neo-badge 
                        @if($h->status === 'hadir') bg-[#20C997] text-white 
                        @elseif($h->status === 'terlambat') bg-[#FFD43B] text-black 
                        @elseif(in_array($h->status, ['izin', 'sakit'])) bg-[#5294FF] text-white
                        @else bg-[#FF6B6B] text-white @endif text-[10px]">
                        {{ strtoupper($h->status) }}
                    </span>
                </div>
            </div>
        @empty
            <div class="bg-white neo-box p-6 text-center text-slate-500 space-y-2">
                <div class="text-3xl">📭</div>
                <div class="font-heading font-bold text-sm text-black">Belum Ada Riwayat</div>
                <p class="text-xs text-slate-600">Catatan riwayat presensi harian Anda akan ditampilkan di sini.</p>
            </div>
        @endforelse
    </div>

    <!-- Sub-Tab Content 2: Catatan Pembinaan Guru -->
    <div id="historySubPane-catatan" class="space-y-2.5 hidden">
        @forelse($studentNotes as $sn)
            <div class="bg-white neo-box p-4 space-y-2">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <span class="neo-badge 
                            @if($sn->category === 'prestasi') bg-[#20C997] text-white
                            @elseif($sn->category === 'kedisiplinan') bg-[#FF6B6B] text-white
                            @elseif($sn->category === 'kesehatan') bg-[#5294FF] text-white
                            @else bg-[#FFD43B] text-black @endif text-[10px]">
                            {{ strtoupper($sn->category) }}
                        </span>
                        <span class="text-xs font-mono font-bold text-slate-600 ml-2">
                            {{ \Carbon\Carbon::parse($sn->date)->translatedFormat('d F Y') }}
                        </span>
                        <h4 class="font-heading font-black text-base text-black mt-1">{{ $sn->title ?? 'Catatan Guru' }}</h4>
                    </div>
                </div>
                <p class="text-xs font-semibold text-slate-800 leading-relaxed">{{ $sn->content }}</p>
                @if($sn->follow_up)
                    <div class="p-2 bg-[#FFF9DB] border border-black text-xs font-bold text-slate-800">
                        📌 Tindak Lanjut: {{ $sn->follow_up }}
                    </div>
                @endif
                <div class="text-[11px] text-slate-500 font-medium pt-1 border-t border-slate-100">
                    Dicatat oleh: <strong class="text-black">{{ $sn->teacher->user->name ?? 'Guru / Wali Kelas' }}</strong>
                </div>
            </div>
        @empty
            <div class="bg-white neo-box p-6 text-center text-slate-500 space-y-2">
                <div class="text-3xl">🌟</div>
                <div class="font-heading font-bold text-sm text-black">Belum Ada Catatan Khusus</div>
                <p class="text-xs text-slate-600">Catatan pembinaan, apresiasi prestasi, atau evaluasi kedisiplinan dari bapak/ibu guru akan tampil di sini.</p>
            </div>
        @endforelse
    </div>
</div>
