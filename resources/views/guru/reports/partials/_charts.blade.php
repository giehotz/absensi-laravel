<!-- Neobrutalism Charts Grid -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 print:hidden">
    <!-- Chart 1: Donut Chart -->
    <div class="lg:col-span-5 bg-white border-2 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000] flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-1">
                <h3 class="font-heading font-black text-base text-black flex items-center gap-2">
                    <span class="w-3 h-3 bg-[#FFD43B] border border-black inline-block"></span>
                    Distribusi Status Kehadiran
                </h3>
                <span class="neo-badge bg-white text-black text-[10px]">Proporsi</span>
            </div>
            <p class="text-xs text-slate-500 mb-4">
                Komposisi persentase kehadiran siswa selama rentang periode yang dipilih.
            </p>

            <div class="relative w-full aspect-square max-h-[260px] mx-auto flex items-center justify-center">
                <canvas id="donutChart"></canvas>
            </div>
        </div>

        <!-- Legend Chips -->
        <div class="grid grid-cols-3 sm:grid-cols-5 gap-2 pt-4 mt-4 border-t-2 border-slate-100 text-center">
            <div class="p-1.5 bg-[#D3F9D8] border border-black rounded">
                <div class="text-[10px] font-bold text-emerald-950 uppercase">Hadir</div>
                <div class="font-heading font-black text-xs text-black">{{ $totalHadir }}</div>
            </div>
            <div class="p-1.5 bg-[#FFF3BF] border border-black rounded">
                <div class="text-[10px] font-bold text-amber-950 uppercase">Terlambat</div>
                <div class="font-heading font-black text-xs text-black">{{ $totalTerlambat }}</div>
            </div>
            <div class="p-1.5 bg-[#D0EBFF] border border-black rounded">
                <div class="text-[10px] font-bold text-blue-950 uppercase">Izin</div>
                <div class="font-heading font-black text-xs text-black">{{ $totalIzin }}</div>
            </div>
            <div class="p-1.5 bg-[#F3D9FA] border border-black rounded">
                <div class="text-[10px] font-bold text-purple-950 uppercase">Sakit</div>
                <div class="font-heading font-black text-xs text-black">{{ $totalSakit }}</div>
            </div>
            <div class="p-1.5 bg-[#FFE3E3] border border-black rounded">
                <div class="text-[10px] font-bold text-rose-950 uppercase">Alpa</div>
                <div class="font-heading font-black text-xs text-black">{{ $totalAlpa }}</div>
            </div>
        </div>
    </div>

    <!-- Chart 2: Bar Chart (Tren Harian) -->
    <div class="lg:col-span-7 bg-white border-2 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000] flex flex-col justify-between">
        <div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-1">
                <h3 class="font-heading font-black text-base text-black flex items-center gap-2">
                    <span class="w-3 h-3 bg-[#5294FF] border border-black inline-block"></span>
                    Tren Kehadiran Harian ({{ $weekPeriodLabel }})
                </h3>
                <div class="flex items-center gap-1.5">
                    <!-- Minggu Sebelumnya -->
                    <a href="{{ $prevWeekUrl }}" 
                       class="relative group neo-btn bg-white hover:bg-slate-100 text-black w-8 h-8 flex items-center justify-center text-sm font-black shadow-[2px_2px_0px_0px_#000] cursor-pointer" 
                       title="Minggu Sebelumnya"
                       aria-label="Minggu Sebelumnya">
                        <span>←</span>
                        <span class="pointer-events-none absolute bottom-full mb-1.5 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-all duration-150 z-30 whitespace-nowrap bg-black text-white text-[11px] font-bold px-2 py-1 rounded shadow-[2px_2px_0px_#000] border border-black scale-90 group-hover:scale-100">
                            Minggu Sebelumnya
                            <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-black"></span>
                        </span>
                    </a>

                    <!-- Minggu Ini -->
                    <a href="{{ $thisWeekUrl }}" 
                       class="relative group neo-btn {{ $isThisWeek ? 'bg-[#5294FF] text-white shadow-[2px_2px_0px_0px_#000]' : 'bg-white hover:bg-slate-100 text-black shadow-[2px_2px_0px_0px_#000]' }} px-3 h-8 flex items-center text-xs font-bold cursor-pointer" 
                       title="Kembali ke minggu berjalan saat ini">
                        Minggu Ini
                        <span class="pointer-events-none absolute bottom-full mb-1.5 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-all duration-150 z-30 whitespace-nowrap bg-black text-white text-[11px] font-bold px-2 py-1 rounded shadow-[2px_2px_0px_#000] border border-black scale-90 group-hover:scale-100">
                            Minggu Berjalan Saat Ini
                            <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-black"></span>
                        </span>
                    </a>

                    <!-- Minggu Selanjutnya -->
                    <a href="{{ $nextWeekUrl }}" 
                       class="relative group neo-btn bg-white hover:bg-slate-100 text-black w-8 h-8 flex items-center justify-center text-sm font-black shadow-[2px_2px_0px_0px_#000] cursor-pointer" 
                       title="Minggu Selanjutnya"
                       aria-label="Minggu Selanjutnya">
                        <span>→</span>
                        <span class="pointer-events-none absolute bottom-full mb-1.5 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-all duration-150 z-30 whitespace-nowrap bg-black text-white text-[11px] font-bold px-2 py-1 rounded shadow-[2px_2px_0px_#000] border border-black scale-90 group-hover:scale-100">
                            Minggu Selanjutnya
                            <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-black"></span>
                        </span>
                    </a>
                </div>
            </div>
            <p class="text-xs text-slate-500 mb-4">
                Fluktuasi kehadiran harian siswa dari hari Senin sampai Sabtu pada rentang tanggal aktif.
            </p>

            <div class="relative w-full h-[260px]">
                <canvas id="barChart"></canvas>
            </div>
        </div>

        <!-- Legend Info -->
        <div class="flex items-center justify-center gap-4 pt-3 mt-4 border-t-2 border-slate-100 text-xs font-bold flex-wrap">
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 bg-[#20C997] border border-black inline-block rounded-xs"></span> Hadir
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 bg-[#FFD43B] border border-black inline-block rounded-xs"></span> Terlambat
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 bg-[#5294FF] border border-black inline-block rounded-xs"></span> Izin/Sakit
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 bg-[#FF6B6B] border border-black inline-block rounded-xs"></span> Alpa
            </span>
        </div>
    </div>
</div>
