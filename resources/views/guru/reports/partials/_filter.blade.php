<!-- Filter Card -->
<div class="bg-white border-2 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000] print:hidden space-y-4">
    <!-- Quick Weekly Navigator Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-[#E7F5FF] border-2 border-black p-3.5 rounded-lg">
        <div class="flex items-center gap-2.5">
            <span class="text-xl">📅</span>
            <div>
                <div class="text-[10px] font-black uppercase text-blue-900 tracking-wider">Rekapitulasi Mingguan (Senin — Sabtu)</div>
                <div class="font-heading font-black text-sm text-black">{{ $weekPeriodLabel }}</div>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-1.5">
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

    <form method="GET" action="{{ route('guru.reports.attendance') }}" class="space-y-4 pt-1">
        <input type="hidden" name="tab" value="{{ $activeTab }}">

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Tanggal Mulai -->
            <div>
                <label class="block text-xs font-bold text-black uppercase tracking-wider mb-1.5">
                    Tanggal Mulai
                </label>
                <input type="date" 
                       name="start_date" 
                       value="{{ $startDate }}"
                       class="w-full bg-slate-50 border-2 border-black px-3 py-2 text-sm font-medium focus:bg-white focus:outline-hidden">
            </div>

            <!-- Tanggal Selesai -->
            <div>
                <label class="block text-xs font-bold text-black uppercase tracking-wider mb-1.5">
                    Tanggal Selesai
                </label>
                <input type="date" 
                       name="end_date" 
                       value="{{ $endDate }}"
                       class="w-full bg-slate-50 border-2 border-black px-3 py-2 text-sm font-medium focus:bg-white focus:outline-hidden">
            </div>

            <!-- Dropdown Kelas (Hanya Kelas Guru) -->
            <div>
                <label class="block text-xs font-bold text-black uppercase tracking-wider mb-1.5">
                    Kelas
                </label>
                <select name="school_class_id" class="w-full bg-slate-50 border-2 border-black px-3 py-2 text-sm font-medium focus:bg-white focus:outline-hidden">
                    <option value="all" {{ $schoolClassId == 'all' || !$schoolClassId ? 'selected' : '' }}>Semua Kelas Binaan/Ajar</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $schoolClassId == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} (Tingkat {{ $c->level }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Dropdown Status -->
            <div>
                <label class="block text-xs font-bold text-black uppercase tracking-wider mb-1.5">
                    Status Presensi
                </label>
                <select name="status" class="w-full bg-slate-50 border-2 border-black px-3 py-2 text-sm font-medium focus:bg-white focus:outline-hidden">
                    <option value="all" {{ $status == 'all' || !$status ? 'selected' : '' }}>Semua Status</option>
                    <option value="hadir" {{ $status == 'hadir' ? 'selected' : '' }}>Hadir</option>
                    <option value="terlambat" {{ $status == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                    <option value="izin" {{ $status == 'izin' ? 'selected' : '' }}>Izin</option>
                    <option value="sakit" {{ $status == 'sakit' ? 'selected' : '' }}>Sakit</option>
                    <option value="alpa" {{ $status == 'alpa' ? 'selected' : '' }}>Alpa</option>
                </select>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2 border-t-2 border-slate-100">
            <a href="{{ route('guru.reports.attendance') }}" 
               class="neo-btn bg-slate-100 hover:bg-slate-200 text-black px-4 py-2 text-xs font-bold cursor-pointer">
                Reset Filter
            </a>
            <button type="submit" 
                    class="neo-btn bg-[#5294FF] hover:bg-blue-600 text-white px-5 py-2 text-xs font-bold flex items-center gap-1.5 cursor-pointer shadow-[2px_2px_0px_0px_#000]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <span>Terapkan Filter</span>
            </button>
        </div>
    </form>
</div>
