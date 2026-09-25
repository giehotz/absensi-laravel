@extends('layouts.guru')

@section('title', 'Rekap & Laporan Kehadiran')
@section('page-title', 'Rekap Kehadiran Siswa')

@section('content')
<div class="space-y-6 pb-12">
    {{-- Kop Surat Resmi untuk Mode Cetak / Dokumen Fisik --}}
    <div class="hidden print:block mb-4">
        <x-kop-surat :useBase64="true" />
    </div>

    {{-- Header Section & Aksi Ekspor / Cetak --}}
    @include('guru.reports.partials._header')

    {{-- Filter Rentang Waktu, Kelas, Status & Quick Weekly Navigator --}}
    @include('guru.reports.partials._filter')

    {{-- 6 Kartu Metrik KPI Presensi --}}
    @include('guru.reports.partials._kpi')

    {{-- Grafik Neobrutalisme: Distribusi Status & Tren Mingguan --}}
    @include('guru.reports.partials._charts')

    {{-- Navigasi Tab & Pencarian Siswa --}}
    @include('guru.reports.partials._tabs-navigation')

    {{-- TAB 1: Rekapitulasi Presensi per Siswa --}}
    @include('guru.reports.partials._tab-summary')

    {{-- TAB 2: Jurnal Riwayat Presensi Harian --}}
    @include('guru.reports.partials._tab-logs')
</div>
@endsection

@push('scripts')
    @include('guru.reports.partials._scripts')
@endpush
