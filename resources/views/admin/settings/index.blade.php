@extends('layouts.admin')

@section('title', 'Pengaturan Sistem & Lembaga')
@section('page-title', 'Pengaturan Sistem & Informasi Sekolah')

@section('content')
<style>
    .settings-tab {
        transition: all .15s ease;
    }
    .settings-tab.tab-active {
        background-color: #000 !important;
        color: #fff !important;
        box-shadow: 4px 4px 0 0 #FFD43B;
        transform: translate(-1px, -1px);
    }
    .settings-tab.tab-active .tab-icon {
        transform: rotate(-6deg) scale(1.15);
    }
    .tab-icon {
        display: inline-block;
        transition: transform .15s ease;
    }
</style>

<div class="max-w-5xl space-y-8">

    <!-- Tab Navigation -->
    <div class="bg-white neo-box-lg p-2 border-2 border-black grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
        <button type="button" data-tab="absensi" onclick="switchTab('absensi')"
                class="settings-tab neo-btn bg-white hover:bg-[#FFF9DB] text-black px-3 py-3 text-[11px] sm:text-xs font-heading font-black uppercase tracking-wider flex items-center justify-center gap-2 cursor-pointer border-2 border-black tab-active">
            <span class="tab-icon">⚙️</span> Konfigurasi Absensi
        </button>
        <button type="button" data-tab="profil" onclick="switchTab('profil')"
                class="settings-tab neo-btn bg-white hover:bg-[#FFF9DB] text-black px-3 py-3 text-[11px] sm:text-xs font-heading font-black uppercase tracking-wider flex items-center justify-center gap-2 cursor-pointer border-2 border-black">
            <span class="tab-icon">🏫</span> Profil Lembaga
        </button>
        <button type="button" data-tab="periode" onclick="switchTab('periode')"
                class="settings-tab neo-btn bg-white hover:bg-[#FFF9DB] text-black px-3 py-3 text-[11px] sm:text-xs font-heading font-black uppercase tracking-wider flex items-center justify-center gap-2 cursor-pointer border-2 border-black">
            <span class="tab-icon">📅</span> Periode Akademik
        </button>
        <button type="button" data-tab="database" onclick="switchTab('database')"
                class="settings-tab neo-btn bg-white hover:bg-[#FFF9DB] text-black px-3 py-3 text-[11px] sm:text-xs font-heading font-black uppercase tracking-wider flex items-center justify-center gap-2 cursor-pointer border-2 border-black">
            <span class="tab-icon">🗄️</span> Pemeliharaan Data
        </button>
    </div>

    <!-- ============================================================ -->
    <!-- TAB 1: KONFIGURASI ABSENSI + TAB 2: PROFIL LEMBAGA (1 form) -->
    <!-- ============================================================ -->
    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Tab 1: Aturan Presensi -->
        <div id="tab-absensi" class="tab-panel">
            <div class="bg-white neo-box-lg p-6 sm:p-8 space-y-6">
                <div class="border-b-2 border-black pb-3">
                    <span class="neo-badge bg-[#5294FF] text-white">KONFIGURASI ABSENSI</span>
                    <h2 class="font-heading font-black text-xl text-black mt-2">
                        Metode & Parameter Presensi
                    </h2>
                    <p class="text-xs text-slate-600 font-semibold mt-0.5">
                        Tentukan bagaimana absensi diproses oleh scanner QR dan batas waktu toleransi terlambat.
                    </p>
                </div>

                <!-- Mode Presensi -->
                <div>
                    <label class="block font-heading font-bold text-sm text-black mb-2">Mode Absensi Sekolah *</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Daily Mode -->
                        <label class="neo-box p-4 cursor-pointer flex items-start gap-3 transition-all hover:bg-slate-50 relative {{ $setting->mode === 'daily' ? 'bg-[#FFF9DB]' : 'bg-white' }}">
                            <input type="radio" name="mode" value="daily" class="mt-1 w-4 h-4 text-[#5294FF] border-2 border-black" {{ $setting->mode === 'daily' ? 'checked' : '' }}>
                            <div>
                                <div class="font-heading font-black text-sm text-black flex items-center gap-1.5">
                                    <span>🌅</span> Mode Harian (Daily)
                                </div>
                                <p class="text-xs text-slate-600 font-medium mt-1">
                                    Siswa absen 1 kali per hari (Jam Masuk dan Jam Pulang). Cocok untuk jenjang SD atau MI.
                                </p>
                            </div>
                        </label>

                        <!-- Per Lesson Mode -->
                        <label class="neo-box p-4 cursor-pointer flex items-start gap-3 transition-all hover:bg-slate-50 relative {{ $setting->mode === 'per_lesson' ? 'bg-[#FFF9DB]' : 'bg-white' }}">
                            <input type="radio" name="mode" value="per_lesson" class="mt-1 w-4 h-4 text-[#5294FF] border-2 border-black" {{ $setting->mode === 'per_lesson' ? 'checked' : '' }}>
                            <div>
                                <div class="font-heading font-black text-sm text-black flex items-center gap-1.5">
                                    <span>⏱️</span> Mode Per Mata Pelajaran
                                </div>
                                <p class="text-xs text-slate-600 font-medium mt-1">
                                    Kehadiran dicatat setiap jam mata pelajaran oleh guru mapel. Cocok untuk SMP/SMA/SMK.
                                </p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Toleransi Keterlambatan & Tahun Ajaran -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2">
                    <div>
                        <label class="block font-heading font-bold text-xs text-black mb-1.5">
                            Batas Toleransi Keterlambatan (Menit) *
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                name="tolerance_minutes"
                                value="{{ old('tolerance_minutes', $setting->tolerance_minutes) }}"
                                min="0"
                                max="120"
                                required
                                class="w-full px-3.5 py-2.5 neo-input bg-slate-50 font-bold text-sm text-black"
                            >
                        </div>
                        <span class="text-[11px] text-slate-500 font-semibold mt-1 block">
                            Siswa yang melakukan scan setelah jam masuk + toleransi ini otomatis berstatus <strong>Terlambat</strong>.
                        </span>
                    </div>

                    <div>
                        <label class="block font-heading font-bold text-xs text-black mb-1.5">
                            Tahun Ajaran Aktif Saat Ini *
                        </label>
                        <select name="active_academic_year_id" class="w-full px-3.5 py-2.5 neo-input bg-slate-50 font-bold text-sm text-black">
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ $year->is_active ? 'selected' : '' }}>
                                    {{ $year->name }} - Semester {{ ucfirst($year->semester) }} {{ $year->is_active ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <span class="text-[11px] text-slate-500 font-semibold mt-1 block">
                            Menentukan periode semester yang sedang berlangsung pada jadwal.
                        </span>
                    </div>
                </div>

                <!-- Submit Absensi -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t-2 border-slate-200">
                    <button type="submit" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-6 py-3 text-sm uppercase tracking-wider font-heading cursor-pointer">
                        💾 Simpan Konfigurasi Absensi
                    </button>
                </div>
            </div>
        </div>

        <!-- Tab 2: Informasi Profil Sekolah -->
        <div id="tab-profil" class="tab-panel hidden">
            <div class="bg-white neo-box-lg p-6 sm:p-8 space-y-5">
                <div class="border-b-2 border-black pb-3">
                    <span class="neo-badge bg-[#FFD43B] text-black">PROFIL LEMBAGA</span>
                    <h2 class="font-heading font-black text-xl text-black mt-2">
                        Identitas Satuan Pendidikan
                    </h2>
                    <p class="text-xs text-slate-600 font-semibold mt-0.5">
                        Informasi ini dicetak pada kartu identitas QR dan header laporan rekapitulasi.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-heading font-bold text-xs text-black mb-1">Nama Lembaga / Sekolah *</label>
                        <input type="text" name="school_name" value="{{ old('school_name', $setting->school_name ?? 'SMP Negeri 1 Garuda') }}" required class="w-full px-3.5 py-2.5 neo-input bg-slate-50 font-bold text-sm text-black">
                    </div>
                    <div>
                        <label class="block font-heading font-bold text-xs text-black mb-1">NPSN</label>
                        <input type="text" name="npsn" value="{{ old('npsn', $setting->npsn ?? '20102030') }}" class="w-full px-3.5 py-2.5 neo-input bg-slate-50 font-mono text-sm text-black">
                    </div>
                    <div>
                        <label class="block font-heading font-bold text-xs text-black mb-1">Jenjang Satuan Pendidikan *</label>
                        <select name="level" required class="w-full px-3.5 py-2.5 neo-input bg-slate-50 font-bold text-sm text-black">
                            @foreach(['SD', 'MI', 'SMP', 'MTs', 'SMA', 'MA', 'SMK'] as $lvl)
                                <option value="{{ $lvl }}" {{ old('level', $setting->level ?? 'SMP') === $lvl ? 'selected' : '' }}>
                                    {{ $lvl }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">Alamat Lengkap</label>
                    <input type="text" name="school_address" value="{{ old('school_address', $setting->school_address ?? 'Jl. Pendidikan No. 45, Kompleks Pelajar Mandiri') }}" class="w-full px-3.5 py-2.5 neo-input bg-slate-50 text-sm text-black">
                    <span class="text-[11px] text-slate-500 font-semibold mt-1 block">
                        Jenjang di atas berfungsi sebagai <strong>sumber data utama</strong> yang otomatis diterapkan pada seluruh rombel/kelas.
                    </span>
                </div>

                <!-- Submit Profil -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t-2 border-slate-200">
                    <button type="submit" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-6 py-3 text-sm uppercase tracking-wider font-heading cursor-pointer">
                        💾 Simpan Profil Sekolah
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- ============================================================ -->
    <!-- TAB 3: MANAJEMEN TAHUN AJARAN & SEMESTER                     -->
    <!-- ============================================================ -->
    <div id="tab-periode" class="tab-panel hidden">
        <div class="bg-white neo-box-lg p-6 sm:p-8 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b-2 border-black pb-4">
                <div>
                    <span class="neo-badge bg-[#20C997] text-black">PERIODE AKADEMIK</span>
                    <h2 class="font-heading font-black text-xl text-black mt-2">
                        Manajemen Tahun Ajaran & Semester
                    </h2>
                    <p class="text-xs text-slate-600 font-semibold mt-0.5">
                        Kelola data tahun ajaran, semester, periode tanggal, serta aktifkan atau nonaktifkan tahun ajaran terpilih.
                    </p>
                </div>
                <div>
                    <button type="button" onclick="openModal('createAcademicYearModal')" class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-4 py-2.5 text-xs font-heading font-black flex items-center gap-2 cursor-pointer shadow-[3px_3px_0px_0px_#000]">
                        <span>➕</span> Tambah Tahun Ajaran Baru
                    </button>
                </div>
            </div>

            <!-- Tabel Tahun Ajaran -->
            <div class="border-2 border-black overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-[#FFF9DB] border-b-2 border-black text-xs font-black uppercase tracking-wider">
                        <tr>
                            <th class="p-3 border-r border-black text-center w-12">No</th>
                            <th class="p-3 border-r border-black">Tahun Ajaran</th>
                            <th class="p-3 border-r border-black">Semester</th>
                            <th class="p-3 border-r border-black">Periode Tanggal</th>
                            <th class="p-3 border-r border-black text-center">Kelas Terdaftar</th>
                            <th class="p-3 border-r border-black text-center">Status</th>
                            <th class="p-3 text-center w-36">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-black">
                        @forelse($academicYears as $index => $year)
                        <tr class="hover:bg-slate-50 font-medium {{ $year->is_active ? 'bg-[#FFF9DB]/40' : '' }}">
                            <td class="p-3 font-bold text-center border-r border-black">
                                {{ $index + 1 }}
                            </td>
                            <td class="p-3 font-black text-black border-r border-black">
                                {{ $year->name }}
                            </td>
                            <td class="p-3 border-r border-black">
                                <span class="neo-badge {{ $year->semester === 'ganjil' ? 'bg-[#5294FF] text-white' : 'bg-[#FFD43B] text-black' }} text-[10px]">
                                    {{ ucfirst($year->semester) }}
                                </span>
                            </td>
                            <td class="p-3 border-r border-black text-xs font-mono">
                                {{ $year->start_date ? \Carbon\Carbon::parse($year->start_date)->format('d M Y') : '-' }} s/d {{ $year->end_date ? \Carbon\Carbon::parse($year->end_date)->format('d M Y') : '-' }}
                            </td>
                            <td class="p-3 border-r border-black text-center font-bold">
                                <span class="bg-[#D3F9D8] px-2 py-0.5 border border-black text-xs font-mono">
                                    {{ $year->school_classes_count ?? $year->schoolClasses()->count() }}
                                </span>
                            </td>
                            <td class="p-3 border-r border-black text-center">
                                @if($year->is_active)
                                    <span class="bg-[#20C997] text-black font-heading font-black text-[11px] px-2.5 py-1 border border-black shadow-[1px_1px_0px_0px_#000] inline-flex items-center gap-1">
                                        ★ Aktif
                                    </span>
                                @else
                                    <span class="bg-slate-100 text-slate-600 font-bold text-[11px] px-2.5 py-1 border border-black inline-flex items-center">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <!-- Tombol Toggle Status -->
                                    <div class="relative group inline-block">
                                        <form action="{{ route('admin.academic-years.toggle', $year) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="neo-btn {{ $year->is_active ? 'bg-[#FFD43B] hover:bg-[#fcc419]' : 'bg-[#20C997] hover:bg-[#12b886]' }} text-black p-1.5 text-xs cursor-pointer flex items-center justify-center"
                                                    aria-label="{{ $year->is_active ? 'Nonaktifkan Tahun Ajaran' : 'Jadikan Tahun Ajaran Aktif' }}">
                                                @if($year->is_active)
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                @endif
                                            </button>
                                        </form>
                                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:flex flex-col items-center pointer-events-none z-50 transition-all duration-150">
                                            <div class="bg-[#FFF9DB] text-black text-[11px] font-bold px-2.5 py-1 whitespace-nowrap border-2 border-black shadow-[2px_2px_0px_0px_#000] rounded-sm">
                                                {{ $year->is_active ? 'Nonaktifkan Tahun Ajaran Ini' : 'Aktifkan Tahun Ajaran Ini' }}
                                            </div>
                                            <div class="w-2 h-2 bg-[#FFF9DB] border-r-2 border-b-2 border-black rotate-45 -mt-1"></div>
                                        </div>
                                    </div>

                                    <!-- Tombol Edit -->
                                    <div class="relative group inline-block">
                                        <button type="button"
                                                onclick="editAcademicYear({{ json_encode([
                                                    'id' => $year->id,
                                                    'name' => $year->name,
                                                    'semester' => $year->semester,
                                                    'start_date' => $year->start_date ? \Carbon\Carbon::parse($year->start_date)->format('Y-m-d') : '',
                                                    'end_date' => $year->end_date ? \Carbon\Carbon::parse($year->end_date)->format('Y-m-d') : '',
                                                    'is_active' => (bool)$year->is_active,
                                                ]) }})"
                                                class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black p-1.5 text-xs cursor-pointer flex items-center justify-center"
                                                aria-label="Edit Tahun Ajaran">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                        </button>
                                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:flex flex-col items-center pointer-events-none z-50 transition-all duration-150">
                                            <div class="bg-[#FFF9DB] text-black text-[11px] font-bold px-2.5 py-1 whitespace-nowrap border-2 border-black shadow-[2px_2px_0px_0px_#000] rounded-sm">
                                                Edit Tahun Ajaran
                                            </div>
                                            <div class="w-2 h-2 bg-[#FFF9DB] border-r-2 border-b-2 border-black rotate-45 -mt-1"></div>
                                        </div>
                                    </div>

                                    <!-- Tombol Hapus -->
                                    <div class="relative group inline-block">
                                        <form action="{{ route('admin.academic-years.destroy', $year) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tahun ajaran ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="neo-btn bg-[#FF6B6B] hover:bg-[#fa5252] text-white p-1.5 text-xs cursor-pointer flex items-center justify-center"
                                                    aria-label="Hapus Tahun Ajaran">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:flex flex-col items-center pointer-events-none z-50 transition-all duration-150">
                                            <div class="bg-[#FFF9DB] text-black text-[11px] font-bold px-2.5 py-1 whitespace-nowrap border-2 border-black shadow-[2px_2px_0px_0px_#000] rounded-sm">
                                                Hapus Tahun Ajaran
                                            </div>
                                            <div class="w-2 h-2 bg-[#FFF9DB] border-r-2 border-b-2 border-black rotate-45 -mt-1"></div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-500 font-semibold">
                                Belum ada data tahun ajaran. Klik tombol "+ Tambah Tahun Ajaran Baru" di atas.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- TAB 4: PEMELIHARAAN & ARSIP DATABASE PRESENSI                -->
    <!-- ============================================================ -->
    <div id="tab-database" class="tab-panel hidden">
        <div class="bg-white neo-box-lg p-6 sm:p-8 space-y-6">
            <div class="border-b-2 border-black pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <span class="neo-badge bg-[#845EF7] text-white">PEMELIHARAAN DATABASE</span>
                    <h2 class="font-heading font-black text-xl text-black mt-2">
                        Kapasitas & Pengarsipan Data Presensi
                    </h2>
                    <p class="text-xs text-slate-600 font-semibold mt-0.5">
                        Pindahkan data presensi dari Tahun Ajaran non-aktif ke tabel arsip agar database tetap cepat dan responsif.
                    </p>
                </div>

                <!-- Tombol Defragmentasi / Optimize Table -->
                <form action="{{ route('admin.database.optimize') }}" method="POST" onsubmit="return confirm('Jalankan proses optimasi & defragmentasi indeks database sekarang?')">
                    @csrf
                    <button type="submit"
                            class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-4 py-2 text-xs font-bold flex items-center gap-2 cursor-pointer shadow-[3px_3px_0px_0px_#000]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span>⚡ Optimasi Database</span>
                    </button>
                </form>
            </div>

            <!-- Realtime Database Metrics -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Data Aktif -->
                <div class="neo-box p-4 bg-[#F4F6FB] flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Tabel Aktif (attendances)</span>
                        <span class="w-2.5 h-2.5 rounded-full bg-[#20C997]"></span>
                    </div>
                    <div class="mt-2 font-heading font-black text-2xl text-black">
                        {{ number_format($activeAttendanceCount) }} <span class="text-xs font-normal text-slate-500">baris</span>
                    </div>
                    <div class="mt-1 flex items-center gap-1.5 text-[11px] font-semibold text-slate-600">
                        <span class="neo-badge bg-[#20C997] text-white text-[9px] px-1 py-0">LIVE</span>
                        <span>Digunakan scanner QR aktif</span>
                    </div>
                </div>

                <!-- Data Terarsip -->
                <div class="neo-box p-4 bg-[#F4F6FB] flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Tabel Arsip (archive)</span>
                        <span class="w-2.5 h-2.5 rounded-full bg-[#5294FF]"></span>
                    </div>
                    <div class="mt-2 font-heading font-black text-2xl text-black">
                        {{ number_format($archivedAttendanceCount) }} <span class="text-xs font-normal text-slate-500">baris</span>
                    </div>
                    <div class="mt-1 flex items-center gap-1.5 text-[11px] font-semibold text-slate-600">
                        <span class="neo-badge bg-[#5294FF] text-white text-[9px] px-1 py-0">COLD</span>
                        <span>Tersimpan aman & dapat dipulihkan</span>
                    </div>
                </div>

                <!-- Status Efisiensi -->
                <div class="neo-box p-4 bg-black text-white flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-300 uppercase tracking-wider">Status Performa</span>
                        <span class="text-xs font-mono font-bold text-[#FFD43B]">MySQL</span>
                    </div>
                    <div class="mt-2 font-heading font-black text-xl text-[#20C997]">
                        {{ $activeAttendanceCount < 200000 ? 'Sangat Prima (Fast)' : 'Perlu Diarsip' }}
                    </div>
                    <div class="mt-1 text-[11px] text-slate-400">
                        Indeks B-Tree Terpantau
                    </div>
                </div>
            </div>

            <!-- Educational Callout Box: Petunjuk Detail Database -->
            <div class="bg-[#E7F5FF] border-2 border-black p-5 rounded-lg shadow-[3px_3px_0px_0px_#000] space-y-3">
                <div class="flex items-center gap-2 font-heading font-black text-sm text-blue-950">
                    <span class="text-lg">ℹ️</span>
                    <span>PETUNJUK SISTEM: Mengapa & Bagaimana Pengarsipan Bekerja pada Database?</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs text-blue-950 font-medium">
                    <div class="bg-white/90 p-3 rounded border border-blue-300">
                        <div class="font-bold text-black mb-1">1. Mengapa Perlu Diarsip?</div>
                        Setiap 500 siswa dengan 26 hari belajar menghasilkan <strong>~13.000 data/bulan</strong> atau <strong>~156.000 data/tahun</strong>. Pengarsipan menjaga tabel utama tetap kecil sehingga proses scan kartu QR dan perhitungan rekap tetap secepat kilat (<50ms).
                    </div>
                    <div class="bg-white/90 p-3 rounded border border-blue-300">
                        <div class="font-bold text-black mb-1">2. Apakah Data Aman?</div>
                        <strong>100% AMAN.</strong> Sistem TIDAK menghapus data Anda secara permanen. Data hanya dipindahkan ke tabel <code>attendances_archive</code> dan dapat <strong>dipulihkan (restore)</strong> kapan saja jika diperlukan untuk keperluan akreditasi atau audit.
                    </div>
                    <div class="bg-white/90 p-3 rounded border border-blue-300">
                        <div class="font-bold text-black mb-1">3. Kapan Harus Dilakukan?</div>
                        Lakukan pengarsipan ketika <strong>Tahun Ajaran telah berakhir</strong> dan seluruh nilai rapor atau laporan kehadiran telah diserahkan, sebelum mengaktifkan Tahun Ajaran yang baru.
                    </div>
                </div>
            </div>

            <!-- Tabel Kelola Arsip per Tahun Ajaran -->
            <div class="space-y-3 pt-2">
                <h3 class="font-heading font-bold text-sm text-black">
                    Daftar Periode & Status Pengarsipan
                </h3>

                <div class="overflow-x-auto neo-box">
                    <table class="w-full text-left text-xs text-slate-800">
                        <thead class="bg-slate-100 border-b-2 border-black font-bold uppercase text-[11px] text-black">
                            <tr>
                                <th class="p-3">Tahun Ajaran</th>
                                <th class="p-3">Semester</th>
                                <th class="p-3 text-center">Status Periode</th>
                                <th class="p-3 text-center">Data Presensi Aktif</th>
                                <th class="p-3 text-center">Data Terarsip</th>
                                <th class="p-3 text-center">Aksi Pemeliharaan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-slate-100 font-medium">
                            @forelse($academicYears as $year)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="p-3 font-heading font-black text-black">
                                    {{ $year->name }}
                                </td>
                                <td class="p-3 uppercase font-bold text-slate-600">
                                    {{ $year->semester }}
                                </td>
                                <td class="p-3 text-center">
                                    @if($year->is_active)
                                        <span class="neo-badge bg-[#20C997] text-white">AKTIF SEKARANG</span>
                                    @else
                                        <span class="neo-badge bg-slate-200 text-slate-700">NON-AKTIF</span>
                                    @endif
                                </td>
                                <td class="p-3 text-center font-mono font-bold">
                                    @if($year->active_records_count > 0)
                                        <span class="text-emerald-700 bg-[#D3F9D8] px-2 py-0.5 rounded border border-black">
                                            {{ number_format($year->active_records_count) }} baris
                                        </span>
                                    @else
                                        <span class="text-slate-400">0 baris</span>
                                    @endif
                                </td>
                                <td class="p-3 text-center font-mono font-bold">
                                    @if($year->archived_records_count > 0)
                                        <span class="text-blue-700 bg-[#D0EBFF] px-2 py-0.5 rounded border border-black">
                                            {{ number_format($year->archived_records_count) }} baris
                                        </span>
                                    @else
                                        <span class="text-slate-400">0 baris</span>
                                    @endif
                                </td>
                                <td class="p-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        @if($year->is_active)
                                            <span class="text-[11px] font-bold text-slate-400 italic">
                                                Terkunci (Periode Aktif)
                                            </span>
                                        @else
                                            @if($year->active_records_count > 0)
                                                <button type="button"
                                                        onclick="openArchiveModal({{ $year->id }})"
                                                        class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-2.5 py-1 text-[11px] font-bold flex items-center gap-1 cursor-pointer">
                                                    <span>📦</span>
                                                    <span>Arsipkan</span>
                                                </button>
                                            @endif

                                            @if($year->archived_records_count > 0)
                                                <button type="button"
                                                        onclick="openRestoreModal({{ $year->id }}, '{{ $year->name }}', '{{ $year->semester }}', {{ $year->archived_records_count }})"
                                                        class="neo-btn bg-white hover:bg-slate-100 text-black px-2.5 py-1 text-[11px] font-bold flex items-center gap-1 cursor-pointer border-2 border-black">
                                                    <span>🔄</span>
                                                    <span>Pulihkan</span>
                                                </button>
                                            @endif

                                            @if($year->active_records_count == 0 && $year->archived_records_count == 0)
                                                <span class="text-slate-400 text-[11px]">Tidak ada data</span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="p-6 text-center text-slate-500 font-bold">
                                    Belum ada data tahun ajaran.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Tahun Ajaran -->
<div id="createAcademicYearModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-md w-full p-6 space-y-5 relative">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>➕</span> Tambah Tahun Ajaran Baru
            </h3>
            <button onclick="closeModal('createAcademicYearModal')" class="text-black font-black text-xl hover:opacity-75">✕</button>
        </div>

        <form action="{{ route('admin.academic-years.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Nama Tahun Ajaran *</label>
                <input type="text" name="name" required placeholder="Contoh: 2026/2027 atau 2027/2028" class="w-full px-3.5 py-2 neo-input text-sm bg-slate-50 font-bold">
            </div>

            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Semester *</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="neo-box p-3 cursor-pointer flex items-center gap-2 bg-white hover:bg-slate-50">
                        <input type="radio" name="semester" value="ganjil" checked class="w-4 h-4 text-[#5294FF]">
                        <span class="font-heading font-bold text-xs">Ganjil</span>
                    </label>
                    <label class="neo-box p-3 cursor-pointer flex items-center gap-2 bg-white hover:bg-slate-50">
                        <input type="radio" name="semester" value="genap" class="w-4 h-4 text-[#5294FF]">
                        <span class="font-heading font-bold text-xs">Genap</span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">Tanggal Mulai *</label>
                    <input type="date" name="start_date" required class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-medium">
                </div>
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">Tanggal Selesai *</label>
                    <input type="date" name="end_date" required class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-medium">
                </div>
            </div>

            <div class="pt-2">
                <label class="neo-box p-3 cursor-pointer flex items-center gap-3 bg-[#FFF9DB]">
                    <input type="checkbox" name="is_active" value="1" class="w-4 h-4 text-[#20C997]">
                    <span class="font-heading font-bold text-xs text-black">
                        Langsung jadikan periode ini sebagai <strong>Tahun Ajaran Aktif</strong>
                    </span>
                </label>
            </div>

            <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('createAcademicYearModal')" class="neo-btn bg-white text-black px-4 py-2 text-xs">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#20C997] text-black px-5 py-2 text-xs font-heading">
                    Simpan Periode
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Tahun Ajaran -->
<div id="editAcademicYearModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-md w-full p-6 space-y-5 relative">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>✏️</span> Edit Data Tahun Ajaran
            </h3>
            <button onclick="closeModal('editAcademicYearModal')" class="text-black font-black text-xl hover:opacity-75">✕</button>
        </div>

        <form id="editAcademicYearForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Nama Tahun Ajaran *</label>
                <input type="text" id="edit_ay_name" name="name" required class="w-full px-3.5 py-2 neo-input text-sm bg-slate-50 font-bold">
            </div>

            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Semester *</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="neo-box p-3 cursor-pointer flex items-center gap-2 bg-white hover:bg-slate-50">
                        <input type="radio" id="edit_ay_semester_ganjil" name="semester" value="ganjil" class="w-4 h-4 text-[#5294FF]">
                        <span class="font-heading font-bold text-xs">Ganjil</span>
                    </label>
                    <label class="neo-box p-3 cursor-pointer flex items-center gap-2 bg-white hover:bg-slate-50">
                        <input type="radio" id="edit_ay_semester_genap" name="semester" value="genap" class="w-4 h-4 text-[#5294FF]">
                        <span class="font-heading font-bold text-xs">Genap</span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">Tanggal Mulai *</label>
                    <input type="date" id="edit_ay_start_date" name="start_date" required class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-medium">
                </div>
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">Tanggal Selesai *</label>
                    <input type="date" id="edit_ay_end_date" name="end_date" required class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-medium">
                </div>
            </div>

            <div class="pt-2">
                <label class="neo-box p-3 cursor-pointer flex items-center gap-3 bg-[#FFF9DB]">
                    <input type="checkbox" id="edit_ay_is_active" name="is_active" value="1" class="w-4 h-4 text-[#20C997]">
                    <span class="font-heading font-bold text-xs text-black">
                        Jadikan periode ini sebagai <strong>Tahun Ajaran Aktif</strong>
                    </span>
                </label>
            </div>

            <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('editAcademicYearModal')" class="neo-btn bg-white text-black px-4 py-2 text-xs">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#5294FF] text-white px-5 py-2 text-xs font-heading">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edukatif 3-Langkah: Pengarsipan Database -->
<div id="archiveModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-lg w-full p-6 space-y-5 relative">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <div class="flex items-center gap-2">
                <span class="text-2xl">📦</span>
                <div>
                    <h3 class="font-heading font-black text-lg text-black">
                        Pengarsipan Data Presensi
                    </h3>
                    <div class="text-[11px] font-bold text-slate-500" id="archiveModalSubtitle">
                        Tahun Ajaran ...
                    </div>
                </div>
            </div>
            <button onclick="closeModal('archiveModal')" class="text-black font-black text-xl hover:opacity-75">✕</button>
        </div>

        <form id="archiveForm" action="{{ route('admin.database.archive') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="academic_year_id" id="archive_academic_year_id">

            <!-- Langkah 1: Pratinjau Data -->
            <div class="bg-[#FFF9DB] border-2 border-black p-3.5 rounded">
                <div class="flex items-center gap-1.5 text-xs font-black text-amber-950 uppercase tracking-wider mb-2">
                    <span class="w-5 h-5 rounded-full bg-black text-white flex items-center justify-center text-[10px]">1</span>
                    <span>Pratinjau Data yang Dipindahkan</span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <span class="text-slate-500 block text-[10px] font-bold uppercase">Rentang Tanggal</span>
                        <span class="font-bold text-black" id="archivePreviewDates">-</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[10px] font-bold uppercase">Jumlah Baris Presensi</span>
                        <span class="font-heading font-black text-emerald-700 text-sm" id="archivePreviewCount">- baris</span>
                    </div>
                </div>
            </div>

            <!-- Langkah 2: Rincian Teknis di Database (Transparan & Detail) -->
            <div class="border-2 border-black p-3.5 rounded bg-slate-50 space-y-2">
                <div class="flex items-center gap-1.5 text-xs font-black text-black uppercase tracking-wider mb-1">
                    <span class="w-5 h-5 rounded-full bg-black text-white flex items-center justify-center text-[10px]">2</span>
                    <span>Rincian Tindakan Sistem pada Database</span>
                </div>
                <ul class="text-xs text-slate-700 space-y-1.5 pl-1">
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-600 font-bold">✓</span>
                        <span>Seluruh <strong id="archivePreviewCountText">-</strong> catatan absensi disalin ke tabel arsip aman <code>attendances_archive</code>.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-600 font-bold">✓</span>
                        <span>Catatan dihapus dari tabel aktif <code>attendances</code> agar tabel utama menjadi ramping kembali.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-600 font-bold">✓</span>
                        <span>Data <strong>tidak hilang</strong> dan bisa Anda pulihkan (restore) sewaktu-waktu dengan 1 klik.</span>
                    </li>
                </ul>
            </div>

            <!-- Langkah 3: Konfirmasi Eksekusi -->
            <div class="pt-2">
                <div class="flex items-center gap-1.5 text-xs font-black text-black uppercase tracking-wider mb-2">
                    <span class="w-5 h-5 rounded-full bg-black text-white flex items-center justify-center text-[10px]">3</span>
                    <span>Konfirmasi Tindakan</span>
                </div>
                <p class="text-xs text-slate-600 font-medium mb-3">
                    Pastikan seluruh proses pelaporan nilai/rapor pada tahun ajaran ini telah selesai sebelum melakukan pengarsipan.
                </p>

                <div class="flex items-center justify-end gap-3 pt-3 border-t-2 border-slate-200">
                    <button type="button" onclick="closeModal('archiveModal')" class="neo-btn bg-white text-black px-4 py-2 text-xs">
                        Batal
                    </button>
                    <button type="submit" class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-5 py-2 text-xs font-bold">
                        Saya Paham, Jalankan Pengarsipan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pemulihan (Restore) Data -->
<div id="restoreModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-md w-full p-6 space-y-5 relative">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <div class="flex items-center gap-2">
                <span class="text-2xl">🔄</span>
                <h3 class="font-heading font-black text-lg text-black">
                    Pulihkan Data Presensi
                </h3>
            </div>
            <button onclick="closeModal('restoreModal')" class="text-black font-black text-xl hover:opacity-75">✕</button>
        </div>

        <form id="restoreForm" action="{{ route('admin.database.restore') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="academic_year_id" id="restore_academic_year_id">

            <div class="bg-[#D0EBFF] border-2 border-black p-3.5 rounded text-xs text-blue-950 font-medium">
                Data presensi Tahun Ajaran <strong id="restoreYearName">-</strong> (<strong id="restoreCount">-</strong> catatan) akan disalin kembali dari tabel arsip ke tabel utama <code>attendances</code>.
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t-2 border-slate-200">
                <button type="button" onclick="closeModal('restoreModal')" class="neo-btn bg-white text-black px-4 py-2 text-xs">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#20C997] text-black px-5 py-2 text-xs font-bold">
                    Pulihkan ke Tabel Aktif
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function switchTab(name) {
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
        const panel = document.getElementById('tab-' + name);
        if (panel) panel.classList.remove('hidden');

        document.querySelectorAll('.settings-tab').forEach(btn => {
            if (btn.getAttribute('data-tab') === name) {
                btn.classList.add('tab-active');
            } else {
                btn.classList.remove('tab-active');
            }
        });
    }

    function editAcademicYear(data) {
        document.getElementById('edit_ay_name').value = data.name;
        if (data.semester === 'genap') {
            document.getElementById('edit_ay_semester_genap').checked = true;
        } else {
            document.getElementById('edit_ay_semester_ganjil').checked = true;
        }
        document.getElementById('edit_ay_start_date').value = data.start_date;
        document.getElementById('edit_ay_end_date').value = data.end_date;
        document.getElementById('edit_ay_is_active').checked = !!data.is_active;
        document.getElementById('editAcademicYearForm').action = '/admin/academic-years/' + data.id;
        openModal('editAcademicYearModal');
    }

    function openArchiveModal(yearId) {
        document.getElementById('archive_academic_year_id').value = yearId;
        document.getElementById('archivePreviewDates').innerText = 'Memuat data...';
        document.getElementById('archivePreviewCount').innerText = 'Memuat...';
        document.getElementById('archivePreviewCountText').innerText = '...';
        openModal('archiveModal');

        fetch('/admin/database-maintenance/preview/' + yearId)
            .then(res => res.json())
            .then(data => {
                document.getElementById('archiveModalSubtitle').innerText = `${data.name} (${data.semester.toUpperCase()})`;
                document.getElementById('archivePreviewDates').innerText = `${data.start_date} s/d ${data.end_date}`;
                document.getElementById('archivePreviewCount').innerText = `${data.active_count} baris data`;
                document.getElementById('archivePreviewCountText').innerText = `${data.active_count}`;
            })
            .catch(err => {
                console.error(err);
                document.getElementById('archivePreviewDates').innerText = 'Gagal memuat pratinjau';
            });
    }

    function openRestoreModal(yearId, name, semester, count) {
        document.getElementById('restore_academic_year_id').value = yearId;
        document.getElementById('restoreYearName').innerText = `${name} (${semester.toUpperCase()})`;
        document.getElementById('restoreCount').innerText = `${count} baris data`;
        openModal('restoreModal');
    }
</script>
@endpush
@endsection