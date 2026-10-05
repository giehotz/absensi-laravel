<!-- Top Welcome Banner (Adapted, Optimized, Colorized Neo-Brutalism) -->
<div class="bg-[#FFD43B] neo-box-lg p-4.5 sm:p-6 md:p-8 relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-5 border-3 border-black shadow-[4px_4px_0px_0px_#000]">
    <!-- Background Neo Graphic Accent -->
    <div class="absolute -right-6 -bottom-6 w-36 h-36 bg-black/5 rounded-full pointer-events-none hidden md:block"></div>
    <div class="absolute right-20 -top-8 w-20 h-20 bg-white/30 rotate-12 pointer-events-none hidden lg:block border-2 border-black/10"></div>

    <div class="space-y-2 z-10 max-w-2xl">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-[10px] sm:text-xs font-heading font-black uppercase px-2.5 py-0.5 bg-black text-[#FFD43B] border border-black shadow-[1.5px_1.5px_0px_0px_#000]">
                DEWAN GURU
            </span>
            <span class="text-[11px] sm:text-xs font-mono font-bold bg-white text-black px-2 py-0.5 border border-black shadow-[1.5px_1.5px_0px_0px_#000]">
                NIP: {{ $teacher->nip ?? '-' }}
            </span>
            <span class="text-[11px] sm:text-xs font-mono font-bold bg-white/90 text-slate-800 px-2 py-0.5 border border-black shadow-[1.5px_1.5px_0px_0px_#000] flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
            </span>
        </div>

        <h1 class="font-heading text-xl sm:text-2xl md:text-3xl font-black text-black tracking-tight leading-tight">
            Halo, {{ Auth::user()->name }}! 👋
        </h1>
        <p class="text-xs sm:text-sm font-semibold text-slate-900 leading-relaxed">
            Pantau kehadiran siswa di kelas binaan dan mata pelajaran Anda hari ini, isi presensi secara cepat, dan verifikasi permohonan izin siswa secara real-time.
        </p>
    </div>

    <!-- Quick Action Buttons: High Contrast, Touch-Ergonomic -->
    <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-2.5 z-10 w-full md:w-auto shrink-0">
        <a href="{{ route('guru.attendance.manual') }}" 
           class="neo-btn bg-black text-white hover:bg-slate-800 px-4 py-2.5 min-h-[44px] text-xs font-heading font-black uppercase flex items-center justify-center gap-2 cursor-pointer border-2 border-black shadow-[3px_3px_0px_0px_#000] transition-transform active:translate-x-0.5 active:translate-y-0.5">
            <svg class="w-4 h-4 text-[#FFD43B] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            <span>Input Presensi Manual</span>
        </a>

        <a href="{{ route('guru.teaching-journals.index') }}" 
           class="neo-btn bg-white text-black hover:bg-slate-100 px-4 py-2.5 min-h-[44px] text-xs font-heading font-black uppercase flex items-center justify-center gap-2 cursor-pointer border-2 border-black shadow-[3px_3px_0px_0px_#000] transition-transform active:translate-x-0.5 active:translate-y-0.5">
            <svg class="w-4 h-4 text-black shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <span>Jurnal Mengajar</span>
        </a>

        <button type="button" onclick="featurePlaceholder('Scanner QR Presensi')" 
                class="neo-btn bg-[#20C997] text-black hover:bg-emerald-400 px-3.5 py-2.5 min-h-[44px] text-xs font-heading font-black uppercase flex items-center justify-center gap-1.5 cursor-pointer border-2 border-black shadow-[3px_3px_0px_0px_#000] transition-transform active:translate-x-0.5 active:translate-y-0.5"
                title="Buka Scanner QR Presensi Siswa">
            <svg class="w-4 h-4 text-black shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
            </svg>
            <span class="sm:hidden lg:inline">Scan QR</span>
        </button>
    </div>
</div>
