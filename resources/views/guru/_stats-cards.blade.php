<!-- 4 Stats Cards Grid (Adapted, Optimized, Colorized Neo-Brutalism) -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6">
    <!-- 1. Hadir Hari Ini -->
    <div class="bg-[#D3F9D8] neo-box p-3.5 sm:p-5 flex flex-col justify-between border-2 border-black shadow-[3px_3px_0px_0px_#000] transition-all hover:-translate-y-0.5">
        <div class="flex items-center justify-between gap-2">
            <span class="text-[10px] sm:text-xs font-heading font-black uppercase tracking-wider text-emerald-950">
                Hadir Tepat Waktu
            </span>
            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-sm bg-black text-[#D3F9D8] flex items-center justify-center shrink-0 border border-black shadow-[1px_1px_0px_0px_#000]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
        </div>
        <div class="mt-3 sm:mt-4">
            <div class="text-2xl sm:text-3xl lg:text-4xl font-black font-heading text-black leading-none">
                {{ $stats['hadir'] }}
            </div>
            <div class="text-[11px] sm:text-xs font-bold text-emerald-900 mt-1.5 flex items-center gap-1 truncate">
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                <span>Siswa hadir di sekolah</span>
            </div>
        </div>
    </div>

    <!-- 2. Terlambat -->
    <div class="bg-[#FFF3BF] neo-box p-3.5 sm:p-5 flex flex-col justify-between border-2 border-black shadow-[3px_3px_0px_0px_#000] transition-all hover:-translate-y-0.5">
        <div class="flex items-center justify-between gap-2">
            <span class="text-[10px] sm:text-xs font-heading font-black uppercase tracking-wider text-amber-950">
                Terlambat
            </span>
            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-sm bg-black text-[#FFD43B] flex items-center justify-center shrink-0 border border-black shadow-[1px_1px_0px_0px_#000]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div class="mt-3 sm:mt-4">
            <div class="text-2xl sm:text-3xl lg:text-4xl font-black font-heading text-black leading-none">
                {{ $stats['terlambat'] }}
            </div>
            <div class="text-[11px] sm:text-xs font-bold text-amber-900 mt-1.5 flex items-center gap-1 truncate">
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                <span>Lewat batas toleransi</span>
            </div>
        </div>
    </div>

    <!-- 3. Izin & Sakit -->
    <div class="bg-[#E7F5FF] neo-box p-3.5 sm:p-5 flex flex-col justify-between border-2 border-black shadow-[3px_3px_0px_0px_#000] transition-all hover:-translate-y-0.5">
        <div class="flex items-center justify-between gap-2">
            <span class="text-[10px] sm:text-xs font-heading font-black uppercase tracking-wider text-blue-950">
                Izin & Sakit
            </span>
            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-sm bg-black text-[#339AF0] flex items-center justify-center shrink-0 border border-black shadow-[1px_1px_0px_0px_#000]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>
        <div class="mt-3 sm:mt-4">
            <div class="text-2xl sm:text-3xl lg:text-4xl font-black font-heading text-black leading-none">
                {{ $stats['izin'] + $stats['sakit'] }}
            </div>
            <div class="text-[11px] sm:text-xs font-bold text-blue-900 mt-1.5 flex items-center gap-1 truncate">
                <span>{{ $stats['sakit'] }} Sakit</span>
                <span class="text-slate-400">•</span>
                <span>{{ $stats['izin'] }} Izin</span>
            </div>
        </div>
    </div>

    <!-- 4. Tanpa Keterangan (Alpa) -->
    <div class="bg-[#FFE3E3] neo-box p-3.5 sm:p-5 flex flex-col justify-between border-2 border-black shadow-[3px_3px_0px_0px_#000] transition-all hover:-translate-y-0.5">
        <div class="flex items-center justify-between gap-2">
            <span class="text-[10px] sm:text-xs font-heading font-black uppercase tracking-wider text-rose-950">
                Tanpa Keterangan
            </span>
            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-sm bg-black text-[#FF6B6B] flex items-center justify-center shrink-0 border border-black shadow-[1px_1px_0px_0px_#000]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
        </div>
        <div class="mt-3 sm:mt-4">
            <div class="text-2xl sm:text-3xl lg:text-4xl font-black font-heading text-black leading-none">
                {{ $stats['alpa'] }}
            </div>
            <div class="text-[11px] sm:text-xs font-bold text-rose-900 mt-1.5 flex items-center gap-1 truncate">
                @if($stats['belum_absen'] > 0)
                    <span>{{ $stats['belum_absen'] }} siswa belum diabsen</span>
                @else
                    <span>Semua siswa terdata</span>
                @endif
            </div>
        </div>
    </div>
</div>
