<!-- Jadwal Mengajar Guru: Hari Ini & Mingguan -->
<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <h2 class="font-heading font-black text-xl text-black flex items-center gap-2">
                <span>📚</span> Jadwal Mengajar
            </h2>
            <span class="text-xs font-bold font-mono bg-white border-2 border-black px-2 py-0.5">
                {{ \Carbon\Carbon::now()->translatedFormat('l') }}
            </span>
        </div>

        <!-- Tab Switcher (Hari Ini vs Mingguan) -->
        <div class="flex items-center gap-1.5 p-1 bg-slate-100 border-2 border-black rounded-sm">
            <button type="button" onclick="switchDashboardScheduleTab('today')" id="tabBtnToday"
                    class="text-xs font-black px-3 py-1 rounded-xs transition-all cursor-pointer bg-white text-black border border-black shadow-xs">
                Hari Ini ({{ $todaySchedules->count() }})
            </button>
            <button type="button" onclick="switchDashboardScheduleTab('weekly')" id="tabBtnWeekly"
                    class="text-xs font-bold px-3 py-1 rounded-xs transition-all cursor-pointer text-slate-700 hover:text-black hover:bg-white/50">
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
            <div class="bg-white neo-box p-5 flex flex-col justify-between space-y-4 relative overflow-hidden">
                @if($isOngoing)
                    <div class="absolute top-0 right-0 bg-[#20C997] text-white text-[10px] font-black uppercase px-3 py-0.5 border-b-2 border-l-2 border-black">
                        ⚡ Sedang Berlangsung
                    </div>
                @endif

                <div class="flex items-start justify-between gap-2 pt-1">
                    <div>
                        <span class="neo-badge bg-[#E7F5FF] text-blue-900 text-[10px] mb-1 inline-block">
                            {{ $sch->subject->code ?? 'MAPEL' }}
                        </span>
                        <h3 class="font-heading font-bold text-lg text-black">{{ $sch->subject->name ?? 'Mata Pelajaran' }}</h3>
                        <p class="text-xs font-bold text-slate-600 mt-0.5 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                            {{ $sch->schoolClass->name ?? 'Kelas' }} (Tingkat {{ $sch->schoolClass->level ?? '-' }})
                        </p>
                    </div>
                    <div class="text-right space-y-1">
                        <span class="text-xs font-mono font-bold bg-[#FFF9DB] border-2 border-black px-2 py-1 inline-block">
                            {{ substr($sch->start_time, 0, 5) }} - {{ substr($sch->end_time, 0, 5) }}
                        </span>
                        <div>
                            <span class="text-[10px] font-black bg-[#FFD43B] text-black px-1.5 py-0.5 border border-black rounded-xs">
                                {{ $jp }} JP
                            </span>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t-2 border-slate-100 flex items-center justify-between gap-2">
                    <div class="text-[11px] text-slate-500 font-semibold flex items-center gap-1">
                        @if($isPast)
                            <span class="text-slate-400 font-medium">✓ Selesai</span>
                        @elseif($isOngoing)
                            <span class="text-emerald-600 font-bold">● Aktif sekarang</span>
                        @else
                            <span class="text-amber-600 font-medium">⏳ Belum dimulai</span>
                        @endif
                    </div>
                    <a href="{{ route('guru.attendance.manual', ['school_class_id' => $sch->school_class_id]) }}" 
                       class="neo-btn bg-[#5294FF] text-white text-xs font-bold px-3 py-1.5 cursor-pointer hover:bg-blue-600 transition-colors flex items-center gap-1">
                        Presensi Kelas →
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white neo-box p-8 text-center text-slate-500 font-semibold space-y-1">
                <div class="text-2xl">☕</div>
                <div>Tidak ada jadwal mengajar pada hari ini.</div>
                <div class="text-xs text-slate-400">Silakan cek jadwal mingguan atau kelas binaan Anda.</div>
            </div>
        @endforelse
    </div>

    <!-- TAB 2: Seluruh Jadwal Mingguan Guru -->
    <div id="tabContentWeekly" class="hidden space-y-4">
        @php
            $daysMap = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];
            $allWeekly = $weeklySchedules ?? collect();
        @endphp

        @if($allWeekly->isEmpty())
            <div class="bg-white neo-box p-8 text-center text-slate-500 font-semibold space-y-1">
                <div class="text-2xl">📅</div>
                <div>Belum ada jadwal mengajar mingguan yang terdaftar.</div>
                <div class="text-xs text-slate-400">Jadwal mingguan disusun oleh administrator sekolah.</div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($daysMap as $dNum => $dName)
                    @php
                        $dayItems = $allWeekly->where('day_of_week', $dNum)->values();
                    @endphp
                    <div class="bg-white neo-box p-4 space-y-2.5">
                        <div class="flex items-center justify-between border-b-2 border-black pb-1.5">
                            <span class="font-heading font-black text-xs uppercase text-black flex items-center gap-1.5">
                                <span>📌</span> {{ $dName }}
                            </span>
                            <span class="text-[10px] font-mono font-bold bg-[#FFF9DB] border border-black px-1.5 py-0.2">
                                {{ count($dayItems) }} Sesi
                            </span>
                        </div>

                        <div class="space-y-1.5">
                            @forelse($dayItems as $item)
                                @php
                                    $iStart = \Carbon\Carbon::parse($item->start_time);
                                    $iEnd = \Carbon\Carbon::parse($item->end_time);
                                    $iJp = max(1, (int) round($iStart->diffInMinutes($iEnd) / 40));
                                @endphp
                                <div class="p-2 bg-slate-50 border border-black rounded-xs flex items-center justify-between gap-2 text-xs">
                                    <div class="min-w-0">
                                        <div class="font-black text-black truncate">{{ $item->subject->name ?? 'Mapel' }}</div>
                                        <div class="text-[10px] text-slate-500 font-semibold">
                                            {{ $item->schoolClass->name ?? '-' }} (Tingkat {{ $item->schoolClass->level ?? '-' }})
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0 flex items-center gap-1">
                                        <span class="text-[9px] font-black bg-[#FFD43B] text-black px-1 py-0.2 border border-black rounded-xs">
                                            {{ $iJp }} JP
                                        </span>
                                        <span class="font-mono text-[10px] font-bold bg-white border border-black px-1.5 py-0.5">
                                            {{ substr($item->start_time, 0, 5) }} - {{ substr($item->end_time, 0, 5) }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="py-2 text-center text-slate-400 text-[11px] font-medium">
                                    - Tidak ada sesi -
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="p-3 bg-[#E7F5FF] border-2 border-black rounded-sm flex items-center justify-between text-xs">
                <span class="font-bold text-blue-950">
                    Total: {{ $allWeekly->count() }} Sesi Pelajaran Terjadwal per Minggu
                </span>
                <a href="{{ route('guru.profile.index') }}" class="font-black text-blue-800 underline hover:text-blue-950">
                    Buka Profil Guru →
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

        if (tab === 'today') {
            tabToday.classList.remove('hidden');
            tabWeekly.classList.add('hidden');
            btnToday.className = 'text-xs font-black px-3 py-1 rounded-xs transition-all cursor-pointer bg-white text-black border border-black shadow-xs';
            btnWeekly.className = 'text-xs font-bold px-3 py-1 rounded-xs transition-all cursor-pointer text-slate-700 hover:text-black hover:bg-white/50';
        } else {
            tabToday.classList.add('hidden');
            tabWeekly.classList.remove('hidden');
            btnWeekly.className = 'text-xs font-black px-3 py-1 rounded-xs transition-all cursor-pointer bg-white text-black border border-black shadow-xs';
            btnToday.className = 'text-xs font-bold px-3 py-1 rounded-xs transition-all cursor-pointer text-slate-700 hover:text-black hover:bg-white/50';
        }
    }
</script>
