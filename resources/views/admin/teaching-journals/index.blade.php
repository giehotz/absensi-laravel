@extends('layouts.admin')

@section('title', 'Manajemen & Monitoring Jurnal Guru')
@section('page-title', 'Jurnal Kegiatan Guru Mengajar')

@section('content')
<div class="space-y-6 pb-12">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white border-2 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000]">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                <span>Presensi & Laporan</span>
                <span>/</span>
                <span class="text-black">Jurnal Kegiatan Guru</span>
            </div>
            <h2 class="font-heading font-black text-2xl text-black">Jurnal Kegiatan Harian Guru Mengajar</h2>
            <p class="text-xs text-slate-600 mt-0.5">
                Monitoring pelaksanaan KBM, evaluasi harian guru, catatan kendala proses belajar, dan rekap presensi kelas.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Tombol Cetak Dokumen PDF Resmi -->
            <a href="{{ route('admin.teaching-journals.print', request()->all()) }}" 
               target="_blank"
               class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-4 py-2 text-xs font-bold flex items-center gap-2 cursor-pointer shadow-[3px_3px_0px_0px_#000]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Cetak Dokumen Resmi (PDF)</span>
            </a>
        </div>
    </div>

    <!-- 4 Kartu Metrik KPI Admin -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-[#FFF4E6] border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[10px] font-black uppercase tracking-wider text-amber-900">Total Jurnal Tercatat</div>
            <div class="font-mono font-black text-2xl text-black mt-1">{{ number_format($totalJournals) }}</div>
            <div class="text-[11px] text-amber-800 font-medium mt-0.5">Seluruh rekaman kegiatan KBM</div>
        </div>
        <div class="bg-[#E7F5FF] border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[10px] font-black uppercase tracking-wider text-blue-900">Guru Aktif Mengisi</div>
            <div class="font-mono font-black text-2xl text-black mt-1">{{ number_format($activeTeachersCount) }}</div>
            <div class="text-[11px] text-blue-800 font-medium mt-0.5">Pendidik yang memiliki jurnal</div>
        </div>
        <div class="bg-[#F3F0FF] border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[10px] font-black uppercase tracking-wider text-purple-900">Jurnal Bulan Ini</div>
            <div class="font-mono font-black text-2xl text-black mt-1">{{ number_format($thisMonthJournals) }}</div>
            <div class="text-[11px] text-purple-800 font-medium mt-0.5">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</div>
        </div>
        <div class="bg-[#E6FCF5] border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[10px] font-black uppercase tracking-wider text-emerald-900">Rata-rata Kehadiran KBM</div>
            <div class="font-mono font-black text-2xl text-black mt-1">{{ number_format($avgAttendance, 1) }}%</div>
            <div class="text-[11px] text-emerald-800 font-medium mt-0.5">Rerata kehadiran siswa per sesi KBM</div>
        </div>
    </div>

    <!-- Filter Card Multi-Kriteria -->
    <div class="bg-white border-2 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000]">
        <form method="GET" action="{{ route('admin.teaching-journals.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <div>
                <label class="block text-[11px] font-black text-black uppercase tracking-wider mb-1">Guru Pengampu</label>
                <select name="teacher_id" class="w-full neo-input px-3 py-2 text-xs bg-slate-50 font-bold focus:bg-white">
                    <option value="">Semua Guru</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}" {{ (string)$teacherId === (string)$t->id ? 'selected' : '' }}>
                            {{ $t->user?->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-black text-black uppercase tracking-wider mb-1">Kelas</label>
                <select name="school_class_id" class="w-full neo-input px-3 py-2 text-xs bg-slate-50 font-bold focus:bg-white">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ (string)$classId === (string)$c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-black text-black uppercase tracking-wider mb-1">Mata Pelajaran</label>
                <select name="subject_id" class="w-full neo-input px-3 py-2 text-xs bg-slate-50 font-bold focus:bg-white">
                    <option value="">Semua Mapel</option>
                    @foreach($subjects as $s)
                        <option value="{{ $s->id }}" {{ (string)$subjectId === (string)$s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-black text-black uppercase tracking-wider mb-1">Mulai Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full neo-input px-3 py-2 text-xs bg-slate-50 font-bold focus:bg-white">
            </div>
            <div>
                <label class="block text-[11px] font-black text-black uppercase tracking-wider mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full neo-input px-3 py-2 text-xs bg-slate-50 font-bold focus:bg-white">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 neo-btn bg-[#20C997] hover:bg-[#1bb386] text-black text-xs font-black py-2.5 shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                    Filter
                </button>
                @if($teacherId || $classId || $subjectId || $startDate || $endDate)
                    <a href="{{ route('admin.teaching-journals.index') }}" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-xs font-bold py-2.5 px-3 shadow-[2px_2px_0px_0px_#000]" title="Reset Filter">
                        ✕
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Admin 9 Kolom Persis Sesuai Format Resmi + Guru & Aksi -->
    <div class="bg-white border-2 border-black rounded-lg shadow-[4px_4px_0px_0px_#000] overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-50 border-b-2 border-black flex items-center justify-between">
            <span class="font-heading font-black text-sm uppercase tracking-wider text-black">
                Semua Catatan Jurnal KBM Guru
            </span>
            <span class="text-xs text-slate-500 font-bold">Total: {{ $journals->total() }} Catatan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100 border-b-2 border-black text-black uppercase tracking-wider font-heading">
                        <th class="p-3 text-center border-r border-black w-10">No</th>
                        <th class="p-3 border-r border-black min-w-[140px]">Guru & Mapel</th>
                        <th class="p-3 border-r border-black min-w-[120px]">Hari / Tanggal</th>
                        <th class="p-3 text-center border-r border-black min-w-[80px]">Kelas</th>
                        <th class="p-3 text-center border-r border-black w-16">Pert.</th>
                        <th class="p-3 border-r border-black min-w-[190px]">Tujuan Pembelajaran</th>
                        <th class="p-3 border-r border-black min-w-[210px]">Kegiatan KBM</th>
                        <th class="p-3 text-center border-r border-black min-w-[100px]">Absensi (S, I, A)</th>
                        <th class="p-3 text-center border-r border-black min-w-[85px]">% Hadir</th>
                        <th class="p-3 border-r border-black min-w-[170px]">Permasalahan KBM</th>
                        <th class="p-3 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/20">
                    @forelse($journals as $index => $journal)
                    <tr class="hover:bg-amber-50/50 transition-colors">
                        <td class="p-3 text-center font-bold border-r border-black/20">
                            {{ $journals->firstItem() + $index }}
                        </td>
                        <td class="p-3 border-r border-black/20">
                            <div class="font-black text-black">{{ $journal->teacher?->user?->name ?? 'Guru' }}</div>
                            <div class="text-[10px] font-bold text-blue-700 mt-0.5">{{ $journal->subject?->name ?? '-' }}</div>
                            <div class="font-mono text-[10px] text-slate-500">NIP. {{ $journal->teacher?->nip ?? '-' }}</div>
                        </td>
                        <td class="p-3 border-r border-black/20">
                            <div class="font-black text-black">{{ $journal->day_name }}</div>
                            <div class="font-mono text-[11px] text-slate-600">{{ $journal->formatted_date }}</div>
                            @if($journal->is_shared_with_students)
                                <span class="inline-block mt-1 bg-emerald-100 text-emerald-900 border border-emerald-400 text-[9px] font-black px-1.5 py-0.2 rounded">
                                    👁️ Publik Siswa
                                </span>
                            @else
                                <span class="inline-block mt-1 bg-slate-100 text-slate-700 border border-slate-300 text-[9px] font-black px-1.5 py-0.2 rounded">
                                    🔒 Privat Guru
                                </span>
                            @endif
                        </td>
                        <td class="p-3 text-center font-black border-r border-black/20">
                            <span class="inline-block bg-slate-100 px-2 py-0.5 border border-black rounded shadow-[1px_1px_0px_0px_#000]">
                                {{ $journal->schoolClass?->name ?? '-' }}
                            </span>
                        </td>
                        <td class="p-3 text-center font-mono font-black text-sm border-r border-black/20">
                            {{ $journal->meeting_number }}
                        </td>
                        <td class="p-3 border-r border-black/20 font-medium">
                            <div class="line-clamp-3">{{ $journal->learning_objective }}</div>
                        </td>
                        <td class="p-3 border-r border-black/20 font-medium">
                            <div class="line-clamp-3">{{ $journal->teaching_activity }}</div>
                        </td>
                        <td class="p-3 text-center border-r border-black/20">
                            <div class="flex items-center justify-center gap-1 font-mono text-[10px]">
                                <span class="bg-amber-100 border border-black px-1 py-0.2 rounded font-black text-amber-950">
                                    S:{{ $journal->count_sakit }}
                                </span>
                                <span class="bg-blue-100 border border-black px-1 py-0.2 rounded font-black text-blue-950">
                                    I:{{ $journal->count_izin }}
                                </span>
                                <span class="bg-rose-100 border border-black px-1 py-0.2 rounded font-black text-rose-950">
                                    A:{{ $journal->count_alpa }}
                                </span>
                            </div>
                        </td>
                        <td class="p-3 text-center border-r border-black/20">
                            <span class="inline-block px-2 py-0.5 border border-black rounded text-[11px] font-mono font-black {{ $journal->attendance_percentage >= 80 ? 'bg-[#D3F9D8] text-emerald-950' : 'bg-[#FFE3E3] text-rose-950' }}">
                                {{ number_format($journal->attendance_percentage, 1) }}%
                            </span>
                        </td>
                        <td class="p-3 border-r border-black/20 text-slate-700">
                            @if($journal->teaching_problem)
                                <div class="line-clamp-2 text-rose-900 font-medium italic">
                                    {{ $journal->teaching_problem }}
                                </div>
                            @else
                                <span class="text-slate-400 font-bold">-</span>
                            @endif
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('admin.teaching-journals.show', $journal) }}" 
                                   class="p-1.5 bg-[#5294FF] hover:bg-blue-600 text-white border border-black rounded shadow-[1px_1px_0px_0px_#000] cursor-pointer"
                                   title="Lihat Detail Lengkap">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('admin.teaching-journals.destroy', $journal) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan jurnal KBM ini?');"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="p-1.5 bg-rose-400 hover:bg-rose-500 text-black border border-black rounded shadow-[1px_1px_0px_0px_#000] cursor-pointer"
                                            title="Hapus Jurnal">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="p-8 text-center text-slate-500">
                            <div class="text-3xl mb-2">📋</div>
                            <div class="font-bold text-sm text-black">Belum Ada Data Jurnal Mengajar</div>
                            <p class="text-xs text-slate-500 mt-1">
                                Data jurnal harian yang dibuat oleh guru akan muncul di halaman monitoring ini.
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($journals->hasPages())
        <div class="p-4 border-t-2 border-black bg-slate-50">
            {{ $journals->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
