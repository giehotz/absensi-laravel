<!-- ========================================================================= -->
<!-- TAB 3: JADWAL PELAJARAN (INTERAKTIF DENGAN PEMILIH HARI & SLOT KEGIATAN) -->
<!-- ========================================================================= -->
<div id="tabContent-jadwal" class="tab-pane hidden space-y-4">
    
    <!-- Header Info Wali Kelas & Ruang -->
    <div class="bg-white neo-box p-4 flex items-center justify-between gap-3">
        <div>
            <div class="text-[10px] font-black uppercase tracking-wider text-slate-500">Jadwal Kelas</div>
            <div class="font-heading font-black text-lg text-black">{{ $student->schoolClass->name ?? 'Kelas Siswa' }}</div>
            <p class="text-xs font-bold text-slate-600">
                Wali Kelas: {{ $student->schoolClass->homeroomTeacher->user->name ?? 'Belum Ditentukan' }}
            </p>
        </div>
        <div class="w-12 h-12 bg-[#FFD43B] border-2 border-black flex items-center justify-center text-xl shadow-[2px_2px_0px_0px_#000] shrink-0">
            📚
        </div>
    </div>

    <!-- Interactive Day Selector Chips -->
    <div class="bg-white neo-box p-2.5">
        <div class="text-[10px] font-black uppercase text-slate-500 tracking-wider px-1 mb-1.5">Pilih Hari:</div>
        <div class="grid grid-cols-3 sm:grid-cols-6 gap-1.5">
            @foreach([1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'] as $dNum => $dName)
                <button type="button" onclick="selectScheduleDay({{ $dNum }})" id="dayBtn-{{ $dNum }}" 
                    class="day-btn py-2 px-1.5 text-xs font-black uppercase border-2 border-black text-center transition-all cursor-pointer
                    @if($dNum === $currentDayOfWeek || ($currentDayOfWeek > 6 && $dNum === 1)) bg-[#FFD43B] shadow-[2px_2px_0px_0px_#000] @else bg-slate-100 hover:bg-slate-200 @endif">
                    {{ $dName }}
                    @if($dNum === $currentDayOfWeek)
                        <span class="block text-[8px] font-bold text-emerald-800">Hari Ini</span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    <!-- Schedule Items per Selected Day -->
    @foreach([1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'] as $dNum => $dName)
        @php
            $dayList = $schedulesByDay[$dNum] ?? [];
            $isDayActive = ($dNum === $currentDayOfWeek || ($currentDayOfWeek > 6 && $dNum === 1));
            $isToday = ($dNum === $currentDayOfWeek);

            // Petakan slot template kegiatan (Non-KBM & KBM)
            $daySlots = $allSlotsByDay[$dNum] ?? collect();
            $renderedScheduleIds = [];
            $timelineItems = collect();

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
                    $matchedSchedule = collect($dayList)->first(function ($sch) use ($slotStart, $slotEnd) {
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
                    }
                }
            }

            // Sertakan jadwal di luar template
            foreach ($dayList as $sch) {
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

        <div id="scheduleDayContainer-{{ $dNum }}" class="schedule-day-pane space-y-2.5 @if(!$isDayActive) hidden @endif">
            <div class="flex items-center justify-between text-xs font-black uppercase text-black px-1">
                <span>Agenda Belajar Hari {{ $dName }}</span>
                <span class="neo-badge bg-[#5294FF] text-white text-[10px]">{{ count($dayList) }} Mapel</span>
            </div>

            @forelse($timelineItems as $item)
                @php
                    $startStr = $item['start'];
                    $endStr = $item['end'];
                    $isOngoing = ($isToday && $currentTimeStr >= ($startStr . ':00') && $currentTimeStr <= ($endStr . ':00'));
                    $isPassed = ($isToday && $currentTimeStr > ($endStr . ':00'));
                @endphp

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
                    <div class="neo-box p-3 flex items-center justify-between gap-3 {{ $colorConfig['bg'] }} @if($isOngoing) border-amber-600 ring-2 ring-amber-400 @elseif($isPassed) opacity-70 @endif">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded border border-black {{ $colorConfig['badge_bg'] }} {{ $colorConfig['badge_text'] }} shrink-0">
                                {{ $colorConfig['name'] }}
                            </span>
                            <div class="min-w-0">
                                <div class="font-heading font-black text-xs {{ $colorConfig['text'] }} truncate">
                                    {{ $nSlot->name ?: $nSlot->getCategoryLabel() }}
                                </div>
                                <div class="text-[10px] font-mono font-bold text-slate-600">
                                    {{ $startStr }} - {{ $endStr }}
                                </div>
                            </div>
                        </div>
                        @if($isOngoing)
                            <span class="neo-badge bg-rose-600 text-white text-[9px] py-0.5 px-1.5 font-black shrink-0 animate-pulse">
                                Berlangsung
                            </span>
                        @endif
                    </div>
                @else
                    @php $s = $item['schedule']; @endphp
                    <div class="neo-box p-3.5 flex items-start justify-between gap-3
                        @if($isOngoing) bg-[#FFF3BF] border-amber-600 shadow-[3px_3px_0px_0px_#D97706] @elseif($isPassed) bg-slate-100 opacity-75 @else bg-white @endif">
                        <div class="min-w-0 space-y-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-mono text-xs font-black text-black bg-white border border-black px-1.5 py-0.5">
                                    {{ $startStr }} - {{ $endStr }}
                                </span>
                                @if($item['slot'])
                                    <span class="text-[10px] font-bold text-slate-500">Jam ke-{{ $item['slot']->jam_ke }}</span>
                                @endif
                                @if($isOngoing)
                                    <span class="neo-badge bg-[#FF6B6B] text-white text-[9px] py-0.2 px-1">Sedang Berlangsung</span>
                                @elseif($isPassed)
                                    <span class="text-[9px] font-bold text-slate-500 uppercase">Selesai</span>
                                @endif
                            </div>
                            <h4 class="font-heading font-black text-base text-black truncate leading-tight">
                                {{ $s->subject->name ?? 'Mata Pelajaran' }}
                            </h4>
                            <div class="text-xs font-semibold text-slate-600 flex items-center gap-1.5 truncate">
                                <span>👨‍🏫</span> {{ $s->teacher->user->name ?? ($s->teacher->nip ?? 'Guru Pengampu') }}
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <div class="bg-white neo-box p-6 text-center text-slate-500 space-y-2">
                    <div class="text-3xl">☕</div>
                    <div class="font-heading font-bold text-sm text-black">Tidak Ada Jadwal Pelajaran</div>
                    <p class="text-xs text-slate-600">Tidak ada mata pelajaran atau kegiatan yang terjadwal untuk hari {{ $dName }}.</p>
                </div>
            @endforelse
        </div>
    @endforeach
</div>
