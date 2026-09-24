@extends('layouts.admin')

@section('title', 'Laporan Kehadiran Siswa')
@section('page-title', 'Laporan Kehadiran Siswa')

@section('content')
<div class="space-y-6 pb-12">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white border-2 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000]">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                <span>Laporan</span>
                <span>/</span>
                <span class="text-black">Kehadiran Siswa</span>
            </div>
            <h2 class="font-heading font-black text-2xl text-black">Laporan Kehadiran Siswa</h2>
            <p class="text-xs text-slate-600 mt-0.5">
                Monitoring komprehensif kehadiran, statistik status presensi, dan log riwayat siswa.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 print:hidden">
            <!-- Tombol Export Excel Multi-Sheet -->
            <a href="{{ route('admin.reports.attendance.export', request()->all()) }}" 
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
    <div class="bg-white border-2 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000] print:hidden">
        <form method="GET" action="{{ route('admin.reports.attendance') }}" class="space-y-4">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            <input type="hidden" name="per_page" value="{{ $perPage }}">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Tanggal Mulai -->
                <div>
                    <label class="block text-xs font-bold text-black uppercase tracking-wider mb-1.5">
                        Tanggal Mulai
                    </label>
                    <input type="date" 
                           name="start_date" 
                           value="{{ $startDate }}"
                           class="w-full neo-input px-3 py-2 text-sm bg-slate-50 font-medium focus:bg-white">
                </div>

                <!-- Tanggal Selesai -->
                <div>
                    <label class="block text-xs font-bold text-black uppercase tracking-wider mb-1.5">
                        Tanggal Selesai
                    </label>
                    <input type="date" 
                           name="end_date" 
                           value="{{ $endDate }}"
                           class="w-full neo-input px-3 py-2 text-sm bg-slate-50 font-medium focus:bg-white">
                </div>

                <!-- Dropdown Kelas -->
                <div>
                    <label class="block text-xs font-bold text-black uppercase tracking-wider mb-1.5">
                        Kelas
                    </label>
                    <select name="school_class_id" class="w-full neo-input px-3 py-2 text-sm bg-slate-50 font-medium focus:bg-white">
                        <option value="all" {{ $schoolClassId == 'all' || !$schoolClassId ? 'selected' : '' }}>Semua Kelas ({{ $classes->sum('students_count') }} Siswa)</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ $schoolClassId == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} (Tingkat {{ $c->level }} • {{ $c->students_count }} Siswa)
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Dropdown Status Kehadiran -->
                <div>
                    <label class="block text-xs font-bold text-black uppercase tracking-wider mb-1.5">
                        Status Presensi
                    </label>
                    <select name="status" class="w-full neo-input px-3 py-2 text-sm bg-slate-50 font-medium focus:bg-white">
                        <option value="all" {{ $status == 'all' || !$status ? 'selected' : '' }}>Semua Status</option>
                        <option value="hadir" {{ $status == 'hadir' ? 'selected' : '' }}>Hadir</option>
                        <option value="terlambat" {{ $status == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                        <option value="izin" {{ $status == 'izin' ? 'selected' : '' }}>Izin</option>
                        <option value="sakit" {{ $status == 'sakit' ? 'selected' : '' }}>Sakit</option>
                        <option value="izin_sakit" {{ $status == 'izin_sakit' ? 'selected' : '' }}>Izin & Sakit</option>
                        <option value="alpa" {{ $status == 'alpa' ? 'selected' : '' }}>Alpa (Tanpa Keterangan)</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2 border-t-2 border-slate-200">
                <div class="text-xs font-semibold text-slate-500">
                    Menampilkan data periode: <span class="font-bold text-black">{{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }}</span> s/d <span class="font-bold text-black">{{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.reports.attendance') }}" 
                       class="neo-btn bg-white hover:bg-slate-100 text-slate-700 px-3 py-1.5 text-xs font-bold">
                        Reset Filter
                    </a>
                    <button type="submit" 
                            class="neo-btn bg-black text-white hover:bg-slate-800 px-4 py-1.5 text-xs font-bold flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        <span>Terapkan Filter</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Summary KPI Stat Cards (Neobrutalism Style) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
        <!-- Total Presensi -->
        <div class="bg-white border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000] flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Total Presensi</span>
                <span class="w-2 h-2 rounded-full bg-black"></span>
            </div>
            <div class="mt-2 font-heading font-black text-2xl sm:text-3xl text-black">
                {{ number_format($totalRecords) }}
            </div>
            <div class="mt-1 text-[11px] font-semibold text-slate-500">
                Catatan presensi
            </div>
        </div>

        <!-- Hadir -->
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
        <div class="bg-[#D0EBFF] border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000] flex flex-col justify-between">
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

    <!-- Neobrutalism Charts Grid (Inspired by neobrutalism.dev/charts) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Chart 1: Donut Chart (Distribusi Status Presensi) -->
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

            <!-- Neobrutalism Legend Chips -->
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

        <!-- Chart 2: Bar Chart (Tren Kehadiran Harian) -->
        <div class="lg:col-span-7 bg-white border-2 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000] flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <h3 class="font-heading font-black text-base text-black flex items-center gap-2">
                        <span class="w-3 h-3 bg-[#5294FF] border border-black inline-block"></span>
                        Tren Kehadiran Harian ({{ $reportMonthName }})
                    </h3>
                    <span class="neo-badge bg-[#E7F5FF] text-blue-900 text-[10px]">Harian</span>
                </div>
                <p class="text-xs text-slate-500 mb-4">
                    Fluktuasi jumlah kehadiran (Hadir, Terlambat, Izin/Sakit, Alpa) per hari.
                </p>

                <div class="relative w-full h-[260px]">
                    <canvas id="dailyBarChart"></canvas>
                </div>
            </div>

            <!-- Neobrutalism Quick Note -->
            <div class="flex items-center justify-between pt-3 mt-4 border-t-2 border-slate-100 text-xs text-slate-600 font-medium">
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 bg-[#20C997] border border-black inline-block"></span> Hadir</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 bg-[#FFD43B] border border-black inline-block"></span> Terlambat</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 bg-[#5294FF] border border-black inline-block"></span> Izin/Sakit</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 bg-[#FF6B6B] border border-black inline-block"></span> Alpa</span>
                </div>
                <span class="text-[11px] font-mono text-slate-400">Total {{ count($barChartData['labels']) }} Hari</span>
            </div>
        </div>
    </div>

    <!-- Tabbed Detailed Tables Section -->
    <div class="bg-white border-2 border-black rounded-lg shadow-[4px_4px_0px_0px_#000] overflow-hidden">
        <!-- Tab Bar Header & Search Bar -->
        <div class="border-b-2 border-black p-3 sm:p-4 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <!-- Tabs -->
            <div class="flex items-center gap-2">
                <button type="button" 
                        onclick="switchTab('summary')"
                        id="tab-btn-summary"
                        class="px-4 py-2 text-xs font-bold border-2 border-black rounded cursor-pointer transition-all {{ $activeTab === 'summary' ? 'bg-black text-white shadow-[2px_2px_0px_0px_#000]' : 'bg-white text-black hover:bg-slate-200' }}">
                    <span>👥 Rekapitulasi per Siswa</span>
                    <span class="ml-1.5 px-1.5 py-0.2 rounded text-[10px] {{ $activeTab === 'summary' ? 'bg-white text-black font-bold' : 'bg-slate-200 text-black' }}">
                        {{ $students->total() }}
                    </span>
                </button>

                <button type="button" 
                        onclick="switchTab('logs')"
                        id="tab-btn-logs"
                        class="px-4 py-2 text-xs font-bold border-2 border-black rounded cursor-pointer transition-all {{ $activeTab === 'logs' ? 'bg-black text-white shadow-[2px_2px_0px_0px_#000]' : 'bg-white text-black hover:bg-slate-200' }}">
                    <span>📋 Jurnal Log Riwayat Harian</span>
                    <span class="ml-1.5 px-1.5 py-0.2 rounded text-[10px] {{ $activeTab === 'logs' ? 'bg-white text-black font-bold' : 'bg-slate-200 text-black' }}">
                        {{ $attendanceLogs->total() }}
                    </span>
                </button>
            </div>

            <!-- Search & Filter Form -->
            <form method="GET" action="{{ route('admin.reports.attendance') }}" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="start_date" value="{{ $startDate }}">
                <input type="hidden" name="end_date" value="{{ $endDate }}">
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="hidden" name="tab" id="search-tab-input" value="{{ $activeTab }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">

                <!-- Quick Filter Kelas Dropdown -->
                <div class="flex items-center gap-1.5">
                    <label for="table_school_class_id" class="text-xs font-bold text-slate-700 hidden sm:inline whitespace-nowrap">
                        Kelas:
                    </label>
                    <select id="table_school_class_id" 
                            name="school_class_id" 
                            onchange="this.form.submit()" 
                            class="neo-input py-1.5 px-3 text-xs bg-white font-bold border-2 border-black rounded shadow-[2px_2px_0px_0px_#000] focus:bg-white cursor-pointer">
                        <option value="all" {{ $schoolClassId == 'all' || !$schoolClassId ? 'selected' : '' }}>
                            Semua Kelas ({{ $classes->sum('students_count') }})
                        </option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ $schoolClassId == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->students_count }} Siswa)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="relative">
                    <input type="text" 
                           name="search" 
                           value="{{ $search }}"
                           placeholder="Cari nama atau NIS..." 
                           class="neo-input pl-8 pr-3 py-1.5 text-xs bg-white focus:bg-white w-40 sm:w-56 border-2 border-black rounded shadow-[2px_2px_0px_0px_#000]">
                    <svg class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>

                <button type="submit" class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-3 py-1.5 text-xs font-bold border-2 border-black rounded shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                    Cari
                </button>
                @if($search || ($schoolClassId && $schoolClassId !== 'all'))
                    <a href="{{ route('admin.reports.attendance', array_merge(request()->except(['search', 'school_class_id']), ['tab' => $activeTab, 'per_page' => $perPage])) }}" 
                       class="neo-btn bg-rose-100 hover:bg-rose-200 text-rose-900 px-2.5 py-1.5 text-xs font-bold border-2 border-black rounded shadow-[2px_2px_0px_0px_#000] cursor-pointer"
                       title="Reset filter kelas & pencarian">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- Tab 1: Panel Rekapitulasi per Siswa -->
        <div id="tab-panel-summary" class="{{ $activeTab === 'summary' ? 'block' : 'hidden' }}">
            <!-- Toolbar Tampilan Data (25, 50, 100, Semua) -->
            <div class="p-3 sm:p-4 bg-slate-50 border-b-2 border-black flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-black uppercase tracking-wider font-heading text-black flex items-center gap-1.5">
                        <span>👥</span> Tampilkan:
                    </span>
                    <div class="inline-flex items-center border-2 border-black rounded shadow-[2px_2px_0px_0px_#000] overflow-hidden bg-white">
                        @foreach(['25' => '25', '50' => '50', '100' => '100', 'semua' => 'Semua'] as $val => $label)
                            <a href="{{ route('admin.reports.attendance', array_merge(request()->all(), ['per_page' => $val, 'tab' => 'summary'])) }}"
                               class="px-3 py-1 text-xs font-bold transition-colors border-r-2 last:border-r-0 border-black cursor-pointer {{ ($perPage == $val || ($val == 'semua' && $perPage == 'all')) ? 'bg-black text-white' : 'bg-white text-black hover:bg-slate-200' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="text-xs font-semibold text-slate-600">
                    Menampilkan <strong class="text-black font-mono font-bold">{{ $students->count() }}</strong> dari <strong class="text-black font-mono font-bold">{{ $students->total() }}</strong> siswa
                    @if($selectedClass)
                        <span class="text-slate-400 mx-1">|</span> Kelas: <strong class="text-black font-bold">{{ $selectedClass->name }}</strong>
                    @endif
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-800">
                    <thead class="bg-slate-100 border-b-2 border-black font-bold uppercase text-[11px] text-black">
                        <tr>
                            <th class="px-4 py-3 text-center w-12 border-r border-slate-300">No</th>
                            <th class="px-4 py-3 border-r border-slate-300">Siswa & NIS</th>
                            <th class="px-4 py-3 border-r border-slate-300">Kelas</th>
                            <th class="px-3 py-3 text-center border-r border-slate-300 bg-[#D3F9D8]/50">Hadir</th>
                            <th class="px-3 py-3 text-center border-r border-slate-300 bg-[#FFF3BF]/50">Terlambat</th>
                            <th class="px-3 py-3 text-center border-r border-slate-300 bg-[#D0EBFF]/50">Izin</th>
                            <th class="px-3 py-3 text-center border-r border-slate-300 bg-[#F3D9FA]/50">Sakit</th>
                            <th class="px-3 py-3 text-center border-r border-slate-300 bg-[#FFE3E3]/50">Alpa</th>
                            <th class="px-3 py-3 text-center border-r border-slate-300">Total</th>
                            <th class="px-4 py-3 text-center w-36">Tingkat Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-slate-100 font-medium">
                        @forelse($students as $idx => $student)
                            @php
                                $totalStudentPresent = $student->count_hadir + $student->count_terlambat;
                                $studentRate = $student->count_total > 0 
                                    ? round(($totalStudentPresent / $student->count_total) * 100, 1) 
                                    : 0;
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3 text-center font-mono font-bold text-slate-500 border-r border-slate-200">
                                    {{ $students->firstItem() + $idx }}
                                </td>
                                <td class="px-4 py-3 border-r border-slate-200">
                                    <div class="font-bold text-black text-sm">{{ $student->user?->name ?? '-' }}</div>
                                    <div class="font-mono text-[11px] text-slate-500">NIS: {{ $student->nis ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 border-r border-slate-200">
                                    <span class="neo-badge bg-white text-black font-mono text-[10px]">
                                        {{ $student->schoolClass?->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-center font-bold text-emerald-700 border-r border-slate-200">
                                    {{ $student->count_hadir }}
                                </td>
                                <td class="px-3 py-3 text-center font-bold text-amber-700 border-r border-slate-200">
                                    {{ $student->count_terlambat }}
                                </td>
                                <td class="px-3 py-3 text-center font-bold text-blue-700 border-r border-slate-200">
                                    {{ $student->count_izin }}
                                </td>
                                <td class="px-3 py-3 text-center font-bold text-purple-700 border-r border-slate-200">
                                    {{ $student->count_sakit }}
                                </td>
                                <td class="px-3 py-3 text-center font-bold text-rose-700 border-r border-slate-200">
                                    {{ $student->count_alpa }}
                                </td>
                                <td class="px-3 py-3 text-center font-mono font-bold text-black border-r border-slate-200">
                                    {{ $student->count_total }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <div class="w-16 bg-slate-200 border border-black rounded-full h-2.5 overflow-hidden">
                                            <div class="h-full {{ $studentRate >= 85 ? 'bg-[#20C997]' : ($studentRate >= 75 ? 'bg-[#FFD43B]' : 'bg-[#FF6B6B]') }}" 
                                                 style="width: {{ $studentRate }}%"></div>
                                        </div>
                                        <span class="font-mono font-bold text-xs {{ $studentRate >= 85 ? 'text-emerald-700' : ($studentRate >= 75 ? 'text-amber-700' : 'text-rose-700') }}">
                                            {{ $studentRate }}%
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-10 text-center text-slate-500 font-bold">
                                    Tidak ada data siswa yang cocok dengan kriteria filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Tab 1 -->
            @if($students->hasPages())
                <div class="p-4 border-t-2 border-black bg-slate-50">
                    {{ $students->links() }}
                </div>
            @endif
        </div>

        <!-- Tab 2: Panel Jurnal Log Riwayat Harian -->
        <div id="tab-panel-logs" class="{{ $activeTab === 'logs' ? 'block' : 'hidden' }}">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-800">
                    <thead class="bg-slate-100 border-b-2 border-black font-bold uppercase text-[11px] text-black">
                        <tr>
                            <th class="px-4 py-3 text-center w-12 border-r border-slate-300">No</th>
                            <th class="px-4 py-3 border-r border-slate-300">Tanggal</th>
                            <th class="px-4 py-3 border-r border-slate-300">Siswa & NIS</th>
                            <th class="px-4 py-3 border-r border-slate-300">Kelas</th>
                            <th class="px-4 py-3 text-center border-r border-slate-300">Jam Masuk / Pulang</th>
                            <th class="px-4 py-3 text-center border-r border-slate-300">Status</th>
                            <th class="px-3 py-3 text-center border-r border-slate-300">Metode</th>
                            <th class="px-4 py-3">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-slate-100 font-medium">
                        @forelse($attendanceLogs as $idx => $log)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3 text-center font-mono font-bold text-slate-500 border-r border-slate-200">
                                    {{ $attendanceLogs->firstItem() + $idx }}
                                </td>
                                <td class="px-4 py-3 font-mono font-bold text-black border-r border-slate-200 whitespace-nowrap">
                                    {{ $log->date ? $log->date->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-4 py-3 border-r border-slate-200">
                                    <div class="font-bold text-black text-sm">{{ $log->student?->user?->name ?? '-' }}</div>
                                    <div class="font-mono text-[11px] text-slate-500">NIS: {{ $log->student?->nis ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 border-r border-slate-200">
                                    <span class="neo-badge bg-white text-black font-mono text-[10px]">
                                        {{ $log->student?->schoolClass?->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center font-mono border-r border-slate-200 whitespace-nowrap">
                                    <div class="font-bold text-black">{{ $log->check_in_time ? $log->check_in_time->format('H:i') : '-' }}</div>
                                    <div class="text-[10px] text-slate-400">Pulang: {{ $log->check_out_time ? $log->check_out_time->format('H:i') : '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-center border-r border-slate-200">
                                    @php
                                        $badgeBg = match($log->status) {
                                            'hadir' => 'bg-[#20C997] text-white',
                                            'terlambat' => 'bg-[#FFD43B] text-black',
                                            'izin' => 'bg-[#5294FF] text-white',
                                            'sakit' => 'bg-[#845EF7] text-white',
                                            'alpa' => 'bg-[#FF6B6B] text-white',
                                            default => 'bg-slate-200 text-black'
                                        };
                                    @endphp
                                    <span class="neo-badge {{ $badgeBg }}">
                                        {{ strtoupper($log->status) }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-center border-r border-slate-200">
                                    <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 border border-black rounded {{ $log->method === 'qr' ? 'bg-[#D3F9D8] text-emerald-950' : 'bg-slate-100 text-slate-800' }}">
                                        {{ strtoupper($log->method) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 text-xs">
                                    {{ $log->notes ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center text-slate-500 font-bold">
                                    Tidak ada catatan log kehadiran yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Tab 2 -->
            @if($attendanceLogs->hasPages())
                <div class="p-4 border-t-2 border-black bg-slate-50">
                    {{ $attendanceLogs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Print Stylesheet -->
<style>
@media print {
    #sidebar, header, nav, .print\:hidden {
        display: none !important;
    }
    #mainContent {
        padding-left: 0 !important;
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

        // Update URL query string without reloading page
        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.replaceState({}, '', url);
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Shared Neobrutalism Tooltip Configuration
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

        // 1. Neobrutalism Donut Chart (Status Breakdown)
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
                cutout: '68%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: neobrutalismTooltip
                }
            }
        });

        // 2. Neobrutalism Bar Chart (Daily Trend)
        const barCtx = document.getElementById('dailyBarChart').getContext('2d');
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
                        backgroundColor: '#5294FF',
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
                        stacked: false,
                        grid: {
                            color: '#e2e8f0',
                            tickColor: '#000000'
                        },
                        ticks: {
                            color: '#000000',
                            font: { family: 'Plus Jakarta Sans', weight: 'bold', size: 10 }
                        }
                    },
                    y: {
                        stacked: false,
                        beginAtZero: true,
                        grid: {
                            color: '#f1f5f9'
                        },
                        ticks: {
                            stepSize: 1,
                            color: '#000000',
                            font: { family: 'Plus Jakarta Sans', weight: 'bold', size: 10 }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
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
