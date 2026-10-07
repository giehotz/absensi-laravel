@php
    $totalScheduledJp = $homeroomClass->total_jp ?? 0;
    $targetJp = $homeroomClass->target_jp ?? 38;
    $percentJp = $homeroomClass->percent_jp ?? min(100, (int) round(($totalScheduledJp / max(1, $targetJp)) * 100));
    $shortageJp = $homeroomClass->shortage_jp ?? max(0, $targetJp - $totalScheduledJp);
@endphp

<!-- Container Matriks Jadwal Rombel Binaan Wali Kelas -->
<div class="space-y-5">
    <!-- Banner Status Izin Pengisian Jadwal dari Admin -->
    @if(!$canHomeroomEdit)
        @if($isDeadlineExpired)
            <div class="bg-[#FFF4E6] border-2 border-black p-4 neo-box text-amber-950 font-bold text-xs flex items-center justify-between gap-3 flex-wrap sm:flex-nowrap">
                <div class="flex items-center gap-2.5">
                    <span class="text-2xl shrink-0">⏳</span>
                    <div>
                        <span class="font-black uppercase tracking-wider block text-rose-950">Batas Waktu Pengisian Jadwal Telah Berakhir</span>
                        <span class="text-[11px] font-semibold text-amber-900">
                            Batas waktu pengisian jadwal oleh Wali Kelas telah berakhir pada <b>{{ $homeroomDeadline ? $homeroomDeadline->translatedFormat('d F Y') : '' }} (pukul 23:59 WIB)</b>. Anda saat ini hanya dapat meninjau jadwal dalam mode baca saja (read-only). Hubungi Admin sekolah jika memerlukan perpanjangan waktu.
                        </span>
                    </div>
                </div>
                <span class="neo-badge bg-[#FFE3E3] text-rose-950 font-black text-[10px] shrink-0 border border-black">
                    Batas Waktu Berakhir
                </span>
            </div>
        @else
            <div class="bg-[#FFF4E6] border-2 border-black p-4 neo-box text-amber-950 font-bold text-xs flex items-center justify-between gap-3 flex-wrap sm:flex-nowrap">
                <div class="flex items-center gap-2.5">
                    <span class="text-xl shrink-0">🔒</span>
                    <div>
                        <span class="font-black uppercase tracking-wider block">Pengisian Jadwal Sedang Dikunci oleh Administrator</span>
                        <span class="text-[11px] font-semibold text-amber-900">
                            Anda saat ini hanya dapat meninjau matriks jadwal kelas binaan Anda dalam mode baca saja (read-only). Hubungi Admin sekolah untuk membuka akses pengisian.
                        </span>
                    </div>
                </div>
                <span class="neo-badge bg-[#FFE3E3] text-rose-950 font-black text-[10px] shrink-0 border border-black">
                    Mode Baca Saja
                </span>
            </div>
        @endif
    @else
        @if($homeroomDeadline)
            @php
                $daysLeft = (int) \Carbon\Carbon::now('Asia/Jakarta')->startOfDay()->diffInDays($homeroomDeadline->copy()->startOfDay(), false);
            @endphp
            <div class="bg-[#EBFBEE] border-2 border-black p-3.5 neo-box text-emerald-950 font-bold text-xs flex items-center justify-between gap-3 flex-wrap sm:flex-nowrap">
                <div class="flex items-center gap-2.5">
                    <span class="text-2xl shrink-0">⏳</span>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-black uppercase tracking-wider block">Akses Input Jadwal Terbuka</span>
                            <span class="text-[10px] font-mono font-bold bg-[#FFF9DB] text-amber-950 px-2 py-0.5 border border-black">
                                Batas Waktu: {{ $homeroomDeadline->translatedFormat('d F Y') }} (23:59 WIB)
                            </span>
                        </div>
                        <span class="text-[11px] font-semibold text-emerald-900 mt-0.5 block">
                            Sebagai Wali Kelas, Anda berhak menyusun, mengubah, dan menghapus jadwal kelas <b>{{ $homeroomClass->name }}</b>.
                            @if($daysLeft === 0)
                                <b class="text-amber-900 underline">Hari ini adalah batas waktu terakhir pengisian!</b>
                            @elseif($daysLeft > 0)
                                Tersisa waktu <b>{{ $daysLeft }} hari</b> lagi sebelum akses otomatis ditutup.
                            @endif
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    @if($daysLeft <= 3)
                        <span class="neo-badge bg-[#FFE3E3] text-rose-950 font-black text-[10px] border border-black">
                            ⚠️ Sisa {{ max(0, $daysLeft) }} Hari
                        </span>
                    @else
                        <span class="neo-badge bg-[#FFF3BF] text-amber-950 font-black text-[10px] border border-black">
                            ⏳ Sisa {{ $daysLeft }} Hari
                        </span>
                    @endif
                    <span class="neo-badge bg-[#20C997] text-black font-black text-[10px] border border-black">
                        Akses Penuh
                    </span>
                </div>
            </div>
        @else
            <div class="bg-[#EBFBEE] border-2 border-black p-3.5 neo-box text-emerald-950 font-bold text-xs flex items-center justify-between gap-3 flex-wrap sm:flex-nowrap">
                <div class="flex items-center gap-2.5">
                    <span class="text-xl shrink-0">✅</span>
                    <div>
                        <span class="font-black uppercase tracking-wider block">Akses Input Jadwal Terbuka</span>
                        <span class="text-[11px] font-semibold text-emerald-900">
                            Sebagai Wali Kelas, Anda berhak menyusun, mengubah, dan menghapus jadwal pelajaran untuk kelas <b>{{ $homeroomClass->name }}</b>.
                        </span>
                    </div>
                </div>
                <span class="neo-badge bg-[#20C997] text-black font-black text-[10px] shrink-0 border border-black">
                    Akses Penuh
                </span>
            </div>
        @endif
    @endif

    <!-- Header Kelas Binaan & Regulasi Beban KBM EMIS GTK -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        <!-- Info Identitas Rombel & Wali Kelas -->
        <div class="lg:col-span-5 bg-white neo-box p-4 flex flex-col justify-between space-y-3">
            <div class="space-y-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="neo-badge bg-[#5294FF] text-white font-black text-[11px]">KELAS BINAAN</span>
                    <span class="text-xs font-mono font-bold bg-[#E7F5FF] text-blue-950 px-2 py-0.5 border border-black">
                        Tingkat {{ $homeroomClass->level }}
                    </span>
                    <span class="text-xs font-mono font-bold bg-[#FFF9DB] text-amber-950 px-2 py-0.5 border border-black">
                        {{ $homeroomClass->academicYear->name ?? 'TA Aktif' }}
                    </span>
                </div>
                <h3 class="font-heading font-black text-xl text-black uppercase mt-1">
                    {{ $homeroomClass->name }}
                </h3>
                <p class="text-xs font-semibold text-slate-600">
                    Wali Kelas: <b>{{ $teacher->user->name ?? '-' }}</b> (NIP: {{ $teacher->nip ?? '-' }})
                </p>
            </div>

            <div class="pt-2 border-t border-black/10 flex items-center justify-between gap-2">
                <span class="text-[11px] font-bold text-slate-600">
                    Jumlah Siswa: <b>{{ $homeroomClass->students->count() ?? '-' }} Siswa</b>
                </span>
                @if($canHomeroomEdit)
                    <button type="button" 
                            onclick="openCreateModalHomeroom(1, '07:15', '07:55')"
                            class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black text-xs font-black px-3 py-1.5 flex items-center gap-1 cursor-pointer border border-black shadow-[2px_2px_0px_0px_#000]">
                        <span>➕</span> Tambah Jadwal
                    </button>
                @endif
            </div>
        </div>

        <!-- Beban KBM & Target Jam Pelajaran (EMIS GTK 38 JP) -->
        <div class="lg:col-span-7 bg-white neo-box p-4 flex flex-col justify-between space-y-3">
            <div class="flex items-center justify-between gap-2">
                <div>
                    <h4 class="font-heading font-black text-xs uppercase text-black flex items-center gap-1.5">
                        <span>📊</span> Kesesuaian Beban Regulasi KBM (EMIS GTK)
                    </h4>
                    <p class="text-[11px] font-medium text-slate-600">
                        Target {{ $targetJp }} JP · Terjadwal <b>{{ $totalScheduledJp }} JP</b> ({{ $homeroomClass->total_sessions }} Sesi) · Kurang <b>{{ $shortageJp }} JP</b>
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

    <!-- Matriks Mingguan Jadwal Pelajaran (Senin s.d. Sabtu) -->
    <div class="bg-white neo-box overflow-hidden">
        <div class="p-4 border-b-2 border-black bg-[#FFF9DB] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="font-heading font-black text-sm uppercase text-black flex items-center gap-1.5">
                    <span>🗓️</span> MATRIKS JADWAL KELAS {{ $homeroomClass->name }} (EMIS GTK)
                </h3>
                <p class="text-[11px] font-semibold text-slate-600">
                    @if($canHomeroomEdit)
                        Klik tombol <b>[+]</b> pada slot KBM kosong untuk menambah jadwal. Klik pada jadwal terisi untuk mengubah atau menghapus.
                    @else
                        Mode baca saja. Klik pada kartu jadwal untuk melihat rincian informasi sesi mengajar.
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-mono font-bold bg-white px-2.5 py-1 border border-black">
                    Total: <b>{{ $homeroomSchedules->count() }} Sesi</b> ({{ $totalScheduledJp }} JP)
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
                                $daySchedCount = $homeroomSchedules->where('day_of_week', $dNum)->count();
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
                                        <!-- Slot KBM -->
                                        @php
                                            $matchedSchedule = $homeroomSchedules->first(function ($s) use ($day, $slot) {
                                                if ($s->day_of_week != $day) return false;
                                                $schStart = substr($s->start_time, 0, 5);
                                                $schEnd = substr($s->end_time, 0, 5);
                                                $slotStart = substr($slot->start_time, 0, 5);
                                                $slotEnd = substr($slot->end_time, 0, 5);
                                                return ($schStart < $slotEnd && $schEnd > $slotStart);
                                            });
                                        @endphp

                                        @if($matchedSchedule)
                                            @php
                                                $sStart = \Carbon\Carbon::parse($matchedSchedule->start_time);
                                                $sEnd = \Carbon\Carbon::parse($matchedSchedule->end_time);
                                                $durationMin = $sStart->diffInMinutes($sEnd);
                                                $estimatedJp = max(1, (int) round($durationMin / 40));

                                                $isMultiJamSlot = false;
                                                $isFirstSlotOfMultiJam = false;
                                                if ($estimatedJp > 1) {
                                                    $isMultiJamSlot = true;
                                                    $slotStart = substr($slot->start_time, 0, 5);
                                                    $schStartStr = substr($matchedSchedule->start_time, 0, 5);
                                                    $isFirstSlotOfMultiJam = ($slotStart <= $schStartStr);
                                                }

                                                $schData = [
                                                    'id' => $matchedSchedule->id,
                                                    'school_class_id' => $matchedSchedule->school_class_id,
                                                    'subject_id' => $matchedSchedule->subject_id,
                                                    'teacher_id' => $matchedSchedule->teacher_id,
                                                    'day_of_week' => $matchedSchedule->day_of_week,
                                                    'start_time' => substr($matchedSchedule->start_time, 0, 5),
                                                    'end_time' => substr($matchedSchedule->end_time, 0, 5),
                                                    'subject_name' => $matchedSchedule->subject->name ?? 'Mapel',
                                                    'teacher_name' => $matchedSchedule->teacher->user->name ?? 'Guru',
                                                    'teacher_nip' => $matchedSchedule->teacher->nip ?? '-',
                                                    'class_name' => $matchedSchedule->schoolClass->name ?? '',
                                                    'day_name' => [1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu'][$matchedSchedule->day_of_week] ?? '',
                                                    'jp' => $estimatedJp
                                                ];
                                            @endphp

                                            <!-- Kartu Sesi Terjadwal -->
                                            <div data-schedule="{{ json_encode($schData) }}"
                                                 onclick="showScheduleDetailHomeroomFromCard(this)"
                                                 class="group relative border-2 border-black bg-[#E7F5FF] hover:bg-[#D0EBFF] p-2 rounded flex flex-col justify-between h-24 text-left cursor-pointer transition-all shadow-[2px_2px_0px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5">
                                                
                                                <div class="space-y-0.5">
                                                    <div class="flex items-center justify-between gap-1">
                                                        <span class="font-mono text-[9px] font-black bg-white border border-black px-1 rounded">
                                                            {{ substr($matchedSchedule->start_time, 0, 5) }}-{{ substr($matchedSchedule->end_time, 0, 5) }}
                                                        </span>
                                                        <span class="neo-badge bg-[#5294FF] text-white text-[8px] font-black px-1 py-0.2">
                                                            {{ $estimatedJp }} JP
                                                        </span>
                                                    </div>

                                                    <h4 class="font-heading font-black text-[11px] text-blue-950 uppercase leading-snug line-clamp-1 group-hover:underline">
                                                        {{ $matchedSchedule->subject->name ?? 'Mapel' }}
                                                    </h4>
                                                </div>

                                                <div class="border-t border-black/15 pt-1 flex items-center justify-between text-[10px] text-slate-700">
                                                    <span class="font-bold truncate max-w-[80px] sm:max-w-[100px]" title="{{ $matchedSchedule->teacher->user->name ?? 'Guru' }}">
                                                        👤 {{ $matchedSchedule->teacher->user->name ?? 'Guru' }}
                                                    </span>

                                                    @if($canHomeroomEdit)
                                                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                            <button type="button" 
                                                                    title="Ubah Jadwal"
                                                                    onclick="event.stopPropagation(); triggerDirectEditHomeroom(this)"
                                                                    class="px-1.5 py-0.5 bg-[#FFD43B] hover:bg-amber-400 text-black border border-black rounded text-[9px] font-black shadow-[1px_1px_0px_0px_#000] cursor-pointer">
                                                                ✏️
                                                            </button>
                                                            <button type="button" 
                                                                    title="Hapus Jadwal"
                                                                    onclick="event.stopPropagation(); triggerDirectDeleteHomeroom(this)"
                                                                    class="px-1.5 py-0.5 bg-[#FFE3E3] hover:bg-rose-300 text-rose-950 border border-black rounded text-[9px] font-black shadow-[1px_1px_0px_0px_#000] cursor-pointer">
                                                                🗑️
                                                            </button>
                                                        </div>
                                                    @else
                                                        <span class="text-[9px] text-blue-900 font-bold opacity-0 group-hover:opacity-100">
                                                            Detail ↗
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <!-- Slot Kosong KBM -->
                                            <div class="border-2 border-dashed border-slate-300 hover:border-black p-2 rounded flex flex-col items-center justify-center h-24 text-slate-400 hover:text-black bg-slate-50 hover:bg-[#FFF9DB] transition-all">
                                                <div class="font-mono text-[9px] text-slate-500 font-semibold mb-1">
                                                    {{ $slot->getShortStartTime() }} - {{ $slot->getShortEndTime() }}
                                                </div>

                                                @if($canHomeroomEdit)
                                                    <button type="button" 
                                                            onclick="openCreateModalHomeroom({{ $day }}, '{{ $slot->getShortStartTime() }}', '{{ $slot->getShortEndTime() }}')"
                                                            class="neo-btn bg-white hover:bg-[#FFD43B] text-black border border-black text-[10px] font-black px-2 py-1 shadow-[1px_1px_0px_0px_#000] cursor-pointer flex items-center gap-1">
                                                        <span>➕</span> Isi Slot
                                                    </button>
                                                @else
                                                    <span class="text-[10px] font-bold text-slate-400">(Kosong)</span>
                                                @endif
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
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL DETAIL JADWAL (HOMEROOM) -->
<!-- ========================================================================= -->
<div id="detailScheduleModalHomeroom" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden" onclick="closeDetailModalHomeroom()">
    <div class="bg-white neo-box-lg max-w-md w-full p-6 space-y-4 relative" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <div class="flex items-center gap-2">
                <span class="text-2xl">📋</span>
                <div>
                    <h3 class="font-heading font-black text-base text-black uppercase" id="detailModalTitleHomeroom">
                        Detail Jadwal Pelajaran
                    </h3>
                    <p class="text-xs font-semibold text-slate-600">
                        Kelas {{ $homeroomClass->name }}
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeDetailModalHomeroom()" class="text-black font-black text-lg hover:opacity-75 cursor-pointer">✕</button>
        </div>

        <!-- Detail Info Card -->
        <div class="bg-slate-50 border-2 border-black p-4 rounded space-y-2.5 text-xs">
            <div class="flex items-start justify-between border-b border-slate-200 pb-2">
                <span class="text-slate-600 font-bold">Mata Pelajaran:</span>
                <span class="font-heading font-black text-sm text-black text-right" id="detailSubjectNameHomeroom">-</span>
            </div>
            <div class="flex items-start justify-between border-b border-slate-200 pb-2">
                <span class="text-slate-600 font-bold">Guru Pengampu:</span>
                <div class="text-right">
                    <span class="font-bold text-black block" id="detailTeacherNameHomeroom">-</span>
                    <span class="font-mono text-[10px] text-slate-500 block" id="detailTeacherNipHomeroom">NIP: -</span>
                </div>
            </div>
            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                <span class="text-slate-600 font-bold">Rombel / Kelas:</span>
                <span class="font-bold text-black" id="detailClassNameHomeroom">Kelas {{ $homeroomClass->name }}</span>
            </div>
            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                <span class="text-slate-600 font-bold">Hari & Waktu:</span>
                <span class="font-mono font-black text-blue-900 bg-[#E7F5FF] border border-black px-2 py-0.5 rounded" id="detailTimeRangeHomeroom">-</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-600 font-bold">Beban JP:</span>
                <span class="neo-badge bg-[#FFD43B] text-black font-black text-xs" id="detailJpBadgeHomeroom">1 JP</span>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex items-center justify-between border-t-2 border-black pt-3">
            @if($canHomeroomEdit)
                <form id="deleteScheduleFormHomeroom" method="POST" action="" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="button" onclick="confirmDeleteScheduleHomeroom()" class="neo-btn bg-[#FFE3E3] hover:bg-[#ffc9c9] text-rose-950 text-xs font-bold px-3 py-2 cursor-pointer border border-black flex items-center gap-1">
                        <span>🗑️</span> Hapus
                    </button>
                </form>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="closeDetailModalHomeroom()" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-xs font-bold px-3.5 py-2 cursor-pointer">
                        Tutup
                    </button>
                    <button type="button" onclick="triggerEditFromDetailHomeroom()" class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black text-xs font-black px-4 py-2 cursor-pointer shadow-[2px_2px_0px_0px_#000] flex items-center gap-1">
                        <span>✏️</span> Ubah Jadwal
                    </button>
                </div>
            @else
                <div class="w-full flex justify-end">
                    <button type="button" onclick="closeDetailModalHomeroom()" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-xs font-bold px-4 py-2 cursor-pointer">
                        Tutup
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

@if($canHomeroomEdit)
<!-- ========================================================================= -->
<!-- MODAL TAMBAH / UBAH JADWAL (HOMEROOM) -->
<!-- ========================================================================= -->
<div id="scheduleFormModalHomeroom" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden" onclick="closeFormModalHomeroom()">
    <div class="bg-white neo-box-lg max-w-lg w-full p-6 space-y-4 relative max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <div class="flex items-center gap-2">
                <span class="text-2xl" id="formModalIconHomeroom">➕</span>
                <div>
                    <h3 class="font-heading font-black text-base text-black uppercase" id="formModalTitleHomeroom">
                        Tambah Jadwal Pelajaran
                    </h3>
                    <p class="text-xs font-semibold text-slate-600">
                        Kelas {{ $homeroomClass->name }} (Tingkat {{ $homeroomClass->level }})
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeFormModalHomeroom()" class="text-black font-black text-lg hover:opacity-75 cursor-pointer">✕</button>
        </div>

        <form id="scheduleFormHomeroom" method="POST" action="{{ route('guru.schedules.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="_method" id="formMethodHomeroom" value="POST">
            <input type="hidden" name="school_class_id" value="{{ $homeroomClass->id }}">

            <!-- Alert Peringatan Pembatasan Guru -->
            <div id="assignmentAlertHomeroom" class="hidden p-3 bg-[#FFF3BF] border-2 border-black rounded text-xs space-y-1">
                <div class="font-black flex items-center gap-1 text-black">
                    <span>⚠️</span> Catatan Penugasan Guru:
                </div>
                <p id="assignmentAlertTextHomeroom" class="text-slate-800 font-medium"></p>
            </div>

            <!-- 1. Pilihan Mata Pelajaran -->
            <div>
                <label for="modalSubjectIdHomeroom" class="block text-xs font-black uppercase text-black mb-1">
                    Mata Pelajaran <span class="text-rose-600">*</span>
                </label>
                <select name="subject_id" id="modalSubjectIdHomeroom" required
                        class="w-full bg-white border-2 border-black p-2 text-xs font-bold text-black focus:outline-hidden">
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    @foreach($subjects as $subj)
                        <option value="{{ $subj->id }}">{{ $subj->name }} ({{ $subj->code }})</option>
                    @endforeach
                </select>
            </div>

            <!-- 2. Pilihan Guru Pengampu -->
            <div>
                <label for="modalTeacherIdHomeroom" class="block text-xs font-black uppercase text-black mb-1">
                    Guru Pengampu <span class="text-rose-600">*</span>
                </label>
                <select name="teacher_id" id="modalTeacherIdHomeroom" required onchange="checkTeacherAssignmentRestrictionHomeroom()"
                        class="w-full bg-white border-2 border-black p-2 text-xs font-bold text-black focus:outline-hidden">
                    <option value="">-- Pilih Guru Pengampu --</option>
                    @foreach($teachers as $tch)
                        <option value="{{ $tch->id }}" {{ $tch->id == $teacher->id ? 'selected' : '' }}>
                            {{ $tch->user->name ?? 'Guru' }} {{ $tch->nip ? '(NIP: '.$tch->nip.')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- 3. Hari Pelaksanaan -->
            <div>
                <label for="modalDayOfWeekHomeroom" class="block text-xs font-black uppercase text-black mb-1">
                    Hari Pelaksanaan <span class="text-rose-600">*</span>
                </label>
                <select name="day_of_week" id="modalDayOfWeekHomeroom" required onchange="onDayChangeHomeroom()"
                        class="w-full bg-white border-2 border-black p-2 text-xs font-bold text-black focus:outline-hidden">
                    @foreach([1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'] as $dVal => $dLabel)
                        <option value="{{ $dVal }}">{{ $dLabel }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 4. Rentang Waktu (Jam Mulai - Jam Selesai) -->
            <div class="bg-slate-50 border-2 border-black p-3.5 rounded space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black uppercase text-black">Alokasi Waktu KBM</span>
                    <span id="calculatedJpHomeroom" class="neo-badge bg-[#5294FF] text-white text-[10px] font-black">
                        1 JP (40 Menit)
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="modalStartTimeHomeroom" class="block text-[11px] font-bold text-slate-700 mb-1">
                            Jam Mulai (WIB):
                        </label>
                        <input type="time" name="start_time" id="modalStartTimeHomeroom" required onchange="recalculateJpHomeroom()"
                               class="w-full bg-white border-2 border-black p-1.5 font-mono text-xs font-bold text-black focus:outline-hidden">
                    </div>
                    <div>
                        <label for="modalEndTimeHomeroom" class="block text-[11px] font-bold text-slate-700 mb-1">
                            Jam Selesai (WIB):
                        </label>
                        <input type="time" name="end_time" id="modalEndTimeHomeroom" required onchange="recalculateJpHomeroom()"
                               class="w-full bg-white border-2 border-black p-1.5 font-mono text-xs font-bold text-black focus:outline-hidden">
                    </div>
                </div>

                <!-- Pemilih Rentang Jam ke-X s.d. Jam ke-Y Cepat -->
                <div class="pt-2 border-t border-slate-200">
                    <label class="block text-[10px] font-bold uppercase text-slate-600 mb-1">
                        Atau Pilih Rentang Jam Berurutan (Multi-Jam):
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <select id="quickSlotStartHomeroom" onchange="applyQuickSlotHomeroom()"
                                class="bg-white border border-black p-1 text-[11px] font-semibold text-slate-800">
                            <option value="">-- Jam Mulai --</option>
                        </select>
                        <select id="quickSlotEndHomeroom" onchange="applyQuickSlotHomeroom()"
                                class="bg-white border border-black p-1 text-[11px] font-semibold text-slate-800">
                            <option value="">-- Jam Selesai --</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Tombol Submit & Batal -->
            <div class="flex items-center justify-end gap-2 border-t-2 border-black pt-3">
                <button type="button" onclick="closeFormModalHomeroom()" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-xs font-bold px-4 py-2 cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="formSubmitBtnHomeroom" class="neo-btn bg-[#20C997] hover:bg-emerald-400 text-black text-xs font-black px-5 py-2 cursor-pointer shadow-[2px_2px_0px_0px_#000]">
                    Simpan Jadwal
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- ========================================================================= -->
<!-- JAVASCRIPT LOGIC KHUSUS MATRIKS WALI KELAS -->
<!-- ========================================================================= -->
<script>
    const allSlotsByDayHomeroom = @json($allSlotsByDay ?? []);
    const teacherAssignmentsMapHomeroom = @json($teacherAssignmentsMap ?? []);
    const homeroomClassId = {{ (int) ($homeroomClass->id ?? 0) }};
    const scheduleBaseUrl = "{{ url('/guru/jadwal') }}";

    let activeSelectedScheduleHomeroom = null;

    function showScheduleDetailHomeroomFromCard(el) {
        if (!el || !el.dataset.schedule) return;
        try {
            const schData = JSON.parse(el.dataset.schedule);
            showScheduleDetailHomeroom(schData);
        } catch (e) {
            console.error('Error parsing schedule data:', e);
        }
    }

    function showScheduleDetailHomeroom(schData) {
        activeSelectedScheduleHomeroom = schData;

        document.getElementById('detailSubjectNameHomeroom').textContent = schData.subject_name;
        document.getElementById('detailTeacherNameHomeroom').textContent = schData.teacher_name;
        document.getElementById('detailTeacherNipHomeroom').textContent = 'NIP: ' + schData.teacher_nip;
        document.getElementById('detailTimeRangeHomeroom').textContent = `${schData.day_name}, ${schData.start_time} - ${schData.end_time}`;
        document.getElementById('detailJpBadgeHomeroom').textContent = schData.jp + ' JP';

        @if($canHomeroomEdit)
            const deleteForm = document.getElementById('deleteScheduleFormHomeroom');
            if (deleteForm) {
                deleteForm.action = `${scheduleBaseUrl}/${schData.id}`;
            }
        @endif

        document.getElementById('detailScheduleModalHomeroom').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeDetailModalHomeroom() {
        document.getElementById('detailScheduleModalHomeroom').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        activeSelectedScheduleHomeroom = null;
    }

    @if($canHomeroomEdit)
    function triggerDirectEditHomeroom(btn) {
        const card = btn.closest('[data-schedule]');
        if (!card) return;
        try {
            const sch = JSON.parse(card.dataset.schedule);
            openEditModalHomeroom(sch);
        } catch (e) {
            console.error('Error in triggerDirectEditHomeroom:', e);
        }
    }

    function triggerDirectDeleteHomeroom(btn) {
        const card = btn.closest('[data-schedule]');
        if (!card) return;
        try {
            const sch = JSON.parse(card.dataset.schedule);
            activeSelectedScheduleHomeroom = sch;
            document.getElementById('detailSubjectNameHomeroom').textContent = sch.subject_name;
            document.getElementById('detailTimeRangeHomeroom').textContent = `${sch.day_name}, ${sch.start_time} - ${sch.end_time}`;
            const deleteForm = document.getElementById('deleteScheduleFormHomeroom');
            if (deleteForm) {
                deleteForm.action = `${scheduleBaseUrl}/${sch.id}`;
            }
            confirmDeleteScheduleHomeroom();
        } catch (e) {
            console.error('Error in triggerDirectDeleteHomeroom:', e);
        }
    }

    function confirmDeleteScheduleHomeroom() {
        const form = document.getElementById('deleteScheduleFormHomeroom');
        if (!form || !form.action) return;

        const subj = document.getElementById('detailSubjectNameHomeroom')?.textContent || 'mata pelajaran ini';
        const timeRange = document.getElementById('detailTimeRangeHomeroom')?.textContent || '';

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus Jadwal Pelajaran?',
                html: `Apakah Anda yakin ingin menghapus jadwal <b>${subj}</b> (${timeRange})?<br><span class="text-xs text-slate-500 mt-1 block">Slot jam terkait akan dikosongkan.</span>`,
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
        } else if (confirm(`Apakah Anda yakin ingin menghapus jadwal ${subj} (${timeRange})? Slot jam terkait akan dikosongkan.`)) {
            form.submit();
        }
    }

    function triggerEditFromDetailHomeroom() {
        if (!activeSelectedScheduleHomeroom) return;
        const sch = activeSelectedScheduleHomeroom;
        closeDetailModalHomeroom();
        openEditModalHomeroom(sch);
    }

    function openCreateModalHomeroom(day, startTime, endTime) {
        const form = document.getElementById('scheduleFormHomeroom');
        if (!form) return;
        form.action = "{{ route('guru.schedules.store') }}";
        document.getElementById('formMethodHomeroom').value = 'POST';

        document.getElementById('formModalTitleHomeroom').textContent = 'Tambah Jadwal Pelajaran';
        document.getElementById('formModalIconHomeroom').textContent = '➕';
        document.getElementById('formSubmitBtnHomeroom').textContent = 'Simpan Jadwal Baru';

        document.getElementById('modalSubjectIdHomeroom').value = '';
        document.getElementById('modalTeacherIdHomeroom').value = '{{ $teacher->id ?? "" }}';
        document.getElementById('modalDayOfWeekHomeroom').value = day || 1;
        document.getElementById('modalStartTimeHomeroom').value = startTime || '07:15';
        document.getElementById('modalEndTimeHomeroom').value = endTime || '07:55';

        onDayChangeHomeroom();
        checkTeacherAssignmentRestrictionHomeroom();
        recalculateJpHomeroom();

        document.getElementById('scheduleFormModalHomeroom').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function openEditModalHomeroom(sch) {
        const form = document.getElementById('scheduleFormHomeroom');
        if (!form) return;
        form.action = `${scheduleBaseUrl}/${sch.id}`;
        document.getElementById('formMethodHomeroom').value = 'PUT';

        document.getElementById('formModalTitleHomeroom').textContent = 'Ubah Jadwal Pelajaran';
        document.getElementById('formModalIconHomeroom').textContent = '✏️';
        document.getElementById('formSubmitBtnHomeroom').textContent = 'Perbarui Jadwal';

        document.getElementById('modalSubjectIdHomeroom').value = sch.subject_id;
        document.getElementById('modalTeacherIdHomeroom').value = sch.teacher_id;
        document.getElementById('modalDayOfWeekHomeroom').value = sch.day_of_week;
        document.getElementById('modalStartTimeHomeroom').value = sch.start_time;
        document.getElementById('modalEndTimeHomeroom').value = sch.end_time;

        onDayChangeHomeroom();
        checkTeacherAssignmentRestrictionHomeroom();
        recalculateJpHomeroom();

        document.getElementById('scheduleFormModalHomeroom').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeFormModalHomeroom() {
        document.getElementById('scheduleFormModalHomeroom').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function recalculateJpHomeroom() {
        const startVal = document.getElementById('modalStartTimeHomeroom').value;
        const endVal = document.getElementById('modalEndTimeHomeroom').value;
        const badge = document.getElementById('calculatedJpHomeroom');

        if (!startVal || !endVal) {
            badge.textContent = '- JP';
            return;
        }

        const [sH, sM] = startVal.split(':').map(Number);
        const [eH, eM] = endVal.split(':').map(Number);

        const startMin = sH * 60 + sM;
        const endMin = eH * 60 + eM;
        const diff = endMin - startMin;

        if (diff <= 0) {
            badge.textContent = 'Waktu Tidak Valid';
            badge.className = 'neo-badge bg-[#FFE3E3] text-rose-950 text-[10px] font-black';
            return;
        }

        const jp = Math.max(1, Math.round(diff / 40));
        badge.textContent = `${jp} JP (${diff} Menit)`;
        badge.className = 'neo-badge bg-[#5294FF] text-white text-[10px] font-black';
    }

    function onDayChangeHomeroom() {
        const day = document.getElementById('modalDayOfWeekHomeroom').value;
        const daySlots = allSlotsByDayHomeroom[day] || [];
        const kbmSlots = daySlots.filter(s => s.k_jadwal === 0);

        const startSel = document.getElementById('quickSlotStartHomeroom');
        const endSel = document.getElementById('quickSlotEndHomeroom');

        startSel.innerHTML = '<option value="">-- Jam Mulai --</option>';
        endSel.innerHTML = '<option value="">-- Jam Selesai --</option>';

        kbmSlots.forEach(s => {
            const sStart = (s.start_time || '').substring(0, 5);
            const sEnd = (s.end_time || '').substring(0, 5);
            const label = (s.name || `Jam ke-${s.jam_ke}`) + ` (${sStart} - ${sEnd})`;

            const opt1 = document.createElement('option');
            opt1.value = sStart;
            opt1.textContent = label;
            startSel.appendChild(opt1);

            const opt2 = document.createElement('option');
            opt2.value = sEnd;
            opt2.textContent = label;
            endSel.appendChild(opt2);
        });
    }

    function applyQuickSlotHomeroom() {
        const startVal = document.getElementById('quickSlotStartHomeroom').value;
        const endVal = document.getElementById('quickSlotEndHomeroom').value;

        if (startVal) {
            document.getElementById('modalStartTimeHomeroom').value = startVal;
        }
        if (endVal) {
            document.getElementById('modalEndTimeHomeroom').value = endVal;
        }

        recalculateJpHomeroom();
    }

    function checkTeacherAssignmentRestrictionHomeroom() {
        const teacherId = document.getElementById('modalTeacherIdHomeroom').value;
        const alertBox = document.getElementById('assignmentAlertHomeroom');
        const alertText = document.getElementById('assignmentAlertTextHomeroom');
        const subjectSelect = document.getElementById('modalSubjectIdHomeroom');

        if (!teacherId || !teacherAssignmentsMapHomeroom[teacherId]) {
            alertBox.classList.add('hidden');
            return;
        }

        const info = teacherAssignmentsMapHomeroom[teacherId];
        if (info.has_restrictions) {
            const isAllowedClass = info.allowed_classes.includes(homeroomClassId);
            if (!isAllowedClass) {
                alertBox.classList.remove('hidden');
                alertText.innerHTML = `Perhatian: Guru ini memiliki pembatasan mengajar dan <b>tidak terdaftar untuk kelas binaan ini</b>. Pilih guru lain atau hubungi admin.`;
            } else {
                alertBox.classList.remove('hidden');
                alertText.innerHTML = `Info: Guru ini hanya diizinkan mengajar mata pelajaran tertentu pada rombel ini. Pastikan mata pelajaran yang dipilih sesuai dengan SK pembagian tugas.`;
            }

            Array.from(subjectSelect.options).forEach(opt => {
                if (!opt.value) return;
                const isAllowed = info.allowed_subjects.includes(parseInt(opt.value));
                opt.text = opt.text.replace(' ⚠️ (Bukan Penugasan Resmi)', '');
                if (!isAllowed) {
                    opt.text += ' ⚠️ (Bukan Penugasan Resmi)';
                }
            });
        } else {
            alertBox.classList.add('hidden');
            Array.from(subjectSelect.options).forEach(opt => {
                opt.text = opt.text.replace(' ⚠️ (Bukan Penugasan Resmi)', '');
            });
        }
    }
    @endif
</script>
