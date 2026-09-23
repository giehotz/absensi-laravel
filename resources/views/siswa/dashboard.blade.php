@extends('layouts.neobrutalism')

@section('title', 'Portal Siswa - ' . ($student->user->name ?? 'Dashboard'))

@section('content')
<div class="max-w-2xl mx-auto pb-28 space-y-4">
    <!-- Header Profil Siswa -->
    @include('siswa._header-profile')

    <!-- Tab 1: Beranda -->
    @include('siswa._tab-beranda')

    <!-- Tab 2: QR Presensi (Kartu Pelajar & Token) -->
    @include('siswa._tab-qr')

    <!-- Tab 3: Jadwal Pelajaran -->
    @include('siswa._tab-jadwal')

    <!-- Tab 4: Pengajuan Izin / Sakit -->
    @include('siswa._tab-izin')

    <!-- Tab 5: Riwayat Presensi & Catatan Guru -->
    @include('siswa._tab-riwayat')

    <!-- Tab 6: Profil Siswa Lengkap -->
    @include('siswa._tab-profil')
</div>


<!-- Fixed Bottom Navigation Bar -->
@include('siswa._bottom-nav')

<!-- Modals (Fullscreen QR & Pengajuan Izin) -->
@include('siswa._modals')

<!-- JavaScript Interactivity -->
@include('siswa._scripts')
@endsection
