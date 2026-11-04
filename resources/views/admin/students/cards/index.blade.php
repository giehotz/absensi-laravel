@extends('layouts.admin')

@section('title', 'Studio Cetak Kartu Siswa & QR Code')
@section('page-title', 'Studio Cetak Kartu Siswa & QR Code')

@section('content')
<div class="space-y-6 pb-20">
    <!-- Header Studio -->
    <div class="bg-white border-2 border-black p-5 rounded-xl shadow-[4px_4px_0px_0px_#000] flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                <a href="{{ route('admin.students.index') }}" class="hover:text-black hover:underline">Data Siswa</a>
                <span>/</span>
                <span class="text-black">Studio Kartu Pelajar & QR</span>
            </div>
            <h1 class="font-heading font-black text-2xl text-black flex items-center gap-2">
                <span>🪪</span> Studio Kartu Pelajar & QR Code
            </h1>
            <p class="text-xs text-slate-600 mt-1 max-w-2xl leading-relaxed">
                Pusat manajemen dan cetak kartu tanda pelajar berstandar fisik CR80 ({{ $setting->card_width_cm }} × {{ $setting->card_height_cm }} cm). Dilengkapi QR code verifikasi publik di sisi depan dan QR scanner presensi di sisi belakang.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <a href="{{ route('admin.students.cards.settings') }}" class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-3.5 py-2 text-xs font-black flex items-center gap-1.5 shadow-[2px_2px_0px_0px_#000]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span>Pengaturan Kartu</span>
            </a>
            <a href="{{ route('admin.students.index') }}" class="neo-btn bg-white hover:bg-slate-100 text-black px-3.5 py-2 text-xs font-black flex items-center gap-1.5 shadow-[2px_2px_0px_0px_#000]">
                <span>←</span>
                <span>Kembali ke Data Siswa</span>
            </a>
        </div>
    </div>

    <!-- Filter & Action Command Deck -->
    <div class="bg-white border-2 border-black p-4 rounded-xl shadow-[3px_3px_0px_0px_#000]">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <!-- Filter Group -->
            <form method="GET" action="{{ route('admin.students.cards') }}" class="flex flex-wrap items-center gap-2.5">
                <!-- Dropdown Kelas -->
                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Kelas:</span>
                    <select name="class_id" onchange="this.form.submit()" class="neo-input px-3 py-1.5 text-xs bg-slate-50 font-bold focus:bg-white cursor-pointer">
                        <option value="all">Semua Kelas</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ $selectedClassId == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} (Tingkat {{ $c->level }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Input Pencarian -->
                <div class="relative w-48 sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" 
                           name="search" 
                           value="{{ $search }}"
                           placeholder="Cari nama, NIS, atau NISN..." 
                           autocomplete="off"
                           class="w-full pl-8 pr-3 py-1.5 text-xs font-semibold text-black bg-slate-50 border-2 border-black rounded-lg shadow-[2px_2px_0px_0px_#000] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none focus:bg-white placeholder:text-slate-400 placeholder:font-normal transition-all">
                </div>

                <button type="submit" class="neo-btn bg-black text-white hover:bg-slate-800 px-3 py-1.5 text-xs font-bold">
                    Filter
                </button>
                @if($search || ($selectedClassId && $selectedClassId !== 'all'))
                    <a href="{{ route('admin.students.cards') }}" class="text-xs font-bold text-rose-600 hover:underline px-1">
                        Reset
                    </a>
                @endif
            </form>

            <!-- Batch Action Tools -->
            <div class="flex items-center gap-3 border-t lg:border-t-0 pt-3 lg:pt-0 border-slate-200 justify-between lg:justify-end">
                <label class="flex items-center gap-2 text-xs font-black text-black cursor-pointer select-none">
                    <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" class="w-4 h-4 text-black border-2 border-black rounded cursor-pointer accent-black">
                    <span>Pilih Semua ({{ count($students) }})</span>
                </label>

                <!-- Tombol Cetak Massal Form Submission -->
                <button type="button" onclick="submitBatchPrint()" 
                        class="neo-btn bg-[#20C997] hover:bg-[#1bb386] text-black px-4 py-2 text-xs font-black flex items-center gap-2 cursor-pointer shadow-[2px_2px_0px_0px_#000]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    <span>Cetak Terpilih (Format A4)</span>
                    <span id="selectedCountBadge" class="bg-black text-white text-[10px] px-1.5 py-0.5 rounded font-mono font-bold">0</span>
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
            @php
                $tileId = 'tile_' . $student->id;
            @endphp
            <div id="{{ $tileId }}_card_wrapper" class="card-tile bg-white border-2 border-black rounded-xl shadow-[4px_4px_0px_0px_#000] p-4 space-y-3 relative group transition-all duration-200">
                <!-- Top Control Header: Checkbox, Name, Sisi Flip, & Direct Print -->
                <div class="flex items-center justify-between border-b-2 border-slate-100 pb-2.5 gap-2">
                    <label class="flex items-center gap-2 cursor-pointer select-none min-w-0">
                        <input type="checkbox" 
                               name="student_ids[]" 
                               value="{{ $student->id }}" 
                               class="student-checkbox w-4 h-4 text-black border-2 border-black rounded cursor-pointer accent-black"
                               onchange="updateSelectedCount('{{ $tileId }}')">
                        <div class="min-w-0 leading-tight">
                            <div class="text-xs font-black text-black truncate max-w-[150px] sm:max-w-[170px]" title="{{ $student->user?->name }}">
                                {{ $student->user?->name ?? 'Siswa' }}
                            </div>
                            <div class="text-[10px] font-mono text-slate-500 font-bold">
                                NIS: {{ $student->nis }}
                            </div>
                        </div>
                    </label>

                    <div class="flex items-center gap-1.5 shrink-0">
                        <!-- Switcher Sisi Kartu (Depan / Belakang) -->
                        <div class="inline-flex p-0.5 bg-slate-100 border border-black rounded-lg shadow-[1px_1px_0px_#000]">
                            <button type="button" 
                                    onclick="setCardSide('{{ $tileId }}', 'front')" 
                                    id="{{ $tileId }}_tab_front"
                                    class="px-2 py-0.5 text-[10px] font-black rounded bg-white text-black shadow-[1px_1px_0px_#000]">
                                Depan
                            </button>
                            <button type="button" 
                                    onclick="setCardSide('{{ $tileId }}', 'back')" 
                                    id="{{ $tileId }}_tab_back"
                                    class="px-2 py-0.5 text-[10px] font-black rounded text-slate-600 hover:text-black">
                                Belakang
                            </button>
                        </div>

                        <!-- Tombol Cetak Satuan -->
                        <a href="{{ route('admin.students.card.single', $student) }}" target="_blank"
                           class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-2 py-1 text-[10px] font-black shadow-[1.5px_1.5px_0px_#000]"
                           title="Buka pratinjau dan cetak kartu satuan">
                            🖨️
                        </a>
                    </div>
                </div>

                <!-- KOMPONEN KARTU SISWA -->
                <div class="flex justify-center py-0.5 overflow-hidden">
                    <x-student-card :student="$student" :setting="$setting" side="both" :interactive="false" :cardId="$tileId" />
                </div>
            </div>
            @empty
            <div class="col-span-full p-12 text-center bg-white border-2 border-black rounded-xl shadow-[4px_4px_0px_0px_#000] space-y-3">
                <div class="text-4xl">🔍</div>
                <h3 class="font-heading font-black text-base text-black">Tidak Ada Data Siswa Ditemukan</h3>
                <p class="text-xs text-slate-600 max-w-md mx-auto">
                    Kriteria pencarian atau filter kelas tidak menghasilkan data kartu siswa. Silakan coba kata kunci lain atau reset filter.
                </p>
                <div class="pt-2">
                    <a href="{{ route('admin.students.cards') }}" class="neo-btn bg-black text-white hover:bg-slate-800 px-4 py-2 text-xs font-bold">
                        Reset Filter & Tampilkan Semua
                    </a>
                </div>
            </div>
            @endforelse
        </div>
    </form>

    <!-- Pagination -->
    @if($students->hasPages())
        <div class="bg-white border-2 border-black p-4 rounded-xl shadow-[3px_3px_0px_0px_#000] flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-xs font-bold text-slate-600">
                Menampilkan <span class="text-black">{{ $students->firstItem() ?? 0 }}</span> - <span class="text-black">{{ $students->lastItem() ?? 0 }}</span> dari <span class="text-black">{{ $students->total() }}</span> siswa
            </div>
            <div>
                {{ $students->links() }}
            </div>
        </div>
    @endif
</div>

<!-- Floating Action Dock (Muncul Ketika Minimal 1 Kartu Dipilih) -->
<div id="floatingActionDock" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 transition-all duration-300 transform translate-y-24 opacity-0 pointer-events-none">
    <div class="bg-white border-3 border-black shadow-[6px_6px_0px_0px_#000] rounded-xl px-5 py-3 flex items-center gap-4">
        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
            <span class="text-xs font-black text-black">
                <span id="floatingSelectedCount">0</span> Kartu Siswa Terpilih
            </span>
        </div>

        <div class="h-4 w-px bg-slate-300"></div>

        <button type="button" onclick="clearAllSelections()" class="text-xs font-bold text-slate-600 hover:text-black hover:underline cursor-pointer">
            Batal
        </button>

        <button type="button" onclick="submitBatchPrint()" class="neo-btn bg-[#20C997] hover:bg-[#1bb386] text-black px-4 py-2 text-xs font-black flex items-center gap-1.5 shadow-[2px_2px_0px_0px_#000] cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            <span>Cetak Lembar A4</span>
        </button>
    </div>
</div>

@push('scripts')
<script>
    function updateSelectedCount(tileId) {
        const checkboxes = document.querySelectorAll('.student-checkbox:checked');
        const count = checkboxes.length;

        // Update badges
        const badge = document.getElementById('selectedCountBadge');
        if (badge) badge.innerText = count;

        const floatingCount = document.getElementById('floatingSelectedCount');
        if (floatingCount) floatingCount.innerText = count;

        // Toggle Floating Dock
        const dock = document.getElementById('floatingActionDock');
        if (dock) {
            if (count > 0) {
                dock.classList.remove('translate-y-24', 'opacity-0', 'pointer-events-none');
                dock.classList.add('translate-y-0', 'opacity-100');
            } else {
                dock.classList.remove('translate-y-0', 'opacity-100');
                dock.classList.add('translate-y-24', 'opacity-0', 'pointer-events-none');
            }
        }

        // Highlight selected tile
        document.querySelectorAll('.student-checkbox').forEach(cb => {
            const wrapper = cb.closest('.card-tile');
            if (wrapper) {
                if (cb.checked) {
                    wrapper.classList.add('border-emerald-600', 'bg-emerald-50/20', 'shadow-[4px_4px_0px_0px_#059669]');
                    wrapper.classList.remove('border-black', 'shadow-[4px_4px_0px_0px_#000]');
                } else {
                    wrapper.classList.remove('border-emerald-600', 'bg-emerald-50/20', 'shadow-[4px_4px_0px_0px_#059669]');
                    wrapper.classList.add('border-black', 'shadow-[4px_4px_0px_0px_#000]');
                }
            }
        });
    }

    function toggleSelectAll(masterCheckbox) {
        const checkboxes = document.querySelectorAll('.student-checkbox');
        checkboxes.forEach(cb => cb.checked = masterCheckbox.checked);
        updateSelectedCount();
    }

    function clearAllSelections() {
        const master = document.getElementById('selectAllCheckbox');
        if (master) master.checked = false;
        const checkboxes = document.querySelectorAll('.student-checkbox');
        checkboxes.forEach(cb => cb.checked = false);
        updateSelectedCount();
    }

    function submitBatchPrint() {
        const checkboxes = document.querySelectorAll('.student-checkbox:checked');
        if (checkboxes.length === 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Kartu Siswa',
                    text: 'Pilih minimal satu kartu siswa yang ingin dicetak!',
                    confirmButtonColor: '#000000',
                });
            } else {
                alert('Pilih minimal satu kartu siswa yang ingin dicetak!');
            }
            return;
        }
        document.getElementById('batchPrintForm').submit();
    }
</script>
@endpush
@endsection
