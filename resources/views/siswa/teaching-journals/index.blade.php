@extends('layouts.neobrutalism')

@section('title', 'Jurnal Kegiatan Pembelajaran - ' . ($student->user->name ?? 'Portal Siswa'))

@section('content')
<div class="max-w-3xl mx-auto pb-16 sm:pb-20 space-y-4 px-2 sm:px-0">
    <!-- Header Navigasi -->
    <div class="bg-white rounded-2xl border-2 sm:border-[2.5px] border-black p-4 flex items-center justify-between gap-3 shadow-[4px_4px_0px_0px_#000]">
        <div class="flex items-center gap-3">
            <a href="{{ route('siswa.dashboard') }}" 
               class="bg-slate-100 hover:bg-slate-200 text-black text-xs font-black px-4 py-2 rounded-xl border-2 border-black shadow-[2px_2px_0px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[1px_1px_0px_0px_#000] flex items-center gap-1.5 transition-all">
                <span>←</span> Dashboard
            </a>
            <div class="hidden sm:block">
                <span class="text-xs font-black uppercase tracking-wider text-black">Jurnal Pembelajaran Siswa</span>
            </div>
        </div>
        <div class="text-right">
            <span class="bg-[#FFD43B] text-black text-[10px] font-black uppercase px-3 py-1 rounded-full border border-black shadow-[1.5px_1.5px_0px_0px_#000]">
                Kelas {{ $student->schoolClass?->name ?? '-' }}
            </span>
        </div>
    </div>

    <!-- Hero Card Banner -->
    <div class="bg-[#F3F0FF] rounded-2xl border-2 sm:border-[2.5px] border-black p-4 sm:p-5 shadow-[4px_4px_0px_0px_#000] flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-[#7950F2] text-white border-2 border-black flex items-center justify-center text-2xl shadow-[2px_2px_0px_0px_#000] shrink-0">
            📖
        </div>
        <div>
            <h1 class="font-heading font-black text-base sm:text-lg text-black">Jurnal Kegiatan Belajar Mengajar</h1>
            <p class="text-xs text-purple-950 font-semibold mt-0.5">
                Rangkuman materi, tujuan pembelajaran, dan agenda kelas resmi dari bapak/ibu guru pengampu.
            </p>
        </div>
    </div>

    <!-- Filter Mata Pelajaran (Chips Horizontal Scroll) -->
    @if($subjects->isNotEmpty())
    <div class="flex items-center gap-2 overflow-x-auto pb-1 pt-0.5 scrollbar-thin">
        <a href="{{ route('siswa.teaching-journals.index') }}" 
           class="px-3.5 py-2 rounded-xl text-xs font-black border-2 border-black shadow-[2px_2px_0px_0px_#000] whitespace-nowrap transition-all {{ empty($subjectId) ? 'bg-black text-white' : 'bg-white text-black hover:bg-slate-100' }}">
            Semua Mata Pelajaran
        </a>
        @foreach($subjects as $s)
            <a href="{{ route('siswa.teaching-journals.index', ['subject_id' => $s->id]) }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-black border-2 border-black shadow-[2px_2px_0px_0px_#000] whitespace-nowrap transition-all {{ (string)$subjectId === (string)$s->id ? 'bg-[#FFD43B] text-black' : 'bg-white text-black hover:bg-slate-100' }}">
                {{ $s->name }}
            </a>
        @endforeach
    </div>
    @endif

    <!-- Daftar Jurnal KBM Siswa -->
    <div class="space-y-4">
        @forelse($journals as $journal)
        <div class="bg-white rounded-2xl border-2 sm:border-[2.5px] border-black p-4 sm:p-5 shadow-[4px_4px_0px_0px_#000] space-y-3.5">
            <!-- Header Kartu: Mapel, Pertemuan, Tanggal -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 border-b-2 border-black/10 pb-3">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="bg-[#5294FF] text-white text-xs font-black px-3 py-1 border border-black rounded-full shadow-[1.5px_1.5px_0px_0px_#000]">
                        {{ $journal->subject?->name ?? 'Mata Pelajaran' }}
                    </span>
                    <span class="bg-[#FFD43B] text-black text-xs font-black px-2.5 py-0.5 border border-black rounded-md shadow-[1px_1px_0px_0px_#000]">
                        Pertemuan Ke-{{ $journal->meeting_number }}
                    </span>
                </div>
                <div class="text-xs font-mono font-bold text-slate-700 flex items-center gap-1.5">
                    <span>📅</span>
                    <span>{{ $journal->day_name }}, {{ $journal->formatted_date_long }}</span>
                </div>
            </div>

            <!-- Guru Pengampu -->
            <div class="flex items-center gap-2 text-xs">
                <span class="text-slate-600 font-bold">👨‍🏫 Guru Pengampu:</span>
                <span class="font-black text-black">{{ $journal->teacher?->user?->name ?? '-' }}</span>
            </div>

            <!-- 1. Tujuan Pembelajaran -->
            <div class="bg-amber-50 rounded-xl border-2 border-black p-3.5 shadow-[2px_2px_0px_0px_#000]">
                <div class="text-[10px] font-black uppercase text-amber-950 tracking-wider mb-1 flex items-center gap-1.5">
                    <span>🎯</span> Tujuan Pembelajaran:
                </div>
                <p class="text-xs sm:text-sm text-black font-semibold leading-relaxed">
                    {{ $journal->learning_objective }}
                </p>
            </div>

            <!-- 2. Kegiatan Belajar Mengajar (KBM) -->
            <div class="bg-slate-50 rounded-xl border-2 border-black p-3.5 shadow-[2px_2px_0px_0px_#000]">
                <div class="text-[10px] font-black uppercase text-slate-800 tracking-wider mb-1 flex items-center gap-1.5">
                    <span>📝</span> Ringkasan KBM & Materi:
                </div>
                <div class="text-xs sm:text-sm text-black font-medium leading-relaxed whitespace-pre-line">
                    {{ $journal->teaching_activity }}
                </div>
            </div>

            <!-- Footer: Kehadiran Kelas -->
            <div class="flex items-center justify-between text-xs pt-2 text-slate-600 font-bold border-t-2 border-dashed border-black/10">
                <div class="flex items-center gap-2">
                    <span>Kehadiran Kelas:</span>
                    <span class="font-mono font-black text-emerald-900 bg-[#D3F9D8] px-2.5 py-0.5 border border-black rounded-md shadow-[1px_1px_0px_0px_#000]">
                        {{ number_format($journal->attendance_percentage, 1) }}%
                    </span>
                </div>
                <span class="text-[11px] text-slate-500 font-medium">Terverifikasi Guru</span>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl border-2 border-dashed border-slate-300 p-8 text-center space-y-2">
            <div class="text-4xl">📚</div>
            <div class="font-heading font-black text-base text-black">Belum Ada Jurnal Pembelajaran</div>
            <p class="text-xs font-semibold text-slate-500 max-w-md mx-auto">
                Bapak/ibu guru belum membagikan catatan kegiatan belajar mengajar untuk kelas ini atau mata pelajaran yang dipilih.
            </p>
        </div>
        @endforelse
    </div>

    @if($journals->hasPages())
    <div class="p-3 bg-white rounded-xl border-2 border-black shadow-[3px_3px_0px_0px_#000]">
        {{ $journals->links() }}
    </div>
    @endif
</div>

<!-- Fixed Bottom Navigation Bar -->
@include('siswa._bottom-nav')
@endsection
