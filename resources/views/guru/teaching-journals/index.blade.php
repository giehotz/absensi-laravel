@extends('layouts.guru')

@section('title', 'Jurnal Kegiatan Mengajar Guru')
@section('page-title', 'Jurnal Kegiatan Guru')

@section('content')
<div class="space-y-5 sm:space-y-6 pb-12">
    <!-- Header Section (Adapted, Optimized, Colorized Neo-Brutalism) -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 bg-white border-3 border-black p-4 sm:p-6 rounded-lg shadow-[4px_4px_0px_0px_#000]">
        <div class="space-y-1 max-w-2xl">
            <div class="flex items-center gap-2 text-xs font-heading font-black text-slate-500 uppercase tracking-wider">
                <span>Aktivitas KBM</span>
                <span>/</span>
                <span class="text-black bg-[#FFD43B] px-1.5 py-0.2 border border-black shadow-[1px_1px_0px_#000]">
                    Jurnal Harian
                </span>
            </div>
            <h1 class="font-heading font-black text-xl sm:text-2xl lg:text-3xl text-black leading-tight">
                Jurnal Harian Guru Mengajar
            </h1>
            <p class="text-xs sm:text-sm font-semibold text-slate-700 leading-relaxed">
                Catatan agenda pembelajaran, tujuan KBM, rekap presensi kelas terintegrasi, dan evaluasi proses mengajar.
            </p>
        </div>

        <!-- Action Buttons: Responsive Hierarchy -->
        <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-2.5 w-full lg:w-auto shrink-0">
            <!-- Primary CTA: Buat Jurnal Baru -->
            <a href="{{ route('guru.teaching-journals.create') }}" 
               class="neo-btn bg-[#FFD43B] hover:bg-[#ffe066] text-black px-4 py-2.5 min-h-[44px] text-xs font-heading font-black uppercase flex items-center justify-center gap-2 cursor-pointer border-2 border-black shadow-[3px_3px_0px_0px_#000] transition-transform active:translate-x-0.5 active:translate-y-0.5 order-1 sm:order-last">
                <svg class="w-4 h-4 text-black shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>+ Isi Jurnal Baru</span>
            </a>

            <div class="grid grid-cols-3 sm:flex sm:items-center gap-2">
                <!-- Tombol Upload Excel -->
                <button type="button" 
                        onclick="openUploadModal()"
                        class="neo-btn bg-[#339AF0] hover:bg-blue-600 text-white px-3 py-2.5 min-h-[44px] sm:min-h-[38px] text-xs font-heading font-black flex flex-col sm:flex-row items-center justify-center gap-1 sm:gap-1.5 border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer transition-transform active:translate-x-0.5 active:translate-y-0.5"
                        title="Unggah berkas Excel jurnal KBM">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <span>Upload</span>
                </button>

                <!-- Tombol Unduh Template Excel -->
                <a href="{{ route('guru.teaching-journals.template') }}" 
                   class="neo-btn bg-[#20C997] hover:bg-emerald-400 text-black px-3 py-2.5 min-h-[44px] sm:min-h-[38px] text-xs font-heading font-black flex flex-col sm:flex-row items-center justify-center gap-1 sm:gap-1.5 border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer transition-transform active:translate-x-0.5 active:translate-y-0.5"
                   title="Unduh Template Excel sesuai jadwal mengajar Anda">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span>Template</span>
                </a>

                <!-- Tombol Cetak Dokumen PDF Resmi -->
                <a href="{{ route('guru.teaching-journals.print', request()->all()) }}" 
                   target="_blank"
                   class="neo-btn bg-white hover:bg-slate-100 text-black px-3 py-2.5 min-h-[44px] sm:min-h-[38px] text-xs font-heading font-black flex flex-col sm:flex-row items-center justify-center gap-1 sm:gap-1.5 border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer transition-transform active:translate-x-0.5 active:translate-y-0.5"
                   title="Cetak Berkas Jurnal Resmi">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>PDF</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 3 Kartu Metrik KPI (Semantic Neo-Brutalism) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 sm:gap-4">
        <!-- 1. Total Jurnal -->
        <div class="bg-[#FFF4E6] border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000] flex items-center justify-between transition-transform hover:-translate-y-0.5">
            <div>
                <div class="text-[10px] font-heading font-black uppercase tracking-wider text-amber-950">
                    Total Jurnal Disimpan
                </div>
                <div class="font-heading font-black text-2xl sm:text-3xl text-black mt-1 leading-none">
                    {{ number_format($totalJournals) }}
                </div>
                <div class="text-[11px] text-amber-900 font-bold mt-1">
                    Seluruh aktivitas KBM Anda
                </div>
            </div>
            <div class="w-10 h-10 rounded-sm bg-black text-[#FFD43B] flex items-center justify-center shrink-0 border border-black shadow-[1.5px_1.5px_0px_#000]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
        </div>

        <!-- 2. Jurnal Bulan Ini -->
        <div class="bg-[#E7F5FF] border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000] flex items-center justify-between transition-transform hover:-translate-y-0.5">
            <div>
                <div class="text-[10px] font-heading font-black uppercase tracking-wider text-blue-950">
                    Jurnal Bulan Ini
                </div>
                <div class="font-heading font-black text-2xl sm:text-3xl text-black mt-1 leading-none">
                    {{ number_format($thisMonthJournals) }}
                </div>
                <div class="text-[11px] text-blue-900 font-bold mt-1">
                    {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}
                </div>
            </div>
            <div class="w-10 h-10 rounded-sm bg-black text-[#339AF0] flex items-center justify-center shrink-0 border border-black shadow-[1.5px_1.5px_0px_#000]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>

        <!-- 3. Rata-rata Kehadiran Siswa -->
        <div class="bg-[#D3F9D8] border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000] flex items-center justify-between transition-transform hover:-translate-y-0.5">
            <div>
                <div class="text-[10px] font-heading font-black uppercase tracking-wider text-emerald-950">
                    Rata-rata Kehadiran
                </div>
                <div class="font-heading font-black text-2xl sm:text-3xl text-black mt-1 leading-none">
                    {{ number_format($avgAttendance, 1) }}%
                </div>
                <div class="text-[11px] text-emerald-900 font-bold mt-1">
                    Tingkat kehadiran di kelas Anda
                </div>
            </div>
            <div class="w-10 h-10 rounded-sm bg-black text-[#20C997] flex items-center justify-center shrink-0 border border-black shadow-[1.5px_1.5px_0px_#000]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Filter Card: Responsive Grid -->
    <div class="bg-white border-2 border-black p-4 sm:p-5 rounded-lg shadow-[4px_4px_0px_0px_#000]">
        <form method="GET" action="{{ route('guru.teaching-journals.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-[11px] font-heading font-black text-black uppercase tracking-wider mb-1">
                    Kelas
                </label>
                <select name="school_class_id" class="w-full neo-input px-3 py-2 text-xs bg-slate-50 font-bold text-black border-2 border-black rounded-xs focus:bg-white min-h-[42px]">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ (string)$classId === (string)$c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-heading font-black text-black uppercase tracking-wider mb-1">
                    Mata Pelajaran
                </label>
                <select name="subject_id" class="w-full neo-input px-3 py-2 text-xs bg-slate-50 font-bold text-black border-2 border-black rounded-xs focus:bg-white min-h-[42px]">
                    <option value="">Semua Mata Pelajaran</option>
                    @foreach($subjects as $s)
                        <option value="{{ $s->id }}" {{ (string)$subjectId === (string)$s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-heading font-black text-black uppercase tracking-wider mb-1">
                    Mulai Tanggal
                </label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full neo-input px-3 py-2 text-xs bg-slate-50 font-bold text-black border-2 border-black rounded-xs focus:bg-white min-h-[42px]">
            </div>

            <div>
                <label class="block text-[11px] font-heading font-black text-black uppercase tracking-wider mb-1">
                    Sampai Tanggal
                </label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full neo-input px-3 py-2 text-xs bg-slate-50 font-bold text-black border-2 border-black rounded-xs focus:bg-white min-h-[42px]">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" 
                        class="flex-1 neo-btn bg-[#20C997] hover:bg-emerald-400 text-black text-xs font-heading font-black py-2.5 min-h-[42px] border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer transition-transform active:translate-x-0.5 active:translate-y-0.5">
                    Terapkan Filter
                </button>
                @if($classId || $subjectId || $startDate || $endDate)
                    <a href="{{ route('guru.teaching-journals.index') }}" 
                       class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-xs font-bold py-2.5 px-3 min-h-[42px] flex items-center justify-center border-2 border-black shadow-[2px_2px_0px_0px_#000]" 
                       title="Reset Filter">
                        ✕
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Data Presentation: Desktop Tabular & Mobile Card Adaptive -->
    <div class="bg-white border-2 border-black rounded-lg shadow-[4px_4px_0px_0px_#000] overflow-hidden">
        <!-- Top Container Header -->
        <div class="px-4 sm:px-5 py-3.5 bg-slate-50 border-b-2 border-black flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#20C997] border border-black inline-block"></span>
                <span class="font-heading font-black text-sm uppercase tracking-wider text-black">
                    Daftar Rekam KBM Harian
                </span>
            </div>
            <span class="text-xs font-mono font-bold text-slate-700 bg-white px-2 py-0.5 border border-black self-start sm:self-auto shadow-[1px_1px_0px_#000]">
                Total: {{ $journals->total() }} Catatan
            </span>
        </div>

        @if($journals->isEmpty())
            <!-- Empty State -->
            <div class="p-8 sm:p-12 text-center text-slate-500 space-y-3">
                <div class="w-14 h-14 rounded-full bg-[#FFF9DB] border-2 border-black mx-auto flex items-center justify-center text-2xl shadow-[2px_2px_0px_#000]">
                    📋
                </div>
                <div class="font-heading font-black text-base sm:text-lg text-black">
                    Belum Ada Catatan Jurnal Mengajar
                </div>
                <p class="text-xs sm:text-sm text-slate-600 max-w-md mx-auto font-medium">
                    Belum ada rekaman KBM yang sesuai dengan kriteria. Klik tombol <strong>+ Isi Jurnal Baru</strong> atau <strong>Upload Excel</strong> untuk mencatat aktivitas mengajar Anda.
                </p>
                <div class="pt-2">
                    <a href="{{ route('guru.teaching-journals.create') }}" 
                       class="neo-btn bg-[#FFD43B] hover:bg-[#ffe066] text-black px-4 py-2 text-xs font-heading font-black border-2 border-black shadow-[2px_2px_0px_#000] inline-flex items-center gap-1.5">
                        <span>+ Mulai Buat Jurnal</span>
                    </a>
                </div>
            </div>
        @else
            <!-- 1. DESKTOP VIEW: Structured Tabular (Hidden on Mobile) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-xs text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100 border-b-2 border-black text-black uppercase tracking-wider font-heading font-black">
                            <th class="p-3 text-center border-r-2 border-black w-10">No</th>
                            <th class="p-3 border-r-2 border-black min-w-[130px]">Hari / Tanggal</th>
                            <th class="p-3 text-center border-r-2 border-black min-w-[90px]">Kelas</th>
                            <th class="p-3 text-center border-r-2 border-black w-20">Pertemuan</th>
                            <th class="p-3 border-r-2 border-black min-w-[200px]">Tujuan Pembelajaran</th>
                            <th class="p-3 border-r-2 border-black min-w-[220px]">Kegiatan KBM</th>
                            <th class="p-3 text-center border-r-2 border-black min-w-[110px]">Absensi (S, I, A)</th>
                            <th class="p-3 text-center border-r-2 border-black min-w-[90px]">% Hadir</th>
                            <th class="p-3 border-r-2 border-black min-w-[180px]">Permasalahan KBM</th>
                            <th class="p-3 text-center w-36">Aksi & Visibilitas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-black/15">
                        @foreach($journals as $index => $journal)
                        <tr class="hover:bg-amber-50/50 transition-colors">
                            <td class="p-3 text-center font-mono font-bold border-r-2 border-black/15 text-slate-700">
                                {{ $journals->firstItem() + $index }}
                            </td>
                            <td class="p-3 border-r-2 border-black/15">
                                <div class="font-heading font-black text-black text-xs">{{ $journal->day_name }}</div>
                                <div class="font-mono text-[11px] text-slate-600">{{ $journal->formatted_date }}</div>
                                <div class="text-[10px] font-bold text-blue-700 mt-0.5 truncate">{{ $journal->subject?->name }}</div>
                            </td>
                            <td class="p-3 text-center border-r-2 border-black/15">
                                <span class="inline-block bg-slate-100 px-2 py-0.5 border border-black font-mono font-bold text-black rounded-xs shadow-[1px_1px_0px_#000]">
                                    {{ $journal->schoolClass?->name ?? '-' }}
                                </span>
                            </td>
                            <td class="p-3 text-center font-mono font-black text-sm border-r-2 border-black/15">
                                <span class="bg-[#FFF9DB] border border-black px-1.5 py-0.5 rounded-xs">
                                    Ke-{{ $journal->meeting_number }}
                                </span>
                            </td>
                            <td class="p-3 border-r-2 border-black/15 font-medium text-slate-800">
                                <div class="line-clamp-3 leading-relaxed">{{ $journal->learning_objective }}</div>
                            </td>
                            <td class="p-3 border-r-2 border-black/15 font-medium text-slate-800">
                                <div class="line-clamp-3 leading-relaxed">{{ $journal->teaching_activity }}</div>
                            </td>
                            <td class="p-3 text-center border-r-2 border-black/15">
                                <div class="flex items-center justify-center gap-1 font-mono text-[10px]">
                                    <span class="bg-[#FFF3BF] border border-black px-1.5 py-0.5 rounded-xs font-bold text-amber-950 shadow-[1px_1px_0px_#000]" title="Sakit: {{ $journal->count_sakit }}">
                                        S:{{ $journal->count_sakit }}
                                    </span>
                                    <span class="bg-[#E7F5FF] border border-black px-1.5 py-0.5 rounded-xs font-bold text-blue-950 shadow-[1px_1px_0px_#000]" title="Izin: {{ $journal->count_izin }}">
                                        I:{{ $journal->count_izin }}
                                    </span>
                                    <span class="bg-[#FFE3E3] border border-black px-1.5 py-0.5 rounded-xs font-bold text-rose-950 shadow-[1px_1px_0px_#000]" title="Alpa: {{ $journal->count_alpa }}">
                                        A:{{ $journal->count_alpa }}
                                    </span>
                                </div>
                            </td>
                            <td class="p-3 text-center border-r-2 border-black/15">
                                <span class="inline-block px-2 py-0.5 border border-black rounded-xs text-[11px] font-mono font-black shadow-[1px_1px_0px_#000] {{ $journal->attendance_percentage >= 80 ? 'bg-[#D3F9D8] text-emerald-950' : 'bg-[#FFE3E3] text-rose-950' }}">
                                    {{ number_format($journal->attendance_percentage, 1) }}%
                                </span>
                            </td>
                            <td class="p-3 border-r-2 border-black/15 text-slate-700">
                                @if($journal->teaching_problem)
                                    <div class="line-clamp-2 text-rose-950 font-semibold italic bg-rose-50 border border-rose-200 p-1.5 rounded-xs">
                                        {{ $journal->teaching_problem }}
                                    </div>
                                @else
                                    <span class="text-slate-400 font-bold">-</span>
                                @endif
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Toggle Share with Students -->
                                    <form action="{{ route('guru.teaching-journals.toggle-share', $journal) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        @if($journal->is_shared_with_students)
                                            <button type="submit" 
                                                    class="p-1.5 bg-[#D3F9D8] hover:bg-emerald-300 text-emerald-950 border border-black rounded-xs shadow-[1px_1px_0px_#000] cursor-pointer transition-transform active:scale-90"
                                                    title="Status: Tampil di Siswa (Klik untuk sembunyikan)">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </button>
                                        @else
                                            <button type="submit" 
                                                    class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-500 border border-black rounded-xs shadow-[1px_1px_0px_#000] cursor-pointer transition-transform active:scale-90"
                                                    title="Status: Disembunyikan dari Siswa (Klik untuk tampilkan)">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </form>

                                    <!-- Tombol Edit -->
                                    <a href="{{ route('guru.teaching-journals.edit', $journal) }}" 
                                       class="p-1.5 bg-[#FFD43B] hover:bg-yellow-400 text-black border border-black rounded-xs shadow-[1px_1px_0px_#000] cursor-pointer transition-transform active:scale-90"
                                       title="Edit Catatan Jurnal">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </a>

                                    <!-- Tombol Hapus -->
                                    <button type="button" 
                                            onclick="confirmDeleteJournal('delete-form-{{ $journal->id }}', 'Pertemuan Ke-{{ $journal->meeting_number }} ({{ $journal->subject?->name }})')"
                                            class="p-1.5 bg-[#FF6B6B] hover:bg-rose-600 text-white border border-black rounded-xs shadow-[1px_1px_0px_#000] cursor-pointer transition-transform active:scale-90"
                                            title="Hapus Catatan Jurnal">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                    <form id="delete-form-{{ $journal->id }}" 
                                          action="{{ route('guru.teaching-journals.destroy', $journal) }}" 
                                          method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- 2. MOBILE VIEW: Adaptive Card System (block md:hidden) -->
            <div class="md:hidden p-3 sm:p-4 space-y-3.5 bg-slate-50">
                @foreach($journals as $index => $journal)
                    <div class="bg-white border-2 border-black rounded-lg p-4 space-y-3 shadow-[3px_3px_0px_0px_#000] transition-all">
                        <!-- Card Header -->
                        <div class="flex items-start justify-between gap-2 pb-2.5 border-b-2 border-black/10">
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-xs font-heading font-black text-black">
                                        {{ $journal->day_name }}, {{ $journal->formatted_date }}
                                    </span>
                                </div>
                                <div class="font-heading font-black text-sm text-blue-900 mt-0.5 truncate">
                                    {{ $journal->subject?->name ?? 'Mata Pelajaran' }}
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1 shrink-0">
                                <span class="bg-[#FFF9DB] border border-black px-2 py-0.5 text-xs font-mono font-black text-black rounded-xs shadow-[1px_1px_0px_#000]">
                                    Ke-{{ $journal->meeting_number }}
                                </span>
                                <span class="bg-slate-100 border border-black/40 px-1.5 py-0.2 text-[10px] font-mono font-bold text-slate-700 rounded-xs">
                                    {{ $journal->schoolClass?->name ?? '-' }}
                                </span>
                            </div>
                        </div>

                        <!-- Attendance Stats Bar inside Card -->
                        <div class="flex items-center justify-between gap-2 p-2 bg-slate-50 border border-black rounded-xs text-xs font-mono">
                            <div class="flex items-center gap-1 font-bold">
                                <span class="text-slate-500 text-[11px]">Kehadiran:</span>
                                <span class="font-black px-1.5 py-0.2 rounded-xs border border-black {{ $journal->attendance_percentage >= 80 ? 'bg-[#D3F9D8] text-emerald-950' : 'bg-[#FFE3E3] text-rose-950' }}">
                                    {{ number_format($journal->attendance_percentage, 1) }}%
                                </span>
                            </div>
                            <div class="flex items-center gap-1 text-[10px] font-bold">
                                <span class="bg-[#FFF3BF] border border-black/60 px-1 py-0.2 text-amber-950">S:{{ $journal->count_sakit }}</span>
                                <span class="bg-[#E7F5FF] border border-black/60 px-1 py-0.2 text-blue-950">I:{{ $journal->count_izin }}</span>
                                <span class="bg-[#FFE3E3] border border-black/60 px-1 py-0.2 text-rose-950">A:{{ $journal->count_alpa }}</span>
                            </div>
                        </div>

                        <!-- Card Content -->
                        <div class="space-y-2 text-xs">
                            <div>
                                <span class="text-[10px] font-heading font-black uppercase text-slate-500 tracking-wider">
                                    🎯 Tujuan Pembelajaran
                                </span>
                                <p class="text-slate-900 font-medium leading-relaxed mt-0.5 line-clamp-3">
                                    {{ $journal->learning_objective }}
                                </p>
                            </div>

                            <div>
                                <span class="text-[10px] font-heading font-black uppercase text-slate-500 tracking-wider">
                                    📝 Kegiatan KBM
                                </span>
                                <p class="text-slate-800 font-medium leading-relaxed mt-0.5 line-clamp-3">
                                    {{ $journal->teaching_activity }}
                                </p>
                            </div>

                            @if($journal->teaching_problem)
                                <div class="p-2.5 bg-rose-50 border border-rose-300 rounded-xs text-xs text-rose-950">
                                    <span class="font-heading font-black text-[10px] uppercase block mb-0.5 text-rose-900">
                                        ⚠️ Permasalahan KBM:
                                    </span>
                                    <span class="italic font-medium">{{ $journal->teaching_problem }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Card Action Bar: Full-Width Touch Controls -->
                        <div class="pt-3 border-t border-black/10 flex items-center justify-between gap-2">
                            <!-- Toggle Share -->
                            <form action="{{ route('guru.teaching-journals.toggle-share', $journal) }}" method="POST" class="flex-1">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                        class="w-full neo-btn py-2 px-2 text-[11px] font-heading font-black flex items-center justify-center gap-1.5 border border-black rounded-xs shadow-[1.5px_1.5px_0px_#000] min-h-[42px] cursor-pointer {{ $journal->is_shared_with_students ? 'bg-[#D3F9D8] text-emerald-950' : 'bg-slate-100 text-slate-700' }}">
                                    @if($journal->is_shared_with_students)
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <span>Tampil Siswa</span>
                                    @else
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                        </svg>
                                        <span>Privat</span>
                                    @endif
                                </button>
                            </form>

                            <!-- Edit Button -->
                            <a href="{{ route('guru.teaching-journals.edit', $journal) }}" 
                               class="neo-btn bg-[#FFD43B] hover:bg-yellow-400 text-black py-2 px-3 text-xs font-heading font-black flex items-center justify-center gap-1 border border-black rounded-xs shadow-[1.5px_1.5px_0px_#000] min-h-[42px]">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                                <span>Edit</span>
                            </a>

                            <!-- Delete Button -->
                            <button type="button" 
                                    onclick="confirmDeleteJournal('delete-form-mobile-{{ $journal->id }}', 'Pertemuan Ke-{{ $journal->meeting_number }}')"
                                    class="neo-btn bg-[#FFE3E3] text-rose-950 hover:bg-[#FF6B6B] hover:text-white py-2 px-3 text-xs font-heading font-black flex items-center justify-center gap-1 border border-black rounded-xs shadow-[1.5px_1.5px_0px_#000] min-h-[42px] cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span>Hapus</span>
                            </button>
                            <form id="delete-form-mobile-{{ $journal->id }}" 
                                  action="{{ route('guru.teaching-journals.destroy', $journal) }}" 
                                  method="POST" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($journals->hasPages())
        <div class="p-4 border-t-2 border-black bg-slate-50">
            {{ $journals->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Upload Excel Jurnal KBM (Neo-Brutalism Backdrop Blur) -->
<div id="uploadExcelModal" class="hidden fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white border-3 border-black rounded-lg shadow-[6px_6px_0px_0px_#000] max-w-md w-full p-5 space-y-4">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-sm bg-black text-[#339AF0] flex items-center justify-center border border-black">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                </div>
                <h3 class="font-heading font-black text-base text-black">Upload Jurnal Excel</h3>
            </div>
            <button type="button" onclick="closeUploadModal()" class="text-black font-black text-xl hover:text-rose-600 cursor-pointer">✕</button>
        </div>

        <form action="{{ route('guru.teaching-journals.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-heading font-black text-black uppercase tracking-wider mb-1.5">
                    Pilih Berkas Excel (.xlsx / .xls)
                </label>
                <input type="file" 
                       name="file" 
                       accept=".xlsx,.xls,.csv" 
                       required 
                       class="w-full neo-input px-3 py-2 text-xs bg-slate-50 font-bold border-2 border-black rounded-xs focus:bg-white cursor-pointer">
                <p class="text-[11px] text-slate-600 font-medium mt-1.5">
                    Gunakan file yang diunduh dari tombol <strong>Unduh Template</strong> agar jadwal dan format kolom terisi akurat.
                </p>
            </div>

            <div class="bg-[#FFF9DB] border-2 border-black p-3 rounded-xs text-xs text-amber-950 font-semibold space-y-1">
                <div class="font-heading font-black flex items-center gap-1">
                    <span>💡</span> Pratinjau Terlebih Dahulu
                </div>
                <p>Setelah file diunggah, Anda akan diarahkan ke form pratinjau untuk memeriksa dan mengedit data sebelum menekan tombol <strong>Publish</strong>.</p>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t-2 border-black/10">
                <button type="button" onclick="closeUploadModal()" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black px-4 py-2 text-xs font-heading font-black border border-black">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#20C997] hover:bg-emerald-400 text-black px-5 py-2 text-xs font-heading font-black border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                    Upload & Pratinjau →
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openUploadModal() {
        document.getElementById('uploadExcelModal').classList.remove('hidden');
    }
    function closeUploadModal() {
        document.getElementById('uploadExcelModal').classList.add('hidden');
    }

    function confirmDeleteJournal(formId, journalTitle) {
        Swal.fire({
            title: 'Hapus Jurnal?',
            html: `Apakah Anda yakin ingin menghapus catatan jurnal <b>${journalTitle}</b>?<br><small class="text-rose-600 font-bold">Tindakan ini tidak dapat dibatalkan.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#FF6B6B',
            cancelButtonColor: '#000000',
            confirmButtonText: '✕ Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById(formId);
                if (form) form.submit();
            }
        });
    }
</script>
@endpush
@endsection
