<!-- Ringkasan Kehadiran Mingguan: 7 Hari Terakhir (Adapted, Optimized, Colorized Neo-Brutalism) -->
<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-sm bg-black text-[#20C997] flex items-center justify-center shrink-0 border border-black shadow-[1.5px_1.5px_0px_#000]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <h2 class="font-heading font-black text-lg sm:text-xl text-black leading-tight">
                    Tren 7 Hari Terakhir
                </h2>
                <div class="text-[11px] font-bold text-slate-500">
                    Siswa Kelas & Mapel Binaan
                </div>
            </div>
        </div>

        <span class="text-xs font-mono font-bold bg-white text-black border-2 border-black px-2.5 py-1 self-start sm:self-auto shadow-[1.5px_1.5px_0px_#000]">
            Total: {{ $weeklyStats['total'] }} Sesi
        </span>
    </div>

    <div class="bg-white neo-box p-4 sm:p-5 space-y-5 border-2 border-black shadow-[3px_3px_0px_0px_#000]">
        @if($weeklyStats['total'] > 0)
            <!-- Macro Stacked Proportion Bar -->
            <div class="space-y-1.5 pb-4 border-b-2 border-black/10">
                <div class="flex items-center justify-between text-[11px] font-bold text-slate-600">
                    <span class="font-heading font-black text-black uppercase">Proporsi Keseluruhan</span>
                    <span class="font-mono text-emerald-800 font-black">
                        {{ $weeklyStats['hadir']['percentage'] + $weeklyStats['terlambat']['percentage'] }}% Kehadiran
                    </span>
                </div>
                <div class="w-full h-3.5 bg-slate-100 border-2 border-black rounded-sm overflow-hidden flex shadow-[1px_1px_0px_#000]">
                    @if($weeklyStats['hadir']['count'] > 0)
                        <div class="bg-[#20C997] h-full" style="width: {{ $weeklyStats['hadir']['percentage'] }}%" title="Hadir: {{ $weeklyStats['hadir']['percentage'] }}%"></div>
                    @endif
                    @if($weeklyStats['terlambat']['count'] > 0)
                        <div class="bg-[#FFD43B] h-full" style="width: {{ $weeklyStats['terlambat']['percentage'] }}%" title="Terlambat: {{ $weeklyStats['terlambat']['percentage'] }}%"></div>
                    @endif
                    @if($weeklyStats['izin']['count'] > 0)
                        <div class="bg-[#339AF0] h-full" style="width: {{ $weeklyStats['izin']['percentage'] }}%" title="Izin: {{ $weeklyStats['izin']['percentage'] }}%"></div>
                    @endif
                    @if($weeklyStats['sakit']['count'] > 0)
                        <div class="bg-[#FF922B] h-full" style="width: {{ $weeklyStats['sakit']['percentage'] }}%" title="Sakit: {{ $weeklyStats['sakit']['percentage'] }}%"></div>
                    @endif
                    @if($weeklyStats['alpa']['count'] > 0)
                        <div class="bg-[#FF6B6B] h-full" style="width: {{ $weeklyStats['alpa']['percentage'] }}%" title="Alpa: {{ $weeklyStats['alpa']['percentage'] }}%"></div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Progress Bars per Status -->
        <div class="space-y-3.5">
            <!-- Hadir Tepat Waktu -->
            <div class="space-y-1">
                <div class="flex justify-between text-xs font-bold text-black">
                    <span class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-xs bg-[#20C997] border border-black inline-block"></span>
                        <span class="font-heading font-black">Hadir Tepat Waktu</span>
                    </span>
                    <span class="font-mono text-emerald-950 font-bold">
                        {{ $weeklyStats['hadir']['count'] }} <span class="text-slate-500 font-normal">({{ $weeklyStats['hadir']['percentage'] }}%)</span>
                    </span>
                </div>
                <div class="w-full bg-slate-100 border-2 border-black h-3.5 rounded-xs overflow-hidden p-0.5 shadow-[1px_1px_0px_#000]">
                    <div class="bg-[#20C997] h-full transition-all duration-500 rounded-xs" style="width: {{ $weeklyStats['hadir']['percentage'] }}%"></div>
                </div>
            </div>

            <!-- Terlambat -->
            <div class="space-y-1">
                <div class="flex justify-between text-xs font-bold text-black">
                    <span class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-xs bg-[#FFD43B] border border-black inline-block"></span>
                        <span class="font-heading font-black">Terlambat</span>
                    </span>
                    <span class="font-mono text-amber-950 font-bold">
                        {{ $weeklyStats['terlambat']['count'] }} <span class="text-slate-500 font-normal">({{ $weeklyStats['terlambat']['percentage'] }}%)</span>
                    </span>
                </div>
                <div class="w-full bg-slate-100 border-2 border-black h-3.5 rounded-xs overflow-hidden p-0.5 shadow-[1px_1px_0px_#000]">
                    <div class="bg-[#FFD43B] h-full transition-all duration-500 rounded-xs" style="width: {{ $weeklyStats['terlambat']['percentage'] }}%"></div>
                </div>
            </div>

            <!-- Izin Resmi -->
            <div class="space-y-1">
                <div class="flex justify-between text-xs font-bold text-black">
                    <span class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-xs bg-[#339AF0] border border-black inline-block"></span>
                        <span class="font-heading font-black">Izin</span>
                    </span>
                    <span class="font-mono text-blue-950 font-bold">
                        {{ $weeklyStats['izin']['count'] }} <span class="text-slate-500 font-normal">({{ $weeklyStats['izin']['percentage'] }}%)</span>
                    </span>
                </div>
                <div class="w-full bg-slate-100 border-2 border-black h-3.5 rounded-xs overflow-hidden p-0.5 shadow-[1px_1px_0px_#000]">
                    <div class="bg-[#339AF0] h-full transition-all duration-500 rounded-xs" style="width: {{ $weeklyStats['izin']['percentage'] }}%"></div>
                </div>
            </div>

            <!-- Sakit -->
            <div class="space-y-1">
                <div class="flex justify-between text-xs font-bold text-black">
                    <span class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-xs bg-[#FF922B] border border-black inline-block"></span>
                        <span class="font-heading font-black">Sakit</span>
                    </span>
                    <span class="font-mono text-orange-950 font-bold">
                        {{ $weeklyStats['sakit']['count'] }} <span class="text-slate-500 font-normal">({{ $weeklyStats['sakit']['percentage'] }}%)</span>
                    </span>
                </div>
                <div class="w-full bg-slate-100 border-2 border-black h-3.5 rounded-xs overflow-hidden p-0.5 shadow-[1px_1px_0px_#000]">
                    <div class="bg-[#FF922B] h-full transition-all duration-500 rounded-xs" style="width: {{ $weeklyStats['sakit']['percentage'] }}%"></div>
                </div>
            </div>

            <!-- Alpa / Tanpa Keterangan -->
            <div class="space-y-1">
                <div class="flex justify-between text-xs font-bold text-black">
                    <span class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-xs bg-[#FF6B6B] border border-black inline-block"></span>
                        <span class="font-heading font-black">Alpa / Tanpa Keterangan</span>
                    </span>
                    <span class="font-mono text-rose-950 font-bold">
                        {{ $weeklyStats['alpa']['count'] }} <span class="text-slate-500 font-normal">({{ $weeklyStats['alpa']['percentage'] }}%)</span>
                    </span>
                </div>
                <div class="w-full bg-slate-100 border-2 border-black h-3.5 rounded-xs overflow-hidden p-0.5 shadow-[1px_1px_0px_#000]">
                    <div class="bg-[#FF6B6B] h-full transition-all duration-500 rounded-xs" style="width: {{ $weeklyStats['alpa']['percentage'] }}%"></div>
                </div>
            </div>
        </div>

        @if($weeklyStats['total'] === 0)
            <div class="text-center text-xs text-slate-500 py-3 border-t-2 border-slate-100">
                Belum ada rekaman presensi dalam 7 hari terakhir.
            </div>
        @else
            <div class="pt-3 border-t-2 border-black/10 flex items-center justify-between text-xs">
                <span class="text-slate-500 text-[11px] font-semibold">Data diperbarui otomatis</span>
                <a href="{{ route('guru.reports.attendance') }}" class="font-heading font-black text-black underline hover:text-blue-700">
                    Buka Rekap Lengkap →
                </a>
            </div>
        @endif
    </div>
</div>
