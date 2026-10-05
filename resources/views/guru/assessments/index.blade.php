@extends('layouts.guru')

@section('title', 'Penilaian Sumatif Siswa')
@section('page-title', 'Penilaian Sumatif Siswa')

@section('content')
<div class="space-y-6 pb-12">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white border-2 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000]">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                <span>Akademik</span>
                <span>/</span>
                <span class="text-black">Penilaian Sumatif</span>
                <span>/</span>
                <span class="text-black font-black">
                    @if($activeType === 'sts')
                        Sumatif Tengah Semester (STS)
                    @elseif($activeType === 'sas')
                        Sumatif Akhir Semester (SAS)
                    @else
                        Sumatif Lingkup Materi
                    @endif
                </span>
            </div>
            <h2 class="font-heading font-black text-2xl text-black">Penilaian Sumatif Siswa</h2>
            <p class="text-xs text-slate-600 mt-0.5">
                Pengelolaan paket nilai sumatif (SUM 1 s.d. SUM 15), template Excel multi-sheet, kalkulasi ketuntasan, dan rekap resmi.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- CTA Buat Paket Baru Sesuai Tab Aktif -->
            <a href="{{ route('guru.penilaian.create', ['type' => $activeType]) }}" 
               class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-4 py-2.5 text-xs font-black flex items-center gap-2 cursor-pointer shadow-[3px_3px_0px_0px_#000] border-2 border-black rounded-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>
                    @if($activeType === 'sts')
                        + Buat Asesmen STS
                    @elseif($activeType === 'sas')
                        + Buat Asesmen SAS
                    @else
                        + Buat Paket Penilaian
                    @endif
                </span>
            </a>
        </div>
    </div>

    <!-- Assessment Category Tabs (Neo-brutalism) -->
    <div class="bg-white border-2 border-black p-2 rounded-lg shadow-[4px_4px_0px_0px_#000]">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
            <!-- Tab 1: Sumatif Lingkup Materi -->
            <a href="{{ route('guru.penilaian.index', array_merge(request()->except('page'), ['type' => 'materi'])) }}" 
               class="flex items-center justify-between p-3 rounded-lg border-2 border-black font-heading transition-all duration-150 {{ $activeType === 'materi' ? 'bg-[#FFD43B] text-black shadow-[3px_3px_0px_0px_#000] font-black' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 font-bold' }}">
                <div class="flex items-center gap-2.5">
                    <div class="p-1.5 rounded border border-black {{ $activeType === 'materi' ? 'bg-black text-yellow-300' : 'bg-[#D3F9D8] text-emerald-950' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs uppercase tracking-wider">Lingkup Materi</div>
                        <div class="text-[10px] text-slate-600 font-normal">SUM 1 s.d. SUM 15</div>
                    </div>
                </div>
                <span class="text-xs font-black px-2 py-0.5 rounded border border-black {{ $activeType === 'materi' ? 'bg-black text-yellow-300' : 'bg-white text-black' }}">
                    {{ $countMateri }}
                </span>
            </a>

            <!-- Tab 2: Asesmen Sumatif Tengah Semester (STS) -->
            <a href="{{ route('guru.penilaian.index', array_merge(request()->except('page'), ['type' => 'sts'])) }}" 
               class="flex items-center justify-between p-3 rounded-lg border-2 border-black font-heading transition-all duration-150 {{ $activeType === 'sts' ? 'bg-[#FFD43B] text-black shadow-[3px_3px_0px_0px_#000] font-black' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 font-bold' }}">
                <div class="flex items-center gap-2.5">
                    <div class="p-1.5 rounded border border-black {{ $activeType === 'sts' ? 'bg-black text-yellow-300' : 'bg-[#FFF3BF] text-amber-950' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs uppercase tracking-wider">Asesmen STS</div>
                        <div class="text-[10px] text-slate-600 font-normal">Tengah Semester (PTS)</div>
                    </div>
                </div>
                <span class="text-xs font-black px-2 py-0.5 rounded border border-black {{ $activeType === 'sts' ? 'bg-black text-yellow-300' : 'bg-white text-black' }}">
                    {{ $countSts }}
                </span>
            </a>

            <!-- Tab 3: Asesmen Sumatif Akhir Semester (SAS) -->
            <a href="{{ route('guru.penilaian.index', array_merge(request()->except('page'), ['type' => 'sas'])) }}" 
               class="flex items-center justify-between p-3 rounded-lg border-2 border-black font-heading transition-all duration-150 {{ $activeType === 'sas' ? 'bg-[#FFD43B] text-black shadow-[3px_3px_0px_0px_#000] font-black' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 font-bold' }}">
                <div class="flex items-center gap-2.5">
                    <div class="p-1.5 rounded border border-black {{ $activeType === 'sas' ? 'bg-black text-yellow-300' : 'bg-[#FFE3E3] text-rose-950' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs uppercase tracking-wider">Asesmen SAS</div>
                        <div class="text-[10px] text-slate-600 font-normal">Akhir Semester (PAS)</div>
                    </div>
                </div>
                <span class="text-xs font-black px-2 py-0.5 rounded border border-black {{ $activeType === 'sas' ? 'bg-black text-yellow-300' : 'bg-white text-black' }}">
                    {{ $countSas }}
                </span>
            </a>
        </div>
    </div>

    <!-- Detail Info Banner Sesuai Jenis Penilaian -->
    @if($activeType === 'sts')
        @include('guru.assessments.sts._info_banner')
    @elseif($activeType === 'sas')
        @include('guru.assessments.sas._info_banner')
    @else
        @include('guru.assessments.materi._info_banner')
    @endif

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Paket</div>
            <div class="font-heading font-black text-2xl text-black mt-1">{{ $totalPackages }}</div>
            <div class="text-[10px] text-slate-600 mt-0.5">Seluruh paket dibuat</div>
        </div>
        <div class="bg-[#FFF9DB] border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Status Draft</div>
            <div class="font-heading font-black text-2xl text-black mt-1">{{ $draftPackages }}</div>
            <div class="text-[10px] text-amber-800 mt-0.5">Bebas diisi & diimpor</div>
        </div>
        <div class="bg-[#D3F9D8] border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Terkunci (Final)</div>
            <div class="font-heading font-black text-2xl text-black mt-1">{{ $lockedPackages }}</div>
            <div class="text-[10px] text-emerald-900 mt-0.5">Tampil di portal siswa</div>
        </div>
        <div class="bg-[#E7F5FF] border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
            <div class="text-[11px] font-bold text-blue-700 uppercase tracking-wider">Kelas Binaan</div>
            <div class="font-heading font-black text-2xl text-black mt-1">{{ $homeroomCount }}</div>
            <div class="text-[10px] text-blue-900 mt-0.5">Sebagai Wali Kelas</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
        <form action="{{ route('guru.penilaian.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <input type="hidden" name="type" value="{{ $activeType }}">
            <div>
                <label class="block text-[11px] font-black text-black uppercase tracking-wider mb-1">Kelas / Rombel</label>
                <select name="school_class_id" class="w-full text-xs font-bold border-2 border-black p-2 rounded bg-slate-50 focus:bg-white focus:outline-none">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ request('school_class_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} (Tk. {{ $c->level }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-black text-black uppercase tracking-wider mb-1">Mata Pelajaran</label>
                <select name="subject_id" class="w-full text-xs font-bold border-2 border-black p-2 rounded bg-slate-50 focus:bg-white focus:outline-none">
                    <option value="">-- Semua Mapel --</option>
                    @foreach($subjects as $s)
                        <option value="{{ $s->id }}" {{ request('subject_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-black text-black uppercase tracking-wider mb-1">Status Nilai</label>
                <select name="status" class="w-full text-xs font-bold border-2 border-black p-2 rounded bg-slate-50 focus:bg-white focus:outline-none">
                    <option value="">-- Semua Status --</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft (Aktif Diisi)</option>
                    <option value="locked" {{ request('status') === 'locked' ? 'selected' : '' }}>Terkunci (Final)</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full bg-black hover:bg-slate-800 text-white font-black text-xs py-2.5 rounded border-2 border-black shadow-[2px_2px_0px_0px_#FFD43B] cursor-pointer">
                    Filter Data
                </button>
                @if(request()->hasAny(['school_class_id', 'subject_id', 'status']))
                    <a href="{{ route('guru.penilaian.index', ['type' => $activeType]) }}" class="bg-slate-200 hover:bg-slate-300 text-black font-bold text-xs p-2.5 rounded border-2 border-black">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Package List Sesuai Tab Aktif -->
    @if($activeType === 'sts')
        @include('guru.assessments.sts._packages_list')
    @elseif($activeType === 'sas')
        @include('guru.assessments.sas._packages_list')
    @else
        @include('guru.assessments.materi._packages_list')
    @endif
</div>

<!-- Modal Dialog Info Paket Terkunci (Neo-brutalism) -->
<div id="lockedModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white border-3 border-black rounded-xl max-w-md w-full p-6 shadow-[6px_6px_0px_0px_#000] space-y-4 animate-in fade-in zoom-in duration-150">
        <div class="flex items-start gap-3">
            <div class="w-12 h-12 rounded-lg bg-[#FFF9DB] border-2 border-black flex items-center justify-center shrink-0 shadow-[2px_2px_0px_0px_#000]">
                <svg class="w-6 h-6 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-amber-900 bg-[#FFF9DB] px-2 py-0.5 rounded border border-black">
                    Status: Terkunci (Final)
                </span>
                <h3 class="font-heading font-black text-lg text-black mt-1">Paket Nilai Telah Dikunci</h3>
                <p id="lockedModalPackageTitle" class="text-xs font-bold text-slate-700"></p>
            </div>
        </div>

        <div class="bg-slate-50 border-2 border-slate-200 p-3.5 rounded-lg text-xs text-slate-700 space-y-2">
            <p>
                Paket ini telah disahkan dan berstatus <strong>Final</strong>. Nilai sudah tampil di portal Siswa dan Orang Tua sehingga tidak dapat dihapus sembarangan demi integritas data akademik.
            </p>
            <div class="flex items-center gap-2 p-2 bg-[#FFF3BF] border border-amber-300 rounded text-amber-950 font-bold">
                <svg class="w-4 h-4 shrink-0 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Untuk membatalkan kunci atau menghapus paket ini, silakan hubungi <strong>Administrator Kurikulum</strong>.</span>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2">
            <button type="button" onclick="closeLockedModal()" class="w-full sm:w-auto bg-black hover:bg-slate-800 text-white font-black text-xs px-5 py-2.5 rounded border-2 border-black shadow-[2px_2px_0px_0px_#FFD43B] cursor-pointer">
                Mengerti
            </button>
        </div>
    </div>
</div>

<script>
    function showLockedModal(title) {
        document.getElementById('lockedModalPackageTitle').textContent = title;
        document.getElementById('lockedModal').classList.remove('hidden');
    }

    function closeLockedModal() {
        document.getElementById('lockedModal').classList.add('hidden');
    }

    document.getElementById('lockedModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeLockedModal();
        }
    });
</script>
@endsection
