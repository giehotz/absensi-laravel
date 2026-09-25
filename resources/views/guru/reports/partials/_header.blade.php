<!-- Header Section -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white border-2 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000]">
    <div>
        <div class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
            <span>Portal Guru</span>
            <span>/</span>
            <span class="text-black">Rekap Kehadiran</span>
        </div>
        <h2 class="font-heading font-black text-2xl text-black">Rekap Kehadiran Siswa</h2>
        <p class="text-xs text-slate-600 mt-0.5">
            Monitoring komprehensif kehadiran siswa untuk kelas binaan dan mata pelajaran yang Anda ampu.
        </p>
    </div>

    <div class="flex flex-wrap items-center gap-2.5 print:hidden">
        <!-- Tombol Export Excel -->
        <a href="{{ route('guru.reports.attendance.export', request()->all()) }}" 
           class="neo-btn bg-[#20C997] hover:bg-[#1bb386] text-black px-4 py-2 text-xs font-bold flex items-center gap-2 cursor-pointer shadow-[3px_3px_0px_0px_#000]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span>Export Excel (.xlsx)</span>
        </a>

        <!-- Tombol Cetak / Print -->
        <button onclick="window.print()" 
                class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-4 py-2 text-xs font-bold flex items-center gap-2 cursor-pointer shadow-[3px_3px_0px_0px_#000]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            <span>Cetak Dokumen</span>
        </button>
    </div>
</div>
