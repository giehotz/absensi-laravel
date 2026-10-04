@extends('layouts.admin')

@section('title', 'Detail Paket Penilaian - ' . $package->title)
@section('page-title', 'Detail Penilaian Sumatif')

@section('content')
<div class="space-y-6 pb-12">
    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.penilaian.index') }}" class="text-xs font-bold text-slate-600 hover:text-black flex items-center gap-1.5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke Monitoring Penilaian</span>
        </a>

        <div class="flex items-center gap-2">
            <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded border-2 border-black {{ $package->isLocked() ? 'bg-[#D3F9D8] text-emerald-950 shadow-[2px_2px_0px_0px_#000]' : 'bg-[#FFF9DB] text-amber-950 shadow-[2px_2px_0px_0px_#000]' }}">
                Status: {{ $package->isLocked() ? 'TERKUNCI (FINAL)' : 'DRAFT' }}
            </span>
        </div>
    </div>

    <!-- Header Section -->
    <div class="bg-white border-2 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000]">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                    <span>{{ $package->schoolClass->name }}</span>
                    <span>•</span>
                    <span class="text-black">{{ $package->subject->name }}</span>
                    <span>•</span>
                    <span>Tahun Ajaran {{ $package->academicYear->name }} ({{ ucfirst($package->academicYear->semester) }})</span>
                </div>
                <h2 class="font-heading font-black text-2xl text-black">{{ $package->title }}</h2>
                <p class="text-xs text-slate-600 mt-0.5">
                    Guru Pengampu: <strong class="text-black">{{ $package->teacher->user?->name ?? '-' }}</strong> | Standar KKTP: <strong class="text-black">{{ $package->kktp_default }}</strong>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if($package->isLocked())
                    <!-- Tombol Unlock Khusus Admin -->
                    <form action="{{ route('admin.penilaian.unlock', $package) }}" method="POST"
                          onsubmit="return confirm('Buka kunci paket nilai ini agar guru pengampu dapat memperbarui nilai kembali?');">
                        @csrf
                        @method('PATCH')
                        <button type="submit" 
                                class="neo-btn bg-[#5294FF] hover:bg-blue-600 text-white px-3.5 py-2 text-xs font-black flex items-center gap-1.5 shadow-[3px_3px_0px_0px_#000] border-2 border-black rounded-lg cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                            </svg>
                            <span>Buka Kunci (Unlock)</span>
                        </button>
                    </form>
                @endif

                <!-- Export Excel -->
                <a href="{{ route('admin.penilaian.export', $package) }}" 
                   class="neo-btn bg-white hover:bg-slate-100 text-black px-3.5 py-2 text-xs font-bold flex items-center gap-1.5 shadow-[3px_3px_0px_0px_#000] border-2 border-black rounded-lg cursor-pointer">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Export Rekap Excel</span>
                </a>

                <!-- Cetak PDF Ber-Kop -->
                <a href="{{ route('admin.penilaian.print', $package) }}" target="_blank"
                   class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-3.5 py-2 text-xs font-black flex items-center gap-1.5 shadow-[3px_3px_0px_0px_#000] border-2 border-black rounded-lg cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Cetak PDF Resmi</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Table of Scores -->
    <div class="bg-white border-2 border-black rounded-lg shadow-[4px_4px_0px_0px_#000] overflow-hidden">
        <div class="p-4 bg-slate-100 border-b-2 border-black flex items-center justify-between">
            <h3 class="font-heading font-black text-base text-black">Matriks Rekapitulasi Nilai Sumatif Aktif ({{ $activeAssessments->count() }} Penilaian)</h3>
            <span class="text-[11px] font-bold px-2 py-0.5 bg-white border border-black rounded shadow-[1px_1px_0px_0px_#000]">
                S1 @if($activeAssessments->count() > 1) s.d. S{{ $activeAssessments->max('sheet_number') }} @endif
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b-2 border-black text-[11px] font-black uppercase text-black">
                        <th class="py-3 px-3 border-r-2 border-black w-10 text-center">No</th>
                        <th class="py-3 px-3 border-r-2 border-black w-24">NIS</th>
                        <th class="py-3 px-3 border-r-2 border-black min-w-[160px]">Nama Siswa</th>
                        @foreach($activeAssessments as $asm)
                            <th class="py-2 px-2 border-r border-slate-300 text-center min-w-[50px]" title="{{ $asm->materi ? 'Materi: ' . $asm->materi : 'SUM ' . $asm->sheet_number }}">
                                <div class="font-black text-black">S{{ $asm->sheet_number }}</div>
                                <div class="text-[9px] font-normal text-slate-500">KKTP {{ $asm->kktp ?? $package->kktp_default }}</div>
                            </th>
                        @endforeach
                        <th class="py-3 px-3 border-l-2 border-black text-center w-20 bg-slate-100">Rata-Rata</th>
                        <th class="py-3 px-3 border-l border-slate-300 text-center w-24 bg-slate-100">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 font-medium">
                    @forelse($students as $idx => $student)
                        @php
                            $scoresList = [];
                            foreach($activeAssessments as $asm) {
                                $sc = $asm->scores->firstWhere('student_id', $student->id)?->score;
                                if ($sc !== null) $scoresList[] = (float)$sc;
                            }
                            $avg = count($scoresList) > 0 ? round(array_sum($scoresList)/count($scoresList), 1) : null;
                            $status = $avg !== null ? ($avg >= $package->kktp_default ? 'Tuntas' : 'Remedial') : '-';
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-2.5 px-3 border-r-2 border-black text-center font-bold text-slate-500">{{ $idx + 1 }}</td>
                            <td class="py-2.5 px-3 border-r-2 border-black font-mono text-slate-600">{{ $student->nis ?? '-' }}</td>
                            <td class="py-2.5 px-3 border-r-2 border-black font-bold text-black whitespace-nowrap">{{ $student->user?->name ?? '-' }}</td>

                            @foreach($activeAssessments as $asm)
                                @php
                                    $score = $asm->scores->firstWhere('student_id', $student->id)?->score;
                                    $isRemed = $score !== null && $score < ($asm->kktp ?? $package->kktp_default);
                                @endphp
                                <td class="py-2 px-1 border-r border-slate-200 text-center font-bold font-mono {{ $score !== null ? ($isRemed ? 'text-red-600 bg-red-50' : 'text-emerald-700 bg-emerald-50') : 'text-slate-300' }}">
                                    {{ $score !== null ? (float)$score : '-' }}
                                </td>
                            @endforeach

                            <td class="py-2.5 px-3 border-l-2 border-black text-center font-mono font-black text-xs {{ $avg !== null ? ($avg >= $package->kktp_default ? 'text-emerald-700' : 'text-rose-600') : 'text-slate-400' }}">
                                {{ $avg !== null ? $avg : '-' }}
                            </td>
                            <td class="py-2.5 px-3 border-l border-slate-300 text-center font-black text-[10px] uppercase">
                                @if($avg !== null)
                                    <span class="px-2 py-0.5 rounded border {{ $avg >= $package->kktp_default ? 'bg-emerald-100 text-emerald-950 border-emerald-400' : 'bg-rose-100 text-rose-950 border-rose-400' }}">
                                        {{ $status }}
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $activeAssessments->count() + 5 }}" class="py-8 text-center text-slate-500 font-bold">
                                Tidak ada siswa terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
