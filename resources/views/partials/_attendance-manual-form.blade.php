@php
    $role = Auth::user()->role;
    $submitRoute = $role === 'admin' ? route('admin.attendances.manual.store') : route('guru.attendance.manual.store');
    $filterRoute = $role === 'admin' ? route('admin.attendances.manual') : route('guru.attendance.manual');
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
                <!-- Tanggal -->
                <div class="w-full sm:w-auto">
                    <label class="block text-xs font-black uppercase text-black mb-1.5 flex items-center gap-1.5">
                        <span>📅</span> Tanggal Presensi
                    </label>
                    <input type="date" name="date" value="{{ $date }}" 
                           onchange="this.form.submit()"
                           class="w-full sm:w-48 bg-white border-2 border-black px-3 py-2 text-xs font-bold font-mono focus:outline-hidden focus:ring-2 focus:ring-black">
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
                <div class="flex items-center gap-2 pt-2 md:pt-0">
                    <span class="text-xs font-mono font-bold bg-[#E7F5FF] text-blue-900 border-2 border-black px-3 py-1.5 rounded-sm">
                        {{ $students->count() }} Siswa Terdaftar
                    </span>
                    @if($selectedClass->homeroomTeacher)
                        <span class="text-xs font-bold bg-[#D3F9D8] text-emerald-900 border-2 border-black px-3 py-1.5 rounded-sm hidden lg:inline-block">
                            Wali Kelas: {{ $selectedClass->homeroomTeacher->user->name ?? '-' }}
                        </span>
                    @endif
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
</script>
@endpush
