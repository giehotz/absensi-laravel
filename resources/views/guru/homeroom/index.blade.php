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

        {{-- Tab Navigation --}}
        <div class="border-b-2 border-black flex items-center gap-2 overflow-x-auto">
            <button type="button" onclick="switchTab('direktori')" id="tabBtn-direktori"
                    class="tab-btn px-4 py-2.5 text-xs font-black uppercase border-t-2 border-l-2 border-r-2 border-black bg-white text-black -mb-[2px] transition-all">
                👥 Direktori Siswa & Kontak Ortu ({{ $students->count() }})
            </button>
            <button type="button" onclick="switchTab('izin')" id="tabBtn-izin"
                    class="tab-btn px-4 py-2.5 text-xs font-bold uppercase border-t-2 border-l-2 border-r-2 border-transparent text-slate-600 hover:text-black hover:border-slate-300 transition-all">
                📝 Pengajuan Izin / Sakit ({{ $leaveRequests->count() }})
            </button>
            <button type="button" onclick="switchTab('rekap-bulan')" id="tabBtn-rekap-bulan"
                    class="tab-btn px-4 py-2.5 text-xs font-bold uppercase border-t-2 border-l-2 border-r-2 border-transparent text-slate-600 hover:text-black hover:border-slate-300 transition-all">
                📊 Rekap Bulan Ini ({{ \Carbon\Carbon::today()->translatedFormat('F Y') }})
            </button>
            <button type="button" onclick="switchTab('catatan')" id="tabBtn-catatan"
                    class="tab-btn px-4 py-2.5 text-xs font-bold uppercase border-t-2 border-l-2 border-r-2 border-transparent text-slate-600 hover:text-black hover:border-slate-300 transition-all">
                📌 Catatan Khusus Siswa ({{ $studentNotes->count() }})
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
