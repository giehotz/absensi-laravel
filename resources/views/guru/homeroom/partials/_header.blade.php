<!-- Header Title Card (Adapted, Optimized, Colorized Neo-Brutalism) -->
<div class="bg-[#20C997] neo-box-lg p-4 sm:p-6 md:p-8 text-black relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border-3 border-black shadow-[4px_4px_0px_0px_#000]">
    <div class="space-y-1.5 z-10 max-w-2xl">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-[10px] sm:text-xs font-heading font-black uppercase px-2.5 py-0.5 bg-black text-[#20C997] border border-black shadow-[1.5px_1.5px_0px_0px_#000]">
                PORTAL WALI KELAS
            </span>
            <span class="text-[11px] sm:text-xs font-mono font-bold bg-white text-black px-2 py-0.5 border border-black shadow-[1.5px_1.5px_0px_0px_#000]">
                NIP: {{ $teacher->nip ?? '-' }}
            </span>
            <span class="text-[11px] sm:text-xs font-mono font-bold bg-white/95 text-slate-800 px-2 py-0.5 border border-black shadow-[1.5px_1.5px_0px_0px_#000] flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
            </span>
        </div>

        <h1 class="font-heading text-xl sm:text-2xl md:text-3xl font-black tracking-tight text-black uppercase leading-tight">
            KELAS BINAAN (WALI KELAS)
        </h1>
        <p class="text-xs sm:text-sm font-semibold text-slate-900 leading-relaxed">
            Pantau data kehadiran siswa binaan, tindak lanjuti perizinan, kelola catatan khusus, dan hubungi orang tua/wali murid dengan akses instan WhatsApp.
        </p>
    </div>

    <div class="z-10 w-full sm:w-auto shrink-0">
        <a href="{{ route('guru.dashboard') }}" 
           class="neo-btn bg-white hover:bg-slate-100 text-black text-xs font-heading font-black px-4 py-2.5 min-h-[42px] flex items-center justify-center gap-1.5 border-2 border-black shadow-[3px_3px_0px_0px_#000] transition-transform active:translate-x-0.5 active:translate-y-0.5 cursor-pointer w-full sm:w-auto">
            <span>← Kembali ke Dashboard</span>
        </a>
    </div>
</div>
