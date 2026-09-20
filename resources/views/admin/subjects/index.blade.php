@extends('layouts.admin')

@section('title', 'Manajemen Mata Pelajaran')
@section('page-title', 'Manajemen Mata Pelajaran')

@section('content')
<div class="space-y-6">
    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white neo-box p-5">
        <div>
            <h2 class="font-heading font-black text-xl text-black flex items-center gap-2">
                <span>📚</span> Master Mata Pelajaran
            </h2>
            <p class="text-xs font-semibold text-slate-600 mt-1">
                Kelola daftar kurikulum mata pelajaran dan kode singkat untuk jadwal pembelajaran.
            </p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <button onclick="openSyncModal()" class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-4 py-2.5 text-xs uppercase flex items-center gap-2 cursor-pointer font-heading font-black">
                <span>⚡</span> Sinkron API Mapel
            </button>
            <button onclick="openModal('createSubjectModal')" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-4 py-2.5 text-xs uppercase flex items-center gap-2 cursor-pointer font-heading font-black">
                <span>+</span> Tambah Mapel Baru
            </button>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white neo-box overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-[#FFF9DB] border-b-2 border-black text-xs font-black uppercase tracking-wider">
                    <tr>
                        <th class="p-3.5 border-r border-black w-16 text-center">No</th>
                        <th class="p-3.5 border-r border-black w-32 text-center">Kode Mapel</th>
                        <th class="p-3.5 border-r border-black">Nama Mata Pelajaran</th>
                        <th class="p-3.5 border-r border-black text-center w-36">Jadwal Terkait</th>
                        <th class="p-3.5 text-center w-40">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black">
                    @forelse($subjects as $index => $subject)
                    <tr class="hover:bg-slate-50 font-medium">
                        <td class="p-3.5 font-bold text-center border-r border-black">
                            {{ $subjects->firstItem() + $index }}
                        </td>
                        <td class="p-3.5 border-r border-black text-center">
                            <span class="neo-badge bg-[#E7F5FF] text-blue-900 font-mono text-xs">
                                {{ $subject->code }}
                            </span>
                        </td>
                        <td class="p-3.5 font-bold text-black border-r border-black">
                            {{ $subject->name }}
                        </td>
                        <td class="p-3.5 border-r border-black text-center font-bold">
                            <span class="bg-slate-100 border border-black px-2 py-0.5 text-xs font-mono">
                                {{ $subject->schedules_count }} Jadwal
                            </span>
                        </td>
                        <td class="p-3.5 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="editSubject({{ json_encode([
                                    'id' => $subject->id,
                                    'name' => $subject->name,
                                    'code' => $subject->code,
                                ]) }})" class="neo-btn bg-[#FFD43B] hover:bg-yellow-400 text-black p-2 text-xs cursor-pointer group relative" title="Edit Mata Pelajaran" aria-label="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>

                                <form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST" onsubmit="return confirm('Hapus mata pelajaran ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="neo-btn bg-[#FF6B6B] hover:bg-red-500 text-white p-2 text-xs cursor-pointer group relative" title="Hapus Mata Pelajaran" aria-label="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-500 font-semibold">
                            Belum ada mata pelajaran. Klik tombol "+ Tambah Mapel Baru" untuk menambahkan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($subjects->hasPages())
        <div class="p-4 border-t-2 border-black bg-slate-50">
            {{ $subjects->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Tambah Mapel -->
<div id="createSubjectModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-md w-full p-6 space-y-5 relative">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>➕</span> Tambah Mata Pelajaran
            </h3>
            <button onclick="closeModal('createSubjectModal')" class="text-black font-black text-xl hover:opacity-75">✕</button>
        </div>

        <form action="{{ route('admin.subjects.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Kode Mapel *</label>
                <input type="text" name="code" required placeholder="Contoh: MTK, IPA, PAI" class="w-full px-3 py-2 neo-input text-sm bg-slate-50 uppercase font-mono">
            </div>

            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Nama Mata Pelajaran *</label>
                <input type="text" name="name" required placeholder="Contoh: Matematika Wajib" class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
            </div>

            <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('createSubjectModal')" class="neo-btn bg-white text-black px-4 py-2 text-xs">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#20C997] text-black px-5 py-2 text-xs font-heading">
                    Simpan Mapel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Mapel -->
<div id="editSubjectModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-md w-full p-6 space-y-5 relative">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>✏️</span> Edit Mata Pelajaran
            </h3>
            <button onclick="closeModal('editSubjectModal')" class="text-black font-black text-xl hover:opacity-75">✕</button>
        </div>

        <form id="editSubjectForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Kode Mapel *</label>
                <input type="text" id="edit_sub_code" name="code" required class="w-full px-3 py-2 neo-input text-sm bg-slate-50 uppercase font-mono">
            </div>

            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Nama Mata Pelajaran *</label>
                <input type="text" id="edit_sub_name" name="name" required class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
            </div>

            <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('editSubjectModal')" class="neo-btn bg-white text-black px-4 py-2 text-xs">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#5294FF] text-white px-5 py-2 text-xs font-heading">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Sinkronisasi Mapel dari API -->
<div id="syncSubjectModal" class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-3 sm:p-4 hidden">
    <div class="bg-white neo-box-lg max-w-3xl w-full p-5 sm:p-6 space-y-4 relative max-h-[92vh] flex flex-col">
        <!-- Modal Header -->
        <div class="flex items-start justify-between border-b-2 border-black pb-3 shrink-0">
            <div>
                <div class="flex items-center gap-2">
                    <span class="neo-badge bg-[#FFD43B] text-black text-[10px]">API REPOSITORI</span>
                    <h3 class="font-heading font-black text-lg text-black">
                        ⚡ Sinkronisasi Mata Pelajaran Nasional
                    </h3>
                </div>
                <p class="text-xs text-slate-600 font-semibold mt-1">
                    Pilih mata pelajaran dari referensi standar kurikulum nasional untuk ditambahkan ke database sekolah.
                </p>
            </div>
            <button onclick="closeModal('syncSubjectModal')" class="text-black font-black text-xl hover:opacity-75 p-1">✕</button>
        </div>

        <!-- Filter & Search Controls -->
        <div class="bg-slate-50 border-2 border-black p-3 space-y-3 shrink-0">
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                <div class="sm:col-span-4">
                    <label class="block text-[10px] font-heading font-black uppercase text-slate-600 mb-1">Filter Jenjang</label>
                    <select id="syncLevelFilter" onchange="fetchApiSubjects()" class="w-full px-3 py-2 neo-input text-xs font-bold bg-white text-black">
                        <option value="SEMUA">Semua Jenjang</option>
                        <option value="MI" {{ ($schoolLevel ?? 'SMP') === 'MI' ? 'selected' : '' }}>MI (Madrasah Ibtidaiyah - KMA 1503)</option>
                        <option value="MTs" {{ ($schoolLevel ?? 'SMP') === 'MTs' ? 'selected' : '' }}>MTs (Madrasah Tsanawiyah - KMA 1503)</option>
                        <option value="MA" {{ ($schoolLevel ?? 'SMP') === 'MA' ? 'selected' : '' }}>MA (Madrasah Aliyah - KMA 1503)</option>
                        <option value="SD" {{ ($schoolLevel ?? 'SMP') === 'SD' ? 'selected' : '' }}>SD (Sekolah Dasar)</option>
                        <option value="SMP" {{ ($schoolLevel ?? 'SMP') === 'SMP' ? 'selected' : '' }}>SMP (Sekolah Menengah Pertama)</option>
                        <option value="SMA" {{ ($schoolLevel ?? 'SMP') === 'SMA' ? 'selected' : '' }}>SMA (Sekolah Menengah Atas)</option>
                        <option value="SMK" {{ ($schoolLevel ?? 'SMP') === 'SMK' ? 'selected' : '' }}>SMK (Kejuruan)</option>
                    </select>
                </div>
                <div class="sm:col-span-8">
                    <label class="block text-[10px] font-heading font-black uppercase text-slate-600 mb-1">Cari Kode / Nama Mapel</label>
                    <div class="flex items-center gap-2">
                        <input type="text" id="syncSearchInput" oninput="filterApiSubjectsTable()" placeholder="Ketik nama atau kode mapel..." class="w-full px-3 py-2 neo-input text-xs bg-white text-black">
                        <button type="button" onclick="fetchApiSubjects()" class="neo-btn bg-white hover:bg-slate-100 text-black px-3 py-2 text-xs shrink-0" title="Muat ulang dari API">
                            🔄
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between flex-wrap gap-2 pt-1 text-xs border-t border-slate-200">
                <label class="inline-flex items-center gap-2 cursor-pointer font-bold text-black select-none">
                    <input type="checkbox" id="selectAllSync" onchange="toggleSelectAllSync(this)" class="w-4 h-4 rounded border-2 border-black text-[#20C997] focus:ring-0 cursor-pointer">
                    <span id="selectAllSyncLabel">Pilih Semua Mapel yang Tersedia</span>
                </label>
                <div class="flex items-center gap-2">
                    <span id="syncStatsText" class="text-[11px] font-mono text-slate-600 font-semibold">Memuat data...</span>
                    <span id="selectedCountBadge" class="neo-badge bg-[#5294FF] text-white text-[11px]">0 dipilih</span>
                </div>
            </div>
        </div>

        <!-- Scrollable Table Container -->
        <div class="flex-1 overflow-y-auto border-2 border-black min-h-[220px] max-h-[340px] bg-white relative">
            <div id="syncLoadingSpinner" class="absolute inset-0 bg-white/85 z-10 flex flex-col items-center justify-center gap-2 hidden">
                <div class="w-8 h-8 border-4 border-black border-t-[#FFD43B] rounded-full animate-spin"></div>
                <span class="text-xs font-bold font-heading text-black">Mengambil data dari API...</span>
            </div>

            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-[#FFF9DB] border-b-2 border-black sticky top-0 z-1 font-black uppercase text-[11px]">
                    <tr>
                        <th class="p-2.5 border-r border-black w-10 text-center">Pilih</th>
                        <th class="p-2.5 border-r border-black w-24">Kode</th>
                        <th class="p-2.5 border-r border-black">Nama Mata Pelajaran</th>
                        <th class="p-2.5 border-r border-black w-28">Kategori</th>
                        <th class="p-2.5 border-r border-black w-24 text-center">Jenjang</th>
                        <th class="p-2.5 text-center w-32">Status</th>
                    </tr>
                </thead>
                <tbody id="syncTableBody" class="divide-y border-black font-medium">
                    <!-- Rows dynamically generated by JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Form for Submitting Selected Subjects -->
        <form id="syncSubjectsForm" action="{{ route('admin.subjects.sync') }}" method="POST" onsubmit="handleSyncSubmit(event)">
            @csrf
            <div id="syncHiddenInputs"></div>

            <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-between gap-3 shrink-0">
                <button type="button" onclick="closeModal('syncSubjectModal')" class="neo-btn bg-white hover:bg-slate-100 text-black px-4 py-2.5 text-xs font-heading font-bold cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="btnSubmitSync" disabled class="neo-btn bg-[#20C997] hover:bg-[#12b886] disabled:opacity-50 disabled:cursor-not-allowed text-black px-6 py-2.5 text-xs font-heading font-black uppercase flex items-center gap-2 cursor-pointer">
                    <span>💾</span> Simpan Mapel Terpilih (<span id="btnSubmitCount">0</span>)
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const EXISTING_SUBJECT_CODES = @json($existingCodes ?? []);
    let apiSubjects = [];
    let selectedSubjects = new Map();

    function editSubject(data) {
        document.getElementById('edit_sub_code').value = data.code;
        document.getElementById('edit_sub_name').value = data.name;
        document.getElementById('editSubjectForm').action = '/admin/subjects/' + data.id;
        openModal('editSubjectModal');
    }

    function openSyncModal() {
        openModal('syncSubjectModal');
        if (apiSubjects.length === 0) {
            fetchApiSubjects();
        }
    }

    function fetchApiSubjects() {
        const level = document.getElementById('syncLevelFilter').value;
        const spinner = document.getElementById('syncLoadingSpinner');
        const stats = document.getElementById('syncStatsText');
        if (spinner) spinner.classList.remove('hidden');
        if (stats) stats.innerText = 'Menghubungkan ke API...';

        const url = new URL('{{ route("api.reference-subjects") }}', window.location.origin);
        if (level && level !== 'SEMUA') {
            url.searchParams.set('level', level);
        }

        fetch(url.toString(), {
            headers: {
                'Accept': 'application/json',
            }
        })
        .then(res => res.json())
        .then(res => {
            if (res.success && Array.isArray(res.data)) {
                apiSubjects = res.data;
                renderSyncTable();
            } else {
                const msg = res.message || 'Format data API tidak valid.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Memuat API',
                        text: msg,
                    });
                } else {
                    alert('Gagal Memuat API: ' + msg);
                }
            }
        })
        .catch(err => {
            console.error(err);
            const msg = 'Tidak dapat mengambil daftar mata pelajaran dari endpoint API.';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Koneksi API Gagal',
                    text: msg,
                });
            } else {
                alert('Koneksi API Gagal: ' + msg);
            }
        })
        .finally(() => {
            if (spinner) spinner.classList.add('hidden');
        });
    }

    function renderSyncTable() {
        const tbody = document.getElementById('syncTableBody');
        const search = (document.getElementById('syncSearchInput').value || '').toLowerCase().trim();
        const existingUpper = EXISTING_SUBJECT_CODES.map(c => String(c).toUpperCase().trim());

        let filtered = apiSubjects.filter(item => {
            if (!search) return true;
            return item.code.toLowerCase().includes(search)
                || item.name.toLowerCase().includes(search)
                || (item.category && item.category.toLowerCase().includes(search));
        });

        tbody.innerHTML = '';

        let availableCount = 0;

        if (filtered.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="p-8 text-center text-slate-500 font-semibold">
                        Tidak ada mata pelajaran yang cocok dengan filter atau pencarian.
                    </td>
                </tr>
            `;
            updateSyncStats(0, 0);
            return;
        }

        filtered.forEach(item => {
            const isExisting = existingUpper.includes(item.code.toUpperCase().trim());
            if (!isExisting) availableCount++;
            const isChecked = selectedSubjects.has(item.code);

            const tr = document.createElement('tr');
            tr.className = isExisting ? 'bg-slate-100 opacity-60 font-medium' : 'hover:bg-slate-50 font-medium';

            tr.innerHTML = `
                <td class="p-2.5 border-r border-black text-center">
                    <input type="checkbox" 
                           data-code="${item.code}" 
                           data-name="${item.name.replace(/"/g, '&quot;')}"
                           ${isExisting ? 'disabled' : ''}
                           ${isChecked ? 'checked' : ''}
                           onchange="handleSubjectCheckboxChange(this)"
                           class="sync-subject-checkbox w-4 h-4 rounded border-2 border-black text-[#20C997] focus:ring-0 ${isExisting ? 'cursor-not-allowed opacity-40' : 'cursor-pointer'}">
                </td>
                <td class="p-2.5 border-r border-black font-mono font-bold text-black">
                    <span class="neo-badge bg-[#E7F5FF] text-blue-900 text-[10px] px-1.5 py-0.5">${item.code}</span>
                </td>
                <td class="p-2.5 border-r border-black font-bold text-black">
                    ${item.name}
                </td>
                <td class="p-2.5 border-r border-black text-slate-600 font-semibold">
                    ${item.category || '-'}
                </td>
                <td class="p-2.5 border-r border-black text-center">
                    <span class="text-[10px] font-mono font-bold bg-slate-100 px-1.5 py-0.5 border border-slate-400 rounded">
                        ${(item.levels || []).slice(0, 3).join(', ')}${(item.levels || []).length > 3 ? '...' : ''}
                    </span>
                </td>
                <td class="p-2.5 text-center">
                    ${isExisting 
                        ? '<span class="inline-block px-2 py-0.5 text-[10px] font-black uppercase bg-slate-200 text-slate-600 border border-black rounded">✓ Sudah Ada</span>'
                        : '<span class="inline-block px-2 py-0.5 text-[10px] font-black uppercase bg-[#D3F9D8] text-emerald-900 border border-black rounded">Tersedia</span>'}
                </td>
            `;

            tbody.appendChild(tr);
        });

        updateSyncStats(filtered.length, availableCount);
        updateSyncSelectedCount();
    }

    function filterApiSubjectsTable() {
        renderSyncTable();
    }

    function handleSubjectCheckboxChange(cb) {
        const code = cb.getAttribute('data-code');
        const name = cb.getAttribute('data-name');
        if (cb.checked) {
            selectedSubjects.set(code, { code, name });
        } else {
            selectedSubjects.delete(code);
        }
        updateSyncSelectedCount();
    }

    function toggleSelectAllSync(masterCb) {
        const checkboxes = document.querySelectorAll('.sync-subject-checkbox:not(:disabled)');
        checkboxes.forEach(cb => {
            cb.checked = masterCb.checked;
            const code = cb.getAttribute('data-code');
            const name = cb.getAttribute('data-name');
            if (masterCb.checked) {
                selectedSubjects.set(code, { code, name });
            } else {
                selectedSubjects.delete(code);
            }
        });
        updateSyncSelectedCount();
    }

    function updateSyncStats(totalFiltered, availableCount) {
        const stats = document.getElementById('syncStatsText');
        if (stats) {
            stats.innerText = `${totalFiltered} mapel (${availableCount} tersedia)`;
        }
    }

    function updateSyncSelectedCount() {
        const count = selectedSubjects.size;
        const badge = document.getElementById('selectedCountBadge');
        const btnCount = document.getElementById('btnSubmitCount');
        const btnSubmit = document.getElementById('btnSubmitSync');

        if (badge) badge.innerText = `${count} dipilih`;
        if (btnCount) btnCount.innerText = count;
        if (btnSubmit) {
            btnSubmit.disabled = count === 0;
        }
    }

    function doSubmitSync() {
        const hiddenContainer = document.getElementById('syncHiddenInputs');
        if (!hiddenContainer) return;
        hiddenContainer.innerHTML = '';

        let idx = 0;
        selectedSubjects.forEach((item) => {
            const inputCode = document.createElement('input');
            inputCode.type = 'hidden';
            inputCode.name = `subjects[${idx}][code]`;
            inputCode.value = item.code;

            const inputName = document.createElement('input');
            inputName.type = 'hidden';
            inputName.name = `subjects[${idx}][name]`;
            inputName.value = item.name;

            hiddenContainer.appendChild(inputCode);
            hiddenContainer.appendChild(inputName);
            idx++;
        });

        const btnSubmit = document.getElementById('btnSubmitSync');
        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span>⏳</span> Menyimpan...';
        }

        document.getElementById('syncSubjectsForm').submit();
    }

    function handleSyncSubmit(event) {
        event.preventDefault();
        if (selectedSubjects.size === 0) {
            const msg = 'Silakan centang setidaknya satu mata pelajaran yang ingin disinkronkan.';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Mapel',
                    text: msg,
                    confirmButtonColor: '#FFD43B',
                });
            } else {
                alert(msg);
            }
            return;
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Konfirmasi Sinkronisasi',
                html: `Simpan <b>${selectedSubjects.size}</b> mata pelajaran terpilih dari API ke database sistem?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#20C997',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '⚡ Ya, Simpan Mapel',
                cancelButtonText: 'Batal',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    doSubmitSync();
                }
            });
        } else {
            if (window.confirm(`Simpan ${selectedSubjects.size} mata pelajaran terpilih dari API ke database sistem?`)) {
                doSubmitSync();
            }
        }
    }
</script>
@endpush
@endsection
