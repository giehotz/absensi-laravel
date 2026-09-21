<!-- Top Compact Student Header -->
<div class="bg-white neo-box p-4 flex items-center justify-between gap-3 relative overflow-hidden">
    <div onclick="switchTab('profil')" class="flex items-center gap-3 min-w-0 cursor-pointer group" title="Buka Profil Lengkap Siswa">
        <!-- Student Avatar -->
        @if(!empty($student->photo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($student->photo))
            <img src="{{ asset('storage/' . $student->photo) }}" alt="Foto {{ $student->user->name }}" class="w-13 h-13 rounded-full border-2 border-black object-cover shrink-0 shadow-[2px_2px_0px_0px_#000] group-hover:scale-105 transition-transform">
        @else
            <div class="w-13 h-13 rounded-full bg-[#5294FF] border-2 border-black flex items-center justify-center font-heading font-black text-xl text-white shrink-0 shadow-[2px_2px_0px_0px_#000] group-hover:scale-105 transition-transform">
                {{ strtoupper(substr($student->user->name ?? 'S', 0, 1)) }}
            </div>
        @endif
        <div class="min-w-0">
            <div class="flex items-center gap-1.5 flex-wrap">
                <span class="neo-badge bg-[#5294FF] text-white text-[10px] py-0.5 px-1.5 leading-none">PORTAL SISWA</span>
                <span class="text-[10px] font-mono font-bold bg-[#FFF9DB] px-1.5 py-0.5 border border-black leading-none">
                    NIS: {{ $student->nis }}
                </span>
                <span class="text-[9px] font-bold text-blue-900 group-hover:underline hidden xs:inline">
                    Lihat Profil →
                </span>
            </div>
            <h1 class="font-heading font-black text-base sm:text-lg text-black truncate mt-1 leading-tight group-hover:text-blue-900 transition-colors">
                {{ $student->user->name }}
            </h1>
            <p class="text-xs font-bold text-slate-600 truncate">
                {{ $student->schoolClass->name ?? 'Kelas Siswa' }} • {{ $student->schoolClass->academicYear->name ?? '2026/2027' }}
            </p>
        </div>
    </div>

    <!-- Quick Fullscreen QR Button -->
    <button onclick="openModal('modalFullscreenQr')" class="neo-btn bg-[#FFD43B] hover:bg-yellow-400 p-2.5 flex flex-col items-center justify-center shrink-0 cursor-pointer" title="Tampilkan QR Presensi Layar Penuh">
        <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
        </svg>
        <span class="text-[9px] font-black uppercase tracking-wider mt-0.5">QR SCAN</span>
    </button>
</div>
