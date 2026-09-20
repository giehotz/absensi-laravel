<!-- 4 Stats Cards Grid -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
    <!-- Hadir -->
    <div class="bg-[#D3F9D8] neo-box p-5 flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-black uppercase tracking-wider text-emerald-900">Hadir Hari Ini</span>
            <span class="text-xl">✅</span>
        </div>
        <div class="mt-4">
            <div class="text-3xl sm:text-4xl font-black font-heading text-black">{{ $stats['hadir'] }}</div>
            <div class="text-xs font-semibold text-emerald-800 mt-1">Siswa tepat waktu</div>
        </div>
    </div>

    <!-- Terlambat -->
    <div class="bg-[#FFF3BF] neo-box p-5 flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-black uppercase tracking-wider text-amber-900">Terlambat</span>
            <span class="text-xl">⏰</span>
        </div>
        <div class="mt-4">
            <div class="text-3xl sm:text-4xl font-black font-heading text-black">{{ $stats['terlambat'] }}</div>
            <div class="text-xs font-semibold text-amber-800 mt-1">Lewat batas toleransi</div>
        </div>
    </div>

    <!-- Izin & Sakit -->
    <div class="bg-[#E7F5FF] neo-box p-5 flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-black uppercase tracking-wider text-blue-900">Izin & Sakit</span>
            <span class="text-xl">📝</span>
        </div>
        <div class="mt-4">
            <div class="text-3xl sm:text-4xl font-black font-heading text-black">{{ $stats['izin'] + $stats['sakit'] }}</div>
            <div class="text-xs font-semibold text-blue-800 mt-1">{{ $stats['sakit'] }} Sakit, {{ $stats['izin'] }} Izin</div>
        </div>
    </div>

    <!-- Alpa / Belum Hadir -->
    <div class="bg-[#FFE3E3] neo-box p-5 flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-black uppercase tracking-wider text-rose-900">Tanpa Keterangan</span>
            <span class="text-xl">❌</span>
        </div>
        <div class="mt-4">
            <div class="text-3xl sm:text-4xl font-black font-heading text-black">{{ $stats['alpa'] }}</div>
            <div class="text-xs font-semibold text-rose-800 mt-1">{{ $stats['belum_absen'] }} belum ada kabar</div>
        </div>
    </div>
</div>
