@extends('layouts.guru')

@section('title', 'Kelas Binaan (Wali Kelas)')
@section('page-title', 'Manajemen Siswa & Kehadiran Kelas Binaan')

@section('content')
<div class="space-y-6">
    {{-- Header Title Card --}}
    @include('guru.homeroom.partials._header')

    @if($homeroomClasses->isEmpty())
        {{-- Empty State: Guru Bukan Wali Kelas --}}
        @include('guru.homeroom.partials._empty')
    @else
        {{-- Toolbar: Filter Kelas, Search & Tombol Rekap/Export --}}
        @include('guru.homeroom.partials._toolbar')

        {{-- 6 KPI Cards & Progress Rate Kehadiran --}}
        @include('guru.homeroom.partials._kpi')

        {{-- Tab Navigation (Centered & Tooltip-Assisted) --}}
        <div class="border-b-2 border-black flex items-center justify-center gap-1 sm:gap-2 pb-0.5 overflow-visible w-full">
            <!-- 1. Direktori Siswa & Kontak Ortu -->
            <button type="button" onclick="switchTab('direktori')" id="tabBtn-direktori"
                    title="👥 Direktori Siswa & Kontak Ortu ({{ $students->count() }})"
                    aria-label="Direktori Siswa & Kontak Ortu"
                    class="tab-btn relative group flex-1 sm:flex-initial px-2.5 sm:px-4 py-2.5 text-xs font-black uppercase border-t-2 border-l-2 border-r-2 border-black bg-white text-black -mb-[2px] transition-all flex items-center justify-center gap-1.5 sm:gap-2 min-h-[44px] cursor-pointer">
                <span class="text-sm sm:text-base shrink-0">👥</span>
                <span class="hidden md:inline whitespace-nowrap">Direktori Siswa</span>
                <span class="tab-badge px-1.5 sm:px-2 py-0.5 rounded-full text-[10px] font-mono font-black bg-black text-white shrink-0">
                    {{ $students->count() }}
                </span>

                <!-- Tooltip Neo-Brutalism -->
                <div class="absolute bottom-full mb-2.5 left-0 sm:left-1/2 sm:-translate-x-1/2 hidden group-hover:flex group-focus:flex flex-col items-center pointer-events-none z-40 whitespace-nowrap">
                    <span class="bg-black text-white text-[10px] sm:text-xs font-black uppercase px-2.5 py-1 border border-black shadow-[2px_2px_0px_0px_#20C997] rounded-xs">
                        👥 Direktori Siswa & Kontak Ortu ({{ $students->count() }})
                    </span>
                    <div class="w-2 h-2 bg-black rotate-45 -mt-1 ml-4 sm:ml-0"></div>
                </div>
            </button>

            <!-- 2. Pengajuan Izin / Sakit -->
            <button type="button" onclick="switchTab('izin')" id="tabBtn-izin"
                    title="📝 Pengajuan Izin / Sakit ({{ $leaveRequests->count() }})"
                    aria-label="Pengajuan Izin / Sakit"
                    class="tab-btn relative group flex-1 sm:flex-initial px-2.5 sm:px-4 py-2.5 text-xs font-bold uppercase border-t-2 border-l-2 border-r-2 border-transparent text-slate-600 hover:text-black hover:border-slate-300 transition-all flex items-center justify-center gap-1.5 sm:gap-2 min-h-[44px] cursor-pointer">
                <span class="text-sm sm:text-base shrink-0">📝</span>
                <span class="hidden md:inline whitespace-nowrap">Pengajuan Izin / Sakit</span>
                <span class="tab-badge px-1.5 sm:px-2 py-0.5 rounded-full text-[10px] font-mono font-black {{ $leaveRequests->where('status', 'pending')->count() > 0 ? 'bg-[#FF6B6B] text-white animate-pulse' : 'bg-slate-200 text-slate-700' }} shrink-0">
                    {{ $leaveRequests->count() }}
                </span>

                <!-- Tooltip Neo-Brutalism -->
                <div class="absolute bottom-full mb-2.5 left-1/2 -translate-x-1/2 hidden group-hover:flex group-focus:flex flex-col items-center pointer-events-none z-40 whitespace-nowrap">
                    <span class="bg-black text-white text-[10px] sm:text-xs font-black uppercase px-2.5 py-1 border border-black shadow-[2px_2px_0px_0px_#339AF0] rounded-xs">
                        📝 Pengajuan Izin / Sakit ({{ $leaveRequests->count() }})
                    </span>
                    <div class="w-2 h-2 bg-black rotate-45 -mt-1"></div>
                </div>
            </button>

            <!-- 3. Rekap Bulan Ini -->
            <button type="button" onclick="switchTab('rekap-bulan')" id="tabBtn-rekap-bulan"
                    title="📊 Rekap Bulan Ini ({{ \Carbon\Carbon::today()->translatedFormat('F Y') }})"
                    aria-label="Rekap Bulan Ini"
                    class="tab-btn relative group flex-1 sm:flex-initial px-2.5 sm:px-4 py-2.5 text-xs font-bold uppercase border-t-2 border-l-2 border-r-2 border-transparent text-slate-600 hover:text-black hover:border-slate-300 transition-all flex items-center justify-center gap-1.5 sm:gap-2 min-h-[44px] cursor-pointer">
                <span class="text-sm sm:text-base shrink-0">📊</span>
                <span class="hidden md:inline whitespace-nowrap">Rekap Bulan Ini</span>
                <span class="tab-badge px-1.5 sm:px-2 py-0.5 rounded-full text-[10px] font-mono font-black bg-slate-200 text-slate-700 shrink-0">
                    {{ \Carbon\Carbon::today()->translatedFormat('M') }}
                </span>

                <!-- Tooltip Neo-Brutalism -->
                <div class="absolute bottom-full mb-2.5 left-1/2 -translate-x-1/2 hidden group-hover:flex group-focus:flex flex-col items-center pointer-events-none z-40 whitespace-nowrap">
                    <span class="bg-black text-white text-[10px] sm:text-xs font-black uppercase px-2.5 py-1 border border-black shadow-[2px_2px_0px_0px_#FFD43B] rounded-xs">
                        📊 Rekap Bulan Ini ({{ \Carbon\Carbon::today()->translatedFormat('F Y') }})
                    </span>
                    <div class="w-2 h-2 bg-black rotate-45 -mt-1"></div>
                </div>
            </button>

            <!-- 4. Catatan Khusus Siswa -->
            <button type="button" onclick="switchTab('catatan')" id="tabBtn-catatan"
                    title="📌 Catatan Khusus Siswa ({{ $studentNotes->count() }})"
                    aria-label="Catatan Khusus Siswa"
                    class="tab-btn relative group flex-1 sm:flex-initial px-2.5 sm:px-4 py-2.5 text-xs font-bold uppercase border-t-2 border-l-2 border-r-2 border-transparent text-slate-600 hover:text-black hover:border-slate-300 transition-all flex items-center justify-center gap-1.5 sm:gap-2 min-h-[44px] cursor-pointer">
                <span class="text-sm sm:text-base shrink-0">📌</span>
                <span class="hidden md:inline whitespace-nowrap">Catatan Khusus Siswa</span>
                <span class="tab-badge px-1.5 sm:px-2 py-0.5 rounded-full text-[10px] font-mono font-black bg-slate-200 text-slate-700 shrink-0">
                    {{ $studentNotes->count() }}
                </span>

                <!-- Tooltip Neo-Brutalism -->
                <div class="absolute bottom-full mb-2.5 right-0 sm:left-1/2 sm:-translate-x-1/2 sm:right-auto hidden group-hover:flex group-focus:flex flex-col items-center pointer-events-none z-40 whitespace-nowrap">
                    <span class="bg-black text-white text-[10px] sm:text-xs font-black uppercase px-2.5 py-1 border border-black shadow-[2px_2px_0px_0px_#FF6B6B] rounded-xs">
                        📌 Catatan Khusus Siswa ({{ $studentNotes->count() }})
                    </span>
                    <div class="w-2 h-2 bg-black rotate-45 -mt-1 mr-4 sm:mr-0"></div>
                </div>
            </button>
        </div>

        {{-- TAB 1: Direktori Siswa & Kontak Ortu --}}
        @include('guru.homeroom.partials._tab-direktori')

        {{-- TAB 2: Pengajuan Izin & Sakit --}}
        @include('guru.homeroom.partials._tab-izin')

        {{-- TAB 3: Rekap Kehadiran Bulan Berjalan --}}
        @include('guru.homeroom.partials._tab-rekap-bulan')

        {{-- TAB 4: Catatan Khusus Siswa --}}
        @include('guru.homeroom.partials._tab-catatan')
    @endif
</div>

{{-- Modals (Detail Siswa, Tambah Catatan, Edit Siswa Bertab) --}}
@include('guru.homeroom.partials._modals')
@endsection

@push('scripts')
    @include('guru.homeroom.partials._scripts')
@endpush
