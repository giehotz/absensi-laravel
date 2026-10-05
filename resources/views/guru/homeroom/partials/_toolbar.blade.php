<!-- Filter & Aksi Cepat Toolbar (Adapted, Optimized, Colorized Neo-Brutalism) -->
<div class="bg-white neo-box p-4 sm:p-5 space-y-4 border-2 border-black shadow-[3px_3px_0px_0px_#000]">
    <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
        <!-- Class Selector & Search Form -->
        <form action="{{ route('guru.classes.binaan') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto">
            <!-- Dropdown Kelas (jika wali kelas mengampu > 1 kelas) -->
            @if($homeroomClasses->count() > 1)
                <div class="w-full sm:w-auto">
                    <label class="block text-[11px] font-heading font-black uppercase text-black mb-1">
                        Pilih Kelas Binaan:
                    </label>
                    <select name="school_class_id" onchange="this.form.submit()"
                            class="w-full sm:w-auto bg-white border-2 border-black px-3 py-2 text-xs font-bold text-black focus:outline-hidden min-h-[42px]">
                        @foreach($homeroomClasses as $c)
                            <option value="{{ $c->id }}" {{ $selectedClassId == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} (Tingkat {{ $c->level }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="school_class_id" value="{{ $selectedClassId }}">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-heading font-black uppercase bg-[#D3F9D8] text-emerald-950 border-2 border-black px-3 py-2 rounded-xs shadow-[1.5px_1.5px_0px_#000] flex items-center gap-1.5">
                        <span>🏫</span>
                        <span>Kelas: <b>{{ $selectedClass->name ?? '-' }}</b></span>
                    </span>
                    <span class="text-xs font-mono font-bold bg-slate-100 border border-black px-2.5 py-1.5 rounded-xs">
                        Tk. {{ $selectedClass->level ?? '-' }} • {{ $selectedClass->academicYear->name ?? 'Tahun Aktif' }}
                    </span>
                </div>
            @endif

            <!-- Pencarian Siswa -->
            <div class="flex items-center gap-2 w-full sm:w-auto flex-1 sm:flex-initial">
                <div class="relative w-full sm:w-64">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari: Nama / NIS / NISN..."
                           class="w-full bg-slate-50 border-2 border-black px-3 py-2 text-xs font-bold focus:bg-white focus:outline-hidden min-h-[42px]">
                    @if($search)
                        <a href="{{ route('guru.classes.binaan', ['school_class_id' => $selectedClassId]) }}" 
                           class="absolute right-2.5 top-2.5 text-xs font-black text-slate-500 hover:text-black"
                           title="Hapus pencarian">✕</a>
                    @endif
                </div>
                <button type="submit" 
                        class="neo-btn bg-black text-white hover:bg-slate-800 text-xs font-heading font-black px-4 py-2 min-h-[42px] border-2 border-black shadow-[2px_2px_0px_#000] cursor-pointer transition-transform active:translate-x-0.5 active:translate-y-0.5 shrink-0">
                    Cari
                </button>
            </div>
        </form>

        <!-- Quick Action Buttons: High Touch Ergonomics -->
        <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2 w-full lg:w-auto justify-start lg:justify-end">
            <!-- 1. Input Presensi Hari Ini -->
            <a href="{{ route('guru.attendance.manual', ['school_class_id' => $selectedClass->id]) }}" 
               class="neo-btn bg-[#20C997] hover:bg-emerald-400 text-black text-xs font-heading font-black px-3 py-2 min-h-[42px] flex items-center justify-center gap-1.5 border-2 border-black shadow-[2px_2px_0px_#000] transition-transform active:translate-x-0.5 active:translate-y-0.5 cursor-pointer">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <span>Input Presensi Hari Ini</span>
            </a>

            <!-- 2. Rekap Kehadiran -->
            <a href="{{ route('guru.reports.attendance', ['school_class_id' => $selectedClass->id]) }}" 
               class="neo-btn bg-[#339AF0] hover:bg-blue-600 text-white text-xs font-heading font-black px-3 py-2 min-h-[42px] flex items-center justify-center gap-1.5 border-2 border-black shadow-[2px_2px_0px_#000] transition-transform active:translate-x-0.5 active:translate-y-0.5 cursor-pointer">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>Rekap Absen</span>
            </a>

            <!-- 3. Unduh Excel (.xlsx) -->
            <a href="{{ route('guru.classes.binaan.export', ['school_class_id' => $selectedClass->id]) }}" 
               class="neo-btn bg-[#FFD43B] hover:bg-yellow-400 text-black text-xs font-heading font-black px-3 py-2 min-h-[42px] flex items-center justify-center gap-1.5 border-2 border-black shadow-[2px_2px_0px_#000] transition-transform active:translate-x-0.5 active:translate-y-0.5 cursor-pointer">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>Unduh Excel (.xlsx)</span>
            </a>

            <!-- 4. Cetak -->
            <button type="button" onclick="window.print()" 
                    class="neo-btn bg-white hover:bg-slate-100 text-black text-xs font-heading font-black px-3 py-2 min-h-[42px] flex items-center justify-center gap-1.5 border-2 border-black shadow-[2px_2px_0px_#000] transition-transform active:translate-x-0.5 active:translate-y-0.5 cursor-pointer">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Cetak</span>
            </button>
        </div>
    </div>
</div>
