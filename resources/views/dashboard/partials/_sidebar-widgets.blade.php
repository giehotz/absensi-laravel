<!-- SECTION 5B: STICKY ACTIVITY LOG & COMPLIANCE SUMMARY (4-COL SIDEBAR) -->
<aside class="lg:col-span-4 space-y-5 lg:sticky lg:top-20">

    <!-- WIDGET 1: AKTIVITAS PRESENSI TERAKHIR -->
    <div class="neo-card bg-white rounded-2xl overflow-hidden">
        <!-- Tab Switcher Header -->
        <div class="p-3 bg-slate-50 border-b-2 border-slate-900 flex items-center justify-between gap-2">
            <div class="flex items-center gap-1.5">
                <button type="button" id="tabScan" onclick="switchActivityTab('scan')" class="tab-btn neo-btn bg-slate-900 text-white rounded-lg px-2.5 py-1.5 text-[11px] font-bold flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="scan-line" class="w-3.5 h-3.5"></i>
                    <span>Log Scan &amp; Manual</span>
                </button>
                <button type="button" id="tabJurnal" onclick="switchActivityTab('jurnal')" class="tab-btn neo-btn bg-white text-slate-800 rounded-lg px-2.5 py-1.5 text-[11px] font-bold flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="file-check" class="w-3.5 h-3.5"></i>
                    <span>Jurnal &amp; Izin</span>
                </button>
            </div>
            <span class="text-[10px] font-extrabold text-slate-500 uppercase font-mono">
                {{ $carbonDate->translatedFormat('d M') }}
            </span>
        </div>

        <!-- Panel Subheader -->
        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
            <h4 id="activityHeaderTitle" class="text-xs font-extrabold uppercase tracking-wider text-slate-900">
                Aktivitas Presensi Terakhir
            </h4>
            <a href="{{ route('admin.reports.attendance', ['start_date' => $date, 'end_date' => $date, 'tab' => 'logs']) }}" class="text-[11px] font-bold text-blue-600 hover:underline flex items-center gap-0.5">
                Lihat Semua Log
                <i data-lucide="arrow-right" class="w-3 h-3"></i>
            </a>
        </div>

        <!-- Tab 1: Log Scan & Input Presensi Terbaru -->
        <div id="panelScan" class="p-4">
            @if($recentAttendances->count() > 0)
                <div class="space-y-2 max-h-[460px] overflow-y-auto pr-1 custom-scroll">
                    @foreach($recentAttendances as $att)
                        <div class="p-2.5 border-2 border-slate-900 rounded-lg flex items-center justify-between gap-2.5 shadow-[1.5px_1.5px_0_#0F172A]
                            @if($att->status === 'hadir') bg-[#F4FBF7] 
                            @elseif($att->status === 'terlambat') bg-[#FFFDF5] 
                            @elseif($att->status === 'izin' || $att->status === 'sakit') bg-[#F0F8FF] 
                            @else bg-slate-50 @endif">
                            
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-8 h-8 bg-white border-2 border-slate-900 rounded-lg flex items-center justify-center font-black text-xs shrink-0 shadow-[1px_1px_0_#0F172A]">
                                    {{ substr($att->student->user->name ?? 'S', 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-xs text-slate-900 truncate">{{ $att->student->user->name ?? 'Siswa' }}</div>
                                    <div class="text-[10px] text-slate-500 flex items-center gap-1 font-mono">
                                        <span>{{ $att->student->schoolClass->name ?? '-' }}</span>
                                        <span>•</span>
                                        <span class="font-bold text-slate-800">{{ $att->check_in_time ? $att->check_in_time->format('H:i') . ' WIB' : '-' }}</span>
                                        <span>•</span>
                                        <span class="uppercase text-[9px] px-1 py-0 border border-slate-300 bg-white font-bold rounded">
                                            {{ $att->method === 'qr' ? '📷 QR' : '✍️ Manual' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="shrink-0">
                                <span class="inline-flex items-center border border-slate-900 rounded px-2 py-0.5 text-[10px] font-black uppercase shadow-[1px_1px_0_#0F172A]
                                    @if($att->status === 'hadir') bg-emerald-500 text-white 
                                    @elseif($att->status === 'terlambat') bg-amber-400 text-slate-950 
                                    @elseif($att->status === 'izin') bg-sky-400 text-slate-950 
                                    @elseif($att->status === 'sakit') bg-blue-500 text-white 
                                    @else bg-rose-500 text-white @endif">
                                    {{ $att->status }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Clean Empty State Body -->
                <div class="p-7 text-center">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-blue-50 border-2 border-slate-900 flex items-center justify-center mb-3 shadow-[2px_2px_0_#0F172A]">
                        <i data-lucide="inbox" class="w-6 h-6 text-blue-600"></i>
                    </div>
                    <p class="text-xs font-bold text-slate-800">Belum ada aktivitas presensi</p>
                    <p class="text-[11px] text-slate-500 mt-1">
                        Scan kartu atau input manual pada tanggal {{ $carbonDate->translatedFormat('d M Y') }} akan muncul di sini.
                    </p>
                </div>
            @endif
        </div>

        <!-- Tab 2: Pengajuan Izin Pending & Jurnal Guru Hari Ini -->
        <div id="panelJurnal" class="p-4 space-y-4 hidden">
            <!-- Sub-Widget 1: Pengajuan Izin Menunggu Konfirmasi -->
            <div class="space-y-2">
                <div class="flex items-center justify-between pb-1 border-b border-slate-100">
                    <span class="text-[11px] font-extrabold uppercase text-slate-900 flex items-center gap-1.5">
                        <i data-lucide="inbox" class="w-3.5 h-3.5 text-rose-500"></i>
                        <span>Izin Menunggu</span>
                    </span>
                    <span class="inline-flex items-center bg-rose-100 text-rose-800 border border-slate-900 rounded px-1.5 py-0.5 text-[10px] font-extrabold">
                        {{ $recentPendingLeaves->count() }} PENDING
                    </span>
                </div>

                <div class="space-y-2 max-h-[190px] overflow-y-auto pr-1 custom-scroll">
                    @forelse($recentPendingLeaves as $leave)
                        <div class="p-2 border-2 border-slate-900 bg-rose-50/50 rounded-lg space-y-1 shadow-[1px_1px_0_#0F172A]">
                            <div class="flex items-center justify-between">
                                <div class="font-bold text-xs text-slate-900">{{ $leave->student->user->name ?? 'Siswa' }}</div>
                                <span class="bg-[#FFD43B] text-slate-950 border border-slate-900 rounded px-1.5 py-0.2 text-[9px] uppercase font-black">
                                    {{ $leave->type }}
                                </span>
                            </div>
                            <div class="text-[10px] text-slate-600 flex items-center gap-1.5">
                                <span>Kelas: <b>{{ $leave->student->schoolClass->name ?? '-' }}</b></span>
                                <span>•</span>
                                <span>{{ $leave->date_from->format('d M') }} s/d {{ $leave->date_to->format('d M') }}</span>
                            </div>
                            @if($leave->reason)
                                <div class="text-[10px] text-slate-700 italic bg-white p-1 border border-slate-300 rounded">
                                    "{{ Str::limit($leave->reason, 70) }}"
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="p-4 text-center text-slate-500 font-semibold text-xs">
                            ✓ Tidak ada pengajuan izin yang menunggu persetujuan.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Sub-Widget 2: Jurnal Mengajar Guru Tanggal Terpilih -->
            <div class="space-y-2 pt-2 border-t border-slate-200">
                <div class="flex items-center justify-between pb-1 border-b border-slate-100">
                    <span class="text-[11px] font-extrabold uppercase text-slate-900 flex items-center gap-1.5">
                        <i data-lucide="book-open" class="w-3.5 h-3.5 text-blue-600"></i>
                        <span>Jurnal Guru Hari Ini</span>
                    </span>
                    <a href="{{ route('admin.teaching-journals.index') }}" class="text-[10px] font-bold text-blue-600 hover:underline">
                        Semua Jurnal →
                    </a>
                </div>

                <div class="space-y-2 max-h-[190px] overflow-y-auto pr-1 custom-scroll">
                    @forelse($recentJournals as $jrn)
                        <div class="p-2 border-2 border-slate-900 bg-[#FFF9DB]/40 rounded-lg space-y-1 shadow-[1px_1px_0_#0F172A]">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs text-slate-900">{{ $jrn->subject->name ?? 'Mata Pelajaran' }}</span>
                                <span class="bg-blue-100 text-blue-800 border border-slate-900 rounded px-1.5 py-0.2 text-[9px] font-bold">
                                    Pertemuan ke-{{ $jrn->meeting_number }}
                                </span>
                            </div>
                            <div class="text-[10px] text-slate-600 flex items-center gap-1.5">
                                <span>Guru: <b>{{ $jrn->teacher->user->name ?? '-' }}</b></span>
                                <span>•</span>
                                <span>Kelas: <b>{{ $jrn->schoolClass->name ?? '-' }}</b></span>
                            </div>
                            @if($jrn->learning_objective)
                                <div class="text-[10px] text-slate-700 bg-white p-1 border border-slate-200 rounded truncate">
                                    {{ $jrn->learning_objective }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="p-4 text-center text-slate-500 font-semibold text-xs">
                            Belum ada jurnal mengajar guru yang tercatat pada tanggal ini.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- WIDGET 2: PROGRES PENGISIAN KELAS (PENYEIMBANG LAYOUT & KEPATUHAN) -->
    <div class="neo-card bg-white rounded-2xl p-4 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-900">
                    Progres Pengisian Kelas
                </h4>
                <p class="text-[11px] text-slate-500 font-medium">Rekap kepatuhan per tingkat (1–6)</p>
            </div>
            <span class="border-2 border-slate-900 rounded-md px-2 py-0.5 text-[11px] font-extrabold font-mono shadow-[1.5px_1.5px_0_#0F172A]
                {{ $completedClassesCount === $totalClassesCount && $totalClassesCount > 0 ? 'bg-emerald-100 text-emerald-900' : 'bg-rose-100 text-rose-900' }}">
                {{ $completedClassesCount }} / {{ $totalClassesCount }}
            </span>
        </div>

        <!-- Overall Progress Bar -->
        <div class="space-y-1.5">
            <div class="flex justify-between text-[11px] font-bold">
                <span class="text-slate-600">Total Terisi Hari Ini</span>
                <span class="font-mono text-slate-900">{{ $overallCompliancePercent }}%</span>
            </div>
            <div class="w-full h-3 bg-slate-100 border-2 border-slate-900 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 transition-all duration-500" style="width: {{ $overallCompliancePercent }}%"></div>
            </div>
        </div>

        <!-- Breakdown Grid per Grade Level -->
        <div class="grid grid-cols-3 gap-2 pt-1">
            @for($g = 1; $g <= 6; $g++)
                @php
                    $gCompleted = $gradeStats[$g]['completed'] ?? 0;
                    $gTotal = $gradeStats[$g]['total'] ?? 0;
                    $isGradeComplete = $gTotal > 0 && $gCompleted >= $gTotal;
                    $isGradePartial = $gCompleted > 0 && $gCompleted < $gTotal;
                @endphp
                <div class="bg-slate-50 border-2 border-slate-900 rounded-lg p-2 text-center shadow-[1.5px_1.5px_0_#0F172A]">
                    <span class="block text-[10px] font-extrabold text-slate-500">KELAS {{ $g }}</span>
                    <span class="text-xs font-black font-mono 
                        @if($isGradeComplete) text-emerald-600 
                        @elseif($isGradePartial) text-amber-600 
                        @else text-rose-600 @endif">
                        {{ $gCompleted }}/{{ $gTotal }}
                    </span>
                </div>
            @endfor
        </div>

        <!-- Action Button: Ingatkan Wali Kelas -->
        <button type="button" 
                onclick="notifyUnfilledTeachers()" 
                class="neo-btn w-full bg-[#FFD43B] hover:bg-[#ffe066] text-slate-900 rounded-xl py-2 text-xs font-extrabold flex items-center justify-center gap-2 cursor-pointer shadow-[2px_2px_0_#0F172A] active:translate-x-0.5 active:translate-y-0.5 transition-all">
            <i data-lucide="bell-ring" class="w-4 h-4"></i>
            <span>Ingatkan Wali Kelas Belum Mengisi</span>
        </button>
    </div>

</aside>
