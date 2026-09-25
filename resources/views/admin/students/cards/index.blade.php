@extends('layouts.admin')

@section('title', 'Studio Cetak Kartu Siswa & QR Code')
@section('page-title', 'Studio Cetak Kartu Siswa & QR Code')

@section('content')
<div class="space-y-6 pb-12">
    <!-- Header Studio -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white border-2 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000]">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                <a href="{{ route('admin.students.index') }}" class="hover:text-black hover:underline">Data Siswa</a>
                <span>/</span>
                <span class="text-black">Studio Kartu Pelajar & QR</span>
            </div>
            <h2 class="font-heading font-black text-2xl text-black flex items-center gap-2">
                <span>🪪</span> Studio Kartu Pelajar & QR Code
            </h2>
            <p class="text-xs text-slate-600 mt-0.5">
                Layout kartu tanda pengenal standar CR80 (85.6mm × 54mm) beresolusi tinggi untuk tanda pengenal resmi dan scanner presensi.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('admin.students.cards.settings') }}" class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-3.5 py-2 text-xs font-bold flex items-center gap-1.5 shadow-[2px_2px_0px_0px_#000]">
                <span>⚙️</span> Pengaturan Kartu
            </a>
            <a href="{{ route('admin.students.index') }}" class="neo-btn bg-white hover:bg-slate-100 text-black px-3.5 py-2 text-xs font-bold flex items-center gap-1.5 shadow-[2px_2px_0px_0px_#000]">
                <span>←</span> Kembali ke Data Siswa
            </a>
        </div>
    </div>

    <!-- Filter & Selection Bar -->
    <div class="bg-white border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Filter Form -->
            <form method="GET" action="{{ route('admin.students.cards') }}" class="flex flex-wrap items-center gap-3">
                <!-- Dropdown Kelas -->
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-black uppercase tracking-wider">Kelas:</label>
                    <select name="class_id" onchange="this.form.submit()" class="neo-input px-3 py-1.5 text-xs bg-slate-50 font-bold focus:bg-white">
                        <option value="all">Semua Kelas</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ $selectedClassId == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} (Tingkat {{ $c->level }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Input Pencarian -->
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           value="{{ $search }}"
                           placeholder="Cari nama atau NIS..." 
                           class="neo-input pl-8 pr-3 py-1.5 text-xs bg-slate-50 focus:bg-white w-48 sm:w-56 font-medium">
                    <svg class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>

                <button type="submit" class="neo-btn bg-black text-white hover:bg-slate-800 px-3 py-1.5 text-xs font-bold">
                    Filter
                </button>
                @if($search || ($selectedClassId && $selectedClassId !== 'all'))
                    <a href="{{ route('admin.students.cards') }}" class="text-xs font-bold text-rose-600 hover:underline">
                        Reset
                    </a>
                @endif
            </form>

            <!-- Multi-Select & Batch Actions -->
            <div class="flex items-center gap-3 border-t md:border-t-0 pt-3 md:pt-0 border-slate-200">
                <label class="flex items-center gap-2 text-xs font-bold text-black cursor-pointer select-none">
                    <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" class="w-4 h-4 text-black border-2 border-black rounded cursor-pointer">
                    <span>Pilih Semua Halaman Ini</span>
                </label>

                <!-- Tombol Cetak Massal Form Submission -->
                <button type="button" onclick="submitBatchPrint()" 
                        class="neo-btn bg-[#20C997] hover:bg-[#1bb386] text-black px-4 py-2 text-xs font-bold flex items-center gap-2 cursor-pointer shadow-[2px_2px_0px_0px_#000]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    <span>Cetak Terpilih (Format A4)</span>
                    <span id="selectedCountBadge" class="bg-black text-white text-[10px] px-1.5 py-0.2 rounded font-mono">0</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Form Kontainer untuk Cetak Massal (POST target _blank) -->
    <form id="batchPrintForm" action="{{ route('admin.students.cards.print') }}" method="POST" target="_blank">
        @csrf
        <!-- Grid Kartu Pelajar -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @forelse($students as $student)
            <div class="bg-white border-2 border-black rounded-lg shadow-[4px_4px_0px_0px_#000] p-3.5 space-y-3 relative group transition-transform hover:-translate-y-1">
                <!-- Top Action Header: Checkbox & Quick Actions -->
                <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                    <label class="flex items-center gap-2 text-xs font-bold text-black cursor-pointer select-none">
                        <input type="checkbox" 
                               name="student_ids[]" 
                               value="{{ $student->id }}" 
                               class="student-checkbox w-4 h-4 text-black border-2 border-black rounded cursor-pointer"
                               onchange="updateSelectedCount()">
                        <span class="font-mono text-[11px]">{{ $student->nis }}</span>
                    </label>

                    <div class="flex items-center gap-1.5">
                        <a href="{{ route('admin.students.card.single', $student) }}" target="_blank"
                           class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-2 py-0.5 text-[10px] font-bold"
                           title="Cetak Satuan">
                            🖨️ Cetak Satuan
                        </a>
                    </div>
                </div>

                <!-- KOMPONEN KARTU SISWA (SINGLE SOURCE OF TRUTH: FRONT & BACK 3D FLIP) -->
                <div class="flex justify-center py-1">
                    <x-student-card :student="$student" :setting="$setting" side="both" :interactive="true" />
                </div>
            </div>
            @empty
            <div class="col-span-full p-12 text-center bg-white neo-box text-slate-500 font-bold">
                Tidak ada data siswa ditemukan untuk kriteria pencarian ini.
            </div>
            @endforelse
        </div>
    </form>

    <!-- Pagination -->
    @if($students->hasPages())
        <div class="bg-white border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_0px_#000]">
            {{ $students->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.student-checkbox:checked');
        const badge = document.getElementById('selectedCountBadge');
        badge.innerText = checkboxes.length;
    }

    function toggleSelectAll(masterCheckbox) {
        const checkboxes = document.querySelectorAll('.student-checkbox');
        checkboxes.forEach(cb => cb.checked = masterCheckbox.checked);
        updateSelectedCount();
    }

    function submitBatchPrint() {
        const checkboxes = document.querySelectorAll('.student-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Pilih minimal satu kartu siswa yang ingin dicetak!');
            return;
        }
        document.getElementById('batchPrintForm').submit();
    }
</script>
@endpush
@endsection
