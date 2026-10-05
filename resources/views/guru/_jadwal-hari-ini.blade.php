<!-- Jadwal Mengajar Guru: Hari Ini & Mingguan (Adapted, Optimized, Colorized Neo-Brutalism) -->
<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-sm bg-black text-[#FFD43B] flex items-center justify-center shrink-0 border border-black shadow-[1.5px_1.5px_0px_#000]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <h2 class="font-heading font-black text-lg sm:text-xl text-black leading-tight">
                    Jadwal Mengajar
                </h2>
                <div class="text-[11px] font-bold text-slate-500">
                    Sesi Tatap Muka & KBM
                </div>
            </div>
        </div>

        <!-- Tab Switcher (Hari Ini vs Mingguan) -->
        <div class="flex items-center gap-1 p-1 bg-slate-100 border-2 border-black rounded-sm self-start sm:self-auto w-full sm:w-auto">
            <button type="button" onclick="switchDashboardScheduleTab('today')" id="tabBtnToday"
                    class="schedule-tab-btn active flex-1 sm:flex-initial text-xs font-heading font-black px-3.5 py-1.5 rounded-xs transition-all cursor-pointer bg-black text-[#FFD43B] border border-black shadow-[1.5px_1.5px_0px_#000]">
                Hari Ini ({{ $todaySchedules->count() }})
            </button>
            <button type="button" onclick="switchDashboardScheduleTab('weekly')" id="tabBtnWeekly"
                    class="schedule-tab-btn flex-1 sm:flex-initial text-xs font-heading font-bold px-3.5 py-1.5 rounded-xs transition-all cursor-pointer text-slate-700 hover:text-black hover:bg-white/60">
                Mingguan ({{ ($weeklySchedules ?? collect())->count() }})
            </button>
        </div>
    </div>

    <!-- TAB 1: Jadwal Mengajar Hari Ini -->
    <div id="tabContentToday" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse($todaySchedules as $sch)
            @php
                $now = \Carbon\Carbon::now()->format('H:i:s');
                $isOngoing = $now >= $sch->start_time && $now <= $sch->end_time;
                $isPast = $now > $sch->end_time;
                $startCarbon = \Carbon\Carbon::parse($sch->start_time);
                $endCarbon = \Carbon\Carbon::parse($sch->end_time);
                $jp = max(1, (int) round($startCarbon->diffInMinutes($endCarbon) / 40));
            @endphp
            <div class="bg-white neo-box p-4 sm:p-5 flex flex-col justify-between space-y-4 border-2 border-black transition-all hover:-translate-y-0.5 {{ $isOngoing ? 'border-3 border-[#20C997] bg-[#F4FDF7] shadow-[4px_4px_0px_0px_#20C997]' : 'shadow-[3px_3px_0px_0px_#000]' }}">
                <div>
                    <!-- Top Status & Indicators -->
                    <div class="flex items-center justify-between gap-2 pb-2 mb-2 border-b border-black/10">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-[10px] font-black uppercase tracking-wide px-2 py-0.5 rounded-xs border border-black {{ $isOngoing ? 'bg-[#20C997] text-white shadow-[1px_1px_0px_#000]' : 'bg-[#E7F5FF] text-blue-950' }}">
                                {{ $sch->subject->code ?? 'MAPEL' }}
                            </span>
                            <span class="text-[10px] font-black bg-[#FFD43B] text-black px-1.5 py-0.5 border border-black rounded-xs">
                                {{ $jp }} JP
                            </span>
                        </div>

                        @if($isOngoing)
                            <div class="flex items-center gap-1.5 text-[10px] font-heading font-black text-emerald-950 bg-[#D3F9D8] px-2 py-0.5 border border-black shadow-[1px_1px_0px_#000]">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                                <span>Sedang Berlangsung</span>
                            </div>
                        @elseif($isPast)
                            <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 border border-slate-300">
                                Selesai
                            </span>
                        @else
                            <span class="text-[10px] font-bold text-amber-900 bg-[#FFF9DB] px-2 py-0.5 border border-amber-300">
                                Akan Datang
                            </span>
                        @endif
                    </div>

                    <!-- Subject & Class Details -->
                    <div class="space-y-1">
                        <h3 class="font-heading font-black text-base sm:text-lg text-black leading-snug">
                            {{ $sch->subject->name ?? 'Mata Pelajaran' }}
                        </h3>
                        <div class="flex items-center gap-2 text-xs font-bold text-slate-700">
                            <span class="px-1.5 py-0.5 bg-slate-100 border border-black text-black font-mono">
                                {{ $sch->schoolClass->name ?? 'Kelas' }}
                            </span>
                            <span class="text-slate-500 text-[11px]">Tingkat {{ $sch->schoolClass->level ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Footer: Time & Action Buttons -->
                <div class="pt-3 border-t-2 border-black/10 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5">
                    <div class="flex items-center gap-1.5 text-xs font-mono font-bold bg-[#FFF9DB] border border-black px-2.5 py-1.5 rounded-xs self-start sm:self-auto shadow-[1px_1px_0px_#000]">
                        <svg class="w-3.5 h-3.5 text-amber-900 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ substr($sch->start_time, 0, 5) }} - {{ substr($sch->end_time, 0, 5) }} WIB</span>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <a href="{{ route('guru.attendance.manual', ['school_class_id' => $sch->school_class_id]) }}" 
                           class="neo-btn flex-1 sm:flex-initial bg-[#5294FF] text-white hover:bg-blue-600 text-xs font-heading font-bold px-3 py-2 min-h-[38px] cursor-pointer transition-transform active:translate-x-0.5 active:translate-y-0.5 flex items-center justify-center gap-1 border border-black shadow-[1.5px_1.5px_0px_#000]">
                            <span>Presensi</span>
                            <span>→</span>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white neo-box p-8 text-center text-slate-500 space-y-2 border-2 border-black shadow-[3px_3px_0px_#000]">
                <div class="w-12 h-12 rounded-full bg-[#FFF9DB] border-2 border-black mx-auto flex items-center justify-center text-xl shadow-[2px_2px_0px_#000]">
                    ☕
                </div>
                <div class="font-heading font-black text-base text-black">
                    Tidak Ada Jadwal Mengajar Hari Ini
                </div>
                <p class="text-xs text-slate-500 max-w-sm mx-auto font-medium">
                    Tidak ada sesi KBM yang terjadwal untuk Anda pada hari ini. Anda dapat memeriksa jadwal mingguan atau mengerjakan jurnal mengajar.
                </p>
            </div>
        @endforelse
    </div>

    <!-- TAB 2: Seluruh Jadwal Mingguan Guru -->
    <div id="tabContentWeekly" class="hidden space-y-4">
        @php
            $daysMap = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];
            $allWeekly = $weeklySchedules ?? collect();
            $currentDayOfWeek = (int) \Carbon\Carbon::now()->dayOfWeekIso;
        @endphp

        @if($allWeekly->isEmpty())
            <div class="bg-white neo-box p-8 text-center text-slate-500 space-y-2 border-2 border-black shadow-[3px_3px_0px_#000]">
                <div class="text-3xl">📅</div>
                <div class="font-heading font-black text-base text-black">Belum Ada Jadwal Mingguan</div>
                <div class="text-xs text-slate-400">Jadwal mingguan disusun dan dipublikasikan oleh kurikulum/administrator.</div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($daysMap as $dNum => $dName)
                    @php
                        $dayItems = $allWeekly->where('day_of_week', $dNum)->values();
                        $isTodayDay = ($dNum === $currentDayOfWeek);
                    @endphp
                    <div class="bg-white neo-box p-4 space-y-3 border-2 border-black transition-all {{ $isTodayDay ? 'bg-[#F4FDF7] border-3 border-[#20C997] shadow-[4px_4px_0px_0px_#20C997]' : 'shadow-[2px_2px_0px_0px_#000]' }}">
                        <div class="flex items-center justify-between border-b-2 border-black pb-2">
                            <div class="flex items-center gap-1.5">
                                <span class="font-heading font-black text-xs uppercase text-black">
                                    {{ $dName }}
                                </span>
                                @if($isTodayDay)
                                    <span class="text-[9px] font-heading font-black uppercase px-1.5 py-0.2 bg-[#20C997] text-white border border-black shadow-[1px_1px_0px_#000]">
                                        Hari Ini
                                    </span>
                                @endif
                            </div>
                            <span class="text-[10px] font-mono font-bold bg-[#FFF9DB] border border-black px-1.5 py-0.5 shadow-[1px_1px_0px_#000]">
                                {{ count($dayItems) }} Sesi
                            </span>
                        </div>

                        <div class="space-y-2">
                            @forelse($dayItems as $item)
                                @php
                                    $iStart = \Carbon\Carbon::parse($item->start_time);
                                    $iEnd = \Carbon\Carbon::parse($item->end_time);
                                    $iJp = max(1, (int) round($iStart->diffInMinutes($iEnd) / 40));
                                @endphp
                                <div class="p-2.5 bg-slate-50 border border-black rounded-xs flex items-center justify-between gap-2 text-xs hover:bg-slate-100 transition-colors">
                                    <div class="min-w-0">
                                        <div class="font-heading font-black text-black truncate">{{ $item->subject->name ?? 'Mapel' }}</div>
                                        <div class="text-[10px] text-slate-600 font-bold flex items-center gap-1 mt-0.5">
                                            <span>{{ $item->schoolClass->name ?? '-' }}</span>
                                            <span>•</span>
                                            <span>Tk. {{ $item->schoolClass->level ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0 flex items-center gap-1.5">
                                        <span class="text-[9px] font-black bg-[#FFD43B] text-black px-1.5 py-0.5 border border-black rounded-xs">
                                            {{ $iJp }} JP
                                        </span>
                                        <span class="font-mono text-[10px] font-bold bg-white border border-black px-1.5 py-0.5 shadow-[1px_1px_0px_#000]">
                                            {{ substr($item->start_time, 0, 5) }} - {{ substr($item->end_time, 0, 5) }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="py-3 text-center text-slate-400 text-xs font-semibold">
                                    Tidak ada jadwal mengajar
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="p-3 bg-[#E7F5FF] border-2 border-black rounded-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 text-xs shadow-[2px_2px_0px_#000]">
                <span class="font-bold text-blue-950 flex items-center gap-1.5">
                    <span>📌</span>
                    <span>Total Beban Mengajar: <strong>{{ $allWeekly->count() }} Sesi</strong> per Minggu</span>
                </span>
                <a href="{{ route('guru.jadwal') }}" class="font-black text-blue-900 underline hover:text-black self-end sm:self-auto">
                    Lihat Kalender Jadwal Lengkap →
                </a>
            </div>
        @endif
    </div>
</div>

<script>
    function switchDashboardScheduleTab(tab) {
        const tabToday = document.getElementById('tabContentToday');
        const tabWeekly = document.getElementById('tabContentWeekly');
        const btnToday = document.getElementById('tabBtnToday');
        const btnWeekly = document.getElementById('tabBtnWeekly');

        if (!tabToday || !tabWeekly || !btnToday || !btnWeekly) return;

        if (tab === 'today') {
            tabToday.classList.remove('hidden');
            tabWeekly.classList.add('hidden');

            btnToday.classList.add('bg-black', 'text-[#FFD43B]', 'shadow-[1.5px_1.5px_0px_#000]');
            btnToday.classList.remove('text-slate-700', 'bg-transparent');

            btnWeekly.classList.remove('bg-black', 'text-[#FFD43B]', 'shadow-[1.5px_1.5px_0px_#000]');
            btnWeekly.classList.add('text-slate-700');
        } else {
            tabToday.classList.add('hidden');
            tabWeekly.classList.remove('hidden');

            btnWeekly.classList.add('bg-black', 'text-[#FFD43B]', 'shadow-[1.5px_1.5px_0px_#000]');
            btnWeekly.classList.remove('text-slate-700', 'bg-transparent');

            btnToday.classList.remove('bg-black', 'text-[#FFD43B]', 'shadow-[1.5px_1.5px_0px_#000]');
            btnToday.classList.add('text-slate-700');
        }
    }
</script>
