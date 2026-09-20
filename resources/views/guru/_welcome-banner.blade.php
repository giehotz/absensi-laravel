<!-- Top Welcome Banner -->
<div class="bg-[#FFF3BF] neo-box-lg p-6 sm:p-8 relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
    <div class="space-y-2 z-10">
        <div class="flex items-center gap-2">
            <span class="neo-badge bg-[#5294FF] text-white">DEWAN GURU</span>
            <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border border-black">
                NIP: {{ $teacher->nip ?? '-' }}
            </span>
            <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border border-black hidden sm:inline-block">
                {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
            </span>
        </div>
        <h1 class="font-heading text-2xl sm:text-3xl font-black text-black tracking-tight uppercase">
            Halo, {{ Auth::user()->name }}!
        </h1>
        <p class="text-sm font-semibold text-slate-800 max-w-xl">
            Pantau kehadiran siswa di kelas binaan dan mata pelajaran yang Anda ampu hari ini, serta kelola permohonan izin siswa secara real-time.
        </p>
    </div>

    <div class="flex flex-wrap gap-2 z-10">
        <button type="button" onclick="featurePlaceholder('Scanner QR Presensi')" class="neo-btn bg-[#20C997] text-black px-4 py-2.5 text-xs font-bold uppercase flex items-center gap-2 cursor-pointer shadow-sm hover:translate-x-0.5 hover:translate-y-0.5 transition-transform">
            <span>📷</span> Buka Scanner QR
        </button>
        <a href="{{ route('guru.attendance.manual') }}" class="neo-btn bg-white text-black px-4 py-2.5 text-xs font-bold uppercase flex items-center gap-2 cursor-pointer shadow-sm hover:translate-x-0.5 hover:translate-y-0.5 transition-transform">
            <span>📋</span> Input Absen Manual
        </a>
    </div>
</div>
