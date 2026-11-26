@push('scripts')
<script>
    function getActiveTeacherReturnQuery() {
        const currentParams = new URLSearchParams(window.location.search);
        const currentQs = typeof window.buildTeacherIndexQueryString === 'function'
            ? window.buildTeacherIndexQueryString({
                page: currentParams.get('page') || String({{ $teachers->currentPage() }}),
                search: currentParams.get('search') ?? @json($search ?? ''),
                sort: currentParams.get('sort') || @json($sort ?? 'name_asc'),
            })
            : '';

        if (currentQs !== '') {
            return currentQs;
        }

        try {
            const raw = localStorage.getItem(window.TEACHER_INDEX_STORAGE_KEY);
            if (raw && typeof window.buildTeacherIndexQueryString === 'function') {
                return window.buildTeacherIndexQueryString(JSON.parse(raw));
            }
        } catch (e) {}

        return '';
    }

    function syncTeacherReturnQueryInputs() {
        const qs = getActiveTeacherReturnQuery();
        document.querySelectorAll('.teacher-return-query').forEach(input => {
            input.value = qs;
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        syncTeacherReturnQueryInputs();

        document.querySelectorAll('.teacher-return-query').forEach(input => {
            const form = input.closest('form');
            if (form) {
                form.addEventListener('submit', syncTeacherReturnQueryInputs);
            }
        });

        const filterForm = document.getElementById('teacherFilterForm');
        if (filterForm) {
            filterForm.addEventListener('submit', function () {
                const searchVal = filterForm.querySelector('input[name="search"]')?.value || '';
                const sortVal = filterForm.querySelector('select[name="sort"]')?.value || 'name_asc';
                if (typeof window.saveTeacherIndexState === 'function') {
                    window.saveTeacherIndexState({ page: '1', search: searchVal, sort: sortVal });
                }
            });
        }

        const paginationContainer = document.getElementById('teachersPaginationContainer');
        if (paginationContainer) {
            paginationContainer.addEventListener('click', function (event) {
                const link = event.target.closest('a[href]');
                if (!link) return;
                try {
                    const url = new URL(link.href, window.location.origin);
                    if (typeof window.saveTeacherIndexState === 'function') {
                        window.saveTeacherIndexState({
                            page: url.searchParams.get('page') || '1',
                            search: url.searchParams.get('search') || '',
                            sort: url.searchParams.get('sort') || 'name_asc',
                        });
                    }
                } catch (e) {}
            });
        }
    });

    // Tutup modal aktif saat menekan tombol Escape
    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;
        const modalIds = [
            'importTeacherModal',
            'createTeacherModal',
            'assignmentTeacherModal',
            'savingsOfficerModal',
            'impersonationTeacherModal'
        ];
        modalIds.forEach(id => {
            const modal = document.getElementById(id);
            if (modal && !modal.classList.contains('hidden')) {
                if (id === 'impersonationTeacherModal') {
                    cancelTeacherImpersonation();
                } else {
                    closeModal(id);
                }
            }
        });
    });

    let pendingImpersonationForm = null;

    function confirmTeacherImpersonation(form) {
        if (!form) return false;

        pendingImpersonationForm = form;
        const nameElement = document.getElementById('impersonation_teacher_name');
        if (nameElement) {
            nameElement.textContent = form.dataset.teacherName || 'Guru';
        }

        openModal('impersonationTeacherModal');
        return false;
    }

    function submitTeacherImpersonation() {
        if (!pendingImpersonationForm) return;

        const form = pendingImpersonationForm;
        pendingImpersonationForm = null;
        closeModal('impersonationTeacherModal');
        form.submit();
    }

    function cancelTeacherImpersonation() {
        pendingImpersonationForm = null;
        closeModal('impersonationTeacherModal');
    }

    function togglePasswordVisibility(inputId, iconContainerId) {
        const input = document.getElementById(inputId);
        const iconContainer = document.getElementById(iconContainerId);
        if (!input || !iconContainer) return;

        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';

        if (isPassword) {
            iconContainer.innerHTML = `
                <svg class="h-4 w-4 text-black" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                </svg>
            `;
        } else {
            iconContainer.innerHTML = `
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            `;
        }
    }

    // Data Master untuk modal Atur Mengajar
    const availableSubjects = @json($subjects ?? []);
    const availableClasses = @json($classes ?? []);
    let assignmentRowIndex = 0;
    let currentTeacherHadRestrictions = false;
    let currentTeacherHomeroomClassIds = [];
    let currentActiveTeacherId = null;
    let currentActiveTeacherName = '';

    // Kelompokkan rombel berdasarkan tingkat/jenjang (Tingkat 1..6, atau Lainnya)
    function groupClassesByLevel(classes) {
        const groups = {};
        classes.forEach(c => {
            const match = c.name.match(/\d+/);
            const levelNum = match ? match[0] : (c.level || 'Lainnya');
            const key = `Kelas ${levelNum}`;
            if (!groups[key]) groups[key] = [];
            groups[key].push(c);
        });
        return groups;
    }

    const groupedAvailableClasses = groupClassesByLevel(availableClasses);

    function toggleAssignmentMode() {
        const isRestricted = document.getElementById('mode_restricted').checked;
        const container = document.getElementById('restricted_container');
        const warning = document.getElementById('assignment_transition_warning');

        if (isRestricted) {
            container.classList.remove('hidden');
            if (warning) warning.classList.add('hidden');
            if (document.querySelectorAll('.assignment-row-card').length === 0) {
                addAssignmentRow();
            }
        } else {
            container.classList.add('hidden');
            if (warning && currentTeacherHadRestrictions) {
                warning.classList.remove('hidden');
            }
        }
        updateLiveSummary();
    }

    function addAssignmentRow(existingData = null) {
        const container = document.getElementById('assignment_rows');
        const existingRows = document.querySelectorAll('.assignment-row-card');

        // Proteksi baris kosong: jangan buat baris baru jika baris paling atas belum memilih minimal 1 mapel
        if (!existingData && existingRows.length > 0) {
            const topRow = existingRows[0];
            const checkedSubs = topRow.querySelectorAll('input[name*="[subject_ids]"]:checked');
            if (checkedSubs.length === 0) {
                topRow.classList.add('ring-2', 'ring-rose-500');
                setTimeout(() => topRow.classList.remove('ring-2', 'ring-rose-500'), 1500);
                topRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
        }

        const idx = assignmentRowIndex++;

        // Ambil data aktif dari existingData jika ada
        const activeSubjectIds = existingData
            ? (existingData.subject_ids || (existingData.subject_id ? [existingData.subject_id] : [])).map(id => parseInt(id))
            : [];
        const activeClassIds = (existingData && existingData.class_ids)
            ? existingData.class_ids.map(id => parseInt(id))
            : [];

        // Render Checkboxes Mata Pelajaran (Multi-Select Chips)
        let subjectChipsHtml = '';
        availableSubjects.forEach(s => {
            const isChecked = activeSubjectIds.includes(parseInt(s.id)) ? 'checked' : '';
            subjectChipsHtml += `
                <label class="subject-chip-${idx} flex cursor-pointer items-center gap-2 rounded-lg border-2 border-black/20 bg-white px-2 py-1.5 text-xs transition-all hover:border-black hover:bg-[#FFF3BF]/60 has-[:checked]:border-black has-[:checked]:bg-[#FFD43B] has-[:checked]:shadow-[2px_2px_0px_0px_#000]"
                       data-subject-search="${s.name.toLowerCase()} ${(s.code || '').toLowerCase()}">
                    <input type="checkbox"
                           name="assignments[${idx}][subject_ids][]"
                           value="${s.id}"
                           ${isChecked}
                           onchange="updateSubjectRowSummary(${idx})"
                           class="subject-checkbox-${idx} h-3.5 w-3.5 rounded border-2 border-black text-black focus:ring-0">
                    <span class="truncate font-extrabold text-black" title="${s.name}">
                        ${s.name} <span class="text-[10px] text-slate-500 font-bold">(${s.code || '-'})</span>
                    </span>
                </label>
            `;
        });

        // Render Checkboxes Rombel yang dikelompokkan per Tingkat
        let groupedHtml = '';
        let levelButtons = '';

        for (const [lvlKey, classList] of Object.entries(groupedAvailableClasses)) {
            levelButtons += `
                <button type="button"
                        onclick="toggleClassesForLevel(${idx}, '${lvlKey}')"
                        class="cursor-pointer rounded-md border border-black bg-white px-2 py-0.5 text-[10px] font-black text-black shadow-[1px_1px_0px_0px_#000] hover:bg-[#E5DBFF] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none"
                        title="Pilih / batalkan seluruh ${lvlKey}">
                    ${lvlKey}
                </button>
            `;

            let classBoxes = '';
            classList.forEach(c => {
                const isChecked = activeClassIds.includes(parseInt(c.id)) ? 'checked' : '';
                classBoxes += `
                    <label class="flex cursor-pointer items-center gap-2 rounded-lg border-2 border-black/20 bg-white px-2 py-1.5 text-xs transition hover:border-black hover:bg-[#E5DBFF]/50 has-[:checked]:border-black has-[:checked]:bg-[#E5DBFF] has-[:checked]:shadow-[2px_2px_0px_0px_#000]">
                        <input type="checkbox"
                               name="assignments[${idx}][class_ids][]"
                               value="${c.id}"
                               data-level-key="${lvlKey}"
                               ${isChecked}
                               onchange="updateRowSummary(${idx})"
                               class="class-checkbox-${idx} h-3.5 w-3.5 rounded border-2 border-black text-black focus:ring-0">
                        <span class="truncate font-extrabold text-black">${c.name}</span>
                    </label>
                `;
            });

            groupedHtml += `
                <div class="space-y-1">
                    <div class="flex items-center justify-between text-[10.5px] font-black uppercase text-slate-700">
                        <span>${lvlKey}</span>
                        <span class="text-[9.5px] font-bold text-slate-500">${classList.length} Kelas</span>
                    </div>
                    <div class="grid grid-cols-2 gap-1.5 sm:grid-cols-3">
                        ${classBoxes}
                    </div>
                </div>
            `;
        }

        const rowCard = document.createElement('div');
        rowCard.className = 'assignment-row-card space-y-3 rounded-xl border-2 border-black bg-[#FFFDF5] p-4 shadow-[3px_3px_0px_0px_#000] transition-all';
        rowCard.id = `assignment_row_${idx}`;
        rowCard.innerHTML = `
            <!-- Header Kartu: Label & Aksi -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b-2 border-black/10 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-md border-2 border-black bg-[#FFD43B] text-xs font-black shadow-[1px_1px_0px_0px_#000]">
                        📖
                    </span>
                    <label class="text-xs font-extrabold uppercase tracking-wide text-black">Mata Pelajaran yang Diajar</label>
                    <span id="subject_badge_count_${idx}" class="inline-flex items-center rounded-md border border-black bg-[#FFE3E3] px-2 py-0.5 text-[10.5px] font-black text-rose-800">
                        0 Mapel
                    </span>
                </div>
                <div class="flex items-center gap-1.5">
                    <button type="button"
                            onclick="copyClassesFromAnotherRow(${idx})"
                            class="copy-classes-btn inline-flex cursor-pointer items-center gap-1 rounded-lg border-2 border-black bg-[#E7F5FF] px-2.5 py-1 text-xs font-extrabold text-black shadow-[2px_2px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-[#D0EBFF] hover:shadow-[3px_3px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none"
                            title="Salin rombel dari baris penugasan lainnya">
                        <span>📋</span>
                        <span>Salin Rombel</span>
                    </button>
                    <button type="button"
                            onclick="removeAssignmentRow(${idx})"
                            class="inline-flex cursor-pointer items-center gap-1 rounded-lg border-2 border-black bg-[#FFE3E3] px-2.5 py-1 text-xs font-extrabold text-black shadow-[2px_2px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-[#FF6B6B] hover:text-white hover:shadow-[3px_3px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none"
                            title="Hapus Kartu Penugasan Ini">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span>Hapus</span>
                    </button>
                </div>
            </div>

            <!-- Area Pemilihan Multi-Mapel -->
            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-bold text-slate-600">Pilih Mapel:</span>
                        <button type="button" onclick="toggleAllSubjectsInRow(${idx}, true)" class="cursor-pointer font-extrabold text-black underline decoration-2 hover:text-blue-700">Pilih Semua</button>
                        <span class="font-black text-black/40">·</span>
                        <button type="button" onclick="toggleAllSubjectsInRow(${idx}, false)" class="cursor-pointer font-bold text-slate-600 hover:text-black hover:underline">Batalkan</button>
                    </div>
                    ${availableSubjects.length > 6 ? `
                    <div>
                        <input type="text"
                               placeholder="Cari mapel..."
                               oninput="filterSubjectsInRow(${idx}, this.value)"
                               class="rounded-md border border-black bg-white px-2 py-0.5 text-[11px] font-semibold text-black placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-black">
                    </div>
                    ` : ''}
                </div>
                <div id="subject_chips_container_${idx}" class="grid grid-cols-2 gap-1.5 max-h-36 overflow-y-auto rounded-lg border-2 border-black bg-slate-50 p-2 sm:grid-cols-3 md:grid-cols-4">
                    ${subjectChipsHtml}
                </div>
            </div>

            <!-- Area Rombel -->
            <div class="space-y-2 border-t-2 border-black/10 pt-2.5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-extrabold text-black">Kelas / Rombel yang Diajar</label>
                        <span id="badge_count_${idx}" class="inline-flex items-center rounded-md border border-black bg-[#FFF9DB] px-2 py-0.5 text-[10.5px] font-black text-black">
                            0 Rombel
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <button type="button" onclick="toggleAllClasses(${idx}, true)" class="cursor-pointer font-extrabold text-black underline decoration-2 hover:text-blue-700">Pilih Semua</button>
                        <span class="font-black text-black/40">·</span>
                        <button type="button" onclick="toggleAllClasses(${idx}, false)" class="cursor-pointer font-bold text-slate-600 hover:text-black hover:underline">Batalkan</button>
                        ${currentTeacherHomeroomClassIds && currentTeacherHomeroomClassIds.length > 0 ? `
                        <span class="font-black text-black/40">·</span>
                        <button type="button" onclick="applyHomeroomToCard(${idx})" class="cursor-pointer font-extrabold text-[#5F3DC4] underline decoration-2 hover:text-black hover:bg-[#E5DBFF] rounded px-1" title="Centang rombel perwalian wali kelas pada kartu ini">
                            🎯 Rombel Wali Kelas
                        </button>
                        ` : ''}
                    </div>
                </div>

                <!-- Tombol Pintas Tingkat -->
                <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                    <span class="text-[10px] font-bold text-slate-600">Pilih Tingkat:</span>
                    ${levelButtons}
                </div>

                <!-- Daftar Checkbox Rombel per Tingkat -->
                <div class="max-h-48 space-y-3 overflow-y-auto rounded-lg border-2 border-black bg-slate-100 p-3">
                    ${groupedHtml}
                </div>
            </div>
        `;

        if (!existingData) {
            // Prepend kartu baru di paling atas
            container.prepend(rowCard);
            rowCard.classList.add('ring-4', 'ring-[#4DABF7]');
            setTimeout(() => rowCard.classList.remove('ring-4', 'ring-[#4DABF7]'), 1200);
            rowCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            // Append data awal agar urutan rapi sesuai yang tersimpan
            container.appendChild(rowCard);
        }

        updateSubjectRowSummary(idx);
        updateRowSummary(idx);
        updateLiveSummary();
    }

    function removeAssignmentRow(idx) {
        const el = document.getElementById(`assignment_row_${idx}`);
        if (el) el.remove();
        updateLiveSummary();
    }

    function toggleAllSubjectsInRow(rowIdx, check) {
        document.querySelectorAll(`.subject-checkbox-${rowIdx}`).forEach(cb => {
            // Hanya toggle checkbox yang terlihat jika sedang difilter
            const label = cb.closest('label');
            if (!label || !label.classList.contains('hidden')) {
                cb.checked = check;
            }
        });
        updateSubjectRowSummary(rowIdx);
        updateLiveSummary();
    }

    function filterSubjectsInRow(rowIdx, query) {
        const q = (query || '').toLowerCase().trim();
        const chips = document.querySelectorAll(`.subject-chip-${rowIdx}`);
        chips.forEach(chip => {
            const searchKey = chip.getAttribute('data-subject-search') || '';
            if (!q || searchKey.includes(q)) {
                chip.classList.remove('hidden');
            } else {
                chip.classList.add('hidden');
            }
        });
    }

    function updateSubjectRowSummary(rowIdx) {
        const checkedCount = document.querySelectorAll(`.subject-checkbox-${rowIdx}:checked`).length;
        const badge = document.getElementById(`subject_badge_count_${rowIdx}`);
        if (badge) {
            badge.textContent = `${checkedCount} Mapel`;
            if (checkedCount === 0) {
                badge.className = 'inline-flex items-center rounded-md border border-black bg-[#FFE3E3] px-2 py-0.5 text-[10.5px] font-black text-rose-800';
            } else {
                badge.className = 'inline-flex items-center rounded-md border border-black bg-[#D3F9D8] px-2 py-0.5 text-[10.5px] font-black text-emerald-900';
            }
        }
        updateLiveSummary();
    }

    function toggleClassesForLevel(rowIdx, levelKey) {
        const checkboxes = Array.from(document.querySelectorAll(`.class-checkbox-${rowIdx}[data-level-key="${levelKey}"]`));
        if (checkboxes.length === 0) return;
        const allChecked = checkboxes.every(cb => cb.checked);
        checkboxes.forEach(cb => cb.checked = !allChecked);
        updateRowSummary(rowIdx);
        updateLiveSummary();
    }

    function toggleAllClasses(rowIdx, check) {
        document.querySelectorAll(`.class-checkbox-${rowIdx}`).forEach(cb => cb.checked = check);
        updateRowSummary(rowIdx);
        updateLiveSummary();
    }

    function updateRowSummary(rowIdx) {
        const checkedCount = document.querySelectorAll(`.class-checkbox-${rowIdx}:checked`).length;
        const totalCount = document.querySelectorAll(`.class-checkbox-${rowIdx}`).length;
        const badge = document.getElementById(`badge_count_${rowIdx}`);
        if (badge) {
            badge.textContent = `${checkedCount} dari ${totalCount} Rombel`;
            if (checkedCount === 0) {
                badge.className = 'inline-flex items-center rounded-md border border-black bg-[#FFE3E3] px-2 py-0.5 text-[10.5px] font-black text-rose-800';
            } else {
                badge.className = 'inline-flex items-center rounded-md border border-black bg-[#D3F9D8] px-2 py-0.5 text-[10.5px] font-black text-emerald-900';
            }
        }
        updateLiveSummary();
    }

    function copyClassesFromAnotherRow(idx) {
        const currentCard = document.getElementById(`assignment_row_${idx}`);
        const allCards = Array.from(document.querySelectorAll('.assignment-row-card'));
        const otherCards = allCards.filter(c => c !== currentCard);

        if (otherCards.length === 0) {
            alert('Belum ada baris penugasan lain untuk disalin rombelnya.');
            return;
        }

        const currentIndex = allCards.indexOf(currentCard);
        // Prioritaskan kartu di bawahnya, jika tidak ada ambil kartu pertama
        const sourceCard = (currentIndex < allCards.length - 1) ? allCards[currentIndex + 1] : otherCards[0];
        const sourceCbs = sourceCard.querySelectorAll('input[name*="[class_ids]"]:checked');
        const sourceIds = Array.from(sourceCbs).map(cb => cb.value);

        if (sourceIds.length === 0) {
            alert('Baris penugasan lain belum memilih rombel kelas.');
            return;
        }

        currentCard.querySelectorAll(`.class-checkbox-${idx}`).forEach(cb => {
            cb.checked = sourceIds.includes(cb.value);
        });

        updateRowSummary(idx);
        updateLiveSummary();
    }

    function onHomeroomClassSelectChanged() {
        const select = document.getElementById('teacher_homeroom_class_select');
        const selectedValue = select.value;
        const selectedOption = select.options[select.selectedIndex];
        const className = selectedOption ? selectedOption.getAttribute('data-class-name') : '';

        const textEl = document.getElementById('assignment_homeroom_text');
        const badgeEl = document.getElementById('assignment_homeroom_badge');

        if (selectedValue) {
            currentTeacherHomeroomClassIds = [parseInt(selectedValue)];
            if (textEl) textEl.textContent = `Wali Kelas di ${className}`;
            if (badgeEl) badgeEl.classList.remove('hidden');

            // Otomatis terapkan rombel terpilih ke kartu penugasan
            applyHomeroomShortcut();
        } else {
            currentTeacherHomeroomClassIds = [];
            if (textEl) textEl.textContent = 'Guru Mata Pelajaran (Bukan Wali Kelas)';
            if (badgeEl) badgeEl.classList.add('hidden');
            updateLiveSummary();
        }
    }

    function applyHomeroomShortcut() {
        const select = document.getElementById('teacher_homeroom_class_select');

        // Jika belum ada rombel wali kelas yang dipilih, sorot select dropdown
        if (!currentTeacherHomeroomClassIds || currentTeacherHomeroomClassIds.length === 0) {
            if (select) {
                select.focus();
                select.classList.add('ring-4', 'ring-[#FF6B6B]');
                setTimeout(() => select.classList.remove('ring-4', 'ring-[#FF6B6B]'), 1500);
            }
            alert('Guru ini belum memiliki rombel perwalian. Silakan pilih kelas pada pilihan "Tetapkan" terlebih dahulu.');
            return;
        }

        // 1. Pastikan beralih ke mode restricted
        const modeRestricted = document.getElementById('mode_restricted');
        if (modeRestricted) {
            modeRestricted.checked = true;
        }
        toggleAssignmentMode();

        // 2. Pastikan minimal ada 1 kartu penugasan
        let rows = document.querySelectorAll('.assignment-row-card');
        if (rows.length === 0) {
            addAssignmentRow();
            rows = document.querySelectorAll('.assignment-row-card');
        }

        const targetCard = rows[0];
        if (!targetCard) return;

        const rowId = targetCard.id;
        const idx = rowId.replace('assignment_row_', '');

        applyHomeroomToCard(idx);

        // Feedback visual pada tombol header shortcut
        const btn = document.getElementById('assignment_apply_homeroom_btn');
        if (btn) {
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<span>✅</span><span>Rombel Diterapkan!</span>';
            btn.classList.remove('bg-[#FFD43B]', 'hover:bg-[#FCC419]');
            btn.classList.add('bg-[#20C997]', 'hover:bg-[#12B886]');

            setTimeout(() => {
                btn.innerHTML = originalHtml;
                btn.classList.remove('bg-[#20C997]', 'hover:bg-[#12B886]');
                btn.classList.add('bg-[#FFD43B]', 'hover:bg-[#FCC419]');
            }, 1800);
        }
    }

    function applyHomeroomToCard(idx) {
        if (!currentTeacherHomeroomClassIds || currentTeacherHomeroomClassIds.length === 0) {
            alert('Guru ini belum ditugaskan sebagai wali kelas di rombel manapun.');
            return;
        }

        const card = document.getElementById(`assignment_row_${idx}`);
        if (!card) return;

        let firstCheckedCb = null;
        currentTeacherHomeroomClassIds.forEach(id => {
            const cb = card.querySelector(`.class-checkbox-${idx}[value="${id}"]`);
            if (cb) {
                cb.checked = true;
                cb.dispatchEvent(new Event('change', { bubbles: true }));
                if (!firstCheckedCb) firstCheckedCb = cb;
            }
        });

        updateRowSummary(idx);
        updateLiveSummary();

        // Sorot kartu penugasan
        card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        card.classList.add('ring-4', 'ring-[#20C997]');
        setTimeout(() => card.classList.remove('ring-4', 'ring-[#20C997]'), 1500);

        // Scroll langsung ke checkbox rombel yang dicentang di dalam kotak checklist
        if (firstCheckedCb) {
            const label = firstCheckedCb.closest('label');
            if (label) {
                label.scrollIntoView({ behavior: 'smooth', block: 'center' });
                label.classList.add('ring-2', 'ring-black');
                setTimeout(() => label.classList.remove('ring-2', 'ring-black'), 1500);
            }
        }
    }

    function updateLiveSummary() {
        const isRestricted = document.getElementById('mode_restricted').checked;
        const summaryText = document.getElementById('summary_text');
        const summaryIcon = document.getElementById('summary_icon');

        if (!isRestricted) {
            if (summaryText) summaryText.textContent = 'Mode Bebas (Tanpa Batasan)';
            if (summaryIcon) summaryIcon.textContent = '🕊️';
            return;
        }

        const cards = document.querySelectorAll('.assignment-row-card');
        if (cards.length === 0) {
            if (summaryIcon) summaryIcon.textContent = '📚';
            if (summaryText) summaryText.textContent = 'Belum ada baris penugasan. Klik "Tambah Mapel" di atas.';
            return;
        }

        const checkedSubjects = document.querySelectorAll('#assignment_rows input[name*="[subject_ids]"]:checked');
        const uniqueSubjectIds = new Set(Array.from(checkedSubjects).map(cb => cb.value));

        const checkedClasses = document.querySelectorAll('#assignment_rows input[name*="[class_ids]"]:checked');
        const uniqueClassIds = new Set(Array.from(checkedClasses).map(cb => cb.value));

        let totalAssignments = 0;
        cards.forEach(card => {
            const subCount = card.querySelectorAll('input[name*="[subject_ids]"]:checked').length;
            const clsCount = card.querySelectorAll('input[name*="[class_ids]"]:checked').length;
            totalAssignments += (subCount * clsCount);
        });

        if (summaryIcon) summaryIcon.textContent = '📚';
        if (summaryText) {
            summaryText.textContent = `Ringkasan: ${uniqueSubjectIds.size} Mapel · ${uniqueClassIds.size} Rombel (Total ${totalAssignments} Penugasan)`;
        }
    }

    function handleAssignmentFormSubmit(e) {
        const isRestricted = document.getElementById('mode_restricted').checked;
        if (isRestricted) {
            const rows = document.querySelectorAll('.assignment-row-card');
            if (rows.length === 0) {
                e.preventDefault();
                alert('Tambahkan minimal satu baris penugasan pada mode Dibatasi Khusus atau beralih ke mode Bebas.');
                return false;
            }

            let hasError = false;
            let firstErrorElement = null;

            rows.forEach(card => {
                const subCbs = card.querySelectorAll('input[name*="[subject_ids]"]:checked');
                const clsCbs = card.querySelectorAll('input[name*="[class_ids]"]:checked');

                if (subCbs.length === 0 || clsCbs.length === 0) {
                    hasError = true;
                    card.classList.add('ring-2', 'ring-rose-500');
                    if (!firstErrorElement) firstErrorElement = card;
                } else {
                    card.classList.remove('ring-2', 'ring-rose-500');
                }
            });

            if (hasError) {
                e.preventDefault();
                if (firstErrorElement) {
                    firstErrorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                alert('Periksa kembali: setiap baris penugasan wajib memilih minimal 1 mata pelajaran dan minimal 1 rombel.');
                return false;
            }
        }

        syncTeacherReturnQueryInputs();

        const submitBtn = document.getElementById('assignment_submit_btn');
        if (submitBtn) {
            submitBtn.disabled = true;
            const submitText = document.getElementById('submit_text');
            if (submitText) submitText.textContent = 'Menyimpan...';
        }
        return true;
    }

    function openAssignmentModal(teacherId, teacherName, isHomeroom = null, homeroomClassIds = [], homeroomClasses = []) {
        currentActiveTeacherId = teacherId;
        currentActiveTeacherName = teacherName;

        const form = document.getElementById('assignmentTeacherForm');
        form.action = `{{ url('admin/teachers') }}/${teacherId}/assignments`;

        document.getElementById('assignment_teacher_subtitle').textContent = `Mengatur penugasan mata pelajaran & rombel untuk ${teacherName}`;
        document.getElementById('assignment_rows').innerHTML = '';
        assignmentRowIndex = 0;
        currentTeacherHadRestrictions = false;
        currentTeacherHomeroomClassIds = homeroomClassIds || [];

        // Set info dan dropdown penetapan wali kelas jika data awal tersedia dari baris tabel
        const select = document.getElementById('teacher_homeroom_class_select');
        const badgeEl = document.getElementById('assignment_homeroom_badge');
        const textEl = document.getElementById('assignment_homeroom_text');

        if (isHomeroom !== null) {
            if (isHomeroom && homeroomClassIds && homeroomClassIds.length > 0) {
                if (select) select.value = homeroomClassIds[0];
                if (textEl) textEl.textContent = `Wali Kelas di ${(homeroomClasses || []).join(', ')}`;
                if (badgeEl) badgeEl.classList.remove('hidden');
            } else {
                if (select) select.value = '';
                if (textEl) textEl.textContent = 'Guru Mata Pelajaran (Bukan Wali Kelas)';
                if (badgeEl) badgeEl.classList.add('hidden');
            }
        }

        // Loading State
        document.getElementById('assignment_loading_skeleton').classList.remove('hidden');
        document.getElementById('assignment_error_state').classList.add('hidden');
        document.getElementById('assignment_content_wrapper').classList.add('hidden');
        document.getElementById('assignment_submit_btn').disabled = true;

        openModal('assignmentTeacherModal');

        loadAssignmentsData(teacherId);
    }

    function loadAssignmentsData(teacherId) {
        fetch(`{{ url('admin/teachers') }}/${teacherId}/assignments`)
            .then(res => {
                if (!res.ok) throw new Error('Gagal memuat status dari server (HTTP ' + res.status + ')');
                return res.json();
            })
            .then(data => {
                document.getElementById('assignment_loading_skeleton').classList.add('hidden');
                document.getElementById('assignment_content_wrapper').classList.remove('hidden');
                document.getElementById('assignment_submit_btn').disabled = false;

                if (data.teacher) {
                    currentTeacherHomeroomClassIds = data.teacher.homeroom_class_ids || [];
                    const select = document.getElementById('teacher_homeroom_class_select');
                    const badgeEl = document.getElementById('assignment_homeroom_badge');
                    const textEl = document.getElementById('assignment_homeroom_text');

                    if (data.teacher.is_homeroom && currentTeacherHomeroomClassIds.length > 0) {
                        if (select) select.value = currentTeacherHomeroomClassIds[0];
                        if (textEl) textEl.textContent = `Wali Kelas di ${data.teacher.homeroom_classes.join(', ')}`;
                        if (badgeEl) badgeEl.classList.remove('hidden');
                    } else {
                        if (select) select.value = '';
                        if (textEl) textEl.textContent = 'Guru Mata Pelajaran (Bukan Wali Kelas)';
                        if (badgeEl) badgeEl.classList.add('hidden');
                    }
                }

                currentTeacherHadRestrictions = data.has_restrictions;

                if (data.has_restrictions && data.assignments && data.assignments.length > 0) {
                    document.getElementById('mode_restricted').checked = true;
                    toggleAssignmentMode();
                    document.getElementById('assignment_rows').innerHTML = '';
                    data.assignments.forEach(item => {
                        addAssignmentRow(item);
                    });
                } else {
                    document.getElementById('mode_unrestricted').checked = true;
                    toggleAssignmentMode();
                }
                updateLiveSummary();
            })
            .catch(err => {
                console.error('Error loading assignments:', err);
                document.getElementById('assignment_loading_skeleton').classList.add('hidden');
                document.getElementById('assignment_error_state').classList.remove('hidden');
                document.getElementById('assignment_error_msg').textContent = err.message || 'Terjadi kesalahan saat memuat data.';
                document.getElementById('assignment_submit_btn').disabled = true;
            });
    }

    function retryLoadAssignments() {
        if (currentActiveTeacherId) {
            document.getElementById('assignment_error_state').classList.add('hidden');
            document.getElementById('assignment_loading_skeleton').classList.remove('hidden');
            loadAssignmentsData(currentActiveTeacherId);
        }
    }

    function openSavingsModal(data) {
        document.getElementById('savingsOfficerForm').action = data.action_url;
        document.getElementById('savings_modal_subtitle').textContent = `Pengaturan wewenang tabungan siswa untuk ${data.name}`;

        if (data.is_savings_officer) {
            document.getElementById('savings_officer_active').checked = true;
        } else {
            document.getElementById('savings_officer_inactive').checked = true;
        }

        if (data.savings_scope === 'restricted') {
            document.getElementById('savings_scope_restricted').checked = true;
        } else {
            document.getElementById('savings_scope_all').checked = true;
        }

        const managedIds = data.managed_class_ids || [];
        document.querySelectorAll('.savings-class-checkbox').forEach(cb => {
            cb.checked = managedIds.includes(parseInt(cb.value));
        });

        toggleSavingsOfficerStatus();
        openModal('savingsOfficerModal');
    }

    function toggleSavingsOfficerStatus() {
        const isActive = document.getElementById('savings_officer_active').checked;
        const scopeSection = document.getElementById('savings_scope_section');
        if (isActive) {
            scopeSection.classList.remove('hidden');
            toggleSavingsScope();
        } else {
            scopeSection.classList.add('hidden');
        }
    }

    function toggleSavingsScope() {
        const isRestricted = document.getElementById('savings_scope_restricted').checked;
        const checklistContainer = document.getElementById('savings_classes_checklist_container');
        if (isRestricted) {
            checklistContainer.classList.remove('hidden');
        } else {
            checklistContainer.classList.add('hidden');
        }
    }

    function toggleAllSavingsClasses(check) {
        document.querySelectorAll('.savings-class-checkbox').forEach(cb => {
            cb.checked = check;
        });
    }
</script>
@endpush
