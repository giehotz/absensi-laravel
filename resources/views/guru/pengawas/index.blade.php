@extends('layouts.guru')

@section('title', 'Jadwal Pengawas Ujian')
@section('page-title', 'Jadwal Pengawas Ujian')

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

    @if(session('warning'))
        <div class="bg-[#FFF3BF] border-2 border-black p-3.5 neo-box text-amber-950 font-bold text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span>⚠️</span> <span>{{ session('warning') }}</span>
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

    @if(!empty($orphanUser))
        <!-- State: User Guru Belum Terhubung ke Profil Tenaga Pengajar -->
        <div class="bg-white neo-box-lg p-6 sm:p-8 border-3 border-black text-center space-y-3">
            <div class="w-16 h-16 bg-[#FFF3BF] border-2 border-black mx-auto flex items-center justify-center text-3xl shadow-[3px_3px_0px_0px_#000]">
                ⚠️
            </div>
            <h2 class="font-heading font-black text-xl text-black">Data Guru Belum Terhubung</h2>
            <p class="text-xs sm:text-sm text-slate-700 max-w-lg mx-auto">
                Akun Anda belum ditautkan ke data guru pada sistem. Silakan hubungi administrator madrasah untuk melakukan sinkronisasi profil tenaga pengajar Anda.
            </p>
        </div>
    @else
        <!-- Header Card Neo-Brutalism -->
        <div class="bg-[#FFF3BF] neo-box-lg p-5 sm:p-7 text-black relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
            <div class="space-y-1.5 z-10">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="neo-badge bg-[#5294FF] text-white font-black">PORTAL GURU</span>
                    <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                        PENGAWAS UJIAN
                    </span>
                    @if($activePeriode)
                        <span class="text-xs font-mono font-black bg-black text-[#FFD43B] px-2 py-0.5 rounded border border-black shadow-[2px_2px_0px_0px_#000]">
                            {{ $activePeriode->tahun_pelajaran ?? '-' }}
                        </span>
                    @endif
                </div>
                <h1 class="font-heading text-xl sm:text-2xl font-black tracking-tight text-black uppercase">
                    Jadwal Pengawas Ujian
                </h1>
                <p class="text-xs sm:text-sm font-semibold text-slate-800 max-w-2xl">
                    Periksa alokasi ruangan, kelas yang diawasi, sesi waktu, dan rekan pengawas tugas Anda pada asesmen madrasah.
                </p>
            </div>

            <!-- Form Filter Periode & Aksi Cetak -->
            <div class="z-10 flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full md:w-auto">
                <form method="GET" action="{{ route('guru.pengawas.index') }}" class="flex items-center gap-2">
                    <div class="relative">
                        <select name="periode_id" onchange="this.form.submit()" 
                                class="bg-white text-black font-bold text-xs border-2 border-black rounded-lg py-2 pl-3 pr-8 shadow-[2px_2px_0px_0px_#000] focus:ring-0 focus:border-black cursor-pointer appearance-none">
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

                @if($activePeriode && $totalDuties > 0)
                    <a href="{{ route('guru.pengawas.print', ['periode_id' => $activePeriode->id]) }}" 
                       target="_blank"
                       class="neo-btn bg-white hover:bg-slate-100 text-black text-xs font-black px-4 py-2 flex items-center justify-center gap-1.5 cursor-pointer shadow-[3px_3px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 transition-all">
                        <span>🖨️</span> Cetak Surat Tugas
                    </a>
                @endif
            </div>
        </div>

        <!-- 4 KPI Summary Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="bg-white neo-box p-4 border-3 border-black flex items-center justify-between">
                <div>
                    <span class="text-[10px] sm:text-[11px] font-black uppercase text-slate-500 tracking-wider">Total Penugasan</span>
                    <div class="text-xl sm:text-2xl font-heading font-black text-black mt-0.5">
                        {{ $totalDuties }} <span class="text-xs font-bold text-slate-500">Sesi</span>
                    </div>
                </div>
                <div class="w-10 h-10 sm:w-11 sm:h-11 bg-[#E7F5FF] border-2 border-black flex items-center justify-center text-lg sm:text-xl shadow-[2px_2px_0px_0px_#000] shrink-0">
                    📋
                </div>
            </div>

            <div class="bg-white neo-box p-4 border-3 border-black flex items-center justify-between">
                <div>
                    <span class="text-[10px] sm:text-[11px] font-black uppercase text-slate-500 tracking-wider">Mengawas Hari Ini</span>
                    <div class="text-xl sm:text-2xl font-heading font-black text-black mt-0.5">
                        {{ $todayDutiesCount }} <span class="text-xs font-bold text-slate-500">Sesi</span>
                    </div>
                </div>
                <div class="w-10 h-10 sm:w-11 sm:h-11 {{ $todayDutiesCount > 0 ? 'bg-[#FFD43B] animate-pulse' : 'bg-slate-100' }} border-2 border-black flex items-center justify-center text-lg sm:text-xl shadow-[2px_2px_0px_0px_#000] shrink-0">
                    ⚡
                </div>
            </div>

            <div class="bg-white neo-box p-4 border-3 border-black flex items-center justify-between">
                <div>
                    <span class="text-[10px] sm:text-[11px] font-black uppercase text-slate-500 tracking-wider">Total Alokasi Waktu</span>
                    <div class="text-xl sm:text-2xl font-heading font-black text-black mt-0.5">
                        {{ $totalMinutes }} <span class="text-xs font-bold text-slate-500">Mnt ({{ round($totalMinutes / 60, 1) }} Jam)</span>
                    </div>
                </div>
                <div class="w-10 h-10 sm:w-11 sm:h-11 bg-[#FFF3BF] border-2 border-black flex items-center justify-center text-lg sm:text-xl shadow-[2px_2px_0px_0px_#000] shrink-0">
                    ⏱️
                </div>
            </div>

            <div class="bg-white neo-box p-4 border-3 border-black flex items-center justify-between">
                <div>
                    <span class="text-[10px] sm:text-[11px] font-black uppercase text-slate-500 tracking-wider">Selesai Dijalani</span>
                    <div class="text-xl sm:text-2xl font-heading font-black text-black mt-0.5">
                        {{ $completedDutiesCount }} <span class="text-xs font-bold text-slate-500">Sesi</span>
                    </div>
                </div>
                <div class="w-10 h-10 sm:w-11 sm:h-11 bg-[#D3F9D8] border-2 border-black flex items-center justify-center text-lg sm:text-xl shadow-[2px_2px_0px_0px_#000] shrink-0">
                    ✅
                </div>
            </div>
        </div>

        @if($duties->isEmpty())
            <!-- Empty State: Belum Ada Jadwal Mengawas -->
            <div class="bg-white neo-box-lg p-8 sm:p-12 border-3 border-black text-center space-y-3">
                <div class="w-16 h-16 bg-slate-100 border-2 border-black mx-auto flex items-center justify-center text-3xl shadow-[3px_3px_0px_0px_#000]">
                    ☕
                </div>
                <h3 class="font-heading font-black text-lg sm:text-xl text-black">Belum Ada Jadwal Mengawas</h3>
                <p class="text-xs sm:text-sm text-slate-600 max-w-md mx-auto">
                    Anda belum memiliki alokasi jadwal mengawas pada periode <strong>{{ $activePeriode->nama_periode ?? 'ini' }}</strong>. 
                    Jadwal akan muncul otomatis setelah panitia ujian menetapkan pembagian pengawas ruang.
                </p>
            </div>
        @else
            <!-- Toolbar Switcher: Mode Kartu Timeline vs Mode Tabel -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-white p-3 border-2 border-black shadow-[2px_2px_0px_0px_#000] rounded-lg">
                <div class="text-xs font-black uppercase text-slate-700 flex items-center gap-2">
                    <span>🗓️ DAFTAR TUGAS MENGAWAS</span>
                    <span class="bg-black text-[#FFD43B] text-[10px] px-2 py-0.5 rounded font-mono">{{ $totalDuties }} Sesi</span>
                </div>
                
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button type="button" id="btnModeCard" onclick="switchView('card')" 
                            class="flex-1 sm:flex-none px-3 py-1.5 text-xs font-heading font-black border-2 border-black rounded transition-all cursor-pointer bg-[#FFD43B] text-black shadow-[2px_2px_0px_0px_#000]">
                        🗂️ Mode Kartu
                    </button>
                    <button type="button" id="btnModeTable" onclick="switchView('table')" 
                            class="flex-1 sm:flex-none px-3 py-1.5 text-xs font-heading font-black border-2 border-black rounded transition-all cursor-pointer bg-white text-slate-700 hover:bg-slate-100 shadow-[2px_2px_0px_0px_#000]">
                        📊 Mode Tabel
                    </button>
                </div>
            </div>

            <!-- VIEW 1: KARTU TIMELINE INTERAKTIF -->
            <div id="viewModeCard" class="space-y-6">
                @foreach($groupedDuties as $dateKey => $dayDuties)
                    @php
                        $firstDuty = $dayDuties->first();
                        $tglCarbon = $firstDuty->jadwal?->tanggal_ujian;
                        $isToday = $tglCarbon && $tglCarbon->isToday();
                    @endphp
                    <div class="space-y-3">
                        <!-- Date Header Strip -->
                        <div class="flex items-center gap-2.5">
                            <div class="px-3 py-1 border-2 border-black font-heading font-black text-xs uppercase tracking-wider {{ $isToday ? 'bg-[#FF6B6B] text-white' : 'bg-black text-white' }} shadow-[2px_2px_0px_0px_#000] rounded">
                                {{ $tglCarbon ? $tglCarbon->locale('id')->translatedFormat('l, d F Y') : 'Tanggal Belum Ditetapkan' }}
                                @if($isToday)
                                    <span class="ml-1 text-yellow-300 font-mono">⚡ (HARI INI)</span>
                                @endif
                            </div>
                            <div class="h-0.5 flex-1 bg-black"></div>
                            <span class="text-[11px] font-bold text-slate-500 font-mono">{{ $dayDuties->count() }} Sesi</span>
                        </div>

                        <!-- Card Grid per Tanggal -->
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                            @foreach($dayDuties as $duty)
                                @php
                                    $jadwal = $duty->jadwal;
                                    $ruang = $duty->ruang;
                                    $targetKelas = $jadwal?->target_kelas ?? '-';
                                    $namaRuang = $ruang?->nama_ruang ?? 'Ruang Belum Ditentukan';
                                @endphp
                                <article class="card bg-white neo-box p-4 border-3 border-black flex flex-col justify-between gap-3.5 relative hover:translate-x-0.5 hover:translate-y-0.5 transition-all">
                                    <!-- Baris Atas: Sesi, Waktu & Badge Status Real-Time -->
                                    <div class="flex items-center justify-between gap-2 border-b-2 border-black pb-2.5">
                                        <div class="flex items-center gap-1.5">
                                            <span class="bg-black text-[#FFD43B] font-heading font-black text-[11px] px-2 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000]">
                                                Sesi {{ $jadwal?->sesi_ke ?? '1' }}
                                            </span>
                                            <span class="font-mono font-bold text-xs text-slate-800">
                                                {{ substr($jadwal?->jam_mulai ?? '', 0, 5) }} - {{ substr($jadwal?->jam_selesai ?? '', 0, 5) }} WIB
                                            </span>
                                        </div>
                                        <span class="text-[10px] font-black px-2 py-0.5 rounded border shadow-[1px_1px_0px_0px_#000] uppercase tracking-wide {{ $duty->status_badge }}">
                                            {{ $duty->status_label }}
                                        </span>
                                    </div>

                                    <!-- Informasi Ruang & Kelas yang Diawasi (Prominent Box) -->
                                    <div class="bg-[#FFF3BF] border-2 border-black p-3 rounded-lg shadow-[2px_2px_0px_0px_#000] space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[10px] font-black uppercase text-amber-950 tracking-wider">LOKASI PENGAWASAN</span>
                                            <span class="text-[10px] font-mono font-bold bg-white px-1.5 py-0.2 rounded border border-black">
                                                {{ $ruang?->kode_ruang ?? '-' }}
                                            </span>
                                        </div>
                                        <div class="font-heading font-black text-base text-black flex items-center gap-1.5">
                                            <span>🏫</span>
                                            <span class="truncate" title="{{ $namaRuang }}">{{ $namaRuang }}</span>
                                        </div>
                                        <div class="text-[11px] font-semibold text-slate-800 flex items-center gap-1">
                                            <span>Peserta:</span>
                                            <span class="font-bold underline">{{ $targetKelas }}</span>
                                            @if($ruang && $ruang->kapasitas_maksimal)
                                                <span class="text-slate-500 font-mono">(Kapasitas: {{ $ruang->kapasitas_maksimal }})</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Mata Pelajaran & Spesifikasi Ujian -->
                                    <div class="space-y-1 text-xs">
                                        <div class="text-[10px] font-bold uppercase text-slate-500">Mata Pelajaran</div>
                                        <div class="font-heading font-black text-sm text-black">
                                            {{ $jadwal?->nama_mapel ?? '-' }}
                                        </div>
                                        <div class="flex items-center gap-2 text-[11px] font-semibold text-slate-600">
                                            <span>⏳ {{ $jadwal?->durasi_menit ?? '120' }} Menit</span>
                                            <span>•</span>
                                            <span>📝 {{ $jadwal?->jenis_soal ?? 'Tertulis' }}</span>
                                        </div>
                                    </div>

                                    <!-- Rekan Pengawas & Tombol WhatsApp -->
                                    <div class="pt-2 border-t-2 border-slate-200">
                                        <div class="text-[10px] font-bold uppercase text-slate-500 mb-1">Rekan Pengawas</div>
                                        @if($duty->partner)
                                            <div class="flex items-center justify-between gap-2 bg-slate-50 border border-slate-300 p-2 rounded">
                                                <div class="min-w-0">
                                                    <div class="font-bold text-xs text-black truncate" title="{{ $duty->partner_name }}">
                                                        {{ $duty->partner_name }}
                                                    </div>
                                                    <div class="text-[10px] font-mono text-slate-500">
                                                        {{ $duty->is_pengawas_1 ? 'Pengawas 2' : 'Pengawas 1' }}
                                                    </div>
                                                </div>
                                                @if($duty->partner_wa_url)
                                                    <a href="{{ $duty->partner_wa_url }}" 
                                                       target="_blank"
                                                       class="px-2 py-1 bg-[#20C997] hover:bg-emerald-500 text-black border border-black rounded text-[11px] font-black flex items-center gap-1 shrink-0 shadow-[1px_1px_0px_0px_#000]">
                                                        <span>💬</span> WA
                                                    </a>
                                                @endif
                                            </div>
                                        @else
                                            <div class="inline-block bg-[#E7F5FF] text-blue-950 font-bold text-[11px] px-2 py-1 rounded border border-blue-400">
                                                👤 Pengawas Tunggal (1 Orang)
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Tombol Aksi Modal Peserta Ruangan -->
                                    <div class="pt-1">
                                        @if($ruang)
                                            <button type="button" 
                                                    onclick="loadPesertaRuang({{ $ruang->id }}, '{{ addslashes($namaRuang) }}', {{ $activePeriode->id }})"
                                                    class="w-full py-2 px-3 bg-white hover:bg-yellow-50 text-black border-2 border-black rounded font-heading font-black text-xs uppercase tracking-wider flex items-center justify-center gap-1.5 shadow-[2px_2px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 transition-all cursor-pointer">
                                                <span>👥</span> Lihat Siswa di Ruangan
                                            </button>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- VIEW 2: TABEL RINGKAS RESPOCIVE -->
            <div id="viewModeTable" class="hidden">
                <div class="bg-white neo-box border-3 border-black overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-black text-[#FFD43B] font-heading font-black uppercase text-[11px] border-b-2 border-black">
                                <th class="p-3 text-center w-12 border-r border-slate-700">No</th>
                                <th class="p-3 border-r border-slate-700">Hari, Tanggal</th>
                                <th class="p-3 text-center border-r border-slate-700">Sesi</th>
                                <th class="p-3 text-center border-r border-slate-700">Waktu</th>
                                <th class="p-3 border-r border-slate-700">Ruang & Kelas</th>
                                <th class="p-3 border-r border-slate-700">Mata Pelajaran</th>
                                <th class="p-3 border-r border-slate-700">Rekan Pengawas</th>
                                <th class="p-3 text-center border-r border-slate-700">Status</th>
                                <th class="p-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-black">
                            @foreach($duties as $idx => $d)
                                @php
                                    $jadwal = $d->jadwal;
                                    $ruang = $d->ruang;
                                    $namaRuang = $ruang?->nama_ruang ?? '-';
                                @endphp
                                <tr class="hover:bg-yellow-50 transition-colors">
                                    <td class="p-3 text-center font-bold font-mono border-r border-slate-300">
                                        {{ $idx + 1 }}
                                    </td>
                                    <td class="p-3 font-bold border-r border-slate-300 whitespace-nowrap">
                                        {{ $jadwal?->tanggal_ujian ? $jadwal->tanggal_ujian->locale('id')->translatedFormat('l, d/m/Y') : '-' }}
                                    </td>
                                    <td class="p-3 text-center font-bold border-r border-slate-300">
                                        Sesi {{ $jadwal?->sesi_ke ?? '1' }}
                                    </td>
                                    <td class="p-3 text-center font-mono font-bold border-r border-slate-300 whitespace-nowrap">
                                        {{ substr($jadwal?->jam_mulai ?? '', 0, 5) }} - {{ substr($jadwal?->jam_selesai ?? '', 0, 5) }}
                                    </td>
                                    <td class="p-3 border-r border-slate-300">
                                        <div class="font-heading font-black text-black">{{ $namaRuang }}</div>
                                        <div class="text-[11px] text-slate-600 font-semibold">Peserta: {{ $jadwal?->target_kelas ?? '-' }}</div>
                                    </td>
                                    <td class="p-3 border-r border-slate-300">
                                        <div class="font-bold text-black">{{ $jadwal?->nama_mapel ?? '-' }}</div>
                                        <div class="text-[10px] text-slate-500 font-mono">{{ $jadwal?->durasi_menit }} mnt • {{ $jadwal?->jenis_soal }}</div>
                                    </td>
                                    <td class="p-3 border-r border-slate-300">
                                        @if($d->partner)
                                            <div class="font-bold text-black">{{ $d->partner_name }}</div>
                                            @if($d->partner_wa_url)
                                                <a href="{{ $d->partner_wa_url }}" target="_blank" class="text-[10px] font-bold text-emerald-700 hover:underline flex items-center gap-1 mt-0.5">
                                                    <span>💬 Hubungi WA</span>
                                                </a>
                                            @endif
                                        @else
                                            <span class="text-slate-500 italic">Pengawas Tunggal</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-center border-r border-slate-300 whitespace-nowrap">
                                        <span class="text-[10px] font-black px-2 py-0.5 rounded border shadow-[1px_1px_0px_0px_#000] uppercase {{ $d->status_badge }}">
                                            {{ $d->status_label }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-center">
                                        @if($ruang)
                                            <button type="button" 
                                                    onclick="loadPesertaRuang({{ $ruang->id }}, '{{ addslashes($namaRuang) }}', {{ $activePeriode->id }})"
                                                    class="px-2.5 py-1 bg-[#FFF3BF] hover:bg-yellow-300 text-black border border-black rounded text-[11px] font-black shadow-[1px_1px_0px_0px_#000] cursor-pointer">
                                                👥 Siswa
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif
</div>

<!-- MODAL DAFTAR PESERTA SISWA DI RUANGAN -->
<div id="modalPeserta" class="fixed inset-0 z-50 hidden bg-black/70 flex items-center justify-center p-3 sm:p-5 overflow-y-auto">
    <div class="bg-white border-4 border-black w-full max-w-2xl shadow-[6px_6px_0px_0px_#000] flex flex-col max-h-[90vh]">
        <!-- Modal Header -->
        <div class="bg-[#FFD43B] border-b-3 border-black p-4 flex items-center justify-between shrink-0">
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider bg-black text-white px-2 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000]">
                    DAFTAR PESERTA UJIAN
                </span>
                <h3 id="modalRuangTitle" class="font-heading font-black text-base sm:text-lg text-black mt-1">
                    Ruang Ujian
                </h3>
            </div>
            <button type="button" onclick="closePesertaModal()" class="w-8 h-8 bg-white hover:bg-slate-100 text-black border-2 border-black font-black flex items-center justify-center shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                ✕
            </button>
        </div>

        <!-- Modal Search & Info Bar -->
        <div class="p-3 border-b-2 border-black bg-slate-50 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5 shrink-0">
            <div class="text-xs font-bold text-slate-700" id="modalPesertaCount">
                Memuat data peserta...
            </div>
            <div class="relative w-full sm:w-64">
                <input type="text" id="modalSearchInput" onkeyup="filterPesertaTable()" placeholder="Cari nama atau nomor..." 
                       class="w-full bg-white text-xs border-2 border-black py-1.5 px-3 rounded shadow-[1px_1px_0px_0px_#000] focus:ring-0 focus:border-black">
            </div>
        </div>

        <!-- Modal Body: Table Peserta -->
        <div class="p-4 overflow-y-auto flex-1">
            <!-- Loading State -->
            <div id="modalLoading" class="py-12 text-center space-y-2">
                <div class="inline-block w-8 h-8 border-4 border-black border-t-[#FFD43B] rounded-full animate-spin"></div>
                <div class="text-xs font-bold text-slate-600">Mengambil daftar peserta ruangan...</div>
            </div>

            <!-- Error State -->
            <div id="modalError" class="hidden py-8 text-center space-y-2">
                <div class="text-3xl">⚠️</div>
                <div id="modalErrorMessage" class="text-xs font-bold text-rose-600">Terjadi kesalahan saat memuat data peserta.</div>
            </div>

            <!-- Table Content -->
            <div id="modalTableWrapper" class="hidden border-2 border-black overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs" id="tablePesertaModal">
                    <thead>
                        <tr class="bg-black text-[#FFD43B] font-heading font-black uppercase text-[10px] border-b-2 border-black">
                            <th class="p-2.5 text-center w-12 border-r border-slate-700">Meja</th>
                            <th class="p-2.5 border-r border-slate-700">No. Peserta</th>
                            <th class="p-2.5 border-r border-slate-700">Nama Siswa</th>
                            <th class="p-2.5 border-r border-slate-700">Rombel/Kelas</th>
                            <th class="p-2.5 text-center">NISN</th>
                        </tr>
                    </thead>
                    <tbody id="pesertaTableBody" class="divide-y border-black font-semibold">
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="bg-slate-100 border-t-2 border-black p-3 flex justify-end shrink-0">
            <button type="button" onclick="closePesertaModal()" 
                    class="px-4 py-1.5 bg-black hover:bg-slate-800 text-white border-2 border-black text-xs font-heading font-black uppercase shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function switchView(mode) {
        const viewCards = document.getElementById('viewModeCard');
        const viewTable = document.getElementById('viewModeTable');
        const btnCards = document.getElementById('btnModeCard');
        const btnTable = document.getElementById('btnModeTable');

        if (mode === 'card') {
            viewCards.classList.remove('hidden');
            viewTable.classList.add('hidden');
            btnCards.className = "flex-1 sm:flex-none px-3 py-1.5 text-xs font-heading font-black border-2 border-black rounded transition-all cursor-pointer bg-[#FFD43B] text-black shadow-[2px_2px_0px_0px_#000]";
            btnTable.className = "flex-1 sm:flex-none px-3 py-1.5 text-xs font-heading font-black border-2 border-black rounded transition-all cursor-pointer bg-white text-slate-700 hover:bg-slate-100 shadow-[2px_2px_0px_0px_#000]";
        } else {
            viewCards.classList.add('hidden');
            viewTable.classList.remove('hidden');
            btnTable.className = "flex-1 sm:flex-none px-3 py-1.5 text-xs font-heading font-black border-2 border-black rounded transition-all cursor-pointer bg-[#FFD43B] text-black shadow-[2px_2px_0px_0px_#000]";
            btnCards.className = "flex-1 sm:flex-none px-3 py-1.5 text-xs font-heading font-black border-2 border-black rounded transition-all cursor-pointer bg-white text-slate-700 hover:bg-slate-100 shadow-[2px_2px_0px_0px_#000]";
        }
    }

    let allPesertas = [];

    function loadPesertaRuang(ruangId, namaRuang, periodeId) {
        const modal = document.getElementById('modalPeserta');
        const modalTitle = document.getElementById('modalRuangTitle');
        const modalCount = document.getElementById('modalPesertaCount');
        const modalLoading = document.getElementById('modalLoading');
        const modalError = document.getElementById('modalError');
        const modalErrorMsg = document.getElementById('modalErrorMessage');
        const modalWrapper = document.getElementById('modalTableWrapper');
        const searchInput = document.getElementById('modalSearchInput');

        modalTitle.textContent = namaRuang;
        modalCount.textContent = 'Memuat data peserta...';
        searchInput.value = '';
        allPesertas = [];

        modalLoading.classList.remove('hidden');
        modalError.classList.add('hidden');
        modalWrapper.classList.add('hidden');
        modal.classList.remove('hidden');

        const url = "{{ url('/guru/jadwal-pengawas/ruang') }}/" + ruangId + "/peserta?periode_id=" + periodeId;

        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                if (response.status === 403) {
                    throw new Error('Anda tidak memiliki hak otorisasi untuk melihat daftar peserta di ruangan ini.');
                }
                throw new Error('Gagal memuat data peserta (Status: ' + response.status + ')');
            }
            return response.json();
        })
        .then(data => {
            modalLoading.classList.add('hidden');
            allPesertas = data.pesertas || [];
            modalCount.textContent = 'Total ' + (data.total_peserta || 0) + ' Siswa teralokasi';
            renderPesertaTable(allPesertas);
            modalWrapper.classList.remove('hidden');
        })
        .catch(err => {
            modalLoading.classList.add('hidden');
            modalErrorMsg.textContent = err.message || 'Terjadi kesalahan sistem.';
            modalError.classList.remove('hidden');
        });
    }

    function renderPesertaTable(pesertas) {
        const tbody = document.getElementById('pesertaTableBody');
        tbody.innerHTML = '';

        if (pesertas.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="p-6 text-center text-slate-500 italic">Tidak ada data peserta di ruangan ini.</td></tr>';
            return;
        }

        pesertas.forEach(p => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50 transition-colors border-b border-slate-200';
            tr.innerHTML = `
                <td class="p-2.5 text-center font-mono font-bold bg-slate-50 border-r border-slate-300">
                    ${p.nomor_kursi ? ('Meja ' + String(p.nomor_kursi).padStart(2, '0')) : '-'}
                </td>
                <td class="p-2.5 font-mono text-slate-700 border-r border-slate-300">
                    ${p.nomor_peserta || '-'}
                </td>
                <td class="p-2.5 font-bold text-black border-r border-slate-300">
                    ${p.nama_peserta || '-'}
                </td>
                <td class="p-2.5 border-r border-slate-300">
                    <span class="bg-yellow-100 text-yellow-900 px-1.5 py-0.5 rounded text-[11px] font-bold border border-yellow-400">
                        ${p.nama_kelas || '-'}
                    </span>
                </td>
                <td class="p-2.5 text-center font-mono text-slate-600">
                    ${p.nisn || '-'}
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function filterPesertaTable() {
        const query = (document.getElementById('modalSearchInput').value || '').toLowerCase();
        const filtered = allPesertas.filter(p => {
            return (p.nama_peserta || '').toLowerCase().includes(query) ||
                   (p.nomor_peserta || '').toLowerCase().includes(query) ||
                   (p.nama_kelas || '').toLowerCase().includes(query) ||
                   (p.nisn || '').includes(query);
        });
        renderPesertaTable(filtered);
    }

    function closePesertaModal() {
        document.getElementById('modalPeserta').classList.add('hidden');
    }

    // Tutup modal jika tombol Escape ditekan atau backdrop diklik
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closePesertaModal();
    });

    document.getElementById('modalPeserta').addEventListener('click', function(e) {
        if (e.target === this) closePesertaModal();
    });
</script>
@endpush
