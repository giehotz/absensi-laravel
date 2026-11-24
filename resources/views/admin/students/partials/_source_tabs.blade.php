<!-- Tab Pilihan Sumber Data Siswa (Semua, Sinkron API, Input Manual Audit, Import Excel) -->
@php
    $baseParams = array_filter([
        'academic_year' => !empty($selectedAcademicYear) && $selectedAcademicYear != ($activeYearName ?? '') ? $selectedAcademicYear : null,
        'class_id' => $selectedClassId,
        'per_page' => !empty($perPage) && $perPage != '25' ? $perPage : null,
        'search' => !empty($search) ? $search : null,
    ]);
@endphp

<div class="space-y-3">
    <!-- Tab Bar Sumber Data -->
    <div class="flex flex-wrap items-center gap-2 border-b-2 border-black bg-white p-2 neo-box">
        <!-- 1. Semua Siswa -->
        <a href="{{ route('admin.students.index', array_merge($baseParams, ['tab' => 'semua'])) }}"
           class="px-4 py-2 text-xs font-heading font-black uppercase tracking-wider flex items-center gap-2 border-2 border-black transition-all cursor-pointer
           {{ $currentTab === 'semua' ? 'bg-[#FFD43B] text-black shadow-[3px_3px_0px_#000]' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' }}">
            <span>👥</span>
            <span>Semua Siswa</span>
            <span class="text-[10px] px-2 py-0.5 rounded-full border border-black {{ $currentTab === 'semua' ? 'bg-black text-white' : 'bg-white text-black' }}">
                {{ $tabCounts->count_semua ?? 0 }}
            </span>
        </a>

        <!-- 2. Sinkron API -->
        <a href="{{ route('admin.students.index', array_merge($baseParams, ['tab' => 'api'])) }}"
           class="px-4 py-2 text-xs font-heading font-black uppercase tracking-wider flex items-center gap-2 border-2 border-black transition-all cursor-pointer
           {{ $currentTab === 'api' ? 'bg-[#A5D8FF] text-black shadow-[3px_3px_0px_#000]' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' }}">
            <span>☁️</span>
            <span>Sinkron API EMIS</span>
            <span class="text-[10px] px-2 py-0.5 rounded-full border border-black {{ $currentTab === 'api' ? 'bg-black text-white' : 'bg-white text-black' }}">
                {{ $tabCounts->count_api ?? 0 }}
            </span>
        </a>

        <!-- 3. Input Manual (Audit) -->
        <a href="{{ route('admin.students.index', array_merge($baseParams, ['tab' => 'manual', 'audit_filter' => 'all'])) }}"
           class="px-4 py-2 text-xs font-heading font-black uppercase tracking-wider flex items-center gap-2 border-2 border-black transition-all cursor-pointer relative
           {{ $currentTab === 'manual' ? 'bg-[#FCC2D7] text-black shadow-[3px_3px_0px_#000]' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' }}">
            <span>✍️</span>
            <span>Input Manual (Audit)</span>
            <span class="text-[10px] px-2 py-0.5 rounded-full border border-black {{ $currentTab === 'manual' ? 'bg-black text-white' : 'bg-white text-black' }}">
                {{ $tabCounts->count_manual ?? 0 }}
            </span>
            @if(($tabCounts->count_manual_unsynced ?? 0) > 0)
                <span class="text-[9px] px-1.5 py-0.5 rounded bg-[#FF6B6B] text-white font-black border border-black shadow-[1px_1px_0px_#000] animate-pulse">
                    ⚠️ {{ $tabCounts->count_manual_unsynced }} Belum Sinkron API
                </span>
            @endif
        </a>

        <!-- 4. Import Excel -->
        <a href="{{ route('admin.students.index', array_merge($baseParams, ['tab' => 'import_excel'])) }}"
           class="px-4 py-2 text-xs font-heading font-black uppercase tracking-wider flex items-center gap-2 border-2 border-black transition-all cursor-pointer
           {{ $currentTab === 'import_excel' ? 'bg-[#D3F9D8] text-black shadow-[3px_3px_0px_#000]' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' }}">
            <span>📊</span>
            <span>Import Excel</span>
            <span class="text-[10px] px-2 py-0.5 rounded-full border border-black {{ $currentTab === 'import_excel' ? 'bg-black text-white' : 'bg-white text-black' }}">
                {{ $tabCounts->count_import ?? 0 }}
            </span>
        </a>
    </div>

    <!-- Sub-Filter Bar Khusus Tab Input Manual (Audit) -->
    @if($currentTab === 'manual')
        <div class="bg-[#FFF9DB] border-2 border-black p-3.5 neo-box flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-xs font-black uppercase tracking-wider font-heading text-black flex items-center gap-1.5">
                    <span>🔍</span> Sub-Filter Audit:
                </span>
                <span class="text-[11px] text-slate-600 font-medium">Saring siswa manual untuk diverifikasi kesesuaiannya dengan EMIS / VervalPD.</span>
            </div>

            <div class="flex flex-wrap items-center gap-1.5">
                <!-- Semua Manual -->
                <a href="{{ route('admin.students.index', array_merge($baseParams, ['tab' => 'manual', 'audit_filter' => 'all'])) }}"
                   class="px-3 py-1.5 text-xs font-bold border-2 border-black rounded transition-all cursor-pointer
                   {{ $auditFilter === 'all' ? 'bg-black text-white shadow-[2px_2px_0px_#FFD43B]' : 'bg-white text-black hover:bg-slate-100' }}">
                    Semua Manual ({{ $tabCounts->count_manual ?? 0 }})
                </a>

                <!-- Belum Cocok API -->
                <a href="{{ route('admin.students.index', array_merge($baseParams, ['tab' => 'manual', 'audit_filter' => 'unsynced'])) }}"
                   class="px-3 py-1.5 text-xs font-bold border-2 border-black rounded transition-all cursor-pointer flex items-center gap-1
                   {{ $auditFilter === 'unsynced' ? 'bg-[#FF922B] text-black shadow-[2px_2px_0px_#000] font-black' : 'bg-white text-black hover:bg-orange-50' }}">
                    <span>🟡</span>
                    <span>Belum Cocok API ({{ $tabCounts->count_manual_unsynced ?? 0 }})</span>
                </a>

                <!-- Sudah Cocok API -->
                <a href="{{ route('admin.students.index', array_merge($baseParams, ['tab' => 'manual', 'audit_filter' => 'synced'])) }}"
                   class="px-3 py-1.5 text-xs font-bold border-2 border-black rounded transition-all cursor-pointer flex items-center gap-1
                   {{ $auditFilter === 'synced' ? 'bg-[#51CF66] text-black shadow-[2px_2px_0px_#000] font-black' : 'bg-white text-black hover:bg-emerald-50' }}">
                    <span>🟢</span>
                    <span>Sudah Cocok API ({{ $tabCounts->count_manual_synced ?? 0 }})</span>
                </a>

                <!-- Bermasalah / Flagged -->
                <a href="{{ route('admin.students.index', array_merge($baseParams, ['tab' => 'manual', 'audit_filter' => 'flagged'])) }}"
                   class="px-3 py-1.5 text-xs font-bold border-2 border-black rounded transition-all cursor-pointer flex items-center gap-1
                   {{ $auditFilter === 'flagged' ? 'bg-[#FF6B6B] text-white shadow-[2px_2px_0px_#000] font-black' : 'bg-white text-black hover:bg-rose-50' }}">
                    <span>🔴</span>
                    <span>Flagged / Konflik ({{ $tabCounts->count_manual_flagged ?? 0 }})</span>
                </a>

                <!-- Terverifikasi -->
                <a href="{{ route('admin.students.index', array_merge($baseParams, ['tab' => 'manual', 'audit_filter' => 'verified'])) }}"
                   class="px-3 py-1.5 text-xs font-bold border-2 border-black rounded transition-all cursor-pointer flex items-center gap-1
                   {{ $auditFilter === 'verified' ? 'bg-[#339AF0] text-white shadow-[2px_2px_0px_#000] font-black' : 'bg-white text-black hover:bg-blue-50' }}">
                    <span>✅</span>
                    <span>Terverifikasi ({{ $tabCounts->count_manual_verified ?? 0 }})</span>
                </a>
            </div>
        </div>
    @endif
</div>
