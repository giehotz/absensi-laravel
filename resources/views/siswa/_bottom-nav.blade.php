<!-- ========================================================================= -->
<!-- FIXED MOBILE-FIRST BOTTOM NAVIGATION BAR (ICON ONLY WITH TOOLTIPS) -->
<!-- ========================================================================= -->
<nav class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t-4 border-black px-3 py-2 shadow-[0_-4px_0px_0px_rgba(0,0,0,0.06)]">
    <div class="max-w-md mx-auto flex items-center justify-around gap-2">
        <!-- 1. Beranda -->
        <button type="button" onclick="switchTab('beranda')" id="navBtn-beranda" 
            class="nav-tab-btn group relative p-2.5 sm:p-3 rounded-lg transition-all text-black bg-[#FFD43B] border-2 border-black shadow-[2px_2px_0px_0px_#000] hover:scale-105 cursor-pointer"
            title="Beranda" aria-label="Beranda">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <!-- Floating Tooltip -->
            <span class="absolute -top-9 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] font-black uppercase px-2 py-0.5 rounded-none pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap border border-black shadow-[2px_2px_0px_0px_#FFD43B] z-50">
                Beranda
            </span>
        </button>

        <!-- 2. QR Presensi -->
        <button type="button" onclick="switchTab('qr')" id="navBtn-qr" 
            class="nav-tab-btn group relative p-2.5 sm:p-3 rounded-lg transition-all text-slate-600 hover:text-black border-2 border-transparent hover:scale-105 cursor-pointer"
            title="QR Presensi" aria-label="QR Presensi">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
            </svg>
            <!-- Floating Tooltip -->
            <span class="absolute -top-9 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] font-black uppercase px-2 py-0.5 rounded-none pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap border border-black shadow-[2px_2px_0px_0px_#FFD43B] z-50">
                QR Presensi
            </span>
        </button>

        <!-- 3. Jadwal -->
        <button type="button" onclick="switchTab('jadwal')" id="navBtn-jadwal" 
            class="nav-tab-btn group relative p-2.5 sm:p-3 rounded-lg transition-all text-slate-600 hover:text-black border-2 border-transparent hover:scale-105 cursor-pointer"
            title="Jadwal Pelajaran" aria-label="Jadwal Pelajaran">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <!-- Floating Tooltip -->
            <span class="absolute -top-9 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] font-black uppercase px-2 py-0.5 rounded-none pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap border border-black shadow-[2px_2px_0px_0px_#FFD43B] z-50">
                Jadwal
            </span>
        </button>

        <!-- 4. Izin -->
        <button type="button" onclick="switchTab('izin')" id="navBtn-izin" 
            class="nav-tab-btn group relative p-2.5 sm:p-3 rounded-lg transition-all text-slate-600 hover:text-black border-2 border-transparent hover:scale-105 cursor-pointer"
            title="Pengajuan Izin / Sakit" aria-label="Pengajuan Izin">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <!-- Floating Tooltip -->
            <span class="absolute -top-9 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] font-black uppercase px-2 py-0.5 rounded-none pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap border border-black shadow-[2px_2px_0px_0px_#FFD43B] z-50">
                Izin
            </span>
        </button>

        <!-- 5. Riwayat -->
        <button type="button" onclick="switchTab('riwayat')" id="navBtn-riwayat" 
            class="nav-tab-btn group relative p-2.5 sm:p-3 rounded-lg transition-all text-slate-600 hover:text-black border-2 border-transparent hover:scale-105 cursor-pointer"
            title="Riwayat Presensi" aria-label="Riwayat Presensi">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            <!-- Floating Tooltip -->
            <span class="absolute -top-9 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] font-black uppercase px-2 py-0.5 rounded-none pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap border border-black shadow-[2px_2px_0px_0px_#FFD43B] z-50">
                Riwayat
            </span>
        </button>
    </div>
</nav>
