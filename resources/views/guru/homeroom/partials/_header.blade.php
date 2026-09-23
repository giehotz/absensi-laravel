<!-- Header Title Card -->
<div class="bg-[#20C997] neo-box-lg p-6 sm:p-8 text-black relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
    <div class="space-y-1.5 z-10">
        <div class="flex items-center gap-2">
            <span class="neo-badge bg-black text-white">PORTAL WALI KELAS</span>
            <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border border-black">
                NIP: {{ $teacher->nip ?? '-' }}
            </span>
            <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border border-black hidden sm:inline-block">
                {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
            </span>
        </div>
        <h1 class="font-heading text-2xl sm:text-3xl font-black tracking-tight text-black uppercase">
            KELAS BINAAN (WALI KELAS)
        </h1>
        <p class="text-xs sm:text-sm font-semibold text-slate-900 max-w-2xl">
            Pantau data komprehensif kehadiran siswa binaan, periksa perizinan, dan hubungi orang tua/wali murid dengan akses cepat WhatsApp.
        </p>
    </div>

    <div class="z-10 flex flex-wrap gap-2">
        <a href="{{ route('guru.dashboard') }}" class="neo-btn bg-white text-black text-xs font-bold px-4 py-2 flex items-center gap-1.5 cursor-pointer hover:bg-slate-100">
            ← Kembali ke Dashboard
        </a>
    </div>
</div>
