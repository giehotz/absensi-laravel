<!-- 6 KPI Cards: Kehadiran Hari Ini Real-Time (Adapted, Optimized, Colorized Neo-Brutalism) -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 sm:gap-3.5">
    <!-- 1. Total Siswa -->
    <div class="bg-white neo-box p-3.5 sm:p-4 flex flex-col justify-between border-2 border-black shadow-[2.5px_2.5px_0px_0px_#000] transition-transform hover:-translate-y-0.5">
        <div class="text-[10px] sm:text-[11px] font-heading font-black uppercase text-slate-700 flex items-center justify-between gap-1">
            <span class="truncate">Total Siswa</span>
            <div class="w-6 h-6 rounded-xs bg-black text-white flex items-center justify-center shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
        </div>
        <div class="mt-2 font-heading font-black text-2xl sm:text-3xl text-black leading-none">
            {{ $kpi['total'] }}
        </div>
        <div class="text-[10px] font-bold text-slate-500 mt-1">Siswa Terdaftar</div>
    </div>

    <!-- 2. Hadir (Hijau Mint) -->
    <div class="bg-[#D3F9D8] neo-box p-3.5 sm:p-4 flex flex-col justify-between border-2 border-black shadow-[2.5px_2.5px_0px_0px_#000] transition-transform hover:-translate-y-0.5">
        <div class="text-[10px] sm:text-[11px] font-heading font-black uppercase text-emerald-950 flex items-center justify-between gap-1">
            <span class="truncate">Hadir</span>
            <div class="w-6 h-6 rounded-xs bg-black text-[#D3F9D8] flex items-center justify-center shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
        </div>
        <div class="mt-2 font-heading font-black text-2xl sm:text-3xl text-emerald-950 leading-none">
            {{ $kpi['hadir'] }}
        </div>
        <div class="text-[10px] font-bold text-emerald-800 mt-1">Tepat Waktu</div>
    </div>

    <!-- 3. Terlambat (Kuning Amber) -->
    <div class="bg-[#FFF3BF] neo-box p-3.5 sm:p-4 flex flex-col justify-between border-2 border-black shadow-[2.5px_2.5px_0px_0px_#000] transition-transform hover:-translate-y-0.5">
        <div class="text-[10px] sm:text-[11px] font-heading font-black uppercase text-amber-950 flex items-center justify-between gap-1">
            <span class="truncate">Terlambat</span>
            <div class="w-6 h-6 rounded-xs bg-black text-[#FFD43B] flex items-center justify-center shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div class="mt-2 font-heading font-black text-2xl sm:text-3xl text-amber-950 leading-none">
            {{ $kpi['terlambat'] }}
        </div>
        <div class="text-[10px] font-bold text-amber-800 mt-1">Lewat Waktu</div>
    </div>

    <!-- 4. Izin (Biru Sky) -->
    <div class="bg-[#E7F5FF] neo-box p-3.5 sm:p-4 flex flex-col justify-between border-2 border-black shadow-[2.5px_2.5px_0px_0px_#000] transition-transform hover:-translate-y-0.5">
        <div class="text-[10px] sm:text-[11px] font-heading font-black uppercase text-blue-950 flex items-center justify-between gap-1">
            <span class="truncate">Izin</span>
            <div class="w-6 h-6 rounded-xs bg-black text-[#339AF0] flex items-center justify-center shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>
        <div class="mt-2 font-heading font-black text-2xl sm:text-3xl text-blue-950 leading-none">
            {{ $kpi['izin'] }}
        </div>
        <div class="text-[10px] font-bold text-blue-800 mt-1">Izin Resmi</div>
    </div>

    <!-- 5. Sakit (Orange Hangat) -->
    <div class="bg-[#FFE8CC] neo-box p-3.5 sm:p-4 flex flex-col justify-between border-2 border-black shadow-[2.5px_2.5px_0px_0px_#000] transition-transform hover:-translate-y-0.5">
        <div class="text-[10px] sm:text-[11px] font-heading font-black uppercase text-orange-950 flex items-center justify-between gap-1">
            <span class="truncate">Sakit</span>
            <div class="w-6 h-6 rounded-xs bg-black text-[#FF922B] flex items-center justify-center shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </div>
        </div>
        <div class="mt-2 font-heading font-black text-2xl sm:text-3xl text-orange-950 leading-none">
            {{ $kpi['sakit'] }}
        </div>
        <div class="text-[10px] font-bold text-orange-800 mt-1">Surat Sakit</div>
    </div>

    <!-- 6. Alpa (Rose) -->
    <div class="bg-[#FFE3E3] neo-box p-3.5 sm:p-4 flex flex-col justify-between border-2 border-black shadow-[2.5px_2.5px_0px_0px_#000] transition-transform hover:-translate-y-0.5">
        <div class="text-[10px] sm:text-[11px] font-heading font-black uppercase text-rose-950 flex items-center justify-between gap-1">
            <span class="truncate">Alpa</span>
            <div class="w-6 h-6 rounded-xs bg-black text-[#FF6B6B] flex items-center justify-center shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
        </div>
        <div class="mt-2 font-heading font-black text-2xl sm:text-3xl text-rose-950 leading-none">
            {{ $kpi['alpa'] }}
        </div>
        <div class="text-[10px] font-bold text-rose-800 mt-1">Tanpa Kabar</div>
    </div>
</div>

<!-- Progress Rate Kehadiran Hari Ini -->
<div class="bg-white neo-box p-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 border-2 border-black shadow-[3px_3px_0px_0px_#000]">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-sm bg-[#D3F9D8] text-emerald-950 flex items-center justify-center shrink-0 border-2 border-black shadow-[1.5px_1.5px_0px_#000]">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
        </div>
        <div>
            <div class="text-xs font-heading font-black uppercase text-black">
                Tingkat Kehadiran Kelas {{ $selectedClass->name ?? '' }} Hari Ini:
            </div>
            <div class="text-[11px] font-semibold text-slate-600">
                {{ $kpi['hadir'] + $kpi['terlambat'] }} dari {{ $kpi['total'] }} siswa hadir di sekolah
                @if($kpi['belum_absen'] > 0)
                    • <span class="font-bold text-amber-700 bg-amber-50 px-1.5 py-0.2 border border-amber-300 rounded-xs">{{ $kpi['belum_absen'] }} belum diabsen</span>
                @else
                    • <span class="font-bold text-emerald-700">Presensi lengkap</span>
                @endif
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3 w-full sm:w-72 shrink-0">
        <div class="w-full bg-slate-100 border-2 border-black h-4 rounded-xs overflow-hidden p-0.5 shadow-[1px_1px_0px_#000]">
            <div class="bg-[#20C997] h-full transition-all duration-500 rounded-xs" style="width: {{ $kpi['rate'] }}%"></div>
        </div>
        <span class="font-mono font-black text-sm text-black min-w-[50px] text-right">{{ $kpi['rate'] }}%</span>
    </div>
</div>
