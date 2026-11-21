<!-- SECTION 5A: MONITORING PRESENSI PER KELAS (8-COL TABLE) -->
<div class="lg:col-span-8 neo-card bg-white rounded-2xl overflow-hidden">
    <!-- Table Card Header & Filter Toolbar -->
    <div class="p-4 sm:p-5 border-b-2 border-slate-900 bg-slate-50/70 space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-lg bg-[#FFD43B] border-2 border-slate-900 flex items-center justify-center shadow-[1.5px_1.5px_0_#0F172A]">
                    <i data-lucide="building-2" class="w-4 h-4 text-slate-900"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 leading-tight">
                        Monitoring Presensi Per Kelas
                    </h3>
                    <p class="text-xs text-slate-500 font-medium">
                        Status kepatuhan pengisian presensi siswa • {{ $carbonDate->translatedFormat('d F Y') }}
                    </p>
                </div>
            </div>
            <span class="bg-[#FFD43B] text-slate-900 border-2 border-slate-900 rounded-lg px-2.5 py-1 text-xs font-extrabold shadow-[2px_2px_0_#0F172A]">
                {{ $classes->count() }} KELAS
            </span>
        </div>

        <!-- Search & Filter Controls -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5 pt-1">
            <div class="relative flex-1 max-w-xs">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                <input
                    id="classSearch"
                    type="text"
                    placeholder="Cari kelas atau wali kelas..."
                    autocomplete="off"
                    class="w-full pl-9 pr-3 py-1.5 text-xs font-semibold bg-white border-2 border-slate-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-[1.5px_1.5px_0_#0F172A]"
                />
            </div>
            <div class="flex items-center gap-1.5 flex-wrap">
                <button type="button" data-filter="all" class="filter-btn neo-btn bg-slate-900 text-white rounded-lg px-2.5 py-1 text-[11px] font-bold cursor-pointer">
                    Semua ({{ $totalClassesCount }})
                </button>
                <button type="button" data-filter="belum" class="filter-btn neo-btn bg-rose-100 text-rose-900 rounded-lg px-2.5 py-1 text-[11px] font-bold cursor-pointer">
                    Belum Diisi ({{ $unfilledClassesCount }})
                </button>
                <button type="button" data-filter="sudah" class="filter-btn neo-btn bg-white text-slate-700 rounded-lg px-2.5 py-1 text-[11px] font-bold cursor-pointer">
                    Sudah Diisi ({{ $completedClassesCount }})
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Scrollable Table Container -->
    <div class="overflow-x-auto max-h-[640px] overflow-y-auto custom-scroll">
        <table class="w-full text-left border-collapse">
            <thead class="sticky top-0 z-10 bg-[#FEF9C3] border-b-2 border-slate-900 text-[11px] font-extrabold uppercase tracking-wider text-slate-900">
                <tr>
                    <th class="py-3 px-4 w-12 text-center border-r border-slate-900/15">No</th>
                    <th class="py-3 px-4 border-r border-slate-900/15">Kelas &amp; Wali</th>
                    <th class="py-3 px-4 text-center border-r border-slate-900/15">Status Pengisian</th>
                    <th class="py-3 px-4 border-r border-slate-900/15">Rincian Kehadiran</th>
                    <th class="py-3 px-4 text-center w-24">Aksi</th>
                </tr>
            </thead>
            <tbody id="classTableBody" class="divide-y divide-slate-200 text-xs">
                @forelse($classes as $idx => $cls)
                    @php
                        $studentCount = $cls->students->count();
                        $classAtts = $cls->students->flatMap->attendances;
                        $recordedCount = $classAtts->count();
                        
                        $hadirClass = $classAtts->where('status', 'hadir')->count();
                        $terlambatClass = $classAtts->where('status', 'terlambat')->count();
                        $izinClass = $classAtts->where('status', 'izin')->count();
                        $sakitClass = $classAtts->where('status', 'sakit')->count();
                        $alpaClass = $classAtts->where('status', 'alpa')->count();

                        $isComplete = $studentCount > 0 && $recordedCount >= $studentCount;
                        $isPartial = $recordedCount > 0 && $recordedCount < $studentCount;
                        $isUnfilled = $recordedCount === 0;

                        $teacherName = $cls->homeroomTeacher->user->name ?? 'Belum Ditentukan';
                    @endphp
                    <tr class="class-row hover:bg-blue-50/50 transition-colors {{ $isComplete ? 'bg-[#F4FBF7]/40' : '' }}"
                        data-name="{{ strtolower($cls->name) }}"
                        data-teacher="{{ strtolower($teacherName) }}"
                        data-status="{{ $isComplete ? 'sudah' : 'belum' }}">
                        
                        <!-- No -->
                        <td class="py-3 px-4 text-center font-mono font-bold text-slate-600 border-r border-slate-200">
                            {{ $idx + 1 }}
                        </td>

                        <!-- Identitas Kelas & Wali -->
                        <td class="py-3 px-4 border-r border-slate-200">
                            <div class="flex items-center gap-2">
                                <span class="font-extrabold text-slate-900">{{ $cls->name }}</span>
                                <span class="text-[10px] font-extrabold bg-slate-100 border border-slate-900 rounded px-1.5 py-0.2 text-slate-800">
                                    {{ $cls->level ?? 'MI' }}
                                </span>
                            </div>
                            <div class="text-[11px] text-slate-500 font-medium mt-0.5 flex items-center gap-1.5 flex-wrap">
                                <span class="{{ $teacherName === 'Belum Ditentukan' ? 'italic text-slate-400' : 'text-slate-700 font-semibold' }}">
                                    {{ $teacherName }}
                                </span>
                                <span>•</span>
                                <span class="font-mono font-bold text-slate-600">{{ $studentCount }} Siswa</span>
                            </div>
                        </td>

                        <!-- Status Kepatuhan Pengisian -->
                        <td class="py-3 px-4 text-center border-r border-slate-200">
                            @if($studentCount === 0)
                                <span class="inline-flex items-center gap-1 bg-slate-200 text-slate-700 border border-slate-900 rounded-md px-2 py-0.5 text-[10px] font-bold">
                                    Tanpa Siswa
                                </span>
                            @elseif($isComplete)
                                <span class="inline-flex items-center gap-1 bg-emerald-500 text-white border-2 border-slate-900 rounded-md px-2.5 py-0.5 text-[10px] font-extrabold tracking-wide shadow-[1.5px_1.5px_0_#0F172A]">
                                    <i data-lucide="check-circle" class="w-3 h-3"></i>
                                    LENGKAP ({{ $recordedCount }}/{{ $studentCount }})
                                </span>
                            @elseif($isPartial)
                                <span class="inline-flex items-center gap-1 bg-amber-400 text-slate-950 border-2 border-slate-900 rounded-md px-2.5 py-0.5 text-[10px] font-extrabold tracking-wide shadow-[1.5px_1.5px_0_#0F172A]">
                                    <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                    SEBAGIAN ({{ $recordedCount }}/{{ $studentCount }})
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 bg-rose-500 text-white border-2 border-slate-900 rounded-md px-2.5 py-0.5 text-[10px] font-extrabold tracking-wide shadow-[1.5px_1.5px_0_#0F172A]">
                                    <i data-lucide="x-circle" class="w-3 h-3"></i>
                                    BELUM DIISI
                                </span>
                            @endif
                        </td>

                        <!-- Rincian Kehadiran -->
                        <td class="py-3 px-4 border-r border-slate-200">
                            @if($recordedCount > 0)
                                <div class="inline-flex items-center gap-1.5 bg-white border border-slate-900 px-2 py-0.5 rounded shadow-[1px_1px_0_#0F172A] font-mono text-[11px]">
                                    <span class="text-emerald-700 font-bold" title="Hadir: {{ $hadirClass }}">H:{{ $hadirClass }}</span>
                                    <span class="text-slate-300">|</span>
                                    <span class="text-blue-700 font-bold" title="Terlambat: {{ $terlambatClass }}">T:{{ $terlambatClass }}</span>
                                    <span class="text-slate-300">|</span>
                                    <span class="text-slate-600 font-bold" title="Izin: {{ $izinClass }}">I:{{ $izinClass }}</span>
                                    <span class="text-slate-300">|</span>
                                    <span class="text-amber-700 font-bold" title="Sakit: {{ $sakitClass }}">S:{{ $sakitClass }}</span>
                                    <span class="text-slate-300">|</span>
                                    <span class="text-rose-700 font-bold" title="Alpa: {{ $alpaClass }}">A:{{ $alpaClass }}</span>
                                </div>
                            @else
                                <span class="text-[11px] italic text-slate-400">Belum ada data kehadiran</span>
                            @endif
                        </td>

                        <!-- Tombol Aksi -->
                        <td class="py-3 px-4 text-center">
                            <a href="{{ route('admin.attendances.manual', ['school_class_id' => $cls->id, 'date' => $date]) }}" 
                               class="neo-btn bg-white hover:bg-[#FFD43B] text-slate-900 rounded-lg px-3 py-1 text-[11px] font-extrabold inline-flex items-center gap-1 cursor-pointer transition-all active:translate-x-0.5 active:translate-y-0.5">
                                <i data-lucide="pencil" class="w-3 h-3"></i>
                                <span>{{ $isComplete ? 'Edit' : 'Isi' }}</span>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-slate-500 font-semibold text-xs">
                            Belum ada data rombel kelas yang terdaftar.
                        </td>
                    </tr>
                @endforelse
                
                <!-- Row ketika hasil filter/pencarian kosong -->
                <tr id="noClassRow" class="hidden">
                    <td colspan="5" class="py-10 text-center text-slate-500 font-semibold text-xs">
                        Tidak ada rombel kelas yang cocok dengan kata kunci atau filter yang dipilih.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
