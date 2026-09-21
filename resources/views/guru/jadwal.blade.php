@extends('layouts.guru')

@section('title', 'Jadwal Pelajaran & Mengajar')
@section('page-title', 'Jadwal Pelajaran KBM')

@section('content')
<div class="space-y-6 print:space-y-4">
    <!-- Header Card -->
    <div class="bg-[#FFF3BF] neo-box-lg p-5 sm:p-7 text-black relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-4 print:hidden">
        <div class="space-y-1.5 z-10">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="neo-badge bg-[#5294FF] text-white font-black">PORTAL GURU</span>
                <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                    {{ $academicYear->name ?? 'Tahun Ajaran Aktif' }}
                </span>
                <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                    Semester {{ ucfirst($academicYear->semester ?? 'Ganjil') }}
                </span>
            </div>
            <h1 class="font-heading text-xl sm:text-2xl font-black tracking-tight text-black uppercase">
                Jadwal Pelajaran & Mengajar
            </h1>
            <p class="text-xs sm:text-sm font-semibold text-slate-800 max-w-2xl">
                Pantau jadwal tatap muka mingguan Anda dan jadwal KBM seluruh rombel/kelas dengan integrasi slot waktu kegiatan madrasah.
            </p>
        </div>

        <div class="z-10 flex flex-wrap items-center gap-2">
            <button type="button" onclick="window.print()" class="neo-btn bg-white hover:bg-slate-100 text-black text-xs font-black px-4 py-2.5 flex items-center gap-1.5 cursor-pointer shadow-[3px_3px_0px_0px_#000]">
                <span>🖨️</span> Cetak Jadwal
            </button>
            <a href="{{ route('guru.attendance.manual') }}" class="neo-btn bg-[#20C997] hover:bg-emerald-400 text-black text-xs font-black px-4 py-2.5 flex items-center gap-1.5 cursor-pointer shadow-[3px_3px_0px_0px_#000]">
                <span>📋</span> Input Presensi Siswa
            </a>
        </div>
    </div>

    <!-- Print-Only Header -->
    <div class="hidden print:block border-b-4 border-black pb-4 mb-4 text-black">
        <div class="text-center space-y-1">
            <h2 class="font-heading font-black text-xl uppercase tracking-wider">JADWAL PELAJARAN MADRASAH</h2>
            <div class="text-xs font-bold font-mono">
                Tahun Ajaran: {{ $academicYear->name ?? '-' }} (Semester {{ ucfirst($academicYear->semester ?? 'Ganjil') }})
            </div>
            <div class="text-xs font-semibold text-slate-700">
                Dicetak pada: {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('l, d F Y H:i') }} WIB
            </div>
        </div>
    </div>

    <!-- Statistik Ringkas Guru -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 print:hidden">
        <div class="bg-white neo-box p-4 border-3 border-black flex items-center justify-between">
            <div>
                <span class="text-[11px] font-black uppercase text-slate-500">Sesi Mengajar Mingguan</span>
                <div class="text-2xl font-heading font-black text-black mt-0.5">
                    {{ $myStats['total_sessions'] }} <span class="text-xs font-bold text-slate-500">Jam/Sesi</span>
                </div>
            </div>
            <div class="w-11 h-11 bg-[#E7F5FF] border-2 border-black flex items-center justify-center text-xl shadow-[2px_2px_0px_0px_#000]">
                📚
            </div>
        </div>

        <div class="bg-white neo-box p-4 border-3 border-black flex items-center justify-between">
            <div>
                <span class="text-[11px] font-black uppercase text-slate-500">Rombel / Kelas Diampu</span>
                <div class="text-2xl font-heading font-black text-black mt-0.5">
                    {{ $myStats['total_classes'] }} <span class="text-xs font-bold text-slate-500">Kelas</span>
                </div>
            </div>
            <div class="w-11 h-11 bg-[#D3F9D8] border-2 border-black flex items-center justify-center text-xl shadow-[2px_2px_0px_0px_#000]">
                🏫
            </div>
        </div>

        <div class="bg-white neo-box p-4 border-3 border-black flex items-center justify-between">
            <div>
                <span class="text-[11px] font-black uppercase text-slate-500">Mata Pelajaran Diajar</span>
                <div class="text-2xl font-heading font-black text-black mt-0.5">
                    {{ $myStats['total_subjects'] }} <span class="text-xs font-bold text-slate-500">Mapel</span>
                </div>
            </div>
            <div class="w-11 h-11 bg-[#FFF3BF] border-2 border-black flex items-center justify-center text-xl shadow-[2px_2px_0px_0px_#000]">
                📖
            </div>
        </div>
    </div>

    <!-- Navigation Tabs: Jadwal Mengajar Saya vs Jadwal Per Kelas -->
    <div class="flex border-b-2 border-black gap-2 print:hidden">
        <button type="button" onclick="switchScheduleTab('my')" id="tabBtn-my" 
           class="px-5 py-2.5 font-heading font-black text-xs uppercase border-t-2 border-x-2 border-black transition-all cursor-pointer
           {{ $activeTab === 'my' ? 'bg-white -mb-[2px] border-b-2 border-b-white z-10 text-black shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
            👨‍🏫 Jadwal Mengajar Saya ({{ $myStats['total_sessions'] }})
        </button>
        <button type="button" onclick="switchScheduleTab('class')" id="tabBtn-class" 
           class="px-5 py-2.5 font-heading font-black text-xs uppercase border-t-2 border-x-2 border-black transition-all cursor-pointer
           {{ $activeTab === 'class' ? 'bg-white -mb-[2px] border-b-2 border-b-white z-10 text-black shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
            🏫 Jadwal Per Rombel / Kelas
        </button>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 1: JADWAL MENGAJAR SAYA -->
    <!-- ========================================================================= -->
    <div id="tabContent-my" class="space-y-5 {{ $activeTab === 'my' ? '' : 'hidden' }}">
        <div class="bg-white neo-box p-4 flex items-center justify-between gap-3 print:border-none print:p-0">
            <div>
                <span class="text-[10px] font-black uppercase text-slate-500 tracking-wider">Guru Pengampu</span>
                <h3 class="font-heading font-black text-base text-black">
                    {{ $teacher->user->name ?? (Auth::user()->name ?? 'Guru') }}
                </h3>
                <p class="text-xs font-semibold text-slate-600">
                    NIP: {{ $teacher->nip ?? '-' }} • Total: {{ $myStats['total_sessions'] }} Jam Tatap Muka / Minggu
                </p>
            </div>
            <div class="text-right hidden sm:block print:hidden">
                <span class="neo-badge bg-[#FFD43B] text-black text-xs font-black">
                    Hari Ini: {{ $daysMap[$currentDayOfWeek] ?? 'Minggu' }}
                </span>
            </div>
        </div>

        <!-- Matriks 6 Hari Jadwal Mengajar Saya -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($mySchedulesByDay as $dayNum => $dayData)
                @php
                    $isToday = ($dayNum === $currentDayOfWeek);
                    $items = $dayData['items'];
                @endphp
                <div class="bg-white neo-box p-5 flex flex-col justify-between space-y-4 {{ $isToday ? 'border-amber-500 shadow-[4px_4px_0px_0px_#F59F00]' : '' }}">
                    <!-- Header Hari -->
                    <div class="flex items-center justify-between border-b-2 border-black pb-2.5">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 {{ $isToday ? 'bg-[#F59F00]' : 'bg-black' }} rounded-full inline-block"></span>
                            <h3 class="font-heading font-black text-base uppercase text-black">
                                {{ $dayData['name'] }}
                            </h3>
                            @if($isToday)
                                <span class="neo-badge bg-[#FFD43B] text-black text-[9px] py-0.2 px-1 font-black">Hari Ini</span>
                            @endif
                        </div>
                        <span class="text-[10px] font-mono font-bold bg-[#E7F5FF] text-blue-950 border border-black px-1.5 py-0.5">
                            {{ count($items) }} Sesi
                        </span>
                    </div>

                    <!-- List Jam Mengajar Hari Ini -->
                    <div class="space-y-2.5 flex-1">
                        @forelse($items as $s)
                            @php
                                $startTimeStr = substr($s->start_time, 0, 5);
                                $endTimeStr = substr($s->end_time, 0, 5);
                                $isOngoing = ($isToday && $currentTimeStr >= $s->start_time && $currentTimeStr <= $s->end_time);
                                $isPassed = ($isToday && $currentTimeStr > $s->end_time);
                            @endphp
                            <div class="p-3 border-2 border-black rounded-sm space-y-1.5 transition-all
                                @if($isOngoing) bg-[#FFF3BF] border-amber-600 shadow-[2px_2px_0px_0px_#D97706] @elseif($isPassed) bg-slate-50 opacity-80 @else bg-white shadow-[2px_2px_0px_0px_#000] @endif">
                                <div class="flex items-center justify-between gap-1">
                                    <span class="font-mono text-xs font-black text-black bg-white border border-black px-1.5 py-0.5">
                                        {{ $startTimeStr }} - {{ $endTimeStr }}
                                    </span>
                                    <span class="neo-badge bg-[#5294FF] text-white text-[10px] font-black">
                                        {{ $s->schoolClass->name ?? 'Kelas' }}
                                    </span>
                                </div>
                                <div class="font-heading font-black text-sm text-black leading-tight">
                                    {{ $s->subject->name ?? 'Mata Pelajaran' }}
                                </div>
                                <div class="flex items-center justify-between text-[10px] font-semibold text-slate-600 pt-0.5">
                                    <span>Tingkat {{ $s->schoolClass->level ?? '-' }}</span>
                                    @if($isOngoing)
                                        <span class="text-rose-700 font-black animate-pulse">● Sedang Berlangsung</span>
                                    @elseif($isPassed)
                                        <span class="text-slate-500 font-bold">✓ Selesai</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-slate-400 space-y-1">
                                <div class="text-2xl">☕</div>
                                <div class="text-xs font-bold text-slate-600">Tidak ada jadwal mengajar</div>
                                <p class="text-[11px] text-slate-400">Tidak ada sesi KBM yang dijadwalkan pada hari {{ $dayData['name'] }}.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 2: JADWAL PER ROMBEL / KELAS (MATRIKS SIMPATIKA LENGKAP) -->
    <!-- ========================================================================= -->
    <div id="tabContent-class" class="space-y-5 {{ $activeTab === 'class' ? '' : 'hidden' }}">
        <!-- Toolbar Pemilihan Kelas -->
        <div class="bg-white neo-box p-4 sm:p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 print:hidden">
            <form method="GET" action="{{ route('guru.jadwal') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <input type="hidden" name="tab" value="class">
                <label for="school_class_id" class="text-xs font-black uppercase text-black shrink-0">
                    Pilih Kelas / Rombel:
                </label>
                <select name="school_class_id" id="school_class_id" onchange="this.form.submit()"
                        class="bg-white border-2 border-black px-3 py-1.5 text-xs font-black text-black focus:outline-hidden min-w-[220px]">
                    @foreach($classes as $cls)
                        <option value="{{ $cls->id }}" {{ $selectedClassId == $cls->id ? 'selected' : '' }}>
                            {{ $cls->name }} (Tingkat {{ $cls->level }})
                        </option>
                    @endforeach
                </select>
            </form>

            @if($selectedClass)
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-bold bg-[#E7F5FF] text-blue-900 border-2 border-black px-3 py-1 shadow-[2px_2px_0px_0px_#000]">
                        Wali Kelas: <b>{{ $selectedClass->homeroomTeacher->user->name ?? 'Belum Ditugaskan' }}</b>
                    </span>
                    <span class="text-xs font-mono font-bold bg-[#D3F9D8] text-emerald-950 border-2 border-black px-3 py-1 shadow-[2px_2px_0px_0px_#000]">
                        Total Sesi: <b>{{ $classSchedules->count() }} Jam KBM</b>
                    </span>
                </div>
            @endif
        </div>

        @if(!$selectedClass)
            <div class="bg-white neo-box p-12 text-center text-slate-500 space-y-2">
                <div class="text-4xl">🏫</div>
                <div class="text-base font-black text-black">Belum Ada Data Rombel / Kelas</div>
                <p class="text-xs">Data kelas belum tersedia pada tahun ajaran aktif ini.</p>
            </div>
        @else
            <!-- Matriks 6 Hari Jadwal Kelas Terpilih -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                @foreach($classSchedulesByDay as $dayNum => $dayData)
                    @php
                        $daySlots = $allSlotsByDay[$dayNum] ?? collect();
                        $daySchedules = $dayData['items'];
                        $renderedScheduleIds = [];
                        $timelineItems = collect();

                        // 1. Petakan slot template (KBM & Non-KBM)
                        foreach ($daySlots as $slot) {
                            $slotStart = substr((string) $slot->start_time, 0, 5);
                            $slotEnd = substr((string) $slot->end_time, 0, 5);

                            if ($slot->k_jadwal != 0) {
                                $timelineItems->push([
                                    'type' => 'non_kbm',
                                    'start' => $slotStart,
                                    'end' => $slotEnd,
                                    'slot' => $slot,
                                ]);
                            } else {
                                $matchedSchedule = $daySchedules->first(function ($sch) use ($slotStart, $slotEnd) {
                                    $schStart = substr((string) $sch->start_time, 0, 5);
                                    $schEnd = substr((string) $sch->end_time, 0, 5);
                                    return ($schStart < $slotEnd) && ($schEnd > $slotStart);
                                });

                                if ($matchedSchedule) {
                                    if (!in_array($matchedSchedule->id, $renderedScheduleIds)) {
                                        $renderedScheduleIds[] = $matchedSchedule->id;
                                        $timelineItems->push([
                                            'type' => 'schedule',
                                            'start' => substr((string) $matchedSchedule->start_time, 0, 5),
                                            'end' => substr((string) $matchedSchedule->end_time, 0, 5),
                                            'schedule' => $matchedSchedule,
                                            'slot' => $slot,
                                        ]);
                                    }
                                } else {
                                    $timelineItems->push([
                                        'type' => 'empty_slot',
                                        'start' => $slotStart,
                                        'end' => $slotEnd,
                                        'slot' => $slot,
                                    ]);
                                }
                            }
                        }

                        // 2. Jadwal di luar slot template
                        foreach ($daySchedules as $sch) {
                            if (!in_array($sch->id, $renderedScheduleIds)) {
                                $timelineItems->push([
                                    'type' => 'schedule',
                                    'start' => substr((string) $sch->start_time, 0, 5),
                                    'end' => substr((string) $sch->end_time, 0, 5),
                                    'schedule' => $sch,
                                    'slot' => null,
                                ]);
                            }
                        }

                        $timelineItems = $timelineItems->sortBy('start')->values();
                    @endphp

                    <div class="bg-white neo-box p-5 flex flex-col justify-between space-y-4">
                        <!-- Header Hari -->
                        <div class="flex items-center justify-between border-b-2 border-black pb-2.5">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 bg-black rounded-full inline-block"></span>
                                <h3 class="font-heading font-black text-base uppercase text-black">
                                    {{ $dayData['name'] }}
                                </h3>
                            </div>
                            <span class="text-[10px] font-mono font-bold bg-[#D3F9D8] text-emerald-950 border border-black px-1.5 py-0.5">
                                {{ count($daySchedules) }} Terisi
                            </span>
                        </div>

                        <!-- Timeline Item Per Hari -->
                        <div class="space-y-2.5 flex-1">
                            @forelse($timelineItems as $item)
                                @if($item['type'] === 'non_kbm')
                                    @php
                                        $nSlot = $item['slot'];
                                        $colorConfig = \App\Models\SlotTemplate::KEGIATAN_COLORS[$nSlot->k_jadwal] ?? [
                                            'bg' => 'bg-slate-100',
                                            'text' => 'text-slate-900',
                                            'badge_bg' => 'bg-slate-300',
                                            'badge_text' => 'text-black',
                                            'name' => 'Kegiatan',
                                        ];
                                    @endphp
                                    <div class="p-2.5 border-2 border-black rounded-sm space-y-1 {{ $colorConfig['bg'] }} shadow-[1.5px_1.5px_0px_0px_#000]">
                                        <div class="flex items-center justify-between gap-1">
                                            <span class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded border border-black {{ $colorConfig['badge_bg'] }} {{ $colorConfig['badge_text'] }}">
                                                {{ $colorConfig['name'] }}
                                            </span>
                                            <span class="text-[10px] font-mono font-black {{ $colorConfig['text'] }}">
                                                {{ $nSlot->getShortStartTime() }} - {{ $nSlot->getShortEndTime() }}
                                            </span>
                                        </div>
                                        <div class="text-xs font-black {{ $colorConfig['text'] }} truncate">
                                            {{ $nSlot->name ?: $nSlot->getCategoryLabel() }}
                                        </div>
                                    </div>
                                @elseif($item['type'] === 'schedule')
                                    @php
                                        $sch = $item['schedule'];
                                        $isMySubject = ($teacher && $sch->teacher_id === $teacher->id);
                                    @endphp
                                    <div class="p-3 border-2 border-black rounded-sm space-y-1.5 {{ $isMySubject ? 'bg-[#FFF3BF] border-amber-600 shadow-[2px_2px_0px_0px_#D97706]' : 'bg-white shadow-[2px_2px_0px_0px_#000]' }}">
                                        <div class="flex items-center justify-between gap-1">
                                            <span class="font-mono text-xs font-black text-black bg-white border border-black px-1.5 py-0.5">
                                                {{ $item['start'] }} - {{ $item['end'] }}
                                            </span>
                                            @if($isMySubject)
                                                <span class="neo-badge bg-[#F59F00] text-black text-[9px] font-black">
                                                    Saya Mengajar
                                                </span>
                                            @elseif($item['slot'])
                                                <span class="text-[10px] font-bold text-slate-500">
                                                    Jam ke-{{ $item['slot']->jam_ke }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="font-heading font-black text-sm text-black leading-tight">
                                            {{ $sch->subject->name ?? 'Mata Pelajaran' }}
                                        </div>
                                        <div class="text-xs font-semibold text-slate-700 flex items-center gap-1 truncate">
                                            <span>👨‍🏫</span>
                                            <span>{{ $sch->teacher->user->name ?? ($sch->teacher->nip ?? 'Belum Ditugaskan') }}</span>
                                        </div>
                                    </div>
                                @else
                                    <!-- Empty Slot KBM -->
                                    @php $eSlot = $item['slot']; @endphp
                                    <div class="p-2 border-2 border-dashed border-slate-300 rounded-sm bg-slate-50/70 flex items-center justify-between text-slate-400">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-mono font-bold">{{ $item['start'] }} - {{ $item['end'] }}</span>
                                            <span class="text-[11px] font-medium italic">Jam ke-{{ $eSlot->jam_ke }}: Kosong</span>
                                        </div>
                                    </div>
                                @endif
                            @empty
                                <div class="py-6 text-center text-slate-400 space-y-1">
                                    <div class="text-xl">☕</div>
                                    <div class="text-xs font-semibold">Tidak ada jadwal KBM</div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<script>
    function switchScheduleTab(tab) {
        const myTab = document.getElementById('tabContent-my');
        const classTab = document.getElementById('tabContent-class');
        const myBtn = document.getElementById('tabBtn-my');
        const classBtn = document.getElementById('tabBtn-class');

        if (tab === 'my') {
            myTab.classList.remove('hidden');
            classTab.classList.add('hidden');
            
            myBtn.className = "px-5 py-2.5 font-heading font-black text-xs uppercase border-t-2 border-x-2 border-black transition-all bg-white -mb-[2px] border-b-2 border-b-white z-10 text-black shadow-sm cursor-pointer";
            classBtn.className = "px-5 py-2.5 font-heading font-black text-xs uppercase border-t-2 border-x-2 border-black transition-all bg-slate-100 text-slate-600 hover:bg-slate-200 cursor-pointer";
        } else {
            myTab.classList.add('hidden');
            classTab.classList.remove('hidden');
            
            classBtn.className = "px-5 py-2.5 font-heading font-black text-xs uppercase border-t-2 border-x-2 border-black transition-all bg-white -mb-[2px] border-b-2 border-b-white z-10 text-black shadow-sm cursor-pointer";
            myBtn.className = "px-5 py-2.5 font-heading font-black text-xs uppercase border-t-2 border-x-2 border-black transition-all bg-slate-100 text-slate-600 hover:bg-slate-200 cursor-pointer";
        }

        // Update URL query tanpa refresh
        const url = new URL(window.location);
        url.searchParams.set('tab', tab);
        window.history.replaceState({}, '', url);
    }
</script>

<style>
@media print {
    /* Hide layout chrome */
    header, aside, #sidebar, #mainContent > header, button, .print\:hidden {
        display: none !important;
    }
    #mainContent {
        padding-left: 0 !important;
        margin: 0 !important;
        width: 100% !important;
    }
    body {
        background: white !important;
        color: black !important;
    }
    .neo-box, .neo-box-lg {
        box-shadow: none !important;
        border-width: 1px !important;
    }
}
</style>
@endsection
