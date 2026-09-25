<!-- 6 KPI Metrics Cards -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
    <!-- Total Presensi -->
    <div class="bg-white border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000] flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Data</span>
            <span class="text-xs font-mono font-bold bg-slate-100 px-1.5 py-0.5 rounded border border-black">Logs</span>
        </div>
        <div class="mt-2 font-heading font-black text-2xl sm:text-3xl text-black">
            {{ number_format($totalRecords) }}
        </div>
        <div class="mt-1 text-[11px] font-semibold text-slate-500">
            Catatan tercatat
        </div>
    </div>

    <!-- Hadir Tepat Waktu -->
    <div class="bg-[#D3F9D8] border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000] flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold text-emerald-900 uppercase tracking-wider">Hadir</span>
            <span class="neo-badge bg-[#20C997] text-white text-[9px] py-0 px-1">Tepat</span>
        </div>
        <div class="mt-2 font-heading font-black text-2xl sm:text-3xl text-emerald-950">
            {{ number_format($totalHadir) }}
        </div>
        <div class="mt-1 text-[11px] font-bold text-emerald-800">
            {{ $totalRecords > 0 ? round(($totalHadir / $totalRecords) * 100, 1) : 0 }}% proporsi
        </div>
    </div>

    <!-- Terlambat -->
    <div class="bg-[#FFF3BF] border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000] flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold text-amber-900 uppercase tracking-wider">Terlambat</span>
            <span class="neo-badge bg-[#FFD43B] text-black text-[9px] py-0 px-1">Late</span>
        </div>
        <div class="mt-2 font-heading font-black text-2xl sm:text-3xl text-amber-950">
            {{ number_format($totalTerlambat) }}
        </div>
        <div class="mt-1 text-[11px] font-bold text-amber-800">
            {{ $totalRecords > 0 ? round(($totalTerlambat / $totalRecords) * 100, 1) : 0 }}% proporsi
        </div>
    </div>

    <!-- Izin & Sakit -->
    <div class="bg-[#E7F5FF] border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000] flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold text-blue-900 uppercase tracking-wider">Izin / Sakit</span>
            <span class="neo-badge bg-[#5294FF] text-white text-[9px] py-0 px-1">Dispen</span>
        </div>
        <div class="mt-2 font-heading font-black text-2xl sm:text-3xl text-blue-950">
            {{ number_format($totalIzin + $totalSakit) }}
        </div>
        <div class="mt-1 text-[11px] font-bold text-blue-800">
            I: {{ $totalIzin }} | S: {{ $totalSakit }}
        </div>
    </div>

    <!-- Alpa -->
    <div class="bg-[#FFE3E3] border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000] flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold text-rose-900 uppercase tracking-wider">Alpa</span>
            <span class="neo-badge bg-[#FF6B6B] text-white text-[9px] py-0 px-1">Tanpa Ket</span>
        </div>
        <div class="mt-2 font-heading font-black text-2xl sm:text-3xl text-rose-950">
            {{ number_format($totalAlpa) }}
        </div>
        <div class="mt-1 text-[11px] font-bold text-rose-800">
            {{ $totalRecords > 0 ? round(($totalAlpa / $totalRecords) * 100, 1) : 0 }}% proporsi
        </div>
    </div>

    <!-- Tingkat Kehadiran Keseluruhan -->
    <div class="bg-black text-white border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000] flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold text-slate-300 uppercase tracking-wider">% Kehadiran</span>
            <span class="text-xs font-mono font-bold text-[#FFD43B]">Rate</span>
        </div>
        <div class="mt-2 font-heading font-black text-2xl sm:text-3xl text-[#20C997]">
            {{ $attendanceRate }}%
        </div>
        <div class="mt-1 text-[11px] font-semibold text-slate-400">
            Hadir + Terlambat
        </div>
    </div>
</div>
