{{-- ============================================================== --}}
{{-- SCRIPTS LOGIKA INTERAKTIF JADWAL PENGAWAS                       --}}
{{-- ============================================================== --}}
@push('scripts')
<script>
    // Tab Navigation Logic
    function switchTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('is-active'));

        const activeContent = document.getElementById('content-' + tabName);
        const activeBtn = document.getElementById('tab-btn-' + tabName);

        if (activeContent) activeContent.classList.remove('hidden');
        if (activeBtn) activeBtn.classList.add('is-active');

        window.location.hash = tabName;
    }

    // Auto open tab based on URL hash
    document.addEventListener('DOMContentLoaded', function() {
        const hash = window.location.hash.replace('#', '');
        if (['penugasan', 'pemetaan', 'riwayat'].includes(hash)) {
            switchTab(hash);
        }
    });

    // Client-side table pagination + pencarian
    const PAGINATION_PER_PAGE_KEY = 'ujian_pengawas_per_page';

    function buildPaginationPages(current, total) {
        const pages = [];
        for (let i = 1; i <= total; i++) {
            if (i === 1 || i === total || Math.abs(i - current) <= 1) {
                pages.push(i);
            } else if (pages[pages.length - 1] !== '…') {
                pages.push('…');
            }
        }
        return pages;
    }

    function initTablePagination(card) {
        const table = card.querySelector('table[data-table]');
        if (!table) return;

        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr.pagination-row'));
        const groupRows = Array.from(tbody.querySelectorAll('tr.pagination-group-row'));
        const searchInput = card.querySelector('[data-table-search]');
        const classFilterSelect = card.querySelector('[data-table-filter-class]');
        const info = card.querySelector('[data-pagination-info]');
        const perPageSelect = card.querySelector('[data-pagination-per-page]');
        const prevBtn = card.querySelector('[data-pagination-prev]');
        const nextBtn = card.querySelector('[data-pagination-next]');
        const pagesBox = card.querySelector('[data-pagination-pages]');
        const scrollBox = card.querySelector('.data-table-scroll');
        const searchEmptyRow = tbody.querySelector('.pagination-search-empty');

        if (rows.length === 0) {
            if (info) info.textContent = 'Tidak ada data untuk ditampilkan';
            if (perPageSelect) perPageSelect.disabled = true;
            if (prevBtn) prevBtn.disabled = true;
            if (nextBtn) nextBtn.disabled = true;
            return;
        }

        let perPage = localStorage.getItem(PAGINATION_PER_PAGE_KEY) || '10';
        if (perPage !== 'all' && ![10, 25, 50].includes(parseInt(perPage, 10))) {
            perPage = '10';
        }
        if (perPageSelect) perPageSelect.value = perPage;

        let currentPage = 1;

        const groupMode = card.dataset.paginationMode === 'group';
        const dayGroups = groupMode ? groupRows.map(groupRow => {
            const groupRowItems = [];
            let sibling = groupRow.nextElementSibling;
            while (sibling && !sibling.classList.contains('pagination-group-row')) {
                if (sibling.classList.contains('pagination-row')) {
                    groupRowItems.push(sibling);
                }
                sibling = sibling.nextElementSibling;
            }
            return {
                header: groupRow,
                rows: groupRowItems,
                label: groupRow.dataset.groupLabel || '',
            };
        }) : [];

        const matchesFilter = (row) => {
            const query = (searchInput?.value || '').trim().toLowerCase();
            if (query !== '' && !row.textContent.toLowerCase().includes(query)) {
                return false;
            }

            const classFilter = (classFilterSelect?.value || '').trim();
            if (classFilter === '') {
                return true;
            }

            if (classFilter.startsWith('tingkat:')) {
                const lvl = classFilter.replace('tingkat:', '');
                const roomLevel = (row.dataset.roomLevel || '').trim();
                if (roomLevel !== '') {
                    return roomLevel === lvl;
                }
                const jadwalLevels = (row.dataset.jadwalLevels || '').split(',').map(s => s.trim());
                return jadwalLevels.includes(lvl);
            }

            if (classFilter.startsWith('rombel:')) {
                const classId = classFilter.replace('rombel:', '');
                const rowClassId = (row.dataset.classId || '').trim();
                return rowClassId === classId;
            }

            return true;
        };

        const getFilteredRows = () => {
            return rows.filter(matchesFilter);
        };

        const getFilteredDayGroups = () => {
            return dayGroups
                .map(group => ({
                    header: group.header,
                    label: group.label,
                    rows: group.rows.filter(matchesFilter),
                }))
                .filter(group => group.rows.length > 0);
        };

        const renderPaginationPages = (totalPages, pageLabels = []) => {
            if (!pagesBox) return;

            pagesBox.innerHTML = '';
            buildPaginationPages(currentPage, totalPages).forEach(page => {
                if (page === '…') {
                    const span = document.createElement('span');
                    span.className = 'pagination-ellipsis';
                    span.textContent = '…';
                    pagesBox.appendChild(span);
                    return;
                }

                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'pagination-page' + (page === currentPage ? ' is-current' : '');
                button.textContent = page;
                if (pageLabels[page - 1]) {
                    button.title = pageLabels[page - 1];
                }
                button.addEventListener('click', () => {
                    currentPage = page;
                    render();
                    scrollBox?.scrollTo({ top: 0, behavior: 'smooth' });
                });
                pagesBox.appendChild(button);
            });
        };

        const renderDayPages = () => {
            const filteredGroups = getFilteredDayGroups();
            const totalPages = Math.max(1, filteredGroups.length);
            currentPage = Math.min(currentPage, totalPages);

            const activeGroup = filteredGroups[currentPage - 1] || null;
            const visibleSet = new Set(activeGroup ? activeGroup.rows : []);

            rows.forEach(row => {
                row.style.display = visibleSet.has(row) ? '' : 'none';
            });
            groupRows.forEach(groupRow => {
                groupRow.style.display = activeGroup && groupRow === activeGroup.header ? '' : 'none';
            });

            if (searchEmptyRow) {
                searchEmptyRow.style.display = filteredGroups.length === 0 ? '' : 'none';
            }

            if (info) {
                info.textContent = activeGroup
                    ? `Hari ${currentPage} dari ${filteredGroups.length} · ${activeGroup.label} · ${activeGroup.rows.length} penugasan`
                    : 'Tidak ada data yang cocok dengan pencarian';
            }

            if (filteredGroups.length === 0) {
                if (pagesBox) pagesBox.innerHTML = '';
            } else {
                renderPaginationPages(totalPages, filteredGroups.map(group => group.label));
            }

            if (prevBtn) {
                prevBtn.disabled = currentPage <= 1;
                prevBtn.title = 'Hari sebelumnya';
            }
            if (nextBtn) {
                nextBtn.disabled = currentPage >= totalPages;
                nextBtn.title = 'Hari berikutnya';
            }
        };

        const render = () => {
            if (groupMode) {
                renderDayPages();
                return;
            }

            const filtered = getFilteredRows();
            const perPageNumber = perPage === 'all' ? Math.max(filtered.length, 1) : parseInt(perPage, 10);
            const totalPages = Math.max(1, Math.ceil(filtered.length / perPageNumber));
            currentPage = Math.min(currentPage, totalPages);

            const startIndex = (currentPage - 1) * perPageNumber;
            const visibleRows = filtered.slice(startIndex, startIndex + perPageNumber);
            const visibleSet = new Set(visibleRows);

            rows.forEach(row => {
                row.style.display = visibleSet.has(row) ? '' : 'none';
            });

            groupRows.forEach(groupRow => {
                let sibling = groupRow.nextElementSibling;
                let hasVisibleRow = false;
                while (sibling && !sibling.classList.contains('pagination-group-row')) {
                    if (sibling.classList.contains('pagination-row') && visibleSet.has(sibling)) {
                        hasVisibleRow = true;
                        break;
                    }
                    sibling = sibling.nextElementSibling;
                }
                groupRow.style.display = hasVisibleRow ? '' : 'none';
            });

            if (searchEmptyRow) {
                searchEmptyRow.style.display = filtered.length === 0 ? '' : 'none';
            }

            if (info) {
                info.textContent = filtered.length === 0
                    ? 'Tidak ada data yang cocok dengan pencarian'
                    : `Menampilkan ${startIndex + 1}–${startIndex + visibleRows.length} dari ${filtered.length} data`;
            }

            renderPaginationPages(totalPages);

            if (prevBtn) prevBtn.disabled = currentPage <= 1;
            if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
        };

        prevBtn?.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                render();
                scrollBox?.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });

        nextBtn?.addEventListener('click', () => {
            if (currentPage < Math.ceil(getFilteredRows().length / (perPage === 'all' ? getFilteredRows().length : parseInt(perPage, 10)))) {
                currentPage++;
                render();
                scrollBox?.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });

        perPageSelect?.addEventListener('change', () => {
            perPage = perPageSelect.value;
            localStorage.setItem(PAGINATION_PER_PAGE_KEY, perPage);
            currentPage = 1;
            render();
        });

        classFilterSelect?.addEventListener('change', () => {
            currentPage = 1;
            render();
        });

        let searchTimer = null;
        searchInput?.addEventListener('input', () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                currentPage = 1;
                render();
            }, 150);
        });

        render();
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-table-card]').forEach(initTablePagination);
        updateTeacherSelectionCount();
    });

    // Checkbox Select All Teachers
    function toggleSelectAllTeachers(master) {
        document.querySelectorAll('.teacher-check').forEach(cb => {
            cb.checked = master.checked;
            cb.closest('tr')?.classList.toggle('is-row-selected', master.checked);
        });
        updateTeacherSelectionCount();
    }

    function updateTeacherSelectionCount() {
        const checks = document.querySelectorAll('.teacher-check');
        const checked = document.querySelectorAll('.teacher-check:checked');
        const countEl = document.getElementById('teacherSelectionCount');
        const master = document.getElementById('checkAllTeachers');

        if (countEl) {
            countEl.textContent = checked.length + ' dipilih';
        }

        if (master) {
            master.indeterminate = checks.length > 0 && checked.length > 0 && checked.length < checks.length;
            master.checked = checks.length > 0 && checked.length === checks.length;
        }
    }

    document.addEventListener('change', function(event) {
        if (event.target.classList && event.target.classList.contains('teacher-check')) {
            event.target.closest('tr')?.classList.toggle('is-row-selected', event.target.checked);
            updateTeacherSelectionCount();
        }
    });

    // Bulk Level Assignment
    function submitBulkLevels(levels) {
        const checked = document.querySelectorAll('.teacher-check:checked');
        if (checked.length === 0) {
            alert('Pilih setidaknya satu guru dengan mencentang checkbox terlebih dahulu.');
            return;
        }

        const form = document.getElementById('formBulkMapping');
        form.action = "{{ route('ujian.pengawas.mapping.bulk') }}";

        // Remove old level inputs
        form.querySelectorAll('input[name="assigned_levels[]"]').forEach(el => el.remove());

        // Append selected levels
        levels.forEach(lvl => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'assigned_levels[]';
            input.value = lvl;
            form.appendChild(input);
        });

        form.submit();
    }

    function submitBulkDelete() {
        const checked = document.querySelectorAll('.teacher-check:checked');
        if (checked.length === 0) {
            alert('Pilih setidaknya satu guru yang pemetaannya hendak dihapus.');
            return;
        }

        if (!confirm('Hapus pemetaan kelompok kelas untuk guru-guru terpilih?')) {
            return;
        }

        const form = document.getElementById('formBulkMapping');
        form.action = "{{ route('ujian.pengawas.mapping.destroy') }}";
        
        let methodField = form.querySelector('input[name="_method"]');
        if (!methodField) {
            methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            form.appendChild(methodField);
        }
        methodField.value = 'DELETE';

        form.submit();
    }

    // Modal Edit Mapping Individual
    function openModalEditMapping(teacherId, teacherName, teacherNip, assignedLevels, keterangan, modePenugasan = 'rotasi', assignedSubjectIds = []) {
        document.getElementById('edit_map_teacher_id').value = teacherId;
        document.getElementById('edit_map_teacher_name').textContent = teacherName;
        document.getElementById('edit_map_teacher_nip').textContent = 'NIP: ' + (teacherNip || '-');
        document.getElementById('edit_map_keterangan').value = keterangan || '';
        document.getElementById('edit_map_mode_penugasan').value = modePenugasan || 'rotasi';

        const levelsArray = (assignedLevels || []).map(String);
        document.querySelectorAll('.edit-level-check').forEach(cb => {
            cb.checked = levelsArray.includes(cb.value);
        });

        const subjectsArray = (assignedSubjectIds || []).map(Number);
        document.querySelectorAll('.edit-subject-check').forEach(cb => {
            cb.checked = subjectsArray.includes(Number(cb.value));
        });

        toggleEditSubjectScope();
        document.getElementById('modalEditMapping').classList.remove('hidden');
    }

    function toggleEditSubjectScope() {
        const mode = document.getElementById('edit_map_mode_penugasan')?.value || 'rotasi';
        const wrapper = document.getElementById('edit_map_subjects_wrapper');
        if (wrapper) {
            wrapper.classList.toggle('hidden', mode !== 'pengampu_mapel');
        }
    }

    @php
        $activeTeachersPreviewData = collect($teachers ?? [])->map(function ($t) use ($panitiaTeacherIds) {
            return [
                'id' => $t->id,
                'name' => $t->user?->name ?? 'Guru',
                'is_panitia' => in_array($t->id, $panitiaTeacherIds ?? [], true),
            ];
        })->values();
        $allSubjectsPreviewData = collect($subjects ?? [])->pluck('name', 'id');
    @endphp
    const teachingSummaryData = @json($teachingSummary ?? []);
    const activeTeachersPreview = @json($activeTeachersPreviewData);
    const allSubjectsMap = @json($allSubjectsPreviewData);

    function openModalSyncTeachingMapping() {
        renderSyncTeachingPreview();
        document.getElementById('modalSyncTeachingMapping').classList.remove('hidden');
    }

    function renderSyncTeachingPreview() {
        const tbody = document.getElementById('syncTeachingPreviewBody');
        if (!tbody) return;

        const selectedLevels = Array.from(document.querySelectorAll('.sync-level-check:checked')).map(cb => cb.value);
        const selectedSubjectIds = Array.from(document.querySelectorAll('.sync-subject-check:checked')).map(cb => Number(cb.value));

        tbody.textContent = '';
        let rowCount = 0;

        activeTeachersPreview.forEach(t => {
            if (t.is_panitia) return;
            const summary = teachingSummaryData[t.id];
            if (!summary || !summary.levels) return;

            const hasLevel = summary.levels.some(lvl => selectedLevels.includes(String(lvl)));
            if (!hasLevel) return;

            const matchedSubjectIds = selectedSubjectIds.length > 0
                ? summary.subject_ids.filter(sid => selectedSubjectIds.includes(Number(sid)))
                : summary.subject_ids;

            if (matchedSubjectIds.length === 0) return;

            const tr = document.createElement('tr');
            const tdName = document.createElement('td');
            tdName.className = 'px-3 py-2 font-bold text-slate-900 border-r border-slate-200';
            tdName.textContent = t.name;

            const tdClasses = document.createElement('td');
            tdClasses.className = 'px-3 py-2 text-slate-700 border-r border-slate-200';
            tdClasses.textContent = (summary.class_names || []).join(', ');

            const tdSubjects = document.createElement('td');
            tdSubjects.className = 'px-3 py-2 text-emerald-900';
            tdSubjects.textContent = matchedSubjectIds.map(sid => allSubjectsMap[sid] || ('Mapel #' + sid)).join(', ');

            tr.appendChild(tdName);
            tr.appendChild(tdClasses);
            tr.appendChild(tdSubjects);
            tbody.appendChild(tr);
            rowCount++;
        });

        if (rowCount === 0) {
            const tr = document.createElement('tr');
            const td = document.createElement('td');
            td.colSpan = 3;
            td.className = 'px-3 py-4 text-center italic text-slate-500';
            td.textContent = 'Tidak ada guru aktif non-panitia yang mengajar sesuai filter tingkat/mapel.';
            tr.appendChild(td);
            tbody.appendChild(tr);
        }
    }

    function setEditLevels(levels) {
        document.querySelectorAll('.edit-level-check').forEach(cb => {
            cb.checked = levels.includes(cb.value);
        });
    }

    // Modal Edit Penugasan Pengawas
    function openEditPengawasModal(jadwalId, ruangId, p1Id, p2Id, kehadiran) {
        document.getElementById('modal_jadwal_id').value = jadwalId;
        document.getElementById('modal_ruang_id').value = ruangId;
        document.getElementById('modal_pengawas_1').value = p1Id || '';
        document.getElementById('modal_pengawas_2').value = p2Id || '';
        document.getElementById('modal_kehadiran').value = kehadiran || 'Terjadwal';
        document.getElementById('modalTambahPengawas').classList.remove('hidden');
    }

    // Modal Pertukaran Jadwal (Swap)
    function openModalTukar(assignmentId, slot, teacherName, mapel, tanggal, jam, ruang, targetKelas) {
        document.getElementById('swap_source_assignment_id').value = assignmentId;
        document.getElementById('swap_source_slot').value = slot;

        document.getElementById('swap_info_teacher').textContent = teacherName + ' (' + (slot === 'pengawas_2' ? 'Pengawas 2' : 'Pengawas 1') + ')';
        document.getElementById('swap_info_mapel').textContent = 'Mata Pelajaran: ' + mapel + ' [' + targetKelas + ']';
        document.getElementById('swap_info_datetime').textContent = 'Waktu: ' + tanggal + ' (' + jam + ') | ' + ruang;

        const select = document.getElementById('swap_target_select');
        select.innerHTML = '<option value="">-- Memuat kandidat pertukaran yang valid... --</option>';
        select.disabled = true;

        const loadingBox = document.getElementById('swap_loading_box');
        loadingBox.classList.remove('hidden');

        fetch(`{{ route('ujian.pengawas.swap.candidates') }}?assignment_id=${assignmentId}&slot=${slot}`)
            .then(res => res.json())
            .then(candidates => {
                loadingBox.classList.add('hidden');
                select.disabled = false;
                select.innerHTML = '<option value="">-- Pilih Penugasan Lawan Tukar --</option>';

                if (candidates.length === 0) {
                    select.innerHTML = '<option value="">-- Tidak ada jadwal yang memenuhi syarat pertukaran --</option>';
                    return;
                }

                candidates.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = `${c.assignment_id}|${c.slot}`;
                    opt.textContent = `[${c.teacher_name}] - ${c.mapel} | ${c.ruang} | ${c.tanggal} (${c.jam}) [${c.target_kelas}]`;
                    select.appendChild(opt);
                });
            })
            .catch(err => {
                loadingBox.classList.add('hidden');
                select.disabled = false;
                select.innerHTML = '<option value="">Gagal memuat kandidat pertukaran.</option>';
            });

        document.getElementById('modalTukarPengawas').classList.remove('hidden');
    }

    function onSelectSwapTarget(select) {
        if (!select.value) {
            document.getElementById('swap_target_assignment_id').value = '';
            document.getElementById('swap_target_slot').value = '';
            return;
        }

        const parts = select.value.split('|');
        document.getElementById('swap_target_assignment_id').value = parts[0];
        document.getElementById('swap_target_slot').value = parts[1];
    }

    // Modal Cetak
    function openModalCetakDaftarHadir() {
        document.getElementById('modalCetakDaftarHadir').classList.remove('hidden');
    }

    function closeModalCetakDaftarHadir() {
        document.getElementById('modalCetakDaftarHadir').classList.add('hidden');
    }
</script>
@endpush
