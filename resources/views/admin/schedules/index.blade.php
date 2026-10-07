@extends('layouts.admin')

@section('title', 'Jadwal Pelajaran Kelas')
@section('page-title', 'Plot Jadwal Kelas (EMIS GTK)')

@section('content')
<div class="space-y-6">
    <!-- Navigation Tabs -->
    @include('admin.schedules.partials._nav-tabs')

    <!-- Alert / Status Notifikasi -->
    @include('admin.schedules.partials._alerts')

    @if(!$selectedClass)
        {{-- ========================================================================= --}}
        {{-- TAMPILAN 1: DIREKTORI DAFTAR KELAS (UBIN & DAFTAR) ALA SIMPATIKA / EMIS --}}
        {{-- ========================================================================= --}}
        @include('admin.schedules.partials._directory')
    @else
        {{-- ========================================================================= --}}
        {{-- TAMPILAN 2: MATRIKS JADWAL MINGGUAN KELAS TERPILIH --}}
        {{-- ========================================================================= --}}
        @include('admin.schedules.partials._matrix-header')
        @include('admin.schedules.partials._matrix-toolbar')
        @include('admin.schedules.partials._matrix-table')
    @endif
</div>

@if($selectedClass)
    <!-- Modal Detail & Form Jadwal -->
    @include('admin.schedules.partials._modals')

    <!-- Scripts Matriks & Form Jadwal -->
    @include('admin.schedules.partials._scripts')
@endif
@endsection
