<!-- 6 KPI Cards: Kehadiran Hari Ini (Real-Time) -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
    <!-- Total Siswa -->
    <div class="bg-white neo-box p-4 flex flex-col justify-between">
        <div class="text-[11px] font-black uppercase text-slate-600 flex items-center justify-between">
            <span>Total Siswa</span>
            <span>👥</span>
        </div>
        <div class="mt-2 font-heading font-black text-2xl text-black">
            {{ $kpi['total'] }}
        </div>
        <div class="text-[10px] font-semibold text-slate-500 mt-1">Siswa Terdaftar</div>
    </div>

    <!-- Hadir (Hijau) -->
    <div class="bg-[#D3F9D8] neo-box p-4 flex flex-col justify-between">
        <div class="text-[11px] font-black uppercase text-emerald-950 flex items-center justify-between">
            <span>Hadir</span>
            <span>🟢</span>
        </div>
        <div class="mt-2 font-heading font-black text-2xl text-emerald-950">
            {{ $kpi['hadir'] }}
        </div>
        <div class="text-[10px] font-bold text-emerald-800 mt-1">Hari Ini</div>
    </div>

    <!-- Terlambat (Biru) -->
    <div class="bg-[#D0EBFF] neo-box p-4 flex flex-col justify-between">
        <div class="text-[11px] font-black uppercase text-blue-950 flex items-center justify-between">
            <span>Terlambat</span>
            <span>🔵</span>
        </div>
        <div class="mt-2 font-heading font-black text-2xl text-blue-950">
            {{ $kpi['terlambat'] }}
        </div>
        <div class="text-[10px] font-bold text-blue-800 mt-1">Hari Ini</div>
    </div>

    <!-- Izin (Abu-abu) -->
    <div class="bg-[#E9ECEF] neo-box p-4 flex flex-col justify-between">
        <div class="text-[11px] font-black uppercase text-slate-800 flex items-center justify-between">
            <span>Izin</span>
            <span>⚪</span>
        </div>
        <div class="mt-2 font-heading font-black text-2xl text-slate-800">
            {{ $kpi['izin'] }}
        </div>
        <div class="text-[10px] font-bold text-slate-600 mt-1">Hari Ini</div>
    </div>

    <!-- Sakit (Kuning) -->
    <div class="bg-[#FFF3BF] neo-box p-4 flex flex-col justify-between">
        <div class="text-[11px] font-black uppercase text-amber-950 flex items-center justify-between">
            <span>Sakit</span>
            <span>🟡</span>
        </div>
        <div class="mt-2 font-heading font-black text-2xl text-amber-950">
            {{ $kpi['sakit'] }}
        </div>
        <div class="text-[10px] font-bold text-amber-800 mt-1">Hari Ini</div>
    </div>

    <!-- Alpa (Merah) -->
    <div class="bg-[#FFE3E3] neo-box p-4 flex flex-col justify-between">
        <div class="text-[11px] font-black uppercase text-rose-950 flex items-center justify-between">
            <span>Alpa</span>
            <span>🔴</span>
        </div>
        <div class="mt-2 font-heading font-black text-2xl text-rose-950">
            {{ $kpi['alpa'] }}
        </div>
        <div class="text-[10px] font-bold text-rose-800 mt-1">Hari Ini</div>
    </div>
</div>

<!-- Progress Rate Kehadiran Hari Ini -->
<div class="bg-white neo-box p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
    <div class="flex items-center gap-3 w-full sm:w-auto">
        <span class="text-2xl">📈</span>
        <div>
            <div class="text-xs font-black uppercase text-black">
                Tingkat Kehadiran Kelas {{ $selectedClass->name ?? '' }} Hari Ini:
            </div>
            <div class="text-[11px] font-medium text-slate-500">
                {{ $kpi['hadir'] + $kpi['terlambat'] }} dari {{ $kpi['total'] }} siswa telah hadir di sekolah hari ini
                @if($kpi['belum_absen'] > 0)
                    • <span class="font-bold text-amber-600">{{ $kpi['belum_absen'] }} siswa belum tercatat</span>
                @endif
            </div>
        </div>
    </div>
    <div class="flex items-center gap-3 w-full sm:w-72">
        <div class="w-full bg-slate-200 border-2 border-black h-4 rounded-sm overflow-hidden">
            <div class="bg-[#20C997] h-full transition-all duration-500" style="width: {{ $kpi['rate'] }}%"></div>
        </div>
        <span class="font-mono font-black text-sm text-black min-w-[50px] text-right">{{ $kpi['rate'] }}%</span>
    </div>
</div>
