@extends('layouts.guru')

@section('title', 'Input Presensi Manual')
@section('page-title', 'Pencatatan Presensi Manual Siswa')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- Header Title Card -->
    <div class="bg-[#20C997] neo-box-lg p-4 sm:p-6 md:p-8 text-black relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="space-y-1.5 z-10">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="neo-badge bg-black text-white text-[10px] sm:text-xs">PORTAL GURU</span>
                <span class="text-[10px] sm:text-xs font-mono font-bold bg-white px-2 py-0.5 border border-black shadow-[1px_1px_0px_#000]">
                    NIP: {{ Auth::user()->teacher->nip ?? '-' }}
                </span>
            </div>
            <h1 class="font-heading text-xl sm:text-2xl md:text-3xl font-black tracking-tight text-black">
                INPUT PRESENSI MANUAL
            </h1>
            <p class="text-xs sm:text-sm font-semibold text-slate-900 max-w-xl leading-relaxed">
                Catat atau perbarui status kehadiran siswa pada kelas binaan dan mata pelajaran yang Anda ampu dengan dukungan aksi massal cepat.
            </p>
        </div>

        <div class="z-10 w-full sm:w-auto">
            <a href="{{ route('guru.dashboard') }}" class="neo-btn bg-white text-black text-xs font-bold px-4 py-2.5 flex items-center justify-center gap-1.5 cursor-pointer hover:bg-slate-100 shadow-[2px_2px_0px_#000] w-full sm:w-auto">
                ← Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- Partial Form Presensi Manual -->
    @include('partials._attendance-manual-form')
</div>
@endsection
