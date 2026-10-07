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
                Total: <b>{{ $schedules->count() }} Sesi</b> ({{ $selectedClass->total_jp ?? 0 }} JP)
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
