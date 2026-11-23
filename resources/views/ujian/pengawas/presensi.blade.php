@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.ujian')

@section('title', 'Presensi & Kehadiran Pengawas Ujian')

@section('content')
<div class="space-y-6">
    <!-- Flash Messages -->
    @if(session('success'))
        <div class="bg-[#D3F9D8] border-2 border-black p-3.5 neo-box text-emerald-950 font-bold text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span>✅</span> <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-black font-black hover:opacity-75 cursor-pointer">✕</button>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-[#FFE3E3] border-2 border-black p-3.5 neo-box text-rose-950 font-bold text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span>⛔</span> <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-black font-black hover:opacity-75 cursor-pointer">✕</button>
        </div>
    @endif

    <!-- Header Panel Neo-Brutalism -->
    <div class="bg-[#FFF3BF] neo-box-lg p-5 sm:p-6 text-black border-3 border-black relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
        <div class="space-y-1 z-10">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="neo-badge bg-[#5294FF] text-white font-black">PANITIA UJIAN</span>
                <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border border-black shadow-[2px_2px_0px_0px_#000]">
                    PRESENSI & REKAP PENGAWAS
                </span>
                @if($activePeriode)
                    <span class="text-xs font-mono font-black bg-black text-[#FFD43B] px-2 py-0.5 rounded border border-black shadow-[2px_2px_0px_0px_#000]">
                        {{ $activePeriode->nama_periode }}
                    </span>
                @endif
            </div>
            <h1 class="font-heading text-xl sm:text-2xl font-black tracking-tight text-black uppercase">
                Presensi & Laporan Pengawas Ruang
            </h1>
            <p class="text-xs sm:text-sm font-semibold text-slate-800 max-w-2xl">
                Pantau kehadiran pengawas ruang secara real-time, verifikasi Berita Acara pelaksanaan ujian, dan cetak rekap kehadiran resmi untuk administrasi SPJ.
            </p>
        </div>

        <!-- Filter Periode & Cetak / Ekspor -->
        <div class="z-10 flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full md:w-auto">
            <form method="GET" action="{{ route('ujian.pengawas.presensi.index') }}" class="flex items-center gap-2">
                <div class="relative">
                    <select name="periode_id" onchange="this.form.submit()" 
                            class="bg-white text-black font-bold text-xs border-2 border-black rounded-lg py-2 pl-3 pr-8 shadow-[2px_2px_0px_0px_#000] focus:ring-0 cursor-pointer appearance-none">
                        @foreach($periodes as $p)
                            <option value="{{ $p->id }}" {{ $activePeriode && $activePeriode->id === $p->id ? 'selected' : '' }}>
                                {{ $p->nama_periode }} {{ $p->is_active ? '★ (Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-black font-black text-xs">
                        ▼
                    </div>
                </div>
            </form>

            @if($activePeriode)
                <a href="{{ route('ujian.pengawas.presensi.print', ['periode_id' => $activePeriode->id]) }}" 
                   target="_blank"
                   class="neo-btn bg-white hover:bg-slate-100 text-black text-xs font-black px-3.5 py-2 flex items-center justify-center gap-1.5 shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                    <span>🖨️</span> Cetak Rekap PDF
                </a>
                <a href="{{ route('ujian.pengawas.presensi.export', ['periode_id' => $activePeriode->id]) }}" 
                   class="neo-btn bg-[#20C997] hover:bg-emerald-400 text-black text-xs font-black px-3.5 py-2 flex items-center justify-center gap-1.5 shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                    <span>📥</span> Ekspor CSV
                </a>
            @endif
        </div>
    </div>

    <!-- Toggle Pengaturan Akses Presensi & Berita Acara Sebelum Hari H -->
    @if($activePeriode)
        <div class="border-3 border-black p-4 neo-box flex flex-col sm:flex-row sm:items-center justify-between gap-3 {{ $activePeriode->izinkan_pengisian_awal ? 'bg-[#D3F9D8]' : 'bg-white' }}">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 border-2 border-black flex items-center justify-center text-lg shadow-[2px_2px_0px_0px_#000] shrink-0 {{ $activePeriode->izinkan_pengisian_awal ? 'bg-[#20C997]' : 'bg-slate-100' }}">
                    {{ $activePeriode->izinkan_pengisian_awal ? '🔓' : '🔒' }}
                </div>
                <div>
                    <div class="font-heading font-black text-xs sm:text-sm text-black uppercase flex items-center gap-2">
                        <span>Akses Pengisian Sebelum Hari H Pelaksanaan Ujian</span>
                        <span class="text-[10px] font-black px-2 py-0.5 rounded border border-black {{ $activePeriode->izinkan_pengisian_awal ? 'bg-[#20C997] text-black' : 'bg-slate-200 text-slate-700' }}">
                            {{ $activePeriode->izinkan_pengisian_awal ? 'AKTIF (DIIZINKAN)' : 'NONAKTIF (TERKUNCI)' }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-700 font-semibold mt-0.5">
                        {{ $activePeriode->izinkan_pengisian_awal 
                            ? 'Pengawas ruangan diizinkan untuk melakukan check-in kehadiran dan mengisi serta mengirim Berita Acara lebih awal sebelum tanggal pelaksanaan ujian.' 
                            : 'Pengawas hanya dapat melakukan check-in dan mengisi Berita Acara tepat pada hari H pelaksanaan masing-masing sesi ujian.' }}
                    </p>
                </div>
            </div>

            <form action="{{ route('ujian.pengawas.presensi.toggle-pengisian-awal') }}" method="POST" class="shrink-0">
                @csrf
                <input type="hidden" name="periode_id" value="{{ $activePeriode->id }}">
                <button type="submit" 
                        class="w-full sm:w-auto px-4 py-2 border-2 border-black rounded text-xs font-heading font-black flex items-center justify-center gap-1.5 cursor-pointer shadow-[2px_2px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 transition-all {{ $activePeriode->izinkan_pengisian_awal ? 'bg-[#FF6B6B] text-white hover:bg-rose-600' : 'bg-[#FFD43B] text-black hover:bg-yellow-400' }}">
                    @if($activePeriode->izinkan_pengisian_awal)
                        <span>🔒</span> Nonaktifkan Pengisian Awal
                    @else
                        <span>⚡</span> Aktifkan Pengisian Sebelum Hari H
                    @endif
                </button>
            </form>
        </div>
    @endif

    <!-- 4 KPI Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white neo-box p-4 border-3 border-black flex items-center justify-between">
            <div>
                <span class="text-[10px] sm:text-[11px] font-black uppercase text-slate-500 tracking-wider">Total Penugasan Sesi</span>
                <div class="text-xl sm:text-2xl font-heading font-black text-black mt-0.5">
                    {{ $duties->count() }} <span class="text-xs font-bold text-slate-500">Ruang ({{ $totalSlots }} Slot)</span>
                </div>
            </div>
            <div class="w-10 h-10 sm:w-11 sm:h-11 bg-[#E7F5FF] border-2 border-black flex items-center justify-center text-lg sm:text-xl shadow-[2px_2px_0px_0px_#000] shrink-0">
                📋
            </div>
        </div>

        <div class="bg-white neo-box p-4 border-3 border-black flex items-center justify-between">
            <div>
                <span class="text-[10px] sm:text-[11px] font-black uppercase text-slate-500 tracking-wider">Total Pengawas Hadir</span>
                <div class="text-xl sm:text-2xl font-heading font-black text-emerald-800 mt-0.5">
                    {{ $totalHadir }} <span class="text-xs font-bold text-slate-500">Slot</span>
                </div>
            </div>
            <div class="w-10 h-10 sm:w-11 sm:h-11 bg-[#D3F9D8] border-2 border-black flex items-center justify-center text-lg sm:text-xl shadow-[2px_2px_0px_0px_#000] shrink-0">
                ✅
            </div>
        </div>

        <div class="bg-white neo-box p-4 border-3 border-black flex items-center justify-between">
            <div>
                <span class="text-[10px] sm:text-[11px] font-black uppercase text-slate-500 tracking-wider">Izin / Sakit / Alpa</span>
                <div class="text-xl sm:text-2xl font-heading font-black text-rose-800 mt-0.5">
                    {{ $totalAbsen }} <span class="text-xs font-bold text-slate-500">Slot</span>
                </div>
            </div>
            <div class="w-10 h-10 sm:w-11 sm:h-11 bg-[#FFE3E3] border-2 border-black flex items-center justify-center text-lg sm:text-xl shadow-[2px_2px_0px_0px_#000] shrink-0">
                ⚠️
            </div>
        </div>

        <div class="bg-white neo-box p-4 border-3 border-black flex items-center justify-between">
            <div>
                <span class="text-[10px] sm:text-[11px] font-black uppercase text-slate-500 tracking-wider">Berita Acara Terkirim</span>
                <div class="text-xl sm:text-2xl font-heading font-black text-black mt-0.5">
                    {{ $totalBeritaAcara }} <span class="text-xs font-bold text-slate-500">dari {{ $duties->count() }}</span>
                </div>
            </div>
            <div class="w-10 h-10 sm:w-11 sm:h-11 bg-[#FFF3BF] border-2 border-black flex items-center justify-center text-lg sm:text-xl shadow-[2px_2px_0px_0px_#000] shrink-0">
                📑
            </div>
        </div>
    </div>

    <!-- Tab Switcher Navigation -->
    <div class="flex items-center gap-2 border-b-2 border-black pb-2">
        <button type="button" id="tabBtnMonitoring" onclick="switchPresensiTab('monitoring')"
                class="px-4 py-2 text-xs font-heading font-black border-2 border-black rounded transition-all cursor-pointer bg-[#FFD43B] text-black shadow-[2px_2px_0px_0px_#000]">
            ⚡ Monitoring Sesi & Berita Acara
        </button>
        <button type="button" id="tabBtnRekap" onclick="switchPresensiTab('rekap')"
                class="px-4 py-2 text-xs font-heading font-black border-2 border-black rounded transition-all cursor-pointer bg-white text-slate-700 hover:bg-slate-100 shadow-[2px_2px_0px_0px_#000]">
            📊 Rekapitulasi Presensi per Guru (SPJ)
        </button>
    </div>

    <!-- TAB 1: MONITORING SESI RUANG & BERITA ACARA -->
    <div id="tabContentMonitoring" class="space-y-4">
        <!-- Filter Toolbar (Tanggal & Ruang) -->
        <div class="bg-white p-3.5 border-2 border-black neo-box flex flex-wrap items-center justify-between gap-3">
            <form method="GET" action="{{ route('ujian.pengawas.presensi.index') }}" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="periode_id" value="{{ $activePeriode?->id }}">

                <div class="flex items-center gap-1.5 text-xs font-bold">
                    <span>📅 Filter Tanggal:</span>
                    <select name="tanggal" onchange="this.form.submit()" 
                            class="bg-white border-2 border-black rounded px-2.5 py-1 text-xs font-bold focus:ring-0">
                        <option value="">Semua Tanggal</option>
                        @foreach($dates as $d)
                            <option value="{{ $d->format('Y-m-d') }}" {{ request('tanggal') === $d->format('Y-m-d') ? 'selected' : '' }}>
                                {{ $d->locale('id')->translatedFormat('l, d/m/Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-1.5 text-xs font-bold ml-2">
                    <span>🏫 Ruang:</span>
                    <select name="ruang_id" onchange="this.form.submit()" 
                            class="bg-white border-2 border-black rounded px-2.5 py-1 text-xs font-bold focus:ring-0">
                        <option value="">Semua Ruangan</option>
                        @foreach($ruangs as $r)
                            <option value="{{ $r->id }}" {{ request('ruang_id') == $r->id ? 'selected' : '' }}>
                                {{ $r->nama_ruang }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if(request('tanggal') || request('ruang_id'))
                    <a href="{{ route('ujian.pengawas.presensi.index', ['periode_id' => $activePeriode?->id]) }}" 
                       class="text-xs text-rose-600 font-bold hover:underline ml-1">
                        ✕ Reset Filter
                    </a>
                @endif
            </form>
        </div>

        <!-- Monitoring Table -->
        <div class="bg-white neo-box border-3 border-black overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-black text-[#FFD43B] font-heading font-black uppercase text-[11px] border-b-2 border-black">
                        <th class="p-3 text-center w-10 border-r border-slate-700">No</th>
                        <th class="p-3 border-r border-slate-700">Waktu & Mapel</th>
                        <th class="p-3 border-r border-slate-700">Ruang & Peserta</th>
                        <th class="p-3 border-r border-slate-700">Pengawas 1</th>
                        <th class="p-3 border-r border-slate-700">Pengawas 2</th>
                        <th class="p-3 border-r border-slate-700 text-center">Berita Acara</th>
                        <th class="p-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black">
                    @forelse($duties as $idx => $duty)
                        @php
                            $jadwal = $duty->jadwal;
                            $ruang = $duty->ruang;
                            $ba = $duty->beritaAcara;
                        @endphp
                        <tr class="hover:bg-yellow-50/60 transition-colors">
                            <td class="p-3 text-center font-bold font-mono border-r border-slate-300">
                                {{ $idx + 1 }}
                            </td>
                            <td class="p-3 border-r border-slate-300">
                                <div class="font-bold text-black">
                                    {{ $jadwal?->tanggal_ujian?->locale('id')->translatedFormat('l, d/m/Y') }}
                                </div>
                                <div class="text-[10px] font-mono text-slate-600">
                                    Sesi {{ $jadwal?->sesi_ke }} ({{ substr($jadwal?->jam_mulai ?? '', 0, 5) }} - {{ substr($jadwal?->jam_selesai ?? '', 0, 5) }})
                                </div>
                                <div class="font-heading font-black text-xs text-indigo-950 mt-1">
                                    {{ $jadwal?->nama_mapel ?? '-' }}
                                </div>
                            </td>
                            <td class="p-3 border-r border-slate-300">
                                <div class="font-heading font-black text-black">{{ $ruang?->nama_ruang }}</div>
                                <div class="text-[10px] font-semibold text-slate-600">Kelas: {{ $jadwal?->target_kelas ?? '-' }}</div>
                            </td>
                            <!-- Pengawas 1 -->
                            <td class="p-3 border-r border-slate-300">
                                <div class="font-bold text-black text-[11px] truncate max-w-[170px]" title="{{ $duty->pengawas1?->user?->name }}">
                                    {{ $duty->pengawas1?->user?->name ?? 'Belum Ditentukan' }}
                                </div>
                                <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                                    @php
                                        $s1 = $duty->status_p1 ?: 'Terjadwal';
                                        $bClass1 = match($s1) {
                                            'Hadir' => 'bg-[#D3F9D8] text-emerald-950 border-emerald-500',
                                            'Izin', 'Sakit' => 'bg-[#FFF3BF] text-amber-950 border-amber-500',
                                            'Alpa' => 'bg-[#FFE3E3] text-rose-950 border-rose-500',
                                            'Digantikan' => 'bg-[#E7F5FF] text-blue-950 border-blue-500',
                                            default => 'bg-slate-100 text-slate-700 border-slate-300',
                                        };
                                    @endphp
                                    <span class="text-[10px] font-black px-1.5 py-0.5 rounded border {{ $bClass1 }}">
                                        {{ $s1 }}
                                    </span>
                                    @if($duty->check_in_p1_at)
                                        <span class="text-[9.5px] font-mono text-slate-500">
                                            ({{ $duty->check_in_p1_at->format('H:i') }})
                                        </span>
                                    @endif
                                </div>
                                @if($duty->pengawas_pengganti_1)
                                    <div class="text-[9.5px] text-blue-800 font-semibold mt-0.5">
                                        Pengganti: {{ $duty->pengawas_pengganti_1 }}
                                    </div>
                                @endif
                            </td>
                            <!-- Pengawas 2 -->
                            <td class="p-3 border-r border-slate-300">
                                @if($duty->pengawas2)
                                    <div class="font-bold text-black text-[11px] truncate max-w-[170px]" title="{{ $duty->pengawas2?->user?->name }}">
                                        {{ $duty->pengawas2?->user?->name }}
                                    </div>
                                    <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                                        @php
                                            $s2 = $duty->status_p2 ?: 'Terjadwal';
                                            $bClass2 = match($s2) {
                                                'Hadir' => 'bg-[#D3F9D8] text-emerald-950 border-emerald-500',
                                                'Izin', 'Sakit' => 'bg-[#FFF3BF] text-amber-950 border-amber-500',
                                                'Alpa' => 'bg-[#FFE3E3] text-rose-950 border-rose-500',
                                                'Digantikan' => 'bg-[#E7F5FF] text-blue-950 border-blue-500',
                                                default => 'bg-slate-100 text-slate-700 border-slate-300',
                                            };
                                        @endphp
                                        <span class="text-[10px] font-black px-1.5 py-0.5 rounded border {{ $bClass2 }}">
                                            {{ $s2 }}
                                        </span>
                                        @if($duty->check_in_p2_at)
                                            <span class="text-[9.5px] font-mono text-slate-500">
                                                ({{ $duty->check_in_p2_at->format('H:i') }})
                                            </span>
                                        @endif
                                    </div>
                                    @if($duty->pengawas_pengganti_2)
                                        <div class="text-[9.5px] text-blue-800 font-semibold mt-0.5">
                                            Pengganti: {{ $duty->pengawas_pengganti_2 }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-[10px] text-slate-400 italic">Pengawas Tunggal</span>
                                @endif
                            </td>
                            <!-- Berita Acara -->
                            <td class="p-3 text-center border-r border-slate-300">
                                @if($ba)
                                    <div class="space-y-1">
                                        <div>
                                            @if($ba->isVerified())
                                                <span class="text-[10px] font-black px-2 py-0.5 rounded bg-[#5294FF] text-white border border-black shadow-[1px_1px_0px_0px_#000]">
                                                    ✓ Verified
                                                </span>
                                            @else
                                                <span class="text-[10px] font-black px-2 py-0.5 rounded bg-[#20C997] text-black border border-black shadow-[1px_1px_0px_0px_#000]">
                                                    ⏳ Terkirim
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-[10px] font-mono font-bold text-slate-700">
                                            H: {{ $ba->peserta_hadir }} / A: {{ $ba->peserta_tidak_hadir }}
                                        </div>
                                        <div class="flex items-center justify-center gap-1">
                                            <a href="{{ route('ujian.administrasi.berita-acara', ['jadwal_id' => $jadwal->id, 'ruang_id' => $ruang->id]) }}" 
                                               target="_blank"
                                               class="text-[10px] font-bold text-blue-700 hover:underline">
                                                Pratinjau
                                            </a>
                                            @if(!$ba->isVerified())
                                                <span>•</span>
                                                <form action="{{ route('ujian.administrasi.berita-acara.verifikasi', $ba->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            onclick="return confirm('Verifikasi dan kunci Berita Acara ini?')"
                                                            class="text-[10px] font-black text-emerald-700 hover:underline cursor-pointer">
                                                        Verifikasi
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="text-[10px] font-semibold text-slate-400 italic">Belum Ada</span>
                                @endif
                            </td>
                            <!-- Aksi Quick Update Status -->
                            <td class="p-3 text-center">
                                <button type="button" 
                                        onclick="openModalPresensi({{ json_encode([
                                            'id' => $duty->id,
                                            'ruang' => $ruang?->nama_ruang,
                                            'jadwal' => $jadwal?->nama_mapel . ' (' . ($jadwal?->tanggal_ujian?->format('d/m') ?? '') . ')',
                                            'p1_name' => $duty->pengawas1?->user?->name ?? 'Pengawas 1',
                                            'p1_status' => $duty->status_p1 ?: 'Terjadwal',
                                            'p1_pengganti' => $duty->pengawas_pengganti_1 ?? '',
                                            'has_p2' => (bool)$duty->pengawas2,
                                            'p2_name' => $duty->pengawas2?->user?->name ?? 'Pengawas 2',
                                            'p2_status' => $duty->status_p2 ?: 'Terjadwal',
                                            'p2_pengganti' => $duty->pengawas_pengganti_2 ?? '',
                                            'catatan' => $duty->catatan ?? '',
                                        ]) }})"
                                        class="px-2.5 py-1.5 bg-[#FFF3BF] hover:bg-yellow-300 text-black border border-black rounded text-[11px] font-black shadow-[1px_1px_0px_0px_#000] cursor-pointer">
                                    ✏️ Ubah Status
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-500 font-semibold">
                                Belum ada data jadwal pengawas pada filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 2: REKAPITULASI KEHADIRAN PER GURU (SPJ) -->
    <div id="tabContentRekap" class="hidden space-y-4">
        <div class="bg-white neo-box border-3 border-black overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-black text-[#FFD43B] font-heading font-black uppercase text-[11px] border-b-2 border-black">
                        <th class="p-3 text-center w-12 border-r border-slate-700">No</th>
                        <th class="p-3 border-r border-slate-700">Nama Guru / Pengawas</th>
                        <th class="p-3 border-r border-slate-700 w-36">NIP / ID</th>
                        <th class="p-3 text-center border-r border-slate-700 w-24">Total Sesi</th>
                        <th class="p-3 text-center border-r border-slate-700 w-20">Hadir</th>
                        <th class="p-3 text-center border-r border-slate-700 w-20">Izin</th>
                        <th class="p-3 text-center border-r border-slate-700 w-20">Sakit</th>
                        <th class="p-3 text-center border-r border-slate-700 w-20">Alpa</th>
                        <th class="p-3 text-center border-r border-slate-700 w-24">Digantikan</th>
                        <th class="p-3 text-center w-28">% Kehadiran</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black">
                    @forelse($rekapTeachers as $idx => $r)
                        <tr class="hover:bg-yellow-50/60 transition-colors">
                            <td class="p-3 text-center font-bold font-mono border-r border-slate-300">
                                {{ $loop->iteration }}
                            </td>
                            <td class="p-3 border-r border-slate-300">
                                <div class="font-heading font-black text-black text-xs">
                                    {{ $r['teacher']->user?->name ?? '-' }}
                                </div>
                            </td>
                            <td class="p-3 font-mono border-r border-slate-300 text-slate-600">
                                {{ $r['teacher']->nip ?? '-' }}
                            </td>
                            <td class="p-3 text-center font-mono font-black border-r border-slate-300 bg-slate-50">
                                {{ $r['total_sesi'] }}
                            </td>
                            <td class="p-3 text-center font-mono font-black text-emerald-800 border-r border-slate-300 bg-emerald-50/40">
                                {{ $r['hadir'] }}
                            </td>
                            <td class="p-3 text-center font-mono font-bold text-amber-800 border-r border-slate-300">
                                {{ $r['izin'] }}
                            </td>
                            <td class="p-3 text-center font-mono font-bold text-amber-800 border-r border-slate-300">
                                {{ $r['sakit'] }}
                            </td>
                            <td class="p-3 text-center font-mono font-bold text-rose-800 border-r border-slate-300">
                                {{ $r['alpa'] }}
                            </td>
                            <td class="p-3 text-center font-mono font-bold text-blue-800 border-r border-slate-300">
                                {{ $r['digantikan'] }}
                            </td>
                            <td class="p-3 text-center font-mono font-black">
                                <span class="px-2 py-0.5 rounded border border-black {{ $r['persen_hadir'] >= 80 ? 'bg-[#D3F9D8] text-emerald-950' : 'bg-[#FFE3E3] text-rose-950' }}">
                                    {{ $r['persen_hadir'] }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="p-8 text-center text-slate-500 font-semibold">
                                Belum ada rekapitulasi data guru pengawas pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Quick Update Status Presensi Pengawas -->
<div id="modalPresensi" class="fixed inset-0 z-50 hidden bg-black/60 flex items-center justify-center p-4">
    <div class="bg-white neo-box-lg border-3 border-black w-full max-w-lg p-5 sm:p-6 space-y-4">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <div>
                <h3 class="font-heading font-black text-base text-black uppercase">Ubah Status Presensi Pengawas</h3>
                <p id="modalSubTitle" class="text-xs text-slate-600 font-semibold"></p>
            </div>
            <button type="button" onclick="closeModalPresensi()" class="text-black font-black text-lg hover:opacity-75 cursor-pointer">✕</button>
        </div>

        <form id="formModalPresensi" method="POST" action="" class="space-y-4">
            @csrf

            <!-- Pengawas 1 -->
            <div class="p-3 bg-slate-50 border-2 border-black rounded space-y-2">
                <div class="font-heading font-black text-xs text-black flex items-center justify-between">
                    <span>Pengawas 1: <span id="labelP1Name"></span></span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-600 mb-1">Status Kehadiran</label>
                        <select id="selectP1Status" name="status_p1" 
                                class="w-full bg-white text-xs font-bold border border-black rounded p-2 focus:ring-0">
                            <option value="Terjadwal">Terjadwal</option>
                            <option value="Hadir">Hadir</option>
                            <option value="Izin">Izin</option>
                            <option value="Sakit">Sakit</option>
                            <option value="Alpa">Alpa</option>
                            <option value="Digantikan">Digantikan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-600 mb-1">Pengawas Pengganti (Opsional)</label>
                        <input type="text" id="inputP1Pengganti" name="pengawas_pengganti_1" placeholder="Nama Guru Pengganti"
                               class="w-full bg-white text-xs border border-black rounded p-2 focus:ring-0">
                    </div>
                </div>
            </div>

            <!-- Pengawas 2 -->
            <div id="containerP2" class="p-3 bg-slate-50 border-2 border-black rounded space-y-2">
                <div class="font-heading font-black text-xs text-black flex items-center justify-between">
                    <span>Pengawas 2: <span id="labelP2Name"></span></span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-600 mb-1">Status Kehadiran</label>
                        <select id="selectP2Status" name="status_p2" 
                                class="w-full bg-white text-xs font-bold border border-black rounded p-2 focus:ring-0">
                            <option value="Terjadwal">Terjadwal</option>
                            <option value="Hadir">Hadir</option>
                            <option value="Izin">Izin</option>
                            <option value="Sakit">Sakit</option>
                            <option value="Alpa">Alpa</option>
                            <option value="Digantikan">Digantikan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-600 mb-1">Pengawas Pengganti (Opsional)</label>
                        <input type="text" id="inputP2Pengganti" name="pengawas_pengganti_2" placeholder="Nama Guru Pengganti"
                               class="w-full bg-white text-xs border border-black rounded p-2 focus:ring-0">
                    </div>
                </div>
            </div>

            <!-- Catatan Panitia -->
            <div>
                <label class="block text-xs font-black uppercase text-slate-700 mb-1">Catatan Panitia (Opsional)</label>
                <textarea id="inputCatatan" name="catatan" rows="2" placeholder="Keterangan tambahan kendala / perubahan..."
                          class="w-full bg-white text-xs border-2 border-black rounded p-2 focus:ring-0"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-300">
                <button type="button" onclick="closeModalPresensi()" 
                        class="px-4 py-2 bg-white hover:bg-slate-100 text-black border-2 border-black rounded font-heading font-black text-xs uppercase cursor-pointer">
                    Batal
                </button>
                <button type="submit" 
                        class="px-5 py-2 bg-[#FFD43B] hover:bg-yellow-400 text-black border-2 border-black rounded font-heading font-black text-xs uppercase cursor-pointer shadow-[2px_2px_0px_0px_#000]">
                    💾 Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function switchPresensiTab(tab) {
        const btnMonitoring = document.getElementById('tabBtnMonitoring');
        const btnRekap = document.getElementById('tabBtnRekap');
        const contentMonitoring = document.getElementById('tabContentMonitoring');
        const contentRekap = document.getElementById('tabContentRekap');

        if (tab === 'monitoring') {
            btnMonitoring.className = 'px-4 py-2 text-xs font-heading font-black border-2 border-black rounded transition-all cursor-pointer bg-[#FFD43B] text-black shadow-[2px_2px_0px_0px_#000]';
            btnRekap.className = 'px-4 py-2 text-xs font-heading font-black border-2 border-black rounded transition-all cursor-pointer bg-white text-slate-700 hover:bg-slate-100 shadow-[2px_2px_0px_0px_#000]';
            contentMonitoring.classList.remove('hidden');
            contentRekap.classList.add('hidden');
        } else {
            btnRekap.className = 'px-4 py-2 text-xs font-heading font-black border-2 border-black rounded transition-all cursor-pointer bg-[#FFD43B] text-black shadow-[2px_2px_0px_0px_#000]';
            btnMonitoring.className = 'px-4 py-2 text-xs font-heading font-black border-2 border-black rounded transition-all cursor-pointer bg-white text-slate-700 hover:bg-slate-100 shadow-[2px_2px_0px_0px_#000]';
            contentRekap.classList.remove('hidden');
            contentMonitoring.classList.add('hidden');
        }
    }

    function openModalPresensi(data) {
        document.getElementById('modalSubTitle').textContent = data.ruang + ' • ' + data.jadwal;
        document.getElementById('formModalPresensi').action = '/ujian/jadwal-pengawas/presensi/' + data.id;

        document.getElementById('labelP1Name').textContent = data.p1_name;
        document.getElementById('selectP1Status').value = data.p1_status;
        document.getElementById('inputP1Pengganti').value = data.p1_pengganti;

        const containerP2 = document.getElementById('containerP2');
        if (data.has_p2) {
            containerP2.classList.remove('hidden');
            document.getElementById('labelP2Name').textContent = data.p2_name;
            document.getElementById('selectP2Status').value = data.p2_status;
            document.getElementById('inputP2Pengganti').value = data.p2_pengganti;
        } else {
            containerP2.classList.add('hidden');
        }

        document.getElementById('inputCatatan').value = data.catatan;
        document.getElementById('modalPresensi').classList.remove('hidden');
    }

    function closeModalPresensi() {
        document.getElementById('modalPresensi').classList.add('hidden');
    }
</script>
@endsection
