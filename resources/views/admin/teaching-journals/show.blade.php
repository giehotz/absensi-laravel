@extends('layouts.admin')

@section('title', 'Detail Jurnal Mengajar Guru')
@section('page-title', 'Detail Jurnal Mengajar')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-12">
    <!-- Header Navigasi -->
    <div class="flex items-center justify-between bg-white border-2 border-black p-4 rounded-lg shadow-[4px_4px_0px_0px_#000]">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.teaching-journals.index') }}" 
               class="neo-btn bg-slate-100 hover:bg-slate-200 text-black px-3 py-1.5 text-xs font-black flex items-center gap-1.5 shadow-[2px_2px_0px_0px_#000]">
                <span>←</span> Kembali
            </a>
            <div>
                <h2 class="font-heading font-black text-xl text-black">Detail Jurnal Mengajar Guru</h2>
                <p class="text-xs text-slate-600">Informasi lengkap agenda KBM, presensi, dan evaluasi proses belajar.</p>
            </div>
        </div>

        <form action="{{ route('admin.teaching-journals.destroy', $teachingJournal) }}" 
              method="POST" 
              onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan jurnal ini?');"
              class="inline">
            @csrf
            @method('DELETE')
            <button type="submit" 
                    class="neo-btn bg-rose-400 hover:bg-rose-500 text-black px-3.5 py-1.5 text-xs font-black flex items-center gap-1.5 shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <span>Hapus Jurnal</span>
            </button>
        </form>
    </div>

    <!-- Card 1: Identitas Guru & KBM -->
    <div class="bg-white border-2 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000] space-y-4">
        <div class="border-b-2 border-black pb-2 flex items-center justify-between">
            <span class="font-heading font-black text-sm uppercase tracking-wider text-black">
                Identitas Guru & Pelaksanaan KBM
            </span>
            <span class="bg-[#FFD43B] text-black text-[10px] font-black uppercase px-2.5 py-1 border border-black shadow-[1px_1px_0px_0px_#000]">
                Pertemuan Ke-{{ $teachingJournal->meeting_number }}
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            <div>
                <span class="block text-slate-500 font-bold uppercase text-[10px]">Guru Pengampu</span>
                <span class="font-black text-black text-sm">{{ $teachingJournal->teacher?->user?->name ?? '-' }}</span>
                <div class="font-mono text-[11px] text-slate-600 mt-0.5">NIP. {{ $teachingJournal->teacher?->nip ?? '-' }}</div>
            </div>
            <div>
                <span class="block text-slate-500 font-bold uppercase text-[10px]">Mata Pelajaran & Kelas</span>
                <span class="font-black text-black text-sm">{{ $teachingJournal->subject?->name ?? '-' }}</span>
                <div class="mt-0.5">
                    <span class="inline-block bg-slate-100 font-bold px-2 py-0.5 border border-black rounded shadow-[1px_1px_0px_0px_#000]">
                        Kelas {{ $teachingJournal->schoolClass?->name ?? '-' }}
                    </span>
                </div>
            </div>
            <div>
                <span class="block text-slate-500 font-bold uppercase text-[10px]">Hari & Tanggal</span>
                <span class="font-black text-black text-sm">{{ $teachingJournal->day_name }}</span>
                <div class="font-mono text-[11px] text-slate-600 mt-0.5">{{ $teachingJournal->formatted_date_long }}</div>
            </div>
            <div>
                <span class="block text-slate-500 font-bold uppercase text-[10px]">Visibilitas Siswa</span>
                @if($teachingJournal->is_shared_with_students)
                    <span class="inline-block mt-1 bg-[#D3F9D8] text-emerald-950 border border-black text-xs font-black px-2.5 py-1 rounded shadow-[1px_1px_0px_0px_#000]">
                        ✓ Ditampilkan ke Siswa
                    </span>
                @else
                    <span class="inline-block mt-1 bg-slate-100 text-slate-700 border border-black text-xs font-black px-2.5 py-1 rounded shadow-[1px_1px_0px_0px_#000]">
                        🔒 Hanya Guru & Admin
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Card 2: Statistik Presensi Siswa -->
    <div class="bg-[#D3F9D8] border-3 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000] space-y-3">
        <div class="flex items-center justify-between">
            <span class="font-heading font-black text-sm text-emerald-950">Statistik Kehadiran Siswa Kelas</span>
            <span class="text-[10px] font-black uppercase tracking-wider bg-white px-2 py-0.5 border border-black rounded shadow-[1px_1px_0px_0px_#000]">
                Data Terverifikasi
            </span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-6 gap-2.5 pt-1">
            <div class="bg-white border-2 border-black p-2.5 rounded text-center shadow-[1px_1px_0px_0px_#000]">
                <div class="text-[10px] font-black uppercase text-slate-500">Total Siswa</div>
                <div class="font-mono font-black text-lg text-black mt-0.5">{{ $teachingJournal->total_students }}</div>
            </div>
            <div class="bg-white border-2 border-black p-2.5 rounded text-center shadow-[1px_1px_0px_0px_#000]">
                <div class="text-[10px] font-black uppercase text-emerald-700">Hadir (H)</div>
                <div class="font-mono font-black text-lg text-emerald-700 mt-0.5">
                    {{ $teachingJournal->count_hadir }}
                    @if($teachingJournal->count_terlambat > 0)
                        <span class="text-xs text-amber-700 font-bold">(+{{ $teachingJournal->count_terlambat }})</span>
                    @endif
                </div>
            </div>
            <div class="bg-white border-2 border-black p-2.5 rounded text-center shadow-[1px_1px_0px_0px_#000]">
                <div class="text-[10px] font-black uppercase text-amber-700">Sakit (S)</div>
                <div class="font-mono font-black text-lg text-amber-700 mt-0.5">{{ $teachingJournal->count_sakit }}</div>
            </div>
            <div class="bg-white border-2 border-black p-2.5 rounded text-center shadow-[1px_1px_0px_0px_#000]">
                <div class="text-[10px] font-black uppercase text-blue-700">Izin (I)</div>
                <div class="font-mono font-black text-lg text-blue-700 mt-0.5">{{ $teachingJournal->count_izin }}</div>
            </div>
            <div class="bg-white border-2 border-black p-2.5 rounded text-center shadow-[1px_1px_0px_0px_#000]">
                <div class="text-[10px] font-black uppercase text-rose-700">Alpa (A)</div>
                <div class="font-mono font-black text-lg text-rose-700 mt-0.5">{{ $teachingJournal->count_alpa }}</div>
            </div>
            <div class="bg-emerald-100 border-2 border-black p-2.5 rounded text-center shadow-[1px_1px_0px_0px_#000]">
                <div class="text-[10px] font-black uppercase text-emerald-950">% Kehadiran</div>
                <div class="font-mono font-black text-lg text-emerald-950 mt-0.5">{{ number_format($teachingJournal->attendance_percentage, 1) }}%</div>
            </div>
        </div>
    </div>

    <!-- Card 3: Rincian Materi, KBM & Evaluasi -->
    <div class="bg-white border-2 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000] space-y-5">
        <div>
            <div class="text-[11px] font-black uppercase tracking-wider text-slate-500 mb-1">Tujuan Pembelajaran</div>
            <div class="bg-slate-50 border-2 border-black p-3.5 rounded text-xs text-black leading-relaxed font-medium">
                {{ $teachingJournal->learning_objective }}
            </div>
        </div>

        <div>
            <div class="text-[11px] font-black uppercase tracking-wider text-slate-500 mb-1">Kegiatan Belajar Mengajar (KBM)</div>
            <div class="bg-slate-50 border-2 border-black p-3.5 rounded text-xs text-black leading-relaxed whitespace-pre-line font-medium">
                {{ $teachingJournal->teaching_activity }}
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-1">
                <span class="text-[11px] font-black uppercase tracking-wider text-rose-800">Permasalahan Dalam Proses KBM</span>
                <span class="text-[9px] font-bold text-amber-900 bg-amber-100 px-2 py-0.2 border border-amber-300 rounded">
                    🔒 Catatan Internal Guru & Admin
                </span>
            </div>
            <div class="bg-rose-50/70 border-2 border-rose-300 p-3.5 rounded text-xs text-rose-950 leading-relaxed font-medium">
                {{ $teachingJournal->teaching_problem ?: 'Tidak ada catatan kendala atau permasalahan khusus.' }}
            </div>
        </div>
    </div>
</div>
@endsection
