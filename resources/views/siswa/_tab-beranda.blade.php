<!-- ========================================================================= -->
<!-- TAB 1: BERANDA -->
<!-- ========================================================================= -->
<div id="tabContent-beranda" class="tab-pane space-y-4">
    
    <!-- Status Kehadiran Hari Ini Card -->
    <div class="neo-box p-4 sm:p-5 
        @if($todayAttendance)
            @if($todayAttendance->status === 'hadir') bg-[#D3F9D8]
            @elseif($todayAttendance->status === 'terlambat') bg-[#FFF3BF]
            @elseif(in_array($todayAttendance->status, ['izin', 'sakit'])) bg-[#E7F5FF]
            @else bg-[#FFE3E3] @endif
        @else bg-[#FFE3E3] @endif relative overflow-hidden">
        
        <div class="flex items-center justify-between text-xs font-bold text-slate-700 mb-2">
            <span class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full 
                    @if($todayAttendance) bg-emerald-500 @else bg-rose-500 animate-pulse @endif"></span>
                STATUS PRESENSI HARI INI
            </span>
            <span class="font-mono">{{ \Carbon\Carbon::now()->translatedFormat('l, d M Y') }}</span>
        </div>

        @if($todayAttendance)
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-xs font-extrabold uppercase tracking-wide text-slate-800">Kehadiran Tercatat</div>
                    <div class="text-2xl sm:text-3xl font-black font-heading uppercase text-black mt-0.5">
                        {{ $todayAttendance->status }}
                    </div>
                    <p class="text-xs font-semibold text-slate-700 mt-1">
                        {{ $todayAttendance->notes ?? 'Presensi berhasil diverifikasi oleh sistem.' }}
                    </p>
                </div>
                <div class="text-right bg-white border-2 border-black p-2.5 shadow-[2px_2px_0px_0px_#000] shrink-0">
                    <div class="text-[9px] font-bold text-slate-500 uppercase">Jam Masuk</div>
                    <div class="text-lg sm:text-xl font-black font-mono text-black">
                        {{ $todayAttendance->check_in_time ? $todayAttendance->check_in_time->format('H:i') . ' WIB' : '-' }}
                    </div>
                    @if($todayAttendance->check_out_time)
                        <div class="text-[9px] font-bold text-slate-500 uppercase mt-1">Jam Pulang</div>
                        <div class="text-xs font-black font-mono text-black">
                            {{ $todayAttendance->check_out_time->format('H:i') . ' WIB' }}
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="flex items-center justify-between gap-3">
                <div>
                    <div class="text-xs font-extrabold uppercase tracking-wide text-rose-900">Belum Ada Presensi</div>
                    <div class="text-xl sm:text-2xl font-black font-heading text-rose-950 mt-0.5">
                        Belum Masuk Sekolah
                    </div>
                    <p class="text-xs font-semibold text-rose-900 mt-1">
                        Pindai QR Token ke scanner di gerbang madrasah/sekolah saat tiba.
                    </p>
                </div>
                <button onclick="switchTab('qr')" class="neo-btn bg-[#FFD43B] text-black px-3.5 py-2 text-xs font-black uppercase shrink-0 flex items-center gap-1.5 shadow-[2px_2px_0px_0px_#000]">
                    <span>Buka QR</span> →
                </button>
            </div>
        @endif
    </div>

    <!-- Quick Dynamic QR Banner -->
    <div class="bg-white neo-box p-4 flex items-center justify-between gap-4 cursor-pointer hover:bg-yellow-50/50 transition-colors" onclick="openModal('modalFullscreenQr')">
        <div class="flex items-center gap-3.5 min-w-0">
            <div class="w-16 h-16 bg-white border-2 border-black p-1 shadow-[2px_2px_0px_0px_#000] shrink-0 flex items-center justify-center">
                <img src="{{ $qrCodeDataUri }}" alt="QR Presensi" class="w-full h-full object-contain">
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-[10px] font-bold uppercase text-emerald-800 tracking-wider">Live QR Token</span>
                </div>
                <div class="font-heading font-black text-sm sm:text-base text-black mt-0.5 truncate">
                    {{ $activeToken->token ?? $student->qr_code_identifier }}
                </div>
                <p class="text-[11px] font-medium text-slate-600 truncate">Ketuk untuk memperbesar layar penuh</p>
            </div>
        </div>
        <div class="neo-btn bg-[#5294FF] text-white p-2.5 shrink-0" title="Perbesar">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
            </svg>
        </div>
    </div>

    <!-- Card Ringkasan Tabungan Siswa -->
    <div onclick="switchTab('tabungan')" class="bg-[#FFF4E6] neo-box p-4 border-3 border-black flex items-center justify-between gap-3 cursor-pointer hover:translate-x-0.5 hover:bg-[#FFE8CC] transition-all group">
        <div class="flex items-center gap-3.5 min-w-0">
            <div class="w-11 h-11 rounded-lg bg-[#FF922B] text-white border-2 border-black flex items-center justify-center text-xl shadow-[2px_2px_0px_0px_#000] shrink-0 group-hover:scale-105 transition-transform">
                💰
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-black uppercase text-amber-950 bg-[#FFE8CC] px-2 py-0.5 border border-black rounded">Tabungan Pelajar</span>
                    <span class="text-[10px] font-mono font-bold text-slate-600">{{ $savingsAccount->account_number ?? '-' }}</span>
                </div>
                <div class="font-mono font-black text-xl text-black mt-0.5">
                    {{ $savingsAccount->formatted_balance ?? 'Rp 0' }}
                </div>
            </div>
        </div>
        <div class="shrink-0 flex items-center gap-1 text-xs font-black text-black">
            <span class="hidden xs:inline text-[11px] text-slate-600">Buku Tabungan</span>
            <span class="text-base group-hover:translate-x-1 transition-transform">→</span>
        </div>
    </div>

    <!-- Monthly Attendance Statistics Grid -->
    <div class="bg-white neo-box p-4 space-y-3">

        <div class="flex items-center justify-between">
            <h3 class="font-heading font-black text-sm uppercase text-black flex items-center gap-1.5">
                <span>📊</span> Kehadiran Bulan Ini ({{ \Carbon\Carbon::now()->translatedFormat('F Y') }})
            </h3>
            <button onclick="switchTab('riwayat')" class="text-xs font-bold text-blue-600 hover:underline">
                Lihat Riwayat →
            </button>
        </div>

        <div class="grid grid-cols-4 gap-2 sm:gap-3">
            <div class="bg-[#D3F9D8] border-2 border-black p-2.5 text-center shadow-[2px_2px_0px_0px_#000]">
                <div class="text-[10px] font-black text-slate-700 uppercase tracking-tight">Hadir</div>
                <div class="text-xl sm:text-2xl font-black font-heading text-black mt-0.5">{{ $summaryMonth['hadir'] }}</div>
                <div class="text-[9px] font-bold text-slate-500">Hari</div>
            </div>
            <div class="bg-[#FFF3BF] border-2 border-black p-2.5 text-center shadow-[2px_2px_0px_0px_#000]">
                <div class="text-[10px] font-black text-slate-700 uppercase tracking-tight">Terlambat</div>
                <div class="text-xl sm:text-2xl font-black font-heading text-black mt-0.5">{{ $summaryMonth['terlambat'] }}</div>
                <div class="text-[9px] font-bold text-slate-500">Kali</div>
            </div>
            <div class="bg-[#E7F5FF] border-2 border-black p-2.5 text-center shadow-[2px_2px_0px_0px_#000]">
                <div class="text-[10px] font-black text-slate-700 uppercase tracking-tight">Izin</div>
                <div class="text-xl sm:text-2xl font-black font-heading text-black mt-0.5">{{ $summaryMonth['izin'] }}</div>
                <div class="text-[9px] font-bold text-slate-500">Hari</div>
            </div>
            <div class="bg-[#FFE3E3] border-2 border-black p-2.5 text-center shadow-[2px_2px_0px_0px_#000]">
                <div class="text-[10px] font-black text-slate-700 uppercase tracking-tight">Sakit</div>
                <div class="text-xl sm:text-2xl font-black font-heading text-black mt-0.5">{{ $summaryMonth['sakit'] }}</div>
                <div class="text-[9px] font-bold text-slate-500">Hari</div>
            </div>
        </div>
    </div>

    <!-- Quick Shortcut Actions -->
    <div class="grid grid-cols-2 gap-3">
        <button onclick="openModal('modalLeaveRequest')" class="neo-btn bg-[#FF6B6B] text-white p-3.5 flex items-center justify-center gap-2 text-xs uppercase font-black tracking-wider">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Ajukan Izin/Sakit</span>
        </button>
        <button onclick="switchTab('jadwal')" class="neo-btn bg-[#20C997] text-white p-3.5 flex items-center justify-center gap-2 text-xs uppercase font-black tracking-wider">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span>Cek Jadwal Kelas</span>
        </button>
    </div>

    <!-- Ringkasan Jadwal Pelajaran Hari Ini -->
    <div class="bg-white neo-box p-4 space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="font-heading font-black text-sm uppercase text-black flex items-center gap-1.5">
                <span>📖</span> Jadwal Pelajaran Hari Ini ({{ $daysMap[$currentDayOfWeek] ?? 'Hari Ini' }})
            </h3>
            <button onclick="switchTab('jadwal')" class="text-xs font-bold text-blue-600 hover:underline">
                Semua Hari →
            </button>
        </div>

        @php
            $todaySchedules = $schedulesByDay[$currentDayOfWeek] ?? [];
        @endphp

        @if(count($todaySchedules) > 0)
            <div class="space-y-2">
                @foreach($todaySchedules as $sched)
                    @php
                        $isOngoing = ($currentTimeStr >= $sched->start_time && $currentTimeStr <= $sched->end_time);
                    @endphp
                    <div class="p-3 border-2 border-black flex items-center justify-between gap-2.5 shadow-[2px_2px_0px_0px_#000]
                        @if($isOngoing) bg-[#FFF3BF] border-yellow-500 @else bg-slate-50 @endif">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs font-black text-black">
                                    {{ substr($sched->start_time, 0, 5) }} - {{ substr($sched->end_time, 0, 5) }}
                                </span>
                                @if($isOngoing)
                                    <span class="neo-badge bg-[#FF6B6B] text-white text-[9px] py-0.2 px-1">Sedang Berlangsung</span>
                                @endif
                            </div>
                            <div class="font-heading font-black text-sm text-black truncate mt-0.5">
                                {{ $sched->subject->name ?? 'Mata Pelajaran' }}
                            </div>
                            <div class="text-[11px] font-bold text-slate-600 truncate">
                                Guru: {{ $sched->teacher->user->name ?? ($sched->teacher->nip ?? '-') }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-4 bg-slate-50 border-2 border-dashed border-slate-300 text-center text-xs font-bold text-slate-500">
                🎉 Tidak ada jadwal pelajaran hari ini. Selamat beristirahat atau belajar mandiri!
            </div>
        @endif
    </div>
</div>
