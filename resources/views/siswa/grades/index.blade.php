@extends('layouts.neobrutalism')

@section('title', 'Capaian Nilai Sumatif - ' . ($student->user->name ?? 'Portal Siswa'))

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
                <span class="text-xs font-black uppercase tracking-wider text-black">Nilai Sumatif</span>
            </div>
        </div>
        <div class="text-right">
            <span class="bg-[#FFD43B] text-black text-[10px] font-black uppercase px-3 py-1 rounded-full border border-black shadow-[1.5px_1.5px_0px_0px_#000]">
                Kelas {{ $student->schoolClass?->name ?? '-' }}
            </span>
        </div>
    </div>

    <!-- Hero Card Banner -->
    <div class="bg-[#FFF9DB] rounded-2xl border-2 sm:border-[2.5px] border-black p-4 sm:p-5 shadow-[4px_4px_0px_0px_#000] flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-[#FFD43B] text-black border-2 border-black flex items-center justify-center text-2xl shadow-[2px_2px_0px_0px_#000] shrink-0">
            📊
        </div>
        <div>
            <h1 class="font-heading font-black text-base sm:text-lg text-black">Capaian Nilai Sumatif Siswa</h1>
            <p class="text-xs text-amber-950 font-semibold mt-0.5">
                Rekap nilai sumatif yang telah berstatus resmi (final) dari seluruh mata pelajaran di semester ini.
            </p>
        </div>
    </div>

    @if($packages->isEmpty())
        <div class="bg-white rounded-2xl border-2 sm:border-[2.5px] border-black p-10 text-center shadow-[4px_4px_0px_0px_#000]">
            <div class="w-14 h-14 bg-slate-100 border-2 border-black rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                ⏳
            </div>
            <h3 class="font-heading font-black text-base text-black">Belum Ada Nilai yang Difinalisasi</h3>
            <p class="text-xs text-slate-600 mt-1 max-w-sm mx-auto">
                Nilai sumatif Anda sedang dalam proses evaluasi oleh bapak/ibu guru pengampu. Nilai resmi akan tampil setelah difinalisasi.
            </p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($packages as $pkg)
                @php
                    $activeAsms = $pkg->getActiveAssessments();
                    $scoresList = [];
                    foreach($activeAsms as $asm) {
                        $s = $asm->scores->first()?->score;
                        if ($s !== null) $scoresList[] = (float)$s;
                    }
                    $avg = count($scoresList) > 0 ? round(array_sum($scoresList)/count($scoresList), 1) : null;
                    $isPassed = $avg !== null && $avg >= $pkg->kktp_default;
                @endphp
                <div class="bg-white rounded-2xl border-2 sm:border-[2.5px] border-black p-4 sm:p-5 shadow-[4px_4px_0px_0px_#000] space-y-4">
                    <!-- Subject Header -->
                    <div class="flex items-start justify-between gap-3 border-b-2 border-slate-100 pb-3">
                        <div>
                            <span class="text-[10px] font-black uppercase text-slate-500 tracking-wider">
                                Mata Pelajaran
                            </span>
                            <h2 class="font-heading font-black text-lg text-black">{{ $pkg->subject->name }}</h2>
                            <p class="text-xs text-slate-600">
                                Guru: <strong class="text-black">{{ $pkg->teacher->user?->name ?? '-' }}</strong> • Standar KKTP: <strong class="text-black">{{ $pkg->kktp_default }}</strong>
                            </p>
                        </div>

                        <div class="text-right shrink-0">
                            <div class="text-[10px] font-bold text-slate-500 uppercase">Rata-Rata</div>
                            <div class="font-heading font-black text-xl {{ $avg !== null ? ($isPassed ? 'text-emerald-700' : 'text-rose-600') : 'text-slate-400' }}">
                                {{ $avg !== null ? $avg : '-' }}
                            </div>
                            @if($avg !== null)
                                <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded border {{ $isPassed ? 'bg-emerald-100 text-emerald-950 border-emerald-300' : 'bg-rose-100 text-rose-950 border-rose-300' }}">
                                    {{ $isPassed ? 'Tuntas' : 'Remedial' }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Sumatif Grid List -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2.5">
                        @foreach($activeAsms as $asm)
                            @php
                                $scoreObj = $asm->scores->first();
                                $scoreVal = $scoreObj?->score;
                                $isScoreTuntas = $scoreVal !== null && $scoreVal >= $asm->kktp;
                            @endphp
                            <div class="border-2 border-black rounded-xl p-2.5 flex flex-col justify-between {{ $scoreVal !== null ? ($isScoreTuntas ? 'bg-[#D3F9D8]' : 'bg-[#FFE3E3]') : 'bg-slate-50 opacity-60' }}">
                                <div class="flex items-center justify-between text-[10px] font-bold mb-1">
                                    <span class="text-black font-black">SUM {{ $asm->sheet_number }}</span>
                                    <span class="text-[9px] text-slate-500">KKTP {{ $asm->kktp }}</span>
                                </div>
                                
                                <div class="text-center my-1">
                                    <span class="font-heading font-black text-xl text-black">
                                        {{ $scoreVal !== null ? (float)$scoreVal : '-' }}
                                    </span>
                                </div>

                                <div class="text-[10px] font-medium text-slate-700 truncate text-center" title="{{ $asm->materi ?? 'Materi belum diisi' }}">
                                    {{ !empty($asm->materi) ? $asm->materi : '-' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
