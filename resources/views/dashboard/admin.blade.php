@extends('layouts.admin')

@section('title', 'Dashboard Administrator')
@section('page-title', 'Ringkasan & Statistik Absensi')

@section('content')
<div class="space-y-8">
    <!-- Top Welcome Banner -->
    <div class="bg-[#5294FF] neo-box-lg p-6 sm:p-8 text-white relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="space-y-2 z-10">
            <div class="flex items-center gap-2">
                <span class="neo-badge bg-[#FFD43B] text-black">ADMINISTRATOR</span>
                <span class="text-xs font-mono font-bold bg-black/20 px-2 py-0.5 border border-black text-white">
                    {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                </span>
            </div>
            <h1 class="font-heading text-2xl sm:text-4xl font-black text-white tracking-tight">
                PANEL KONTROL ABSENSI
            </h1>
            <p class="text-sm font-semibold text-slate-900 max-w-xl text-yellow-500">
                Pantau statistik kehadiran seluruh siswa, status per kelas, dan konfigurasi presensi sekolah secara terpusat.
            </p>
        </div>

        <div class="flex flex-wrap gap-2 z-10">
            <div class="bg-white text-black neo-box px-4 py-3 text-center">
                <div class="text-[10px] uppercase font-bold text-slate-500">Mode Sistem</div>
                <div class="text-sm font-black font-heading uppercase text-blue-700">{{ $setting->mode }}</div>
            </div>
            <div class="bg-white text-black neo-box px-4 py-3 text-center">
                <div class="text-[10px] uppercase font-bold text-slate-500">Toleransi</div>
                <div class="text-sm font-black font-heading text-amber-700">{{ $setting->tolerance_minutes }} Menit</div>
            </div>
        </div>
    </div>

    <!-- 4 Stats Cards Grid -->
    @php $today = \Carbon\Carbon::today()->toDateString(); @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <!-- Hadir -->
        <a href="{{ route('admin.reports.attendance', ['status' => 'hadir', 'start_date' => $today, 'end_date' => $today, 'tab' => 'logs']) }}" class="bg-[#D3F9D8] neo-box p-5 flex flex-col justify-between cursor-pointer hover:scale-[1.02] hover:shadow-[5px_5px_0px_0px_#000] transition-all duration-150">
            <div class="flex items-center justify-between">
                <span class="text-xs font-black uppercase tracking-wider text-emerald-900">Hadir Hari Ini</span>
                <span class="text-xl">✅</span>
            </div>
            <div class="mt-4">
                <div class="text-3xl sm:text-4xl font-black font-heading text-black">{{ $stats['hadir_today'] }}</div>
                <div class="text-xs font-semibold text-emerald-800 mt-1">Siswa tepat waktu</div>
            </div>
        </a>

        <!-- Terlambat -->
        <a href="{{ route('admin.reports.attendance', ['status' => 'terlambat', 'start_date' => $today, 'end_date' => $today, 'tab' => 'logs']) }}" class="bg-[#FFF3BF] neo-box p-5 flex flex-col justify-between cursor-pointer hover:scale-[1.02] hover:shadow-[5px_5px_0px_0px_#000] transition-all duration-150">
            <div class="flex items-center justify-between">
                <span class="text-xs font-black uppercase tracking-wider text-amber-900">Terlambat</span>
                <span class="text-xl">⏰</span>
            </div>
            <div class="mt-4">
                <div class="text-3xl sm:text-4xl font-black font-heading text-black">{{ $stats['terlambat_today'] }}</div>
                <div class="text-xs font-semibold text-amber-800 mt-1">Melebihi batas toleransi</div>
            </div>
        </a>

        <!-- Izin & Sakit -->
        <a href="{{ route('admin.reports.attendance', ['status' => 'izin_sakit', 'start_date' => $today, 'end_date' => $today, 'tab' => 'logs']) }}" class="bg-[#E7F5FF] neo-box p-5 flex flex-col justify-between cursor-pointer hover:scale-[1.02] hover:shadow-[5px_5px_0px_0px_#000] transition-all duration-150">
            <div class="flex items-center justify-between">
                <span class="text-xs font-black uppercase tracking-wider text-blue-900">Izin & Sakit</span>
                <span class="text-xl">📝</span>
            </div>
            <div class="mt-4">
                <div class="text-3xl sm:text-4xl font-black font-heading text-black">{{ $stats['izin_today'] + $stats['sakit_today'] }}</div>
                <div class="text-xs font-semibold text-blue-800 mt-1">{{ $stats['sakit_today'] }} Sakit, {{ $stats['izin_today'] }} Izin</div>
            </div>
        </a>

        <!-- Alpa -->
        <a href="{{ route('admin.reports.attendance', ['status' => 'alpa', 'start_date' => $today, 'end_date' => $today, 'tab' => 'logs']) }}" class="bg-[#FFE3E3] neo-box p-5 flex flex-col justify-between cursor-pointer hover:scale-[1.02] hover:shadow-[5px_5px_0px_0px_#000] transition-all duration-150">
            <div class="flex items-center justify-between">
                <span class="text-xs font-black uppercase tracking-wider text-rose-900">Tanpa Keterangan</span>
                <span class="text-xl">❌</span>
            </div>
            <div class="mt-4">
                <div class="text-3xl sm:text-4xl font-black font-heading text-black">{{ $stats['alpa_today'] }}</div>
                <div class="text-xs font-semibold text-rose-800 mt-1">Belum melakukan presensi</div>
            </div>
        </a>
    </div>

    <!-- Two-Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Left: Class List & Homeroom Teachers -->
        <div class="lg:col-span-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="font-heading font-black text-xl text-black flex items-center gap-2">
                    <span>🏫</span> Data Kelas & Wali Kelas
                </h2>
                <span class="neo-badge bg-[#FFD43B] text-black">{{ $classes->count() }} KELAS</span>
            </div>

            <div class="bg-white neo-box overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[#FFF9DB] border-b-2 border-black text-xs font-black uppercase tracking-wider">
                            <tr>
                                <th class="p-3 border-r border-black">Kelas</th>
                                <th class="p-3 border-r border-black">Jenjang</th>
                                <th class="p-3 border-r border-black">Wali Kelas</th>
                                <th class="p-3 text-center">Siswa</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-black">
                            @forelse($classes as $cls)
                            <tr class="hover:bg-slate-50 font-medium">
                                <td class="p-3 font-bold text-black border-r border-black">{{ $cls->name }}</td>
                                <td class="p-3 border-r border-black">
                                    <span class="neo-badge bg-slate-100 text-black text-[10px]">{{ $cls->level }}</span>
                                </td>
                                <td class="p-3 border-r border-black text-xs">
                                    {{ $cls->homeroomTeacher->user->name ?? 'Belum Ditentukan' }}
                                </td>
                                <td class="p-3 text-center font-bold">
                                    <span class="bg-[#D3F9D8] px-2 py-0.5 border border-black text-xs">
                                        {{ $cls->students->count() }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="p-4 text-center text-slate-500 font-semibold">Belum ada data kelas.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right: Real-time Attendance Feed -->
        <div class="lg:col-span-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="font-heading font-black text-xl text-black flex items-center gap-2">
                    <span>⏱</span> Log Presensi Terbaru
                </h2>
                <span class="text-xs font-bold text-slate-600">Hari Ini</span>
            </div>

            <div class="bg-white neo-box p-4 space-y-3">
                @forelse($recentAttendances as $att)
                <div class="p-3 border-2 border-black rounded-sm flex items-center justify-between gap-3 
                    @if($att->status === 'hadir') bg-[#F4FBF7] 
                    @elseif($att->status === 'terlambat') bg-[#FFFDF5] 
                    @else bg-slate-50 @endif">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-white border-2 border-black flex items-center justify-center font-black text-sm">
                            {{ substr($att->student->user->name ?? 'S', 0, 1) }}
                        </div>
                        <div>
                            <div class="font-bold text-sm text-black">{{ $att->student->user->name ?? 'Nama Siswa' }}</div>
                            <div class="text-xs text-slate-600 flex items-center gap-2">
                                <span>{{ $att->student->schoolClass->name ?? '-' }}</span>
                                <span>•</span>
                                <span class="font-mono text-[11px]">{{ $att->check_in_time ? $att->check_in_time->format('H:i') . ' WIB' : '-' }}</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <span class="neo-badge 
                            @if($att->status === 'hadir') bg-[#20C997] text-white 
                            @elseif($att->status === 'terlambat') bg-[#FFD43B] text-black 
                            @else bg-[#FF6B6B] text-white @endif">
                            {{ $att->status }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="p-6 text-center text-slate-500 font-semibold">
                    Belum ada presensi yang masuk hari ini.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
