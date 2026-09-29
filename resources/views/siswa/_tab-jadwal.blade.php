<!-- ========================================================================= -->
<!-- TAB 3: JADWAL PELAJARAN (PLAYFUL NEO-BRUTALISM & TIMELINE INTERAKTIF) -->
<!-- ========================================================================= -->
<div id="tabContent-jadwal" class="tab-pane hidden space-y-4 sm:space-y-5">
    
    <!-- Header Info Wali Kelas & Ruang -->
    <div class="bg-white rounded-2xl border-2 sm:border-[2.5px] border-black p-4 sm:p-5 shadow-[4px_4px_0px_0px_#000] flex items-center justify-between gap-3">
        <div class="min-w-0">
            <span class="inline-flex items-center gap-1.5 bg-[#FFF4E6] px-2.5 py-0.5 rounded-full border border-black text-[10px] font-black uppercase tracking-wider text-amber-950">
                <span>🏫</span> Jadwal Kelas
            </span>
            <div class="font-heading font-black text-xl sm:text-2xl text-black truncate mt-1">
                {{ $student->schoolClass->name ?? 'Kelas Siswa' }}
            </div>
            <p class="text-xs font-bold text-slate-700 mt-1 flex items-center gap-1.5 truncate">
                <span>👨‍🏫 Wali Kelas:</span>
                <span class="text-black font-extrabold">{{ $student->schoolClass->homeroomTeacher->user->name ?? 'Belum Ditentukan' }}</span>
            </p>
        </div>
        <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-[#FFD43B] border-2 border-black flex items-center justify-center text-2xl sm:text-3xl shadow-[2.5px_2.5px_0px_0px_#000] shrink-0">
            📚
        </div>
    </div>

    <!-- Interactive Day Selector Chips -->
    <div class="bg-white rounded-2xl border-2 sm:border-[2.5px] border-black p-3 sm:p-4 shadow-[4px_4px_0px_0px_#000]">
        <div class="flex items-center justify-between px-1 mb-2">
            <span class="text-[10px] font-black uppercase text-slate-600 tracking-wider">Pilih Hari Pembelajaran:</span>
            <span class="text-[10px] font-bold text-slate-500 hidden sm:inline">Klik hari untuk melihat jadwal lengkap</span>
        </div>
        <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
            @foreach([1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'] as $dNum => $dName)
                @php
                    $isInitActive = ($dNum === $currentDayOfWeek || ($currentDayOfWeek > 6 && $dNum === 1));
                @endphp
                <button type="button" onclick="selectScheduleDay({{ $dNum }})" id="dayBtn-{{ $dNum }}" 
                    class="day-btn py-2.5 px-2 rounded-xl border-2 border-black text-center transition-all cursor-pointer flex flex-col items-center justify-center
                    @if($isInitActive) bg-[#FFD43B] shadow-[2.5px_2.5px_0px_0px_#000] font-black @else bg-slate-100 hover:bg-slate-200/90 font-bold text-slate-700 @endif">
                    <span class="text-xs uppercase tracking-tight">{{ $dName }}</span>
                    @if($dNum === $currentDayOfWeek)
                        <span class="inline-flex items-center gap-1 text-[9px] font-black text-emerald-900 bg-[#D3F9D8] px-1.5 py-0.2 rounded border border-black mt-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                            Hari Ini
                        </span>
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

        <div id="scheduleDayContainer-{{ $dNum }}" class="schedule-day-pane space-y-3 @if(!$isDayActive) hidden @endif">
            <div class="flex items-center justify-between text-xs font-black uppercase text-black px-1">
                <span class="flex items-center gap-1.5">
                    <span>🗓️</span> Agenda Belajar Hari {{ $dName }}
                </span>
                <span class="bg-[#5294FF] text-white text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full border border-black shadow-[1.5px_1.5px_0px_0px_#000]">
                    {{ count($dayList) }} Mapel Terjadwal
                </span>
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
                    <div class="rounded-xl border-2 border-black p-3 sm:p-3.5 flex items-center justify-between gap-3 {{ $colorConfig['bg'] }} shadow-[2px_2px_0px_0px_#000]
                        @if($isOngoing) ring-2 ring-amber-400 @elseif($isPassed) opacity-70 @endif">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="text-[9px] font-black uppercase px-2.5 py-0.5 rounded-md border border-black {{ $colorConfig['badge_bg'] }} {{ $colorConfig['badge_text'] }} shadow-[1px_1px_0px_0px_#000] shrink-0">
                                {{ $colorConfig['name'] }}
                            </span>
                            <div class="min-w-0">
                                <div class="font-heading font-black text-xs sm:text-sm {{ $colorConfig['text'] }} truncate">
                                    {{ $nSlot->name ?: $nSlot->getCategoryLabel() }}
                                </div>
                                <div class="text-[11px] font-mono font-bold text-slate-700 mt-0.5">
                                    ⏰ {{ $startStr }} - {{ $endStr }} WIB
                                </div>
                            </div>
                        </div>
                        @if($isOngoing)
                            <span class="bg-[#FF6B6B] text-white text-[10px] py-0.5 px-2 font-black uppercase rounded-full border border-black shadow-[1.5px_1.5px_0px_0px_#000] shrink-0 animate-pulse">
                                Berlangsung
                            </span>
                        @endif
                    </div>
                @else
                    @php $s = $item['schedule']; @endphp
                    <div class="rounded-xl border-2 border-black p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-[3px_3px_0px_0px_#000] transition-all
                        @if($isOngoing) bg-[#FFF3BF] ring-2 ring-yellow-400 @elseif($isPassed) bg-slate-50 opacity-80 @else bg-white hover:bg-slate-50 @endif">
                        <div class="min-w-0 space-y-1.5 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-mono text-xs font-black text-black bg-white border border-black px-2 py-0.5 rounded shadow-[1px_1px_0px_0px_#000]">
                                    ⏰ {{ $startStr }} - {{ $endStr }} WIB
                                </span>
                                @if($item['slot'])
                                    <span class="text-[10px] font-black bg-slate-200 text-slate-800 px-2 py-0.5 rounded border border-black">
                                        Jam ke-{{ $item['slot']->jam_ke }}
                                    </span>
                                @endif
                                @if($isOngoing)
                                    <span class="inline-flex items-center gap-1 bg-[#FF6B6B] text-white text-[10px] font-black uppercase px-2 py-0.5 rounded-full border border-black animate-pulse">
                                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                        Sedang Berlangsung
                                    </span>
                                @elseif($isPassed)
                                    <span class="text-[10px] font-black text-slate-500 uppercase bg-slate-200 px-1.5 py-0.2 rounded border border-slate-300">
                                        Selesai
                                    </span>
                                @endif
                            </div>
                            <h4 class="font-heading font-black text-base sm:text-lg text-black truncate leading-tight">
                                {{ $s->subject->name ?? 'Mata Pelajaran' }}
                            </h4>
                            <div class="text-xs font-bold text-slate-700 flex items-center gap-1.5 truncate">
                                <span>👨‍🏫 Guru:</span>
                                <span class="text-black font-extrabold">{{ $s->teacher->user->name ?? ($s->teacher->nip ?? 'Guru Pengampu') }}</span>
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <div class="bg-white rounded-2xl border-2 border-dashed border-slate-300 p-8 text-center text-slate-500 space-y-2">
                    <div class="text-4xl">☕</div>
                    <div class="font-heading font-black text-base text-black">Tidak Ada Jadwal Pelajaran</div>
                    <p class="text-xs font-semibold text-slate-600 max-w-sm mx-auto">
                        Tidak ada mata pelajaran atau kegiatan terjadwal untuk hari {{ $dName }}.
                    </p>
                </div>
            @endforelse
        </div>
    @endforeach
</div>
