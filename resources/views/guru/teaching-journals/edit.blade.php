@extends('layouts.guru')

@section('title', 'Edit Jurnal Harian Mengajar')
@section('page-title', 'Edit Jurnal Mengajar')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-12">
    <!-- Header Back Navigation -->
    <div class="flex items-center justify-between bg-white border-2 border-black p-4 rounded-lg shadow-[4px_4px_0px_0px_#000]">
        <div class="flex items-center gap-3">
            <a href="{{ route('guru.teaching-journals.index') }}" 
               class="neo-btn bg-slate-100 hover:bg-slate-200 text-black px-3 py-1.5 text-xs font-black flex items-center gap-1.5 shadow-[2px_2px_0px_0px_#000]">
                <span>←</span> Kembali
            </a>
            <div>
                <h2 class="font-heading font-black text-xl text-black">Edit Jurnal Mengajar</h2>
                <p class="text-xs text-slate-600">Perbarui catatan aktivitas pembelajaran dan evaluasi KBM.</p>
            </div>
        </div>
        <span class="bg-[#20C997] text-black text-[10px] font-black uppercase px-2.5 py-1 border border-black shadow-[1px_1px_0px_0px_#000]">
            Pertemuan Ke-{{ $teachingJournal->meeting_number }}
        </span>
    </div>

    <!-- Form Edit Jurnal -->
    <form action="{{ route('guru.teaching-journals.update', $teachingJournal) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Card 1: Identitas Kelas & Tanggal -->
        <div class="bg-white border-2 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000] space-y-4">
            <div class="border-b-2 border-black pb-2 flex items-center justify-between">
                <span class="font-heading font-black text-sm uppercase tracking-wider text-black">
                    1. Identitas Kelas & Tanggal KBM
                </span>
                <span class="text-[11px] font-bold text-slate-500">{{ $teachingJournal->day_name }}</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-black uppercase tracking-wider mb-1.5">
                        Tanggal KBM <span class="text-rose-600">*</span>
                    </label>
                    <input type="date" 
                           name="date" 
                           value="{{ old('date', $teachingJournal->date->format('Y-m-d')) }}" 
                           required 
                           class="w-full neo-input px-3 py-2 text-xs bg-slate-50 font-bold focus:bg-white @error('date') border-rose-500 @enderror">
                </div>

                <div>
                    <label class="block text-xs font-bold text-black uppercase tracking-wider mb-1.5">
                        Kelas <span class="text-rose-600">*</span>
                    </label>
                    <select name="school_class_id" required class="w-full neo-input px-3 py-2 text-xs bg-slate-50 font-bold focus:bg-white @error('school_class_id') border-rose-500 @enderror">
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ (string)old('school_class_id', $teachingJournal->school_class_id) === (string)$c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-black uppercase tracking-wider mb-1.5">
                        Mata Pelajaran <span class="text-rose-600">*</span>
                    </label>
                    <select name="subject_id" required class="w-full neo-input px-3 py-2 text-xs bg-slate-50 font-bold focus:bg-white @error('subject_id') border-rose-500 @enderror">
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}" {{ (string)old('subject_id', $teachingJournal->subject_id) === (string)$s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Card 2: Ringkasan Presensi Terkunci -->
        <div class="bg-[#D3F9D8] border-3 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000] space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-full bg-[#20C997] border-2 border-black flex items-center justify-center font-black text-black shrink-0 text-sm">✓</span>
                    <span class="font-heading font-black text-sm text-emerald-950">Statistik Presensi Kelas (Terkunci Sesuai Data Sistem)</span>
                </div>
                <span class="text-[10px] font-black uppercase tracking-wider bg-white px-2 py-0.5 border border-black rounded shadow-[1px_1px_0px_0px_#000]">
                    Auto Presensi
                </span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-6 gap-2.5 pt-1">
                <div class="bg-white border-2 border-black p-2 rounded text-center shadow-[1px_1px_0px_0px_#000]">
                    <div class="text-[10px] font-black uppercase text-slate-500">Total Siswa</div>
                    <div class="font-mono font-black text-base text-black mt-0.5">{{ $teachingJournal->total_students }}</div>
                </div>
                <div class="bg-white border-2 border-black p-2 rounded text-center shadow-[1px_1px_0px_0px_#000]">
                    <div class="text-[10px] font-black uppercase text-emerald-700">Hadir (H)</div>
                    <div class="font-mono font-black text-base text-emerald-700 mt-0.5">
                        {{ $teachingJournal->count_hadir }}
                        @if($teachingJournal->count_terlambat > 0)
                            <span class="text-xs text-amber-700 font-bold">(+{{ $teachingJournal->count_terlambat }})</span>
                        @endif
                    </div>
                </div>
                <div class="bg-white border-2 border-black p-2 rounded text-center shadow-[1px_1px_0px_0px_#000]">
                    <div class="text-[10px] font-black uppercase text-amber-700">Sakit (S)</div>
                    <div class="font-mono font-black text-base text-amber-700 mt-0.5">{{ $teachingJournal->count_sakit }}</div>
                </div>
                <div class="bg-white border-2 border-black p-2 rounded text-center shadow-[1px_1px_0px_0px_#000]">
                    <div class="text-[10px] font-black uppercase text-blue-700">Izin (I)</div>
                    <div class="font-mono font-black text-base text-blue-700 mt-0.5">{{ $teachingJournal->count_izin }}</div>
                </div>
                <div class="bg-white border-2 border-black p-2 rounded text-center shadow-[1px_1px_0px_0px_#000]">
                    <div class="text-[10px] font-black uppercase text-rose-700">Alpa (A)</div>
                    <div class="font-mono font-black text-base text-rose-700 mt-0.5">{{ $teachingJournal->count_alpa }}</div>
                </div>
                <div class="bg-emerald-100 border-2 border-black p-2 rounded text-center shadow-[1px_1px_0px_0px_#000]">
                    <div class="text-[10px] font-black uppercase text-emerald-950">% Hadir</div>
                    <div class="font-mono font-black text-base text-emerald-950 mt-0.5">{{ number_format($teachingJournal->attendance_percentage, 1) }}%</div>
                </div>
            </div>
        </div>

        <!-- Card 3: Agenda KBM & Evaluasi -->
        <div class="bg-white border-2 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000] space-y-4">
            <div class="border-b-2 border-black pb-2 flex items-center justify-between">
                <span class="font-heading font-black text-sm uppercase tracking-wider text-black">
                    2. Rincian Agenda & Evaluasi KBM
                </span>
            </div>

            <!-- Pertemuan Ke- -->
            <div class="w-full sm:w-1/3">
                <label class="block text-xs font-bold text-black uppercase tracking-wider mb-1.5">
                    Pertemuan Ke- <span class="text-rose-600">*</span>
                </label>
                <input type="number" 
                       name="meeting_number" 
                       value="{{ old('meeting_number', $teachingJournal->meeting_number) }}" 
                       min="1" 
                       required 
                       class="w-full neo-input px-3 py-2 text-sm font-mono font-black bg-slate-50 focus:bg-white @error('meeting_number') border-rose-500 @enderror">
            </div>

            <!-- Tujuan Pembelajaran -->
            <div>
                <label class="block text-xs font-bold text-black uppercase tracking-wider mb-1.5">
                    Tujuan Pembelajaran <span class="text-rose-600">*</span>
                </label>
                <textarea name="learning_objective" 
                          rows="3" 
                          required 
                          class="w-full neo-input px-3 py-2.5 text-xs font-medium bg-slate-50 focus:bg-white @error('learning_objective') border-rose-500 @enderror">{{ old('learning_objective', $teachingJournal->learning_objective) }}</textarea>
            </div>

            <!-- Kegiatan Belajar Mengajar -->
            <div>
                <label class="block text-xs font-bold text-black uppercase tracking-wider mb-1.5">
                    Kegiatan Belajar Mengajar (KBM) <span class="text-rose-600">*</span>
                </label>
                <textarea name="teaching_activity" 
                          rows="4" 
                          required 
                          class="w-full neo-input px-3 py-2.5 text-xs font-medium bg-slate-50 focus:bg-white @error('teaching_activity') border-rose-500 @enderror">{{ old('teaching_activity', $teachingJournal->teaching_activity) }}</textarea>
            </div>

            <!-- Permasalahan Dalam Proses KBM (Internal) -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-bold text-black uppercase tracking-wider">
                        Permasalahan Dalam Proses KBM (Opsional)
                    </label>
                    <span class="text-[10px] font-bold text-amber-900 bg-amber-100 px-2 py-0.5 border border-amber-300 rounded">
                        🔒 Internal (Hanya Anda & Admin)
                    </span>
                </div>
                <textarea name="teaching_problem" 
                          rows="3" 
                          class="w-full neo-input px-3 py-2.5 text-xs font-medium bg-slate-50 focus:bg-white @error('teaching_problem') border-rose-500 @enderror">{{ old('teaching_problem', $teachingJournal->teaching_problem) }}</textarea>
            </div>

            <!-- Toggle Switch Visibilitas Siswa -->
            <div class="pt-3 border-t-2 border-black/10 flex items-center justify-between">
                <div>
                    <div class="font-heading font-black text-xs text-black">Tampilkan Jurnal Ini ke Siswa di Kelas Terkait?</div>
                    <p class="text-[11px] text-slate-500">Siswa dapat melihat ringkasan materi KBM dan tujuan pembelajaran (permasalahan internal tetap disembunyikan).</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_shared_with_students" value="1" {{ old('is_shared_with_students', $teachingJournal->is_shared_with_students) ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none border-2 border-black rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-black after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-2 after:border-black after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#20C997]"></div>
                </label>
            </div>
        </div>

        <!-- Tombol Aksi Submit -->
        <div class="flex items-center justify-between gap-4">
            <a href="{{ route('guru.teaching-journals.index') }}" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black px-4 py-2.5 text-xs font-bold shadow-[2px_2px_0px_0px_#000]">
                Batal
            </a>

            <button type="submit" 
                    class="neo-btn bg-[#20C997] hover:bg-[#1bb386] text-black px-6 py-2.5 text-xs font-black flex items-center gap-2 shadow-[3px_3px_0px_0px_#000] cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                <span>Perbarui Jurnal Mengajar</span>
            </button>
        </div>
    </form>
</div>
@endsection
