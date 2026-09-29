<!-- Top Playful Student Header Card -->
<div class="bg-white border-3 border-black rounded-2xl p-4 sm:p-5 flex items-center justify-between gap-3 shadow-[4px_4px_0px_0px_#000] relative overflow-hidden">
    <!-- Clickable Profile Area -->
    <div onclick="switchTab('profil')" class="flex items-center gap-3.5 min-w-0 cursor-pointer group" title="Buka Profil Lengkap Siswa">
        <!-- Student Avatar with Online Badge -->
        <div class="relative shrink-0">
            @if($student->photo_url)
                <img src="{{ $student->photo_url }}" alt="Foto {{ $student->user->name }}" class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl border-2 border-black object-cover shadow-[2px_2px_0px_0px_#000] group-hover:scale-105 transition-transform">
            @else
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-[#5294FF] border-2 border-black flex items-center justify-center font-heading font-black text-2xl text-white shadow-[2px_2px_0px_0px_#000] group-hover:scale-105 transition-transform">
                    {{ strtoupper(substr($student->user->name ?? 'S', 0, 1)) }}
                </div>
            @endif
            <!-- Active indicator dot -->
            <span class="absolute -bottom-1 -right-1 w-4 h-4 bg-[#20C997] border-2 border-black rounded-full flex items-center justify-center shadow-[1px_1px_0px_0px_#000]" title="Status: Siswa Aktif">
                <span class="w-1.5 h-1.5 bg-white rounded-full"></span>
            </span>
        </div>

        <!-- Student Meta -->
        <div class="min-w-0">
            <div class="flex items-center gap-1.5 flex-wrap">
                <span class="bg-[#5294FF] text-white text-[10px] font-black uppercase px-2 py-0.5 rounded-md border border-black shadow-[1px_1px_0px_0px_#000] leading-none">
                    Siswa
                </span>
                <span class="text-[10px] font-mono font-bold bg-[#FFF9DB] text-amber-950 px-2 py-0.5 rounded-md border border-black shadow-[1px_1px_0px_0px_#000] leading-none">
                    NIS: {{ $student->nis }}
                </span>
                <span class="text-[10px] font-bold text-blue-700 group-hover:underline hidden sm:inline">
                    Lihat Profil →
                </span>
            </div>
            
            <h1 class="font-heading font-black text-base sm:text-lg text-black truncate mt-1 leading-tight group-hover:text-blue-700 transition-colors">
                {{ $student->user->name }}
            </h1>
            
            <div class="flex items-center gap-1.5 mt-0.5 text-xs font-bold text-slate-600 truncate">
                <span class="bg-[#E7F5FF] text-blue-950 px-1.5 py-0.2 rounded border border-black text-[10px] font-black">
                    Kelas {{ $student->schoolClass->name ?? '-' }}
                </span>
                <span class="text-slate-400">•</span>
                <span class="text-[11px] text-slate-600 truncate">
                    {{ $student->schoolClass->academicYear->name ?? '2026/2027' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Quick Fullscreen QR Button (Punchy Neo-Brutalist CTA) -->
    <button type="button" onclick="openModal('modalFullscreenQr')" class="p-2.5 sm:px-3 sm:py-2.5 rounded-xl bg-[#FFD43B] hover:bg-yellow-400 text-black border-2 border-black shadow-[3px_3px_0px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[1px_1px_0px_0px_#000] active:translate-x-1 active:translate-y-1 active:shadow-none transition-all flex flex-col items-center justify-center shrink-0 cursor-pointer group" title="Tampilkan QR Token Layar Penuh">
        <svg class="w-5 h-5 text-black group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
        </svg>
        <span class="text-[9px] font-black uppercase tracking-wider mt-0.5 font-heading">QR SCAN</span>
    </button>
</div>
