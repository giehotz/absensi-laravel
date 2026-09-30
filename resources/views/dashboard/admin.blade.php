@extends('layouts.admin')

@section('title', 'Dashboard Administrator')
@section('page-title', 'Pusat Kontrol & Monitoring Absensi')

@section('content')
<div class="space-y-6 sm:space-y-8">
    <!-- Top Institutional Identity & Configuration Banner (Neo-Brutalism) -->
    <div class="bg-[#3B5BDB] neo-box-lg p-6 sm:p-8 text-white relative overflow-hidden flex flex-col xl:flex-row items-start xl:items-center justify-between gap-6 border-4 border-black shadow-[6px_6px_0px_0px_#000]">
        <!-- Decorative Background Pattern -->
        <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none select-none text-9xl font-black font-heading text-white">
            MIN2
        </div>

        <div class="space-y-4 z-10 max-w-2xl">
            <!-- Header Badges -->
            <div class="flex flex-wrap items-center gap-2">
                <span class="neo-badge bg-[#FFD43B] text-black text-[11px] font-black uppercase px-2.5 py-0.5 border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                    👑 PUSAT KONTROL ADMIN
                </span>
                <span class="text-xs font-mono font-bold bg-white/20 border-2 border-black/40 px-2.5 py-0.5 text-white">
                    NPSN: {{ $schoolSetting->npsn ?? '60705691' }}
                </span>
                <span class="text-xs font-bold bg-[#D3F9D8] text-emerald-950 px-2.5 py-0.5 border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                    {{ $schoolSetting->level ?? 'Madrasah Ibtidaiyah' }}
                </span>
            </div>

            <!-- School Identity & Address -->
            <div class="flex items-start gap-4">
                @if(!empty($schoolSetting->logo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($schoolSetting->logo))
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-white rounded-md border-3 border-black shadow-[3px_3px_0px_0px_#000] p-1 shrink-0 flex items-center justify-center">
                        <img src="{{ asset('storage/' . $schoolSetting->logo) }}" alt="Logo {{ $schoolSetting->school_name ?? 'Sekolah' }}" class="w-full h-full object-contain">
                    </div>
                @else
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-[#FFD43B] text-black font-heading font-black text-3xl flex items-center justify-center rounded-md border-3 border-black shadow-[3px_3px_0px_0px_#000] shrink-0">
                        {{ !empty($schoolSetting->school_name) ? strtoupper(substr($schoolSetting->school_name, 0, 1)) : 'M' }}
                    </div>
                @endif
                <div>
                    <h1 class="font-heading text-2xl sm:text-3xl lg:text-4xl font-black text-white tracking-tight uppercase leading-tight drop-shadow-sm">
                        {{ $schoolSetting->school_name ?? 'MIN 2 TANGGAMUS' }}
                    </h1>
                    <p class="text-xs sm:text-sm font-semibold text-blue-100 mt-1 flex items-center gap-1.5">
                        <span>📍</span>
                        <span>{{ $schoolSetting->school_address ?? 'Jl. Lapangan Ampera Purwodadi No.109, Kec. Gisting' }}</span>
                    </p>
                </div>
            </div>

            <!-- Academic & System Context Badges -->
            <div class="flex flex-wrap items-center gap-2 pt-1">
                <!-- Academic Year & Semester -->
                <div class="bg-white text-black border-2 border-black px-3 py-1 neo-box-sm text-xs font-bold flex items-center gap-1.5 shadow-[2px_2px_0px_0px_#000]">
                    <span>🎓</span>
                    <span>T.A: <strong>{{ $academicYear->name ?? '2026/2027' }}</strong></span>
                    <span class="text-slate-400">|</span>
                    <span class="uppercase text-blue-700 font-black">Semester {{ $academicYear->semester ?? 'Ganjil' }}</span>
                </div>

                <!-- Mode & School Time -->
                <div class="bg-[#FFF9DB] text-black border-2 border-black px-3 py-1 neo-box-sm text-xs font-bold flex items-center gap-1.5 shadow-[2px_2px_0px_0px_#000]">
                    <span>⏰</span>
                    <span>Jam Masuk: <strong>{{ substr($setting->school_start_time ?? '07:00:00', 0, 5) }} WIB</strong></span>
                    <span class="text-slate-400">•</span>
                    <span>Toleransi: <strong>{{ $setting->tolerance_minutes }}m</strong></span>
                </div>

                <div class="bg-white text-black border-2 border-black px-3 py-1 neo-box-sm text-xs font-bold flex items-center gap-1.5 shadow-[2px_2px_0px_0px_#000]">
                    <span>⚙️</span>
                    <span>Mode: <strong class="uppercase text-purple-700">{{ $setting->mode }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Quick Action Shortcuts Panel -->
        <div class="z-10 w-full xl:w-auto bg-black/25 backdrop-blur-xs p-4 sm:p-5 rounded-md border-2 border-black/40 space-y-3">
            <div class="text-[11px] font-black uppercase tracking-wider text-yellow-300 flex items-center gap-1.5">
                <span>⚡</span> AKSI CEPAT ADMINISTRATOR:
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-2 gap-2 text-xs">
                <a href="{{ route('admin.attendances.manual', ['date' => $date]) }}" 
                   class="neo-btn bg-[#20C997] hover:bg-emerald-400 text-black font-black px-3 py-2 flex items-center gap-2 border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer transition-all active:translate-x-0.5 active:translate-y-0.5">
                    <span>📝</span> Presensi Manual
                </a>
                <a href="{{ route('admin.reports.attendance', ['start_date' => $date, 'end_date' => $date]) }}" 
                   class="neo-btn bg-[#FFD43B] hover:bg-[#ffe066] text-black font-black px-3 py-2 flex items-center gap-2 border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer transition-all active:translate-x-0.5 active:translate-y-0.5">
                    <span>📊</span> Rekap Laporan
                </a>
                <a href="{{ route('admin.teaching-journals.index') }}" 
                   class="neo-btn bg-white hover:bg-slate-100 text-black font-black px-3 py-2 flex items-center gap-2 border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer transition-all active:translate-x-0.5 active:translate-y-0.5">
                    <span>📖</span> Jurnal Guru
                </a>
                <a href="{{ route('admin.savings.index') }}" 
                   class="neo-btn bg-[#D0EBFF] hover:bg-[#a5d8ff] text-blue-950 font-black px-3 py-2 flex items-center gap-2 border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer transition-all active:translate-x-0.5 active:translate-y-0.5">
                    <span>💰</span> Kas Tabungan
                </a>
                <a href="{{ route('admin.academic-calendar.index') }}" 
                   class="neo-btn bg-[#FFF3BF] hover:bg-[#ffec99] text-amber-950 font-black px-3 py-2 flex items-center gap-2 border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer transition-all active:translate-x-0.5 active:translate-y-0.5">
                    <span>📅</span> Kalender Akademik
                </a>
                <a href="{{ route('admin.settings.index') }}" 
                   class="neo-btn bg-white hover:bg-slate-100 text-black font-black px-3 py-2 flex items-center gap-2 border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer transition-all active:translate-x-0.5 active:translate-y-0.5">
                    <span>⚙️</span> Pengaturan
                </a>
            </div>
        </div>
    </div>

    <!-- Interactive Date Switcher Bar (Neo-Brutalism) -->
    <div class="bg-white neo-box p-3.5 sm:p-4 flex flex-col md:flex-row items-center justify-between gap-4 border-3 border-black shadow-[4px_4px_0px_0px_#000]">
        <div class="flex items-center gap-2 w-full md:w-auto">
            <span class="w-3 h-3 rounded-full bg-blue-600 animate-pulse"></span>
            <span class="text-xs font-black uppercase tracking-wider text-slate-800">
                Data Presensi Tanggal Terpilih:
            </span>
        </div>

        <div class="flex flex-wrap items-center gap-2 justify-center w-full md:w-auto">
            <!-- Tombol Hari Sebelumnya (<) -->
            <a href="{{ route('admin.dashboard', ['date' => $prevDate]) }}" 
               title="Hari Sebelumnya: {{ \Carbon\Carbon::parse($prevDate)->translatedFormat('d M Y') }}"
               class="neo-btn bg-white hover:bg-[#FFD43B] text-black border-2 border-black px-3 py-1.5 font-black text-xs shadow-[2px_2px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 cursor-pointer flex items-center gap-1.5 transition-colors">
                ◀ <span>{{ \Carbon\Carbon::parse($prevDate)->translatedFormat('d M') }}</span>
            </a>

            <!-- Display Tanggal & Trigger Datepicker -->
            <div onclick="document.getElementById('dashboardDatePicker').showPicker ? document.getElementById('dashboardDatePicker').showPicker() : document.getElementById('dashboardDatePicker').focus()" 
                 class="bg-[#FFF9DB] hover:bg-[#FFF3BF] border-2 border-black px-4 py-1.5 text-center cursor-pointer select-none neo-box-sm shadow-[2px_2px_0px_0px_#000] transition-colors group">
                <div class="text-[10px] font-black text-slate-600 uppercase tracking-wider">
                    {{ $carbonDate->isToday() ? 'HARI INI' : ($carbonDate->isYesterday() ? 'KEMARIN' : ($carbonDate->isTomorrow() ? 'BESOK' : 'TANGGAL')) }}
                </div>
                <div class="text-xs sm:text-sm font-black text-black font-mono leading-tight group-hover:underline flex items-center gap-1.5">
                    <span>📅</span> {{ $carbonDate->translatedFormat('l, d F Y') }}
                </div>
            </div>

            <!-- Native Hidden Date Input -->
            <form id="dashboardDateForm" action="{{ route('admin.dashboard') }}" method="GET" class="inline">
                <input type="date" 
                       id="dashboardDatePicker" 
                       name="date" 
                       value="{{ $date }}" 
                       onchange="this.form.submit()" 
                       class="sr-only">
            </form>

            <!-- Tombol Hari Berikutnya (>) -->
            <a href="{{ route('admin.dashboard', ['date' => $nextDate]) }}" 
               title="Hari Berikutnya: {{ \Carbon\Carbon::parse($nextDate)->translatedFormat('d M Y') }}"
               class="neo-btn bg-white hover:bg-[#FFD43B] text-black border-2 border-black px-3 py-1.5 font-black text-xs shadow-[2px_2px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 cursor-pointer flex items-center gap-1.5 transition-colors">
                <span>{{ \Carbon\Carbon::parse($nextDate)->translatedFormat('d M') }}</span> ▶
            </a>

            @if(!$carbonDate->isToday())
                <a href="{{ route('admin.dashboard') }}" 
                   class="neo-btn bg-[#20C997] hover:bg-emerald-400 text-black border-2 border-black px-2.5 py-1.5 font-black text-xs shadow-[2px_2px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 cursor-pointer flex items-center gap-1 transition-colors">
                    <span>⚡</span> Hari Ini
                </a>
            @endif
        </div>
    </div>

    <!-- Alert Banner Hari Libur Nasional / Cuti Bersama (Jika Tanggal Terpilih Libur) -->
    @if(isset($holiday) && $holiday && $holiday->is_active)
        <div class="bg-[#FFF4E6] border-3 border-black p-4 sm:p-5 neo-box flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-[4px_4px_0px_0px_#000]">
            <div class="flex items-start gap-3">
                <span class="text-2xl sm:text-3xl shrink-0">🏖️</span>
                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-heading font-black text-xs sm:text-sm tracking-wide uppercase px-2 py-0.5 bg-[#FF6B6B] text-white border-2 border-black shadow-[1.5px_1.5px_0px_0px_#000]">
                            HARI LIBUR: {{ strtoupper($holiday->name) }}
                        </span>
                        <span class="text-xs font-mono font-bold text-amber-900 bg-white/90 border border-black/30 px-2 py-0.5">
                            {{ $holiday->is_cuti_bersama ? '📌 Cuti Bersama Resmi' : ($holiday->is_national ? '🇮🇩 Libur Nasional SKB 3 Menteri' : '🏫 Libur Internal Sekolah') }}
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-amber-950 font-medium">
                        Tanggal <strong>{{ $carbonDate->translatedFormat('l, d F Y') }}</strong> merupakan agenda hari libur. Presensi harian reguler tidak diwajibkan.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- 5 Attendance Key Metric Cards Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4 lg:gap-5">
        <!-- 1. Tingkat Kehadiran (%) -->
        <div class="col-span-2 sm:col-span-1 lg:col-span-1 bg-[#D3F9D8] neo-box p-4 sm:p-5 flex flex-col justify-between border-3 border-black shadow-[4px_4px_0px_0px_#000] relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase tracking-wider text-emerald-950">Persentase Hadir</span>
                <span class="text-xl">📈</span>
            </div>
            <div class="mt-3">
                <div class="flex items-baseline gap-1">
                    <span class="text-3xl sm:text-4xl font-black font-heading text-black">{{ $stats['attendance_rate'] }}%</span>
                </div>
                <!-- Progress Bar -->
                <div class="w-full bg-black/15 h-2 rounded-full border border-black overflow-hidden mt-2">
                    <div class="bg-emerald-600 h-full rounded-full transition-all duration-500" style="width: {{ min(100, $stats['attendance_rate']) }}%"></div>
                </div>
                <div class="text-[11px] font-bold text-emerald-900 mt-2">
                    {{ $stats['present_today'] }} / {{ $stats['total_students'] }} Siswa
                </div>
            </div>
        </div>

        <!-- 2. Hadir Tepat Waktu -->
        <a href="{{ route('admin.reports.attendance', ['status' => 'hadir', 'start_date' => $date, 'end_date' => $date, 'tab' => 'logs']) }}" 
           class="bg-[#EBFBEE] hover:bg-[#D3F9D8] neo-box p-4 sm:p-5 flex flex-col justify-between border-3 border-black shadow-[4px_4px_0px_0px_#000] hover:scale-[1.02] hover:shadow-[6px_6px_0px_0px_#000] transition-all cursor-pointer">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase tracking-wider text-emerald-900">Tepat Waktu</span>
                <span class="text-xl">✅</span>
            </div>
            <div class="mt-3">
                <div class="text-3xl sm:text-4xl font-black font-heading text-black">{{ $stats['hadir_today'] }}</div>
                <div class="text-[11px] font-bold text-emerald-800 mt-1">Hadir Tepat Waktu</div>
            </div>
        </a>

        <!-- 3. Terlambat -->
        <a href="{{ route('admin.reports.attendance', ['status' => 'terlambat', 'start_date' => $date, 'end_date' => $date, 'tab' => 'logs']) }}" 
           class="bg-[#FFF9DB] hover:bg-[#FFF3BF] neo-box p-4 sm:p-5 flex flex-col justify-between border-3 border-black shadow-[4px_4px_0px_0px_#000] hover:scale-[1.02] hover:shadow-[6px_6px_0px_0px_#000] transition-all cursor-pointer">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase tracking-wider text-amber-900">Terlambat</span>
                <span class="text-xl">⏰</span>
            </div>
            <div class="mt-3">
                <div class="text-3xl sm:text-4xl font-black font-heading text-black">{{ $stats['terlambat_today'] }}</div>
                <div class="text-[11px] font-bold text-amber-900 mt-1">> {{ $setting->tolerance_minutes }} menit toleransi</div>
            </div>
        </a>

        <!-- 4. Izin & Sakit -->
        <a href="{{ route('admin.reports.attendance', ['status' => 'izin_sakit', 'start_date' => $date, 'end_date' => $date, 'tab' => 'logs']) }}" 
           class="bg-[#E7F5FF] hover:bg-[#d0ebff] neo-box p-4 sm:p-5 flex flex-col justify-between border-3 border-black shadow-[4px_4px_0px_0px_#000] hover:scale-[1.02] hover:shadow-[6px_6px_0px_0px_#000] transition-all cursor-pointer">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase tracking-wider text-blue-900">Izin & Sakit</span>
                <span class="text-xl">📝</span>
            </div>
            <div class="mt-3">
                <div class="text-3xl sm:text-4xl font-black font-heading text-black">{{ $stats['izin_today'] + $stats['sakit_today'] }}</div>
                <div class="text-[11px] font-bold text-blue-900 mt-1">{{ $stats['sakit_today'] }} Sakit • {{ $stats['izin_today'] }} Izin</div>
            </div>
        </a>

        <!-- 5. Alpa / Belum Presensi -->
        <a href="{{ route('admin.reports.attendance', ['status' => 'alpa', 'start_date' => $date, 'end_date' => $date, 'tab' => 'logs']) }}" 
           class="bg-[#FFE3E3] hover:bg-[#ffc9c9] neo-box p-4 sm:p-5 flex flex-col justify-between border-3 border-black shadow-[4px_4px_0px_0px_#000] hover:scale-[1.02] hover:shadow-[6px_6px_0px_0px_#000] transition-all cursor-pointer">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase tracking-wider text-rose-900">Alpa / Belum</span>
                <span class="text-xl">❌</span>
            </div>
            <div class="mt-3">
                <div class="text-3xl sm:text-4xl font-black font-heading text-black">{{ $stats['alpa_today'] }}</div>
                <div class="text-[11px] font-bold text-rose-900 mt-1">Belum Terdata Hadir</div>
            </div>
        </a>
    </div>

    <!-- Master Data & Operational Metrics Bar (5 Quick Indicators) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        <!-- Siswa Terdaftar -->
        <a href="{{ route('admin.students.index') }}" 
           class="bg-white hover:bg-slate-50 neo-box p-3 border-2 border-black shadow-[3px_3px_0px_0px_#000] flex items-center gap-3 transition-transform active:translate-x-0.5 active:translate-y-0.5">
            <div class="w-10 h-10 bg-[#5294FF] text-white border-2 border-black flex items-center justify-center text-lg font-black shrink-0">
                👥
            </div>
            <div class="min-w-0">
                <div class="text-[10px] font-black uppercase text-slate-500">Total Siswa</div>
                <div class="text-base font-black font-heading text-black">{{ $stats['total_students'] }}</div>
                <div class="text-[10px] font-mono text-slate-600 truncate">L: {{ $stats['male_students'] }} | P: {{ $stats['female_students'] }}</div>
            </div>
        </a>

        <!-- Total Guru -->
        <a href="{{ route('admin.teachers.index') }}" 
           class="bg-white hover:bg-slate-50 neo-box p-3 border-2 border-black shadow-[3px_3px_0px_0px_#000] flex items-center gap-3 transition-transform active:translate-x-0.5 active:translate-y-0.5">
            <div class="w-10 h-10 bg-[#FFD43B] text-black border-2 border-black flex items-center justify-center text-lg font-black shrink-0">
                👨‍🏫
            </div>
            <div class="min-w-0">
                <div class="text-[10px] font-black uppercase text-slate-500">Guru / Tendik</div>
                <div class="text-base font-black font-heading text-black">{{ $stats['total_teachers'] }}</div>
                <div class="text-[10px] font-bold text-slate-600 truncate">Tenaga Pendidik</div>
            </div>
        </a>

        <!-- Total Rombel Kelas -->
        <a href="{{ route('admin.classes.index') }}" 
           class="bg-white hover:bg-slate-50 neo-box p-3 border-2 border-black shadow-[3px_3px_0px_0px_#000] flex items-center gap-3 transition-transform active:translate-x-0.5 active:translate-y-0.5">
            <div class="w-10 h-10 bg-[#20C997] text-white border-2 border-black flex items-center justify-center text-lg font-black shrink-0">
                🏫
            </div>
            <div class="min-w-0">
                <div class="text-[10px] font-black uppercase text-slate-500">Rombel Kelas</div>
                <div class="text-base font-black font-heading text-black">{{ $stats['total_classes'] }}</div>
                <div class="text-[10px] font-bold text-slate-600 truncate">Kelas Binaan</div>
            </div>
        </a>

        <!-- Izin Menunggu Konfirmasi -->
        <div class="bg-white neo-box p-3 border-2 border-black shadow-[3px_3px_0px_0px_#000] flex items-center gap-3">
            <div class="w-10 h-10 {{ $stats['pending_leaves'] > 0 ? 'bg-[#FF6B6B] text-white animate-bounce' : 'bg-slate-100 text-slate-600' }} border-2 border-black flex items-center justify-center text-lg font-black shrink-0">
                📩
            </div>
            <div class="min-w-0">
                <div class="text-[10px] font-black uppercase text-slate-500">Izin Pending</div>
                <div class="text-base font-black font-heading {{ $stats['pending_leaves'] > 0 ? 'text-rose-600 font-black' : 'text-black' }}">
                    {{ $stats['pending_leaves'] }}
                </div>
                <div class="text-[10px] font-bold text-slate-600 truncate">Menunggu Peninjauan</div>
            </div>
        </div>

        <!-- Kas Tabungan Siswa -->
        <a href="{{ route('admin.savings.index') }}" 
           class="col-span-2 sm:col-span-1 bg-[#FFF9DB] hover:bg-[#FFF3BF] neo-box p-3 border-2 border-black shadow-[3px_3px_0px_0px_#000] flex items-center gap-3 transition-transform active:translate-x-0.5 active:translate-y-0.5">
            <div class="w-10 h-10 bg-[#FAB005] text-black border-2 border-black flex items-center justify-center text-lg font-black shrink-0">
                💰
            </div>
            <div class="min-w-0">
                <div class="text-[10px] font-black uppercase text-amber-900">Kas Tabungan</div>
                <div class="text-sm sm:text-base font-black font-mono text-black truncate">
                    Rp {{ number_format($stats['total_savings'], 0, ',', '.') }}
                </div>
                <div class="text-[10px] font-bold text-amber-800 truncate">Saldo Siswa Terhimpun</div>
            </div>
        </a>
    </div>

    <!-- Main Two-Column Layout: Class Monitoring & Activity Feed -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8">
        <!-- Left: Live Class Compliance Monitoring Table (7 Cols) -->
        <div class="lg:col-span-7 space-y-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                <div class="space-y-0.5">
                    <h2 class="font-heading font-black text-lg sm:text-xl text-black flex items-center gap-2">
                        <span>🏫</span> Monitoring Presensi Per Kelas
                    </h2>
                    <p class="text-xs text-slate-600 font-medium">
                        Status kepatuhan pengisian presensi siswa pada tanggal <strong>{{ $carbonDate->translatedFormat('d F Y') }}</strong>
                    </p>
                </div>
                <span class="neo-badge bg-[#FFD43B] text-black text-xs font-black uppercase">
                    {{ $classes->count() }} KELAS
                </span>
            </div>

            <div class="bg-white neo-box overflow-hidden border-3 border-black shadow-[4px_4px_0px_0px_#000]">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[#FFF9DB] border-b-2 border-black text-xs font-black uppercase tracking-wider text-black">
                            <tr>
                                <th class="p-3 border-r-2 border-black w-10 text-center">No</th>
                                <th class="p-3 border-r-2 border-black">Kelas & Wali</th>
                                <th class="p-3 border-r-2 border-black text-center">Status Pengisian</th>
                                <th class="p-3 border-r-2 border-black text-center">Rincian Kehadiran</th>
                                <th class="p-3 text-center w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-black">
                            @forelse($classes as $idx => $cls)
                                @php
                                    $studentCount = $cls->students->count();
                                    $classAtts = $cls->students->flatMap->attendances;
                                    $recordedCount = $classAtts->count();
                                    
                                    $hadirClass = $classAtts->where('status', 'hadir')->count();
                                    $terlambatClass = $classAtts->where('status', 'terlambat')->count();
                                    $izinClass = $classAtts->where('status', 'izin')->count();
                                    $sakitClass = $classAtts->where('status', 'sakit')->count();
                                    $alpaClass = $classAtts->where('status', 'alpa')->count();

                                    $isComplete = $studentCount > 0 && $recordedCount >= $studentCount;
                                    $isPartial = $recordedCount > 0 && $recordedCount < $studentCount;
                                    $isUnfilled = $recordedCount === 0;
                                @endphp
                                <tr class="hover:bg-slate-50 transition-colors {{ $isComplete ? 'bg-[#F4FBF7]/40' : '' }}">
                                    <!-- No -->
                                    <td class="p-3 text-center font-mono font-bold text-xs border-r-2 border-black">
                                        {{ $idx + 1 }}
                                    </td>

                                    <!-- Identitas Kelas & Wali -->
                                    <td class="p-3 border-r-2 border-black">
                                        <div class="flex items-center gap-2">
                                            <span class="font-heading font-black text-black text-sm">{{ $cls->name }}</span>
                                            <span class="neo-badge bg-slate-100 text-black text-[10px] py-0 px-1 border border-black font-bold">
                                                {{ $cls->level }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-slate-600 mt-0.5 flex items-center gap-1.5">
                                            <span>👨‍🏫</span>
                                            <span class="font-semibold">{{ $cls->homeroomTeacher->user->name ?? 'Belum Ditentukan' }}</span>
                                            <span class="text-slate-400">•</span>
                                            <span class="font-mono font-bold text-[11px] text-slate-700">{{ $studentCount }} Siswa</span>
                                        </div>
                                    </td>

                                    <!-- Status Kepatuhan Pengisian -->
                                    <td class="p-3 border-r-2 border-black text-center">
                                        @if($studentCount === 0)
                                            <span class="neo-badge bg-slate-200 text-slate-700 text-[10px] font-bold">
                                                Tanpa Siswa
                                            </span>
                                        @elseif($isComplete)
                                            <span class="neo-badge bg-[#20C997] text-white text-[10px] font-black border border-black shadow-[1px_1px_0px_0px_#000]">
                                                ✓ LENGKAP ({{ $recordedCount }}/{{ $studentCount }})
                                            </span>
                                        @elseif($isPartial)
                                            <span class="neo-badge bg-[#FFD43B] text-black text-[10px] font-black border border-black shadow-[1px_1px_0px_0px_#000]">
                                                ⚠ SEBAGIAN ({{ $recordedCount }}/{{ $studentCount }})
                                            </span>
                                        @else
                                            <span class="neo-badge bg-[#FF6B6B] text-white text-[10px] font-black border border-black shadow-[1px_1px_0px_0px_#000]">
                                                ✕ BELUM DIISI
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Rincian H / T / I / S / A -->
                                    <td class="p-3 border-r-2 border-black text-center font-mono text-xs">
                                        @if($recordedCount > 0)
                                            <div class="inline-flex items-center gap-1 bg-white border border-black px-2 py-0.5 rounded-xs shadow-[1px_1px_0px_0px_#000]">
                                                <span class="text-emerald-700 font-bold" title="Hadir: {{ $hadirClass }}">H:{{ $hadirClass }}</span>
                                                <span class="text-slate-300">|</span>
                                                <span class="text-blue-700 font-bold" title="Terlambat: {{ $terlambatClass }}">T:{{ $terlambatClass }}</span>
                                                <span class="text-slate-300">|</span>
                                                <span class="text-slate-600 font-bold" title="Izin: {{ $izinClass }}">I:{{ $izinClass }}</span>
                                                <span class="text-slate-300">|</span>
                                                <span class="text-amber-700 font-bold" title="Sakit: {{ $sakitClass }}">S:{{ $sakitClass }}</span>
                                                <span class="text-slate-300">|</span>
                                                <span class="text-rose-700 font-bold" title="Alpa: {{ $alpaClass }}">A:{{ $alpaClass }}</span>
                                            </div>
                                        @else
                                            <span class="text-xs text-slate-400 italic">Belum ada data</span>
                                        @endif
                                    </td>

                                    <!-- Tombol Input / Periksa -->
                                    <td class="p-3 text-center">
                                        <a href="{{ route('admin.attendances.manual', ['school_class_id' => $cls->id, 'date' => $date]) }}" 
                                           class="neo-btn bg-white hover:bg-[#FFD43B] text-black text-xs font-black px-2.5 py-1 border-2 border-black shadow-[1.5px_1.5px_0px_0px_#000] inline-flex items-center gap-1 cursor-pointer transition-transform active:translate-x-0.5 active:translate-y-0.5">
                                            <span>✏️</span> {{ $isComplete ? 'Edit' : 'Isi' }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-6 text-center text-slate-500 font-semibold">
                                        Belum ada data kelas yang terdaftar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right: Tabbed Operational Feed & Daily Activity (5 Cols) -->
        <div class="lg:col-span-5 space-y-4">
            <!-- Tabs Navigation Header -->
            <div class="flex items-center justify-between border-b-2 border-black pb-2">
                <div class="flex items-center gap-2">
                    <button type="button" onclick="switchDashboardTab('feed')" id="tabBtnFeed" 
                            class="neo-btn bg-black text-white text-xs font-black px-3 py-1.5 border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                        ⏱️ Log Scan & Manual
                    </button>
                    <button type="button" onclick="switchDashboardTab('activities')" id="tabBtnActivities" 
                            class="neo-btn bg-white hover:bg-slate-100 text-black text-xs font-bold px-3 py-1.5 border-2 border-black cursor-pointer">
                        📑 Jurnal & Izin
                    </button>
                </div>
                <span class="text-[11px] font-mono font-bold text-slate-600">
                    {{ $carbonDate->translatedFormat('d M') }}
                </span>
            </div>

            <!-- Tab 1: Live Feed Presensi Terkini -->
            <div id="tabContentFeed" class="space-y-3">
                <div class="bg-white neo-box p-4 space-y-3 border-3 border-black shadow-[4px_4px_0px_0px_#000]">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                        <span class="text-xs font-black uppercase text-black">Aktivitas Presensi Terakhir</span>
                        <a href="{{ route('admin.reports.attendance', ['start_date' => $date, 'end_date' => $date, 'tab' => 'logs']) }}" 
                           class="text-xs font-bold text-blue-700 hover:underline flex items-center gap-1">
                            <span>Lihat Semua Log</span> <span>→</span>
                        </a>
                    </div>

                    <div class="space-y-2 max-h-[460px] overflow-y-auto pr-1">
                        @forelse($recentAttendances as $att)
                            <div class="p-2.5 border-2 border-black rounded-xs flex items-center justify-between gap-2.5 
                                @if($att->status === 'hadir') bg-[#F4FBF7] 
                                @elseif($att->status === 'terlambat') bg-[#FFFDF5] 
                                @elseif($att->status === 'izin' || $att->status === 'sakit') bg-[#F0F8FF] 
                                @else bg-slate-50 @endif">
                                
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 bg-white border-2 border-black flex items-center justify-center font-black text-xs shrink-0 shadow-[1px_1px_0px_0px_#000]">
                                        {{ substr($att->student->user->name ?? 'S', 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-xs text-black truncate">{{ $att->student->user->name ?? 'Siswa' }}</div>
                                        <div class="text-[10px] text-slate-600 flex items-center gap-1 font-mono">
                                            <span>{{ $att->student->schoolClass->name ?? '-' }}</span>
                                            <span>•</span>
                                            <span class="font-bold text-black">{{ $att->check_in_time ? $att->check_in_time->format('H:i') . ' WIB' : '-' }}</span>
                                            <span>•</span>
                                            <span class="uppercase text-[9px] px-1 py-0 border border-slate-400 bg-white font-bold rounded-xs">
                                                {{ $att->method === 'qr' ? '📷 QR' : '✍️ Manual' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="shrink-0">
                                    <span class="neo-badge text-[10px] font-black uppercase px-2 py-0.5 border border-black shadow-[1px_1px_0px_0px_#000]
                                        @if($att->status === 'hadir') bg-[#20C997] text-white 
                                        @elseif($att->status === 'terlambat') bg-[#FFD43B] text-black 
                                        @elseif($att->status === 'izin') bg-[#868E96] text-white 
                                        @elseif($att->status === 'sakit') bg-[#FAB005] text-black 
                                        @else bg-[#FF6B6B] text-white @endif">
                                        {{ $att->status }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-slate-500 font-semibold space-y-2">
                                <div class="text-3xl">📭</div>
                                <div class="text-xs">Belum ada aktivitas presensi pada tanggal ini.</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Tab 2: Pengajuan Izin Pending & Jurnal Guru Hari Ini -->
            <div id="tabContentActivities" class="space-y-4 hidden">
                <!-- Sub-Widget 1: Pengajuan Izin Menunggu Konfirmasi -->
                <div class="bg-white neo-box p-4 space-y-3 border-3 border-black shadow-[4px_4px_0px_0px_#000]">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                        <span class="text-xs font-black uppercase text-black flex items-center gap-1.5">
                            <span>📩</span> Pengajuan Izin Menunggu
                        </span>
                        <span class="neo-badge bg-[#FF6B6B] text-white text-[10px] font-black">
                            {{ $recentPendingLeaves->count() }} PENDING
                        </span>
                    </div>

                    <div class="space-y-2">
                        @forelse($recentPendingLeaves as $leave)
                            <div class="p-2.5 border-2 border-black bg-rose-50/50 rounded-xs space-y-1">
                                <div class="flex items-center justify-between">
                                    <div class="font-bold text-xs text-black">{{ $leave->student->user->name ?? 'Siswa' }}</div>
                                    <span class="neo-badge bg-[#FFD43B] text-black text-[9px] uppercase font-black">
                                        {{ $leave->type }}
                                    </span>
                                </div>
                                <div class="text-[11px] text-slate-600 flex items-center gap-2">
                                    <span>Kelas: <b>{{ $leave->student->schoolClass->name ?? '-' }}</b></span>
                                    <span>•</span>
                                    <span>{{ $leave->date_from->format('d M') }} s/d {{ $leave->date_to->format('d M') }}</span>
                                </div>
                                @if($leave->reason)
                                    <div class="text-[11px] text-slate-700 italic bg-white p-1.5 border border-slate-300 rounded-xs">
                                        "{{ Str::limit($leave->reason, 80) }}"
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="p-4 text-center text-slate-500 font-semibold text-xs">
                                ✓ Tidak ada pengajuan izin yang menunggu persetujuan.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Sub-Widget 2: Jurnal Mengajar Guru Tanggal Terpilih -->
                <div class="bg-white neo-box p-4 space-y-3 border-3 border-black shadow-[4px_4px_0px_0px_#000]">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                        <span class="text-xs font-black uppercase text-black flex items-center gap-1.5">
                            <span>📖</span> Jurnal Mengajar Hari Ini
                        </span>
                        <a href="{{ route('admin.teaching-journals.index') }}" 
                           class="text-xs font-bold text-blue-700 hover:underline">
                            Semua Jurnal →
                        </a>
                    </div>

                    <div class="space-y-2">
                        @forelse($recentJournals as $jrn)
                            <div class="p-2.5 border-2 border-black bg-[#FFF9DB]/40 rounded-xs space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-xs text-black">{{ $jrn->subject->name ?? 'Mata Pelajaran' }}</span>
                                    <span class="neo-badge bg-[#5294FF] text-white text-[9px] font-bold">
                                        Pertemuan ke-{{ $jrn->meeting_number }}
                                    </span>
                                </div>
                                <div class="text-[11px] text-slate-600 flex items-center gap-2">
                                    <span>Guru: <b>{{ $jrn->teacher->user->name ?? '-' }}</b></span>
                                    <span>•</span>
                                    <span>Kelas: <b>{{ $jrn->schoolClass->name ?? '-' }}</b></span>
                                </div>
                                @if($jrn->learning_objective)
                                    <div class="text-[11px] text-slate-700 bg-white p-1 border border-slate-200 rounded-xs truncate">
                                        {{ $jrn->learning_objective }}
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="p-4 text-center text-slate-500 font-semibold text-xs">
                                Belum ada jurnal mengajar guru yang tercatat pada tanggal ini.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Tab Switcher pada Panel Kanan Dashboard
    function switchDashboardTab(tab) {
        const tabBtnFeed = document.getElementById('tabBtnFeed');
        const tabBtnActivities = document.getElementById('tabBtnActivities');
        const tabContentFeed = document.getElementById('tabContentFeed');
        const tabContentActivities = document.getElementById('tabContentActivities');

        if (tab === 'feed') {
            tabContentFeed.classList.remove('hidden');
            tabContentActivities.classList.add('hidden');

            tabBtnFeed.classList.remove('bg-white', 'text-black', 'font-bold');
            tabBtnFeed.classList.add('bg-black', 'text-white', 'font-black', 'shadow-[2px_2px_0px_0px_#000]');

            tabBtnActivities.classList.remove('bg-black', 'text-white', 'font-black', 'shadow-[2px_2px_0px_0px_#000]');
            tabBtnActivities.classList.add('bg-white', 'text-black', 'font-bold');
        } else {
            tabContentFeed.classList.add('hidden');
            tabContentActivities.classList.remove('hidden');

            tabBtnActivities.classList.remove('bg-white', 'text-black', 'font-bold');
            tabBtnActivities.classList.add('bg-black', 'text-white', 'font-black', 'shadow-[2px_2px_0px_0px_#000]');

            tabBtnFeed.classList.remove('bg-black', 'text-white', 'font-black', 'shadow-[2px_2px_0px_0px_#000]');
            tabBtnFeed.classList.add('bg-white', 'text-black', 'font-bold');
        }
    }
</script>
@endpush
@endsection
