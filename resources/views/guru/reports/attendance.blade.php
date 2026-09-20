@extends('layouts.guru')

@section('title', 'Rekap & Laporan Kehadiran')
@section('page-title', 'Rekap Kehadiran Siswa')

@section('content')
<div class="space-y-6 pb-12">
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

    <!-- Filter Card -->
    <div class="bg-white border-2 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000] print:hidden space-y-4">
        <!-- Quick Weekly Navigator Banner -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-[#E7F5FF] border-2 border-black p-3.5 rounded-lg">
            <div class="flex items-center gap-2.5">
                <span class="text-xl">📅</span>
                <div>
                    <div class="text-[10px] font-black uppercase text-blue-900 tracking-wider">Rekapitulasi Mingguan (Senin — Sabtu)</div>
                    <div class="font-heading font-black text-sm text-black">{{ $weekPeriodLabel }}</div>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ $prevWeekUrl }}" 
                   class="neo-btn bg-white hover:bg-slate-100 text-black px-3 py-1.5 text-xs font-bold flex items-center gap-1 shadow-[2px_2px_0px_0px_#000] cursor-pointer" 
                   title="Merekap data minggu sebelumnya">
                    <span>←</span> <span>Minggu Sebelumnya</span>
                </a>
                <a href="{{ $thisWeekUrl }}" 
                   class="neo-btn {{ $isThisWeek ? 'bg-[#5294FF] text-white shadow-[2px_2px_0px_0px_#000]' : 'bg-white hover:bg-slate-100 text-black shadow-[2px_2px_0px_0px_#000]' }} px-3 py-1.5 text-xs font-bold cursor-pointer" 
                   title="Kembali ke minggu berjalan saat ini">
                    Minggu Ini
                </a>
                <a href="{{ $nextWeekUrl }}" 
                   class="neo-btn bg-white hover:bg-slate-100 text-black px-3 py-1.5 text-xs font-bold flex items-center gap-1 shadow-[2px_2px_0px_0px_#000] cursor-pointer" 
                   title="Merekap data minggu selanjutnya">
                    <span>Minggu Selanjutnya</span> <span>→</span>
                </a>
            </div>
        </div>

        <form method="GET" action="{{ route('guru.reports.attendance') }}" class="space-y-4 pt-1">
            <input type="hidden" name="tab" value="{{ $activeTab }}">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Tanggal Mulai -->
                <div>
                    <label class="block text-xs font-bold text-black uppercase tracking-wider mb-1.5">
                        Tanggal Mulai
                    </label>
                    <input type="date" 
                           name="start_date" 
                           value="{{ $startDate }}"
                           class="w-full bg-slate-50 border-2 border-black px-3 py-2 text-sm font-medium focus:bg-white focus:outline-hidden">
                </div>

                <!-- Tanggal Selesai -->
                <div>
                    <label class="block text-xs font-bold text-black uppercase tracking-wider mb-1.5">
                        Tanggal Selesai
                    </label>
                    <input type="date" 
                           name="end_date" 
                           value="{{ $endDate }}"
                           class="w-full bg-slate-50 border-2 border-black px-3 py-2 text-sm font-medium focus:bg-white focus:outline-hidden">
                </div>

                <!-- Dropdown Kelas (Hanya Kelas Guru) -->
                <div>
                    <label class="block text-xs font-bold text-black uppercase tracking-wider mb-1.5">
                        Kelas
                    </label>
                    <select name="school_class_id" class="w-full bg-slate-50 border-2 border-black px-3 py-2 text-sm font-medium focus:bg-white focus:outline-hidden">
                        <option value="all" {{ $schoolClassId == 'all' || !$schoolClassId ? 'selected' : '' }}>Semua Kelas Binaan/Ajar</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ $schoolClassId == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} (Tingkat {{ $c->level }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Dropdown Status -->
                <div>
                    <label class="block text-xs font-bold text-black uppercase tracking-wider mb-1.5">
                        Status Presensi
                    </label>
                    <select name="status" class="w-full bg-slate-50 border-2 border-black px-3 py-2 text-sm font-medium focus:bg-white focus:outline-hidden">
                        <option value="all" {{ $status == 'all' || !$status ? 'selected' : '' }}>Semua Status</option>
                        <option value="hadir" {{ $status == 'hadir' ? 'selected' : '' }}>Hadir</option>
                        <option value="terlambat" {{ $status == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                        <option value="izin" {{ $status == 'izin' ? 'selected' : '' }}>Izin</option>
                        <option value="sakit" {{ $status == 'sakit' ? 'selected' : '' }}>Sakit</option>
                        <option value="alpa" {{ $status == 'alpa' ? 'selected' : '' }}>Alpa</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2 border-t-2 border-slate-100">
                <a href="{{ route('guru.reports.attendance') }}" 
                   class="neo-btn bg-slate-100 hover:bg-slate-200 text-black px-4 py-2 text-xs font-bold cursor-pointer">
                    Reset Filter
                </a>
                <button type="submit" 
                        class="neo-btn bg-[#5294FF] hover:bg-blue-600 text-white px-5 py-2 text-xs font-bold flex items-center gap-1.5 cursor-pointer shadow-[2px_2px_0px_0px_#000]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <span>Terapkan Filter</span>
                </button>
            </div>
        </form>
    </div>

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
                <span class="neo-badge bg-[#FFD43B] text-black text-[9px] py-0 px-1">Toleransi</span>
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

    <!-- Neobrutalism Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 print:hidden">
        <!-- Chart 1: Donut Chart -->
        <div class="lg:col-span-5 bg-white border-2 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000] flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <h3 class="font-heading font-black text-base text-black flex items-center gap-2">
                        <span class="w-3 h-3 bg-[#FFD43B] border border-black inline-block"></span>
                        Distribusi Status Kehadiran
                    </h3>
                    <span class="neo-badge bg-white text-black text-[10px]">Proporsi</span>
                </div>
                <p class="text-xs text-slate-500 mb-4">
                    Komposisi persentase kehadiran siswa selama rentang periode yang dipilih.
                </p>

                <div class="relative w-full aspect-square max-h-[260px] mx-auto flex items-center justify-center">
                    <canvas id="donutChart"></canvas>
                </div>
            </div>

            <!-- Legend Chips -->
            <div class="grid grid-cols-3 sm:grid-cols-5 gap-2 pt-4 mt-4 border-t-2 border-slate-100 text-center">
                <div class="p-1.5 bg-[#D3F9D8] border border-black rounded">
                    <div class="text-[10px] font-bold text-emerald-950 uppercase">Hadir</div>
                    <div class="font-heading font-black text-xs text-black">{{ $totalHadir }}</div>
                </div>
                <div class="p-1.5 bg-[#FFF3BF] border border-black rounded">
                    <div class="text-[10px] font-bold text-amber-950 uppercase">Terlambat</div>
                    <div class="font-heading font-black text-xs text-black">{{ $totalTerlambat }}</div>
                </div>
                <div class="p-1.5 bg-[#D0EBFF] border border-black rounded">
                    <div class="text-[10px] font-bold text-blue-950 uppercase">Izin</div>
                    <div class="font-heading font-black text-xs text-black">{{ $totalIzin }}</div>
                </div>
                <div class="p-1.5 bg-[#F3D9FA] border border-black rounded">
                    <div class="text-[10px] font-bold text-purple-950 uppercase">Sakit</div>
                    <div class="font-heading font-black text-xs text-black">{{ $totalSakit }}</div>
                </div>
                <div class="p-1.5 bg-[#FFE3E3] border border-black rounded">
                    <div class="text-[10px] font-bold text-rose-950 uppercase">Alpa</div>
                    <div class="font-heading font-black text-xs text-black">{{ $totalAlpa }}</div>
                </div>
            </div>
        </div>

        <!-- Chart 2: Bar Chart (Tren Harian) -->
        <div class="lg:col-span-7 bg-white border-2 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000] flex flex-col justify-between">
            <div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-1">
                    <h3 class="font-heading font-black text-base text-black flex items-center gap-2">
                        <span class="w-3 h-3 bg-[#5294FF] border border-black inline-block"></span>
                        Tren Kehadiran Harian ({{ $weekPeriodLabel }})
                    </h3>
                    <div class="flex items-center gap-1.5">
                        <a href="{{ $prevWeekUrl }}" 
                           class="neo-btn bg-white hover:bg-slate-100 text-black px-2.5 py-1 text-xs font-bold flex items-center gap-1 shadow-[2px_2px_0px_0px_#000] cursor-pointer" 
                           title="Minggu Sebelumnya">
                            <span>←</span> <span class="hidden sm:inline">Sebelumnya</span>
                        </a>
                        <a href="{{ $thisWeekUrl }}" 
                           class="neo-btn {{ $isThisWeek ? 'bg-[#5294FF] text-white shadow-[2px_2px_0px_0px_#000]' : 'bg-white hover:bg-slate-100 text-black shadow-[2px_2px_0px_0px_#000]' }} px-2.5 py-1 text-xs font-bold cursor-pointer" 
                           title="Minggu Ini">
                            Minggu Ini
                        </a>
                        <a href="{{ $nextWeekUrl }}" 
                           class="neo-btn bg-white hover:bg-slate-100 text-black px-2.5 py-1 text-xs font-bold flex items-center gap-1 shadow-[2px_2px_0px_0px_#000] cursor-pointer" 
                           title="Minggu Selanjutnya">
                            <span class="hidden sm:inline">Selanjutnya</span> <span>→</span>
                        </a>
                    </div>
                </div>
                <p class="text-xs text-slate-500 mb-4">
                    Fluktuasi kehadiran harian siswa dari hari Senin sampai Sabtu pada rentang tanggal aktif.
                </p>

                <div class="relative w-full h-[260px]">
                    <canvas id="barChart"></canvas>
                </div>
            </div>

            <!-- Legend Info -->
            <div class="flex items-center justify-center gap-4 pt-3 mt-4 border-t-2 border-slate-100 text-xs font-bold flex-wrap">
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 bg-[#20C997] border border-black inline-block rounded-xs"></span> Hadir
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 bg-[#FFD43B] border border-black inline-block rounded-xs"></span> Terlambat
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 bg-[#5294FF] border border-black inline-block rounded-xs"></span> Izin/Sakit
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 bg-[#FF6B6B] border border-black inline-block rounded-xs"></span> Alpa
                </span>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="bg-white border-2 border-black p-3 rounded-lg shadow-[4px_4px_0px_0px_#000] flex flex-wrap items-center justify-between gap-3 print:hidden">
        <div class="flex items-center gap-2">
            <button id="tab-btn-summary" 
                    onclick="switchTab('summary')" 
                    class="px-4 py-2 text-xs font-bold border-2 border-black rounded cursor-pointer transition-all {{ $activeTab === 'summary' ? 'bg-black text-white shadow-[2px_2px_0px_0px_#000]' : 'bg-white text-black hover:bg-slate-200' }}">
                📊 Rekapitulasi per Siswa
            </button>
            <button id="tab-btn-logs" 
                    onclick="switchTab('logs')" 
                    class="px-4 py-2 text-xs font-bold border-2 border-black rounded cursor-pointer transition-all {{ $activeTab === 'logs' ? 'bg-black text-white shadow-[2px_2px_0px_0px_#000]' : 'bg-white text-black hover:bg-slate-200' }}">
                📋 Jurnal Riwayat Harian
            </button>
        </div>

        <!-- Search Form -->
        <form method="GET" action="{{ route('guru.reports.attendance') }}" class="flex items-center gap-2">
            <input type="hidden" name="start_date" value="{{ $startDate }}">
            <input type="hidden" name="end_date" value="{{ $endDate }}">
            <input type="hidden" name="school_class_id" value="{{ $schoolClassId }}">
            <input type="hidden" name="status" value="{{ $status }}">
            <input type="hidden" name="tab" id="search-tab-input" value="{{ $activeTab }}">

            <div class="relative">
                <input type="text" 
                       name="search" 
                       value="{{ $search }}" 
                       placeholder="Cari NIS / Nama Siswa..." 
                       class="neo-input pl-8 pr-3 py-1.5 text-xs bg-slate-50 focus:bg-white w-48 sm:w-60">
                <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            @if($search)
                <a href="{{ route('guru.reports.attendance', array_merge(request()->except('search'), ['tab' => $activeTab])) }}" 
                   class="neo-btn bg-rose-100 hover:bg-rose-200 text-rose-900 px-2.5 py-1.5 text-xs font-bold cursor-pointer"
                   title="Hapus pencarian">
                    ✕
                </a>
            @endif
        </form>
    </div>

    <!-- TAB 1: REKAPITULASI PER SISWA -->
    <div id="tab-panel-summary" class="{{ $activeTab === 'summary' ? '' : 'hidden' }}">
        <div class="bg-white border-2 border-black rounded-lg shadow-[4px_4px_0px_0px_#000] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead class="bg-slate-100 border-b-2 border-black uppercase font-black text-black">
                        <tr>
                            <th class="p-3 text-center border-r-2 border-black w-10">No</th>
                            <th class="p-3 text-center border-r-2 border-black w-24">NIS</th>
                            <th class="p-3 border-r-2 border-black">Nama Siswa</th>
                            <th class="p-3 border-r-2 border-black text-center w-28">Kelas</th>
                            <th class="p-3 text-center border-r-2 border-black w-14 bg-[#D3F9D8] text-emerald-950">Hadir</th>
                            <th class="p-3 text-center border-r-2 border-black w-14 bg-[#FFF3BF] text-amber-950">Telat</th>
                            <th class="p-3 text-center border-r-2 border-black w-14 bg-[#D0EBFF] text-blue-950">Izin</th>
                            <th class="p-3 text-center border-r-2 border-black w-14 bg-[#F3D9FA] text-purple-950">Sakit</th>
                            <th class="p-3 text-center border-r-2 border-black w-14 bg-[#FFE3E3] text-rose-950">Alpa</th>
                            <th class="p-3 text-center border-r-2 border-black w-16">Total</th>
                            <th class="p-3 text-center border-r-2 border-black w-24">% Kehadiran</th>
                            <th class="p-3 text-center w-24">Predikat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-black font-medium">
                        @forelse($students as $index => $stu)
                            @php
                                $totalPresent = $stu->count_hadir + $stu->count_terlambat;
                                $rate = $stu->count_total > 0 
                                    ? round(($totalPresent / $stu->count_total) * 100, 1) 
                                    : 0;

                                $predikatBadge = 'bg-[#20C997] text-white';
                                $predikatText = 'Sangat Baik';
                                if ($rate < 75) {
                                    $predikatBadge = 'bg-[#FF6B6B] text-white';
                                    $predikatText = 'Perhatian';
                                } elseif ($rate < 85) {
                                    $predikatBadge = 'bg-[#FFD43B] text-black';
                                    $predikatText = 'Cukup';
                                } elseif ($rate < 95) {
                                    $predikatBadge = 'bg-[#5294FF] text-white';
                                    $predikatText = 'Baik';
                                }
                            @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="p-3 text-center font-mono font-bold border-r-2 border-black">
                                    {{ $students->firstItem() + $index }}
                                </td>
                                <td class="p-3 text-center font-mono font-bold border-r-2 border-black">
                                    {{ $stu->nis ?? '-' }}
                                </td>
                                <td class="p-3 font-bold text-black border-r-2 border-black">
                                    <div>{{ $stu->user->name ?? '-' }}</div>
                                    <div class="text-[10px] font-normal text-slate-500 font-mono">NISN: {{ $stu->nisn ?? '-' }}</div>
                                </td>
                                <td class="p-3 text-center font-semibold text-slate-700 border-r-2 border-black">
                                    {{ $stu->schoolClass->name ?? '-' }}
                                </td>
                                <td class="p-3 text-center font-mono font-bold border-r-2 border-black bg-emerald-50/50 text-emerald-950">
                                    {{ $stu->count_hadir }}
                                </td>
                                <td class="p-3 text-center font-mono font-bold border-r-2 border-black bg-amber-50/50 text-amber-950">
                                    {{ $stu->count_terlambat }}
                                </td>
                                <td class="p-3 text-center font-mono font-bold border-r-2 border-black bg-blue-50/50 text-blue-950">
                                    {{ $stu->count_izin }}
                                </td>
                                <td class="p-3 text-center font-mono font-bold border-r-2 border-black bg-purple-50/50 text-purple-950">
                                    {{ $stu->count_sakit }}
                                </td>
                                <td class="p-3 text-center font-mono font-bold border-r-2 border-black bg-rose-50/50 text-rose-950">
                                    {{ $stu->count_alpa }}
                                </td>
                                <td class="p-3 text-center font-mono font-bold border-r-2 border-black bg-slate-50">
                                    {{ $stu->count_total }}
                                </td>
                                <td class="p-3 text-center font-mono font-black border-r-2 border-black">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <span>{{ $rate }}%</span>
                                    </div>
                                </td>
                                <td class="p-3 text-center">
                                    <span class="neo-badge {{ $predikatBadge }} text-[10px]">
                                        {{ $predikatText }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="p-8 text-center text-slate-500 font-semibold">
                                    Tidak ada data siswa ditemukan untuk kriteria filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Siswa -->
            @if($students->hasPages())
                <div class="p-4 border-t-2 border-black bg-slate-50">
                    {{ $students->appends(request()->except('student_page'))->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- TAB 2: JURNAL LOG HARIAN -->
    <div id="tab-panel-logs" class="{{ $activeTab === 'logs' ? '' : 'hidden' }}">
        <div class="bg-white border-2 border-black rounded-lg shadow-[4px_4px_0px_0px_#000] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead class="bg-slate-100 border-b-2 border-black uppercase font-black text-black">
                        <tr>
                            <th class="p-3 text-center border-r-2 border-black w-10">No</th>
                            <th class="p-3 text-center border-r-2 border-black w-24">Tanggal</th>
                            <th class="p-3 text-center border-r-2 border-black w-24">NIS</th>
                            <th class="p-3 border-r-2 border-black">Nama Siswa</th>
                            <th class="p-3 text-center border-r-2 border-black w-24">Kelas</th>
                            <th class="p-3 text-center border-r-2 border-black w-24">Waktu Masuk</th>
                            <th class="p-3 text-center border-r-2 border-black w-20">Metode</th>
                            <th class="p-3 text-center border-r-2 border-black w-24">Status</th>
                            <th class="p-3">Catatan / Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-black font-medium">
                        @forelse($attendanceLogs as $index => $log)
                            <tr class="hover:bg-slate-50">
                                <td class="p-3 text-center font-mono font-bold border-r-2 border-black">
                                    {{ $attendanceLogs->firstItem() + $index }}
                                </td>
                                <td class="p-3 text-center font-mono font-bold border-r-2 border-black">
                                    {{ \Carbon\Carbon::parse($log->date)->translatedFormat('d/m/Y') }}
                                </td>
                                <td class="p-3 text-center font-mono font-bold border-r-2 border-black">
                                    {{ $log->student->nis ?? '-' }}
                                </td>
                                <td class="p-3 font-bold text-black border-r-2 border-black">
                                    {{ $log->student->user->name ?? '-' }}
                                </td>
                                <td class="p-3 text-center font-semibold text-slate-700 border-r-2 border-black">
                                    {{ $log->student->schoolClass->name ?? '-' }}
                                </td>
                                <td class="p-3 text-center font-mono font-bold border-r-2 border-black">
                                    @if($log->check_in_time)
                                        <span class="bg-slate-100 px-1.5 py-0.5 border border-slate-300 rounded">
                                            {{ \Carbon\Carbon::parse($log->check_in_time)->format('H:i') }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="p-3 text-center border-r-2 border-black">
                                    <span class="neo-badge bg-white text-black text-[9px] border border-black">
                                        {{ strtoupper($log->method ?? 'manual') }}
                                    </span>
                                </td>
                                <td class="p-3 text-center border-r-2 border-black">
                                    <span class="neo-badge text-[10px]
                                        @if($log->status === 'hadir') bg-[#20C997] text-white 
                                        @elseif($log->status === 'terlambat') bg-[#FFD43B] text-black 
                                        @elseif($log->status === 'izin') bg-[#74C0FC] text-blue-950 
                                        @elseif($log->status === 'sakit') bg-[#A5D8FF] text-blue-950 
                                        @else bg-[#FF6B6B] text-white @endif">
                                        {{ strtoupper($log->status ?? '-') }}
                                    </span>
                                </td>
                                <td class="p-3 text-slate-700 italic">
                                    {{ $log->notes ?: '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-8 text-center text-slate-500 font-semibold">
                                    Tidak ada log presensi ditemukan untuk periode dan filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Log -->
            @if($attendanceLogs->hasPages())
                <div class="p-4 border-t-2 border-black bg-slate-50">
                    {{ $attendanceLogs->appends(request()->except('log_page'))->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<style>
@media print {
    aside, header, .print\:hidden {
        display: none !important;
    }
    main {
        padding: 0 !important;
        margin: 0 !important;
        max-width: 100% !important;
    }
    body {
        background: #ffffff !important;
    }
    .shadow-\[4px_4px_0px_0px_\#000\], .shadow-\[3px_3px_0px_0px_\#000\], .shadow-\[2px_2px_0px_0px_\#000\] {
        box-shadow: none !important;
    }
}
</style>

<!-- Chart.js CDN & Neobrutalism Chart Initializer -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Tab Switching Logic
    function switchTab(tabName) {
        const summaryPanel = document.getElementById('tab-panel-summary');
        const logsPanel = document.getElementById('tab-panel-logs');
        const summaryBtn = document.getElementById('tab-btn-summary');
        const logsBtn = document.getElementById('tab-btn-logs');
        const searchTabInput = document.getElementById('search-tab-input');

        if (searchTabInput) {
            searchTabInput.value = tabName;
        }

        if (tabName === 'summary') {
            summaryPanel.classList.remove('hidden');
            logsPanel.classList.add('hidden');

            summaryBtn.className = "px-4 py-2 text-xs font-bold border-2 border-black rounded cursor-pointer transition-all bg-black text-white shadow-[2px_2px_0px_0px_#000]";
            logsBtn.className = "px-4 py-2 text-xs font-bold border-2 border-black rounded cursor-pointer transition-all bg-white text-black hover:bg-slate-200";
        } else {
            summaryPanel.classList.add('hidden');
            logsPanel.classList.remove('hidden');

            logsBtn.className = "px-4 py-2 text-xs font-bold border-2 border-black rounded cursor-pointer transition-all bg-black text-white shadow-[2px_2px_0px_0px_#000]";
            summaryBtn.className = "px-4 py-2 text-xs font-bold border-2 border-black rounded cursor-pointer transition-all bg-white text-black hover:bg-slate-200";
        }

        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.replaceState({}, '', url);
    }

    document.addEventListener('DOMContentLoaded', function () {
        const neobrutalismTooltip = {
            backgroundColor: '#ffffff',
            titleColor: '#000000',
            bodyColor: '#000000',
            borderColor: '#000000',
            borderWidth: 2,
            padding: 10,
            cornerRadius: 4,
            titleFont: { family: 'Plus Jakarta Sans', weight: 'bold', size: 12 },
            bodyFont: { family: 'Plus Jakarta Sans', weight: '600', size: 11 },
            displayColors: true,
            boxWidth: 10,
            boxHeight: 10,
            boxPadding: 4,
            callbacks: {
                label: function(context) {
                    let label = context.dataset.label || context.label || '';
                    if (label) {
                        label += ': ';
                    }
                    if (context.parsed.y !== undefined) {
                        label += context.parsed.y;
                    } else if (context.parsed !== undefined) {
                        label += context.parsed;
                    }
                    return label;
                }
            }
        };

        // Donut Chart
        const donutCtx = document.getElementById('donutChart').getContext('2d');
        const donutData = @json($donutChartData);
        const hasDonutData = donutData.data.some(val => val > 0);

        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: hasDonutData ? donutData.labels : ['Belum Ada Data'],
                datasets: [{
                    data: hasDonutData ? donutData.data : [1],
                    backgroundColor: hasDonutData ? donutData.colors : ['#e2e8f0'],
                    borderColor: '#000000',
                    borderWidth: 2.5,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: { display: false },
                    tooltip: hasDonutData ? neobrutalismTooltip : { enabled: false }
                }
            }
        });

        // Bar Chart
        const barCtx = document.getElementById('barChart').getContext('2d');
        const barData = @json($barChartData);

        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: barData.labels,
                datasets: [
                    {
                        label: 'Hadir',
                        data: barData.hadir,
                        backgroundColor: '#20C997',
                        borderColor: '#000000',
                        borderWidth: 2,
                        borderRadius: 3
                    },
                    {
                        label: 'Terlambat',
                        data: barData.terlambat,
                        backgroundColor: '#FFD43B',
                        borderColor: '#000000',
                        borderWidth: 2,
                        borderRadius: 3
                    },
                    {
                        label: 'Izin/Sakit',
                        data: barData.izin_sakit,
                        backgroundColor: '#74C0FC',
                        borderColor: '#000000',
                        borderWidth: 2,
                        borderRadius: 3
                    },
                    {
                        label: 'Alpa',
                        data: barData.alpa,
                        backgroundColor: '#FF6B6B',
                        borderColor: '#000000',
                        borderWidth: 2,
                        borderRadius: 3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    x: {
                        grid: { color: '#e2e8f0', tickColor: '#000000' },
                        ticks: { color: '#000000', font: { family: 'Plus Jakarta Sans', weight: 'bold', size: 10 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { stepSize: 1, color: '#000000', font: { family: 'Plus Jakarta Sans', weight: 'bold', size: 10 } }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...neobrutalismTooltip,
                        callbacks: {
                            ...neobrutalismTooltip.callbacks,
                            title: function(tooltipItems) {
                                if (!tooltipItems.length) return '';
                                const idx = tooltipItems[0].dataIndex;
                                return (barData.full_dates && barData.full_dates[idx]) ? barData.full_dates[idx] : tooltipItems[0].label;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
