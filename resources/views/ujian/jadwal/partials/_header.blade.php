{{-- Header Card Neo-Brutalism Terpadu --}}
<div class="bg-white border-4 border-black rounded-xl shadow-[4px_4px_0px_0px_#000] overflow-hidden">
    {{-- Tier 1: Judul, Subtitle & Aksi Utama --}}
    <div class="p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b-2 border-black">
        <div class="min-w-0">
            <div class="flex items-center gap-2 mb-1.5">
                <span class="text-xs font-black uppercase tracking-wider bg-[#FFD43B] text-black px-2.5 py-0.5 rounded-md border-2 border-black shadow-[1.5px_1.5px_0px_0px_#000]">
                    Sub-Menu 2
                </span>
                <span class="text-xs font-bold text-slate-600">Anti-Bentrok & Duplikasi Cepat</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black font-heading text-black tracking-tight">
                Jadwal Mata Pelajaran Ujian
            </h1>
            <p class="text-xs font-semibold text-slate-600 mt-1 max-w-2xl leading-relaxed">
                Mengatur slot waktu ujian per rombel/tingkat menggunakan daftar {{ $subjects->count() }} mata pelajaran madrasah yang telah terdaftar.
            </p>
        </div>

        @if($activePeriode)
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" onclick="document.getElementById('modalTambahJadwal').classList.remove('hidden')" 
                        class="px-5 py-2.5 bg-[#FFD43B] hover:bg-yellow-400 text-black border-2 border-black rounded-lg text-xs sm:text-sm font-black shadow-[3px_3px_0px_0px_#000] flex items-center gap-2 cursor-pointer transition-all active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                    <span class="text-base font-black leading-none">+</span>
                    <span>Tambah Slot Jadwal</span>
                </button>
            </div>
        @endif
    </div>

    {{-- Tier 2: Toolbar Periode & Total Slot --}}
    <div class="px-6 py-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-200 bg-white">
        <form method="GET" action="{{ route('ujian.jadwal.index') }}" class="flex items-center gap-3">
            <div class="flex items-center gap-2 shrink-0">
                <svg class="h-4 w-4 text-black" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                </svg>
                <label for="header_periode_id" class="text-xs font-black uppercase tracking-wider text-black">Periode</label>
            </div>
            <select id="header_periode_id" name="periode_id" onchange="this.form.submit()"
                    class="cursor-pointer rounded-lg border-2 border-black bg-white px-3.5 py-1.5 text-xs font-black text-slate-900 shadow-[2px_2px_0px_0px_#000] focus:outline-none">
                @foreach($periodes as $p)
                    <option value="{{ $p->id }}" {{ $p->id == $activePeriode?->id ? 'selected' : '' }}>
                        {{ $p->nama_periode }} {{ $p->is_active ? '★' : '' }}
                    </option>
                @endforeach
            </select>
        </form>

        <div class="flex items-center gap-2 self-end sm:self-auto shrink-0">
            <span class="text-xs font-bold text-slate-600">Total:</span>
            <span class="bg-white px-2.5 py-0.5 rounded-md border-2 border-black font-black text-xs text-black shadow-[1.5px_1.5px_0px_0px_#000]">
                {{ $jadwals->count() }} Slot
            </span>
        </div>
    </div>

    {{-- Tier 3: Cetak & Aksi Duplikasi/Upload --}}
    @if($activePeriode)
        <div class="px-6 py-3.5 flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white">
            {{-- Kiri: Cetak Segmented Button Group --}}
            <div class="inline-flex rounded-lg border-2 border-black shadow-[2px_2px_0px_0px_#000] overflow-hidden bg-white shrink-0">
                <a href="{{ route('ujian.jadwal.print', ['periode_id' => $activePeriode->id, 'format' => 'standar']) }}" target="_blank"
                   class="px-3.5 py-1.5 bg-white hover:bg-slate-100 text-black text-xs font-black flex items-center gap-1.5 cursor-pointer border-r-2 border-black transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/>
                    </svg>
                    <span>Standar</span>
                </a>
                <a href="{{ route('ujian.jadwal.print', ['periode_id' => $activePeriode->id, 'format' => 'matriks_rombel', 'kelompok' => '1-2']) }}" target="_blank"
                   title="Cetak Jadwal Mapel per Rombel (Sheet 1: Kelompok 1–2)"
                   class="px-3.5 py-1.5 bg-[#4DABF7] hover:bg-blue-400 text-black text-xs font-black flex items-center gap-1.5 cursor-pointer border-r-2 border-black transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                    <span>Sheet 1</span>
                </a>
                <a href="{{ route('ujian.jadwal.print', ['periode_id' => $activePeriode->id, 'format' => 'rekap']) }}" target="_blank"
                   title="Cetak Format Rekap Matriks 1 Lembar Landscape (Seperti jadwal.pdf)"
                   class="px-3.5 py-1.5 bg-[#FFD43B] hover:bg-yellow-400 text-black text-xs font-black flex items-center gap-1.5 cursor-pointer transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                    </svg>
                    <span>Rekap</span>
                </a>
            </div>

            {{-- Kanan: Aksi Operasional --}}
            <div class="flex flex-wrap items-center gap-2.5">
                {{-- Upload Template --}}
                <button type="button" onclick="document.getElementById('modalImportJadwal').classList.remove('hidden')"
                        title="Upload Template Excel atau PDF Matriks Jadwal Ujian"
                        class="px-3.5 py-1.5 bg-[#51CF66] hover:bg-green-500 text-black border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer transition-all active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                    </svg>
                    <span>Upload Template</span>
                </button>

                {{-- Salin Antar Tingkat --}}
                <button type="button" onclick="document.getElementById('modalSalinJadwalTingkat').classList.remove('hidden')" 
                        class="px-3.5 py-1.5 bg-[#845EF7] hover:bg-purple-600 text-white border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer transition-all active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 7.5V6.108c0-1.135.845-2.098 1.976-2.192.373-.03.748-.057 1.123-.08M15.75 18H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08M15.75 18.75v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75"/>
                    </svg>
                    <span>Salin Tingkat</span>
                </button>

                {{-- Salin Antar Rombel --}}
                <button type="button" onclick="document.getElementById('modalSalinJadwal').classList.remove('hidden')" 
                        class="px-3.5 py-1.5 bg-[#339AF0] hover:bg-blue-500 text-white border-2 border-black rounded-lg text-xs font-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer transition-all active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75"/>
                    </svg>
                    <span>Salin Rombel</span>
                </button>
            </div>
        </div>
    @endif
</div>
