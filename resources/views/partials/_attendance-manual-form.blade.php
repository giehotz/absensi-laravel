@php
    $role = Auth::user()->role;
    $submitRoute = $role === 'admin' ? route('admin.attendances.manual.store') : route('guru.attendance.manual.store');
    $filterRoute = $role === 'admin' ? route('admin.attendances.manual') : route('guru.attendance.manual');
    $templateRoute = $role === 'admin' ? route('admin.attendances.template') : route('guru.attendance.template');
    $uploadRoute = $role === 'admin' ? route('admin.attendances.upload') : route('guru.attendance.upload');
    $batchesRoute = $role === 'admin' ? route('admin.attendances.batches') : route('guru.attendance.batches');
    $monthlyFillRoute = $role === 'admin' ? route('admin.attendances.monthly-fill') : '#';

    $carbonDate = \Carbon\Carbon::parse($date)->locale('id');
    $prevDate = $carbonDate->copy()->subDay()->toDateString();
    $nextDate = $carbonDate->copy()->addDay()->toDateString();

    if ($carbonDate->isToday()) {
        $dayLabel = 'Hari ini';
    } elseif ($carbonDate->isYesterday()) {
        $dayLabel = 'Kemarin';
    } elseif ($carbonDate->isTomorrow()) {
        $dayLabel = 'Besok';
    } else {
        $dayLabel = $carbonDate->translatedFormat('l');
    }

    $formattedDateIndo = $carbonDate->translatedFormat('l, d F Y');
@endphp

<style>
    /* Color coding untuk CELL Status Kehadiran */
    .status-cell-hadir {
        background-color: #D3F9D8 !important; /* HIJAU soft */
    }
    .status-cell-terlambat {
        background-color: #D0EBFF !important; /* BIRU soft */
    }
    .status-cell-izin {
        background-color: #E9ECEF !important; /* ABU-ABU soft */
    }
    .status-cell-sakit {
        background-color: #FFF3BF !important; /* KUNING soft */
    }
    .status-cell-alpa {
        background-color: #FFE3E3 !important; /* MERAH soft */
    }
</style>

<div class="space-y-6">
    <!-- Filter Card: Tanggal & Kelas -->
    <div class="bg-white neo-box p-5">
        <form action="{{ $filterRoute }}" method="GET" class="flex flex-col md:flex-row items-start md:items-end gap-4 justify-between">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full md:w-auto">
                <!-- Navigasi Tanggal Presensi (Neo-Brutalism) -->
                <div class="w-full sm:w-auto">
                    <label class="block text-xs font-black uppercase text-black mb-1.5 flex items-center gap-1.5">
                        <span>📅</span> Tanggal Presensi
                    </label>

                    <div class="flex items-center gap-2 bg-slate-50 border-2 border-black px-2 py-1.5 neo-box-sm relative min-w-[280px] sm:min-w-[340px] justify-between">
                        <!-- Tombol Hari Sebelumnya (<) -->
                        <button type="button" 
                                onclick="setAttendanceFilterDate('{{ $prevDate }}')"
                                title="Hari Sebelumnya: {{ \Carbon\Carbon::parse($prevDate)->translatedFormat('d M Y') }}"
                                class="neo-btn bg-white hover:bg-[#FFD43B] text-black border-2 border-black w-8 h-8 flex items-center justify-center font-black text-xs shadow-[1.5px_1.5px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 cursor-pointer shrink-0 transition-colors">
                            ◀
                        </button>

                        <!-- Label & Tanggal di Tengah (Dapat diklik untuk membuka datepicker) -->
                        <div class="text-center px-2 flex-1 cursor-pointer select-none group" 
                             onclick="triggerAttendanceDatePicker()" 
                             title="Klik untuk memilih tanggal melalui kalender">
                            <div class="text-[11px] font-black text-slate-700 uppercase tracking-wider leading-tight group-hover:text-black">
                                {{ $dayLabel }}
                            </div>
                            <div class="text-xs sm:text-[13px] font-black text-[#2b8a3e] font-mono leading-tight group-hover:underline">
                                {{ $formattedDateIndo }}
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 shrink-0">
                            <!-- Tombol Hari Selanjutnya (>) -->
                            <button type="button" 
                                    onclick="setAttendanceFilterDate('{{ $nextDate }}')"
                                    title="Hari Selanjutnya: {{ \Carbon\Carbon::parse($nextDate)->translatedFormat('d M Y') }}"
                                    class="neo-btn bg-white hover:bg-[#FFD43B] text-black border-2 border-black w-8 h-8 flex items-center justify-center font-black text-xs shadow-[1.5px_1.5px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 cursor-pointer transition-colors">
                                ▶
                            </button>

                            <!-- Tombol Kalender (Datepicker Picker Icon) -->
                            <button type="button" 
                                    onclick="triggerAttendanceDatePicker()"
                                    title="Pilih Tanggal Kalender"
                                    class="neo-btn bg-[#FFD43B] hover:bg-[#ffe066] text-black border-2 border-black w-8 h-8 flex items-center justify-center font-black text-sm shadow-[1.5px_1.5px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 cursor-pointer transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                            </button>
                        </div>

                        <!-- Input Native Date (Tersembunyi tapi aktif) -->
                        <input type="date" 
                               id="attendanceDateInput" 
                               name="date" 
                               value="{{ $date }}" 
                               onchange="this.form.submit()"
                               class="absolute opacity-0 pointer-events-none w-0 h-0">
                    </div>
                </div>

                <!-- Pilihan Kelas -->
                <div class="w-full sm:w-auto">
                    <label class="block text-xs font-black uppercase text-black mb-1.5 flex items-center gap-1.5">
                        <span>🏫</span> Pilih Kelas
                    </label>
                    <select name="school_class_id" onchange="this.form.submit()"
                            class="w-full sm:w-56 bg-white border-2 border-black px-3 py-2 text-xs font-bold text-black focus:outline-hidden focus:ring-2 focus:ring-black">
                        @forelse($classes as $c)
                            <option value="{{ $c->id }}" {{ $selectedClassId == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->level }})
                            </option>
                        @empty
                            <option value="">Tidak ada kelas tersedia</option>
                        @endforelse
                    </select>
                </div>

                <div class="pt-2 sm:pt-6">
                    <button type="submit" class="neo-btn bg-black text-white text-xs px-4 py-2 font-bold cursor-pointer hover:bg-slate-800">
                        Muat Data
                    </button>
                </div>
            </div>

            @if($selectedClass)
                <div class="flex items-center gap-2 pt-2 md:pt-0 flex-wrap justify-end">
                    <span class="text-xs font-mono font-bold bg-[#E7F5FF] text-blue-900 border-2 border-black px-3 py-1.5 rounded-sm">
                        {{ $students->count() }} Siswa Terdaftar
                    </span>
                    @if($selectedClass->homeroomTeacher)
                        <span class="text-xs font-bold bg-[#D3F9D8] text-emerald-900 border-2 border-black px-3 py-1.5 rounded-sm hidden lg:inline-block">
                            Wali: {{ $selectedClass->homeroomTeacher->user->name ?? '-' }}
                        </span>
                    @endif

                    <!-- Tombol Aksi: Icon-Only dengan Tooltip Neo-Brutalism -->
                    <div class="flex items-center gap-1.5">
                        <!-- Tooltip 1: Download Template Excel -->
                        <div class="relative group">
                            <a href="{{ $templateRoute }}?school_class_id={{ $selectedClassId }}&date={{ $date }}" 
                               title="Download Template Excel"
                               class="neo-btn bg-[#FFF3BF] hover:bg-[#FFE066] text-black w-8 h-8 sm:w-8.5 sm:h-8.5 flex items-center justify-center font-bold border-2 border-black shadow-[2px_2px_0px_0px_#000] transition-transform active:translate-x-0.5 active:translate-y-0.5 text-sm sm:text-base">
                                📥
                            </a>
                            <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 hidden group-hover:flex flex-col items-center pointer-events-none z-30 whitespace-nowrap">
                                <span class="bg-black text-white text-[10px] font-black px-2 py-1 border border-black shadow-[2px_2px_0px_0px_#FFD43B]">
                                    Download Template Excel
                                </span>
                                <div class="w-1.5 h-1.5 bg-black rotate-45 -mt-1"></div>
                            </div>
                        </div>

                        <!-- Tooltip 2: Upload Presensi Excel -->
                        <div class="relative group">
                            <button type="button" 
                                    onclick="openAttendanceUploadModal()" 
                                    title="Upload Presensi Excel"
                                    class="neo-btn bg-[#5294FF] hover:bg-[#3b82f6] text-white w-8 h-8 sm:w-8.5 sm:h-8.5 flex items-center justify-center font-bold border-2 border-black shadow-[2px_2px_0px_0px_#000] transition-transform active:translate-x-0.5 active:translate-y-0.5 cursor-pointer text-sm sm:text-base">
                                📤
                            </button>
                            <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 hidden group-hover:flex flex-col items-center pointer-events-none z-30 whitespace-nowrap">
                                <span class="bg-black text-white text-[10px] font-black px-2 py-1 border border-black shadow-[2px_2px_0px_0px_#5294FF]">
                                    Upload Presensi Excel
                                </span>
                                <div class="w-1.5 h-1.5 bg-black rotate-45 -mt-1"></div>
                            </div>
                        </div>

                        <!-- Tooltip 3: Riwayat Upload Presensi -->
                        <div class="relative group">
                            <button type="button" 
                                    onclick="openAttendanceBatchesModal()" 
                                    title="Riwayat Upload Presensi"
                                    class="neo-btn bg-white hover:bg-slate-100 text-black w-8 h-8 sm:w-8.5 sm:h-8.5 flex items-center justify-center font-bold border-2 border-black shadow-[2px_2px_0px_0px_#000] transition-transform active:translate-x-0.5 active:translate-y-0.5 cursor-pointer text-sm sm:text-base">
                                📋
                            </button>
                            <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 hidden group-hover:flex flex-col items-center pointer-events-none z-30 whitespace-nowrap">
                                <span class="bg-black text-white text-[10px] font-black px-2 py-1 border border-black shadow-[2px_2px_0px_0px_#D3F9D8]">
                                    Riwayat Upload
                                </span>
                                <div class="w-1.5 h-1.5 bg-black rotate-45 -mt-1"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </form>
    </div>

    @if(!$selectedClassId || $students->isEmpty())
        <div class="bg-white neo-box p-12 text-center text-slate-500 space-y-3">
            <div class="text-4xl">👥</div>
            <div class="font-heading font-bold text-lg text-black">
                {{ !$selectedClassId ? 'Pilih Kelas Terlebih Dahulu' : 'Tidak Ada Siswa Terdaftar di Kelas Ini' }}
            </div>
            <p class="text-xs text-slate-500 max-w-md mx-auto font-medium">
                {{ !$selectedClassId ? 'Silakan pilih kelas dari dropdown di atas untuk memulai pencatatan presensi manual.' : 'Kelas yang dipilih belum memiliki data siswa terdaftar. Silakan hubungi Administrator.' }}
            </p>
        </div>
    @else
        {{-- Banner Peringatan Hari Libur Nasional / Cuti Bersama (Neo-Brutalism) --}}
        @if(isset($holiday) && $holiday && $holiday->is_active)
            <div class="bg-[#FFF4E6] border-2 border-black p-4 sm:p-5 neo-box flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-[4px_4px_0px_0px_#000]">
                <div class="flex items-start gap-3">
                    <span class="text-2xl sm:text-3xl shrink-0">🏖️</span>
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-heading font-black text-xs sm:text-sm text-black tracking-wide uppercase px-2 py-0.5 bg-[#FF6B6B] text-white border border-black shadow-[1px_1px_0px_0px_#000]">
                                HARI LIBUR: {{ strtoupper($holiday->name) }}
                            </span>
                            <span class="text-xs font-mono font-bold text-amber-900 bg-white/80 border border-black/30 px-2 py-0.5">
                                {{ $holiday->is_cuti_bersama ? '📌 Cuti Bersama Resmi' : ($holiday->is_national ? '🇮🇩 Libur Nasional SKB 3 Menteri' : '🏫 Libur Internal Sekolah') }}
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm text-amber-950 font-medium leading-relaxed">
                            Tanggal <strong>{{ $formattedDateIndo }}</strong> terdaftar sebagai hari libur. KBM dan presensi reguler tidak diwajibkan.
                        </p>
                        <p class="text-[11px] text-amber-900 font-semibold italic">
                            * Anda tetap dapat mencatat dan menyimpan presensi jika sekolah mengadakan agenda ekstrakurikuler, pembinaan, atau perayaan khusus.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Banner Status Pengisian Presensi Kelas (Neo-Brutalism) --}}
        @if(isset($attendanceMeta) && $attendanceMeta)
            @if($attendanceMeta['is_complete'])
                {{-- STATUS 1: LENGKAP DIISI (HIJAU NEO-BRUTALISM) --}}
                <div class="bg-[#D3F9D8] border-2 border-black p-4 sm:p-5 neo-box flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <span class="text-2xl sm:text-3xl shrink-0">✅</span>
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-heading font-black text-xs sm:text-sm text-black tracking-wide uppercase px-2 py-0.5 bg-black text-[#D3F9D8] rounded-xs">
                                    PRESENSI SUDAH DIISI
                                </span>
                                <span class="text-xs font-mono font-bold text-slate-800">
                                    ({{ $attendanceMeta['recorded_count'] }}/{{ $attendanceMeta['total_students'] }} Siswa)
                                </span>
                            </div>
                            <p class="text-xs sm:text-sm text-slate-900 font-semibold leading-relaxed">
                                Presensi kelas <strong>{{ $selectedClass->name }}</strong> untuk tanggal <strong>{{ $formattedDateIndo }}</strong> {{ $attendanceMeta['is_updated'] ? 'terakhir diperbarui' : 'telah dicatat' }} oleh <strong>{{ $attendanceMeta['recorder_name'] }}</strong> pada pukul <strong>{{ $attendanceMeta['recorded_at'] ? \Carbon\Carbon::parse($attendanceMeta['recorded_at'])->timezone(config('app.timezone', 'Asia/Jakarta'))->format('H:i') . ' WIB' : '-' }}</strong>.
                            </p>
                            <div class="flex flex-wrap items-center gap-2 pt-0.5 text-[11px] font-bold font-mono text-slate-800">
                                <span class="text-emerald-800 font-black">Status:</span>
                                <span class="bg-white/80 border border-black/30 px-1.5 py-0.5 rounded-xs text-emerald-900">{{ $attendanceMeta['status_counts']['hadir'] }} Hadir</span>
                                @if($attendanceMeta['status_counts']['terlambat'] > 0)
                                    <span class="bg-white/80 border border-black/30 px-1.5 py-0.5 rounded-xs text-blue-900">{{ $attendanceMeta['status_counts']['terlambat'] }} Terlambat</span>
                                @endif
                                @if($attendanceMeta['status_counts']['sakit'] > 0)
                                    <span class="bg-white/80 border border-black/30 px-1.5 py-0.5 rounded-xs text-amber-900">{{ $attendanceMeta['status_counts']['sakit'] }} Sakit</span>
                                @endif
                                @if($attendanceMeta['status_counts']['izin'] > 0)
                                    <span class="bg-white/80 border border-black/30 px-1.5 py-0.5 rounded-xs text-slate-800">{{ $attendanceMeta['status_counts']['izin'] }} Izin</span>
                                @endif
                                @if($attendanceMeta['status_counts']['alpa'] > 0)
                                    <span class="bg-white/80 border border-black/30 px-1.5 py-0.5 rounded-xs text-rose-900">{{ $attendanceMeta['status_counts']['alpa'] }} Alpa</span>
                                @endif
                            </div>
                            <p class="text-[11px] text-slate-600 font-medium italic pt-0.5">
                                * Catatan: Anda tetap dapat memperbarui status siswa jika ada perubahan di tengah jam pelajaran.
                            </p>
                        </div>
                    </div>
                </div>
            @elseif($attendanceMeta['is_partial'])
                {{-- STATUS 2: SEBAGIAN TERISI (KUNING / BIRU NEO-BRUTALISM) --}}
                <div class="bg-[#FFF3BF] border-2 border-black p-4 sm:p-5 neo-box flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <span class="text-2xl sm:text-3xl shrink-0">ℹ️</span>
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-heading font-black text-xs sm:text-sm text-black tracking-wide uppercase px-2 py-0.5 bg-black text-[#FFF3BF] rounded-xs">
                                    PRESENSI SEBAGIAN TERISI
                                </span>
                                <span class="text-xs font-mono font-bold text-slate-800">
                                    ({{ $attendanceMeta['recorded_count'] }} dari {{ $attendanceMeta['total_students'] }} Siswa Terdata)
                                </span>
                            </div>
                            <p class="text-xs sm:text-sm text-slate-900 font-semibold leading-relaxed">
                                Baru sebagian siswa pada kelas <strong>{{ $selectedClass->name }}</strong> yang tercatat presensinya untuk tanggal <strong>{{ $formattedDateIndo }}</strong> (misal dari persetujuan surat izin/sakit). {{ $attendanceMeta['is_updated'] ? 'Terakhir diperbarui' : 'Dicatat' }} oleh <strong>{{ $attendanceMeta['recorder_name'] }}</strong> pada pukul <strong>{{ $attendanceMeta['recorded_at'] ? \Carbon\Carbon::parse($attendanceMeta['recorded_at'])->timezone(config('app.timezone', 'Asia/Jakarta'))->format('H:i') . ' WIB' : '-' }}</strong>.
                            </p>
                            <p class="text-[11px] text-amber-900 font-bold pt-0.5">
                                ⚠️ Silakan lengkapi presensi seluruh siswa kelas ini kemudian klik Simpan Presensi.
                            </p>
                        </div>
                    </div>
                </div>
            @else
                {{-- STATUS 3: BELUM DIISI (KUNING SOFT / MERAH SOFT NEO-BRUTALISM) --}}
                <div class="bg-[#FFF9DB] border-2 border-black p-4 sm:p-5 neo-box flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <span class="text-2xl sm:text-3xl shrink-0">⚠️</span>
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-heading font-black text-xs sm:text-sm text-black tracking-wide uppercase px-2 py-0.5 bg-[#FF6B6B] text-white border border-black rounded-xs">
                                    PRESENSI BELUM DIISI
                                </span>
                            </div>
                            <p class="text-xs sm:text-sm text-slate-900 font-semibold leading-relaxed">
                                Presensi untuk kelas <strong>{{ $selectedClass->name }}</strong> pada tanggal <strong>{{ $formattedDateIndo }}</strong> belum dicatat oleh siapapun.
                            </p>
                            <p class="text-[11px] text-slate-600 font-medium">
                                Silakan gunakan tombol aksi cepat (misal: <em>✓ Semua Hadir</em>) atau pilih status per-siswa di bawah, lalu klik <strong>Simpan Presensi</strong>.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        <!-- Main Form Presensi Manual -->
        <form id="attendanceManualForm" action="{{ $submitRoute }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">
            <input type="hidden" name="school_class_id" value="{{ $selectedClassId }}">

            <!-- Dual Bulk Action Toolbar -->
            <div class="bg-[#FFF9DB] neo-box p-4 sm:p-5 space-y-4">
                <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                    <!-- Global Set Semua -->
                    <div class="space-y-1.5 w-full lg:w-auto">
                        <div class="text-xs font-black uppercase text-black flex items-center gap-1.5">
                            <span>⚡</span> Aksi Cepat (Terapkan ke SEMUA siswa):
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" onclick="setAllStatus('hadir')" 
                                    class="neo-btn bg-[#20C997] text-white text-xs font-bold px-3 py-1.5 cursor-pointer hover:opacity-90">
                                ✓ Semua Hadir
                            </button>
                            <button type="button" onclick="setAllStatus('terlambat')" 
                                    class="neo-btn bg-[#339AF0] text-white text-xs font-bold px-3 py-1.5 cursor-pointer hover:opacity-90">
                                ⏰ Semua Terlambat
                            </button>
                            <button type="button" onclick="setAllStatus('izin')" 
                                    class="neo-btn bg-[#868E96] text-white text-xs font-bold px-3 py-1.5 cursor-pointer hover:opacity-90">
                                📝 Semua Izin
                            </button>
                            <button type="button" onclick="setAllStatus('sakit')" 
                                    class="neo-btn bg-[#FFD43B] text-black text-xs font-bold px-3 py-1.5 cursor-pointer hover:opacity-90">
                                🩺 Semua Sakit
                            </button>
                            <button type="button" onclick="setAllStatus('alpa')" 
                                    class="neo-btn bg-[#FF6B6B] text-white text-xs font-bold px-3 py-1.5 cursor-pointer hover:opacity-90">
                                ✕ Semua Alpa
                            </button>
                            @if($role === 'admin')
                                <button type="button" onclick="openMonthlyFillModal('all')" 
                                        class="neo-btn bg-[#FFD43B] hover:bg-[#ffe066] text-black text-xs font-black px-3 py-1.5 cursor-pointer flex items-center gap-1.5 border-2 border-black shadow-[2px_2px_0px_0px_#000] transition-transform active:translate-x-0.5 active:translate-y-0.5">
                                    <span>📅</span> Isi 1 Bulan Penuh
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Live Counter Pills -->
                    <div class="flex flex-wrap items-center gap-2 bg-white border-2 border-black p-2 rounded-sm text-xs font-bold font-mono">
                        <span class="text-emerald-700 flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#20C997] border border-black inline-block"></span> H: <span id="counter-hadir">0</span></span>
                        <span class="text-slate-300">|</span>
                        <span class="text-blue-700 flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#339AF0] border border-black inline-block"></span> T: <span id="counter-terlambat">0</span></span>
                        <span class="text-slate-300">|</span>
                        <span class="text-slate-600 flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#868E96] border border-black inline-block"></span> I: <span id="counter-izin">0</span></span>
                        <span class="text-slate-300">|</span>
                        <span class="text-amber-700 flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#FFD43B] border border-black inline-block"></span> S: <span id="counter-sakit">0</span></span>
                        <span class="text-slate-300">|</span>
                        <span class="text-rose-700 flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#FF6B6B] border border-black inline-block"></span> A: <span id="counter-alpa">0</span></span>
                    </div>
                </div>

                <!-- Selective Bulk Action Bar (muncul jika ada siswa yang dicentang) -->
                <div id="selectionActionBar" class="pt-3 border-t-2 border-black/10 flex flex-wrap items-center justify-between gap-3 bg-white/70 p-2.5 rounded-sm border border-black/20">
                    <div class="flex items-center gap-2 text-xs font-black text-black">
                        <span class="w-2 h-2 rounded-full bg-blue-600 animate-ping"></span>
                        <span id="selectedCountText">0 Siswa Dipilih</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="text-[11px] font-bold text-slate-600 mr-1">Set Terpilih:</span>
                        <button type="button" onclick="setSelectedStatus('hadir')" 
                                class="neo-btn bg-[#20C997] text-white text-[11px] font-bold px-2.5 py-1 cursor-pointer">
                            Hadir
                        </button>
                        <button type="button" onclick="setSelectedStatus('terlambat')" 
                                class="neo-btn bg-[#339AF0] text-white text-[11px] font-bold px-2.5 py-1 cursor-pointer">
                            Terlambat
                        </button>
                        <button type="button" onclick="setSelectedStatus('izin')" 
                                class="neo-btn bg-[#868E96] text-white text-[11px] font-bold px-2.5 py-1 cursor-pointer">
                            Izin
                        </button>
                        <button type="button" onclick="setSelectedStatus('sakit')" 
                                class="neo-btn bg-[#FFD43B] text-black text-[11px] font-bold px-2.5 py-1 cursor-pointer">
                            Sakit
                        </button>
                        <button type="button" onclick="setSelectedStatus('alpa')" 
                                class="neo-btn bg-[#FF6B6B] text-white text-[11px] font-bold px-2.5 py-1 cursor-pointer">
                            Alpa
                        </button>
                        @if($role === 'admin')
                            <div class="h-4 w-px bg-slate-400 mx-1"></div>
                            <button type="button" onclick="openMonthlyFillModal('selected')" 
                                    class="neo-btn bg-[#FFD43B] hover:bg-[#ffe066] text-black text-[11px] font-black px-2.5 py-1 cursor-pointer flex items-center gap-1 border-2 border-black shadow-[1.5px_1.5px_0px_0px_#000] transition-transform active:translate-x-0.5 active:translate-y-0.5">
                                <span>📅</span> Isi 1 Bulan
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tabel Siswa -->
            <div class="bg-white neo-box overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-100 border-b-2 border-black text-xs font-black uppercase text-black">
                            <tr>
                                <th class="p-3 w-10 text-center border-r-2 border-black">
                                    <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)"
                                           class="w-4 h-4 cursor-pointer accent-black rounded-sm border-2 border-black">
                                </th>
                                <th class="p-3 w-12 text-center border-r-2 border-black">No</th>
                                <th class="p-3 border-r-2 border-black">Siswa</th>
                                <th class="p-3 border-r-2 border-black text-center min-w-[180px]">
                                    <div>Status Kehadiran</div>
                                    <div class="flex items-center justify-center gap-1.5 mt-0.5 text-[9px] font-bold text-slate-500 uppercase tracking-tight">
                                        <span class="text-emerald-700" title="Hadir">✓ Hadir</span>
                                        <span>•</span>
                                        <span class="text-blue-700" title="Terlambat">⏱️ Tlt</span>
                                        <span>•</span>
                                        <span class="text-slate-600" title="Izin">📝 Izin</span>
                                        <span>•</span>
                                        <span class="text-amber-700" title="Sakit">🩹 Skt</span>
                                        <span>•</span>
                                        <span class="text-rose-700" title="Alpa">✕ Alpa</span>
                                    </div>
                                </th>
                                <th class="p-3 min-w-[180px]">Catatan / Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-black">
                            @foreach($students as $index => $stu)
                                @php
                                    $existingAtt = $stu->attendances->first();
                                    $currentStatus = $existingAtt ? $existingAtt->status : 'hadir';
                                    $currentNotes = $existingAtt ? $existingAtt->notes : '';
                                    $cellClass = match($currentStatus) {
                                        'hadir' => 'status-cell-hadir',
                                        'terlambat' => 'status-cell-terlambat',
                                        'izin' => 'status-cell-izin',
                                        'sakit' => 'status-cell-sakit',
                                        'alpa' => 'status-cell-alpa',
                                        default => 'status-cell-hadir'
                                    };
                                @endphp
                                <tr class="student-row hover:bg-slate-50 transition-colors" data-student-id="{{ $stu->id }}">
                                    <!-- Checkbox Baris -->
                                    <td class="p-3 text-center border-r-2 border-black bg-slate-50/50">
                                        <input type="checkbox" class="row-checkbox w-4 h-4 cursor-pointer accent-black rounded-sm border-2 border-black" 
                                               value="{{ $stu->id }}" onchange="updateSelectionBar()">
                                    </td>

                                    <!-- No -->
                                    <td class="p-3 text-center font-mono font-bold text-xs border-r-2 border-black">
                                        {{ $index + 1 }}
                                    </td>

                                    <!-- Identitas Siswa -->
                                    <td class="p-3 border-r-2 border-black">
                                        <input type="hidden" name="attendances[{{ $index }}][student_id]" value="{{ $stu->id }}">
                                        <div class="font-black text-black text-sm">{{ $stu->user->name ?? '-' }}</div>
                                        <div class="flex items-center gap-2 mt-0.5 text-xs text-slate-500 font-mono">
                                            <span>NIS: {{ $stu->nis }}</span>
                                            <span>•</span>
                                            <span class="font-bold text-slate-700">{{ $stu->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                                        </div>
                                    </td>

                                    <!-- Pilihan Radio Icon Status (Cell Berwarna Sesuai Status) -->
                                    <td class="status-cell p-2.5 border-r-2 border-black transition-colors duration-200 {{ $cellClass }}">
                                        <div class="flex items-center justify-center gap-1 sm:gap-1.5">
                                            <!-- Hadir (Hijau) -->
                                            <label class="status-pill cursor-pointer relative group" title="Hadir (Tepat Waktu)">
                                                <input type="radio" name="attendances[{{ $index }}][status]" value="hadir" 
                                                       {{ $currentStatus === 'hadir' ? 'checked' : '' }}
                                                       onchange="updateCounters(); updateCellColor(this);"
                                                       class="sr-only peer">
                                                <span class="w-8 h-8 flex items-center justify-center border-2 border-black rounded-sm transition-all
                                                             peer-checked:bg-[#20C997] peer-checked:text-white peer-checked:shadow-[2px_2px_0px_0px_#000] peer-checked:scale-105
                                                             bg-white text-slate-700 hover:bg-slate-100 hover:scale-105">
                                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </span>
                                                <!-- Tooltip Popup -->
                                                <span class="pointer-events-none absolute -top-8 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-all duration-150 z-30 whitespace-nowrap bg-black text-white text-[10px] font-black px-2 py-0.5 rounded shadow-[2px_2px_0px_#000] border border-black scale-90 group-hover:scale-100">
                                                    Hadir
                                                    <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-black"></span>
                                                </span>
                                            </label>

                                            <!-- Terlambat (Biru) -->
                                            <label class="status-pill cursor-pointer relative group" title="Terlambat">
                                                <input type="radio" name="attendances[{{ $index }}][status]" value="terlambat" 
                                                       {{ $currentStatus === 'terlambat' ? 'checked' : '' }}
                                                       onchange="updateCounters(); updateCellColor(this);"
                                                       class="sr-only peer">
                                                <span class="w-8 h-8 flex items-center justify-center border-2 border-black rounded-sm transition-all
                                                             peer-checked:bg-[#339AF0] peer-checked:text-white peer-checked:shadow-[2px_2px_0px_0px_#000] peer-checked:scale-105
                                                             bg-white text-slate-700 hover:bg-slate-100 hover:scale-105">
                                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </span>
                                                <!-- Tooltip Popup -->
                                                <span class="pointer-events-none absolute -top-8 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-all duration-150 z-30 whitespace-nowrap bg-black text-white text-[10px] font-black px-2 py-0.5 rounded shadow-[2px_2px_0px_#000] border border-black scale-90 group-hover:scale-100">
                                                    Terlambat
                                                    <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-black"></span>
                                                </span>
                                            </label>

                                            <!-- Izin (Abu-abu) -->
                                            <label class="status-pill cursor-pointer relative group" title="Izin">
                                                <input type="radio" name="attendances[{{ $index }}][status]" value="izin" 
                                                       {{ $currentStatus === 'izin' ? 'checked' : '' }}
                                                       onchange="updateCounters(); updateCellColor(this);"
                                                       class="sr-only peer">
                                                <span class="w-8 h-8 flex items-center justify-center border-2 border-black rounded-sm transition-all
                                                             peer-checked:bg-[#868E96] peer-checked:text-white peer-checked:shadow-[2px_2px_0px_0px_#000] peer-checked:scale-105
                                                             bg-white text-slate-700 hover:bg-slate-100 hover:scale-105">
                                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                </span>
                                                <!-- Tooltip Popup -->
                                                <span class="pointer-events-none absolute -top-8 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-all duration-150 z-30 whitespace-nowrap bg-black text-white text-[10px] font-black px-2 py-0.5 rounded shadow-[2px_2px_0px_#000] border border-black scale-90 group-hover:scale-100">
                                                    Izin
                                                    <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-black"></span>
                                                </span>
                                            </label>

                                            <!-- Sakit (Kuning) -->
                                            <label class="status-pill cursor-pointer relative group" title="Sakit">
                                                <input type="radio" name="attendances[{{ $index }}][status]" value="sakit" 
                                                       {{ $currentStatus === 'sakit' ? 'checked' : '' }}
                                                       onchange="updateCounters(); updateCellColor(this);"
                                                       class="sr-only peer">
                                                <span class="w-8 h-8 flex items-center justify-center border-2 border-black rounded-sm transition-all
                                                             peer-checked:bg-[#FFD43B] peer-checked:text-black peer-checked:shadow-[2px_2px_0px_0px_#000] peer-checked:scale-105
                                                             bg-white text-slate-700 hover:bg-slate-100 hover:scale-105">
                                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                </span>
                                                <!-- Tooltip Popup -->
                                                <span class="pointer-events-none absolute -top-8 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-all duration-150 z-30 whitespace-nowrap bg-black text-white text-[10px] font-black px-2 py-0.5 rounded shadow-[2px_2px_0px_#000] border border-black scale-90 group-hover:scale-100">
                                                    Sakit
                                                    <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-black"></span>
                                                </span>
                                            </label>

                                            <!-- Alpa (Merah) -->
                                            <label class="status-pill cursor-pointer relative group" title="Alpa">
                                                <input type="radio" name="attendances[{{ $index }}][status]" value="alpa" 
                                                       {{ $currentStatus === 'alpa' ? 'checked' : '' }}
                                                       onchange="updateCounters(); updateCellColor(this);"
                                                       class="sr-only peer">
                                                <span class="w-8 h-8 flex items-center justify-center border-2 border-black rounded-sm transition-all
                                                             peer-checked:bg-[#FF6B6B] peer-checked:text-white peer-checked:shadow-[2px_2px_0px_0px_#000] peer-checked:scale-105
                                                             bg-white text-slate-700 hover:bg-slate-100 hover:scale-105">
                                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </span>
                                                <!-- Tooltip Popup -->
                                                <span class="pointer-events-none absolute -top-8 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-all duration-150 z-30 whitespace-nowrap bg-black text-white text-[10px] font-black px-2 py-0.5 rounded shadow-[2px_2px_0px_#000] border border-black scale-90 group-hover:scale-100">
                                                    Alpa
                                                    <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-black"></span>
                                                </span>
                                            </label>
                                        </div>
                                    </td>

                                    <!-- Catatan -->
                                    <td class="p-3">
                                        <input type="text" name="attendances[{{ $index }}][notes]" value="{{ $currentNotes }}" 
                                               placeholder="Catatan jika izin/sakit/terlambat..."
                                               class="w-full bg-slate-50 border border-slate-300 px-2.5 py-1.5 text-xs font-medium text-black rounded-sm focus:bg-white focus:outline-hidden focus:border-black">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bottom Submit Bar -->
            <div class="bg-white neo-box-lg p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <span class="text-xl">📋</span>
                    <div>
                        <div class="font-heading font-black text-sm text-black">
                            Presensi {{ $selectedClass->name ?? '' }} — {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}
                        </div>
                        <div class="text-[11px] text-slate-500 font-semibold">
                            Pastikan data kehadiran telah diperiksa sebelum menekan tombol simpan.
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <button type="button" onclick="confirmSaveAttendance()" 
                            class="w-full sm:w-auto neo-btn bg-[#20C997] text-black px-6 py-2.5 text-xs font-black uppercase flex items-center justify-center gap-2 cursor-pointer hover:bg-emerald-400 transition-colors shadow-md">
                        <span>💾</span> Simpan Presensi
                    </button>
                </div>
            </div>
        </form>
    @endif
</div>

@push('scripts')
<script>
    // Inisialisasi saat DOM siap
    document.addEventListener('DOMContentLoaded', () => {
        updateCounters();
        updateSelectionBar();
        // Sinkronisasi awal warna background cell dari radio yang aktif
        document.querySelectorAll('.status-cell input[type="radio"]:checked').forEach(r => {
            updateCellColor(r);
        });
    });

    // Update warna cell status kehadiran saat radio status dipilih
    function updateCellColor(radio) {
        const td = radio.closest('.status-cell');
        if (!td) return;

        td.classList.remove(
            'status-cell-hadir',
            'status-cell-terlambat',
            'status-cell-izin',
            'status-cell-sakit',
            'status-cell-alpa',
            'bg-[#D3F9D8]',
            'bg-[#D0EBFF]',
            'bg-[#E9ECEF]',
            'bg-[#FFF3BF]',
            'bg-[#FFE3E3]'
        );

        td.classList.add('status-cell-' + radio.value);
    }

    // Update Live Counters
    function updateCounters() {
        const counts = { hadir: 0, terlambat: 0, izin: 0, sakit: 0, alpa: 0 };
        const checkedRadios = document.querySelectorAll('#attendanceManualForm input[type="radio"]:checked');
        
        checkedRadios.forEach(r => {
            if (counts[r.value] !== undefined) {
                counts[r.value]++;
            }
        });

        document.getElementById('counter-hadir').innerText = counts.hadir;
        document.getElementById('counter-terlambat').innerText = counts.terlambat;
        document.getElementById('counter-izin').innerText = counts.izin;
        document.getElementById('counter-sakit').innerText = counts.sakit;
        document.getElementById('counter-alpa').innerText = counts.alpa;
    }

    // Set Status untuk SEMUA siswa
    function setAllStatus(status) {
        const rows = document.querySelectorAll('.student-row');
        rows.forEach(row => {
            const radio = row.querySelector(`input[type="radio"][value="${status}"]`);
            if (radio) {
                radio.checked = true;
                updateCellColor(radio);
            }
        });
        updateCounters();

        const statusNames = {
            hadir: 'HADIR',
            terlambat: 'TERLAMBAT',
            izin: 'IZIN',
            sakit: 'SAKIT',
            alpa: 'ALPA'
        };

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true
        });
        Toast.fire({
            icon: 'info',
            title: `Semua siswa diatur ke: ${statusNames[status]}`
        });
    }

    // Toggle Select All Checkbox
    function toggleSelectAll(masterCheckbox) {
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        rowCheckboxes.forEach(cb => cb.checked = masterCheckbox.checked);
        updateSelectionBar();
    }

    // Update Seleksi Baris
    function updateSelectionBar() {
        const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
        const countText = document.getElementById('selectedCountText');
        const masterCb = document.getElementById('selectAllCheckbox');
        const totalRows = document.querySelectorAll('.row-checkbox').length;

        if (countText) {
            countText.innerText = `${checkedCount} Siswa Dipilih`;
        }

        if (masterCb && totalRows > 0) {
            masterCb.checked = (checkedCount === totalRows);
        }
    }

    // Set Status untuk Siswa TERPILIH (yang dicentang)
    function setSelectedStatus(status) {
        const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
        if (checkedBoxes.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Pilih Siswa',
                text: 'Centang minimal satu siswa untuk menggunakan aksi terpilih.',
                confirmButtonColor: '#5294FF'
            });
            return;
        }

        checkedBoxes.forEach(cb => {
            const row = cb.closest('.student-row');
            if (row) {
                const radio = row.querySelector(`input[type="radio"][value="${status}"]`);
                if (radio) {
                    radio.checked = true;
                    updateCellColor(radio);
                }
            }
        });

        updateCounters();

        const statusNames = {
            hadir: 'HADIR',
            terlambat: 'TERLAMBAT',
            izin: 'IZIN',
            sakit: 'SAKIT',
            alpa: 'ALPA'
        };

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true
        });
        Toast.fire({
            icon: 'success',
            title: `${checkedBoxes.length} siswa diatur ke: ${statusNames[status]}`
        });
    }

    // Konfirmasi Simpan dengan Ringkasan SweetAlert2
    function confirmSaveAttendance() {
        const counts = {
            hadir: parseInt(document.getElementById('counter-hadir').innerText) || 0,
            terlambat: parseInt(document.getElementById('counter-terlambat').innerText) || 0,
            izin: parseInt(document.getElementById('counter-izin').innerText) || 0,
            sakit: parseInt(document.getElementById('counter-sakit').innerText) || 0,
            alpa: parseInt(document.getElementById('counter-alpa').innerText) || 0
        };

        const total = counts.hadir + counts.terlambat + counts.izin + counts.sakit + counts.alpa;
        const className = "{{ $selectedClass->name ?? 'Kelas' }}";
        const dateStr = "{{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}";

        const summaryHtml = `
            <div class="text-left space-y-3 p-3 bg-slate-50 border-2 border-black neo-box-sm">
                <div class="text-xs font-bold text-slate-600">
                    Kelas: <b>${className}</b><br>
                    Tanggal: <b>${dateStr}</b>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs font-mono font-bold pt-2 border-t border-slate-200">
                    <div class="text-emerald-700">🟢 Hadir: <b>${counts.hadir}</b></div>
                    <div class="text-blue-700">🔵 Terlambat: <b>${counts.terlambat}</b></div>
                    <div class="text-slate-600">⚪ Izin: <b>${counts.izin}</b></div>
                    <div class="text-amber-700">🟡 Sakit: <b>${counts.sakit}</b></div>
                    <div class="text-rose-700 col-span-2">🔴 Alpa: <b>${counts.alpa}</b></div>
                </div>
                <div class="text-xs font-black text-black pt-2 border-t border-slate-200">
                    Total Siswa: <b>${total}</b>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-3 font-semibold">Simpan seluruh data presensi ini ke database?</p>
        `;

        Swal.fire({
            title: 'Simpan Presensi?',
            html: summaryHtml,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#20C997',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '💾 Ya, Simpan Presensi',
            cancelButtonText: 'Batal Periksa Kembali',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('attendanceManualForm').submit();
            }
        });
    }

    // Modal Upload Excel Presensi
    function openAttendanceUploadModal() {
        const modal = document.getElementById('modalUploadAttendance');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    function closeAttendanceUploadModal() {
        const modal = document.getElementById('modalUploadAttendance');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    // Modal Riwayat Upload Batch
    let loadedBatches = [];

    function openAttendanceBatchesModal() {
        const modal = document.getElementById('modalBatchesAttendance');
        if (!modal) return;

        modal.classList.remove('hidden');
        loadAttendanceBatches();
    }

    function closeAttendanceBatchesModal() {
        const modal = document.getElementById('modalBatchesAttendance');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    function loadAttendanceBatches() {
        const container = document.getElementById('batchesTableBody');
        const emptyState = document.getElementById('batchesEmptyState');
        const loadingState = document.getElementById('batchesLoadingState');

        if (!container) return;

        loadingState.classList.remove('hidden');
        emptyState.classList.add('hidden');
        container.innerHTML = '';

        const classId = "{{ $selectedClassId }}";
        const url = `{{ $batchesRoute }}?school_class_id=${classId}`;

        fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(data => {
                loadingState.classList.add('hidden');
                loadedBatches = data.batches || [];

                if (loadedBatches.length === 0) {
                    emptyState.classList.remove('hidden');
                    return;
                }

                let html = '';
                loadedBatches.forEach((b, idx) => {
                    let statusBadge = '';
                    if (b.status === 'completed') {
                        statusBadge = '<span class="px-2 py-0.5 text-[11px] font-black bg-[#D3F9D8] text-emerald-950 border border-black rounded shadow-[1px_1px_0px_0px_#000]">Sukses</span>';
                    } else if (b.status === 'completed_with_errors') {
                        statusBadge = '<span class="px-2 py-0.5 text-[11px] font-black bg-[#FFF3BF] text-amber-950 border border-black rounded shadow-[1px_1px_0px_0px_#000]">Sebagian Error</span>';
                    } else {
                        statusBadge = '<span class="px-2 py-0.5 text-[11px] font-black bg-[#FFE3E3] text-rose-950 border border-black rounded shadow-[1px_1px_0px_0px_#000]">Gagal</span>';
                    }

                    const errorBtn = (b.failed_rows > 0 && b.error_log && b.error_log.length > 0)
                        ? `<button type="button" onclick="showBatchErrorDetail(${idx})" class="neo-btn bg-[#FFE3E3] hover:bg-[#ffc9c9] text-rose-900 border border-black text-[10px] font-bold px-2 py-1 cursor-pointer">Lihat ${b.failed_rows} Error</button>`
                        : '<span class="text-slate-400 text-xs">-</span>';

                    html += `
                        <tr class="border-b border-slate-200 hover:bg-slate-50 text-xs">
                            <td class="p-2.5 font-mono font-bold text-slate-700">${b.created_at}</td>
                            <td class="p-2.5 font-bold text-black">${b.uploader_name}</td>
                            <td class="p-2.5 font-mono text-slate-800 truncate max-w-[180px]" title="${b.original_filename}">${b.original_filename}</td>
                            <td class="p-2.5 text-center font-bold font-mono">${b.total_rows}</td>
                            <td class="p-2.5 text-center font-bold font-mono text-emerald-700">${b.success_rows}</td>
                            <td class="p-2.5 text-center font-bold font-mono text-rose-700">${b.failed_rows}</td>
                            <td class="p-2.5 text-center">${statusBadge}</td>
                            <td class="p-2.5 text-center">${errorBtn}</td>
                        </tr>
                    `;
                });

                container.innerHTML = html;
            })
            .catch(err => {
                loadingState.classList.add('hidden');
                container.innerHTML = `<tr><td colspan="8" class="p-4 text-center text-xs font-bold text-rose-600">Gagal memuat riwayat: ${err.message}</td></tr>`;
            });
    }

    function showBatchErrorDetail(index) {
        const batch = loadedBatches[index];
        if (!batch || !batch.error_log) return;

        let errorListHtml = '<div class="space-y-1 text-left max-h-60 overflow-y-auto font-mono text-xs border border-slate-200 p-2 bg-slate-50">';
        batch.error_log.forEach(err => {
            errorListHtml += `<div class="p-1 border-b border-slate-200 text-rose-800">
                <b>Baris ${err.row ?? '-'}:</b> ${err.error}
            </div>`;
        });
        errorListHtml += '</div>';

        Swal.fire({
            title: `Rincian Error (${batch.failed_rows} Baris Gagal)`,
            html: errorListHtml,
            icon: 'warning',
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#000000',
        });
    }

    // Helper Navigasi Tanggal Presensi
    function setAttendanceFilterDate(targetDate) {
        const input = document.getElementById('attendanceDateInput');
        if (input) {
            input.value = targetDate;
            input.form.submit();
        }
    }

    function triggerAttendanceDatePicker() {
        const input = document.getElementById('attendanceDateInput');
        if (input) {
            if (typeof input.showPicker === 'function') {
                input.showPicker();
            } else {
                input.focus();
                input.click();
            }
        }
    }

    // Modal Isi Absensi 1 Bulan Penuh (Khusus Admin)
    function openMonthlyFillModal(defaultScope = 'all') {
        const modal = document.getElementById('modalMonthlyAttendance');
        if (!modal) return;

        const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
        const checkedCount = checkedBoxes.length;

        const badge = document.getElementById('monthlySelectedBadge');
        if (badge) badge.innerText = checkedCount;

        const radioAll = document.getElementById('monthlyScopeAll');
        const radioSelected = document.getElementById('monthlyScopeSelected');

        if (defaultScope === 'selected' && checkedCount > 0) {
            if (radioSelected) {
                radioSelected.checked = true;
                radioSelected.disabled = false;
            }
        } else {
            if (radioAll) radioAll.checked = true;
            if (radioSelected) {
                radioSelected.disabled = (checkedCount === 0);
            }
        }

        modal.classList.remove('hidden');
    }

    function closeMonthlyFillModal() {
        const modal = document.getElementById('modalMonthlyAttendance');
        if (modal) modal.classList.add('hidden');
    }

    function confirmMonthlyFill() {
        const form = document.getElementById('monthlyFillForm');
        if (!form) return;

        const monthSelect = document.getElementById('monthlyFillMonth');
        const yearInput = document.getElementById('monthlyFillYear');
        const statusRadio = form.querySelector('input[name="status"]:checked');
        const scopeRadio = form.querySelector('input[name="scope"]:checked');
        const overwriteCheck = document.getElementById('monthlyOverwrite');

        const monthText = monthSelect.options[monthSelect.selectedIndex].text;
        const yearVal = yearInput.value;
        const statusVal = statusRadio ? statusRadio.value : 'hadir';
        const scopeVal = scopeRadio ? scopeRadio.value : 'all';
        const isOverwrite = overwriteCheck && overwriteCheck.checked;

        const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
        if (scopeVal === 'selected' && checkedBoxes.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Pilih minimal 1 siswa dengan mencentang kotak pada tabel terlebih dahulu.',
                confirmButtonColor: '#000000'
            });
            return;
        }

        const className = "{{ $selectedClass->name ?? 'Kelas' }}";
        const totalStudents = "{{ $students->count() }}";
        const targetLabel = scopeVal === 'all' 
            ? `Seluruh Siswa (${totalStudents} siswa)` 
            : `Hanya Siswa Terpilih (${checkedBoxes.length} siswa)`;

        const statusNames = {
            hadir: 'HADIR',
            terlambat: 'TERLAMBAT',
            izin: 'IZIN',
            sakit: 'SAKIT',
            alpa: 'ALPA'
        };

        const overwriteLabel = isOverwrite 
            ? '<span class="text-rose-700">Ya, Timpa Data Lama</span>' 
            : '<span class="text-emerald-700">Aman (Hanya Isi Hari Kosong)</span>';

        const summaryHtml = `
            <div class="text-left space-y-2.5 p-3 bg-slate-50 border-2 border-black neo-box-sm text-xs font-bold">
                <div class="flex justify-between border-b border-slate-200 pb-1.5">
                    <span class="text-slate-500">Kelas Target:</span>
                    <span class="text-black">${className}</span>
                </div>
                <div class="flex justify-between border-b border-slate-200 pb-1.5">
                    <span class="text-slate-500">Bulan & Tahun:</span>
                    <span class="text-black font-mono">${monthText} ${yearVal}</span>
                </div>
                <div class="flex justify-between border-b border-slate-200 pb-1.5">
                    <span class="text-slate-500">Target Siswa:</span>
                    <span class="text-black">${targetLabel}</span>
                </div>
                <div class="flex justify-between border-b border-slate-200 pb-1.5">
                    <span class="text-slate-500">Status Kehadiran:</span>
                    <span class="font-black text-black">${statusNames[statusVal] || statusVal.toUpperCase()}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Opsi Penimpaan:</span>
                    <span>${overwriteLabel}</span>
                </div>
            </div>
            <div class="p-2.5 bg-[#FFF3BF] border border-black text-[11px] text-amber-950 font-bold mt-3 text-left">
                ⚠️ <b>Perhatian:</b> Seluruh hari aktif (Senin–Sabtu) di bulan ${monthText} ${yearVal} akan diisi. Hari Minggu otomatis dilewati. Data presensi tetap dapat diubah oleh Guru kapan saja.
            </div>
            <p class="text-xs text-slate-700 mt-3 font-semibold">Jalankan proses pengisian absensi 1 bulan ini sekarang?</p>
        `;

        Swal.fire({
            title: 'Isi Absensi 1 Bulan?',
            html: summaryHtml,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#20C997',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '🚀 Ya, Proses 1 Bulan',
            cancelButtonText: 'Batal Periksa Kembali',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const container = document.getElementById('monthlyStudentIdsContainer');
                if (container) {
                    container.innerHTML = '';
                    if (scopeVal === 'selected') {
                        checkedBoxes.forEach(cb => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'student_ids[]';
                            input.value = cb.value;
                            container.appendChild(input);
                        });
                    }
                }
                form.submit();
            }
        });
    }

    // Event listener tombol ESC untuk menutup modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAttendanceUploadModal();
            closeAttendanceBatchesModal();
            closeMonthlyFillModal();
        }
    });
</script>

<!-- MODAL 1: Upload Excel Presensi -->
<div id="modalUploadAttendance" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white border-4 border-black p-6 shadow-[8px_8px_0px_0px_#000000] max-w-lg w-full relative">
        <div class="flex items-center justify-between pb-3 mb-4 border-b-2 border-black">
            <div class="flex items-center gap-2">
                <span class="text-2xl">📤</span>
                <h3 class="text-base font-black uppercase tracking-wider text-black">Upload Presensi Excel</h3>
            </div>
            <button type="button" onclick="closeAttendanceUploadModal()" class="text-black font-black text-xl hover:text-rose-600 cursor-pointer">✕</button>
        </div>

        <form action="{{ $uploadRoute }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="school_class_id" value="{{ $selectedClassId }}">
            <input type="hidden" name="date" value="{{ $date }}">

            <!-- Banner Info Konfirmasi -->
            <div class="p-3 bg-[#E7F5FF] border-2 border-black neo-box-sm text-xs font-bold text-slate-800 space-y-1">
                <div class="flex items-center justify-between">
                    <span>Kelas Target:</span>
                    <span class="font-black text-black">{{ $selectedClass->name ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Tanggal Presensi:</span>
                    <span class="font-black text-black font-mono">{{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</span>
                </div>
            </div>

            <!-- Petunjuk Format Singkat -->
            <div class="p-3 bg-[#FFF3BF] border-2 border-black neo-box-sm text-[11px] text-amber-950 font-bold space-y-1">
                <div class="font-black uppercase">💡 Panduan Cepat:</div>
                <p>1. Unduh template terlebih dahulu melalui tombol <b>Template Excel</b>.</p>
                <p>2. Kolom status menerima: <b>H (Hadir), T (Terlambat), S (Sakit), I (Izin), A (Alpa)</b>.</p>
                <p>3. Jika data pada tanggal yang sama sudah ada, sistem akan langsung <b>memperbarui (upsert)</b> data tersebut.</p>
            </div>

            <!-- Input File -->
            <div>
                <label class="block text-xs font-black uppercase text-black mb-1.5">
                    Pilih File Spreadsheet (.xlsx, .xls, .csv)
                </label>
                <input type="file" 
                       name="file" 
                       accept=".xlsx,.xls,.csv" 
                       required
                       class="w-full bg-white border-2 border-black p-2 text-xs font-bold text-black focus:outline-hidden cursor-pointer file:mr-3 file:py-1 file:px-3 file:border-2 file:border-black file:bg-[#FFD43B] file:text-xs file:font-black file:cursor-pointer">
                <p class="text-[10px] text-slate-500 font-semibold mt-1">Ukuran berkas maksimal 5 MB.</p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t-2 border-black">
                <button type="button" 
                        onclick="closeAttendanceUploadModal()" 
                        class="neo-btn bg-white hover:bg-slate-100 text-black text-xs px-4 py-2 font-bold border-2 border-black cursor-pointer">
                    Batal
                </button>
                <button type="submit" 
                        class="neo-btn bg-[#5294FF] hover:bg-[#3b82f6] text-white text-xs px-5 py-2 font-black border-2 border-black shadow-[3px_3px_0px_0px_#000] cursor-pointer">
                    🚀 Unggah & Proses
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: Riwayat Upload Batch -->
<div id="modalBatchesAttendance" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white border-4 border-black p-6 shadow-[8px_8px_0px_0px_#000000] max-w-4xl w-full max-h-[90vh] flex flex-col relative">
        <div class="flex items-center justify-between pb-3 mb-4 border-b-2 border-black">
            <div class="flex items-center gap-2">
                <span class="text-2xl">📋</span>
                <div>
                    <h3 class="text-base font-black uppercase tracking-wider text-black">Riwayat Upload Presensi</h3>
                    <p class="text-[11px] font-bold text-slate-500">Kelas: {{ $selectedClass->name ?? '-' }} (20 batch terakhir)</p>
                </div>
            </div>
            <button type="button" onclick="closeAttendanceBatchesModal()" class="text-black font-black text-xl hover:text-rose-600 cursor-pointer">✕</button>
        </div>

        <div class="overflow-y-auto flex-1 border-2 border-black neo-box-sm">
            <table class="w-full text-left border-collapse">
                <thead class="bg-black text-white text-[11px] uppercase font-black tracking-wider sticky top-0">
                    <tr>
                        <th class="p-2.5">Waktu</th>
                        <th class="p-2.5">Pengunggah</th>
                        <th class="p-2.5">Nama Berkas</th>
                        <th class="p-2.5 text-center">Total</th>
                        <th class="p-2.5 text-center">Sukses</th>
                        <th class="p-2.5 text-center">Gagal</th>
                        <th class="p-2.5 text-center">Status</th>
                        <th class="p-2.5 text-center">Rincian</th>
                    </tr>
                </thead>
                <tbody id="batchesTableBody" class="divide-y divide-slate-200">
                    <!-- Dinamis via AJAX -->
                </tbody>
            </table>

            <div id="batchesLoadingState" class="p-8 text-center text-xs font-bold text-slate-500">
                <span class="inline-block animate-spin mr-1">⌛</span> Memuat riwayat upload...
            </div>

            <div id="batchesEmptyState" class="hidden p-8 text-center text-xs font-bold text-slate-500">
                Belum ada riwayat berkas presensi yang diunggah untuk kelas ini.
            </div>
        </div>

        <div class="flex items-center justify-between pt-4 mt-4 border-t-2 border-black">
            <span class="text-[11px] font-bold text-slate-500">Klik "Lihat Error" untuk memeriksa baris yang tidak lolos validasi.</span>
            <button type="button" 
                    onclick="closeAttendanceBatchesModal()" 
                    class="neo-btn bg-black text-white text-xs px-5 py-2 font-bold cursor-pointer hover:bg-slate-800">
                Tutup
            </button>
        </div>
    </div>
</div>

@if($role === 'admin')
<!-- MODAL 3: Isi Absensi 1 Bulan Penuh (Khusus Admin) -->
<div id="modalMonthlyAttendance" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white border-4 border-black p-6 shadow-[8px_8px_0px_0px_#000000] max-w-lg w-full relative">
        <div class="flex items-center justify-between pb-3 mb-4 border-b-2 border-black">
            <div class="flex items-center gap-2">
                <span class="text-2xl">📅</span>
                <div>
                    <h3 class="text-base font-black uppercase tracking-wider text-black">Isi Absensi 1 Bulan Penuh</h3>
                    <p class="text-[11px] font-bold text-slate-500">Khusus Administrator • Kelas: {{ $selectedClass->name ?? '-' }}</p>
                </div>
            </div>
            <button type="button" onclick="closeMonthlyFillModal()" class="text-black font-black text-xl hover:text-rose-600 cursor-pointer">✕</button>
        </div>

        <form id="monthlyFillForm" action="{{ $monthlyFillRoute }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="school_class_id" value="{{ $selectedClassId }}">
            <div id="monthlyStudentIdsContainer"></div>

            <!-- Periode Bulan & Tahun -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-black uppercase text-black mb-1.5">
                        Pilih Bulan
                    </label>
                    <select name="month" id="monthlyFillMonth" class="w-full bg-white border-2 border-black p-2 text-xs font-bold text-black focus:outline-hidden">
                        @for($m = 1; $m <= 12; $m++)
                            @php
                                $mName = \Carbon\Carbon::create(2026, $m, 1)->locale('id')->translatedFormat('F');
                            @endphp
                            <option value="{{ $m }}" {{ $carbonDate->month == $m ? 'selected' : '' }}>
                                {{ $mName }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-black uppercase text-black mb-1.5">
                        Tahun
                    </label>
                    <input type="number" name="year" id="monthlyFillYear" value="{{ $carbonDate->year }}" min="2020" max="2099" required
                           class="w-full bg-white border-2 border-black p-2 text-xs font-bold font-mono text-black focus:outline-hidden">
                </div>
            </div>

            <!-- Status Kehadiran yang Diterapkan -->
            <div>
                <label class="block text-xs font-black uppercase text-black mb-1.5">
                    Status Kehadiran (Senin s/d Sabtu)
                </label>
                <div class="grid grid-cols-3 sm:grid-cols-5 gap-1.5">
                    <label class="flex flex-col items-center justify-center p-2 border-2 border-black rounded cursor-pointer transition-colors bg-[#D3F9D8] has-checked:ring-2 has-checked:ring-black">
                        <input type="radio" name="status" value="hadir" checked class="sr-only">
                        <span class="text-xs font-black text-emerald-950">Hadir</span>
                    </label>
                    <label class="flex flex-col items-center justify-center p-2 border-2 border-black rounded cursor-pointer transition-colors bg-[#D0EBFF] has-checked:ring-2 has-checked:ring-black">
                        <input type="radio" name="status" value="terlambat" class="sr-only">
                        <span class="text-xs font-black text-blue-950">Terlambat</span>
                    </label>
                    <label class="flex flex-col items-center justify-center p-2 border-2 border-black rounded cursor-pointer transition-colors bg-[#E9ECEF] has-checked:ring-2 has-checked:ring-black">
                        <input type="radio" name="status" value="izin" class="sr-only">
                        <span class="text-xs font-black text-slate-900">Izin</span>
                    </label>
                    <label class="flex flex-col items-center justify-center p-2 border-2 border-black rounded cursor-pointer transition-colors bg-[#FFF3BF] has-checked:ring-2 has-checked:ring-black">
                        <input type="radio" name="status" value="sakit" class="sr-only">
                        <span class="text-xs font-black text-amber-950">Sakit</span>
                    </label>
                    <label class="flex flex-col items-center justify-center p-2 border-2 border-black rounded cursor-pointer transition-colors bg-[#FFE3E3] has-checked:ring-2 has-checked:ring-black">
                        <input type="radio" name="status" value="alpa" class="sr-only">
                        <span class="text-xs font-black text-rose-950">Alpa</span>
                    </label>
                </div>
            </div>

            <!-- Target Siswa -->
            <div class="space-y-1.5 bg-slate-50 border-2 border-black p-3 rounded-sm neo-box-sm">
                <label class="block text-xs font-black uppercase text-black mb-1">
                    Target Siswa
                </label>
                <div class="space-y-2 text-xs font-bold text-black">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="scope" value="all" id="monthlyScopeAll" checked class="accent-black">
                        <span>Seluruh Siswa di Kelas ({{ $students->count() }} siswa)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="scope" value="selected" id="monthlyScopeSelected" class="accent-black">
                        <span>Hanya Siswa Terpilih (<span id="monthlySelectedBadge" class="font-mono font-black text-blue-700">0</span> siswa dicentang)</span>
                    </label>
                </div>
            </div>

            <!-- Opsi Timpa -->
            <div class="p-3 bg-white border-2 border-black neo-box-sm">
                <label class="flex items-start gap-2 cursor-pointer">
                    <input type="checkbox" name="overwrite" value="1" id="monthlyOverwrite" class="mt-0.5 accent-black w-4 h-4 border-2 border-black">
                    <div>
                        <span class="text-xs font-black text-black">Timpa data presensi yang sudah ada</span>
                        <p class="text-[11px] text-slate-500 font-semibold">Jika tidak dicentang, tanggal yang sudah memiliki rekaman presensi (misal izin/sakit yang diinput guru) tidak akan ditimpa.</p>
                    </div>
                </label>
            </div>

            <!-- Note Banner -->
            <div class="p-2.5 bg-[#FFF9DB] border border-black text-[11px] text-amber-950 font-bold">
                <span>ℹ️</span> Hari Minggu otomatis dikecualikan (hanya Senin s/d Sabtu yang dicatat). Seluruh rekaman presensi ini tetap dapat diedit oleh Guru kapan saja melalui halaman presensi manual.
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t-2 border-black">
                <button type="button" onclick="closeMonthlyFillModal()" class="neo-btn bg-white hover:bg-slate-100 text-black text-xs px-4 py-2 font-bold border-2 border-black cursor-pointer">
                    Batal
                </button>
                <button type="button" onclick="confirmMonthlyFill()" class="neo-btn bg-[#FFD43B] hover:bg-[#ffe066] text-black text-xs px-5 py-2 font-black border-2 border-black shadow-[3px_3px_0px_0px_#000] cursor-pointer">
                    Lanjutkan & Konfirmasi ➔
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endpush
