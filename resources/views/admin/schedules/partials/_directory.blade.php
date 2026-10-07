{{-- Direktori Daftar Rombel / Kelas (Ubin & Daftar) --}}
<!-- Header Title Card: Direktori Kelas -->
<div class="bg-[#FFF3BF] neo-box-lg p-6 sm:p-7 text-black relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
    <div class="space-y-1.5 z-10">
        <div class="flex items-center gap-2 flex-wrap">
            <span class="neo-badge bg-[#5294FF] text-white">ADMINISTRATOR</span>
            <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border border-black">
                {{ $academicYear->name ?? 'Tahun Ajaran Aktif' }}
            </span>
            <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border border-black hidden sm:inline-block">
                Semester {{ ucfirst($academicYear->semester ?? 'Ganjil') }}
            </span>
            <span class="text-xs font-heading font-black bg-[#FFD43B] text-black px-2.5 py-0.5 border border-black">
                {{ $classes->count() }} Rombel Aktif
            </span>
        </div>
        <h1 class="font-heading text-2xl sm:text-3xl font-black tracking-tight text-black uppercase">
            DIREKTORI JADWAL PELAJARAN KELAS
        </h1>
        <p class="text-xs sm:text-sm font-semibold text-slate-800 max-w-2xl">
            Pilih rombongan belajar (kelas) di bawah untuk melihat matriks mingguan dan menginput jadwal pelajaran tatap muka berbasis standar regulasi EMIS GTK.
        </p>
    </div>

    <div class="z-10 flex flex-wrap gap-2 shrink-0">
        <a href="{{ route('admin.schedules.slots.index') }}" class="neo-btn bg-white hover:bg-slate-100 text-black text-xs font-bold px-3.5 py-2.5 flex items-center gap-1.5 cursor-pointer shadow-[2px_2px_0px_0px_#000]">
            <span>⚙️</span> Atur Template Slot Jam
        </a>
    </div>
</div>

@if($classes->isEmpty())
    <div class="bg-white neo-box p-12 text-center text-slate-500 space-y-2 border-2 border-black">
        <div class="text-4xl">🏫</div>
        <div class="text-base font-black text-black">Belum Ada Data Rombel / Kelas</div>
        <p class="text-xs">Silakan tambahkan data kelas terlebih dahulu di menu Data Kelas.</p>
        <div class="pt-3">
            <a href="{{ route('admin.classes.index') }}" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black font-black text-xs px-4 py-2 inline-flex items-center gap-1.5 shadow-[2px_2px_0px_0px_#000]">
                <span>➕</span> Kelola Data Kelas
            </a>
        </div>
    </div>
@else
    <!-- 4 Summary Cards Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Total Rombel -->
        <div class="bg-[#FFF9DB] neo-box border-2 border-black p-4 flex items-center gap-3">
            <div class="w-11 h-11 bg-white border-2 border-black rounded flex items-center justify-center text-xl shrink-0 shadow-[2px_2px_0px_0px_#000]">
                🏫
            </div>
            <div class="min-w-0">
                <span class="text-[10px] font-bold text-slate-600 uppercase block">Total Rombel</span>
                <span class="font-heading font-black text-lg text-black block leading-none mt-0.5">{{ $classes->count() }} Kelas</span>
                <span class="text-[10px] font-mono text-slate-500 mt-1 block">Tahun {{ $academicYear->name ?? '-' }}</span>
            </div>
        </div>

        <!-- Total Sesi Terjadwal -->
        <div class="bg-[#E7F5FF] neo-box border-2 border-black p-4 flex items-center gap-3">
            <div class="w-11 h-11 bg-white border-2 border-black rounded flex items-center justify-center text-xl shrink-0 shadow-[2px_2px_0px_0px_#000]">
                📅
            </div>
            <div class="min-w-0">
                <span class="text-[10px] font-bold text-slate-600 uppercase block">Sesi Terjadwal</span>
                <span class="font-heading font-black text-lg text-black block leading-none mt-0.5">{{ $classes->sum('total_sessions') }} Sesi</span>
                <span class="text-[10px] font-mono text-slate-500 mt-1 block">Pertemuan Mingguan</span>
            </div>
        </div>

        <!-- Total Beban JP -->
        <div class="bg-[#F3D9FA] neo-box border-2 border-black p-4 flex items-center gap-3">
            <div class="w-11 h-11 bg-white border-2 border-black rounded flex items-center justify-center text-xl shrink-0 shadow-[2px_2px_0px_0px_#000]">
                ⏱️
            </div>
            <div class="min-w-0">
                <span class="text-[10px] font-bold text-slate-600 uppercase block">Total Beban JP</span>
                <span class="font-heading font-black text-lg text-black block leading-none mt-0.5">{{ $classes->sum('total_jp') }} JP</span>
                <span class="text-[10px] font-mono text-slate-500 mt-1 block">Rata-rata {{ $classes->count() > 0 ? round($classes->sum('total_jp') / $classes->count(), 1) : 0 }} JP/Kls</span>
            </div>
        </div>

        <!-- Status Beban KBM -->
        @php
            $completeClassesCount = $classes->where('shortage_jp', 0)->count();
        @endphp
        <div class="bg-[#D3F9D8] neo-box border-2 border-black p-4 flex items-center gap-3">
            <div class="w-11 h-11 bg-white border-2 border-black rounded flex items-center justify-center text-xl shrink-0 shadow-[2px_2px_0px_0px_#000]">
                ✅
            </div>
            <div class="min-w-0">
                <span class="text-[10px] font-bold text-slate-600 uppercase block">Status Keterisian</span>
                <span class="font-heading font-black text-lg text-black block leading-none mt-0.5">{{ $completeClassesCount }} / {{ $classes->count() }}</span>
                <span class="text-[10px] font-mono text-slate-600 mt-1 block">Rombel 100% Lengkap</span>
            </div>
        </div>
    </div>

    <!-- Toolbar Pencarian & Filter & Switcher Toggle -->
    <div class="bg-white neo-box p-4 border-2 border-black flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        <!-- Search Input -->
        <div class="relative flex-1">
            <input type="text" 
                   id="classSearchInput" 
                   placeholder="Cari nama kelas atau wali kelas..." 
                   class="w-full pl-9 pr-3 py-2 text-xs font-bold border-2 border-black rounded shadow-[2px_2px_0px_0px_#000] bg-white focus:outline-none focus:ring-2 focus:ring-[#FFD43B]">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Filter Tingkat -->
            <div class="flex items-center gap-1.5">
                <span class="text-xs font-bold text-slate-700 whitespace-nowrap">Jenjang:</span>
                <select id="classLevelFilter" onchange="filterClasses()" 
                        class="text-xs font-bold py-2 px-3 border-2 border-black rounded shadow-[2px_2px_0px_0px_#000] bg-white cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#FFD43B]">
                    <option value="">Semua Tingkat</option>
                    @foreach($classes->pluck('level')->unique()->sort() as $lvl)
                        <option value="{{ $lvl }}">Tingkat {{ $lvl }}</option>
                    @endforeach
                </select>
            </div>

            <!-- View Switcher (Ubin / Daftar) -->
            <div class="flex items-center gap-1 bg-slate-100 border-2 border-black p-1 rounded shadow-[2px_2px_0px_0px_#000]">
                <button type="button" 
                        id="btnViewGrid" 
                        onclick="setViewMode('grid')" 
                        class="px-3 py-1.5 text-xs font-heading font-black cursor-pointer flex items-center gap-1.5 rounded transition-all bg-[#FFD43B] text-black shadow-[1.5px_1.5px_0px_#000]">
                    <span>▦</span> Ubin
                </button>
                <button type="button" 
                        id="btnViewTable" 
                        onclick="setViewMode('table')" 
                        class="px-3 py-1.5 text-xs font-heading font-bold cursor-pointer flex items-center gap-1.5 rounded transition-all bg-white text-slate-600 hover:text-black">
                    <span>☰</span> Daftar
                </button>
            </div>
        </div>
    </div>

    <!-- CONTAINER MODE UBIN (GRID / CARDS) -->
    <div id="viewGridContainer" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        @foreach($classes as $cls)
            <div class="class-card-item bg-white neo-box border-2 border-black p-5 flex flex-col justify-between hover:shadow-[5px_5px_0px_0px_#000] transition-all group"
                 data-class-name="{{ strtolower($cls->name) }}"
                 data-teacher-name="{{ strtolower($cls->homeroomTeacher->user->name ?? '') }}"
                 data-level="{{ $cls->level }}">
                
                <div>
                    <!-- Header Card: Badges -->
                    <div class="flex items-center justify-between gap-2">
                        <span class="neo-badge bg-[#E7F5FF] text-blue-950 font-black text-[11px]">
                            Tingkat {{ $cls->level }}
                        </span>
                        <span class="text-[11px] font-mono font-bold bg-slate-100 border border-black px-2 py-0.5 rounded">
                            👥 {{ $cls->students->count() }} Siswa
                        </span>
                    </div>

                    <!-- Nama Kelas -->
                    <div class="mt-2.5">
                        <h3 class="font-heading font-black text-xl text-black">
                            Kelas {{ $cls->name }}
                        </h3>
                        <p class="text-[11px] font-mono text-slate-500 mt-0.5">
                            {{ $cls->academicYear->name ?? 'Tahun Aktif' }} · Semester {{ ucfirst($cls->academicYear->semester ?? 'Ganjil') }}
                        </p>
                    </div>

                    <!-- Info Wali Kelas dengan Foto -->
                    <div class="bg-[#FFF9DB] border-2 border-black p-3 rounded mt-3.5 flex items-center gap-3">
                        <div class="w-12 h-14 rounded-xs border-2 border-black overflow-hidden shrink-0 bg-white flex items-center justify-center shadow-[1.5px_1.5px_0px_#000]">
                            @if($cls->homeroomTeacher && $cls->homeroomTeacher->photo)
                                <img src="{{ $cls->homeroomTeacher->photo_url }}" alt="{{ $cls->homeroomTeacher->user->name ?? 'Wali Kelas' }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-[#5294FF] text-white font-heading font-black text-sm flex items-center justify-center">
                                    {{ strtoupper(substr($cls->homeroomTeacher->user->name ?? 'W', 0, 2)) }}
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-[10px] font-black uppercase text-slate-600 block">Wali Kelas:</span>
                            <div class="font-heading font-black text-xs text-black truncate" title="{{ $cls->homeroomTeacher->user->name ?? 'Belum Ditugaskan' }}">
                                {{ $cls->homeroomTeacher->user->name ?? 'Belum Ditugaskan' }}
                            </div>
                            <div class="text-[10px] font-mono text-slate-600 truncate mt-0.5">
                                @if($cls->homeroomTeacher?->nip)
                                    NIP: {{ $cls->homeroomTeacher->nip }}
                                @elseif($cls->homeroomTeacher?->nuptk)
                                    NUPTK: {{ $cls->homeroomTeacher->nuptk }}
                                @else
                                    NIP: -
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Beban Jam Pelajaran (JP) & Sesi -->
                    <div class="space-y-2 mt-4 pt-3 border-t border-slate-200">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-700">Alokasi Jam Belajar:</span>
                            <span class="font-mono font-black text-black">
                                <b class="text-sm">{{ $cls->total_jp }}</b> / {{ $cls->target_jp }} JP
                            </span>
                        </div>
                        <div class="w-full bg-slate-100 border-2 border-black h-3.5 rounded overflow-hidden p-0.5">
                            <div class="h-full {{ $cls->shortage_jp === 0 ? 'bg-[#20C997]' : 'bg-[#5294FF]' }} transition-all duration-300 rounded-xs" 
                                 style="width: {{ $cls->percent_jp }}%;"></div>
                        </div>
                        <div class="flex items-center justify-between text-[11px] font-mono">
                            <span class="text-slate-600 font-bold">Terjadwal: <b>{{ $cls->total_sessions }} Sesi</b></span>
                            @if($cls->shortage_jp === 0)
                                <span class="neo-badge bg-[#20C997] text-black font-black text-[10px] px-2 py-0.5">
                                    ✅ 100% Lengkap
                                </span>
                            @else
                                <span class="neo-badge bg-[#FFF3BF] text-amber-950 font-black text-[10px] px-2 py-0.5">
                                    ⚠️ Kurang {{ $cls->shortage_jp }} JP
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Tombol Input Jadwal -->
                <div class="mt-4 pt-3 border-t-2 border-black">
                    <a href="{{ route('admin.schedules.index', ['school_class_id' => $cls->id]) }}" 
                       class="w-full neo-btn bg-[#20C997] hover:bg-[#12b886] text-black text-xs font-black py-2.5 px-4 flex items-center justify-center gap-2 cursor-pointer shadow-[2px_2px_0px_0px_#000] hover:translate-x-[1px] hover:translate-y-[1px]">
                        <span>📅</span> Input Jadwal
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <!-- CONTAINER MODE DAFTAR (TABLE / LIST) -->
    <div id="viewTableContainer" class="bg-white neo-box overflow-hidden border-2 border-black hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-[#FFF9DB] border-b-2 border-black text-xs font-black uppercase tracking-wider text-black">
                    <tr>
                        <th class="p-3.5 border-r border-black text-center w-12">No</th>
                        <th class="p-3.5 border-r border-black min-w-[140px]">Kelas</th>
                        <th class="p-3.5 border-r border-black text-center w-24">Tingkat</th>
                        <th class="p-3.5 border-r border-black min-w-[240px]">Wali Kelas</th>
                        <th class="p-3.5 border-r border-black text-center w-28">Jumlah Siswa</th>
                        <th class="p-3.5 border-r border-black min-w-[200px]">Alokasi Jam (JP)</th>
                        <th class="p-3.5 border-r border-black text-center min-w-[150px]">Status Beban</th>
                        <th class="p-3.5 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black text-xs">
                    @foreach($classes as $index => $cls)
                        <tr class="class-row-item hover:bg-slate-50 font-medium transition-colors"
                            data-class-name="{{ strtolower($cls->name) }}"
                            data-teacher-name="{{ strtolower($cls->homeroomTeacher->user->name ?? '') }}"
                            data-level="{{ $cls->level }}">
                            <td class="p-3.5 font-bold text-center border-r border-black text-slate-700">
                                {{ $index + 1 }}
                            </td>
                            <td class="p-3.5 border-r border-black">
                                <div class="font-heading font-black text-black text-sm">Kelas {{ $cls->name }}</div>
                                <div class="text-[10px] font-mono text-slate-500">{{ $cls->academicYear->name ?? 'Tahun Aktif' }}</div>
                            </td>
                            <td class="p-3.5 border-r border-black text-center">
                                <span class="neo-badge bg-[#E7F5FF] text-blue-950 text-[10px] font-black">
                                    Tingkat {{ $cls->level }}
                                </span>
                            </td>
                            <td class="p-3.5 border-r border-black">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-11 rounded-xs border border-black overflow-hidden shrink-0 bg-white flex items-center justify-center shadow-[1px_1px_0px_#000]">
                                        @if($cls->homeroomTeacher && $cls->homeroomTeacher->photo)
                                            <img src="{{ $cls->homeroomTeacher->photo_url }}" alt="{{ $cls->homeroomTeacher->user->name ?? 'Wali Kelas' }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full bg-[#5294FF] text-white font-heading font-black text-xs flex items-center justify-center">
                                                {{ strtoupper(substr($cls->homeroomTeacher->user->name ?? 'W', 0, 2)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-heading font-black text-black text-xs truncate">
                                            {{ $cls->homeroomTeacher->user->name ?? 'Belum Ditugaskan' }}
                                        </div>
                                        <div class="text-[10px] font-mono text-slate-500">
                                            @if($cls->homeroomTeacher?->nip)
                                                NIP: {{ $cls->homeroomTeacher->nip }}
                                            @elseif($cls->homeroomTeacher?->nuptk)
                                                NUPTK: {{ $cls->homeroomTeacher->nuptk }}
                                            @else
                                                NIP: -
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3.5 border-r border-black text-center font-bold">
                                <span class="bg-[#D3F9D8] px-2 py-0.5 border border-black text-xs font-mono">
                                    {{ $cls->students->count() }} Siswa
                                </span>
                            </td>
                            <td class="p-3.5 border-r border-black">
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between text-[11px] font-mono">
                                        <span class="font-bold text-slate-700"><b>{{ $cls->total_jp }}</b> / {{ $cls->target_jp }} JP</span>
                                        <span class="text-slate-500">{{ $cls->total_sessions }} Sesi</span>
                                    </div>
                                    <div class="w-full bg-slate-100 border border-black h-2.5 rounded overflow-hidden">
                                        <div class="h-full {{ $cls->shortage_jp === 0 ? 'bg-[#20C997]' : 'bg-[#5294FF]' }}" style="width: {{ $cls->percent_jp }}%;"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3.5 border-r border-black text-center">
                                @if($cls->shortage_jp === 0)
                                    <span class="neo-badge bg-[#20C997] text-black font-black text-[10px] px-2 py-0.5">
                                        ✅ Lengkap (100%)
                                    </span>
                                @else
                                    <span class="neo-badge bg-[#FFF3BF] text-amber-950 font-black text-[10px] px-2 py-0.5">
                                        ⚠️ Kurang {{ $cls->shortage_jp }} JP
                                    </span>
                                @endif
                            </td>
                            <td class="p-3.5 text-center">
                                <a href="{{ route('admin.schedules.index', ['school_class_id' => $cls->id]) }}" 
                                   class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black text-xs font-black py-1.5 px-3 inline-flex items-center justify-center gap-1.5 cursor-pointer shadow-[2px_2px_0px_0px_#000]">
                                    <span>📅</span> Input Jadwal
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Empty Search State -->
    <div id="classEmptyState" class="bg-white neo-box p-12 text-center text-slate-500 space-y-2 hidden border-2 border-black">
        <div class="text-4xl">🔍</div>
        <div class="text-base font-black text-black">Tidak Ada Kelas yang Cocok</div>
        <p class="text-xs">Coba ubah kata kunci pencarian atau reset filter tingkat jenjang.</p>
    </div>

    <script>
        const CLASS_VIEW_STORAGE_KEY = 'admin_schedules_view_mode';

        function setViewMode(mode) {
            const gridBtn = document.getElementById('btnViewGrid');
            const tableBtn = document.getElementById('btnViewTable');
            const gridContainer = document.getElementById('viewGridContainer');
            const tableContainer = document.getElementById('viewTableContainer');

            if (!gridContainer || !tableContainer) return;

            if (mode === 'table') {
                gridContainer.classList.add('hidden');
                tableContainer.classList.remove('hidden');

                if (tableBtn && gridBtn) {
                    tableBtn.classList.remove('bg-white', 'text-slate-600');
                    tableBtn.classList.add('bg-[#FFD43B]', 'text-black', 'shadow-[1.5px_1.5px_0px_#000]');

                    gridBtn.classList.remove('bg-[#FFD43B]', 'text-black', 'shadow-[1.5px_1.5px_0px_#000]');
                    gridBtn.classList.add('bg-white', 'text-slate-600');
                }
            } else {
                tableContainer.classList.add('hidden');
                gridContainer.classList.remove('hidden');

                if (tableBtn && gridBtn) {
                    gridBtn.classList.remove('bg-white', 'text-slate-600');
                    gridBtn.classList.add('bg-[#FFD43B]', 'text-black', 'shadow-[1.5px_1.5px_0px_#000]');

                    tableBtn.classList.remove('bg-[#FFD43B]', 'text-black', 'shadow-[1.5px_1.5px_0px_#000]');
                    tableBtn.classList.add('bg-white', 'text-slate-600');
                }
            }

            try {
                localStorage.setItem(CLASS_VIEW_STORAGE_KEY, mode);
            } catch (e) {}
        }

        function filterClasses() {
            const searchVal = (document.getElementById('classSearchInput')?.value || '').toLowerCase().trim();
            const levelVal = document.getElementById('classLevelFilter')?.value || '';

            const cards = document.querySelectorAll('.class-card-item');
            const rows = document.querySelectorAll('.class-row-item');
            let visibleCount = 0;

            cards.forEach(card => {
                const cName = card.getAttribute('data-class-name') || '';
                const tName = card.getAttribute('data-teacher-name') || '';
                const cLevel = card.getAttribute('data-level') || '';

                const matchSearch = !searchVal || cName.includes(searchVal) || tName.includes(searchVal);
                const matchLevel = !levelVal || cLevel === levelVal;

                if (matchSearch && matchLevel) {
                    card.classList.remove('hidden');
                    visibleCount++;
                } else {
                    card.classList.add('hidden');
                }
            });

            rows.forEach(row => {
                const cName = row.getAttribute('data-class-name') || '';
                const tName = row.getAttribute('data-teacher-name') || '';
                const cLevel = row.getAttribute('data-level') || '';

                const matchSearch = !searchVal || cName.includes(searchVal) || tName.includes(searchVal);
                const matchLevel = !levelVal || cLevel === levelVal;

                if (matchSearch && matchLevel) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });

            const emptyState = document.getElementById('classEmptyState');
            if (emptyState) {
                if (visibleCount === 0) {
                    emptyState.classList.remove('hidden');
                } else {
                    emptyState.classList.add('hidden');
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            let savedMode = 'grid';
            try {
                savedMode = localStorage.getItem(CLASS_VIEW_STORAGE_KEY) || 'grid';
            } catch(e) {}
            setViewMode(savedMode);

            const searchInput = document.getElementById('classSearchInput');
            if (searchInput) {
                searchInput.addEventListener('input', filterClasses);
            }
        });
    </script>
@endif
