@extends('layouts.guru')

@section('title', 'Kelas Binaan (Wali Kelas)')
@section('page-title', 'Manajemen Siswa & Kehadiran Kelas Binaan')

@section('content')
<div class="space-y-6">
    <!-- Header Title Card -->
    <div class="bg-[#20C997] neo-box-lg p-6 sm:p-8 text-black relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="space-y-1.5 z-10">
            <div class="flex items-center gap-2">
                <span class="neo-badge bg-black text-white">PORTAL WALI KELAS</span>
                <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border border-black">
                    NIP: {{ $teacher->nip ?? '-' }}
                </span>
                <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border border-black hidden sm:inline-block">
                    {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                </span>
            </div>
            <h1 class="font-heading text-2xl sm:text-3xl font-black tracking-tight text-black uppercase">
                KELAS BINAAN (WALI KELAS)
            </h1>
            <p class="text-xs sm:text-sm font-semibold text-slate-900 max-w-2xl">
                Pantau data komprehensif kehadiran siswa binaan, periksa perizinan, dan hubungi orang tua/wali murid dengan akses cepat WhatsApp.
            </p>
        </div>

        <div class="z-10 flex flex-wrap gap-2">
            <a href="{{ route('guru.dashboard') }}" class="neo-btn bg-white text-black text-xs font-bold px-4 py-2 flex items-center gap-1.5 cursor-pointer hover:bg-slate-100">
                ← Kembali ke Dashboard
            </a>
        </div>
    </div>

    @if($homeroomClasses->isEmpty())
        <!-- Empty State: Guru Bukan Wali Kelas -->
        <div class="bg-white neo-box p-12 text-center space-y-4 max-w-2xl mx-auto">
            <div class="text-5xl">🛡️</div>
            <div class="space-y-1">
                <h3 class="font-heading font-black text-xl text-black">Anda Belum Ditugaskan Sebagai Wali Kelas</h3>
                <p class="text-xs sm:text-sm font-medium text-slate-600">
                    Akun Anda saat ini belum tercatat sebagai wali kelas untuk rombongan belajar manapun pada tahun ajaran aktif.
                </p>
            </div>
            <div class="p-4 bg-slate-50 border-2 border-black rounded-sm text-xs font-semibold text-slate-700 max-w-lg mx-auto text-left space-y-1">
                <div class="font-bold text-black flex items-center gap-1.5">
                    <span>💡</span> Informasi:
                </div>
                <div>• Penugasan wali kelas dilakukan oleh Administrator Sekolah pada menu Data Master Kelas.</div>
                <div>• Jika Anda adalah wali kelas, silakan hubungi bagian tata usaha / admin sistem sekolah.</div>
            </div>
            <div class="pt-2">
                <a href="{{ route('guru.attendance.manual') }}" class="neo-btn bg-[#5294FF] text-white text-xs font-bold px-5 py-2.5 inline-flex items-center gap-2">
                    <span>📋</span> Masuk ke Presensi Manual Pengajar
                </a>
            </div>
        </div>
    @else
        <!-- Filter & Aksi Cepat Toolbar -->
        <div class="bg-white neo-box p-5 space-y-4">
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <!-- Class Selector & Search -->
                <form action="{{ route('guru.classes.binaan') }}" method="GET" class="flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full lg:w-auto">
                    <!-- Dropdown Kelas (jika > 1) -->
                    @if($homeroomClasses->count() > 1)
                        <div>
                            <label class="block text-[11px] font-black uppercase text-black mb-1">Pilih Kelas Binaan:</label>
                            <select name="school_class_id" onchange="this.form.submit()"
                                    class="bg-white border-2 border-black px-3 py-2 text-xs font-bold text-black focus:outline-hidden focus:ring-2 focus:ring-black">
                                @foreach($homeroomClasses as $c)
                                    <option value="{{ $c->id }}" {{ $selectedClassId == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }} (Tingkat {{ $c->level }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="school_class_id" value="{{ $selectedClassId }}">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-black uppercase bg-[#D3F9D8] text-emerald-950 border-2 border-black px-3 py-1.5 rounded-sm">
                                🏫 Kelas: <b>{{ $selectedClass->name ?? '-' }}</b>
                            </span>
                            <span class="text-xs font-bold bg-slate-100 border border-black px-2 py-1 rounded-sm">
                                Tingkat {{ $selectedClass->level ?? '-' }} • {{ $selectedClass->academicYear->name ?? 'Tahun Aktif' }}
                            </span>
                        </div>
                    @endif

                    <!-- Pencarian Siswa -->
                    <div class="w-full sm:w-64">
                        <label class="block text-[11px] font-black uppercase text-black mb-1">Cari Siswa:</label>
                        <div class="relative">
                            <input type="text" name="search" value="{{ $search }}" placeholder="Nama / NIS / NISN..."
                                   class="w-full bg-slate-50 border-2 border-black px-3 py-1.5 text-xs font-bold focus:bg-white focus:outline-hidden">
                            @if($search)
                                <a href="{{ route('guru.classes.binaan', ['school_class_id' => $selectedClassId]) }}" 
                                   class="absolute right-2 top-1.5 text-xs font-black text-slate-500 hover:text-black">✕</a>
                            @endif
                        </div>
                    </div>

                    <div class="pt-0 sm:pt-4">
                        <button type="submit" class="neo-btn bg-black text-white text-xs px-3 py-1.5 font-bold cursor-pointer hover:bg-slate-800">
                            Cari
                        </button>
                    </div>
                </form>

                <!-- Quick Action Buttons -->
                <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto justify-start lg:justify-end">
                    <a href="{{ route('guru.attendance.manual', ['school_class_id' => $selectedClass->id]) }}" 
                       class="neo-btn bg-[#20C997] text-black text-xs font-black px-3.5 py-2 flex items-center gap-1.5 hover:bg-emerald-400">
                        <span>💾</span> Input Presensi Hari Ini
                    </a>
                    <a href="{{ route('guru.reports.attendance', ['school_class_id' => $selectedClass->id]) }}" 
                       class="neo-btn bg-[#5294FF] text-white text-xs font-bold px-3.5 py-2 flex items-center gap-1.5 hover:bg-blue-600">
                        <span>📊</span> Rekap Kehadiran
                    </a>
                    <a href="{{ route('guru.classes.binaan.export', ['school_class_id' => $selectedClass->id]) }}" 
                       class="neo-btn bg-emerald-100 text-emerald-950 text-xs font-bold px-3.5 py-2 flex items-center gap-1.5 hover:bg-emerald-200">
                        <span>📥</span> Unduh Excel (.xlsx)
                    </a>
                    <button type="button" onclick="window.print()" 
                            class="neo-btn bg-white text-black text-xs font-bold px-3.5 py-2 flex items-center gap-1.5 hover:bg-slate-100">
                        <span>🖨️</span> Cetak
                    </button>
                </div>
            </div>
        </div>

        <!-- 6 KPI Cards: Kehadiran Hari Ini (Real-Time) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
            <!-- Total Siswa -->
            <div class="bg-white neo-box p-4 flex flex-col justify-between">
                <div class="text-[11px] font-black uppercase text-slate-600 flex items-center justify-between">
                    <span>Total Siswa</span>
                    <span>👥</span>
                </div>
                <div class="mt-2 font-heading font-black text-2xl text-black">
                    {{ $kpi['total'] }}
                </div>
                <div class="text-[10px] font-semibold text-slate-500 mt-1">Siswa Terdaftar</div>
            </div>

            <!-- Hadir (Hijau) -->
            <div class="bg-[#D3F9D8] neo-box p-4 flex flex-col justify-between">
                <div class="text-[11px] font-black uppercase text-emerald-950 flex items-center justify-between">
                    <span>Hadir</span>
                    <span>🟢</span>
                </div>
                <div class="mt-2 font-heading font-black text-2xl text-emerald-950">
                    {{ $kpi['hadir'] }}
                </div>
                <div class="text-[10px] font-bold text-emerald-800 mt-1">Hari Ini</div>
            </div>

            <!-- Terlambat (Biru) -->
            <div class="bg-[#D0EBFF] neo-box p-4 flex flex-col justify-between">
                <div class="text-[11px] font-black uppercase text-blue-950 flex items-center justify-between">
                    <span>Terlambat</span>
                    <span>🔵</span>
                </div>
                <div class="mt-2 font-heading font-black text-2xl text-blue-950">
                    {{ $kpi['terlambat'] }}
                </div>
                <div class="text-[10px] font-bold text-blue-800 mt-1">Hari Ini</div>
            </div>

            <!-- Izin (Abu-abu) -->
            <div class="bg-[#E9ECEF] neo-box p-4 flex flex-col justify-between">
                <div class="text-[11px] font-black uppercase text-slate-800 flex items-center justify-between">
                    <span>Izin</span>
                    <span>⚪</span>
                </div>
                <div class="mt-2 font-heading font-black text-2xl text-slate-800">
                    {{ $kpi['izin'] }}
                </div>
                <div class="text-[10px] font-bold text-slate-600 mt-1">Hari Ini</div>
            </div>

            <!-- Sakit (Kuning) -->
            <div class="bg-[#FFF3BF] neo-box p-4 flex flex-col justify-between">
                <div class="text-[11px] font-black uppercase text-amber-950 flex items-center justify-between">
                    <span>Sakit</span>
                    <span>🟡</span>
                </div>
                <div class="mt-2 font-heading font-black text-2xl text-amber-950">
                    {{ $kpi['sakit'] }}
                </div>
                <div class="text-[10px] font-bold text-amber-800 mt-1">Hari Ini</div>
            </div>

            <!-- Alpa (Merah) -->
            <div class="bg-[#FFE3E3] neo-box p-4 flex flex-col justify-between">
                <div class="text-[11px] font-black uppercase text-rose-950 flex items-center justify-between">
                    <span>Alpa</span>
                    <span>🔴</span>
                </div>
                <div class="mt-2 font-heading font-black text-2xl text-rose-950">
                    {{ $kpi['alpa'] }}
                </div>
                <div class="text-[10px] font-bold text-rose-800 mt-1">Hari Ini</div>
            </div>
        </div>

        <!-- Progress Rate Kehadiran Hari Ini -->
        <div class="bg-white neo-box p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <span class="text-2xl">📈</span>
                <div>
                    <div class="text-xs font-black uppercase text-black">
                        Tingkat Kehadiran Kelas {{ $selectedClass->name ?? '' }} Hari Ini:
                    </div>
                    <div class="text-[11px] font-medium text-slate-500">
                        {{ $kpi['hadir'] + $kpi['terlambat'] }} dari {{ $kpi['total'] }} siswa telah hadir di sekolah hari ini
                        @if($kpi['belum_absen'] > 0)
                            • <span class="font-bold text-amber-600">{{ $kpi['belum_absen'] }} siswa belum tercatat</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3 w-full sm:w-72">
                <div class="w-full bg-slate-200 border-2 border-black h-4 rounded-sm overflow-hidden">
                    <div class="bg-[#20C997] h-full transition-all duration-500" style="width: {{ $kpi['rate'] }}%"></div>
                </div>
                <span class="font-mono font-black text-sm text-black min-w-[50px] text-right">{{ $kpi['rate'] }}%</span>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="border-b-2 border-black flex items-center gap-2 overflow-x-auto">
            <button type="button" onclick="switchTab('direktori')" id="tabBtn-direktori"
                    class="tab-btn px-4 py-2.5 text-xs font-black uppercase border-t-2 border-l-2 border-r-2 border-black bg-white text-black -mb-[2px] transition-all">
                👥 Direktori Siswa & Kontak Ortu ({{ $students->count() }})
            </button>
            <button type="button" onclick="switchTab('izin')" id="tabBtn-izin"
                    class="tab-btn px-4 py-2.5 text-xs font-bold uppercase border-t-2 border-l-2 border-r-2 border-transparent text-slate-600 hover:text-black hover:border-slate-300 transition-all">
                📝 Pengajuan Izin / Sakit ({{ $leaveRequests->count() }})
            </button>
            <button type="button" onclick="switchTab('rekap-bulan')" id="tabBtn-rekap-bulan"
                    class="tab-btn px-4 py-2.5 text-xs font-bold uppercase border-t-2 border-l-2 border-r-2 border-transparent text-slate-600 hover:text-black hover:border-slate-300 transition-all">
                📊 Rekap Bulan Ini ({{ \Carbon\Carbon::today()->translatedFormat('F Y') }})
            </button>
            <button type="button" onclick="switchTab('catatan')" id="tabBtn-catatan"
                    class="tab-btn px-4 py-2.5 text-xs font-bold uppercase border-t-2 border-l-2 border-r-2 border-transparent text-slate-600 hover:text-black hover:border-slate-300 transition-all">
                📌 Catatan Khusus Siswa ({{ $studentNotes->count() }})
            </button>
        </div>

        <!-- TAB 1: Direktori Siswa & Kontak Orang Tua -->
        <div id="tabContent-direktori" class="tab-pane space-y-4">
            <div class="bg-white neo-box overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-100 border-b-2 border-black text-xs font-black uppercase text-black">
                            <tr>
                                <th class="p-3 w-12 text-center border-r-2 border-black">No</th>
                                <th class="p-3 border-r-2 border-black">Siswa</th>
                                <th class="p-3 w-14 text-center border-r-2 border-black">L/P</th>
                                <th class="p-3 text-center border-r-2 border-black min-w-[140px]">Status Hari Ini</th>
                                <th class="p-3 border-r-2 border-black min-w-[200px]">Kontak Siswa</th>
                                <th class="p-3 border-r-2 border-black min-w-[240px]">Orang Tua / Wali</th>
                                <th class="p-3 text-center w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-black">
                            @forelse($students as $index => $stu)
                                @php
                                    $todayAtt = $stu->attendances->first();
                                    $firstParent = $stu->parents->first();
                                    $parentName = $firstParent?->user?->name ?? '-';
                                    $parentRelation = $firstParent?->relation ? ucfirst($firstParent->relation) : '-';
                                    $parentPhone = $firstParent?->phone ?? '';
                                    
                                    // Format no WhatsApp
                                    $cleanPhone = preg_replace('/[^0-9]/', '', $parentPhone);
                                    if (str_starts_with($cleanPhone, '0')) {
                                        $cleanPhone = '62' . substr($cleanPhone, 1);
                                    }
                                    $waUrl = $cleanPhone ? 'https://wa.me/' . $cleanPhone . '?text=' . urlencode("Halo Bapak/Ibu {$parentName}, saya Wali Kelas {$selectedClass->name} ingin menginformasikan terkait kehadiran siswa an. {$stu->user->name}.") : null;
                                @endphp
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="p-3 text-center font-mono font-bold text-xs border-r-2 border-black">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="p-3 border-r-2 border-black">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-[#5294FF] text-white font-black text-xs flex items-center justify-center border-2 border-black shrink-0">
                                                {{ strtoupper(substr($stu->user->name ?? 'S', 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="font-black text-black text-sm">{{ $stu->user->name ?? '-' }}</div>
                                                <div class="flex items-center gap-2 mt-0.5 text-xs text-slate-500 font-mono">
                                                    <span>NIS: <b>{{ $stu->nis }}</b></span>
                                                    <span>•</span>
                                                    <span>NISN: {{ $stu->nisn ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-3 text-center font-bold text-xs border-r-2 border-black">
                                        <span class="inline-block px-1.5 py-0.5 rounded border border-black {{ $stu->gender == 'L' ? 'bg-blue-50 text-blue-900' : 'bg-pink-50 text-pink-900' }}">
                                            {{ $stu->gender }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-center border-r-2 border-black">
                                        @if($todayAtt)
                                            <span class="neo-badge text-[11px]
                                                @if($todayAtt->status === 'hadir') bg-[#20C997] text-white 
                                                @elseif($todayAtt->status === 'terlambat') bg-[#339AF0] text-white 
                                                @elseif($todayAtt->status === 'izin') bg-[#868E96] text-white 
                                                @elseif($todayAtt->status === 'sakit') bg-[#FFD43B] text-black 
                                                @else bg-[#FF6B6B] text-white @endif">
                                                {{ strtoupper($todayAtt->status) }}
                                            </span>
                                        @else
                                            <span class="neo-badge bg-slate-200 text-slate-700 text-[11px]">
                                                BELUM HADIR
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-3 border-r-2 border-black font-mono text-xs">
                                        @if($stu->phone)
                                            <div class="font-bold text-slate-800">{{ $stu->phone }}</div>
                                        @else
                                            <span class="text-slate-400 italic">Tidak ada kontak</span>
                                        @endif
                                    </td>
                                    <td class="p-3 border-r-2 border-black">
                                        @if($firstParent)
                                            <div class="space-y-1">
                                                <div class="font-bold text-black text-xs flex items-center gap-1.5">
                                                    <span>{{ $parentName }}</span>
                                                    <span class="text-[10px] bg-slate-100 border border-slate-300 px-1 py-0.2 rounded text-slate-600 font-semibold">
                                                        {{ $parentRelation }}
                                                    </span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="font-mono text-xs text-slate-600">{{ $parentPhone ?: '-' }}</span>
                                                    @if($waUrl)
                                                        <a href="{{ $waUrl }}" target="_blank" 
                                                           title="Kirim pesan WhatsApp ke Wali Murid"
                                                           class="neo-btn bg-[#20C997] text-white text-[10px] font-bold px-2 py-0.5 inline-flex items-center gap-1 hover:bg-emerald-400">
                                                            <span>💬</span> WhatsApp
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-xs text-slate-400 italic">Data ortu belum ditautkan</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-center">
                                        <button type="button" 
                                                onclick="openStudentDetailModal({{ json_encode([
                                                    'id' => $stu->id,
                                                    'name' => $stu->user->name ?? '-',
                                                    'nis' => $stu->nis,
                                                    'nisn' => $stu->nisn ?? '-',
                                                    'gender' => $stu->gender == 'L' ? 'Laki-laki' : 'Perempuan',
                                                    'birth_date' => $stu->birth_date ? \Carbon\Carbon::parse($stu->birth_date)->translatedFormat('d F Y') : '-',
                                                    'phone' => $stu->phone ?? '-',
                                                    'class_name' => $selectedClass->name,
                                                    'parent_name' => $parentName,
                                                    'parent_relation' => $parentRelation,
                                                    'parent_phone' => $parentPhone,
                                                    'wa_url' => $waUrl,
                                                    'm_hadir' => $stu->month_hadir,
                                                    'm_terlambat' => $stu->month_terlambat,
                                                    'm_izin' => $stu->month_izin,
                                                    'm_sakit' => $stu->month_sakit,
                                                    'm_alpa' => $stu->month_alpa,
                                                    'm_total' => $stu->month_total,
                                                ]) }})"
                                                class="neo-btn bg-white text-black text-xs font-bold px-2.5 py-1 hover:bg-slate-100 cursor-pointer">
                                            🔍 Detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-slate-500 font-semibold">
                                        Tidak ada data siswa yang cocok dengan filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 2: Pengajuan Izin / Sakit Khusus Siswa Kelas Binaan -->
        <div id="tabContent-izin" class="tab-pane hidden space-y-4">
            <div class="bg-white neo-box overflow-hidden">
                <div class="p-4 bg-slate-50 border-b-2 border-black flex items-center justify-between">
                    <div>
                        <h4 class="font-heading font-black text-sm text-black uppercase">Permohonan Izin / Sakit Siswa Kelas Binaan</h4>
                        <p class="text-[11px] font-semibold text-slate-500">Tinjau dan verifikasi pengajuan ketidakhadiran dari siswa atau wali murid.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-100 border-b-2 border-black text-xs font-black uppercase text-black">
                            <tr>
                                <th class="p-3 w-12 text-center border-r-2 border-black">No</th>
                                <th class="p-3 border-r-2 border-black">Siswa</th>
                                <th class="p-3 text-center border-r-2 border-black w-24">Jenis</th>
                                <th class="p-3 border-r-2 border-black min-w-[180px]">Rentang Tanggal</th>
                                <th class="p-3 border-r-2 border-black min-w-[200px]">Alasan & Bukti</th>
                                <th class="p-3 text-center border-r-2 border-black w-28">Status</th>
                                <th class="p-3 text-center min-w-[140px]">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-black">
                            @forelse($leaveRequests as $idx => $req)
                                <tr class="hover:bg-slate-50">
                                    <td class="p-3 text-center font-mono font-bold text-xs border-r-2 border-black">
                                        {{ $idx + 1 }}
                                    </td>
                                    <td class="p-3 border-r-2 border-black">
                                        <div class="font-bold text-black text-xs">{{ $req->student->user->name ?? '-' }}</div>
                                        <div class="text-[10px] font-mono text-slate-500">NIS: {{ $req->student->nis ?? '-' }}</div>
                                    </td>
                                    <td class="p-3 text-center border-r-2 border-black">
                                        <span class="neo-badge text-[10px] {{ $req->type === 'izin' ? 'bg-[#868E96] text-white' : 'bg-[#FFD43B] text-black' }}">
                                            {{ strtoupper($req->type) }}
                                        </span>
                                    </td>
                                    <td class="p-3 border-r-2 border-black text-xs font-mono">
                                        <div class="font-bold">{{ \Carbon\Carbon::parse($req->date_from)->translatedFormat('d M Y') }}</div>
                                        <div class="text-slate-500">s/d {{ \Carbon\Carbon::parse($req->date_to)->translatedFormat('d M Y') }}</div>
                                    </td>
                                    <td class="p-3 border-r-2 border-black text-xs">
                                        <div class="font-medium text-slate-800">{{ $req->reason }}</div>
                                        @if($req->attachment)
                                            <a href="{{ asset('storage/' . $req->attachment) }}" target="_blank" 
                                               class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-600 underline mt-1">
                                                <span>📎</span> Lihat Surat/Bukti
                                            </a>
                                        @endif
                                    </td>
                                    <td class="p-3 text-center border-r-2 border-black">
                                        <span class="neo-badge text-[10px]
                                            @if($req->status === 'approved') bg-[#20C997] text-white 
                                            @elseif($req->status === 'rejected') bg-[#FF6B6B] text-white 
                                            @else bg-amber-400 text-black @endif">
                                            {{ strtoupper($req->status) }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-center">
                                        @if($req->status === 'pending')
                                            <div class="flex items-center justify-center gap-1.5">
                                                <form action="{{ route('guru.leave-requests.approve', $req->id) }}" method="POST" onsubmit="return confirmAction(event, 'Setujui Pengajuan?', 'Presensi siswa akan otomatis dicatat sebagai {{ $req->type }} pada tanggal tersebut.')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="neo-btn bg-[#20C997] text-white text-[11px] font-bold px-2.5 py-1">
                                                        ✓ Setujui
                                                    </button>
                                                </form>
                                                <form action="{{ route('guru.leave-requests.reject', $req->id) }}" method="POST" onsubmit="return confirmAction(event, 'Tolak Pengajuan?', 'Pengajuan izin/sakit ini akan ditandai ditolak.')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="neo-btn bg-[#FF6B6B] text-white text-[11px] font-bold px-2.5 py-1">
                                                        ✕ Tolak
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-xs text-slate-400 font-semibold italic">Selesai Diverifikasi</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-slate-500 font-semibold">
                                        Belum ada data permohonan izin atau sakit untuk siswa di kelas binaan ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 3: Rekap Kehadiran Bulan Berjalan -->
        <div id="tabContent-rekap-bulan" class="tab-pane hidden space-y-4">
            <div class="bg-white neo-box overflow-hidden">
                <div class="p-4 bg-slate-50 border-b-2 border-black flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                    <div>
                        <h4 class="font-heading font-black text-sm text-black uppercase">
                            Akumulasi Kehadiran Siswa Bulan {{ \Carbon\Carbon::today()->translatedFormat('F Y') }}
                        </h4>
                        <p class="text-[11px] font-semibold text-slate-500">Rangkuman total status presensi siswa sepanjang bulan berjalan.</p>
                    </div>
                    <a href="{{ route('guru.reports.attendance', ['school_class_id' => $selectedClass->id]) }}" 
                       class="neo-btn bg-black text-white text-xs font-bold px-3 py-1.5 flex items-center gap-1.5">
                        Buka Laporan Penuh →
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-100 border-b-2 border-black text-xs font-black uppercase text-black">
                            <tr>
                                <th class="p-3 w-12 text-center border-r-2 border-black">No</th>
                                <th class="p-3 border-r-2 border-black">Siswa</th>
                                <th class="p-3 text-center border-r-2 border-black w-20 text-emerald-800">Hadir</th>
                                <th class="p-3 text-center border-r-2 border-black w-20 text-blue-800">Terlambat</th>
                                <th class="p-3 text-center border-r-2 border-black w-20 text-slate-700">Izin</th>
                                <th class="p-3 text-center border-r-2 border-black w-20 text-amber-800">Sakit</th>
                                <th class="p-3 text-center border-r-2 border-black w-20 text-rose-800">Alpa</th>
                                <th class="p-3 text-center border-r-2 border-black w-24">Total Logs</th>
                                <th class="p-3 text-center w-36">Tingkat Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-black">
                            @foreach($students as $idx => $st)
                                @php
                                    $present = $st->month_hadir + $st->month_terlambat;
                                    $rate = $st->month_total > 0 ? round(($present / $st->month_total) * 100, 1) : 0;
                                @endphp
                                <tr class="hover:bg-slate-50 font-mono text-xs">
                                    <td class="p-3 text-center font-bold border-r-2 border-black">{{ $idx + 1 }}</td>
                                    <td class="p-3 border-r-2 border-black font-sans">
                                        <div class="font-bold text-black">{{ $st->user->name ?? '-' }}</div>
                                        <div class="text-[10px] text-slate-500 font-mono">NIS: {{ $st->nis }}</div>
                                    </td>
                                    <td class="p-3 text-center border-r-2 border-black font-bold text-emerald-700">{{ $st->month_hadir }}</td>
                                    <td class="p-3 text-center border-r-2 border-black font-bold text-blue-700">{{ $st->month_terlambat }}</td>
                                    <td class="p-3 text-center border-r-2 border-black font-bold text-slate-700">{{ $st->month_izin }}</td>
                                    <td class="p-3 text-center border-r-2 border-black font-bold text-amber-700">{{ $st->month_sakit }}</td>
                                    <td class="p-3 text-center border-r-2 border-black font-bold text-rose-700">{{ $st->month_alpa }}</td>
                                    <td class="p-3 text-center border-r-2 border-black font-bold text-black">{{ $st->month_total }}</td>
                                    <td class="p-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <span class="font-bold {{ $rate >= 85 ? 'text-emerald-700' : ($rate >= 70 ? 'text-amber-700' : 'text-rose-700') }}">
                                                {{ $rate }}%
                                            </span>
                                            <span class="neo-badge text-[9px] px-1 py-0
                                                {{ $rate >= 85 ? 'bg-[#D3F9D8] text-emerald-950' : ($rate >= 70 ? 'bg-[#FFF3BF] text-amber-950' : 'bg-[#FFE3E3] text-rose-950') }}">
                                                {{ $rate >= 85 ? 'Baik' : ($rate >= 70 ? 'Cukup' : 'Perhatian') }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 4: Catatan Khusus Siswa -->
        <div id="tabContent-catatan" class="tab-pane hidden space-y-4">
            <div class="bg-white neo-box overflow-hidden">
                <div class="p-4 bg-slate-50 border-b-2 border-black flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div>
                        <h4 class="font-heading font-black text-sm text-black uppercase flex items-center gap-2">
                            <span>📌</span> Catatan Khusus & Pembinaan Siswa Binaan
                        </h4>
                        <p class="text-[11px] font-semibold text-slate-500">
                            Dokumentasikan perkembangan kedisiplinan, prestasi, bimbingan, kendala kesehatan, atau tindak lanjut dengan orang tua.
                        </p>
                    </div>
                    <button type="button" onclick="openAddNoteModal()" 
                            class="neo-btn bg-[#20C997] text-black text-xs font-black px-4 py-2 flex items-center gap-1.5 hover:bg-emerald-400 cursor-pointer">
                        <span>➕</span> Tambah Catatan Siswa
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-100 border-b-2 border-black text-xs font-black uppercase text-black">
                            <tr>
                                <th class="p-3 w-12 text-center border-r-2 border-black">No</th>
                                <th class="p-3 border-r-2 border-black w-28">Tanggal</th>
                                <th class="p-3 border-r-2 border-black min-w-[180px]">Siswa</th>
                                <th class="p-3 text-center border-r-2 border-black w-32">Kategori</th>
                                <th class="p-3 border-r-2 border-black min-w-[220px]">Catatan Khusus</th>
                                <th class="p-3 border-r-2 border-black min-w-[180px]">Tindak Lanjut</th>
                                <th class="p-3 text-center w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-black">
                            @forelse($studentNotes as $idx => $note)
                                <tr class="hover:bg-slate-50 text-xs">
                                    <td class="p-3 text-center font-mono font-bold border-r-2 border-black">
                                        {{ $idx + 1 }}
                                    </td>
                                    <td class="p-3 border-r-2 border-black font-mono font-bold text-slate-700">
                                        {{ \Carbon\Carbon::parse($note->date)->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="p-3 border-r-2 border-black">
                                        <div class="font-bold text-black">{{ $note->student->user->name ?? '-' }}</div>
                                        <div class="text-[10px] text-slate-500 font-mono">NIS: {{ $note->student->nis }}</div>
                                    </td>
                                    <td class="p-3 text-center border-r-2 border-black">
                                        <span class="neo-badge text-[10px] {{ $note->category_badge_class }}">
                                            {{ $note->category_label }}
                                        </span>
                                    </td>
                                    <td class="p-3 border-r-2 border-black">
                                        @if($note->title)
                                            <div class="font-black text-black text-xs mb-0.5">{{ $note->title }}</div>
                                        @endif
                                        <div class="text-slate-800 font-medium whitespace-pre-line">{{ $note->content }}</div>
                                        <div class="text-[10px] text-slate-400 font-semibold mt-1">
                                            Oleh: {{ $note->teacher?->user?->name ?? 'Wali Kelas' }}
                                        </div>
                                    </td>
                                    <td class="p-3 border-r-2 border-black">
                                        @if($note->follow_up)
                                            <div class="inline-flex items-center gap-1 text-slate-800 font-semibold bg-amber-50 border border-amber-300 px-2 py-1 rounded-xs">
                                                <span>👉</span> {{ $note->follow_up }}
                                            </div>
                                        @else
                                            <span class="text-slate-400 italic">Belum ada tindak lanjut</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-center">
                                        <form action="{{ route('guru.classes.binaan.notes.destroy', $note->id) }}" method="POST"
                                              onsubmit="return confirmAction(event, 'Hapus Catatan?', 'Catatan khusus untuk siswa ini akan dihapus permanen.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="neo-btn bg-[#FF6B6B] text-white text-[10px] font-bold px-2.5 py-1 hover:bg-rose-600 cursor-pointer">
                                                🗑 Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-slate-500 font-semibold">
                                        <div class="text-2xl mb-1">📝</div>
                                        <div>Belum ada catatan khusus yang dibuat untuk siswa di kelas ini.</div>
                                        <div class="text-xs text-slate-400 mt-1">Gunakan tombol <b>"Tambah Catatan Siswa"</b> di atas untuk mendokumentasikan catatan kedisiplinan, prestasi, atau pembinaan.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Modal Detail Siswa & Kontak Ortu -->
<div id="studentDetailModal" class="fixed inset-0 z-50 bg-black/60 hidden flex items-center justify-center p-4">
    <div class="bg-white neo-box-lg w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-200">
        <!-- Modal Header -->
        <div class="bg-[#5294FF] p-4 text-white border-b-2 border-black flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xl">👤</span>
                <h3 class="font-heading font-black text-base uppercase">Detail Siswa Kelas Binaan</h3>
            </div>
            <button type="button" onclick="closeStudentDetailModal()" class="font-black text-lg hover:opacity-75">✕</button>
        </div>

        <!-- Modal Body -->
        <div class="p-5 space-y-4 max-h-[80vh] overflow-y-auto">
            <!-- Profil Singkat Siswa -->
            <div class="bg-slate-50 border-2 border-black p-3 rounded-sm space-y-2">
                <div class="font-heading font-black text-base text-black" id="modal-student-name">Nama Siswa</div>
                <div class="grid grid-cols-2 gap-2 text-xs font-semibold text-slate-700">
                    <div>NIS: <span class="font-mono font-bold text-black" id="modal-student-nis">-</span></div>
                    <div>NISN: <span class="font-mono font-bold text-black" id="modal-student-nisn">-</span></div>
                    <div>Jenis Kelamin: <span class="font-bold text-black" id="modal-student-gender">-</span></div>
                    <div>Tanggal Lahir: <span class="font-bold text-black" id="modal-student-birth">-</span></div>
                    <div>Kelas: <span class="font-bold text-black" id="modal-student-class">-</span></div>
                    <div>No HP Siswa: <span class="font-mono font-bold text-black" id="modal-student-phone">-</span></div>
                </div>
            </div>

            <!-- Data Orang Tua & Tombol WhatsApp -->
            <div class="border-2 border-black p-3 rounded-sm bg-[#FFF9DB] space-y-2">
                <div class="text-xs font-black uppercase text-black flex items-center gap-1.5">
                    <span>👨‍👩‍👧‍👦</span> Kontak Orang Tua / Wali Murid:
                </div>
                <div class="text-xs text-slate-800 space-y-1">
                    <div>Nama: <b id="modal-parent-name">-</b> (<span id="modal-parent-relation">-</span>)</div>
                    <div>No Telepon / WhatsApp: <b class="font-mono" id="modal-parent-phone">-</b></div>
                </div>
                <div class="pt-2" id="modal-wa-container">
                    <a id="modal-wa-btn" href="#" target="_blank" 
                       class="neo-btn bg-[#20C997] text-white text-xs font-bold px-3 py-2 w-full flex items-center justify-center gap-2 hover:bg-emerald-400">
                        <span>💬</span> Buka Chat WhatsApp Langsung
                    </a>
                </div>
            </div>

            <!-- Akumulasi Presensi Bulan Ini -->
            <div class="border-2 border-black p-3 rounded-sm bg-white space-y-2">
                <div class="text-xs font-black uppercase text-black flex items-center gap-1.5">
                    <span>📊</span> Kehadiran Bulan Berjalan:
                </div>
                <div class="grid grid-cols-5 gap-1.5 text-center text-xs font-mono font-bold">
                    <div class="p-1.5 bg-[#D3F9D8] border border-black rounded-sm">
                        <div class="text-[10px] text-emerald-900">Hadir</div>
                        <div class="text-sm font-black text-emerald-950" id="modal-m-hadir">0</div>
                    </div>
                    <div class="p-1.5 bg-[#D0EBFF] border border-black rounded-sm">
                        <div class="text-[10px] text-blue-900">Telat</div>
                        <div class="text-sm font-black text-blue-950" id="modal-m-terlambat">0</div>
                    </div>
                    <div class="p-1.5 bg-[#E9ECEF] border border-black rounded-sm">
                        <div class="text-[10px] text-slate-700">Izin</div>
                        <div class="text-sm font-black text-slate-900" id="modal-m-izin">0</div>
                    </div>
                    <div class="p-1.5 bg-[#FFF3BF] border border-black rounded-sm">
                        <div class="text-[10px] text-amber-900">Sakit</div>
                        <div class="text-sm font-black text-amber-950" id="modal-m-sakit">0</div>
                    </div>
                    <div class="p-1.5 bg-[#FFE3E3] border border-black rounded-sm">
                        <div class="text-[10px] text-rose-900">Alpa</div>
                        <div class="text-sm font-black text-rose-950" id="modal-m-alpa">0</div>
                    </div>
                </div>
            </div>
            <!-- Tombol Cepat Tambah Catatan -->
            <div class="pt-2">
                <button type="button" onclick="openAddNoteForStudent()" 
                        class="neo-btn bg-[#20C997] text-black text-xs font-bold px-3 py-2 w-full flex items-center justify-center gap-2 hover:bg-emerald-400">
                    <span>📌</span> Buat Catatan Khusus Untuk Siswa Ini
                </button>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-3 bg-slate-100 border-t-2 border-black flex justify-end">
            <button type="button" onclick="closeStudentDetailModal()" class="neo-btn bg-black text-white text-xs font-bold px-4 py-2 hover:bg-slate-800">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- Modal Tambah Catatan Khusus Siswa -->
<div id="addNoteModal" class="fixed inset-0 z-50 bg-black/60 hidden flex items-center justify-center p-4">
    <div class="bg-white neo-box-lg w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-200">
        <!-- Modal Header -->
        <div class="bg-[#20C997] p-4 text-black border-b-2 border-black flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xl">📌</span>
                <h3 class="font-heading font-black text-base uppercase">Tambah Catatan Khusus Siswa</h3>
            </div>
            <button type="button" onclick="closeAddNoteModal()" class="font-black text-lg hover:opacity-75">✕</button>
        </div>

        <!-- Modal Body -->
        <form action="{{ route('guru.classes.binaan.notes.store') }}" method="POST" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-black uppercase text-black mb-1">Pilih Siswa *</label>
                <select name="student_id" id="note-student-id" required
                        class="w-full bg-white border-2 border-black px-3 py-2 text-xs font-bold text-black focus:outline-hidden">
                    <option value="">-- Pilih Siswa Kelas Binaan --</option>
                    @foreach($students as $st)
                        <option value="{{ $st->id }}">{{ $st->nis }} — {{ $st->user->name ?? '-' }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-black uppercase text-black mb-1">Tanggal *</label>
                    <input type="date" name="date" value="{{ $today }}" required
                           class="w-full bg-white border-2 border-black px-3 py-1.5 text-xs font-bold font-mono focus:outline-hidden">
                </div>
                <div>
                    <label class="block text-xs font-black uppercase text-black mb-1">Kategori *</label>
                    <select name="category" required
                            class="w-full bg-white border-2 border-black px-3 py-1.5 text-xs font-bold text-black focus:outline-hidden">
                        <option value="kedisiplinan">⚠️ Kedisiplinan / Pelanggaran</option>
                        <option value="prestasi">⭐ Prestasi & Keaktifan</option>
                        <option value="pembinaan">🤝 Pembinaan / Konseling (BK)</option>
                        <option value="kesehatan">🩺 Kesehatan / Kendala Fisik</option>
                        <option value="umum" selected>📝 Catatan Umum</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-black uppercase text-black mb-1">Judul / Topik Catatan (Opsional)</label>
                <input type="text" name="title" placeholder="Contoh: Sering Terlambat Upacara, Juara Lomba Matematika, dsb..."
                       class="w-full bg-white border-2 border-black px-3 py-1.5 text-xs font-bold text-black focus:outline-hidden">
            </div>

            <div>
                <label class="block text-xs font-black uppercase text-black mb-1">Isi Catatan Khusus *</label>
                <textarea name="content" rows="3" required placeholder="Tuliskan uraian detail kejadian, pembinaan, atau catatan wali kelas..."
                          class="w-full bg-white border-2 border-black px-3 py-2 text-xs font-medium text-black focus:outline-hidden"></textarea>
            </div>

            <div>
                <label class="block text-xs font-black uppercase text-black mb-1">Tindak Lanjut / Status Penanganan (Opsional)</label>
                <input type="text" name="follow_up" placeholder="Contoh: Sudah ditelepon ortu, Dijadwalkan konseling BK, dsb..."
                       class="w-full bg-white border-2 border-black px-3 py-1.5 text-xs font-bold text-black focus:outline-hidden">
            </div>

            <!-- Modal Footer -->
            <div class="pt-3 border-t-2 border-black/20 flex items-center justify-end gap-2">
                <button type="button" onclick="closeAddNoteModal()" class="neo-btn bg-slate-200 text-black text-xs font-bold px-4 py-2 hover:bg-slate-300">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#20C997] text-black text-xs font-black px-5 py-2 hover:bg-emerald-400">
                    💾 Simpan Catatan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentModalStudentId = null;

    // Tab Switcher
    function switchTab(tabId) {
        document.querySelectorAll('.tab-pane').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('bg-white', 'text-black', '-mb-[2px]', 'font-black');
            btn.classList.add('text-slate-600', 'border-transparent', 'font-bold');
        });

        const activeContent = document.getElementById('tabContent-' + tabId);
        const activeBtn = document.getElementById('tabBtn-' + tabId);

        if (activeContent) activeContent.classList.remove('hidden');
        if (activeBtn) {
            activeBtn.classList.remove('text-slate-600', 'border-transparent', 'font-bold');
            activeBtn.classList.add('bg-white', 'text-black', '-mb-[2px]', 'font-black');
        }
    }

    // Student Detail Modal
    function openStudentDetailModal(data) {
        currentModalStudentId = data.id;

        document.getElementById('modal-student-name').innerText = data.name;
        document.getElementById('modal-student-nis').innerText = data.nis;
        document.getElementById('modal-student-nisn').innerText = data.nisn;
        document.getElementById('modal-student-gender').innerText = data.gender;
        document.getElementById('modal-student-birth').innerText = data.birth_date;
        document.getElementById('modal-student-class').innerText = data.class_name;
        document.getElementById('modal-student-phone').innerText = data.phone;

        document.getElementById('modal-parent-name').innerText = data.parent_name;
        document.getElementById('modal-parent-relation').innerText = data.parent_relation;
        document.getElementById('modal-parent-phone').innerText = data.parent_phone || '-';

        const waContainer = document.getElementById('modal-wa-container');
        const waBtn = document.getElementById('modal-wa-btn');
        if (data.wa_url) {
            waBtn.href = data.wa_url;
            waContainer.classList.remove('hidden');
        } else {
            waContainer.classList.add('hidden');
        }

        document.getElementById('modal-m-hadir').innerText = data.m_hadir;
        document.getElementById('modal-m-terlambat').innerText = data.m_terlambat;
        document.getElementById('modal-m-izin').innerText = data.m_izin;
        document.getElementById('modal-m-sakit').innerText = data.m_sakit;
        document.getElementById('modal-m-alpa').innerText = data.m_alpa;

        document.getElementById('studentDetailModal').classList.remove('hidden');
    }

    function closeStudentDetailModal() {
        document.getElementById('studentDetailModal').classList.add('hidden');
    }

    // Modal Tambah Catatan Siswa
    function openAddNoteModal(studentId = null) {
        const select = document.getElementById('note-student-id');
        if (select && studentId) {
            select.value = studentId;
        }
        document.getElementById('addNoteModal').classList.remove('hidden');
    }

    function closeAddNoteModal() {
        document.getElementById('addNoteModal').classList.add('hidden');
    }

    function openAddNoteForStudent() {
        closeStudentDetailModal();
        if (currentModalStudentId) {
            openAddNoteModal(currentModalStudentId);
        } else {
            openAddNoteModal();
        }
    }

    // Konfirmasi SweetAlert2 untuk Aksi Approve / Reject / Delete
    function confirmAction(e, title, text) {
        e.preventDefault();
        const form = e.target;
        Swal.fire({
            title: title,
            text: text,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#20C997',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
        return false;
    }
</script>
@endpush
