@extends('layouts.guru')

@section('title', 'Jadwal Mengajar - Sedang Dalam Pengembangan')
@section('page-title', 'Jadwal Mengajar KBM')

@section('content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-[#FFD43B] neo-box-lg p-6 sm:p-8 text-black relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="space-y-1.5 z-10">
            <div class="flex items-center gap-2">
                <span class="neo-badge bg-black text-white">PORTAL GURU</span>
                <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border border-black shadow-[2px_2px_0px_0px_#000]">
                    FITUR BARU
                </span>
            </div>
            <h1 class="font-heading text-2xl sm:text-3xl font-black tracking-tight text-black">
                JADWAL MENGAJAR KBM
            </h1>
            <p class="text-xs sm:text-sm font-semibold text-slate-900 max-w-xl">
                Modul manajemen jadwal pelajaran terpadu dan slot template KBM mingguan sedang dalam proses perancangan & pengembangan.
            </p>
        </div>

        <div class="z-10 flex flex-wrap gap-2">
            <a href="{{ route('guru.dashboard') }}#jadwal" class="neo-btn bg-white text-black text-xs font-bold px-4 py-2 flex items-center gap-1.5 cursor-pointer hover:bg-slate-100">
                <span>📋</span> Lihat Ringkasan di Dashboard
            </a>
            <a href="{{ route('guru.dashboard') }}" class="neo-btn bg-black text-white text-xs font-bold px-4 py-2 flex items-center gap-1.5 cursor-pointer hover:bg-slate-800">
                ← Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- Under Development Showcase Card -->
    <div class="neo-box bg-white p-8 sm:p-12 text-center relative overflow-hidden">
        <!-- Background Pattern Decor -->
        <div class="absolute -right-8 -bottom-8 opacity-5 text-9xl select-none pointer-events-none font-black font-mono">
            KBM
        </div>

        <div class="max-w-xl mx-auto space-y-6">
            <!-- Animated / Stylish Icon Badge -->
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-2xl bg-[#FFF3BF] border-4 border-black shadow-[6px_6px_0px_0px_#000] text-5xl">
                🚧
            </div>

            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-[#FF6B6B] text-white border-2 border-black font-black text-xs uppercase tracking-wider shadow-[2px_2px_0px_0px_#000]">
                    <span class="w-2 h-2 rounded-full bg-white animate-ping"></span>
                    Under Construction
                </div>
                <h2 class="font-heading font-black text-2xl sm:text-3xl text-black">
                    Halaman Sedang Dalam Pengembangan
                </h2>
                <p class="text-sm font-medium text-slate-600 leading-relaxed">
                    Fitur matriks jadwal mingguan lengkap, pengaturan slot KBM mandiri, dan monitoring jam mengajar sedang dikembangkan untuk memberikan pengalaman yang lebih komprehensif.
                </p>
            </div>

            <!-- Coming Soon Features Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-left pt-2">
                <div class="p-3.5 bg-slate-50 border-2 border-black rounded-lg shadow-[3px_3px_0px_0px_#000]">
                    <div class="text-2xl mb-1">🗓️</div>
                    <div class="text-xs font-black text-black">Matriks Slot KBM</div>
                    <div class="text-[11px] font-semibold text-slate-600 mt-1">Grid jadwal interaktif per rombel & jam pelajaran.</div>
                </div>

                <div class="p-3.5 bg-slate-50 border-2 border-black rounded-lg shadow-[3px_3px_0px_0px_#000]">
                    <div class="text-2xl mb-1">⚡</div>
                    <div class="text-xs font-black text-black">Cek Bentrok Otomatis</div>
                    <div class="text-[11px] font-semibold text-slate-600 mt-1">Validasi real-time ruang kelas dan guru pengampu.</div>
                </div>

                <div class="p-3.5 bg-slate-50 border-2 border-black rounded-lg shadow-[3px_3px_0px_0px_#000]">
                    <div class="text-2xl mb-1">📑</div>
                    <div class="text-xs font-black text-black">Jurnal & Presensi</div>
                    <div class="text-[11px] font-semibold text-slate-600 mt-1">Terhubung langsung dengan agenda presensi per jam.</div>
                </div>
            </div>

            <!-- Footer Action CTA -->
            <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('guru.dashboard') }}" class="w-full sm:w-auto neo-btn bg-[#5294FF] text-white font-bold px-6 py-2.5 text-xs shadow-[3px_3px_0px_0px_#000] hover:bg-blue-600">
                    ← Kembali ke Dashboard Utama
                </a>
                <a href="{{ route('guru.dashboard') }}#jadwal" class="w-full sm:w-auto neo-btn bg-[#20C997] text-black font-bold px-6 py-2.5 text-xs shadow-[3px_3px_0px_0px_#000] hover:bg-emerald-400">
                    Buka Jadwal Hari Ini di Dashboard →
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
