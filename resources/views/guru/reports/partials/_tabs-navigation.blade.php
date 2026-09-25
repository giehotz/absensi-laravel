<!-- Navigation Tabs -->
<div class="bg-white border-2 border-black p-3 rounded-lg shadow-[4px_4px_0px_0px_#000] flex flex-wrap items-center justify-between gap-3 print:hidden">
    <div class="flex items-center gap-2">
        <button id="tab-btn-summary" 
                onclick="switchTab('summary')" 
                class="px-4 py-2 text-xs font-bold border-2 border-black rounded cursor-pointer transition-all {{ $activeTab === 'summary' ? 'bg-black text-white shadow-[2px_2px_0px_0px_#000]' : 'bg-white text-black hover:bg-slate-200' }}">
            📊 Rekapitulasi per Siswa
        </button>
        <button id="tab-btn-logs" 
                onclick="switchTab('logs')" 
                class="px-4 py-2 text-xs font-bold border-2 border-black rounded cursor-pointer transition-all {{ $activeTab === 'logs' ? 'bg-black text-white shadow-[2px_2px_0px_0px_#000]' : 'bg-white text-black hover:bg-slate-200' }}">
            📋 Jurnal Riwayat Harian
        </button>
    </div>

    <!-- Search Form -->
    <form method="GET" action="{{ route('guru.reports.attendance') }}" class="flex items-center gap-2">
        <input type="hidden" name="start_date" value="{{ $startDate }}">
        <input type="hidden" name="end_date" value="{{ $endDate }}">
        <input type="hidden" name="school_class_id" value="{{ $schoolClassId }}">
        <input type="hidden" name="status" value="{{ $status }}">
        <input type="hidden" name="tab" id="search-tab-input" value="{{ $activeTab }}">

        <div class="relative">
            <input type="text" 
                   name="search" 
                   value="{{ $search }}" 
                   placeholder="Cari NIS / Nama Siswa..." 
                   class="neo-input pl-8 pr-3 py-1.5 text-xs bg-slate-50 focus:bg-white w-48 sm:w-60">
            <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
        @if($search)
            <a href="{{ route('guru.reports.attendance', array_merge(request()->except('search'), ['tab' => $activeTab])) }}" 
               class="neo-btn bg-rose-100 hover:bg-rose-200 text-rose-900 px-2.5 py-1.5 text-xs font-bold cursor-pointer"
               title="Hapus pencarian">
                ✕
            </a>
        @endif
    </form>
</div>
