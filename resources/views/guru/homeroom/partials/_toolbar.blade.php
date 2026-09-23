<!-- Filter & Aksi Cepat Toolbar -->
<div class="bg-white neo-box p-5 space-y-4">
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
        <!-- Class Selector & Search -->
        <form action="{{ route('guru.classes.binaan') }}" method="GET" class="flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full lg:w-auto">
            <!-- Dropdown Kelas (jika > 1) -->
            @if($homeroomClasses->count() > 1)
                <div>
                    <label class="block text-[11px] font-black uppercase text-black mb-1">Pilih Kelas Binaan:</label>
                    <select name="school_class_id" onchange="this.form.submit()"
                            class="bg-white border-2 border-black px-3 py-2 text-xs font-bold text-black focus:outline-hidden focus:ring-2 focus:ring-black">
                        @foreach($homeroomClasses as $c)
                            <option value="{{ $c->id }}" {{ $selectedClassId == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} (Tingkat {{ $c->level }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="school_class_id" value="{{ $selectedClassId }}">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-black uppercase bg-[#D3F9D8] text-emerald-950 border-2 border-black px-3 py-1.5 rounded-sm">
                        🏫 Kelas: <b>{{ $selectedClass->name ?? '-' }}</b>
                    </span>
                    <span class="text-xs font-bold bg-slate-100 border border-black px-2 py-1 rounded-sm">
                        Tingkat {{ $selectedClass->level ?? '-' }} • {{ $selectedClass->academicYear->name ?? 'Tahun Aktif' }}
                    </span>
                </div>
            @endif

            <!-- Pencarian Siswa -->
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <div class="relative w-full sm:w-64">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari Siswa: Nama / NIS / NISN..."
                           class="w-full bg-slate-50 border-2 border-black px-3 py-1.5 text-xs font-bold focus:bg-white focus:outline-hidden">
                    @if($search)
                        <a href="{{ route('guru.classes.binaan', ['school_class_id' => $selectedClassId]) }}" 
                           class="absolute right-2 top-1.5 text-xs font-black text-slate-500 hover:text-black">✕</a>
                    @endif
                </div>
                <button type="submit" class="neo-btn bg-black text-white text-xs px-3 py-1.5 font-bold cursor-pointer hover:bg-slate-800 shrink-0">
                    Cari
                </button>
            </div>
        </form>

        <!-- Quick Action Buttons dengan Tooltips -->
        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto justify-start lg:justify-end">
            <a href="{{ route('guru.attendance.manual', ['school_class_id' => $selectedClass->id]) }}" 
               title="Input Presensi Hari Ini"
               class="relative group neo-btn bg-[#20C997] text-black text-xs font-bold px-3 py-1.5 flex items-center gap-1.5 hover:bg-emerald-400 cursor-pointer">
                <span>💾</span>
                <span>Simpan</span>
                <span class="pointer-events-none absolute bottom-full mb-1.5 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-all duration-150 z-30 whitespace-nowrap bg-black text-white text-[11px] font-bold px-2 py-1 rounded shadow-[2px_2px_0px_#000] border border-black scale-90 group-hover:scale-100">
                    Input Presensi Hari Ini
                    <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-black"></span>
                </span>
            </a>
            <a href="{{ route('guru.reports.attendance', ['school_class_id' => $selectedClass->id]) }}" 
               title="Rekap Kehadiran"
               class="relative group neo-btn bg-[#5294FF] text-white text-xs font-bold px-3 py-1.5 flex items-center gap-1.5 hover:bg-blue-600 cursor-pointer">
                <span>📊</span>
                <span>Rekap</span>
                <span class="pointer-events-none absolute bottom-full mb-1.5 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-all duration-150 z-30 whitespace-nowrap bg-black text-white text-[11px] font-bold px-2 py-1 rounded shadow-[2px_2px_0px_#000] border border-black scale-90 group-hover:scale-100">
                    Rekap Kehadiran
                    <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-black"></span>
                </span>
            </a>
            <a href="{{ route('guru.classes.binaan.export', ['school_class_id' => $selectedClass->id]) }}" 
               title="Unduh Excel (.xlsx)"
               class="relative group neo-btn bg-emerald-100 text-emerald-950 text-xs font-bold px-3 py-1.5 flex items-center gap-1.5 hover:bg-emerald-200 cursor-pointer">
                <span>📥</span>
                <span>Unduh</span>
                <span class="pointer-events-none absolute bottom-full mb-1.5 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-all duration-150 z-30 whitespace-nowrap bg-black text-white text-[11px] font-bold px-2 py-1 rounded shadow-[2px_2px_0px_#000] border border-black scale-90 group-hover:scale-100">
                    Unduh Excel (.xlsx)
                    <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-black"></span>
                </span>
            </a>
            <button type="button" onclick="window.print()" 
                    title="Cetak"
                    class="relative group neo-btn bg-white text-black text-xs font-bold px-3 py-1.5 flex items-center gap-1.5 hover:bg-slate-100 cursor-pointer">
                <span>🖨️</span>
                <span>Cetak</span>
                <span class="pointer-events-none absolute bottom-full mb-1.5 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-all duration-150 z-30 whitespace-nowrap bg-black text-white text-[11px] font-bold px-2 py-1 rounded shadow-[2px_2px_0px_#000] border border-black scale-90 group-hover:scale-100">
                    Cetak
                    <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-black"></span>
                </span>
            </button>
        </div>
    </div>
</div>
