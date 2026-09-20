<!-- Ringkasan Kehadiran Mingguan (7 Hari Terakhir) -->
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h2 class="font-heading font-black text-xl text-black flex items-center gap-2">
            <span>📊</span> Ringkasan Kehadiran 7 Hari Terakhir
        </h2>
        <span class="text-xs font-mono font-bold bg-white border-2 border-black px-2.5 py-1">
            Total Rekap: {{ $weeklyStats['total'] }} Catatan
        </span>
    </div>

    <div class="bg-white neo-box p-6 space-y-5">
        <!-- Progress Bars per Status -->
        <div class="space-y-4">
            <!-- Hadir -->
            <div class="space-y-1.5">
                <div class="flex justify-between text-xs font-bold text-black">
                    <span class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-sm bg-[#20C997] border border-black inline-block"></span>
                        Hadir Tepat Waktu
                    </span>
                    <span class="font-mono">{{ $weeklyStats['hadir']['count'] }} ({{ $weeklyStats['hadir']['percentage'] }}%)</span>
                </div>
                <div class="w-full bg-slate-100 border-2 border-black h-4 rounded-sm overflow-hidden p-0.5">
                    <div class="bg-[#20C997] h-full transition-all duration-500 rounded-xs" style="width: {{ $weeklyStats['hadir']['percentage'] }}%"></div>
                </div>
            </div>

            <!-- Terlambat -->
            <div class="space-y-1.5">
                <div class="flex justify-between text-xs font-bold text-black">
                    <span class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-sm bg-[#FFD43B] border border-black inline-block"></span>
                        Terlambat
                    </span>
                    <span class="font-mono">{{ $weeklyStats['terlambat']['count'] }} ({{ $weeklyStats['terlambat']['percentage'] }}%)</span>
                </div>
                <div class="w-full bg-slate-100 border-2 border-black h-4 rounded-sm overflow-hidden p-0.5">
                    <div class="bg-[#FFD43B] h-full transition-all duration-500 rounded-xs" style="width: {{ $weeklyStats['terlambat']['percentage'] }}%"></div>
                </div>
            </div>

            <!-- Sakit -->
            <div class="space-y-1.5">
                <div class="flex justify-between text-xs font-bold text-black">
                    <span class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-sm bg-[#74C0FC] border border-black inline-block"></span>
                        Sakit
                    </span>
                    <span class="font-mono">{{ $weeklyStats['sakit']['count'] }} ({{ $weeklyStats['sakit']['percentage'] }}%)</span>
                </div>
                <div class="w-full bg-slate-100 border-2 border-black h-4 rounded-sm overflow-hidden p-0.5">
                    <div class="bg-[#74C0FC] h-full transition-all duration-500 rounded-xs" style="width: {{ $weeklyStats['sakit']['percentage'] }}%"></div>
                </div>
            </div>

            <!-- Izin -->
            <div class="space-y-1.5">
                <div class="flex justify-between text-xs font-bold text-black">
                    <span class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-sm bg-[#A5D8FF] border border-black inline-block"></span>
                        Izin
                    </span>
                    <span class="font-mono">{{ $weeklyStats['izin']['count'] }} ({{ $weeklyStats['izin']['percentage'] }}%)</span>
                </div>
                <div class="w-full bg-slate-100 border-2 border-black h-4 rounded-sm overflow-hidden p-0.5">
                    <div class="bg-[#A5D8FF] h-full transition-all duration-500 rounded-xs" style="width: {{ $weeklyStats['izin']['percentage'] }}%"></div>
                </div>
            </div>

            <!-- Alpa -->
            <div class="space-y-1.5">
                <div class="flex justify-between text-xs font-bold text-black">
                    <span class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-sm bg-[#FF6B6B] border border-black inline-block"></span>
                        Alpa / Tanpa Keterangan
                    </span>
                    <span class="font-mono">{{ $weeklyStats['alpa']['count'] }} ({{ $weeklyStats['alpa']['percentage'] }}%)</span>
                </div>
                <div class="w-full bg-slate-100 border-2 border-black h-4 rounded-sm overflow-hidden p-0.5">
                    <div class="bg-[#FF6B6B] h-full transition-all duration-500 rounded-xs" style="width: {{ $weeklyStats['alpa']['percentage'] }}%"></div>
                </div>
            </div>
        </div>

        @if($weeklyStats['total'] === 0)
            <div class="text-center text-xs text-slate-500 py-2 border-t-2 border-slate-100">
                Belum ada data presensi yang tercatat dalam 7 hari terakhir.
            </div>
        @endif
    </div>
</div>
