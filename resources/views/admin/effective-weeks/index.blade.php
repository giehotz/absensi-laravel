@extends('layouts.admin')

@section('title', 'Analisis Minggu Efektif')
@section('page-title', 'Analisis Minggu Efektif & Alokasi Waktu')

@php
    $currentTab = request('tab', 'ganjil');
    if (!in_array($currentTab, ['ganjil', 'genap'])) {
        $currentTab = 'ganjil';
    }
@endphp

@section('content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 bg-white neo-box p-5 border-2 border-black shadow-[4px_4px_0px_0px_#000] relative z-20">
        <div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <span class="text-2xl">📅</span>
                <h1 class="font-heading font-black text-xl text-black">
                    Analisis Minggu Efektif
                </h1>
                <span class="bg-[#FFD43B] text-black text-xs font-black px-2.5 py-0.5 rounded border border-black uppercase shadow-[1px_1px_0px_0px_#000]">
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
            <p class="text-xs font-semibold text-slate-600 mt-1">
                Perhitungan otomatis minggu efektif KBM, minggu tidak efektif, dan distribusi jam pelajaran kurikulum madrasah secara real-time.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <!-- Cetak Dropdown Button -->
            <div class="relative">
                <button type="button" id="printDropdownBtn" onclick="togglePrintDropdown(event)"
                        class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-4 py-2.5 text-xs uppercase flex items-center gap-2 font-heading font-black border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                    <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Cetak Dokumen Resmi</span>
                    <svg id="printDropdownChevron" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Dropdown Popover Menu -->
                <div id="printDropdownMenu" 
                     class="hidden absolute right-0 mt-2 w-64 bg-white border-2 border-black shadow-[4px_4px_0px_0px_#000] z-50 py-1.5 text-xs font-bold divide-y divide-black/10 rounded-sm">
                    <a href="{{ route('admin.effective-weeks.print', array_merge(request()->query(), ['print_semester' => 'all'])) }}" target="_blank"
                       class="px-4 py-2.5 hover:bg-[#FFF9DB] text-slate-900 flex items-center gap-2.5 transition-colors">
                        <span class="w-3 h-3 rounded-full bg-slate-800 border border-black shrink-0"></span>
                        <div class="flex flex-col">
                            <span class="font-black text-black">Cetak Semua Semester</span>
                            <span class="text-[10px] text-slate-500 font-medium">Semester Gasal &amp; Genap Lengkap</span>
                        </div>
                    </a>
                    <a href="{{ route('admin.effective-weeks.print', array_merge(request()->query(), ['print_semester' => 'ganjil'])) }}" target="_blank"
                       class="px-4 py-2.5 hover:bg-[#D0EBFF] text-slate-900 flex items-center gap-2.5 transition-colors">
                        <span class="w-3 h-3 rounded-full bg-[#5294FF] border border-black shrink-0"></span>
                        <div class="flex flex-col">
                            <span class="font-black text-blue-950">Cetak Semester Gasal (1)</span>
                            <span class="text-[10px] text-blue-700 font-medium">Juli s/d Desember</span>
                        </div>
                    </a>
                    <a href="{{ route('admin.effective-weeks.print', array_merge(request()->query(), ['print_semester' => 'genap'])) }}" target="_blank"
                       class="px-4 py-2.5 hover:bg-[#D3F9D8] text-slate-900 flex items-center gap-2.5 transition-colors">
                        <span class="w-3 h-3 rounded-full bg-[#20C997] border border-black shrink-0"></span>
                        <div class="flex flex-col">
                            <span class="font-black text-emerald-950">Cetak Semester Genap (2)</span>
                            <span class="text-[10px] text-emerald-700 font-medium">Januari s/d Juni</span>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Link ke Kalender Pendidikan -->
            <a href="{{ route('admin.academic-calendar.index', ['academic_year_name' => $selectedYear]) }}"
               class="neo-btn bg-[#FFF4E6] hover:bg-[#ffe8cc] text-amber-950 px-3.5 py-2.5 text-xs uppercase flex items-center gap-1.5 font-heading font-black border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                <span>📅</span>
                <span>Kalender Pendidikan</span>
            </a>
        </div>
    </div>

    <!-- Parameter & Filter Panel -->
    <div class="bg-white neo-box p-5 border-2 border-black shadow-[4px_4px_0px_0px_#000]">
        <form method="GET" action="{{ route('admin.effective-weeks.index') }}" class="space-y-4">
            <input type="hidden" name="tab" id="activeTabInput" value="{{ $currentTab }}">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Tahun Ajaran -->
                <div>
                    <label class="block text-xs font-black uppercase font-heading text-slate-700 mb-1">Tahun Ajaran</label>
                    <select name="academic_year_name" class="w-full neo-input text-xs font-black py-2.5 px-3 bg-[#FFF9DB] border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                        @foreach($availableYears as $year)
                            <option value="{{ $year }}" {{ $selectedYear === $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Mata Pelajaran -->
                <div>
                    <label class="block text-xs font-black uppercase font-heading text-slate-700 mb-1">Mata Pelajaran</label>
                    <select name="subject_id" class="w-full neo-input text-xs font-bold py-2.5 px-3 bg-white border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                        <option value="">-- Semua / Umum --</option>
                        @foreach($subjects as $subj)
                            <option value="{{ $subj->id }}" {{ ($subject?->id == $subj->id) ? 'selected' : '' }}>
                                {{ $subj->name }} ({{ $subj->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Guru Pengampu -->
                <div>
                    <label class="block text-xs font-black uppercase font-heading text-slate-700 mb-1">Guru Pengampu</label>
                    <select name="teacher_id" class="w-full neo-input text-xs font-bold py-2.5 px-3 bg-white border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                        <option value="">-- Pilih Guru --</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}" {{ ($teacher?->id == $t->id) ? 'selected' : '' }}>
                                {{ $t->user?->name ?? 'Guru #'.$t->id }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Kelas / Rombel -->
                <div>
                    <label class="block text-xs font-black uppercase font-heading text-slate-700 mb-1">Kelas / Rombel</label>
                    <select name="school_class_id" class="w-full neo-input text-xs font-bold py-2.5 px-3 bg-white border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
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
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 pt-3 border-t-2 border-black/10">
                <!-- Hari Sekolah -->
                <div>
                    <label class="block text-[11px] font-black uppercase font-heading text-slate-700 mb-1">Hari Sekolah</label>
                    <select name="school_days" class="w-full neo-input text-xs font-bold py-2.5 px-2.5 bg-white border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                        <option value="5" {{ $params['school_days'] == 5 ? 'selected' : '' }}>5 Hari (Senin - Jumat)</option>
                        <option value="6" {{ $params['school_days'] == 6 ? 'selected' : '' }}>6 Hari (Senin - Sabtu)</option>
                    </select>
                </div>

                <!-- Syarat Hari Efektif -->
                <div>
                    <label class="block text-[11px] font-black uppercase font-heading text-slate-700 mb-1">Min Hari Aktif/Mgg</label>
                    <select name="min_effective_days" class="w-full neo-input text-xs font-bold py-2.5 px-2.5 bg-white border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
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
                            <span class="text-[9px] bg-green-100 text-green-800 font-bold px-1 rounded border border-green-400" title="Terdeteksi otomatis dari jadwal KBM">
                                Auto: {{ $params['detected_jp'] }} JP
                            </span>
                        @endif
                    </div>
                    <input type="number" name="jp_per_week" value="{{ $params['jp_per_week'] }}" min="1" max="50" required
                           class="w-full neo-input text-xs font-black py-2.5 px-3 bg-white border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                </div>

                <!-- Alokasi Jam Ujian -->
                <div>
                    <label class="block text-[11px] font-black uppercase font-heading text-slate-700 mb-1">Alokasi Ujian (JP)</label>
                    <input type="number" name="exam_hours" value="{{ $params['exam_hours'] }}" min="0" max="100"
                           class="w-full neo-input text-xs font-black py-2.5 px-3 bg-white border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                </div>

                <!-- Alokasi Jam Cadangan -->
                <div>
                    <label class="block text-[11px] font-black uppercase font-heading text-slate-700 mb-1">Alokasi Cadangan (JP)</label>
                    <input type="number" name="reserve_hours" value="{{ $params['reserve_hours'] }}" min="0" max="100"
                           class="w-full neo-input text-xs font-black py-2.5 px-3 bg-white border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                </div>
            </div>

            <!-- Action Toolbar Form -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.effective-weeks.index') }}" 
                   class="neo-btn bg-slate-200 hover:bg-slate-300 text-slate-800 px-4 py-2.5 text-xs uppercase font-heading font-black border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                    Reset
                </a>
                <button type="submit" 
                        class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-5 py-2.5 text-xs uppercase flex items-center gap-1.5 font-heading font-black border-2 border-black shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                    <span>🔄</span>
                    <span>Hitung Analisis</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Tab Navigation (Gasal vs Genap) -->
    <div class="flex items-center gap-3 border-b-2 border-black pb-1">
        <button type="button" id="tabBtnGasal" onclick="switchSemesterTab('ganjil')"
                class="neo-btn px-5 py-2.5 text-xs uppercase font-heading font-black border-2 border-black transition-all flex items-center gap-2 cursor-pointer {{ $currentTab === 'ganjil' ? 'bg-[#5294FF] text-white shadow-[3px_3px_0px_0px_#000] -translate-y-0.5' : 'bg-white text-black hover:bg-slate-100' }}">
            <span>📘</span>
            <span>Semester Gasal (1)</span>
            <span id="tabBadgeGasal" class="ml-1 text-[10px] {{ $currentTab === 'ganjil' ? 'bg-black/20 text-white' : 'bg-slate-200 text-slate-900' }} px-2 py-0.5 rounded-full font-mono">
                {{ $semesters['ganjil']['analysis']['total_effective_weeks'] }} Pekan Efektif
            </span>
        </button>

        <button type="button" id="tabBtnGenap" onclick="switchSemesterTab('genap')"
                class="neo-btn px-5 py-2.5 text-xs uppercase font-heading font-black border-2 border-black transition-all flex items-center gap-2 cursor-pointer {{ $currentTab === 'genap' ? 'bg-[#20C997] text-black shadow-[3px_3px_0px_0px_#000] -translate-y-0.5' : 'bg-white text-black hover:bg-slate-100' }}">
            <span>📗</span>
            <span>Semester Genap (2)</span>
            <span id="tabBadgeGenap" class="ml-1 text-[10px] {{ $currentTab === 'genap' ? 'bg-black/10 text-black' : 'bg-slate-200 text-slate-900' }} px-2 py-0.5 rounded-full font-mono">
                {{ $semesters['genap']['analysis']['total_effective_weeks'] }} Pekan Efektif
            </span>
        </button>
    </div>

    <!-- Konten Tab Gasal -->
    <div id="tabContentGasal" class="{{ $currentTab === 'ganjil' ? '' : 'hidden' }}">
        @include('admin.effective-weeks._semester', [
            'semesterKey' => 'ganjil',
            'semesterData' => $semesters['ganjil'],
            'params' => $params,
        ])
    </div>

    <!-- Konten Tab Genap -->
    <div id="tabContentGenap" class="{{ $currentTab === 'genap' ? '' : 'hidden' }}">
        @include('admin.effective-weeks._semester', [
            'semesterKey' => 'genap',
            'semesterData' => $semesters['genap'],
            'params' => $params,
        ])
    </div>
</div>

<script>
    // Tab Switcher Logika Murni (Vanilla JS)
    function switchSemesterTab(tab) {
        const btnGasal = document.getElementById('tabBtnGasal');
        const btnGenap = document.getElementById('tabBtnGenap');
        const badgeGasal = document.getElementById('tabBadgeGasal');
        const badgeGenap = document.getElementById('tabBadgeGenap');
        const contentGasal = document.getElementById('tabContentGasal');
        const contentGenap = document.getElementById('tabContentGenap');
        const activeTabInput = document.getElementById('activeTabInput');

        if (activeTabInput) activeTabInput.value = tab;

        if (tab === 'ganjil') {
            if (contentGasal) contentGasal.classList.remove('hidden');
            if (contentGenap) contentGenap.classList.add('hidden');

            if (btnGasal) {
                btnGasal.className = 'neo-btn px-5 py-2.5 text-xs uppercase font-heading font-black border-2 border-black transition-all flex items-center gap-2 cursor-pointer bg-[#5294FF] text-white shadow-[3px_3px_0px_0px_#000] -translate-y-0.5';
            }
            if (badgeGasal) {
                badgeGasal.className = 'ml-1 text-[10px] bg-black/20 text-white px-2 py-0.5 rounded-full font-mono';
            }

            if (btnGenap) {
                btnGenap.className = 'neo-btn px-5 py-2.5 text-xs uppercase font-heading font-black border-2 border-black transition-all flex items-center gap-2 cursor-pointer bg-white text-black hover:bg-slate-100';
            }
            if (badgeGenap) {
                badgeGenap.className = 'ml-1 text-[10px] bg-slate-200 text-slate-900 px-2 py-0.5 rounded-full font-mono';
            }
        } else {
            if (contentGasal) contentGasal.classList.add('hidden');
            if (contentGenap) contentGenap.classList.remove('hidden');

            if (btnGenap) {
                btnGenap.className = 'neo-btn px-5 py-2.5 text-xs uppercase font-heading font-black border-2 border-black transition-all flex items-center gap-2 cursor-pointer bg-[#20C997] text-black shadow-[3px_3px_0px_0px_#000] -translate-y-0.5';
            }
            if (badgeGenap) {
                badgeGenap.className = 'ml-1 text-[10px] bg-black/10 text-black px-2 py-0.5 rounded-full font-mono';
            }

            if (btnGasal) {
                btnGasal.className = 'neo-btn px-5 py-2.5 text-xs uppercase font-heading font-black border-2 border-black transition-all flex items-center gap-2 cursor-pointer bg-white text-black hover:bg-slate-100';
            }
            if (badgeGasal) {
                badgeGasal.className = 'ml-1 text-[10px] bg-slate-200 text-slate-900 px-2 py-0.5 rounded-full font-mono';
            }
        }
    }

    // Dropdown Cetak Toggle & Click Away
    function togglePrintDropdown(event) {
        event.stopPropagation();
        const menu = document.getElementById('printDropdownMenu');
        const chevron = document.getElementById('printDropdownChevron');
        if (menu) {
            const isHidden = menu.classList.toggle('hidden');
            if (chevron) {
                chevron.style.transform = isHidden ? 'rotate(0deg)' : 'rotate(180deg)';
            }
        }
    }

    document.addEventListener('click', function(e) {
        const menu = document.getElementById('printDropdownMenu');
        const btn = document.getElementById('printDropdownBtn');
        const chevron = document.getElementById('printDropdownChevron');
        if (menu && !menu.classList.contains('hidden')) {
            if (!menu.contains(e.target) && !btn.contains(e.target)) {
                menu.classList.add('hidden');
                if (chevron) chevron.style.transform = 'rotate(0deg)';
            }
        }
    });
</script>
@endsection
