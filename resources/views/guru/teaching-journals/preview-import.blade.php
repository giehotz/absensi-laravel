@extends('layouts.guru')

@section('title', 'Review & Publikasikan Jurnal Excel')
@section('page-title', 'Review Impor Jurnal KBM')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-16">
    <!-- Header Navigasi -->
    <div class="flex items-center justify-between bg-white border-2 border-black p-4 rounded-lg shadow-[4px_4px_0px_0px_#000]">
        <div class="flex items-center gap-3">
            <a href="{{ route('guru.teaching-journals.index') }}" 
               class="neo-btn bg-slate-100 hover:bg-slate-200 text-black px-3 py-1.5 text-xs font-black flex items-center gap-1.5 shadow-[2px_2px_0px_0px_#000]">
                <span>←</span> Batal
            </a>
            <div>
                <h2 class="font-heading font-black text-xl text-black">Pratinjau & Edit Data Jurnal Excel</h2>
                <p class="text-xs text-slate-600">Periksa dan sesuaikan data KBM di bawah sebelum menekan tombol Publish.</p>
            </div>
        </div>
        <span class="bg-[#20C997] text-black text-xs font-black px-3 py-1 border border-black shadow-[1px_1px_0px_0px_#000]">
            {{ count($rows) }} Baris Terbaca
        </span>
    </div>

    <!-- Alert Ringkasan Status Presensi -->
    @php
        $readyCount = collect($rows)->where('has_attendance', true)->where('is_valid', true)->count();
        $missingCount = count($rows) - $readyCount;
    @endphp
    <div class="bg-white border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000] flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-3">
            <span class="text-2xl">📋</span>
            <div>
                <span class="font-bold text-black">Status Verifikasi Prasyarat:</span>
                <span class="text-emerald-800 font-black ml-1.5 bg-[#D3F9D8] px-2 py-0.5 border border-black rounded">
                    ✓ {{ $readyCount }} Siap Dipublish
                </span>
                @if($missingCount > 0)
                <span class="text-rose-800 font-black ml-1.5 bg-[#FFE3E3] px-2 py-0.5 border border-black rounded">
                    ⚠️ {{ $missingCount }} Presensi Belum Diisi / Data Belum Lengkap
                </span>
                @endif
            </div>
        </div>
        <div class="text-[11px] text-slate-500 font-bold">
            Catatan: Baris yang presensinya belum diisi akan otomatis dilewati saat dipublish.
        </div>
    </div>

    <!-- Form Review & Edit Sebelum Publish -->
    <form action="{{ route('guru.teaching-journals.import.publish') }}" method="POST" class="space-y-5">
        @csrf

        @foreach($rows as $index => $row)
        <div class="bg-white border-2 border-black rounded-lg shadow-[4px_4px_0px_0px_#000] overflow-hidden {{ $row['has_attendance'] ? 'border-l-8 border-l-[#20C997]' : 'border-l-8 border-l-[#FF6B6B]' }}">
            <!-- Row Header -->
            <div class="p-3.5 bg-slate-50 border-b-2 border-black flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-mono font-black bg-black text-white px-2 py-0.5 rounded text-[11px]">
                        #Baris {{ $row['row_index'] }}
                    </span>
                    <span class="font-black text-black text-sm">
                        {{ $row['class_name'] }} • {{ $row['subject_name'] }}
                    </span>
                    @if(!empty($row['time_range']))
                        <span class="text-[11px] font-mono text-slate-600 bg-slate-200 px-2 py-0.5 rounded border border-black">
                            ⏰ {{ $row['time_range'] }}
                        </span>
                    @endif
                </div>

                <div>
                    @if($row['has_attendance'])
                        <span class="bg-[#D3F9D8] text-emerald-950 font-black px-2.5 py-1 border border-black rounded text-[11px] shadow-[1px_1px_0px_0px_#000]">
                            ✓ Presensi Siswa Terisi
                        </span>
                    @else
                        <div class="flex items-center gap-2">
                            <span class="bg-[#FFE3E3] text-rose-950 font-black px-2.5 py-1 border border-black rounded text-[11px] shadow-[1px_1px_0px_0px_#000]">
                                ⚠️ Presensi Belum Diisi
                            </span>
                            @if(!empty($row['school_class_id']) && !empty($row['date']))
                                <a href="{{ route('guru.attendance.manual', ['school_class_id' => $row['school_class_id'], 'date' => $row['date']]) }}" 
                                   target="_blank" 
                                   class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black text-[10px] font-black px-2 py-1 shadow-[1px_1px_0px_0px_#000]">
                                    Isi Presensi →
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Row Body Inputs -->
            <div class="p-4 space-y-4">
                <input type="hidden" name="items[{{ $index }}][schedule_id]" value="{{ $row['schedule_id'] }}">
                <input type="hidden" name="items[{{ $index }}][school_class_id]" value="{{ $row['school_class_id'] }}">
                <input type="hidden" name="items[{{ $index }}][subject_id]" value="{{ $row['subject_id'] }}">

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-black text-black uppercase tracking-wider mb-1">Tanggal KBM</label>
                        <input type="date" 
                               name="items[{{ $index }}][date]" 
                               value="{{ $row['date'] }}" 
                               required 
                               class="w-full neo-input px-3 py-1.5 text-xs font-bold bg-slate-50 focus:bg-white">
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-black uppercase tracking-wider mb-1">Pertemuan Ke-</label>
                        <input type="number" 
                               name="items[{{ $index }}][meeting_number]" 
                               value="{{ $row['meeting_number'] }}" 
                               min="1" 
                               required 
                               class="w-full neo-input px-3 py-1.5 text-xs font-mono font-black bg-slate-50 focus:bg-white">
                    </div>
                    <div class="flex items-end pb-1.5">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-black select-none">
                            <input type="checkbox" 
                                   name="items[{{ $index }}][is_shared_with_students]" 
                                   value="1" 
                                   class="w-4 h-4 rounded border-2 border-black text-[#20C997] focus:ring-0">
                            <span>Tampilkan ke Siswa</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-black text-black uppercase tracking-wider mb-1">Tujuan Pembelajaran</label>
                    <textarea name="items[{{ $index }}][learning_objective]" 
                              rows="2" 
                              required 
                              class="w-full neo-input px-3 py-2 text-xs font-medium bg-slate-50 focus:bg-white">{{ $row['learning_objective'] }}</textarea>
                </div>

                <div>
                    <label class="block text-[11px] font-black text-black uppercase tracking-wider mb-1">Kegiatan Belajar Mengajar (KBM)</label>
                    <textarea name="items[{{ $index }}][teaching_activity]" 
                              rows="2" 
                              required 
                              class="w-full neo-input px-3 py-2 text-xs font-medium bg-slate-50 focus:bg-white">{{ $row['teaching_activity'] }}</textarea>
                </div>

                <div>
                    <label class="block text-[11px] font-black text-slate-700 uppercase tracking-wider mb-1">
                        Permasalahan Dalam KBM (Opsional - Catatan Internal)
                    </label>
                    <input type="text" 
                           name="items[{{ $index }}][teaching_problem]" 
                           value="{{ $row['teaching_problem'] }}" 
                           placeholder="Catatan kendala khusus jika ada..." 
                           class="w-full neo-input px-3 py-1.5 text-xs font-medium bg-slate-50 focus:bg-white">
                </div>
            </div>
        </div>
        @endforeach

        <!-- Action Bar Bawah -->
        <div class="sticky bottom-4 bg-white border-3 border-black p-4 rounded-lg shadow-[6px_6px_0px_0px_#000] flex items-center justify-between gap-4 z-20">
            <a href="{{ route('guru.teaching-journals.index') }}" 
               class="neo-btn bg-slate-100 hover:bg-slate-200 text-black px-4 py-2.5 text-xs font-bold shadow-[2px_2px_0px_0px_#000]">
                Batal Impor
            </a>

            <button type="submit" 
                    class="neo-btn bg-[#20C997] hover:bg-[#1bb386] text-black px-6 py-2.5 text-xs font-black flex items-center gap-2 shadow-[3px_3px_0px_0px_#000] cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                <span>🚀 Publikasikan Jurnal Sekarang</span>
            </button>
        </div>
    </form>
</div>
@endsection
