@extends('layouts.guru')

@section('title', 'Analisis Minggu Efektif')
@section('page-title', 'Analisis Minggu Efektif & Alokasi Waktu')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'ganjil', showPrintMenu: false }">
    <!-- Header Card -->
    <div class="bg-[#FFF3BF] neo-box-lg p-5 sm:p-7 text-black relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border-2 border-black shadow-[4px_4px_0px_0px_#000]">
        <div class="space-y-1.5 z-10">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="neo-badge bg-[#5294FF] text-white font-black text-xs px-2.5 py-0.5 border border-black shadow-[1px_1px_0px_0px_#000]">
                    PORTAL GURU
                </span>
                <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border border-black shadow-[1px_1px_0px_0px_#000]">
                    TA {{ $selectedYear }}
                </span>
                @if($subject)
                    <span class="bg-[#D0EBFF] text-blue-900 text-xs font-black px-2.5 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000]">
                        Mapel: {{ $subject->name }}
                    </span>
                @endif
                @if($selectedClass)
                    <span class="bg-[#EBFBEE] text-emerald-900 text-xs font-black px-2.5 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000]">
                        Kelas: {{ $selectedClass->name }}
                    </span>
                @endif
            </div>
            <h1 class="font-heading text-xl sm:text-2xl font-black tracking-tight text-black uppercase">
                Analisis Minggu Efektif & Alokasi Waktu
            </h1>
            <p class="text-xs sm:text-sm font-semibold text-slate-800 max-w-2xl">
                Rencana perhitungan pekan efektif dan alokasi jam tatap muka untuk penyusunan Program Tahunan (Prota) dan Program Semester (Promes).
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap z-10">
            <!-- Cetak Dropdown Button -->
            <div class="relative" @click.away="showPrintMenu = false">
                <button type="button" @click="showPrintMenu = !showPrintMenu"
                        class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-4 py-2 text-xs uppercase flex items-center gap-2 font-heading font-black border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                    <span>🖨️</span>
                    <span>Cetak Dokumen Resmi</span>
                    <span class="text-[10px]">▼</span>
                </button>

                <div x-show="showPrintMenu" x-cloak
                     class="absolute right-0 mt-2 w-56 bg-white border-2 border-black shadow-[4px_4px_0px_0px_#000] z-50 py-1 text-xs font-bold divide-y divide-black/10">
                    <a href="{{ route('guru.effective-weeks.print', array_merge(request()->query(), ['print_semester' => 'all'])) }}" target="_blank"
                       class="block px-4 py-2 hover:bg-amber-100 text-slate-900 flex items-center justify-between">
                        <span>📄 Cetak Semua (Gasal & Genap)</span>
                    </a>
                    <a href="{{ route('guru.effective-weeks.print', array_merge(request()->query(), ['print_semester' => 'ganjil'])) }}" target="_blank"
                       class="block px-4 py-2 hover:bg-amber-100 text-slate-900 flex items-center justify-between">
                        <span>📘 Cetak Semester Gasal (1)</span>
                    </a>
                    <a href="{{ route('guru.effective-weeks.print', array_merge(request()->query(), ['print_semester' => 'genap'])) }}" target="_blank"
                       class="block px-4 py-2 hover:bg-amber-100 text-slate-900 flex items-center justify-between">
                        <span>📗 Cetak Semester Genap (2)</span>
                    </a>
                </div>
            </div>

            <a href="{{ route('guru.calendar.index') }}"
               class="neo-btn bg-white hover:bg-slate-100 text-slate-900 px-3.5 py-2 text-xs uppercase flex items-center gap-1.5 font-heading font-black border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                <span>📅</span>
                <span>Kalender</span>
            </a>
        </div>
    </div>

    <!-- Parameter & Filter Panel -->
    <div class="bg-white neo-box p-5 border-2 border-black shadow-[4px_4px_0px_0px_#000]">
        <form method="GET" action="{{ route('guru.effective-weeks.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Tahun Ajaran -->
                <div>
                    <label class="block text-xs font-black uppercase font-heading text-slate-700 mb-1">Tahun Ajaran</label>
                    <select name="academic_year_name" class="w-full neo-input text-xs font-black py-2 px-3 bg-[#FFF9DB] border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                        @foreach($availableYears as $year)
                            <option value="{{ $year }}" {{ $selectedYear === $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Mata Pelajaran -->
                <div>
                    <label class="block text-xs font-black uppercase font-heading text-slate-700 mb-1">Mata Pelajaran Diampu</label>
                    <select name="subject_id" class="w-full neo-input text-xs font-bold py-2 px-3 bg-white border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                        <option value="">-- Semua / Umum --</option>
                        @foreach($subjects as $subj)
                            <option value="{{ $subj->id }}" {{ ($subject?->id == $subj->id) ? 'selected' : '' }}>
                                {{ $subj->name }} ({{ $subj->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Kelas / Rombel -->
                <div>
                    <label class="block text-xs font-black uppercase font-heading text-slate-700 mb-1">Kelas Sasaran</label>
                    <select name="school_class_id" class="w-full neo-input text-xs font-bold py-2 px-3 bg-white border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                        <option value="">-- Semua Kelas --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ ($selectedClass?->id == $c->id) ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Baris 2: Variabel Jam & Hari Sekolah -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 pt-2 border-t-2 border-black/10">
                <!-- Hari Sekolah -->
                <div>
                    <label class="block text-[11px] font-black uppercase font-heading text-slate-700 mb-1">Hari Sekolah</label>
                    <select name="school_days" class="w-full neo-input text-xs font-bold py-2 px-2.5 bg-white border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                        <option value="5" {{ $params['school_days'] == 5 ? 'selected' : '' }}>5 Hari (Senin - Jumat)</option>
                        <option value="6" {{ $params['school_days'] == 6 ? 'selected' : '' }}>6 Hari (Senin - Sabtu)</option>
                    </select>
                </div>

                <!-- Syarat Hari Efektif -->
                <div>
                    <label class="block text-[11px] font-black uppercase font-heading text-slate-700 mb-1">Min Hari Aktif/Mgg</label>
                    <select name="min_effective_days" class="w-full neo-input text-xs font-bold py-2 px-2.5 bg-white border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                        <option value="3" {{ $params['min_effective_days'] == 3 ? 'selected' : '' }}>3 Hari (Standar)</option>
                        <option value="4" {{ $params['min_effective_days'] == 4 ? 'selected' : '' }}>4 Hari</option>
                        <option value="2" {{ $params['min_effective_days'] == 2 ? 'selected' : '' }}>2 Hari</option>
                    </select>
                </div>

                <!-- Jam Pelajaran (JP) / Minggu -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-[11px] font-black uppercase font-heading text-slate-700">JP / Minggu</label>
                        @if($params['detected_jp'])
                            <span class="text-[9px] bg-green-100 text-green-800 font-bold px-1 rounded border border-green-400" title="Terdeteksi otomatis dari jadwal KBM Anda">
                                Auto: {{ $params['detected_jp'] }} JP
                            </span>
                        @endif
                    </div>
                    <input type="number" name="jp_per_week" value="{{ $params['jp_per_week'] }}" min="1" max="50" required
                           class="w-full neo-input text-xs font-black py-2 px-3 bg-white border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                </div>

                <!-- Alokasi Jam Ujian -->
                <div>
                    <label class="block text-[11px] font-black uppercase font-heading text-slate-700 mb-1">Alokasi Ujian (JP)</label>
                    <input type="number" name="exam_hours" value="{{ $params['exam_hours'] }}" min="0" max="100"
                           class="w-full neo-input text-xs font-black py-2 px-3 bg-white border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                </div>

                <!-- Alokasi Jam Cadangan -->
                <div>
                    <label class="block text-[11px] font-black uppercase font-heading text-slate-700 mb-1">Alokasi Cadangan (JP)</label>
                    <input type="number" name="reserve_hours" value="{{ $params['reserve_hours'] }}" min="0" max="100"
                           class="w-full neo-input text-xs font-black py-2 px-3 bg-white border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                </div>
            </div>

            <!-- Action Toolbar Form -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('guru.effective-weeks.index') }}" 
                   class="neo-btn bg-slate-200 hover:bg-slate-300 text-slate-800 px-4 py-2 text-xs uppercase font-heading font-black border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                    Reset
                </a>
                <button type="submit" 
                        class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-5 py-2 text-xs uppercase flex items-center gap-1.5 font-heading font-black border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                    <span>🔄</span>
                    <span>Hitung Analisis</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Tab Navigation (Gasal vs Genap) -->
    <div class="flex items-center gap-3 border-b-2 border-black pb-1">
        <button type="button" @click="activeTab = 'ganjil'"
                :class="activeTab === 'ganjil' ? 'bg-[#5294FF] text-white shadow-[3px_3px_0px_0px_#000] -translate-y-0.5' : 'bg-white text-black hover:bg-slate-100'"
                class="neo-btn px-5 py-2.5 text-xs uppercase font-heading font-black border-2 border-black transition-all flex items-center gap-2">
            <span>📘</span>
            <span>Semester Gasal (1)</span>
            <span class="ml-1 text-[10px] bg-black/20 text-white px-2 py-0.5 rounded-full">
                {{ $semesters['ganjil']['analysis']['total_effective_weeks'] }} Pekan Efektif
            </span>
        </button>

        <button type="button" @click="activeTab = 'genap'"
                :class="activeTab === 'genap' ? 'bg-[#20C997] text-black shadow-[3px_3px_0px_0px_#000] -translate-y-0.5' : 'bg-white text-black hover:bg-slate-100'"
                class="neo-btn px-5 py-2.5 text-xs uppercase font-heading font-black border-2 border-black transition-all flex items-center gap-2">
            <span>📗</span>
            <span>Semester Genap (2)</span>
            <span class="ml-1 text-[10px] bg-black/10 text-black px-2 py-0.5 rounded-full">
                {{ $semesters['genap']['analysis']['total_effective_weeks'] }} Pekan Efektif
            </span>
        </button>
    </div>

    <!-- Konten Tab Gasal -->
    <div x-show="activeTab === 'ganjil'" x-cloak>
        @include('admin.effective-weeks._semester', [
            'semesterKey' => 'ganjil',
            'semesterData' => $semesters['ganjil'],
            'params' => $params,
        ])
    </div>

    <!-- Konten Tab Genap -->
    <div x-show="activeTab === 'genap'" x-cloak>
        @include('admin.effective-weeks._semester', [
            'semesterKey' => 'genap',
            'semesterData' => $semesters['genap'],
            'params' => $params,
        ])
    </div>
</div>
@endsection
