<!-- ========================================================================= -->
<!-- TAB 5: RIWAYAT & CATATAN GURU (PLAYFUL NEO-BRUTALISM) -->
<!-- ========================================================================= -->
<div id="tabContent-riwayat" class="tab-pane hidden space-y-4 sm:space-y-5">
    
    <!-- Sub-tab Switcher Bar -->
    <div class="bg-white rounded-2xl border-2 sm:border-[2.5px] border-black p-1.5 shadow-[4px_4px_0px_0px_#000] grid grid-cols-2 gap-2">
        <button type="button" onclick="switchHistorySubTab('presensi')" id="btnSubTab-presensi" 
            class="py-2.5 px-3 text-xs sm:text-sm font-black uppercase text-center rounded-xl border-2 border-black bg-[#FFD43B] shadow-[2px_2px_0px_0px_#000] transition-all cursor-pointer flex items-center justify-center gap-1.5">
            <span>📅 Riwayat Presensi</span>
            <span class="hidden sm:inline">(30 Hari)</span>
        </button>
        <button type="button" onclick="switchHistorySubTab('catatan')" id="btnSubTab-catatan" 
            class="py-2.5 px-3 text-xs sm:text-sm font-bold uppercase text-center rounded-xl border-2 border-transparent hover:border-black text-slate-700 hover:bg-slate-100 transition-all cursor-pointer flex items-center justify-center gap-1.5">
            <span>📝 Catatan Guru</span>
            <span class="bg-slate-200 text-black text-[10px] font-black px-2 py-0.2 rounded-full border border-black">{{ count($studentNotes) }}</span>
        </button>
    </div>

    <!-- Sub-Tab Content 1: Riwayat Kehadiran -->
    <div id="historySubPane-presensi" class="space-y-3">
        @forelse($history as $h)
            <div class="bg-white rounded-2xl border-2 sm:border-[2.5px] border-black p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-[3px_3px_0px_0px_#000] hover:-translate-y-0.5 transition-all">
                <div class="min-w-0 space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="font-heading font-black text-base text-black">
                            {{ $h->date->translatedFormat('l, d M Y') }}
                        </span>
                    </div>
                    <div class="text-xs font-mono font-bold text-slate-600 flex items-center gap-2 flex-wrap">
                        <span class="bg-slate-100 px-2 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000]">
                            Masuk: <strong class="text-black">{{ $h->check_in_time ? $h->check_in_time->format('H:i') . ' WIB' : '-' }}</strong>
                        </span>
                        @if($h->check_out_time)
                            <span class="bg-slate-100 px-2 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000]">
                                Pulang: <strong class="text-emerald-700">{{ $h->check_out_time->format('H:i') . ' WIB' }}</strong>
                            </span>
                        @endif
                    </div>
                    @if($h->notes)
                        <div class="text-xs font-bold text-slate-600 bg-amber-50 px-2.5 py-1 rounded-md border border-amber-200 mt-1 inline-block">
                            💬 Catatan: {{ $h->notes }}
                        </div>
                    @endif
                </div>

                <div class="self-start sm:self-center shrink-0">
                    <span class="inline-flex items-center px-3.5 py-1 rounded-full border-2 border-black text-xs font-black uppercase shadow-[2px_2px_0px_0px_#000]
                        @if($h->status === 'hadir') bg-[#20C997] text-white 
                        @elseif($h->status === 'terlambat') bg-[#FFD43B] text-black 
                        @elseif(in_array($h->status, ['izin', 'sakit'])) bg-[#5294FF] text-white
                        @else bg-[#FF6B6B] text-white @endif">
                        {{ strtoupper($h->status) }}
                    </span>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border-2 border-dashed border-slate-300 p-8 text-center text-slate-500 space-y-2">
                <div class="text-4xl">📭</div>
                <div class="font-heading font-black text-base text-black">Belum Ada Riwayat Presensi</div>
                <p class="text-xs font-medium text-slate-600 max-w-sm mx-auto">
                    Catatan kehadiran harian Anda akan otomatis terarsip rapi pada halaman ini setelah presensi dipindai.
                </p>
            </div>
        @endforelse
    </div>

    <!-- Sub-Tab Content 2: Catatan Pembinaan Guru -->
    <div id="historySubPane-catatan" class="space-y-3.5 hidden">
        @forelse($studentNotes as $sn)
            <div class="bg-white rounded-2xl border-2 sm:border-[2.5px] border-black p-4 sm:p-5 space-y-3 shadow-[3.5px_3.5px_0px_0px_#000]">
                <div class="flex items-start justify-between gap-2 flex-wrap">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-3 py-0.5 rounded-full border border-black text-[10px] font-black uppercase tracking-wider shadow-[1.5px_1.5px_0px_0px_#000]
                                @if($sn->category === 'prestasi') bg-[#20C997] text-white
                                @elseif($sn->category === 'kedisiplinan') bg-[#FF6B6B] text-white
                                @elseif($sn->category === 'kesehatan') bg-[#5294FF] text-white
                                @else bg-[#FFD43B] text-black @endif">
                                {{ strtoupper($sn->category) }}
                            </span>
                            <span class="text-xs font-mono font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded border border-black">
                                📅 {{ $sn->date?->translatedFormat('d F Y') }}
                            </span>
                        </div>
                        <h4 class="font-heading font-black text-base sm:text-lg text-black mt-2 leading-tight">
                            {{ $sn->title ?? 'Catatan Khusus Siswa' }}
                        </h4>
                    </div>
                </div>

                <div class="bg-slate-50 rounded-xl border border-black/20 p-3.5">
                    <p class="text-xs sm:text-sm font-semibold text-slate-800 leading-relaxed">{{ $sn->content }}</p>
                </div>

                @if($sn->follow_up)
                    <div class="p-3 bg-[#FFF9DB] rounded-xl border-2 border-black text-xs font-bold text-amber-950 shadow-[2px_2px_0px_0px_#000]">
                        <span class="font-black text-black">📌 Tindak Lanjut:</span> {{ $sn->follow_up }}
                    </div>
                @endif

                <div class="text-xs text-slate-600 font-semibold pt-2 border-t-2 border-dashed border-slate-200 flex items-center justify-between">
                    <div>
                        Dicatat oleh: <strong class="text-black">{{ $sn->teacher->user->name ?? 'Guru / Wali Kelas' }}</strong>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border-2 border-dashed border-slate-300 p-8 text-center text-slate-500 space-y-2">
                <div class="text-4xl">🌟</div>
                <div class="font-heading font-black text-base text-black">Belum Ada Catatan Khusus</div>
                <p class="text-xs font-medium text-slate-600 max-w-sm mx-auto">
                    Catatan apresiasi prestasi, pembinaan, atau evaluasi dari bapak/ibu guru akan ditampilkan di sini.
                </p>
            </div>
        @endforelse
    </div>
</div>
