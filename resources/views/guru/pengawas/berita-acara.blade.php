@extends('layouts.guru')

@section('title', 'Berita Acara Ujian - ' . ($jadwalPengawas->ruang->nama_ruang ?? 'Ruang'))
@section('page-title', 'Berita Acara Pelaksanaan Ujian')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
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

    @if($errors->any())
        <div class="bg-[#FFE3E3] border-2 border-black p-4 neo-box text-rose-950 text-xs space-y-1">
            <div class="font-black flex items-center gap-1.5">
                <span>⚠️</span> <span>Terdapat beberapa kesalahan input:</span>
            </div>
            <ul class="list-disc pl-5 font-semibold">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $jadwal = $jadwalPengawas->jadwal;
        $ruang = $jadwalPengawas->ruang;
        $periode = $jadwal?->periode;
        $allowEarly = (bool) ($periode?->izinkan_pengisian_awal ?? false);
        $isVerified = $beritaAcara && $beritaAcara->isVerified();
        $isFuture = $jadwal?->tanggal_ujian?->isFuture();
        $isFutureBlocked = $isFuture && ! $allowEarly;
        $isReadOnly = $isVerified || $isFutureBlocked;

        // Map data absen yang tersimpan sebelumnya (jika ada)
        $existingAbsenMap = collect($beritaAcara?->daftar_absen_siswa ?? [])->keyBy('student_id');
        $totalPeserta = $pesertas->count();
        $initialTidakHadir = $existingAbsenMap->count();
        $initialHadir = max(0, $totalPeserta - $initialTidakHadir);

        // Naskah default
        $defaultDiterima = $beritaAcara?->naskah_soal_diterima ?? max(0, $totalPeserta + 2);
        $defaultDigunakan = $beritaAcara?->naskah_soal_digunakan ?? $initialHadir;
        $defaultSisa = $beritaAcara?->naskah_soal_sisa ?? max(0, $defaultDiterima - $defaultDigunakan);
        $defaultRusak = $beritaAcara?->naskah_soal_rusak ?? 0;
    @endphp

    <!-- Warning / Lock Banners -->
    @if($isVerified)
        <div class="bg-[#D3F9D8] border-3 border-black p-4 neo-box-lg text-emerald-950 flex items-center gap-3">
            <span class="text-3xl">🔒</span>
            <div>
                <div class="font-heading font-black text-sm uppercase">Laporan Berita Acara Telah Diverifikasi Panitia</div>
                <div class="text-xs font-semibold text-emerald-900 mt-0.5">
                    Dokumen ini telah diverifikasi dan dikunci resmi oleh Panitia Ujian pada <strong>{{ $beritaAcara->verified_at?->translatedFormat('d F Y H:i') }}</strong>. Data tidak dapat diubah lagi oleh pengawas.
                </div>
            </div>
        </div>
    @elseif($isFutureBlocked)
        <div class="bg-[#FFF3BF] border-3 border-black p-4 neo-box-lg text-amber-950 flex items-center gap-3">
            <span class="text-3xl">⏳</span>
            <div>
                <div class="font-heading font-black text-sm uppercase">Ujian Belum Dimulai</div>
                <div class="text-xs font-semibold text-amber-900 mt-0.5">
                    Ujian dijadwalkan pada <strong>{{ $jadwal?->tanggal_ujian?->translatedFormat('l, d F Y') }}</strong>. Berita Acara dan absensi baru dapat dikirim pada hari H pelaksanaan ujian.
                </div>
            </div>
        </div>
    @elseif($isFuture && $allowEarly)
        <div class="bg-[#E7F5FF] border-3 border-black p-4 neo-box-lg text-blue-950 flex items-center gap-3">
            <span class="text-3xl">⚡</span>
            <div>
                <div class="font-heading font-black text-sm uppercase">Mode Pengisian Awal Diizinkan Panitia</div>
                <div class="text-xs font-semibold text-blue-900 mt-0.5">
                    Ujian dijadwalkan pada <strong>{{ $jadwal?->tanggal_ujian?->translatedFormat('l, d F Y') }}</strong>. Panitia Ujian telah mengizinkan pengisian presensi dan berita acara lebih awal sebelum pelaksanaan ujian.
                </div>
            </div>
        </div>
    @endif

    <!-- Header Panel Informasi Ruang & Ujian -->
    <div class="bg-[#FFF3BF] neo-box-lg p-4 sm:p-6 border-3 border-black space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b-2 border-black pb-3">
            <div>
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <span class="neo-badge bg-black text-[#FFD43B] font-black text-[10px]">FORM BERITA ACARA</span>
                    <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border border-black">
                        {{ $ruang->kode_ruang ?? 'RUANG' }}
                    </span>
                    @if($beritaAcara)
                        <span class="text-[10px] font-black px-2 py-0.5 rounded border border-black {{ $beritaAcara->status === 'verified' ? 'bg-[#5294FF] text-white' : 'bg-[#20C997] text-black' }}">
                            {{ $beritaAcara->status === 'verified' ? '✓ DIVERIFIKASI' : '⏳ TERKIRIM' }}
                        </span>
                    @else
                        <span class="text-[10px] font-black px-2 py-0.5 rounded border border-black bg-rose-200 text-rose-950">
                            BLANGKO BELUM DIISI
                        </span>
                    @endif
                </div>
                <h1 class="font-heading font-black text-xl sm:text-2xl text-black uppercase">
                    {{ $ruang->nama_ruang }}
                </h1>
                <p class="text-xs font-semibold text-slate-800">
                    {{ $periode->nama_periode ?? 'Ujian Madrasah' }} • TP {{ $periode->tahun_pelajaran ?? '-' }}
                </p>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('guru.pengawas.index') }}" 
                   class="flex-1 sm:flex-none justify-center neo-btn bg-white hover:bg-slate-100 text-black text-xs font-black px-3.5 py-2 flex items-center gap-1.5 cursor-pointer shadow-[2px_2px_0px_0px_#000]">
                    ← Kembali
                </a>
                @if($beritaAcara)
                    <a href="{{ route('ujian.administrasi.berita-acara', ['jadwal_id' => $jadwal->id, 'ruang_id' => $ruang->id]) }}" 
                       target="_blank"
                       class="flex-1 sm:flex-none justify-center neo-btn bg-[#FFD43B] hover:bg-yellow-400 text-black text-xs font-black px-3.5 py-2 flex items-center gap-1.5 cursor-pointer shadow-[2px_2px_0px_0px_#000]">
                        🖨️ Cetak BA
                    </a>
                @endif
            </div>
        </div>

        <!-- Meta Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5 sm:gap-3 text-xs">
            <div class="bg-white p-2.5 border-2 border-black rounded shadow-[2px_2px_0px_0px_#000]">
                <div class="text-[10px] font-bold uppercase text-slate-500">Mata Pelajaran</div>
                <div class="font-heading font-black text-sm text-black truncate">{{ $jadwal?->nama_mapel ?? '-' }}</div>
            </div>
            <div class="bg-white p-2.5 border-2 border-black rounded shadow-[2px_2px_0px_0px_#000]">
                <div class="text-[10px] font-bold uppercase text-slate-500">Hari, Tanggal & Sesi</div>
                <div class="font-bold text-black truncate">
                    {{ $jadwal?->tanggal_ujian?->locale('id')->translatedFormat('l, d/m/Y') }}
                </div>
                <div class="text-[10px] font-mono font-bold text-slate-600">
                    Sesi {{ $jadwal?->sesi_ke }} ({{ substr($jadwal?->jam_mulai ?? '', 0, 5) }} - {{ substr($jadwal?->jam_selesai ?? '', 0, 5) }})
                </div>
            </div>
            <div class="bg-white p-2.5 border-2 border-black rounded shadow-[2px_2px_0px_0px_#000]">
                <div class="text-[10px] font-bold uppercase text-slate-500">Target Kelas Peserta</div>
                <div class="font-heading font-black text-sm text-black truncate">{{ $jadwal?->target_kelas ?? '-' }}</div>
                <div class="text-[10px] text-slate-600 font-semibold">{{ $totalPeserta }} Siswa Terdaftar</div>
            </div>
            <div class="bg-white p-2.5 border-2 border-black rounded shadow-[2px_2px_0px_0px_#000]">
                <div class="text-[10px] font-bold uppercase text-slate-500">Pengawas Ruang</div>
                <div class="font-bold text-black truncate text-[11px]" title="{{ $jadwalPengawas->pengawas1?->user?->name }}">
                    1. {{ $jadwalPengawas->pengawas1?->user?->name ?? 'Belum Diisi' }}
                </div>
                @if($jadwalPengawas->pengawas2)
                    <div class="font-bold text-slate-700 truncate text-[11px]" title="{{ $jadwalPengawas->pengawas2?->user?->name }}">
                        2. {{ $jadwalPengawas->pengawas2?->user?->name }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Form Utama Berita Acara -->
    <form action="{{ route('guru.pengawas.berita-acara.store', $jadwalPengawas->id) }}" method="POST" id="formBeritaAcara" class="space-y-6">
        @csrf

        <!-- BAGIAN 1: REKAPITULASI & CHECKLIST KEHADIRAN SISWA -->
        <div class="bg-white neo-box-lg border-3 border-black p-4 sm:p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b-2 border-black pb-3">
                <div>
                    <h2 class="font-heading font-black text-base text-black flex items-center gap-2">
                        <span>👥</span> I. DAFTAR HADIR SISWA DI RUANGAN
                    </h2>
                    <p class="text-xs text-slate-600 font-semibold mt-0.5">
                        Centang hijau bagi siswa yang <strong>Hadir</strong>. Hapus centang (uncheck) bila siswa <strong>Tidak Hadir</strong> dan pilih alasannya (Sakit, Izin, Alpa).
                    </p>
                </div>
                <!-- Dynamic Counter Pill -->
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <span class="bg-[#D3F9D8] text-emerald-950 border-2 border-black font-black text-xs px-2.5 py-1 rounded shadow-[2px_2px_0px_0px_#000]">
                        Hadir: <span id="counterHadir">{{ $initialHadir }}</span>
                    </span>
                    <span class="bg-[#FFE3E3] text-rose-950 border-2 border-black font-black text-xs px-2.5 py-1 rounded shadow-[2px_2px_0px_0px_#000]">
                        Tidak Hadir: <span id="counterTidakHadir">{{ $initialTidakHadir }}</span>
                    </span>
                    <span class="bg-slate-100 text-black border-2 border-black font-black text-xs px-2.5 py-1 rounded shadow-[2px_2px_0px_0px_#000]">
                        Total: {{ $totalPeserta }}
                    </span>
                </div>
            </div>

            <!-- Peserta Responsive Table / Cards -->
            <div class="border-2 border-black rounded-xl overflow-hidden bg-white shadow-[2px_2px_0px_0px_#000]">
                <table class="w-full text-left border-collapse text-xs responsive-student-table">
                    <thead class="hidden md:table-header-group">
                        <tr class="bg-black text-[#FFD43B] font-heading font-black uppercase text-[11px] border-b-2 border-black">
                            <th class="p-2.5 text-center w-14 border-r border-slate-700">Meja</th>
                            <th class="p-2.5 border-r border-slate-700 w-36">No. Peserta / NISN</th>
                            <th class="p-2.5 border-r border-slate-700">Nama Lengkap Siswa</th>
                            <th class="p-2.5 text-center border-r border-slate-700 w-24">Kelas</th>
                            <th class="p-2.5 text-center border-r border-slate-700 w-32">Status Hadir</th>
                            <th class="p-2.5">Keterangan / Alasan Absen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-black md:table-row-group block">
                        @forelse($pesertas as $index => $peserta)
                            @php
                                $sId = $peserta->student_id ?: $peserta->id;
                                $isAbsent = $existingAbsenMap->has($sId);
                                $savedAbsen = $existingAbsenMap->get($sId);
                                $savedAlasan = $savedAbsen['alasan'] ?? 'S';
                                $savedCatatan = $savedAbsen['catatan'] ?? '';
                            @endphp
                            <tr class="student-row block md:table-row p-3 md:p-0 border-b-2 md:border-b-0 border-black md:hover:bg-yellow-50/60 transition-colors {{ $isAbsent ? 'bg-rose-50/70' : 'bg-white' }}" id="row_{{ $sId }}">
                                <!-- Kolom 1: Meja (desktop only) -->
                                <td class="p-2.5 text-center font-mono font-black border-r border-slate-300 hidden md:table-cell">
                                    {{ $peserta->nomor_kursi ? str_pad($peserta->nomor_kursi, 2, '0', STR_PAD_LEFT) : ($index + 1) }}
                                </td>

                                <!-- Kolom 2: No Peserta & NISN (desktop only) -->
                                <td class="p-2.5 font-mono border-r border-slate-300 hidden md:table-cell">
                                    <div class="font-bold text-black">{{ $peserta->nomor_peserta ?? '-' }}</div>
                                    <div class="text-[10px] text-slate-500">NISN: {{ $peserta->nisn ?? '-' }}</div>
                                </td>

                                <!-- Kolom 3: Nama Siswa & Mobile Header Info -->
                                <td class="p-2.5 border-r border-slate-300 block md:table-cell">
                                    <!-- Mobile bar: Nomor Meja, Kelas, & No Peserta -->
                                    <div class="flex items-center justify-between gap-2 mb-1.5 md:hidden">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="inline-flex items-center justify-center font-mono font-black text-xs bg-black text-[#FFD43B] px-2 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000]">
                                                Meja {{ $peserta->nomor_kursi ? str_pad($peserta->nomor_kursi, 2, '0', STR_PAD_LEFT) : ($index + 1) }}
                                            </span>
                                            <span class="text-[11px] font-bold bg-slate-100 text-slate-800 px-2 py-0.5 rounded border border-slate-300">
                                                {{ $peserta->schoolClass?->name ?? $peserta->nama_kelas ?? '-' }}
                                            </span>
                                        </div>
                                        <span class="text-[10px] font-mono font-bold text-slate-600 bg-white px-1.5 py-0.5 rounded border border-slate-200">
                                            {{ $peserta->nomor_peserta ?? '-' }}
                                        </span>
                                    </div>

                                    <div class="font-heading font-black text-sm text-black">{{ $peserta->nama_peserta }}</div>
                                    <div class="text-[10px] text-slate-500 hidden md:block">{{ $peserta->schoolClass?->name ?? $peserta->nama_kelas }}</div>
                                    <div class="text-[10px] font-mono text-slate-500 md:hidden">NISN: {{ $peserta->nisn ?? '-' }}</div>
                                </td>

                                <!-- Kolom 4: Kelas (desktop only) -->
                                <td class="p-2.5 text-center font-bold border-r border-slate-300 hidden md:table-cell">
                                    {{ $peserta->schoolClass?->name ?? $peserta->nama_kelas ?? '-' }}
                                </td>

                                <!-- Kolom 5: Status Hadir Toggle -->
                                <td class="p-2.5 text-center border-r border-slate-300 block md:table-cell mt-2 md:mt-0">
                                    <label class="flex md:inline-flex items-center justify-between md:justify-center gap-2 p-2.5 md:p-0 rounded-lg md:rounded-none border-2 md:border-0 border-black md:border-transparent cursor-pointer select-none transition-all {{ !$isAbsent ? 'bg-emerald-50 md:bg-transparent' : 'bg-rose-50 md:bg-transparent' }}">
                                        <div class="flex items-center gap-2.5">
                                            <input type="checkbox" 
                                                   class="toggle-hadir w-5 h-5 md:w-4 md:h-4 text-emerald-600 rounded border-2 border-black focus:ring-0 cursor-pointer"
                                                   data-student-id="{{ $sId }}"
                                                   {{ !$isAbsent ? 'checked' : '' }}
                                                   {{ $isReadOnly ? 'disabled' : '' }}
                                                   onchange="handleToggleKehadiran(this, '{{ $sId }}')">
                                            <span class="text-xs font-black status-label {{ !$isAbsent ? 'text-emerald-800' : 'text-rose-800' }}">
                                                <span class="md:hidden">{{ !$isAbsent ? 'Hadir Mengikuti Ujian' : 'Tidak Hadir (Absen)' }}</span>
                                                <span class="hidden md:inline">{{ !$isAbsent ? 'Hadir' : 'Absen' }}</span>
                                            </span>
                                        </div>
                                        <span class="text-xs font-black md:hidden px-2 py-0.5 rounded border border-black {{ !$isAbsent ? 'bg-[#20C997] text-black' : 'bg-rose-200 text-rose-950' }}">
                                            {{ !$isAbsent ? 'HADIR' : 'ABSEN' }}
                                        </span>
                                    </label>
                                </td>

                                <!-- Kolom 6: Keterangan / Alasan Absen -->
                                <td class="p-2.5 block md:table-cell mt-2 md:mt-0">
                                    <!-- Container Data Absen (Hanya diaktifkan jika TIDAK HADIR) -->
                                    <div class="absen-details-container {{ $isAbsent ? '' : 'hidden' }} space-y-2 p-2.5 md:p-0 bg-white md:bg-transparent border-2 md:border-0 border-rose-300 rounded-lg" id="details_{{ $sId }}">
                                        <!-- Hidden Inputs Siswa Absen -->
                                        <input type="hidden" name="absen_siswa[{{ $sId }}][student_id]" value="{{ $sId }}" {{ $isAbsent ? '' : 'disabled' }}>
                                        <input type="hidden" name="absen_siswa[{{ $sId }}][nomor_peserta]" value="{{ $peserta->nomor_peserta }}" {{ $isAbsent ? '' : 'disabled' }}>
                                        <input type="hidden" name="absen_siswa[{{ $sId }}][nama_peserta]" value="{{ $peserta->nama_peserta }}" {{ $isAbsent ? '' : 'disabled' }}>
                                        <input type="hidden" name="absen_siswa[{{ $sId }}][nama_kelas]" value="{{ $peserta->schoolClass?->name ?? $peserta->nama_kelas }}" {{ $isAbsent ? '' : 'disabled' }}>

                                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                                            <div class="w-full sm:w-44 shrink-0">
                                                <label class="block text-[10px] font-black uppercase text-slate-600 mb-0.5 sm:hidden">Alasan Tidak Hadir:</label>
                                                <select name="absen_siswa[{{ $sId }}][alasan]" 
                                                        {{ $isAbsent ? '' : 'disabled' }}
                                                        {{ $isReadOnly ? 'disabled' : '' }}
                                                        class="w-full bg-white text-black font-bold text-xs border-2 border-black rounded p-2 sm:py-1 focus:ring-0 focus:border-black cursor-pointer shadow-[1px_1px_0px_0px_#000]">
                                                    <option value="S" {{ $savedAlasan === 'S' ? 'selected' : '' }}>Sakit (S)</option>
                                                    <option value="I" {{ $savedAlasan === 'I' ? 'selected' : '' }}>Izin (I)</option>
                                                    <option value="A" {{ $savedAlasan === 'A' ? 'selected' : '' }}>Alpa / Tnp Ket (A)</option>
                                                </select>
                                            </div>
                                            <div class="flex-1">
                                                <label class="block text-[10px] font-black uppercase text-slate-600 mb-0.5 sm:hidden">Catatan Tambahan:</label>
                                                <input type="text" 
                                                       name="absen_siswa[{{ $sId }}][catatan]" 
                                                       value="{{ $savedCatatan }}"
                                                       placeholder="Catatan tambahan (opsional)"
                                                       {{ $isAbsent ? '' : 'disabled' }}
                                                       {{ $isReadOnly ? 'disabled' : '' }}
                                                       class="w-full bg-white text-xs border-2 border-black rounded p-2 sm:py-1 placeholder-slate-400 focus:ring-0 shadow-[1px_1px_0px_0px_#000]">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-semibold hadir-placeholder {{ $isAbsent ? 'hidden' : '' }}" id="placeholder_{{ $sId }}">
                                        — Peserta terverifikasi mengikuti ujian —
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-6 text-center text-slate-500 font-semibold">
                                    Tidak ada data peserta yang terdaftar di ruangan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- BAGIAN 2: ADMINISTRASI NASKAH SOAL & LJK -->
        <div class="bg-white neo-box-lg border-3 border-black p-4 sm:p-6 space-y-4">
            <div class="border-b-2 border-black pb-3">
                <h2 class="font-heading font-black text-base text-black flex items-center gap-2">
                    <span>📄</span> II. ADMINISTRASI NASKAH SOAL & LEMBAR JAWABAN
                </h2>
                <p class="text-xs text-slate-600 font-semibold mt-0.5">
                    Pastikan jumlah naskah yang diterima sesuai dengan amplop ruang, dan hitungan digunakan + sisa + rusak tidak melebihi naskah diterima.
                </p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
                <div class="space-y-1">
                    <label class="block text-xs font-black uppercase text-slate-700">
                        1. Naskah Diterima <span class="text-rose-600">*</span>
                    </label>
                    <input type="number" 
                           id="inputDiterima"
                           name="naskah_soal_diterima" 
                           value="{{ old('naskah_soal_diterima', $defaultDiterima) }}" 
                           min="0" 
                           required
                           {{ $isReadOnly ? 'disabled' : '' }}
                           oninput="recalculateNaskah()"
                           class="w-full bg-white text-black font-mono font-black text-sm border-2 border-black rounded p-2.5 shadow-[2px_2px_0px_0px_#000] focus:ring-0">
                    <span class="text-[10px] text-slate-500">Termasuk cadangan</span>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-black uppercase text-slate-700">
                        2. Naskah Digunakan <span class="text-rose-600">*</span>
                    </label>
                    <input type="number" 
                           id="inputDigunakan"
                           name="naskah_soal_digunakan" 
                           value="{{ old('naskah_soal_digunakan', $defaultDigunakan) }}" 
                           min="0" 
                           required
                           {{ $isReadOnly ? 'disabled' : '' }}
                           oninput="recalculateNaskah()"
                           class="w-full bg-white text-black font-mono font-black text-sm border-2 border-black rounded p-2.5 shadow-[2px_2px_0px_0px_#000] focus:ring-0">
                    <span class="text-[10px] text-slate-500">Sinkron siswa hadir</span>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-black uppercase text-slate-700">
                        3. Naskah Sisa <span class="text-rose-600">*</span>
                    </label>
                    <input type="number" 
                           id="inputSisa"
                           name="naskah_soal_sisa" 
                           value="{{ old('naskah_soal_sisa', $defaultSisa) }}" 
                           min="0" 
                           required
                           {{ $isReadOnly ? 'disabled' : '' }}
                           class="w-full bg-white text-black font-mono font-black text-sm border-2 border-black rounded p-2.5 shadow-[2px_2px_0px_0px_#000] focus:ring-0">
                    <span class="text-[10px] text-slate-500">Naskah belum dipakai</span>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-black uppercase text-slate-700">
                        4. Naskah Rusak
                    </label>
                    <input type="number" 
                           id="inputRusak"
                           name="naskah_soal_rusak" 
                           value="{{ old('naskah_soal_rusak', $defaultRusak) }}" 
                           min="0" 
                           {{ $isReadOnly ? 'disabled' : '' }}
                           oninput="recalculateNaskah()"
                           class="w-full bg-white text-black font-mono font-black text-sm border-2 border-black rounded p-2.5 shadow-[2px_2px_0px_0px_#000] focus:ring-0">
                    <span class="text-[10px] text-slate-500">Robek, cetakan buram</span>
                </div>
            </div>
        </div>

        <!-- BAGIAN 3: CATATAN KHUSUS PELAKSANAAN UJIAN -->
        <div class="bg-white neo-box-lg border-3 border-black p-4 sm:p-6 space-y-4">
            <div class="border-b-2 border-black pb-3">
                <h2 class="font-heading font-black text-base text-black flex items-center gap-2">
                    <span>📝</span> III. CATATAN KEJADIAN KHUSUS SELAMA UJIAN
                </h2>
                <p class="text-xs text-slate-600 font-semibold mt-0.5">
                    Tuliskan catatan kejadian penting di ruang ujian jika ada (gangguan ketertiban, siswa sakit saat ujian, atau penggantian naskah soal).
                </p>
            </div>

            <div>
                <textarea name="catatan_kejadian" 
                          rows="3" 
                          placeholder="Pelaksanaan ujian berjalan tertib, aman, dan lancar."
                          {{ $isReadOnly ? 'disabled' : '' }}
                          class="w-full bg-white text-black font-semibold text-xs border-2 border-black rounded p-3 shadow-[2px_2px_0px_0px_#000] focus:ring-0">{{ old('catatan_kejadian', $beritaAcara?->catatan_kejadian) }}</textarea>
            </div>
        </div>

        <!-- Action Submit Buttons (Sticky on Mobile, Standard on Desktop) -->
        @if(!$isReadOnly)
            <div class="sticky bottom-3 z-30 sm:static bg-white/95 sm:bg-transparent backdrop-blur-sm p-3 sm:p-0 border-2 sm:border-0 border-black rounded-xl sm:rounded-none shadow-[4px_4px_0px_0px_#000] sm:shadow-none flex flex-col sm:flex-row items-center justify-end gap-2.5 sm:gap-3 pt-2">
                <a href="{{ route('guru.pengawas.index') }}" 
                   class="w-full sm:w-auto px-5 py-2.5 bg-white hover:bg-slate-100 text-black border-2 border-black rounded-lg font-heading font-black text-xs uppercase text-center shadow-[2px_2px_0px_0px_#000] order-2 sm:order-1">
                    Batal
                </a>
                <button type="submit" 
                        class="w-full sm:w-auto px-6 py-3 sm:py-2.5 bg-[#FFD43B] hover:bg-yellow-400 text-black border-2 border-black rounded-lg font-heading font-black text-xs uppercase flex items-center justify-center gap-2 shadow-[3px_3px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 cursor-pointer order-1 sm:order-2">
                    <span>💾</span> Simpan & Kirim Berita Acara
                </button>
            </div>
        @endif
    </form>
</div>

<script>
    const totalPeserta = {{ $totalPeserta }};

    function handleToggleKehadiran(checkbox, studentId) {
        const row = document.getElementById('row_' + studentId);
        const detailsContainer = document.getElementById('details_' + studentId);
        const placeholder = document.getElementById('placeholder_' + studentId);
        const isHadir = checkbox.checked;
        const statusLabel = checkbox.closest('label').querySelector('.status-label');
        const labelContainer = checkbox.closest('label');

        if (isHadir) {
            statusLabel.innerHTML = '<span class="md:hidden">Hadir Mengikuti Ujian</span><span class="hidden md:inline">Hadir</span>';
            statusLabel.classList.remove('text-rose-800');
            statusLabel.classList.add('text-emerald-800');
            row.classList.remove('bg-rose-50/70');
            row.classList.add('bg-white');
            if (labelContainer) {
                labelContainer.classList.remove('bg-rose-50');
                labelContainer.classList.add('bg-emerald-50');
            }
            detailsContainer.classList.add('hidden');
            placeholder.classList.remove('hidden');

            // Disable absent inputs so they are not sent via POST
            detailsContainer.querySelectorAll('input, select').forEach(el => el.disabled = true);
        } else {
            statusLabel.innerHTML = '<span class="md:hidden">Tidak Hadir (Absen)</span><span class="hidden md:inline">Absen</span>';
            statusLabel.classList.remove('text-emerald-800');
            statusLabel.classList.add('text-rose-800');
            row.classList.remove('bg-white');
            row.classList.add('bg-rose-50/70');
            if (labelContainer) {
                labelContainer.classList.remove('bg-emerald-50');
                labelContainer.classList.add('bg-rose-50');
            }
            detailsContainer.classList.remove('hidden');
            placeholder.classList.add('hidden');

            // Enable absent inputs for form submission
            detailsContainer.querySelectorAll('input, select').forEach(el => el.disabled = false);
        }

        recalculateKehadiran();
    }

    function recalculateKehadiran() {
        const uncheckedBoxes = document.querySelectorAll('.toggle-hadir:not(:checked)');
        const tidakHadir = uncheckedBoxes.length;
        const hadir = Math.max(0, totalPeserta - tidakHadir);

        document.getElementById('counterHadir').textContent = hadir;
        document.getElementById('counterTidakHadir').textContent = tidakHadir;

        // Auto-update Naskah Digunakan
        const inputDigunakan = document.getElementById('inputDigunakan');
        if (inputDigunakan) {
            inputDigunakan.value = hadir;
            recalculateNaskah();
        }
    }

    function recalculateNaskah() {
        const diterima = parseInt(document.getElementById('inputDiterima')?.value || 0, 10);
        const digunakan = parseInt(document.getElementById('inputDigunakan')?.value || 0, 10);
        const rusak = parseInt(document.getElementById('inputRusak')?.value || 0, 10);

        const inputSisa = document.getElementById('inputSisa');
        if (inputSisa) {
            const calculatedSisa = Math.max(0, diterima - digunakan - rusak);
            inputSisa.value = calculatedSisa;
        }
    }
</script>
@endsection
