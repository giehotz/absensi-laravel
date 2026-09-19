@extends('layouts.neobrutalism')

@section('title', 'Dashboard Siswa')

@section('content')
<div class="space-y-8">
    <!-- Top Welcome Banner -->
    <div class="bg-[#D3F9D8] neo-box-lg p-6 sm:p-8 relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="space-y-2 z-10">
            <div class="flex items-center gap-2">
                <span class="neo-badge bg-[#5294FF] text-white">PORTAL SISWA</span>
                <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border border-black">
                    NIS: {{ $student->nis }}
                </span>
            </div>
            <h1 class="font-heading text-2xl sm:text-3xl font-black text-black tracking-tight">
                HALO, {{ Auth::user()->name }}!
            </h1>
            <p class="text-sm font-semibold text-emerald-950 max-w-xl">
                Tunjukkan QR Code di kartu digital Anda ke scanner kamera sekolah saat tiba untuk mencatat presensi harian.
            </p>
        </div>

        <div class="flex flex-wrap gap-2 z-10">
            <button class="neo-btn bg-[#FF6B6B] text-white px-4 py-2.5 text-xs uppercase flex items-center gap-2 cursor-pointer">
                <span>📄</span> Ajukan Izin / Sakit
            </button>
        </div>
    </div>

    <!-- Grid 2 Column: Digital Card vs Today's Status -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Left: Digital Student ID Card with Dynamic QR -->
        <div class="lg:col-span-5 space-y-4">
            <h2 class="font-heading font-black text-xl text-black flex items-center gap-2">
                <span>🪪</span> Kartu Pelajar Digital
            </h2>

            <div class="bg-white neo-box-lg p-6 space-y-5 relative overflow-hidden">
                <div class="absolute top-0 right-0 bg-[#FFD43B] text-black font-mono font-bold text-[10px] px-3 py-1 border-b-2 border-l-2 border-black">
                    AKTIF 2026/2027
                </div>

                <!-- Student Identity -->
                <div class="flex items-center gap-4 pt-2">
                    <div class="w-16 h-16 bg-[#5294FF] border-3 border-black flex items-center justify-center font-heading font-black text-2xl text-white neo-box-sm shrink-0">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-lg text-black leading-tight">{{ Auth::user()->name }}</h3>
                        <p class="text-xs font-bold text-slate-600 mt-0.5">{{ $student->schoolClass->name ?? 'Kelas Siswa' }}</p>
                        <p class="text-[11px] font-mono text-slate-500">NIS: {{ $student->nis }} • NISN: {{ $student->nisn ?? '-' }}</p>
                    </div>
                </div>

                <!-- QR Code Box (Neobrutalism Frame) -->
                <div class="bg-[#FFF9DB] border-3 border-black p-4 text-center space-y-3 neo-box">
                    <div class="flex items-center justify-between text-[11px] font-bold">
                        <span class="text-slate-700">QR TOKEN PRESENSI</span>
                        <span class="text-emerald-700 flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span> Live Token
                        </span>
                    </div>

                    <!-- Simulated Dynamic QR Box -->
                    <div class="bg-white border-2 border-black p-4 inline-block shadow-[3px_3px_0px_0px_#000]">
                        <svg class="w-36 h-36 mx-auto" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- QR Pattern Matrix Mockup -->
                            <rect width="100" height="100" fill="white"/>
                            <rect x="10" y="10" width="25" height="25" fill="black"/>
                            <rect x="15" y="15" width="15" height="15" fill="white"/>
                            <rect x="18" y="18" width="9" height="9" fill="black"/>

                            <rect x="65" y="10" width="25" height="25" fill="black"/>
                            <rect x="70" y="15" width="15" height="15" fill="white"/>
                            <rect x="73" y="18" width="9" height="9" fill="black"/>

                            <rect x="10" y="65" width="25" height="25" fill="black"/>
                            <rect x="15" y="70" width="15" height="15" fill="white"/>
                            <rect x="18" y="73" width="9" height="9" fill="black"/>

                            <rect x="42" y="12" width="6" height="18" fill="black"/>
                            <rect x="42" y="38" width="18" height="6" fill="black"/>
                            <rect x="65" y="42" width="12" height="12" fill="black"/>
                            <rect x="42" y="65" width="18" height="18" fill="black"/>
                            <rect x="70" y="70" width="15" height="15" fill="black"/>
                        </svg>
                    </div>

                    <div class="text-[11px] font-mono font-bold text-slate-800 break-all">
                        {{ $activeToken->token ?? $student->qr_code_identifier }}
                    </div>
                    <div class="text-[10px] text-slate-500 font-semibold">
                        Token berganti berkala untuk mencegah titip absen
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Status Today & Attendance Stats -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Today Status Card -->
            <div class="bg-white neo-box-lg p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-600">Presensi Hari Ini</span>
                    <span class="font-mono text-xs font-bold text-black">{{ \Carbon\Carbon::now()->format('d M Y') }}</span>
                </div>

                @if($todayAttendance)
                <div class="p-4 bg-[#D3F9D8] border-2 border-black rounded-sm flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-emerald-900 uppercase">Status Kehadiran</div>
                        <div class="text-2xl font-black font-heading uppercase text-emerald-950 mt-0.5">
                            {{ $todayAttendance->status }}
                        </div>
                        <p class="text-xs text-emerald-800 mt-1 font-medium">{{ $todayAttendance->notes ?? 'Presensi tercatat sukses.' }}</p>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] font-bold text-emerald-900 uppercase">Jam Masuk</div>
                        <div class="text-xl font-black font-mono text-black">
                            {{ $todayAttendance->check_in_time ? $todayAttendance->check_in_time->format('H:i') . ' WIB' : '-' }}
                        </div>
                    </div>
                </div>
                @else
                <div class="p-4 bg-[#FFE3E3] border-2 border-black rounded-sm flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-rose-900 uppercase">Status Kehadiran</div>
                        <div class="text-xl font-black font-heading text-rose-950 mt-0.5">Belum Melakukan Presensi</div>
                        <p class="text-xs text-rose-800 mt-1 font-medium">Silakan lakukan scan QR di gerbang atau ruang kelas.</p>
                    </div>
                    <span class="text-3xl">⚠️</span>
                </div>
                @endif

                <!-- Monthly Stats -->
                <div class="grid grid-cols-4 gap-3 pt-3 border-t-2 border-black">
                    <div class="p-2.5 bg-[#D3F9D8] border border-black text-center">
                        <div class="text-[10px] font-bold text-slate-700">HADIR</div>
                        <div class="text-lg font-black font-heading text-black">{{ $summaryMonth['hadir'] }}</div>
                    </div>
                    <div class="p-2.5 bg-[#FFF3BF] border border-black text-center">
                        <div class="text-[10px] font-bold text-slate-700">TERLAMBAT</div>
                        <div class="text-lg font-black font-heading text-black">{{ $summaryMonth['terlambat'] }}</div>
                    </div>
                    <div class="p-2.5 bg-[#E7F5FF] border border-black text-center">
                        <div class="text-[10px] font-bold text-slate-700">IZIN</div>
                        <div class="text-lg font-black font-heading text-black">{{ $summaryMonth['izin'] }}</div>
                    </div>
                    <div class="p-2.5 bg-[#FFE3E3] border border-black text-center">
                        <div class="text-[10px] font-bold text-slate-700">SAKIT</div>
                        <div class="text-lg font-black font-heading text-black">{{ $summaryMonth['sakit'] }}</div>
                    </div>
                </div>
            </div>

            <!-- Recent History Table -->
            <div class="bg-white neo-box p-5 space-y-3">
                <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                    <span>📅</span> Riwayat Presensi Terakhir
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-100 border-b-2 border-black text-xs font-black uppercase">
                            <tr>
                                <th class="p-2.5 border-r border-black">Tanggal</th>
                                <th class="p-2.5 border-r border-black">Jam Masuk</th>
                                <th class="p-2.5 border-r border-black">Jam Pulang</th>
                                <th class="p-2.5 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-black font-medium">
                            @forelse($history as $h)
                            <tr class="hover:bg-slate-50">
                                <td class="p-2.5 font-bold border-r border-black">{{ $h->date->format('d/m/Y') }}</td>
                                <td class="p-2.5 font-mono text-xs border-r border-black">{{ $h->check_in_time ? $h->check_in_time->format('H:i') : '-' }}</td>
                                <td class="p-2.5 font-mono text-xs border-r border-black">{{ $h->check_out_time ? $h->check_out_time->format('H:i') : '-' }}</td>
                                <td class="p-2.5 text-center">
                                    <span class="neo-badge 
                                        @if($h->status === 'hadir') bg-[#20C997] text-white 
                                        @elseif($h->status === 'terlambat') bg-[#FFD43B] text-black 
                                        @else bg-[#FF6B6B] text-white @endif">
                                        {{ $h->status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="p-4 text-center text-slate-500">Belum ada catatan riwayat presensi.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
