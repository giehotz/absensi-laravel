<!-- ========================================================================= -->
<!-- TAB 3: JADWAL PELAJARAN (INTERAKTIF DENGAN PEMILIH HARI) -->
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
        @endphp
        <div id="scheduleDayContainer-{{ $dNum }}" class="schedule-day-pane space-y-2.5 @if(!$isDayActive) hidden @endif">
            <div class="flex items-center justify-between text-xs font-black uppercase text-black px-1">
                <span>Mata Pelajaran Hari {{ $dName }}</span>
                <span class="neo-badge bg-[#5294FF] text-white text-[10px]">{{ count($dayList) }} Sesi</span>
            </div>

            @forelse($dayList as $s)
                @php
                    $isOngoing = ($dNum === $currentDayOfWeek && $currentTimeStr >= $s->start_time && $currentTimeStr <= $s->end_time);
                    $isPassed = ($dNum === $currentDayOfWeek && $currentTimeStr > $s->end_time);
                @endphp
                <div class="neo-box p-3.5 flex items-start justify-between gap-3
                    @if($isOngoing) bg-[#FFF3BF] border-yellow-500 @elseif($isPassed) bg-slate-100 opacity-75 @else bg-white @endif">
                    <div class="min-w-0 space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-mono text-xs font-black text-black bg-white border border-black px-1.5 py-0.5">
                                {{ substr($s->start_time, 0, 5) }} - {{ substr($s->end_time, 0, 5) }}
                            </span>
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
            @empty
                <div class="bg-white neo-box p-6 text-center text-slate-500 space-y-2">
                    <div class="text-3xl">☕</div>
                    <div class="font-heading font-bold text-sm text-black">Tidak Ada Jadwal Pelajaran</div>
                    <p class="text-xs text-slate-600">Tidak ada mata pelajaran yang terjadwal untuk hari {{ $dName }}.</p>
                </div>
            @endforelse
        </div>
    @endforeach
</div>
