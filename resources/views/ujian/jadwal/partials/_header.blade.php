{{-- Header Card Neo-Brutalism Terpadu --}}
<div class="bg-white border-[3px] border-black rounded-2xl shadow-[6px_6px_0px_0px_#000] overflow-hidden">
    {{-- Tier 1: Judul, Subtitle & Aksi Utama --}}
    <div class="p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b-2 border-black">
        <div class="min-w-0">
            <div class="flex items-center gap-2.5 mb-2">
                <span class="text-xs font-black uppercase tracking-wider bg-[#FFDE59] text-black px-3 py-1 rounded-lg border-2 border-black">
                    SUB-MENU 2
                </span>
                <span class="text-xs sm:text-sm font-bold text-slate-600">Anti-Bentrok & Duplikasi Cepat</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black font-heading text-black tracking-tight">
                Jadwal Mata Pelajaran Ujian
            </h1>
            <p class="text-xs sm:text-sm font-semibold text-slate-600 mt-1 max-w-2xl leading-relaxed">
                Mengatur slot waktu ujian per rombel/tingkat menggunakan daftar {{ $subjects->count() }} mata pelajaran madrasah yang telah terdaftar.
            </p>
        </div>

        @if($activePeriode)
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" onclick="document.getElementById('modalTambahJadwal').classList.remove('hidden')" 
                        class="px-5 py-2.5 bg-[#FFDE59] hover:bg-yellow-400 text-black border-2 border-black rounded-xl text-xs sm:text-sm font-black shadow-[3px_3px_0px_0px_#000] flex items-center gap-2 cursor-pointer transition-all active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                    <span class="text-base font-black leading-none">+</span>
                    <span>Tambah Slot Jadwal</span>
                </button>
            </div>
        @endif
    </div>

    {{-- Tier 2: Toolbar Periode & Total Slot --}}
    <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-200 bg-white">
        <form method="GET" action="{{ route('ujian.jadwal.index') }}" class="flex items-center gap-3">
            <div class="flex items-center gap-2 shrink-0">
                <svg class="h-4 w-4 text-black" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                </svg>
                <label for="header_periode_id" class="text-xs font-black uppercase tracking-wider text-black">PERIODE</label>
            </div>
            <div class="relative inline-block">
                <select id="header_periode_id" name="periode_id" onchange="this.form.submit()"
                        class="cursor-pointer appearance-none rounded-xl border-2 border-black bg-white pl-4 pr-10 py-1.5 text-xs sm:text-sm font-black text-slate-900 shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                    @foreach($periodes as $p)
                        <option value="{{ $p->id }}" {{ $p->id == $activePeriode?->id ? 'selected' : '' }}>
                            {{ $p->nama_periode }} {{ $p->is_active ? '★' : '' }}
                        </option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-black">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </div>
            </div>
        </form>

        <div class="flex items-center gap-2 self-end sm:self-auto shrink-0">
            <span class="text-xs font-bold text-slate-600">Total:</span>
            <span class="bg-white px-3 py-1 rounded-lg border-2 border-black font-black text-xs sm:text-sm text-black shadow-[2px_2px_0px_0px_#000]">
                {{ $jadwals->count() }} Slot
            </span>
        </div>
    </div>

    {{-- Tier 3: Cetak & Aksi Duplikasi/Upload --}}
    @if($activePeriode)
        <div class="px-6 py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white">
            {{-- Kiri: Cetak Segmented Button Group --}}
            <div class="inline-flex rounded-xl border-2 border-black shadow-[3px_3px_0px_0px_#000] overflow-hidden bg-white shrink-0">
                <a href="{{ route('ujian.jadwal.print', ['periode_id' => $activePeriode->id, 'format' => 'standar']) }}" target="_blank"
                   class="px-4 py-2 bg-white hover:bg-slate-50 text-black text-xs sm:text-sm font-black flex items-center gap-2 cursor-pointer border-r-2 border-black transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6m-12 5h12v8H6v-8z"/>
                    </svg>
                    <span>Standar</span>
                </a>
                <a href="{{ route('ujian.jadwal.print', ['periode_id' => $activePeriode->id, 'format' => 'matriks_rombel', 'kelompok' => '1-2']) }}" target="_blank"
                   title="Cetak Jadwal Mapel per Rombel (Sheet 1: Kelompok 1–2)"
                   class="px-4 py-2 bg-[#74C0FC] hover:bg-[#4DABF7] text-black text-xs sm:text-sm font-black flex items-center gap-2 cursor-pointer border-r-2 border-black transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z" fill="#93C5FD"/>
                        <path d="M14 2v6h6" fill="#60A5FA"/>
                        <line x1="8" y1="13" x2="16" y2="13" stroke="#1E40AF" stroke-width="1.5"/>
                        <line x1="8" y1="17" x2="13" y2="17" stroke="#1E40AF" stroke-width="1.5"/>
                    </svg>
                    <span>Sheet 1</span>
                </a>
                <a href="{{ route('ujian.jadwal.print', ['periode_id' => $activePeriode->id, 'format' => 'rekap']) }}" target="_blank"
                   title="Cetak Format Rekap Matriks 1 Lembar Landscape (Seperti jadwal.pdf)"
                   class="px-4 py-2 bg-[#FFDE59] hover:bg-yellow-400 text-black text-xs sm:text-sm font-black flex items-center gap-2 cursor-pointer transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                        <rect x="3" y="12" width="4.5" height="9" rx="1" fill="#38BDF8"/>
                        <rect x="9.5" y="7" width="4.5" height="14" rx="1" fill="#4ADE80"/>
                        <rect x="16" y="3" width="4.5" height="18" rx="1" fill="#FBBF24"/>
                    </svg>
                    <span>Rekap</span>
                </a>
            </div>

            {{-- Kanan: Aksi Operasional --}}
            <div class="flex flex-wrap items-center gap-3">
                {{-- Upload Template --}}
                <button type="button" onclick="document.getElementById('modalImportJadwal').classList.remove('hidden')"
                        title="Upload Template Excel atau PDF Matriks Jadwal Ujian"
                        class="px-4 py-2 bg-[#51CF66] hover:bg-green-500 text-black border-2 border-black rounded-xl text-xs sm:text-sm font-black shadow-[3px_3px_0px_0px_#000] flex items-center gap-2 cursor-pointer transition-all active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" x2="12" y1="3" y2="15"/>
                    </svg>
                    <span>Upload Template</span>
                </button>

                {{-- Salin Antar Tingkat --}}
                <button type="button" onclick="document.getElementById('modalSalinJadwalTingkat').classList.remove('hidden')" 
                        class="px-4 py-2 bg-[#845EF7] hover:bg-purple-600 text-white border-2 border-black rounded-xl text-xs sm:text-sm font-black shadow-[3px_3px_0px_0px_#000] flex items-center gap-2 cursor-pointer transition-all active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m17 2 4 4-4 4"/>
                        <path d="M3 11v-1a4 4 0 0 1 4-4h14"/>
                        <path d="m7 22-4-4 4-4"/>
                        <path d="M21 13v1a4 4 0 0 1-4 4H3"/>
                    </svg>
                    <span>Salin Tingkat</span>
                </button>

                {{-- Salin Antar Rombel --}}
                <button type="button" onclick="document.getElementById('modalSalinJadwal').classList.remove('hidden')" 
                        class="px-4 py-2 bg-[#339AF0] hover:bg-blue-500 text-white border-2 border-black rounded-xl text-xs sm:text-sm font-black shadow-[3px_3px_0px_0px_#000] flex items-center gap-2 cursor-pointer transition-all active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="13" height="13" x="9" y="9" rx="2" ry="2"/>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                    </svg>
                    <span>Salin Rombel</span>
                </button>
            </div>
        </div>
    @endif
</div>
