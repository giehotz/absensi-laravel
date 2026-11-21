@push('scripts')
<script>
    window.coverageData = {!! json_encode($coverageData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};

    function escapeCoverageHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (char) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char];
        });
    }

    function formatCoverageDate(value) {
        if (!value) {
            return '-';
        }

        const raw = String(value).substring(0, 10);
        const parts = raw.split('-');
        if (parts.length !== 3) {
            return raw;
        }

        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const monthIndex = parseInt(parts[1], 10) - 1;

        return `${parts[2]} ${months[monthIndex] || parts[1]} ${parts[0]}`;
    }

    function jadwalMatchesCoverageScope(jadwal, mode, scope) {
        if (mode === 'tingkat') {
            if (jadwal.school_class_id) {
                return String(jadwal.class_level) === String(scope);
            }

            return (jadwal.target_levels || []).map(String).includes(String(scope));
        }

        const schoolClass = (window.coverageData?.classes || []).find(c => String(c.id) === String(scope));
        if (!schoolClass) {
            return false;
        }

        if (jadwal.school_class_id) {
            return String(jadwal.school_class_id) === String(scope);
        }

        return (jadwal.target_levels || []).map(String).includes(String(schoolClass.level));
    }

    function updateCoverageScopeOptions() {
        const modeElement = document.getElementById('coverage_mode');
        const scopeElement = document.getElementById('coverage_scope');
        if (!modeElement || !scopeElement || !window.coverageData) {
            return;
        }

        const mode = modeElement.value;
        const previousScope = scopeElement.value;
        scopeElement.innerHTML = '';

        if (mode === 'tingkat') {
            for (let level = 1; level <= 6; level++) {
                const option = document.createElement('option');
                option.value = String(level);
                option.textContent = `Kelas ${level}`;
                scopeElement.appendChild(option);
            }

            const preferred = window.coverageData.selectedTingkat !== 'all'
                ? String(window.coverageData.selectedTingkat)
                : (previousScope && previousScope !== 'all' ? String(previousScope) : '1');
            scopeElement.value = Array.from(scopeElement.options).some(option => option.value === preferred)
                ? preferred
                : '1';
        } else {
            const classes = window.coverageData.classes || [];
            if (classes.length === 0) {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'Belum ada data rombel';
                scopeElement.appendChild(option);
            } else {
                classes.forEach(schoolClass => {
                    const option = document.createElement('option');
                    option.value = String(schoolClass.id);
                    option.textContent = `${schoolClass.name} (Tingkat ${schoolClass.level})`;
                    scopeElement.appendChild(option);
                });

                const preferred = classes.some(schoolClass => String(schoolClass.id) === String(previousScope))
                    ? String(previousScope)
                    : String(classes[0].id);
                scopeElement.value = preferred;
            }
        }

        renderCoverageGrid();
    }

    function openAddJadwalFor(subjectId) {
        const form = document.querySelector('#modalTambahJadwal form');
        if (!form) {
            return;
        }

        const subjectSelect = form.querySelector('select[name="subject_id"]');
        const customInput = form.querySelector('input[name="nama_mapel_kustom"]');
        const classSelect = form.querySelector('select[name="school_class_id"]');
        const levelCheckboxes = form.querySelectorAll('input[name="tingkat[]"]');
        const mode = document.getElementById('coverage_mode')?.value || 'tingkat';
        const scope = document.getElementById('coverage_scope')?.value || '';

        if (subjectSelect) {
            subjectSelect.value = String(subjectId);
        }
        if (customInput) {
            customInput.value = '';
        }
        if (classSelect) {
            classSelect.value = '';
        }
        levelCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });

        if (mode === 'rombel') {
            if (classSelect) {
                classSelect.value = String(scope);
            }
        } else {
            const levelCheckbox = Array.from(levelCheckboxes).find(checkbox => checkbox.value === String(scope));
            if (levelCheckbox) {
                levelCheckbox.checked = true;
            }
        }

        updateBentukUjianAddForm();
        document.getElementById('modalTambahJadwal')?.classList.remove('hidden');
    }

    function renderCoverageGrid() {
        const grid = document.getElementById('coverageGrid');
        if (!grid || !window.coverageData) {
            return;
        }

        const mode = document.getElementById('coverage_mode')?.value || 'tingkat';
        const scope = document.getElementById('coverage_scope')?.value || '';
        const onlyUnscheduled = document.getElementById('coverage_only_unscheduled')?.checked || false;
        const subjects = window.coverageData.subjects || [];
        const jadwals = window.coverageData.jadwals || [];
        const summary = document.getElementById('coverage_summary');
        const unscheduledBadge = document.getElementById('coverage_unscheduled_badge');

        if (subjects.length === 0) {
            grid.innerHTML = '<div class="md:col-span-2 xl:col-span-3 p-6 text-center text-xs font-bold text-slate-500 border-2 border-dashed border-slate-400 rounded-lg">Belum ada mata pelajaran terdaftar di master data.</div>';
            if (summary) {
                summary.textContent = '0/0 mapel terjadwal';
            }
            if (unscheduledBadge) {
                unscheduledBadge.textContent = '-';
            }

            return;
        }

        if (!scope) {
            grid.innerHTML = '<div class="md:col-span-2 xl:col-span-3 p-6 text-center text-xs font-bold text-slate-500 border-2 border-dashed border-slate-400 rounded-lg">Belum ada rombel yang bisa dipilih.</div>';
            if (summary) {
                summary.textContent = `0/${subjects.length} mapel terjadwal`;
            }
            if (unscheduledBadge) {
                unscheduledBadge.textContent = '-';
            }

            return;
        }

        let scheduledCount = 0;
        const cards = [];

        subjects.forEach(subject => {
            const subjectJadwals = jadwals.filter(jadwal => {
                return String(jadwal.subject_id) === String(subject.id)
                    && jadwalMatchesCoverageScope(jadwal, mode, scope);
            });
            const isScheduled = subjectJadwals.length > 0;
            if (isScheduled) {
                scheduledCount++;
            }
            if (onlyUnscheduled && isScheduled) {
                return;
            }

            const card = document.createElement('div');
            card.className = 'p-3 bg-white border-2 border-black rounded-lg shadow-[2px_2px_0px_0px_#000] flex flex-col gap-2';
            const code = subject.code ? escapeCoverageHtml(subject.code) : 'MAPEL';
            const name = escapeCoverageHtml(subject.name);
            const statusBadge = isScheduled
                ? '<span class="px-1.5 py-0.5 text-[9px] font-black uppercase bg-[#D3F9D8] text-emerald-950 border border-black rounded">Terjadwal</span>'
                : '<span class="px-1.5 py-0.5 text-[9px] font-black uppercase bg-amber-100 text-amber-950 border border-black rounded">Belum</span>';

            let slotsHtml = '';
            if (subjectJadwals.length === 0) {
                slotsHtml = '<div class="p-2 bg-slate-50 border border-dashed border-slate-400 rounded text-[10px] font-bold text-slate-500 text-center">Belum ada slot di scope ini</div>';
            } else {
                slotsHtml = subjectJadwals.map(jadwal => {
                    const date = formatCoverageDate(jadwal.tanggal_ujian);
                    const session = escapeCoverageHtml(jadwal.sesi_ke);
                    const start = escapeCoverageHtml(String(jadwal.jam_mulai || '').substring(0, 5));
                    const end = escapeCoverageHtml(String(jadwal.jam_selesai || '').substring(0, 5));
                    const target = escapeCoverageHtml(
                        jadwal.school_class_id
                            ? (jadwal.school_class?.name || 'Rombel')
                            : (jadwal.tingkat ? `Tingkat ${jadwal.tingkat}` : 'Semua Tingkat')
                    );
                    const jenis = escapeCoverageHtml(jadwal.jenis_soal || '-');

                    return `
                        <div class="p-2 bg-amber-50 border border-black rounded">
                            <div class="flex items-start justify-between gap-1.5">
                                <div class="min-w-0">
                                    <div class="text-[10px] font-black text-black">${date} <span class="text-slate-500">/ Sesi ${session}</span></div>
                                    <div class="text-[10px] font-mono font-bold text-slate-700">${start} - ${end}</div>
                                    <div class="text-[9px] font-semibold text-slate-600">${target} / ${jenis}</div>
                                </div>
                                <button type="button" data-edit-slot="${escapeCoverageHtml(jadwal.id)}" class="shrink-0 px-1.5 py-1 bg-[#FFD43B] hover:bg-yellow-400 text-black rounded text-[9px] font-black border border-black shadow-[1px_1px_0px_0px_#000] cursor-pointer">
                                    Edit
                                </button>
                            </div>
                        </div>
                    `;
                }).join('');
            }

            card.innerHTML = `
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-1.5 mb-1">
                            <span class="px-1.5 py-0.5 text-[9px] font-black uppercase bg-[#FFD43B] text-black border border-black rounded">${code}</span>
                            ${statusBadge}
                        </div>
                        <h3 class="text-xs font-black text-black leading-tight">${name}</h3>
                    </div>
                    <button type="button" data-add-subject="${escapeCoverageHtml(subject.id)}" class="shrink-0 px-2 py-1.5 bg-[#51CF66] hover:bg-green-500 text-black rounded text-[10px] font-black border border-black shadow-[1px_1px_0px_0px_#000] cursor-pointer">
                        + Slot
                    </button>
                </div>
                <div class="space-y-1.5">${slotsHtml}</div>
            `;

            card.querySelector('[data-add-subject]')?.addEventListener('click', () => openAddJadwalFor(subject.id));
            card.querySelectorAll('[data-edit-slot]').forEach(button => {
                button.addEventListener('click', () => {
                    const jadwal = subjectJadwals.find(item => String(item.id) === String(button.dataset.editSlot));
                    if (jadwal) {
                        openEditJadwalModal(jadwal);
                    }
                });
            });

            cards.push(card);
        });

        grid.innerHTML = '';
        if (cards.length === 0) {
            grid.innerHTML = '<div class="md:col-span-2 xl:col-span-3 p-6 text-center text-xs font-bold text-slate-500 border-2 border-dashed border-slate-400 rounded-lg">Semua mapel sudah terjadwal untuk scope ini.</div>';
        } else {
            cards.forEach(card => grid.appendChild(card));
        }

        const total = subjects.length;
        const unscheduledCount = total - scheduledCount;
        if (summary) {
            summary.textContent = `${scheduledCount}/${total} mapel terjadwal`;
        }
        if (unscheduledBadge) {
            unscheduledBadge.textContent = unscheduledCount > 0 ? `${unscheduledCount} belum terjadwal` : 'Lengkap';
            unscheduledBadge.className = unscheduledCount > 0
                ? 'px-2.5 py-1 text-[11px] font-black border-2 border-black rounded-lg bg-amber-100 text-amber-950 shadow-[1.5px_1.5px_0px_0px_#000]'
                : 'px-2.5 py-1 text-[11px] font-black border-2 border-black rounded-lg bg-[#D3F9D8] text-emerald-950 shadow-[1.5px_1.5px_0px_0px_#000]';
        }
    }

    function updateSyncTargetOptions() {
        const sourceLvl = document.getElementById('sync_source_level').value;
        document.querySelectorAll('.sync-target-cb').forEach(cb => {
            const lbl = document.getElementById(`lbl_target_level_${cb.value}`);
            if (cb.value === sourceLvl) {
                cb.checked = false;
                cb.disabled = true;
                if (lbl) {
                    lbl.classList.add('opacity-40', 'cursor-not-allowed');
                    lbl.classList.remove('hover:bg-yellow-100');
                }
            } else {
                cb.disabled = false;
                if (lbl) {
                    lbl.classList.remove('opacity-40', 'cursor-not-allowed');
                    lbl.classList.add('hover:bg-yellow-100');
                }
            }
        });
    }

    async function loadSyncPreview() {
        const sourceLvl = document.getElementById('sync_source_level').value;
        const targetBoxes = Array.from(document.querySelectorAll('.sync-target-cb:checked')).map(cb => cb.value);
        if (!sourceLvl) {
            alert('Pilih tingkat sumber terlebih dahulu.');
            return;
        }
        if (targetBoxes.length === 0) {
            alert('Pilih minimal 1 tingkat sasaran.');
            return;
        }

        const notice = document.getElementById('previewLoadingNotice');
        const wrapper = document.getElementById('syncPreviewWrapper');
        const tbody = document.getElementById('syncPreviewTbody');
        const badge = document.getElementById('previewSummaryBadge');

        notice.classList.remove('hidden');

        try {
            const res = await fetch("{{ route('ujian.jadwal.sync-levels.preview') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    ujian_periode_id: "{{ $activePeriode->id }}",
                    source_level: sourceLvl,
                    target_levels: targetBoxes
                })
            });

            const data = await res.json();
            notice.classList.add('hidden');
            wrapper.classList.remove('hidden');
            tbody.innerHTML = '';

            if (!data.items || data.items.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="p-3 text-center text-slate-500 font-bold">Tidak ada jadwal ditemukan pada tingkat sumber.</td></tr>';
                badge.textContent = '0 Slot';
                return;
            }

            badge.textContent = `${data.items.length} Slot Preview`;

            data.items.forEach(item => {
                const tr = document.createElement('tr');
                let statusBadge = '';
                if (item.status === 'new') {
                    statusBadge = '<span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px]">Siap Disalin</span>';
                } else if (item.status === 'conflict') {
                    statusBadge = `<span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 font-bold text-[10px]" title="Bentrok dengan ${item.conflict_with?.nama_mapel}">Bentrok</span>`;
                } else {
                    statusBadge = '<span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold text-[10px]">Duplikat</span>';
                }

                tr.innerHTML = `
                    <td class="p-1.5 border-r border-black font-mono font-medium">${item.tanggal} (${item.jam_mulai.substring(0,5)}-${item.jam_selesai.substring(0,5)})</td>
                    <td class="p-1.5 border-r border-black font-bold text-black">${item.nama_mapel} (${item.jenis_soal})</td>
                    <td class="p-1.5 border-r border-black text-center font-bold">Kelas ${item.target_level}</td>
                    <td class="p-1.5 text-center">${statusBadge}</td>
                `;
                tbody.appendChild(tr);
            });
        } catch (err) {
            notice.classList.add('hidden');
            alert('Gagal memuat data preview: ' + err.message);
        }
    }

    function switchImportJadwalTab(tab) {
        const btnExcel = document.getElementById('tabBtnExcel');
        const btnPdf = document.getElementById('tabBtnPdf');
        const contentExcel = document.getElementById('tabContentExcel');
        const contentPdf = document.getElementById('tabContentPdf');

        if (tab === 'excel') {
            btnExcel.classList.add('bg-[#FFD43B]', 'text-black', 'font-black');
            btnExcel.classList.remove('bg-slate-100', 'text-slate-700', 'font-bold');
            btnPdf.classList.remove('bg-[#FFD43B]', 'text-black', 'font-black');
            btnPdf.classList.add('bg-slate-100', 'text-slate-700', 'font-bold');
            contentExcel.classList.remove('hidden');
            contentPdf.classList.add('hidden');
        } else {
            btnPdf.classList.add('bg-[#FFD43B]', 'text-black', 'font-black');
            btnPdf.classList.remove('bg-slate-100', 'text-slate-700', 'font-bold');
            btnExcel.classList.remove('bg-[#FFD43B]', 'text-black', 'font-black');
            btnExcel.classList.add('bg-slate-100', 'text-slate-700', 'font-bold');
            contentPdf.classList.remove('hidden');
            contentExcel.classList.add('hidden');
        }
    }

    function applySesiTime(prefix, selectEl) {
        const opt = selectEl.options[selectEl.selectedIndex];
        if (!opt) return;

        const mulai = opt.getAttribute('data-mulai');
        const selesai = opt.getAttribute('data-selesai');
        const durasi = opt.getAttribute('data-durasi');

        if (mulai) {
            const elMulai = document.getElementById(`${prefix}_jam_mulai`);
            if (elMulai) elMulai.value = mulai;
        }
        if (selesai) {
            const elSelesai = document.getElementById(`${prefix}_jam_selesai`);
            if (elSelesai) elSelesai.value = selesai;
        }
        if (durasi) {
            const elDurasi = document.getElementById(`${prefix}_durasi_menit`);
            if (elDurasi) elDurasi.value = durasi;
        }
    }

    function autoCalcJadwalDuration(prefix) {
        const elMulai = document.getElementById(`${prefix}_jam_mulai`);
        const elSelesai = document.getElementById(`${prefix}_jam_selesai`);
        const elDurasi = document.getElementById(`${prefix}_durasi_menit`);
        if (!elMulai || !elSelesai || !elDurasi) return;

        const mulai = elMulai.value;
        const selesai = elSelesai.value;
        if (!mulai || !selesai) return;

        const [h1, m1] = mulai.split(':').map(Number);
        const [h2, m2] = selesai.split(':').map(Number);
        const diffMinutes = (h2 * 60 + m2) - (h1 * 60 + m1);
        if (diffMinutes > 0) {
            elDurasi.value = diffMinutes;
        }
    }

    function filterJadwalTable() {
        const query = (document.getElementById('search_jadwal')?.value || '').trim().toLowerCase();
        const jenisFilter = (document.getElementById('filter_bentuk_ujian')?.value || '').trim().toLowerCase();
        const rows = document.querySelectorAll('.jadwal-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const rowSearch = row.dataset.search || '';
            const rowJenis = row.dataset.jenisSoal || '';
            const matchesQuery = query === '' || rowSearch.includes(query);
            const matchesJenis = jenisFilter === '' || rowJenis === jenisFilter;

            if (matchesQuery && matchesJenis) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const emptySearchRow = document.getElementById('empty_search_row');
        if (emptySearchRow) {
            emptySearchRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
        }

        const countBadge = document.getElementById('jadwal_count_badge');
        if (countBadge) {
            countBadge.textContent = `${visibleCount} Slot Jadwal`;
        }
    }

    function toggleCustomBentukUjian(selectEl, wrapperId) {
        const wrapper = document.getElementById(wrapperId);
        if (!wrapper) return;
        if (selectEl.value === 'Lainnya') {
            wrapper.classList.remove('hidden');
            wrapper.querySelector('input')?.focus();
        } else {
            wrapper.classList.add('hidden');
        }
    }

    function updateBentukUjianAddForm() {
        const rombelSelect = document.getElementById('add_school_class_id');
        const checkedBoxes = Array.from(document.querySelectorAll('#modalTambahJadwal input[name="tingkat[]"]:checked'));
        const levels = checkedBoxes.map(cb => cb.value);

        const hasBawah = levels.some(l => ['1', '2', '3'].includes(l));
        const hasAtas = levels.some(l => ['4', '5', '6'].includes(l));
        const isMixed = hasBawah && hasAtas && (!rombelSelect || !rombelSelect.value);

        const singleWrapper = document.getElementById('wrapper_bentuk_ujian_single');
        const splitWrapper = document.getElementById('wrapper_bentuk_ujian_split');
        const singleSelect = document.getElementById('add_jenis_soal_single');
        const labelSingle = document.getElementById('label_bentuk_ujian_single');

        if (!singleWrapper || !splitWrapper) return;

        if (isMixed) {
            singleWrapper.classList.add('hidden');
            splitWrapper.classList.remove('hidden');
            singleSelect.removeAttribute('required');
        } else {
            singleWrapper.classList.remove('hidden');
            splitWrapper.classList.add('hidden');
            singleSelect.setAttribute('required', 'required');

            if (hasBawah && !hasAtas) {
                labelSingle.textContent = 'Bentuk Ujian (Kelas Bawah 1–3)';
            } else if (hasAtas && !hasBawah) {
                labelSingle.textContent = 'Bentuk Ujian (Kelas Atas 4–6)';
            } else {
                labelSingle.textContent = 'Bentuk Ujian';
            }
        }
    }

    function openEditJadwalModal(j) {
        const form = document.getElementById('formEditJadwal');
        form.action = `/ujian/jadwal-ujian/${j.id}`;

        document.getElementById('edit_subject_id').value = j.subject_id || '';
        document.getElementById('edit_nama_mapel_kustom').value = j.nama_mapel_kustom || '';
        document.getElementById('edit_school_class_id').value = j.school_class_id || '';

        document.getElementById('edit_tanggal_ujian').value = j.tanggal_ujian ? j.tanggal_ujian.substring(0, 10) : '';

        // Sesi Ke-
        const sesiSelect = document.getElementById('edit_sesi_ke');
        if (sesiSelect) {
            const sesiVal = (j.sesi_ke || 1).toString();
            let hasOption = Array.from(sesiSelect.options).some(o => o.value === sesiVal);
            if (!hasOption) {
                const opt = document.createElement('option');
                opt.value = sesiVal;
                opt.textContent = `Sesi ${sesiVal}`;
                sesiSelect.appendChild(opt);
            }
            sesiSelect.value = sesiVal;
        }

        document.getElementById('edit_jam_mulai').value = j.jam_mulai ? j.jam_mulai.substring(0, 5) : '';
        document.getElementById('edit_jam_selesai').value = j.jam_selesai ? j.jam_selesai.substring(0, 5) : '';
        document.getElementById('edit_durasi_menit').value = j.durasi_menit || 90;

        // Bentuk Ujian Fleksibel
        const standardOptions = ['Tertulis', 'CBT / Digital', 'Praktik', 'Portofolio', 'Lisan'];
        const editSelect = document.getElementById('edit_jenis_soal');
        const customWrapper = document.getElementById('edit_jenis_soal_kustom_wrapper');
        const customInput = document.getElementById('edit_jenis_soal_kustom');

        if (standardOptions.includes(j.jenis_soal)) {
            editSelect.value = j.jenis_soal;
            customWrapper.classList.add('hidden');
            customInput.value = '';
        } else if (j.jenis_soal) {
            editSelect.value = 'Lainnya';
            customWrapper.classList.remove('hidden');
            customInput.value = j.jenis_soal;
        } else {
            editSelect.value = 'Tertulis';
            customWrapper.classList.add('hidden');
            customInput.value = '';
        }

        document.getElementById('edit_catatan').value = j.catatan || '';

        const selectedLevels = (j.tingkat || '').split(',').map(s => s.trim()).filter(Boolean);
        document.querySelectorAll('.edit-tingkat-cb').forEach(cb => {
            cb.checked = selectedLevels.includes(cb.value);
        });

        document.getElementById('modalEditJadwal').classList.remove('hidden');
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateBentukUjianAddForm();
        updateCoverageScopeOptions();
    });
</script>
@endpush
