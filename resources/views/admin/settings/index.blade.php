@extends('layouts.admin')

@section('title', 'Pengaturan Sistem & Lembaga')
@section('page-title', 'Pengaturan Sistem & Informasi Sekolah')

@section('content')
<style>
    .settings-tab {
        transition: all .15s ease;
    }
    .settings-tab.tab-active {
        background-color: #000 !important;
        color: #fff !important;
        box-shadow: 4px 4px 0 0 #FFD43B;
        transform: translate(-1px, -1px);
    }
    .settings-tab.tab-active .tab-icon {
        transform: rotate(-6deg) scale(1.15);
    }
    .tab-icon {
        display: inline-block;
        transition: transform .15s ease;
    }
</style>

<div class="max-w-5xl mx-auto space-y-8">

    <!-- Tab Navigation -->
    <div class="bg-white neo-box-lg p-2 border-2 border-black grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2">
        <button type="button" data-tab="absensi" onclick="switchTab('absensi')"
                class="settings-tab neo-btn bg-white hover:bg-[#FFF9DB] text-black px-3 py-3 text-[11px] sm:text-xs font-heading font-black uppercase tracking-wider flex items-center justify-center gap-2 cursor-pointer border-2 border-black tab-active">
            <span class="tab-icon">⚙️</span> Konfigurasi Absensi
        </button>
        <button type="button" data-tab="profil" onclick="switchTab('profil')"
                class="settings-tab neo-btn bg-white hover:bg-[#FFF9DB] text-black px-3 py-3 text-[11px] sm:text-xs font-heading font-black uppercase tracking-wider flex items-center justify-center gap-2 cursor-pointer border-2 border-black">
            <span class="tab-icon">🏫</span> Profil Lembaga
        </button>
        <button type="button" data-tab="kop" onclick="switchTab('kop')"
                class="settings-tab neo-btn bg-white hover:bg-[#FFF9DB] text-black px-3 py-3 text-[11px] sm:text-xs font-heading font-black uppercase tracking-wider flex items-center justify-center gap-2 cursor-pointer border-2 border-black">
            <span class="tab-icon">📜</span> Kop Surat
        </button>
        <button type="button" data-tab="periode" onclick="switchTab('periode')"
                class="settings-tab neo-btn bg-white hover:bg-[#FFF9DB] text-black px-3 py-3 text-[11px] sm:text-xs font-heading font-black uppercase tracking-wider flex items-center justify-center gap-2 cursor-pointer border-2 border-black">
            <span class="tab-icon">📅</span> Periode Akademik
        </button>
        <button type="button" data-tab="database" onclick="switchTab('database')"
                class="settings-tab neo-btn bg-white hover:bg-[#FFF9DB] text-black px-3 py-3 text-[11px] sm:text-xs font-heading font-black uppercase tracking-wider flex items-center justify-center gap-2 cursor-pointer border-2 border-black">
            <span class="tab-icon">🗄️</span> Pemeliharaan Data
        </button>
    </div>

    <!-- ============================================================ -->
    <!-- TAB 1: ABSENSI + TAB 2: PROFIL + TAB 3: KOP SURAT (1 form)   -->
    <!-- ============================================================ -->
    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        @include('admin.settings.partials._tab-absensi')
        @include('admin.settings.partials._tab-profil')
        @include('admin.settings.partials._tab-kop-surat')
    </form>

    <!-- ============================================================ -->
    <!-- TAB 3: MANAJEMEN TAHUN AJARAN & SEMESTER                     -->
    <!-- ============================================================ -->
    @include('admin.settings.partials._tab-periode')

    <!-- ============================================================ -->
    <!-- TAB 4: PEMELIHARAAN & ARSIP DATABASE PRESENSI                -->
    <!-- ============================================================ -->
    @include('admin.settings.partials._tab-database')

</div>

{{-- Modals: Create, Edit, Archive, Restore --}}
@include('admin.settings.partials._modals')

@push('scripts')
@include('admin.settings.partials._scripts')
@endpush
@endsection