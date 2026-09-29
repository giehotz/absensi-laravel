<!-- ========================================================================= -->
<!-- TAB 1: BERANDA (PLAYFUL NEO-BRUTALISM) -->
<!-- ========================================================================= -->
<div id="tabContent-beranda" class="tab-pane space-y-4 sm:space-y-5">
    
    <!-- Status Kehadiran Hari Ini Card -->
    <div class="rounded-2xl border-2 sm:border-[2.5px] border-black p-4 sm:p-5 shadow-[4px_4px_0px_0px_#000] relative overflow-hidden
        @if($todayAttendance)
            @if($todayAttendance->status === 'hadir') bg-[#D3F9D8]
            @elseif($todayAttendance->status === 'terlambat') bg-[#FFF3BF]
            @elseif(in_array($todayAttendance->status, ['izin', 'sakit'])) bg-[#E7F5FF]
            @else bg-[#FFE3E3] @endif
        @else bg-[#FFE3E3] @endif">
        
        <!-- Header Info Pill -->
        <div class="flex items-center justify-between text-xs font-black text-black mb-3">
            <span class="inline-flex items-center gap-2 bg-white/80 backdrop-blur-sm px-3 py-1 rounded-full border border-black shadow-[1.5px_1.5px_0px_0px_#000]">
                <span class="w-2.5 h-2.5 rounded-full 
                    @if($todayAttendance) bg-emerald-500 @else bg-rose-500 animate-pulse @endif"></span>
                <span class="text-[11px] uppercase tracking-wider font-extrabold">STATUS PRESENSI HARI INI</span>
            </span>
            <span class="font-mono text-[11px] font-bold bg-white/80 px-2.5 py-1 rounded-md border border-black shadow-[1.5px_1.5px_0px_0px_#000]">
                {{ \Carbon\Carbon::now()->translatedFormat('l, d M Y') }}
            </span>
        </div>

        @if($todayAttendance)
            <div class="flex items-start justify-between gap-3 sm:gap-4">
                <div class="min-w-0">
                    <div class="text-[11px] font-black uppercase tracking-wider text-slate-800">Kehadiran Tercatat</div>
                    <div class="text-3xl sm:text-4xl font-black font-heading uppercase text-black tracking-tight mt-0.5">
                        {{ $todayAttendance->status }}
                    </div>
                    <p class="text-xs font-bold text-slate-800 mt-1.5 leading-relaxed">
                        {{ $todayAttendance->notes ?? 'Presensi telah sukses diverifikasi oleh sistem sekolah.' }}
                    </p>
                </div>
                <div class="text-right bg-white rounded-xl border-2 border-black p-3 shadow-[2.5px_2.5px_0px_0px_#000] shrink-0 min-w-[100px] sm:min-w-[120px]">
                    <div class="text-[9px] font-black text-slate-500 uppercase tracking-wider">Jam Masuk</div>
                    <div class="text-lg sm:text-xl font-black font-mono text-black mt-0.5">
                        {{ $todayAttendance->check_in_time ? $todayAttendance->check_in_time->format('H:i') . ' WIB' : '-' }}
                    </div>
                    @if($todayAttendance->check_out_time)
                        <div class="border-t border-slate-200 mt-2 pt-1.5">
                            <div class="text-[9px] font-black text-slate-500 uppercase tracking-wider">Jam Pulang</div>
                            <div class="text-sm font-black font-mono text-emerald-700">
                                {{ $todayAttendance->check_out_time->format('H:i') . ' WIB' }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5">
                <div>
                    <div class="text-[11px] font-black uppercase tracking-wider text-rose-900">Belum Ada Presensi</div>
                    <div class="text-2xl sm:text-3xl font-black font-heading text-rose-950 mt-0.5">
                        Belum Masuk Sekolah
                    </div>
                    <p class="text-xs font-bold text-rose-900/90 mt-1 max-w-md leading-relaxed">
                        Tunjukkan QR Token pada kamera scanner di gerbang madrasah/sekolah saat tiba.
                    </p>
                </div>
                <button type="button" onclick="switchTab('qr')" class="self-start sm:self-center bg-[#FFD43B] hover:bg-[#fcc419] text-black px-4 py-2.5 text-xs font-black uppercase rounded-xl border-2 border-black shadow-[3px_3px_0px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[1px_1px_0px_0px_#000] transition-all flex items-center gap-2 shrink-0">
                    <span>⚡ Buka QR Presensi</span>
                    <span>→</span>
                </button>
            </div>
        @endif
    </div>

    <!-- Quick Dynamic QR Banner -->
    <div class="bg-white rounded-2xl border-2 sm:border-[2.5px] border-black p-4 sm:p-5 shadow-[4px_4px_0px_0px_#000] flex items-center justify-between gap-4 cursor-pointer hover:bg-amber-50/50 transition-all group" onclick="openModal('modalFullscreenQr')">
        <div class="flex items-center gap-3.5 sm:gap-4 min-w-0">
            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-white rounded-xl border-2 border-black p-1.5 shadow-[2px_2px_0px_0px_#000] shrink-0 flex items-center justify-center group-hover:scale-105 transition-transform">
                <img src="{{ $qrCodeDataUri }}" alt="QR Presensi" class="w-full h-full object-contain">
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 bg-[#D3F9D8] px-2 py-0.5 rounded-full border border-black text-[10px] font-black uppercase tracking-wider text-emerald-900">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Live QR Token
                    </span>
                    <span class="text-[10px] font-bold text-slate-500 hidden sm:inline">Dinamis & Otomatis</span>
                </div>
                <div class="font-heading font-black text-base sm:text-lg text-black mt-1 truncate">
                    {{ $activeToken->token ?? $student->qr_code_identifier }}
                </div>
                <p class="text-xs font-semibold text-slate-600 truncate mt-0.5 flex items-center gap-1">
                    <span>Ketuk untuk perbesar layar penuh</span>
                    <span class="text-[#5294FF] font-bold">🔍</span>
                </p>
            </div>
        </div>
        <div class="bg-[#5294FF] group-hover:bg-[#3b82f6] text-white p-3 rounded-xl border-2 border-black shadow-[2.5px_2.5px_0px_0px_#000] group-hover:translate-x-0.5 group-hover:translate-y-0.5 group-hover:shadow-[1px_1px_0px_0px_#000] transition-all shrink-0" title="Perbesar Layar Penuh">
            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
            </svg>
        </div>
    </div>

    <!-- 3 Core Interactive Hero Feature Widgets -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 sm:gap-4">
        
        <!-- Card 1: Ringkasan Tabungan Siswa -->
        <a href="{{ route('siswa.savings.index') }}" class="bg-[#FFF4E6] rounded-2xl border-2 sm:border-[2.5px] border-black p-4 sm:p-5 shadow-[4px_4px_0px_0px_#000] flex flex-col justify-between gap-4 cursor-pointer hover:bg-[#FFE8CC] hover:-translate-y-0.5 hover:shadow-[5px_5px_0px_0px_#000] transition-all group block">
            <div class="flex items-start justify-between gap-3">
                <div class="w-12 h-12 rounded-xl bg-[#FF922B] text-white border-2 border-black flex items-center justify-center text-2xl shadow-[2px_2px_0px_0px_#000] shrink-0 group-hover:rotate-6 transition-transform">
                    💰
                </div>
                <div class="text-right">
                    <span class="text-[10px] font-black uppercase text-amber-950 bg-[#FFE8CC] px-2.5 py-0.5 border border-black rounded-full">
                        Tabungan Pelajar
                    </span>
                    <div class="text-[11px] font-mono font-bold text-slate-700 mt-1">
                        {{ $savingsAccount?->account_number ?? 'Belum Terdaftar' }}
                    </div>
                </div>
            </div>

            <div>
                <div class="text-xs font-bold text-slate-700">Saldo Rekening</div>
                <div class="font-mono font-black text-2xl sm:text-3xl text-black tracking-tight mt-0.5">
                    @if($savingsAccount && $savingsAccount->isActive())
                        {{ $savingsAccount->formatted_balance }}
                    @else
                        <span class="text-sm text-amber-900 font-extrabold bg-[#FFD43B] px-2 py-0.5 border border-black rounded">Aktifkan Sekarang →</span>
                    @endif
                </div>
            </div>

            <div class="pt-2 border-t-2 border-dashed border-amber-900/20 flex items-center justify-between text-xs font-black text-black">
                <span class="text-slate-700 font-bold text-[11px]">Buku Tabungan & Mutasi</span>
                <span class="inline-flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                    Buka <span class="text-sm">→</span>
                </span>
            </div>
        </a>

        <!-- Card 2: Kalender Pendidikan & Hari Libur -->
        <a href="{{ route('siswa.calendar.index') }}" class="bg-[#E7F5FF] rounded-2xl border-2 sm:border-[2.5px] border-black p-4 sm:p-5 shadow-[4px_4px_0px_0px_#000] flex flex-col justify-between gap-4 cursor-pointer hover:bg-[#D0EBFF] hover:-translate-y-0.5 hover:shadow-[5px_5px_0px_0px_#000] transition-all group block">
            <div class="flex items-start justify-between gap-3">
                <div class="w-12 h-12 rounded-xl bg-[#5294FF] text-white border-2 border-black flex items-center justify-center text-2xl shadow-[2px_2px_0px_0px_#000] shrink-0 group-hover:rotate-6 transition-transform">
                    📅
                </div>
                <div class="text-right">
                    <span class="text-[10px] font-black uppercase text-blue-950 bg-[#D0EBFF] px-2.5 py-0.5 border border-black rounded-full">
                        Agenda Sekolah
                    </span>
                    <div class="text-[11px] font-bold text-blue-900 mt-1">
                        Ganjil & Genap
                    </div>
                </div>
            </div>

            <div>
                <div class="font-heading font-black text-lg sm:text-xl text-black tracking-tight leading-snug">
                    Kalender Pendidikan & Libur
                </div>
                <p class="text-xs font-semibold text-slate-700 mt-1 line-clamp-2">
                    Jadwal ujian semester, hari libur nasional, dan agenda kegiatan madrasah/sekolah.
                </p>
            </div>

            <div class="pt-2 border-t-2 border-dashed border-blue-900/20 flex items-center justify-between text-xs font-black text-black">
                <span class="text-slate-700 font-bold text-[11px]">Cek Agenda Lengkap</span>
                <span class="inline-flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                    Lihat <span class="text-sm">→</span>
                </span>
            </div>
        </a>

        <!-- Card 3: Jurnal Pembelajaran Kelas -->
        <a href="{{ route('siswa.teaching-journals.index') }}" class="bg-[#F3F0FF] rounded-2xl border-2 sm:border-[2.5px] border-black p-4 sm:p-5 shadow-[4px_4px_0px_0px_#000] flex flex-col justify-between gap-4 cursor-pointer hover:bg-[#E5DBFF] hover:-translate-y-0.5 hover:shadow-[5px_5px_0px_0px_#000] transition-all group block">
            <div class="flex items-start justify-between gap-3">
                <div class="w-12 h-12 rounded-xl bg-[#7950F2] text-white border-2 border-black flex items-center justify-center text-2xl shadow-[2px_2px_0px_0px_#000] shrink-0 group-hover:rotate-6 transition-transform">
                    📖
                </div>
                <div class="text-right">
                    <span class="text-[10px] font-black uppercase text-purple-950 bg-[#E5DBFF] px-2.5 py-0.5 border border-black rounded-full">
                        Materi & KBM
                    </span>
                    <div class="text-[11px] font-bold text-purple-900 mt-1">
                        Catatan Guru
                    </div>
                </div>
            </div>

            <div>
                <div class="font-heading font-black text-lg sm:text-xl text-black tracking-tight leading-snug">
                    Jurnal Pembelajaran Kelas
                </div>
                <p class="text-xs font-semibold text-slate-700 mt-1 line-clamp-2">
                    Akses catatan harian guru, capaian kompetensi, serta rangkuman materi pembelajaran kelas.
                </p>
            </div>

            <div class="pt-2 border-t-2 border-dashed border-purple-900/20 flex items-center justify-between text-xs font-black text-black">
                <span class="text-slate-700 font-bold text-[11px]">Buka Catatan Belajar</span>
                <span class="inline-flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                    Buka <span class="text-sm">→</span>
                </span>
            </div>
        </a>

    </div>

    <!-- Monthly Attendance Statistics Grid -->
    <div class="bg-white rounded-2xl border-2 sm:border-[2.5px] border-black p-4 sm:p-5 shadow-[4px_4px_0px_0px_#000] space-y-3.5">
        <div class="flex items-center justify-between">
            <h3 class="font-heading font-black text-sm sm:text-base uppercase text-black flex items-center gap-2">
                <span>📊</span> 
                <span>Kehadiran Bulan Ini</span>
                <span class="text-xs font-bold text-slate-500 font-mono">({{ \Carbon\Carbon::now()->translatedFormat('F Y') }})</span>
            </h3>
            <button type="button" onclick="switchTab('riwayat')" class="text-xs font-black text-blue-700 hover:text-black hover:underline flex items-center gap-1">
                <span>Riwayat Lengkap</span>
                <span>→</span>
            </button>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3">
            <div class="bg-[#D3F9D8] rounded-xl border-2 border-black p-3 text-center shadow-[2px_2px_0px_0px_#000] hover:-translate-y-0.5 transition-transform">
                <div class="text-[10px] font-black text-emerald-950 uppercase tracking-wider">Hadir</div>
                <div class="text-2xl sm:text-3xl font-black font-heading text-black mt-0.5">{{ $summaryMonth['hadir'] }}</div>
                <div class="text-[10px] font-bold text-emerald-900 mt-0.5">Hari Efektif</div>
            </div>
            <div class="bg-[#FFF3BF] rounded-xl border-2 border-black p-3 text-center shadow-[2px_2px_0px_0px_#000] hover:-translate-y-0.5 transition-transform">
                <div class="text-[10px] font-black text-amber-950 uppercase tracking-wider">Terlambat</div>
                <div class="text-2xl sm:text-3xl font-black font-heading text-black mt-0.5">{{ $summaryMonth['terlambat'] }}</div>
                <div class="text-[10px] font-bold text-amber-900 mt-0.5">Kali</div>
            </div>
            <div class="bg-[#E7F5FF] rounded-xl border-2 border-black p-3 text-center shadow-[2px_2px_0px_0px_#000] hover:-translate-y-0.5 transition-transform">
                <div class="text-[10px] font-black text-blue-950 uppercase tracking-wider">Izin</div>
                <div class="text-2xl sm:text-3xl font-black font-heading text-black mt-0.5">{{ $summaryMonth['izin'] }}</div>
                <div class="text-[10px] font-bold text-blue-900 mt-0.5">Hari</div>
            </div>
            <div class="bg-[#FFE3E3] rounded-xl border-2 border-black p-3 text-center shadow-[2px_2px_0px_0px_#000] hover:-translate-y-0.5 transition-transform">
                <div class="text-[10px] font-black text-rose-950 uppercase tracking-wider">Sakit</div>
                <div class="text-2xl sm:text-3xl font-black font-heading text-black mt-0.5">{{ $summaryMonth['sakit'] }}</div>
                <div class="text-[10px] font-bold text-rose-900 mt-0.5">Hari</div>
            </div>
        </div>
    </div>

    <!-- Quick Shortcut Actions -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <button type="button" onclick="openModal('modalLeaveRequest')" class="bg-[#FF6B6B] hover:bg-[#fa5252] text-white p-3.5 sm:p-4 rounded-xl border-2 border-black shadow-[3px_3px_0px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[1px_1px_0px_0px_#000] transition-all flex items-center justify-center gap-2.5 text-xs sm:text-sm uppercase font-black tracking-wider cursor-pointer">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Ajukan Izin / Sakit</span>
        </button>
        <button type="button" onclick="switchTab('jadwal')" class="bg-[#20C997] hover:bg-[#12b886] text-black p-3.5 sm:p-4 rounded-xl border-2 border-black shadow-[3px_3px_0px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[1px_1px_0px_0px_#000] transition-all flex items-center justify-center gap-2.5 text-xs sm:text-sm uppercase font-black tracking-wider cursor-pointer">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span>Cek Jadwal Pelajaran</span>
        </button>
    </div>

    <!-- Ringkasan Jadwal Pelajaran Hari Ini -->
    <div class="bg-white rounded-2xl border-2 sm:border-[2.5px] border-black p-4 sm:p-5 shadow-[4px_4px_0px_0px_#000] space-y-3.5">
        <div class="flex items-center justify-between">
            <h3 class="font-heading font-black text-sm sm:text-base uppercase text-black flex items-center gap-2">
                <span>📚</span> 
                <span>Jadwal Pelajaran Hari Ini</span>
                <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md border border-black">
                    {{ $daysMap[$currentDayOfWeek] ?? 'Hari Ini' }}
                </span>
            </h3>
            <button type="button" onclick="switchTab('jadwal')" class="text-xs font-black text-blue-700 hover:text-black hover:underline flex items-center gap-1">
                <span>Semua Hari</span>
                <span>→</span>
            </button>
        </div>

        @php
            $todaySchedules = $schedulesByDay[$currentDayOfWeek] ?? [];
        @endphp

        @if(count($todaySchedules) > 0)
            <div class="space-y-2.5">
                @foreach($todaySchedules as $sched)
                    @php
                        $isOngoing = ($currentTimeStr >= $sched->start_time && $currentTimeStr <= $sched->end_time);
                    @endphp
                    <div class="p-3.5 rounded-xl border-2 border-black flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-[2.5px_2.5px_0px_0px_#000] transition-all
                        @if($isOngoing) bg-[#FFF3BF] ring-2 ring-yellow-400 @else bg-slate-50 hover:bg-slate-100/80 @endif">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-mono text-xs font-black bg-white px-2 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000] text-black">
                                    ⏰ {{ substr($sched->start_time, 0, 5) }} - {{ substr($sched->end_time, 0, 5) }}
                                </span>
                                @if($isOngoing)
                                    <span class="inline-flex items-center gap-1 bg-[#FF6B6B] text-white text-[10px] font-black uppercase px-2 py-0.5 rounded-full border border-black shadow-[1px_1px_0px_0px_#000] animate-pulse">
                                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                        Sedang Berlangsung
                                    </span>
                                @endif
                            </div>
                            <div class="font-heading font-black text-base text-black truncate mt-1.5">
                                {{ $sched->subject->name ?? 'Mata Pelajaran' }}
                            </div>
                            <div class="text-xs font-bold text-slate-700 truncate mt-0.5 flex items-center gap-1">
                                <span>👨‍🏫 Guru:</span>
                                <span class="text-black">{{ $sched->teacher->user->name ?? ($sched->teacher->nip ?? '-') }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-6 bg-slate-50 rounded-xl border-2 border-dashed border-slate-300 text-center">
                <div class="text-3xl mb-1.5">🎉</div>
                <div class="text-sm font-black text-black font-heading">Tidak Ada Jadwal Pelajaran Hari Ini</div>
                <p class="text-xs font-medium text-slate-500 mt-1 max-w-sm mx-auto">
                    Selamat beristirahat, mengikuti kegiatan ekstrakurikuler, atau belajar mandiri!
                </p>
            </div>
        @endif
    </div>
</div>
