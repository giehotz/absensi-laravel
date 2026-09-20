@extends('layouts.admin')

@section('title', 'Presensi Manual Siswa')
@section('page-title', 'Pencatatan Presensi Manual')

@section('content')
<div class="space-y-6">
    <!-- Header Title Card -->
    <div class="bg-[#5294FF] neo-box-lg p-6 sm:p-8 text-white relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="space-y-1.5 z-10">
            <div class="flex items-center gap-2">
                <span class="neo-badge bg-[#FFD43B] text-black">ADMINISTRATOR</span>
                <span class="text-xs font-mono font-bold bg-black/20 px-2 py-0.5 border border-black text-white">
                    Seluruh Kelas
                </span>
            </div>
            <h1 class="font-heading text-2xl sm:text-3xl font-black tracking-tight text-white">
                INPUT PRESENSI MANUAL SISWA
            </h1>
            <p class="text-xs sm:text-sm font-semibold text-blue-100 max-w-xl">
                Catat presensi harian seluruh rombel kelas di sekolah secara manual, perbarui status kehadiran dan input catatan khusus siswa.
            </p>
        </div>

        <div class="z-10">
            <a href="{{ route('admin.dashboard') }}" class="neo-btn bg-white text-black text-xs font-bold px-4 py-2 flex items-center gap-1.5 cursor-pointer hover:bg-slate-100">
                ← Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- Partial Form Presensi Manual -->
    @include('partials._attendance-manual-form')
</div>
@endsection
