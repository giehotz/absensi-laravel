@extends('layouts.admin')

@section('title', 'Jadwal Pelajaran')
@section('page-title', 'Manajemen Jadwal Pelajaran')

@section('content')
<div class="space-y-6">
    <!-- Header Title Card -->
    <div class="bg-[#FFF3BF] neo-box-lg p-6 sm:p-8 text-black relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="space-y-1.5 z-10">
            <div class="flex items-center gap-2">
                <span class="neo-badge bg-[#5294FF] text-white">ADMINISTRATOR</span>
                <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border border-black">
                    {{ $academicYear->name ?? 'Tahun Ajaran Aktif' }}
                </span>
                <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border border-black hidden sm:inline-block">
                    Semester {{ ucfirst($academicYear->semester ?? 'Ganjil') }}
                </span>
            </div>
            <h1 class="font-heading text-2xl sm:text-3xl font-black tracking-tight text-black uppercase">
                JADWAL KELAS MINGGUAN
            </h1>
            <p class="text-xs sm:text-sm font-semibold text-slate-800 max-w-2xl">
                Susun dan kelola jadwal tatap muka mingguan kelas dengan sistem deteksi otomatis anti-bentrok guru & kelas (referensi Simpatika).
            </p>
        </div>

        <div class="z-10 flex flex-wrap gap-2">
            <button type="button" onclick="openTemplateModal()" class="neo-btn bg-white text-black text-xs font-bold px-4 py-2.5 flex items-center gap-1.5 hover:bg-slate-100 cursor-pointer">
                <span>📋</span> Template Jam Simpatika
            </button>
            <button type="button" onclick="openCreateScheduleModal()" class="neo-btn bg-[#20C997] text-black text-xs font-black px-4 py-2.5 flex items-center gap-1.5 hover:bg-emerald-400 cursor-pointer shadow-sm">
                <span>➕</span> Tambah Jadwal Baru
            </button>
        </div>
    </div>

    <!-- Toolbar: Pemilihan Kelas & Statistik Ringkas -->
    <div class="bg-white neo-box p-4 sm:p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.schedules.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <label for="school_class_id" class="text-xs font-black uppercase text-black shrink-0">
                Pilih Kelas:
            </label>
            <select name="school_class_id" id="school_class_id" onchange="this.form.submit()"
                    class="bg-white border-2 border-black px-3 py-1.5 text-xs font-black text-black focus:outline-hidden min-w-[200px]">
                @foreach($classes as $cls)
                    <option value="{{ $cls->id }}" {{ $selectedClassId == $cls->id ? 'selected' : '' }}>
                        {{ $cls->name }} (Tingkat {{ $cls->level }})
                    </option>
                @endforeach
            </select>
        </form>

        @if($selectedClass)
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold bg-[#E7F5FF] text-blue-900 border-2 border-black px-3 py-1">
                    Wali Kelas: <b>{{ $selectedClass->homeroomTeacher->user->name ?? 'Belum Ditugaskan' }}</b>
                </span>
                <span class="text-xs font-mono font-bold bg-[#D3F9D8] text-emerald-950 border-2 border-black px-3 py-1">
                    Total: <b>{{ $schedules->count() }} Sesi Mengajar</b>
                </span>
            </div>
        @endif
    </div>

    @if(!$selectedClass)
        <div class="bg-white neo-box p-12 text-center text-slate-500 space-y-2">
            <div class="text-4xl">🏫</div>
            <div class="text-base font-black text-black">Belum Ada Data Kelas</div>
            <p class="text-xs">Silakan tambahkan data kelas terlebih dahulu di menu Data Kelas.</p>
        </div>
    @else
        <!-- Matriks 6 Hari Jadwal Mingguan (Senin s.d. Sabtu) -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($schedulesByDay as $dayNum => $dayData)
                <div class="bg-white neo-box p-5 flex flex-col justify-between space-y-4">
                    <!-- Header Hari -->
                    <div class="flex items-center justify-between border-b-2 border-black pb-2.5">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 bg-black rounded-full inline-block"></span>
                            <h3 class="font-heading font-black text-base uppercase text-black">
                                {{ $dayData['name'] }}
                            </h3>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] font-mono font-bold bg-[#FFF9DB] border border-black px-2 py-0.5">
                                {{ count($dayData['items']) }} Sesi
                            </span>
                            <button type="button" onclick="openCreateScheduleModal({{ $dayNum }})" 
                                    class="text-xs font-black bg-[#20C997] hover:bg-emerald-400 border border-black px-1.5 py-0.5 cursor-pointer" 
                                    title="Tambah Jadwal Hari {{ $dayData['name'] }}">
                                ➕
                            </button>
                        </div>
                    </div>

                    <!-- Daftar Sesi Tatap Muka -->
                    <div class="space-y-3 flex-1">
                        @forelse($dayData['items'] as $sch)
                            <div class="p-3 bg-slate-50 border-2 border-black rounded-sm space-y-2 hover:bg-amber-50/50 transition-colors relative group">
                                <div class="flex items-center justify-between gap-2">
                                    @php
                                        $startCarbon = \Carbon\Carbon::parse($sch->start_time);
                                        $endCarbon = \Carbon\Carbon::parse($sch->end_time);
                                        $diffMinutes = $startCarbon->diffInMinutes($endCarbon);
                                        $jp = max(1, (int) round($diffMinutes / 40));
                                    @endphp
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="text-[10px] font-black bg-[#FFD43B] text-black px-1.5 py-0.5 border border-black rounded-xs">
                                            {{ $jp }} JP
                                        </span>
                                        <span class="text-[11px] font-mono font-black bg-black text-white px-2 py-0.5 rounded-sm">
                                            {{ substr($sch->start_time, 0, 5) }} - {{ substr($sch->end_time, 0, 5) }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <button type="button" onclick='openEditScheduleModal(@json($sch))' 
                                                class="text-[11px] font-bold bg-white border border-black px-1.5 py-0.5 hover:bg-slate-200 cursor-pointer" title="Edit Jadwal">
                                            ✏️
                                        </button>
                                        <form action="{{ route('admin.schedules.destroy', $sch->id) }}" method="POST" class="inline" onsubmit="return confirmDeleteSchedule(event)">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-[11px] font-bold bg-[#FF6B6B] text-white border border-black px-1.5 py-0.5 hover:bg-rose-600 cursor-pointer" title="Hapus">
                                                ✕
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <div>
                                    <div class="text-xs font-black text-black">
                                        {{ $sch->subject->name ?? 'Mata Pelajaran' }}
                                    </div>
                                    <div class="text-[10px] font-mono text-slate-500 font-semibold">
                                        Kode: {{ $sch->subject->code ?? '-' }}
                                    </div>
                                </div>

                                <div class="pt-1.5 border-t border-black/10 flex items-center justify-between text-[11px] font-medium text-slate-700">
                                    <div class="flex items-center gap-1.5 truncate">
                                        <span>👨‍🏫</span>
                                        <span class="truncate font-semibold text-black">{{ $sch->teacher->user->name ?? 'Guru' }}</span>
                                    </div>
                                    <span class="text-[10px] font-mono text-slate-500">
                                        {{ $sch->teacher->nip ?? '-' }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="py-6 px-3 bg-slate-50/50 border border-dashed border-slate-300 rounded text-center text-slate-400 text-xs">
                                <span>☕ Belum ada jadwal</span>
                            </div>
                        @endforelse
                    </div>

                    <!-- Footer Hari -->
                    <div class="pt-2 border-t border-black/10 flex justify-end">
                        <button type="button" onclick="openCreateScheduleModal({{ $dayNum }})" 
                                class="w-full neo-btn bg-slate-100 hover:bg-white text-black text-[11px] font-bold py-1.5 text-center">
                            + Tambah Sesi {{ $dayData['name'] }}
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Modal Tambah / Edit Jadwal Pelajaran -->
<div id="scheduleModal" class="fixed inset-0 z-50 bg-black/60 hidden flex items-center justify-center p-2 sm:p-4" onclick="if(event.target===this) closeScheduleModal()">
    <div class="bg-white neo-box-lg w-full max-w-xl overflow-hidden animate-in fade-in zoom-in duration-200 flex flex-col max-h-[92vh]">
        <!-- Fixed Header -->
        <div class="bg-[#FFF3BF] p-3.5 sm:p-4 text-black border-b-2 border-black flex items-center justify-between shrink-0">
            <h3 id="modalTitle" class="font-heading font-black text-sm uppercase flex items-center gap-2">
                <span>📅</span> Tambah Jadwal Pelajaran
            </h3>
            <button type="button" onclick="closeScheduleModal()" class="font-black text-lg hover:opacity-75 cursor-pointer p-1">✕</button>
        </div>

        <!-- Form Wrapper -->
        <form id="scheduleForm" method="POST" action="{{ route('admin.schedules.store') }}" class="flex flex-col flex-1 min-h-0 overflow-hidden">
            @csrf
            <div id="methodContainer"></div>
            
            <input type="hidden" name="school_class_id" id="form_school_class_id" value="{{ $selectedClassId }}">

            <!-- Scrollable Body Content -->
            <div class="p-4 sm:p-5 space-y-4 overflow-y-auto flex-1 text-left">
                <!-- Info Kelas Terpilih -->
                <div class="p-3 bg-[#E7F5FF] border-2 border-black text-xs font-bold text-blue-950 flex flex-wrap items-center justify-between gap-2">
                    <span>Kelas: {{ $selectedClass->name ?? '' }} (Tingkat {{ $selectedClass->level ?? '' }})</span>
                    <span class="text-[10px] bg-white px-2 py-0.5 border border-black font-mono">ID: #{{ $selectedClassId }}</span>
                </div>

                <!-- Pilih Hari -->
                <div>
                    <label class="block text-xs font-black uppercase text-black mb-1">Hari Belajar *</label>
                    <select name="day_of_week" id="form_day_of_week" required
                            class="w-full bg-white border-2 border-black px-3 py-2 text-xs font-bold text-black focus:outline-hidden">
                        <option value="">-- Pilih Hari --</option>
                        @foreach($daysMap as $dNum => $dName)
                            <option value="{{ $dNum }}">{{ $dName }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Pilihan Rentang Jam Tatap Muka (Bisa Lebih Dari 1 Jam Pelajaran) -->
                <div class="p-3 bg-amber-50 border-2 border-black space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="text-[11px] font-black uppercase text-black flex items-center gap-1">
                            <span>⚡</span> Pilihan Rentang Jam Pelajaran (JP)
                        </span>
                        <span class="text-[10px] bg-white border border-black px-1.5 py-0.5 font-mono font-bold text-slate-700">
                            Bisa Multi-Jam / Blok
                        </span>
                    </div>

                    <!-- Dropdown Rentang Dari Jam Ke - Sampai Jam Ke -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 bg-white p-2.5 border-2 border-black rounded-xs">
                        <div>
                            <label class="block text-[10px] font-black text-slate-800 uppercase mb-1">Dari Jam Ke-</label>
                            <select id="quick_slot_from" onchange="applyRangeSlotTime()" 
                                    class="w-full bg-slate-50 border border-black px-2 py-1.5 text-xs font-bold text-black focus:outline-hidden">
                                <option value="">-- Jam Awal --</option>
                                @foreach($presets['reguler_40']['slots'] as $idx => $slot)
                                    <option value="{{ $idx }}" data-start="{{ $slot['start'] }}" data-end="{{ $slot['end'] }}">
                                        {{ $slot['label'] }} ({{ $slot['start'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-800 uppercase mb-1">Sampai Jam Ke-</label>
                            <select id="quick_slot_to" onchange="applyRangeSlotTime()" 
                                    class="w-full bg-slate-50 border border-black px-2 py-1.5 text-xs font-bold text-black focus:outline-hidden">
                                <option value="">-- Jam Akhir --</option>
                                @foreach($presets['reguler_40']['slots'] as $idx => $slot)
                                    <option value="{{ $idx }}" data-start="{{ $slot['start'] }}" data-end="{{ $slot['end'] }}">
                                        {{ $slot['label'] }} (s.d {{ $slot['end'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Info Kalkulasi JP Real-Time -->
                    <div id="jpCalcInfo" class="hidden p-2.5 bg-[#D3F9D8] border border-black text-[11px] font-bold text-emerald-950 flex flex-wrap items-center justify-between gap-2">
                        <span id="jpCalcText"></span>
                        <span id="jpCalcBadge" class="bg-black text-white text-[10px] font-mono px-2 py-0.5 rounded-xs"></span>
                    </div>

                    <!-- Tombol Pintas Blok Jam Populer (1 Klik) -->
                    <div>
                        <span class="text-[10px] font-black uppercase text-slate-700 block mb-1.5">
                            Pintas Blok Jam Populer:
                        </span>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" onclick="applyPresetBlock(0, 2)" 
                                    class="text-[10px] font-black bg-white hover:bg-[#FFD43B] border border-black px-2.5 py-1 cursor-pointer transition-colors shadow-2xs">
                                Jam 1–3 (3 JP) 🔥
                            </button>
                            <button type="button" onclick="applyPresetBlock(0, 1)" 
                                    class="text-[10px] font-bold bg-white hover:bg-[#FFD43B] border border-black px-2.5 py-1 cursor-pointer transition-colors">
                                Jam 1–2 (2 JP)
                            </button>
                            <button type="button" onclick="applyPresetBlock(1, 2)" 
                                    class="text-[10px] font-bold bg-white hover:bg-[#FFD43B] border border-black px-2.5 py-1 cursor-pointer transition-colors">
                                Jam 2–3 (2 JP)
                            </button>
                            <button type="button" onclick="applyPresetBlock(3, 4)" 
                                    class="text-[10px] font-bold bg-white hover:bg-[#FFD43B] border border-black px-2.5 py-1 cursor-pointer transition-colors">
                                Jam 4–5 (2 JP)
                            </button>
                            <button type="button" onclick="applyPresetBlock(3, 5)" 
                                    class="text-[10px] font-bold bg-white hover:bg-[#FFD43B] border border-black px-2.5 py-1 cursor-pointer transition-colors">
                                Jam 4–6 (3 JP)
                            </button>
                            <button type="button" onclick="applyPresetBlock(6, 7)" 
                                    class="text-[10px] font-bold bg-white hover:bg-[#FFD43B] border border-black px-2.5 py-1 cursor-pointer transition-colors">
                                Jam 7–8 (2 JP)
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Rentang Waktu (Jam Mulai & Jam Selesai) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-black uppercase text-black mb-1">Jam Mulai (HH:MM) *</label>
                        <input type="time" name="start_time" id="form_start_time" required
                               class="w-full bg-white border-2 border-black px-3 py-2 text-xs font-mono font-bold text-black focus:outline-hidden">
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase text-black mb-1">Jam Selesai (HH:MM) *</label>
                        <input type="time" name="end_time" id="form_end_time" required
                               class="w-full bg-white border-2 border-black px-3 py-2 text-xs font-mono font-bold text-black focus:outline-hidden">
                    </div>
                </div>

                <!-- Mata Pelajaran -->
                <div>
                    <label class="block text-xs font-black uppercase text-black mb-1">Mata Pelajaran *</label>
                    <select name="subject_id" id="form_subject_id" required
                            class="w-full bg-white border-2 border-black px-3 py-2 text-xs font-bold text-black focus:outline-hidden">
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        @foreach($subjects as $sub)
                            <option value="{{ $sub->id }}">{{ $sub->code }} — {{ $sub->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Guru Pengampu -->
                <div>
                    <label class="block text-xs font-black uppercase text-black mb-1">Guru Pengampu *</label>
                    <select name="teacher_id" id="form_teacher_id" required
                            class="w-full bg-white border-2 border-black px-3 py-2 text-xs font-bold text-black focus:outline-hidden">
                        <option value="">-- Pilih Guru Pengampu --</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}">
                                {{ $t->user->name ?? 'Guru' }} (NIP: {{ $t->nip ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="p-3 bg-[#FFF3BF] border-2 border-black rounded-sm text-[11px] font-semibold text-slate-800">
                    🛡️ <b>Deteksi Bentrok Otomatis:</b> Sistem akan memvalidasi jadwal agar tidak bertabrakan dengan jadwal guru di kelas lain atau jadwal kelas lain di jam yang sama.
                </div>
            </div>

            <!-- Fixed Footer Action Buttons -->
            <div class="p-3.5 sm:p-4 border-t-2 border-black bg-slate-50 flex items-center justify-end gap-2.5 shrink-0">
                <button type="button" onclick="closeScheduleModal()" class="neo-btn bg-slate-200 text-black text-xs font-bold px-4 py-2 hover:bg-slate-300 cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#20C997] text-black text-xs font-black px-5 py-2 hover:bg-emerald-400 cursor-pointer">
                    💾 Simpan Jadwal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Referensi Template Jam Simpatika -->
<div id="templateModal" class="fixed inset-0 z-50 bg-black/60 hidden flex items-center justify-center p-2 sm:p-4" onclick="if(event.target===this) closeTemplateModal()">
    <div class="bg-white neo-box-lg w-full max-w-2xl overflow-hidden animate-in fade-in zoom-in duration-200 flex flex-col max-h-[92vh]">
        <div class="bg-black p-3.5 sm:p-4 text-white flex items-center justify-between shrink-0">
            <h3 class="font-heading font-black text-sm uppercase flex items-center gap-2">
                <span>📋</span> Panduan & Template Model Jam Tatap Muka (Simpatika)
            </h3>
            <button type="button" onclick="closeTemplateModal()" class="font-black text-lg hover:opacity-75 cursor-pointer p-1">✕</button>
        </div>

        <div class="p-4 sm:p-5 space-y-4 overflow-y-auto flex-1">
            <p class="text-xs font-semibold text-slate-700">
                Sesuai regulasi kurikulum dan panduan Simpatika Kemenag, sekolah menyusun alokasi jam tatap muka mingguan dengan durasi 40–45 menit per jam pelajaran:
            </p>

            <!-- Tabel Model Reguler 40 Menit -->
            <div class="border-2 border-black rounded-sm overflow-hidden">
                <div class="bg-[#FFD43B] p-2 text-xs font-black uppercase text-black border-b-2 border-black">
                    1. Model Standar Reguler Pagi (40 Menit / Jam Pelajaran)
                </div>
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-100 font-bold border-b border-black">
                        <tr>
                            <th class="p-2 border-r border-black">Sesi / Jam</th>
                            <th class="p-2 border-r border-black">Rentang Jam</th>
                            <th class="p-2">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/20 font-mono">
                        <tr>
                            <td class="p-2 border-r border-black font-bold">Jam ke-1</td>
                            <td class="p-2 border-r border-black">07:15 - 07:55</td>
                            <td class="p-2 font-sans">Tatap Muka Sesi 1</td>
                        </tr>
                        <tr>
                            <td class="p-2 border-r border-black font-bold">Jam ke-2</td>
                            <td class="p-2 border-r border-black">07:55 - 08:35</td>
                            <td class="p-2 font-sans">Tatap Muka Sesi 2</td>
                        </tr>
                        <tr>
                            <td class="p-2 border-r border-black font-bold">Jam ke-3</td>
                            <td class="p-2 border-r border-black">08:35 - 09:15</td>
                            <td class="p-2 font-sans">Tatap Muka Sesi 3</td>
                        </tr>
                        <tr class="bg-amber-50">
                            <td class="p-2 border-r border-black font-bold">Istirahat 1</td>
                            <td class="p-2 border-r border-black">09:15 - 09:45</td>
                            <td class="p-2 font-sans">Istirahat Pagi (30 Menit)</td>
                        </tr>
                        <tr>
                            <td class="p-2 border-r border-black font-bold">Jam ke-4</td>
                            <td class="p-2 border-r border-black">09:45 - 10:25</td>
                            <td class="p-2 font-sans">Tatap Muka Sesi 4</td>
                        </tr>
                        <tr>
                            <td class="p-2 border-r border-black font-bold">Jam ke-5</td>
                            <td class="p-2 border-r border-black">10:25 - 11:05</td>
                            <td class="p-2 font-sans">Tatap Muka Sesi 5</td>
                        </tr>
                        <tr>
                            <td class="p-2 border-r border-black font-bold">Jam ke-6</td>
                            <td class="p-2 border-r border-black">11:05 - 11:45</td>
                            <td class="p-2 font-sans">Tatap Muka Sesi 6</td>
                        </tr>
                        <tr class="bg-amber-50">
                            <td class="p-2 border-r border-black font-bold">Istirahat 2</td>
                            <td class="p-2 border-r border-black">11:45 - 12:30</td>
                            <td class="p-2 font-sans">Istirahat / Sholat Dzuhur</td>
                        </tr>
                        <tr>
                            <td class="p-2 border-r border-black font-bold">Jam ke-7</td>
                            <td class="p-2 border-r border-black">12:30 - 13:10</td>
                            <td class="p-2 font-sans">Tatap Muka Sesi 7</td>
                        </tr>
                        <tr>
                            <td class="p-2 border-r border-black font-bold">Jam ke-8</td>
                            <td class="p-2 border-r border-black">13:10 - 13:50</td>
                            <td class="p-2 font-sans">Tatap Muka Sesi 8 (Kepulangan)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="p-3 bg-white border-t-2 border-black flex justify-end">
            <button type="button" onclick="closeTemplateModal()" class="neo-btn bg-black text-white text-xs font-bold px-4 py-1.5 cursor-pointer">
                Tutup Panduan
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const slotList = @json($presets['reguler_40']['slots']);

    function applyRangeSlotTime() {
        const fromVal = document.getElementById('quick_slot_from').value;
        const toVal = document.getElementById('quick_slot_to').value;
        const infoBox = document.getElementById('jpCalcInfo');
        const infoText = document.getElementById('jpCalcText');
        const infoBadge = document.getElementById('jpCalcBadge');

        if (fromVal === '') {
            infoBox.classList.add('hidden');
            return;
        }

        let fromIdx = parseInt(fromVal);
        let toIdx = toVal !== '' ? parseInt(toVal) : fromIdx;

        if (toIdx < fromIdx) {
            toIdx = fromIdx;
            document.getElementById('quick_slot_to').value = toIdx;
        }

        const startSlot = slotList[fromIdx];
        const endSlot = slotList[toIdx];

        document.getElementById('form_start_time').value = startSlot.start;
        document.getElementById('form_end_time').value = endSlot.end;

        const totalJp = (toIdx - fromIdx + 1);
        infoBox.classList.remove('hidden');
        infoText.textContent = `✅ Terpilih: ${startSlot.label} s.d ${endSlot.label} (${startSlot.start} - ${endSlot.end})`;
        infoBadge.textContent = `${totalJp} Jam Pelajaran (JP)`;
    }

    function applyPresetBlock(fromIdx, toIdx) {
        document.getElementById('quick_slot_from').value = fromIdx;
        document.getElementById('quick_slot_to').value = toIdx;
        applyRangeSlotTime();
    }

    function openCreateScheduleModal(dayOfWeek = null) {
        document.getElementById('modalTitle').innerHTML = '<span>📅</span> Tambah Jadwal Pelajaran';
        document.getElementById('scheduleForm').action = "{{ route('admin.schedules.store') }}";
        document.getElementById('methodContainer').innerHTML = '';
        
        document.getElementById('form_subject_id').value = '';
        document.getElementById('form_teacher_id').value = '';
        document.getElementById('form_start_time').value = '';
        document.getElementById('form_end_time').value = '';
        document.getElementById('quick_slot_from').value = '';
        document.getElementById('quick_slot_to').value = '';
        document.getElementById('jpCalcInfo').classList.add('hidden');
        
        if (dayOfWeek) {
            document.getElementById('form_day_of_week').value = dayOfWeek;
        } else {
            document.getElementById('form_day_of_week').value = '';
        }

        document.getElementById('scheduleModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function openEditScheduleModal(sch) {
        document.getElementById('modalTitle').innerHTML = '<span>✏️</span> Edit Jadwal Pelajaran';
        document.getElementById('scheduleForm').action = `/admin/schedules/${sch.id}`;
        document.getElementById('methodContainer').innerHTML = '@method("PUT")';

        document.getElementById('form_day_of_week').value = sch.day_of_week;
        document.getElementById('form_subject_id').value = sch.subject_id;
        document.getElementById('form_teacher_id').value = sch.teacher_id;
        document.getElementById('form_start_time').value = sch.start_time.substring(0, 5);
        document.getElementById('form_end_time').value = sch.end_time.substring(0, 5);

        // Reset quick dropdowns
        document.getElementById('quick_slot_from').value = '';
        document.getElementById('quick_slot_to').value = '';
        document.getElementById('jpCalcInfo').classList.add('hidden');

        document.getElementById('scheduleModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeScheduleModal() {
        document.getElementById('scheduleModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function openTemplateModal() {
        document.getElementById('templateModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeTemplateModal() {
        document.getElementById('templateModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeScheduleModal();
            closeTemplateModal();
        }
    });

    function confirmDeleteSchedule(e) {
        e.preventDefault();
        const form = e.target;
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus Jadwal Pelajaran?',
                text: 'Jadwal yang dihapus tidak dapat dipulihkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#FF6B6B',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        } else {
            if (confirm('Hapus jadwal pelajaran ini? Jadwal yang dihapus tidak dapat dipulihkan.')) {
                form.submit();
            }
        }
        return false;
    }

    // Tampilkan SweetAlert2 jika ada error bentrok dari redirect server
    @if(session('conflict_error'))
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Jadwal Bentrok!',
                text: @json(session('conflict_error')),
                confirmButtonColor: '#FF6B6B',
                confirmButtonText: 'Perbaiki Jadwal'
            });
        } else {
            alert('Jadwal Bentrok: ' + @json(session('conflict_error')));
        }
    });
    @endif
</script>
@endpush
