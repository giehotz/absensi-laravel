@extends('layouts.admin')

@section('title', 'Jadwal Pelajaran Kelas')
@section('page-title', 'Plot Jadwal Kelas (EMIS GTK)')

@section('content')
<div class="space-y-6">
    <!-- Navigation Tabs -->
    <div class="flex border-b-2 border-black gap-2">
        <a href="{{ route('admin.schedules.index') }}" 
           class="px-5 py-2.5 font-heading font-black text-xs uppercase border-t-2 border-x-2 border-black transition-all bg-white -mb-[2px] border-b-2 border-b-white z-10 text-black shadow-sm">
            📅 Plot Jadwal Kelas
        </a>
        <a href="{{ route('admin.schedules.slots.index') }}" 
           class="px-5 py-2.5 font-heading font-black text-xs uppercase border-t-2 border-x-2 border-black transition-all bg-slate-100 text-slate-600 hover:bg-slate-200">
            ⚙️ Template Jam Simpatika / EMIS GTK
        </a>
    </div>

    <!-- Header Title Card -->
    <div class="bg-[#FFF3BF] neo-box-lg p-6 sm:p-7 text-black relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="space-y-1.5 z-10">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="neo-badge bg-[#5294FF] text-white">ADMINISTRATOR</span>
                <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border border-black">
                    {{ $academicYear->name ?? 'Tahun Ajaran Aktif' }}
                </span>
                <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border border-black hidden sm:inline-block">
                    Semester {{ ucfirst($academicYear->semester ?? 'Ganjil') }}
                </span>
                @if($selectedClass)
                    <span class="text-xs font-heading font-black bg-[#CC5DE8] text-white px-2.5 py-0.5 border border-black">
                        Kelas {{ $selectedClass->name }}
                    </span>
                @endif
            </div>
            <h1 class="font-heading text-2xl sm:text-3xl font-black tracking-tight text-black uppercase">
                PLOT JADWAL KELAS MINGGUAN
            </h1>
            <p class="text-xs sm:text-sm font-semibold text-slate-800 max-w-2xl">
                Plotting jadwal tatap muka kelas berbasis matriks mingguan EMIS GTK. Dilengkapi deteksi bentrok jadwal guru dan dukungan multi-jam pelajaran.
            </p>
        </div>

        <div class="z-10 flex flex-wrap gap-2 shrink-0">
            <button type="button" onclick="window.print()" class="neo-btn bg-white hover:bg-slate-100 text-black text-xs font-bold px-3.5 py-2.5 flex items-center gap-1.5 cursor-pointer shadow-[2px_2px_0px_0px_#000]">
                <span>🖨️</span> Cetak Jadwal
            </button>
            <a href="{{ route('admin.schedules.slots.index') }}" class="neo-btn bg-white hover:bg-slate-100 text-black text-xs font-bold px-3.5 py-2.5 flex items-center gap-1.5 cursor-pointer shadow-[2px_2px_0px_0px_#000]">
                <span>⚙️</span> Atur Template Slot
            </a>
            <button type="button" onclick="openCreateScheduleModal()" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black text-xs font-black px-4 py-2.5 flex items-center gap-1.5 cursor-pointer shadow-[2px_2px_0px_0px_#000]">
                <span>➕</span> Tambah Jadwal Baru
            </button>
        </div>
    </div>

    <!-- Alert / Status Notifikasi -->
    @if(session('success'))
    <div class="bg-[#D3F9D8] border-2 border-black p-4 neo-box text-emerald-950 font-bold text-xs flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span>✅</span> {{ session('success') }}
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-black font-black hover:opacity-75">✕</button>
    </div>
    @endif

    @if(session('conflict_error'))
    <div class="bg-[#FFE3E3] border-2 border-black p-4 neo-box text-rose-950 font-bold text-xs flex items-start gap-2.5">
        <span class="text-lg">⚠️</span>
        <div class="flex-1">
            <span class="font-black uppercase block mb-0.5">Jadwal Gagal Disimpan (Bentrok)</span>
            <span>{{ session('conflict_error') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-black font-black hover:opacity-75">✕</button>
    </div>
    @endif

    @php
        // Hitung total jam pelajaran (JP) terjadwal untuk kelas yang dipilih
        $totalScheduledMinutes = 0;
        foreach ($schedules as $sch) {
            $startM = \Carbon\Carbon::parse($sch->start_time);
            $endM = \Carbon\Carbon::parse($sch->end_time);
            $totalScheduledMinutes += $startM->diffInMinutes($endM);
        }
        $totalScheduledJp = max(0, (int) round($totalScheduledMinutes / 40));
        $targetJp = 38; // Target standar JP per minggu Kurikulum Merdeka / Kemenag
        $percentJp = min(100, (int) round(($totalScheduledJp / max(1, $targetJp)) * 100));
        $shortageJp = max(0, $targetJp - $totalScheduledJp);
    @endphp

    <!-- Toolbar: Pemilihan Kelas & Kesesuaian Regulasi EMIS GTK -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        <!-- Selector Kelas & Info Wali -->
        <div class="lg:col-span-5 bg-white neo-box p-4 flex flex-col justify-between space-y-3">
            <form method="GET" action="{{ route('admin.schedules.index') }}" class="space-y-1.5">
                <label for="school_class_id" class="block text-xs font-black uppercase text-black flex items-center justify-between">
                    <span>Pilih Kelas:</span>
                    <span class="text-[10px] font-mono text-slate-500">{{ $classes->count() }} Rombel Tersedia</span>
                </label>
                <div class="flex gap-2">
                    <select name="school_class_id" id="school_class_id" onchange="this.form.submit()"
                            class="flex-1 bg-slate-50 border-2 border-black px-3 py-2 text-xs font-black text-black focus:outline-hidden rounded">
                        @foreach($classes as $cls)
                            <option value="{{ $cls->id }}" {{ $selectedClassId == $cls->id ? 'selected' : '' }}>
                                Kelas {{ $cls->name }} (Tingkat {{ $cls->level }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>

            @if($selectedClass)
                <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-200 text-xs">
                    <span class="text-slate-600 font-bold">Wali Kelas:</span>
                    <span class="font-heading font-black text-slate-900 bg-[#E7F5FF] border border-black px-2 py-0.5 rounded truncate max-w-[240px]">
                        👨‍🏫 {{ $selectedClass->homeroomTeacher->user->name ?? 'Belum Ditugaskan' }}
                    </span>
                </div>
            @endif
        </div>

        <!-- Kesesuaian Regulasi (Target JP vs Terjadwal) ala EMIS GTK -->
        <div class="lg:col-span-7 bg-white neo-box p-4 flex flex-col justify-between space-y-3">
            <div class="flex items-center justify-between gap-2">
                <div>
                    <h4 class="font-heading font-black text-xs uppercase text-black flex items-center gap-1.5">
                        <span>📊</span> Kesesuaian Beban Regulasi KBM
                    </h4>
                    <p class="text-[11px] font-medium text-slate-600">
                        Target {{ $targetJp }} JP · Terjadwal <b>{{ $totalScheduledJp }} JP</b> · Kurang <b>{{ $shortageJp }} JP</b>
                    </p>
                </div>
                <div>
                    @if($shortageJp === 0)
                        <span class="neo-badge bg-[#20C997] text-black font-black text-[11px]">
                            ✅ Lengkap (100%)
                        </span>
                    @else
                        <span class="neo-badge bg-[#FFF3BF] text-amber-950 font-black text-[11px]">
                            ⚠️ Kurang {{ $shortageJp }} JP ({{ $percentJp }}%)
                        </span>
                    @endif
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="space-y-1">
                <div class="w-full bg-slate-100 border-2 border-black h-4 rounded overflow-hidden p-0.5">
                    <div class="h-full {{ $shortageJp === 0 ? 'bg-[#20C997]' : 'bg-[#5294FF]' }} transition-all duration-500 rounded-xs" 
                         style="width: {{ $percentJp }}%;"></div>
                </div>
                <div class="flex justify-between text-[10px] font-mono font-bold text-slate-500">
                    <span>0 JP</span>
                    <span>50%</span>
                    <span>Target: {{ $targetJp }} JP</span>
                </div>
            </div>
        </div>
    </div>

    @if(!$selectedClass)
        <div class="bg-white neo-box p-12 text-center text-slate-500 space-y-2">
            <div class="text-4xl">🏫</div>
            <div class="text-base font-black text-black">Belum Ada Data Rombel / Kelas</div>
            <p class="text-xs">Silakan tambahkan data kelas terlebih dahulu di menu Data Kelas.</p>
        </div>
    @else
        <!-- Matriks Mingguan Jadwal Pelajaran (Senin s.d. Sabtu) ala EMIS GTK -->
        <div class="bg-white neo-box overflow-hidden">
            <div class="p-4 border-b-2 border-black bg-[#FFF9DB] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="font-heading font-black text-sm uppercase text-black flex items-center gap-1.5">
                        <span>🗓️</span> MATRIKS JADWAL KELAS {{ $selectedClass->name }} (EMIS GTK)
                    </h3>
                    <p class="text-[11px] font-semibold text-slate-600">
                        Klik tombol <b>[+]</b> pada slot KBM kosong untuk menambah jadwal. Klik pada jadwal terisi untuk melihat detail, mengubah, atau menghapus.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-mono font-bold bg-white px-2.5 py-1 border border-black">
                        Total: <b>{{ $schedules->count() }} Sesi</b> ({{ $totalScheduledJp }} JP)
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[980px]">
                    <thead>
                        <tr class="bg-slate-100 border-b-2 border-black text-xs font-black uppercase text-black">
                            <th class="p-3 border-r-2 border-black text-center w-24 bg-slate-200/70">Jam</th>
                            @foreach([1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'] as $dNum => $dName)
                                @php
                                    $daySlotsCount = ($allSlotsByDay[$dNum] ?? collect())->count();
                                    $daySchedCount = $schedules->where('day_of_week', $dNum)->count();
                                @endphp
                                <th class="p-3 border-r-2 border-black text-center min-w-[150px] {{ $dNum === 5 ? 'bg-emerald-50 text-emerald-950' : '' }}">
                                    <div>{{ $dName }}</div>
                                    <div class="text-[10px] font-mono font-semibold opacity-75">
                                        {{ $daySchedCount }} Mapel ({{ $daySlotsCount }} Jam)
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-black text-xs font-medium">
                        @for($jam = 1; $jam <= $maxJam; $jam++)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <!-- Nomor Jam -->
                                <td class="p-2.5 border-r-2 border-black text-center font-heading font-black text-xs bg-slate-100">
                                    <div>Jam {{ $jam }}</div>
                                </td>

                                <!-- Kolom Hari 1 s.d. 6 (Senin s.d. Sabtu) -->
                                @for($day = 1; $day <= 6; $day++)
                                    @php
                                        $daySlots = $allSlotsByDay[$day] ?? collect();
                                        $slot = $daySlots->firstWhere('jam_ke', $jam);
                                    @endphp

                                    <td class="p-1.5 border-r-2 border-black text-center align-middle">
                                        @if(!$slot)
                                            <!-- Jam di luar batas aktif hari tersebut (Pulang) -->
                                            <div class="border-2 border-dashed border-slate-200 p-2 rounded flex flex-col items-center justify-center h-24 text-slate-400 bg-slate-50/70">
                                                <span class="text-[10px] font-bold uppercase tracking-wider">Pulang</span>
                                                <span class="text-[9px] text-slate-400 mt-0.5">Non-Aktif</span>
                                            </div>
                                        @elseif($slot->isNonKbm())
                                            <!-- Slot Non-KBM (Upacara, Istirahat, Pembiasaan, Senam, Religi) -->
                                            @php
                                                $colorConfig = \App\Models\SlotTemplate::KEGIATAN_COLORS[$slot->k_jadwal] ?? [
                                                    'bg' => 'bg-slate-100',
                                                    'text' => 'text-slate-900',
                                                    'badge_bg' => 'bg-slate-300',
                                                    'badge_text' => 'text-black',
                                                    'name' => 'Kegiatan',
                                                ];
                                            @endphp
                                            <div class="border-2 border-black p-2 rounded flex flex-col justify-between h-24 shadow-[2px_2px_0px_0px_#000] {{ $colorConfig['bg'] }}">
                                                <div class="flex items-center justify-between text-[10px] font-mono font-bold {{ $colorConfig['text'] }}">
                                                    <span>{{ $slot->getShortStartTime() }} - {{ $slot->getShortEndTime() }}</span>
                                                    <span class="w-2 h-2 rounded-full border border-black {{ $colorConfig['badge_bg'] }}"></span>
                                                </div>
                                                <div class="font-heading font-black text-xs {{ $colorConfig['text'] }} truncate my-auto">
                                                    {{ $slot->name ?: $slot->getCategoryLabel() }}
                                                </div>
                                                <div class="text-[9px] font-bold text-slate-600 truncate flex items-center justify-center gap-1">
                                                    <span>🔒</span> {{ $colorConfig['name'] }}
                                                </div>
                                            </div>
                                        @else
                                            <!-- Slot KBM: Cek apakah ada jadwal aktif yang mencakup slot ini -->
                                            @php
                                                $slotStart = $slot->getShortStartTime();
                                                $slotEnd = $slot->getShortEndTime();

                                                $coveringSchedule = $schedules->first(function ($sch) use ($day, $slotStart, $slotEnd) {
                                                    if ((int)$sch->day_of_week !== $day) return false;
                                                    $schStart = substr((string)$sch->start_time, 0, 5);
                                                    $schEnd = substr((string)$sch->end_time, 0, 5);
                                                    return ($schStart < $slotEnd) && ($schEnd > $slotStart);
                                                });
                                            @endphp

                                            @if($coveringSchedule)
                                                @php
                                                    $sch = $coveringSchedule;
                                                    $schStart = substr((string)$sch->start_time, 0, 5);
                                                    $schEnd = substr((string)$sch->end_time, 0, 5);
                                                    $diffMin = \Carbon\Carbon::parse($sch->start_time)->diffInMinutes(\Carbon\Carbon::parse($sch->end_time));
                                                    $jp = max(1, (int) round($diffMin / 40));
                                                @endphp
                                                <!-- Kartu Jadwal Pelajaran Aktif -->
                                                <div onclick="showScheduleDetail({{ json_encode([
                                                    'id' => $sch->id,
                                                    'subject_name' => $sch->subject->name ?? 'Mapel',
                                                    'subject_code' => $sch->subject->code ?? '-',
                                                    'teacher_name' => $sch->teacher->user->name ?? 'Guru',
                                                    'teacher_nip' => $sch->teacher->nip ?? '-',
                                                    'day_name' => $daysMap[$day],
                                                    'day_of_week' => $day,
                                                    'start_time' => $schStart,
                                                    'end_time' => $schEnd,
                                                    'jp' => $jp,
                                                    'class_name' => $selectedClass->name,
                                                    'school_class_id' => $sch->school_class_id,
                                                    'subject_id' => $sch->subject_id,
                                                    'teacher_id' => $sch->teacher_id
                                                ]) }})"
                                                     class="border-2 border-black bg-white hover:bg-[#FFF9DB] p-2 rounded flex flex-col justify-between h-24 shadow-[2px_2px_0px_0px_#000] hover:shadow-[3px_3px_0px_0px_#000] transition-all cursor-pointer group text-left">
                                                    <div class="flex items-center justify-between text-[10px] font-mono font-bold text-slate-800 border-b border-slate-200 pb-0.5">
                                                        <span>{{ $schStart }} - {{ $schEnd }}</span>
                                                        <span class="neo-badge bg-[#FFD43B] text-black text-[9px] font-black px-1 py-0.2">
                                                            {{ $jp }} JP
                                                        </span>
                                                    </div>
                                                    <div class="my-auto">
                                                        <div class="font-heading font-black text-xs text-black group-hover:text-blue-900 truncate">
                                                            {{ $sch->subject->name ?? 'Mapel' }}
                                                        </div>
                                                        <div class="text-[10px] font-semibold text-slate-600 truncate flex items-center gap-1 mt-0.5">
                                                            <span>👤</span> {{ $sch->teacher->user->name ?? 'Guru' }}
                                                        </div>
                                                    </div>
                                                    <div class="text-[9px] font-bold text-slate-500 text-right opacity-0 group-hover:opacity-100 transition-opacity">
                                                        Detail ↗
                                                    </div>
                                                </div>
                                            @else
                                                <!-- Slot KBM Kosong (Tersedia untuk Diisi) -->
                                                <div onclick="openCreateScheduleModal({{ $day }}, '{{ $slotStart }}', '{{ $slotEnd }}', {{ $jam }})"
                                                     class="border-2 border-dashed border-slate-300 hover:border-black p-2 rounded flex flex-col items-center justify-between h-24 text-slate-500 hover:text-black hover:bg-[#FFFDF5] transition-all cursor-pointer group">
                                                    <div class="flex items-center justify-between w-full text-[10px] font-mono font-bold text-slate-400 group-hover:text-slate-700">
                                                        <span>{{ $slotStart }} - {{ $slotEnd }}</span>
                                                        <span class="text-[9px]">Kosong</span>
                                                    </div>
                                                    <div class="flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 group-hover:bg-[#20C997] group-hover:text-black text-slate-700 border border-slate-300 group-hover:border-black rounded text-[11px] font-black transition-all">
                                                        <span>+</span>
                                                        <span>Isi Jadwal</span>
                                                    </div>
                                                    <div class="text-[9px] font-mono text-slate-400">
                                                        Jam ke-{{ $jam }}
                                                    </div>
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t-2 border-black bg-slate-50 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-600">
                    💡 Gunakan fitur Multi-Jam pada formulir untuk menugaskan mata pelajaran sekaligus 2-3 jam tatap muka.
                </span>
                <button type="button" onclick="openCreateScheduleModal()" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black text-xs font-black px-4 py-2 flex items-center gap-1.5 cursor-pointer shadow-[2px_2px_0px_0px_#000]">
                    <span>➕</span> Tambah Jadwal Baru
                </button>
            </div>
        </div>
    @endif

    <!-- Modal Detail Jadwal (Lihat, Edit, Hapus) -->
    <div id="detailScheduleModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white neo-box-lg max-w-md w-full p-6 space-y-4 relative" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between border-b-2 border-black pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-2xl">📋</span>
                    <div>
                        <h3 class="font-heading font-black text-base text-black uppercase" id="detailModalTitle">
                            Detail Jadwal Pelajaran
                        </h3>
                        <p class="text-xs font-semibold text-slate-600" id="detailModalSubtitle">
                            Informasi Sesi Mengajar
                        </p>
                    </div>
                </div>
                <button type="button" onclick="closeDetailModal()" class="text-black font-black text-lg hover:opacity-75 cursor-pointer">✕</button>
            </div>

            <!-- Detail Info Card -->
            <div class="bg-slate-50 border-2 border-black p-4 rounded space-y-2.5 text-xs">
                <div class="flex items-start justify-between border-b border-slate-200 pb-2">
                    <span class="text-slate-600 font-bold">Mata Pelajaran:</span>
                    <span class="font-heading font-black text-sm text-black text-right" id="detailSubjectName">-</span>
                </div>
                <div class="flex items-start justify-between border-b border-slate-200 pb-2">
                    <span class="text-slate-600 font-bold">Guru Pengampu:</span>
                    <div class="text-right">
                        <span class="font-bold text-black block" id="detailTeacherName">-</span>
                        <span class="font-mono text-[10px] text-slate-500 block" id="detailTeacherNip">NIP: -</span>
                    </div>
                </div>
                <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                    <span class="text-slate-600 font-bold">Rombel / Kelas:</span>
                    <span class="font-bold text-black" id="detailClassName">-</span>
                </div>
                <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                    <span class="text-slate-600 font-bold">Hari & Waktu:</span>
                    <span class="font-mono font-black text-blue-900 bg-[#E7F5FF] border border-black px-2 py-0.5 rounded" id="detailTimeRange">-</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-600 font-bold">Beban JP:</span>
                    <span class="neo-badge bg-[#FFD43B] text-black font-black text-xs" id="detailJpBadge">1 JP</span>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center justify-between border-t-2 border-black pt-3">
                <form id="deleteScheduleForm" method="POST" action="" onsubmit="return false;">
                    @csrf
                    @method('DELETE')
                    <button type="button" onclick="confirmDeleteSchedule()" class="neo-btn bg-[#FFE3E3] hover:bg-[#ffc9c9] text-rose-950 text-xs font-bold px-3 py-2 cursor-pointer border border-black flex items-center gap-1">
                        <span>🗑️</span> Hapus
                    </button>
                </form>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="closeDetailModal()" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-xs font-bold px-3.5 py-2 cursor-pointer">
                        Tutup
                    </button>
                    <button type="button" onclick="triggerEditFromDetail()" class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black text-xs font-black px-4 py-2 cursor-pointer shadow-[2px_2px_0px_0px_#000] flex items-center gap-1">
                        <span>✏️</span> Edit Jadwal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form Tambah / Edit Jadwal Pelajaran (Dengan Multi-Jam Support) -->
    <div id="scheduleModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white neo-box-lg max-w-lg w-full p-6 space-y-4 max-h-[92vh] overflow-y-auto" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between border-b-2 border-black pb-3">
                <h3 class="font-heading font-black text-base text-black flex items-center gap-1.5" id="modalTitle">
                    <span>📅</span> Tambah Jadwal Pelajaran
                </h3>
                <button type="button" onclick="closeScheduleModal()" class="text-black font-black text-lg hover:opacity-75 cursor-pointer">✕</button>
            </div>

            <form id="scheduleForm" method="POST" action="{{ route('admin.schedules.store') }}" class="space-y-4">
                @csrf
                <div id="methodContainer"></div>
                <input type="hidden" name="school_class_id" value="{{ $selectedClassId }}">

                <!-- Pilih Hari -->
                <div>
                    <label for="form_day_of_week" class="block text-xs font-black uppercase text-black mb-1">
                        Hari Tatap Muka: <span class="text-rose-600">*</span>
                    </label>
                    <select name="day_of_week" id="form_day_of_week" required onchange="onDayChange()"
                            class="w-full border-2 border-black rounded p-2 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-[#FFD43B]">
                        <option value="">-- Pilih Hari --</option>
                        @foreach($daysMap as $dNum => $dName)
                            <option value="{{ $dNum }}">{{ $dName }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Multi-Jam Selector (Pilih Rentang Jam ke-X s.d. Jam ke-Y) -->
                <div class="bg-[#FFF9DB] border-2 border-black p-3.5 rounded space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-black uppercase tracking-wider text-black flex items-center gap-1">
                            <span>⏱️</span> Pilih Rentang Jam Pelajaran (Multi-Jam):
                        </span>
                        <span id="multiJamBadge" class="text-[10px] font-mono font-black bg-[#FFD43B] text-black border border-black px-1.5 py-0.2 rounded">
                            1 JP
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-700 uppercase mb-0.5">Mulai Jam ke-:</label>
                            <select id="select_jam_start" onchange="onJamRangeChange()" class="w-full bg-white border-2 border-black rounded p-1.5 text-xs font-bold">
                                <option value="">-- Jam Awal --</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-700 uppercase mb-0.5">Sampai Jam ke-:</label>
                            <select id="select_jam_end" onchange="onJamRangeChange()" class="w-full bg-white border-2 border-black rounded p-1.5 text-xs font-bold">
                                <option value="">-- Jam Akhir --</option>
                            </select>
                        </div>
                    </div>

                    <div id="rangeTimeSummary" class="text-[11px] font-mono font-bold text-blue-900 bg-white border border-black p-1.5 rounded flex items-center justify-between">
                        <span>Rentang Waktu: <b id="rangeTimeDisplay">07:00 - 07:40</b></span>
                        <span class="text-[10px] text-slate-500">Auto-Sync</span>
                    </div>
                </div>

                <!-- Input Waktu Eksplisit (Sinkron Otomatis dari Slot) -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="form_start_time" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Jam Mulai (HH:mm): <span class="text-rose-600">*</span>
                        </label>
                        <input type="time" name="start_time" id="form_start_time" required
                               class="w-full border-2 border-black rounded p-2 text-xs font-mono font-bold bg-white">
                    </div>
                    <div>
                        <label for="form_end_time" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Jam Selesai (HH:mm): <span class="text-rose-600">*</span>
                        </label>
                        <input type="time" name="end_time" id="form_end_time" required
                               class="w-full border-2 border-black rounded p-2 text-xs font-mono font-bold bg-white">
                    </div>
                </div>

                <!-- Pilih Mata Pelajaran -->
                <div>
                    <label for="form_subject_id" class="block text-xs font-black uppercase text-black mb-1">
                        Mata Pelajaran: <span class="text-rose-600">*</span>
                    </label>
                    <select name="subject_id" id="form_subject_id" required
                            class="w-full border-2 border-black rounded p-2 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-[#FFD43B]">
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        @foreach($subjects as $subj)
                            <option value="{{ $subj->id }}">{{ $subj->name }} ({{ $subj->code }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Pilih Guru Pengampu -->
                <div>
                    <label for="form_teacher_id" class="block text-xs font-black uppercase text-black mb-1">
                        Guru Pengampu: <span class="text-rose-600">*</span>
                    </label>
                    <select name="teacher_id" id="form_teacher_id" required onchange="onTeacherChange()"
                            class="w-full border-2 border-black rounded p-2 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-[#FFD43B]">
                        <option value="">-- Pilih Guru --</option>
                        @foreach($teachers as $tch)
                            <option value="{{ $tch->id }}">
                                {{ $tch->user->name ?? 'Guru' }} (NIP: {{ $tch->nip ?? '-' }})
                            </option>
                        @endforeach
                    </select>

                    <div id="teacher_assignment_hint" class="mt-1 text-[11px] font-bold text-blue-900 bg-[#E7F5FF] border border-black p-2 rounded hidden">
                        <span>ℹ️ Guru ini memiliki penugasan mata pelajaran khusus.</span>
                    </div>
                </div>

                <!-- Tombol Submit -->
                <div class="flex items-center justify-end gap-2 border-t-2 border-black pt-3">
                    <button type="button" onclick="closeScheduleModal()" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-xs font-bold px-4 py-2 cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black text-xs font-black px-5 py-2 cursor-pointer shadow-[2px_2px_0px_0px_#000]">
                        <span>💾</span> Simpan Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const allSlotsByDay = @json($allSlotsByDay ?? []);
    const teacherAssignmentsMap = @json($teacherAssignmentsMap ?? []);
    const currentClassId = {{ (int) ($selectedClassId ?? 0) }};

    let activeSelectedSchedule = null;

    // Show Detail Modal when clicking on a scheduled cell
    function showScheduleDetail(schData) {
        activeSelectedSchedule = schData;

        document.getElementById('detailSubjectName').textContent = schData.subject_name;
        document.getElementById('detailTeacherName').textContent = schData.teacher_name;
        document.getElementById('detailTeacherNip').textContent = 'NIP: ' + schData.teacher_nip;
        document.getElementById('detailClassName').textContent = 'Kelas ' + schData.class_name;
        document.getElementById('detailTimeRange').textContent = `${schData.day_name}, ${schData.start_time} - ${schData.end_time}`;
        document.getElementById('detailJpBadge').textContent = schData.jp + ' JP';

        document.getElementById('deleteScheduleForm').action = `/admin/schedules/${schData.id}`;

        document.getElementById('detailScheduleModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeDetailModal() {
        document.getElementById('detailScheduleModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        activeSelectedSchedule = null;
    }

    function confirmDeleteSchedule() {
        const form = document.getElementById('deleteScheduleForm');
        if (!form || !form.action) return;

        const subj = document.getElementById('detailSubjectName')?.textContent || 'mata pelajaran ini';
        const cls = document.getElementById('detailClassName')?.textContent || '';
        const timeRange = document.getElementById('detailTimeRange')?.textContent || '';

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus Jadwal Pelajaran?',
                html: `Apakah Anda yakin ingin menghapus jadwal <b>${subj}</b> (${cls}) pada <b>${timeRange}</b>?<br><span class="text-xs text-slate-500 mt-1 block">Slot jam terkait akan dikosongkan.</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#FF6B6B',
                cancelButtonColor: '#0F172A',
                confirmButtonText: 'Ya, Hapus Jadwal!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                focusCancel: true,
                customClass: {
                    popup: 'neo-box-lg border-3 border-black',
                    confirmButton: 'neo-btn border-2 border-black font-black',
                    cancelButton: 'neo-btn border-2 border-black font-bold'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        } else {
            if (confirm(`Apakah Anda yakin ingin menghapus jadwal ${subj}? Jadwal yang dihapus akan mengosongkan slot jam terkait.`)) {
                form.submit();
            }
        }
    }

    function triggerEditFromDetail() {
        if (!activeSelectedSchedule) return;
        const sch = activeSelectedSchedule;
        closeDetailModal();
        openEditScheduleModal(sch);
    }

    // Open Create Schedule Modal
    function openCreateScheduleModal(dayOfWeek = null, startTime = null, endTime = null, preferredJam = null) {
        document.getElementById('modalTitle').innerHTML = '<span>📅</span> Tambah Jadwal Pelajaran';
        document.getElementById('scheduleForm').action = "{{ route('admin.schedules.store') }}";
        document.getElementById('methodContainer').innerHTML = '';

        document.getElementById('form_subject_id').value = '';
        document.getElementById('form_teacher_id').value = '';

        if (dayOfWeek) {
            document.getElementById('form_day_of_week').value = dayOfWeek;
        } else {
            document.getElementById('form_day_of_week').value = '';
        }

        populateJamOptions(dayOfWeek, preferredJam);

        if (startTime && endTime) {
            document.getElementById('form_start_time').value = startTime;
            document.getElementById('form_end_time').value = endTime;
        }

        onTeacherChange();
        document.getElementById('scheduleModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    // Open Edit Schedule Modal
    function openEditScheduleModal(sch) {
        document.getElementById('modalTitle').innerHTML = '<span>✏️</span> Edit Jadwal Pelajaran';
        document.getElementById('scheduleForm').action = `/admin/schedules/${sch.id}`;
        document.getElementById('methodContainer').innerHTML = '@method("PUT")';

        document.getElementById('form_day_of_week').value = sch.day_of_week;
        populateJamOptions(sch.day_of_week);

        document.getElementById('form_teacher_id').value = sch.teacher_id;
        onTeacherChange();

        document.getElementById('form_subject_id').value = sch.subject_id;
        document.getElementById('form_start_time').value = sch.start_time;
        document.getElementById('form_end_time').value = sch.end_time;

        syncRangeFromCustomTimes(sch.day_of_week, sch.start_time, sch.end_time);

        document.getElementById('scheduleModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeScheduleModal() {
        document.getElementById('scheduleModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function onDayChange() {
        const day = document.getElementById('form_day_of_week').value;
        populateJamOptions(day);
    }

    // Populate dropdowns for Multi-Jam range
    function populateJamOptions(day, preferredJam = null) {
        const startSel = document.getElementById('select_jam_start');
        const endSel = document.getElementById('select_jam_end');

        startSel.innerHTML = '<option value="">-- Jam Awal --</option>';
        endSel.innerHTML = '<option value="">-- Jam Akhir --</option>';

        if (!day || !allSlotsByDay[day] || allSlotsByDay[day].length === 0) {
            return;
        }

        // Only include KBM slots
        const kbmSlots = allSlotsByDay[day].filter(s => parseInt(s.k_jadwal) === 0);

        kbmSlots.forEach((s) => {
            const label = `Jam ke-${s.jam_ke} (${(s.start_time||'').substring(0,5)} - ${(s.end_time||'').substring(0,5)})`;
            startSel.innerHTML += `<option value="${s.jam_ke}" data-start="${(s.start_time||'').substring(0,5)}" data-end="${(s.end_time||'').substring(0,5)}">${label}</option>`;
            endSel.innerHTML += `<option value="${s.jam_ke}" data-start="${(s.start_time||'').substring(0,5)}" data-end="${(s.end_time||'').substring(0,5)}">${label}</option>`;
        });

        if (preferredJam) {
            startSel.value = preferredJam;
            endSel.value = preferredJam;
            onJamRangeChange();
        } else if (kbmSlots.length > 0) {
            startSel.value = kbmSlots[0].jam_ke;
            endSel.value = kbmSlots[0].jam_ke;
            onJamRangeChange();
        }
    }

    function onJamRangeChange() {
        const day = document.getElementById('form_day_of_week').value;
        const startSel = document.getElementById('select_jam_start');
        const endSel = document.getElementById('select_jam_end');

        const startJam = parseInt(startSel.value);
        let endJam = parseInt(endSel.value);

        if (!startJam) return;

        if (!endJam || endJam < startJam) {
            endJam = startJam;
            endSel.value = endJam;
        }

        const daySlots = allSlotsByDay[day] || [];
        const startSlot = daySlots.find(s => parseInt(s.jam_ke) === startJam);
        const endSlot = daySlots.find(s => parseInt(s.jam_ke) === endJam);

        if (startSlot && endSlot) {
            const startVal = (startSlot.start_time || '07:00').substring(0, 5);
            const endVal = (endSlot.end_time || '07:40').substring(0, 5);

            document.getElementById('form_start_time').value = startVal;
            document.getElementById('form_end_time').value = endVal;

            const jpCount = (endJam - startJam + 1);
            document.getElementById('multiJamBadge').textContent = jpCount + ' JP';
            document.getElementById('rangeTimeDisplay').textContent = `${startVal} - ${endVal}`;
        }
    }

    function syncRangeFromCustomTimes(day, start, end) {
        if (!day || !allSlotsByDay[day]) return;
        const daySlots = allSlotsByDay[day] || [];
        const startSlot = daySlots.find(s => (s.start_time || '').substring(0, 5) === start);
        const endSlot = daySlots.find(s => (s.end_time || '').substring(0, 5) === end);

        if (startSlot) {
            document.getElementById('select_jam_start').value = startSlot.jam_ke;
        }
        if (endSlot) {
            document.getElementById('select_jam_end').value = endSlot.jam_ke;
        }
        onJamRangeChange();
    }

    function onTeacherChange() {
        const teacherId = document.getElementById('form_teacher_id').value;
        const hintEl = document.getElementById('teacher_assignment_hint');
        const subjectSelect = document.getElementById('form_subject_id');

        if (!teacherId || !teacherAssignmentsMap[teacherId] || !teacherAssignmentsMap[teacherId].has_restrictions) {
            if (hintEl) hintEl.classList.add('hidden');
            Array.from(subjectSelect.options).forEach(opt => opt.disabled = false);
            return;
        }

        const restriction = teacherAssignmentsMap[teacherId];
        const allowedSubjects = restriction.allowed_subjects || [];
        const allowedClasses = restriction.allowed_classes || [];

        const isClassAllowed = allowedClasses.includes(currentClassId);

        if (hintEl) {
            hintEl.classList.remove('hidden');
            if (!isClassAllowed) {
                hintEl.className = 'mt-1 text-[11px] font-bold text-rose-900 bg-rose-100 border border-black p-2 rounded';
                hintEl.textContent = '⚠️ Peringatan: Guru ini tidak ditugaskan pada rombel/kelas saat ini.';
            } else {
                hintEl.className = 'mt-1 text-[11px] font-bold text-blue-900 bg-[#E7F5FF] border border-black p-2 rounded';
                hintEl.textContent = 'ℹ️ Guru ini memiliki penugasan khusus. Hanya mata pelajaran tertentu yang diizinkan.';
            }
        }

        Array.from(subjectSelect.options).forEach(opt => {
            if (!opt.value) return;
            const isSubjAllowed = allowedSubjects.includes(parseInt(opt.value));
            opt.disabled = (!isClassAllowed || !isSubjAllowed);
        });
    }
</script>

<style>
@media print {
    header, aside, #sidebar, .neo-btn, form, select, .breadcrumbs {
        display: none !important;
    }
    body {
        background: white !important;
        color: black !important;
    }
    .neo-box, .neo-box-lg {
        box-shadow: none !important;
        border-width: 1px !important;
    }
    table {
        min-width: 100% !important;
    }
}
</style>
@endsection
