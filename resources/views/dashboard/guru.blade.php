@extends('layouts.neobrutalism')

@section('title', 'Dashboard Guru')

@section('content')
<div class="space-y-8">
    <!-- Top Welcome Banner -->
    <div class="bg-[#FFF3BF] neo-box-lg p-6 sm:p-8 relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="space-y-2 z-10">
            <div class="flex items-center gap-2">
                <span class="neo-badge bg-[#5294FF] text-white">DEWAN GURU</span>
                <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border border-black">
                    NIP: {{ $teacher->nip ?? '-' }}
                </span>
            </div>
            <h1 class="font-heading text-2xl sm:text-3xl font-black text-black tracking-tight">
                HALO, {{ Auth::user()->name }}!
            </h1>
            <p class="text-sm font-semibold text-slate-800 max-w-xl">
                Kelola presensi mata pelajaran yang Anda ampu hari ini dan pantau kehadiran siswa kelas binaan Anda.
            </p>
        </div>

        <div class="flex flex-wrap gap-2 z-10">
            <button class="neo-btn bg-[#20C997] text-black px-4 py-2.5 text-xs uppercase flex items-center gap-2 cursor-pointer">
                <span>📷</span> Buka Scanner QR
            </button>
            <button class="neo-btn bg-white text-black px-4 py-2.5 text-xs uppercase flex items-center gap-2 cursor-pointer">
                <span>📋</span> Input Absen Manual
            </button>
        </div>
    </div>

    <!-- Jadwal Mengajar Hari Ini -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="font-heading font-black text-xl text-black flex items-center gap-2">
                <span>📚</span> Jadwal Mengajar Hari Ini
            </h2>
            <span class="text-xs font-bold font-mono bg-white border border-black px-2 py-1">
                {{ \Carbon\Carbon::now()->translatedFormat('l') }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($todaySchedules as $sch)
            <div class="bg-white neo-box p-5 flex flex-col justify-between space-y-4">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <span class="neo-badge bg-[#E7F5FF] text-blue-900 text-[10px] mb-1 inline-block">
                            {{ $sch->subject->code ?? 'MAPEL' }}
                        </span>
                        <h3 class="font-heading font-bold text-lg text-black">{{ $sch->subject->name ?? 'Mata Pelajaran' }}</h3>
                        <p class="text-xs font-bold text-slate-600 mt-0.5">{{ $sch->schoolClass->name ?? 'Kelas' }}</p>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-mono font-bold bg-[#FFF9DB] border border-black px-2 py-1 inline-block">
                            {{ substr($sch->start_time, 0, 5) }} - {{ substr($sch->end_time, 0, 5) }}
                        </span>
                    </div>
                </div>

                <div class="pt-3 border-t-2 border-slate-100 flex items-center justify-between gap-2">
                    <span class="text-[11px] text-slate-500 font-semibold">Ruang Kelas Utama</span>
                    <button class="neo-btn bg-[#5294FF] text-white text-[11px] px-3 py-1.5 cursor-pointer">
                        Presensi Kelas →
                    </button>
                </div>
            </div>
            @empty
            <div class="col-span-full bg-white neo-box p-8 text-center text-slate-500 font-semibold">
                Tidak ada jadwal mengajar pada hari ini.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Kelas Binaan (Wali Kelas) -->
    @if($homeroomClasses->isNotEmpty())
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="font-heading font-black text-xl text-black flex items-center gap-2">
                <span>🛡</span> Siswa Kelas Binaan (Wali Kelas)
            </h2>
            <span class="neo-badge bg-[#20C997] text-white">WALI KELAS AKTIF</span>
        </div>

        @foreach($homeroomClasses as $hClass)
        <div class="bg-white neo-box p-5 space-y-4">
            <div class="flex items-center justify-between border-b-2 border-black pb-3">
                <h3 class="font-heading font-black text-lg text-black">{{ $hClass->name }}</h3>
                <span class="text-xs font-bold bg-[#D3F9D8] border border-black px-2 py-0.5">
                    {{ $hClass->students->count() }} Siswa Terdaftar
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-100 border-b-2 border-black text-xs font-black uppercase">
                        <tr>
                            <th class="p-2.5 border-r border-black">NIS</th>
                            <th class="p-2.5 border-r border-black">Nama Siswa</th>
                            <th class="p-2.5 border-r border-black">Jenis Kelamin</th>
                            <th class="p-2.5 text-center">Status Hari Ini</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-black">
                        @forelse($hClass->students as $stu)
                        @php
                            $att = $stu->attendances->first();
                        @endphp
                        <tr class="hover:bg-slate-50 font-medium">
                            <td class="p-2.5 font-mono text-xs font-bold border-r border-black">{{ $stu->nis }}</td>
                            <td class="p-2.5 font-bold text-black border-r border-black">{{ $stu->user->name ?? '-' }}</td>
                            <td class="p-2.5 border-r border-black text-xs">{{ $stu->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                            <td class="p-2.5 text-center">
                                @if($att)
                                    <span class="neo-badge 
                                        @if($att->status === 'hadir') bg-[#20C997] text-white 
                                        @elseif($att->status === 'terlambat') bg-[#FFD43B] text-black 
                                        @else bg-[#FF6B6B] text-white @endif">
                                        {{ $att->status }}
                                    </span>
                                @else
                                    <span class="neo-badge bg-slate-200 text-slate-700">Belum Hadir</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="p-4 text-center text-slate-500">Belum ada data siswa di kelas ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
