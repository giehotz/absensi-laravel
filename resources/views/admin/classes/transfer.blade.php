@extends('layouts.admin')

@section('title', 'Pindah / Mutasi Kelas Siswa')
@section('page-title', 'Pindah Kelas Siswa')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Back Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="{{ route('admin.classes.index') }}" class="neo-btn bg-white hover:bg-slate-100 text-black px-4 py-2 text-xs uppercase flex items-center gap-2 cursor-pointer font-heading w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke Daftar Kelas</span>
        </a>

        <div class="flex items-center gap-2 text-xs font-bold font-mono">
            <span class="bg-[#FFF9DB] px-3 py-1 border-2 border-black neo-box flex items-center gap-1.5">
                <span>🔄</span> Mutasi Rombel Internal
            </span>
        </div>
    </div>

    <!-- Header Description Card -->
    <div class="bg-[#FFF9DB] neo-box-lg p-6 relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="space-y-1.5 z-10">
            <h1 class="font-heading text-xl sm:text-2xl font-black text-black tracking-tight flex items-center gap-2">
                <span>⇄</span> Pindah / Mutasi Siswa Antar Kelas
            </h1>
            <p class="text-xs font-semibold text-slate-700 max-w-2xl">
                Gunakan halaman ini untuk memindahkan siswa yang salah penempatan kelas. Pilih kelas asal di panel kiri, centang siswa yang ingin dipindahkan, lalu tentukan kelas tujuan di panel kanan.
            </p>
        </div>
        <div class="hidden md:flex items-center gap-2 shrink-0">
            <span class="neo-badge bg-[#5294FF] text-white text-xs">Satu Tahun Ajaran</span>
        </div>
    </div>

    <!-- Transfer Dual-Panel Form -->
    <form id="transferForm" action="{{ route('admin.classes.transfer.store') }}" method="POST">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- ===================== PANEL KIRI: KELAS ASAL ===================== -->
            <div class="lg:col-span-7 bg-white neo-box p-5 space-y-4">
                <div class="flex items-center justify-between border-b-2 border-black pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-[#FFD43B] border-2 border-black flex items-center justify-center text-xs font-black">1</span>
                        <h2 class="font-heading font-black text-sm uppercase tracking-wide text-black">
                            Kelas Asal & Pilih Siswa
                        </h2>
                    </div>
                    <span id="badgeSourceStatus" class="neo-badge bg-slate-100 text-slate-700 text-[10px]">
                        Pilih Kelas
                    </span>
                </div>

                <!-- Dropdown Pilih Kelas Asal -->
                <div>
                    <label for="source_class_id" class="block font-heading font-bold text-xs text-black mb-1.5">
                        Pilih Rombel / Kelas Asal <span class="text-red-500">*</span>
                    </label>
                    <select id="source_class_id" name="source_class_id" required onchange="handleSourceClassChange(this.value)" class="w-full px-3 py-2.5 neo-input text-sm bg-slate-50 font-medium">
                        <option value="">-- Pilih Kelas Asal --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" 
                                    data-academic-year="{{ $c->academicYear->name ?? '-' }}" 
                                    {{ (string)$c->id === (string)$selectedClassId ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->level }}) - {{ $c->academicYear->name ?? 'T.A -' }} [{{ $c->students_count }} Siswa]
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Meta Info Kelas Asal (Tampil saat kelas dipilih) -->
                <div id="sourceClassInfo" class="hidden bg-slate-50 border-2 border-black p-3 text-xs space-y-1 rounded-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <span class="text-slate-500">Wali Kelas:</span>
                            <span id="srcHomeroom" class="font-bold text-black ml-1">-</span>
                        </div>
                        <div>
                            <span class="text-slate-500">T.A:</span>
                            <span id="srcAcademicYear" class="font-mono font-bold text-black ml-1">-</span>
                        </div>
                        <div>
                            <span class="text-slate-500">Total Siswa:</span>
                            <span id="srcTotalStudents" class="font-bold bg-[#D3F9D8] px-2 py-0.5 border border-black text-black ml-1">0</span>
                        </div>
                    </div>
                </div>

                <!-- Live Search & Select All Controls -->
                <div id="studentControls" class="hidden space-y-3 pt-2">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <!-- Search Box -->
                        <div class="relative flex-1">
                            <input type="text" id="searchStudent" oninput="filterStudents()" placeholder="Cari nama atau NIS siswa..." class="w-full pl-8 pr-3 py-1.5 neo-input text-xs bg-white">
                            <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>

                        <!-- Select All Checkbox -->
                        <div class="flex items-center gap-2 bg-[#FFF9DB] border-2 border-black px-3 py-1.5 shrink-0">
                            <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this.checked)" class="w-4 h-4 border-2 border-black text-black rounded cursor-pointer accent-black">
                            <label for="selectAllCheckbox" class="text-xs font-bold text-black cursor-pointer select-none">
                                Pilih Semua
                            </label>
                        </div>
                    </div>

                    <!-- Selected Counter Bar -->
                    <div class="flex items-center justify-between text-xs font-semibold px-1 text-slate-700">
                        <div>
                            Dipilih: <span id="selectedCounterText" class="font-black text-black bg-[#FFD43B] px-2 py-0.5 border border-black">0</span> dari <span id="visibleCounterText">0</span> siswa
                        </div>
                        <button type="button" onclick="clearSelection()" class="text-red-600 hover:underline text-[11px] font-bold">
                            Reset Pilihan
                        </button>
                    </div>
                </div>

                <!-- Student List Container -->
                <div id="studentListContainer" class="border-2 border-black bg-slate-50 min-h-[220px] max-h-[460px] overflow-y-auto">
                    <!-- Default Initial State -->
                    <div id="initialStateNotice" class="p-8 text-center text-slate-500 font-semibold space-y-2">
                        <div class="text-3xl">👈</div>
                        <p class="text-xs">Silakan pilih kelas asal di atas untuk memuat daftar siswa.</p>
                    </div>

                    <!-- Loading Spinner -->
                    <div id="loadingState" class="hidden p-8 text-center text-slate-600 font-semibold space-y-3">
                        <div class="inline-block w-8 h-8 border-4 border-black border-t-transparent rounded-full animate-spin"></div>
                        <p class="text-xs font-heading">Memuat data siswa & kelas tujuan...</p>
                    </div>

                    <!-- Empty Students Notice -->
                    <div id="emptyStudentsNotice" class="hidden p-8 text-center text-slate-500 font-semibold space-y-2">
                        <div class="text-2xl">📭</div>
                        <p class="text-xs">Kelas ini belum memiliki siswa yang terdaftar.</p>
                    </div>

                    <!-- Actual Students Checklist Table -->
                    <table id="studentsTable" class="w-full text-left text-xs hidden">
                        <thead class="bg-[#FFF9DB] border-b-2 border-black sticky top-0 z-10 text-[11px] uppercase font-black">
                            <tr>
                                <th class="p-2.5 w-10 text-center border-r border-black">Pilih</th>
                                <th class="p-2.5 border-r border-black">Siswa</th>
                                <th class="p-2.5 border-r border-black">NIS / NISN</th>
                                <th class="p-2.5 text-center w-16">L/P</th>
                            </tr>
                        </thead>
                        <tbody id="studentsTableBody" class="divide-y border-black font-medium">
                            <!-- Populated via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ===================== PANEL KANAN: KELAS TUJUAN & CTA ===================== -->
            <div class="lg:col-span-5 bg-white neo-box p-5 space-y-4">
                <div class="flex items-center justify-between border-b-2 border-black pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-[#20C997] border-2 border-black flex items-center justify-center text-xs font-black text-black">2</span>
                        <h2 class="font-heading font-black text-sm uppercase tracking-wide text-black">
                            Kelas Tujuan & Mutasi
                        </h2>
                    </div>
                    <span id="badgeTargetStatus" class="neo-badge bg-slate-100 text-slate-700 text-[10px]">
                        Menunggu Kelas Asal
                    </span>
                </div>

                <!-- Dropdown Pilih Kelas Tujuan -->
                <div>
                    <label for="target_class_id" class="block font-heading font-bold text-xs text-black mb-1.5">
                        Pilih Rombel / Kelas Tujuan <span class="text-red-500">*</span>
                    </label>
                    <select id="target_class_id" name="target_class_id" required disabled onchange="handleTargetClassChange(this.value)" class="w-full px-3 py-2.5 neo-input text-sm bg-slate-100 font-medium cursor-not-allowed">
                        <option value="">-- Pilih Kelas Asal Terlebih Dahulu --</option>
                    </select>
                    <p class="text-[11px] text-slate-500 mt-1">
                        *Hanya menampilkan kelas dalam tahun ajaran yang sama dengan kelas asal.
                    </p>
                </div>

                <!-- Meta Info Kelas Tujuan -->
                <div id="targetClassInfo" class="hidden bg-[#E7F5FF] border-2 border-black p-3.5 space-y-2 rounded-sm text-xs">
                    <div class="font-heading font-black text-black text-sm flex items-center justify-between">
                        <span id="targetClassName">Kelas Tujuan</span>
                        <span id="targetClassLevel" class="neo-badge bg-[#5294FF] text-white text-[10px]">-</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 pt-1 border-t border-blue-200">
                        <div>
                            <div class="text-[10px] uppercase font-bold text-slate-500">Wali Kelas</div>
                            <div id="targetHomeroom" class="font-bold text-black truncate">-</div>
                        </div>
                        <div>
                            <div class="text-[10px] uppercase font-bold text-slate-500">Siswa Saat Ini</div>
                            <div id="targetStudentsCount" class="font-mono font-bold text-black">0 Siswa</div>
                        </div>
                    </div>
                    <div class="bg-white p-2 border border-black text-[11px] flex items-center justify-between font-bold">
                        <span class="text-blue-900">Proyeksi Setelah Mutasi:</span>
                        <span id="targetProjectedCount" class="font-mono bg-[#D3F9D8] px-2 py-0.5 border border-black text-black">0 Siswa</span>
                    </div>
                </div>

                <!-- Preview Siswa yang Terpilih untuk Dipindahkan -->
                <div class="space-y-2 pt-1">
                    <label class="block font-heading font-bold text-xs text-black flex items-center justify-between">
                        <span>Ringkasan Siswa Terpilih:</span>
                        <span id="previewSelectedCount" class="text-[11px] font-mono text-slate-600">0 dipilih</span>
                    </label>
                    <div id="selectedChipsContainer" class="border-2 border-black bg-slate-50 p-3 min-h-[100px] max-h-[160px] overflow-y-auto space-y-1.5 rounded-sm">
                        <p id="emptySelectedNotice" class="text-center text-slate-400 text-xs italic py-4">
                            Belum ada siswa yang dicentang di panel kiri.
                        </p>
                        <div id="selectedChipsList" class="flex flex-wrap gap-1.5">
                            <!-- Populated via JS -->
                        </div>
                    </div>
                </div>

                <!-- Warning Callout -->
                <div class="bg-[#FFF9DB] border-2 border-black p-3 text-[11px] space-y-1">
                    <div class="font-black text-black flex items-center gap-1.5">
                        <span>⚠️</span> Catatan Mutasi:
                    </div>
                    <p class="text-slate-700 leading-relaxed">
                        Data riwayat presensi siswa tetap tersimpan. ID kelas siswa akan otomatis diperbarui ke kelas tujuan.
                    </p>
                </div>

                <!-- CTA Action Button -->
                <div class="pt-2">
                    <button type="button" id="btnSubmitTransfer" onclick="confirmAndSubmitTransfer()" disabled class="w-full neo-btn bg-[#20C997] hover:bg-[#12b886] disabled:bg-slate-300 disabled:text-slate-500 disabled:cursor-not-allowed text-black font-heading font-black py-3.5 px-4 text-xs uppercase tracking-wider flex items-center justify-center gap-2 cursor-pointer shadow-[3px_3px_0px_0px_#000000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all">
                        <span>⇄</span>
                        <span id="btnSubmitText">Pindahkan Siswa Terpilih</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    let currentStudents = [];
    let currentTargetClasses = [];
    let selectedStudentIds = new Set();
    let currentSourceClass = null;

    document.addEventListener('DOMContentLoaded', () => {
        const sourceSelect = document.getElementById('source_class_id');
        if (sourceSelect && sourceSelect.value) {
            handleSourceClassChange(sourceSelect.value);
        }
    });

    async function handleSourceClassChange(classId) {
        const targetSelect = document.getElementById('target_class_id');
        const sourceInfo = document.getElementById('sourceClassInfo');
        const studentControls = document.getElementById('studentControls');
        const initialState = document.getElementById('initialStateNotice');
        const loadingState = document.getElementById('loadingState');
        const emptyNotice = document.getElementById('emptyStudentsNotice');
        const studentsTable = document.getElementById('studentsTable');
        const badgeSourceStatus = document.getElementById('badgeSourceStatus');
        const badgeTargetStatus = document.getElementById('badgeTargetStatus');

        // Reset selections
        selectedStudentIds.clear();
        updateSelectedUI();

        if (!classId) {
            sourceInfo.classList.add('hidden');
            studentControls.classList.add('hidden');
            studentsTable.classList.add('hidden');
            emptyNotice.classList.add('hidden');
            initialState.classList.remove('hidden');
            loadingState.classList.add('hidden');
            
            targetSelect.innerHTML = '<option value="">-- Pilih Kelas Asal Terlebih Dahulu --</option>';
            targetSelect.disabled = true;
            targetSelect.classList.add('cursor-not-allowed', 'bg-slate-100');
            badgeSourceStatus.textContent = 'Pilih Kelas';
            badgeTargetStatus.textContent = 'Menunggu Kelas Asal';
            hideTargetClassPreview();
            return;
        }

        // Show loading state
        initialState.classList.add('hidden');
        studentsTable.classList.add('hidden');
        emptyNotice.classList.add('hidden');
        loadingState.classList.remove('hidden');
        badgeSourceStatus.textContent = 'Memuat Data...';
        badgeSourceStatus.className = 'neo-badge bg-[#FFD43B] text-black text-[10px]';

        try {
            const url = `{{ url('admin/classes') }}/${classId}/transfer-data`;
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error('Gagal mengambil data kelas.');
            }

            const data = await response.json();
            currentSourceClass = data.source_class;
            currentStudents = data.students || [];
            currentTargetClasses = data.target_classes || [];

            // Update Source Info Card
            document.getElementById('srcHomeroom').textContent = currentSourceClass.homeroom_teacher;
            document.getElementById('srcAcademicYear').textContent = currentSourceClass.academic_year;
            document.getElementById('srcTotalStudents').textContent = currentStudents.length;
            sourceInfo.classList.remove('hidden');

            badgeSourceStatus.textContent = `${currentStudents.length} Siswa Terdaftar`;
            badgeSourceStatus.className = 'neo-badge bg-[#D3F9D8] text-green-950 text-[10px]';

            // Populate Target Classes Dropdown
            targetSelect.innerHTML = '<option value="">-- Pilih Kelas Tujuan --</option>';
            if (currentTargetClasses.length === 0) {
                targetSelect.innerHTML += '<option value="" disabled>(Tidak ada kelas lain di tahun ajaran yang sama)</option>';
                badgeTargetStatus.textContent = 'Tidak Ada Kelas Tujuan';
                badgeTargetStatus.className = 'neo-badge bg-[#FFE3E3] text-rose-950 text-[10px]';
            } else {
                currentTargetClasses.forEach(tc => {
                    const opt = document.createElement('option');
                    opt.value = tc.id;
                    opt.textContent = `${tc.name} (${tc.level}) - [Saat ini: ${tc.students_count} Siswa]`;
                    targetSelect.appendChild(opt);
                });
                targetSelect.disabled = false;
                targetSelect.classList.remove('cursor-not-allowed', 'bg-slate-100');
                targetSelect.classList.add('bg-slate-50');
                badgeTargetStatus.textContent = `${currentTargetClasses.length} Opsi Kelas`;
                badgeTargetStatus.className = 'neo-badge bg-[#E7F5FF] text-blue-900 text-[10px]';
            }

            // Render Students List
            loadingState.classList.add('hidden');
            if (currentStudents.length === 0) {
                emptyNotice.classList.remove('hidden');
                studentControls.classList.add('hidden');
            } else {
                studentControls.classList.remove('hidden');
                document.getElementById('visibleCounterText').textContent = currentStudents.length;
                renderStudentsTable(currentStudents);
                studentsTable.classList.remove('hidden');
            }

        } catch (error) {
            loadingState.classList.add('hidden');
            emptyNotice.classList.remove('hidden');
            emptyNotice.querySelector('p').textContent = 'Terjadi kesalahan saat memuat data: ' + error.message;
            badgeSourceStatus.textContent = 'Error';
            badgeSourceStatus.className = 'neo-badge bg-[#FF6B6B] text-white text-[10px]';
        }
    }

    function renderStudentsTable(students) {
        const tbody = document.getElementById('studentsTableBody');
        tbody.innerHTML = '';

        students.forEach((student) => {
            const tr = document.createElement('tr');
            tr.id = `row-student-${student.id}`;
            tr.className = 'hover:bg-[#FFF9DB]/40 cursor-pointer transition-colors border-b border-black';
            tr.onclick = (e) => {
                if (e.target.type !== 'checkbox') {
                    const cb = tr.querySelector('.student-checkbox');
                    if (cb) {
                        cb.checked = !cb.checked;
                        toggleStudentSelection(student.id, cb.checked);
                    }
                }
            };

            const isChecked = selectedStudentIds.has(student.id);

            tr.innerHTML = `
                <td class="p-2.5 text-center border-r border-black" onclick="event.stopPropagation()">
                    <input type="checkbox" 
                           name="student_ids[]" 
                           value="${student.id}" 
                           class="student-checkbox w-4 h-4 border-2 border-black rounded cursor-pointer accent-black" 
                           ${isChecked ? 'checked' : ''} 
                           onchange="toggleStudentSelection(${student.id}, this.checked)">
                </td>
                <td class="p-2.5 font-bold text-black border-r border-black">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black border border-black shrink-0 ${student.gender === 'L' ? 'bg-[#D0EBFF] text-blue-900' : 'bg-[#FCC2D7] text-rose-900'}">
                            ${student.gender || '?'}
                        </span>
                        <span class="truncate max-w-[200px] sm:max-w-xs">${escapeHtml(student.name)}</span>
                    </div>
                </td>
                <td class="p-2.5 border-r border-black font-mono text-[11px] text-slate-700">
                    <div>${escapeHtml(student.nis)}</div>
                    <div class="text-[10px] text-slate-400">${escapeHtml(student.nisn)}</div>
                </td>
                <td class="p-2.5 text-center font-bold font-mono">
                    <span class="px-1.5 py-0.5 border border-black text-[10px] ${student.gender === 'L' ? 'bg-[#E7F5FF] text-blue-900' : 'bg-[#FFF0F6] text-rose-900'}">
                        ${student.gender === 'L' ? 'Laki' : (student.gender === 'P' ? 'Peremp' : '-')}
                    </span>
                </td>
            `;

            tbody.appendChild(tr);
        });

        updateSelectAllState();
    }

    function filterStudents() {
        const query = document.getElementById('searchStudent').value.toLowerCase().trim();
        const filtered = currentStudents.filter(s => {
            return s.name.toLowerCase().includes(query) ||
                   s.nis.toLowerCase().includes(query) ||
                   s.nisn.toLowerCase().includes(query);
        });

        document.getElementById('visibleCounterText').textContent = filtered.length;
        renderStudentsTable(filtered);
    }

    function toggleStudentSelection(studentId, isChecked) {
        if (isChecked) {
            selectedStudentIds.add(studentId);
        } else {
            selectedStudentIds.delete(studentId);
        }
        updateSelectedUI();
        updateSelectAllState();
    }

    function toggleSelectAll(checked) {
        const visibleCheckboxes = document.querySelectorAll('.student-checkbox');
        visibleCheckboxes.forEach(cb => {
            cb.checked = checked;
            const sId = parseInt(cb.value);
            if (checked) {
                selectedStudentIds.add(sId);
            } else {
                selectedStudentIds.delete(sId);
            }
        });
        updateSelectedUI();
    }

    function clearSelection() {
        selectedStudentIds.clear();
        document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = false);
        const selectAll = document.getElementById('selectAllCheckbox');
        if (selectAll) selectAll.checked = false;
        updateSelectedUI();
    }

    function updateSelectAllState() {
        const visibleCheckboxes = document.querySelectorAll('.student-checkbox');
        const selectAll = document.getElementById('selectAllCheckbox');
        if (!selectAll) return;

        if (visibleCheckboxes.length === 0) {
            selectAll.checked = false;
            selectAll.indeterminate = false;
            return;
        }

        let checkedCount = 0;
        visibleCheckboxes.forEach(cb => {
            if (cb.checked) checkedCount++;
        });

        selectAll.checked = (checkedCount === visibleCheckboxes.length);
        selectAll.indeterminate = (checkedCount > 0 && checkedCount < visibleCheckboxes.length);
    }

    function updateSelectedUI() {
        const count = selectedStudentIds.size;
        document.getElementById('selectedCounterText').textContent = count;
        document.getElementById('previewSelectedCount').textContent = `${count} dipilih`;

        // Update selected chips list
        const chipsList = document.getElementById('selectedChipsList');
        const emptyNotice = document.getElementById('emptySelectedNotice');

        chipsList.innerHTML = '';
        if (count === 0) {
            emptyNotice.classList.remove('hidden');
        } else {
            emptyNotice.classList.add('hidden');
            selectedStudentIds.forEach(id => {
                const student = currentStudents.find(s => s.id === id);
                if (student) {
                    const chip = document.createElement('span');
                    chip.className = 'inline-flex items-center gap-1 px-2 py-0.5 bg-[#FFF9DB] border border-black text-black font-bold text-[11px] rounded-sm';
                    chip.innerHTML = `
                        <span class="truncate max-w-[140px]">${escapeHtml(student.name)}</span>
                        <button type="button" onclick="removeSelectedStudent(${student.id})" class="text-red-600 hover:text-black font-black ml-0.5 cursor-pointer">×</button>
                    `;
                    chipsList.appendChild(chip);
                }
            });
        }

        // Update target preview projected count
        updateTargetProjectedCount();
        checkSubmitButtonState();
    }

    function removeSelectedStudent(studentId) {
        selectedStudentIds.delete(studentId);
        const cb = document.querySelector(`.student-checkbox[value="${studentId}"]`);
        if (cb) cb.checked = false;
        updateSelectedUI();
        updateSelectAllState();
    }

    function handleTargetClassChange(targetId) {
        if (!targetId) {
            hideTargetClassPreview();
            checkSubmitButtonState();
            return;
        }

        const targetClass = currentTargetClasses.find(c => String(c.id) === String(targetId));
        if (!targetClass) return;

        document.getElementById('targetClassName').textContent = targetClass.name;
        document.getElementById('targetClassLevel').textContent = targetClass.level;
        document.getElementById('targetHomeroom').textContent = targetClass.homeroom_teacher;
        document.getElementById('targetStudentsCount').textContent = `${targetClass.students_count} Siswa`;
        document.getElementById('targetClassInfo').classList.remove('hidden');

        updateTargetProjectedCount();
        checkSubmitButtonState();
    }

    function hideTargetClassPreview() {
        document.getElementById('targetClassInfo').classList.add('hidden');
    }

    function updateTargetProjectedCount() {
        const targetId = document.getElementById('target_class_id').value;
        const projectedElem = document.getElementById('targetProjectedCount');
        if (!targetId || !projectedElem) return;

        const targetClass = currentTargetClasses.find(c => String(c.id) === String(targetId));
        if (!targetClass) return;

        const currentCount = targetClass.students_count;
        const addCount = selectedStudentIds.size;
        const totalProjected = currentCount + addCount;
        projectedElem.textContent = `${totalProjected} Siswa (+${addCount})`;
    }

    function checkSubmitButtonState() {
        const targetId = document.getElementById('target_class_id').value;
        const btn = document.getElementById('btnSubmitTransfer');
        const btnText = document.getElementById('btnSubmitText');
        const count = selectedStudentIds.size;

        if (count > 0 && targetId) {
            const targetClass = currentTargetClasses.find(c => String(c.id) === String(targetId));
            const targetName = targetClass ? targetClass.name : 'Kelas Tujuan';
            btn.disabled = false;
            btnText.textContent = `Pindahkan ${count} Siswa ke ${targetName} ➡️`;
        } else {
            btn.disabled = true;
            if (count === 0) {
                btnText.textContent = 'Pilih Siswa Terlebih Dahulu';
            } else {
                btnText.textContent = 'Pilih Kelas Tujuan';
            }
        }
    }

    function confirmAndSubmitTransfer() {
        const targetSelect = document.getElementById('target_class_id');
        const targetId = targetSelect.value;
        const count = selectedStudentIds.size;

        if (count === 0) {
            alert('Silakan pilih minimal 1 siswa yang ingin dipindahkan.');
            return;
        }

        if (!targetId) {
            alert('Silakan tentukan kelas tujuan terlebih dahulu.');
            return;
        }

        const sourceName = currentSourceClass ? currentSourceClass.name : 'Kelas Asal';
        const targetClass = currentTargetClasses.find(c => String(c.id) === String(targetId));
        const targetName = targetClass ? targetClass.name : 'Kelas Tujuan';

        const message = `Pindahkan ${count} siswa dari kelas "${sourceName}" ke kelas "${targetName}"?`;

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Konfirmasi Pindah Kelas',
                text: message,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#20C997',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Pindahkan Sekarang!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('transferForm').submit();
                }
            });
        } else {
            if (confirm(message)) {
                document.getElementById('transferForm').submit();
            }
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
    }
</script>
@endpush
