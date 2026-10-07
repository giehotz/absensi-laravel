<script>
    const allSlotsByDay = @json($allSlotsByDay ?? []);
    const teacherAssignmentsMap = @json($teacherAssignmentsMap ?? []);
    const currentClassId = {{ (int) ($selectedClassId ?? 0) }};

    let activeSelectedSchedule = null;

    // Show Detail Modal when clicking on a scheduled cell
    function showScheduleDetail(schData) {
        activeSelectedSchedule = schData;

        document.getElementById('detailSubjectName').textContent = schData.subject_name;
        document.getElementById('detailTeacherName').textContent = schData.teacher_name;
        document.getElementById('detailTeacherNip').textContent = 'NIP: ' + schData.teacher_nip;
        document.getElementById('detailClassName').textContent = 'Kelas ' + schData.class_name;
        document.getElementById('detailTimeRange').textContent = `${schData.day_name}, ${schData.start_time} - ${schData.end_time}`;
        document.getElementById('detailJpBadge').textContent = schData.jp + ' JP';

        document.getElementById('deleteScheduleForm').action = `/admin/schedules/${schData.id}`;

        document.getElementById('detailScheduleModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeDetailModal() {
        document.getElementById('detailScheduleModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        activeSelectedSchedule = null;
    }

    function confirmDeleteSchedule() {
        const form = document.getElementById('deleteScheduleForm');
        if (!form || !form.action) return;

        const subj = document.getElementById('detailSubjectName')?.textContent || 'mata pelajaran ini';
        const cls = document.getElementById('detailClassName')?.textContent || '';
        const timeRange = document.getElementById('detailTimeRange')?.textContent || '';

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus Jadwal Pelajaran?',
                html: `Apakah Anda yakin ingin menghapus jadwal <b>${subj}</b> (${cls}) pada <b>${timeRange}</b>?<br><span class="text-xs text-slate-500 mt-1 block">Slot jam terkait akan dikosongkan.</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#FF6B6B',
                cancelButtonColor: '#0F172A',
                confirmButtonText: 'Ya, Hapus Jadwal!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                focusCancel: true,
                customClass: {
                    popup: 'neo-box-lg border-3 border-black',
                    confirmButton: 'neo-btn border-2 border-black font-black',
                    cancelButton: 'neo-btn border-2 border-black font-bold'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        } else {
            if (confirm(`Apakah Anda yakin ingin menghapus jadwal ${subj}? Jadwal yang dihapus akan mengosongkan slot jam terkait.`)) {
                form.submit();
            }
        }
    }

    function triggerEditFromDetail() {
        if (!activeSelectedSchedule) return;
        const sch = activeSelectedSchedule;
        closeDetailModal();
        openEditScheduleModal(sch);
    }

    // Open Create Schedule Modal
    function openCreateScheduleModal(dayOfWeek = null, startTime = null, endTime = null, preferredJam = null) {
        document.getElementById('modalTitle').innerHTML = '<span>📅</span> Tambah Jadwal Pelajaran';
        document.getElementById('scheduleForm').action = "{{ route('admin.schedules.store') }}";
        document.getElementById('methodContainer').innerHTML = '';

        const classInput = document.querySelector('#scheduleForm input[name="school_class_id"]');
        if (classInput) {
            classInput.value = "{{ $selectedClassId }}";
        }

        document.getElementById('form_subject_id').value = '';
        document.getElementById('form_teacher_id').value = '';

        if (dayOfWeek) {
            document.getElementById('form_day_of_week').value = dayOfWeek;
        } else {
            document.getElementById('form_day_of_week').value = '';
        }

        populateJamOptions(dayOfWeek, preferredJam);

        if (startTime && endTime) {
            document.getElementById('form_start_time').value = startTime;
            document.getElementById('form_end_time').value = endTime;
        }

        onTeacherChange();
        document.getElementById('scheduleModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    // Open Edit Schedule Modal
    function openEditScheduleModal(sch) {
        document.getElementById('modalTitle').innerHTML = '<span>✏️</span> Edit Jadwal Pelajaran';
        document.getElementById('scheduleForm').action = `/admin/schedules/${sch.id}`;
        document.getElementById('methodContainer').innerHTML = '@method("PUT")';

        const classInput = document.querySelector('#scheduleForm input[name="school_class_id"]');
        if (classInput) {
            classInput.value = sch.school_class_id || "{{ $selectedClassId }}";
        }

        document.getElementById('form_day_of_week').value = sch.day_of_week;
        populateJamOptions(sch.day_of_week);

        document.getElementById('form_teacher_id').value = sch.teacher_id;
        onTeacherChange();

        document.getElementById('form_subject_id').value = sch.subject_id;
        document.getElementById('form_start_time').value = sch.start_time;
        document.getElementById('form_end_time').value = sch.end_time;

        syncRangeFromCustomTimes(sch.day_of_week, sch.start_time, sch.end_time);

        document.getElementById('scheduleModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeScheduleModal() {
        document.getElementById('scheduleModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function onDayChange() {
        const day = document.getElementById('form_day_of_week').value;
        populateJamOptions(day);
    }

    // Populate dropdowns for Multi-Jam range
    function populateJamOptions(day, preferredJam = null) {
        const startSel = document.getElementById('select_jam_start');
        const endSel = document.getElementById('select_jam_end');

        startSel.innerHTML = '<option value="">-- Jam Awal --</option>';
        endSel.innerHTML = '<option value="">-- Jam Akhir --</option>';

        if (!day || !allSlotsByDay[day] || allSlotsByDay[day].length === 0) {
            return;
        }

        // Only include KBM slots
        const kbmSlots = allSlotsByDay[day].filter(s => parseInt(s.k_jadwal) === 0);

        kbmSlots.forEach((s) => {
            const label = `Jam ke-${s.jam_ke} (${(s.start_time||'').substring(0,5)} - ${(s.end_time||'').substring(0,5)})`;
            startSel.innerHTML += `<option value="${s.jam_ke}" data-start="${(s.start_time||'').substring(0,5)}" data-end="${(s.end_time||'').substring(0,5)}">${label}</option>`;
            endSel.innerHTML += `<option value="${s.jam_ke}" data-start="${(s.start_time||'').substring(0,5)}" data-end="${(s.end_time||'').substring(0,5)}">${label}</option>`;
        });

        if (preferredJam) {
            startSel.value = preferredJam;
            endSel.value = preferredJam;
            onJamRangeChange();
        } else if (kbmSlots.length > 0) {
            startSel.value = kbmSlots[0].jam_ke;
            endSel.value = kbmSlots[0].jam_ke;
            onJamRangeChange();
        }
    }

    function onJamRangeChange() {
        const day = document.getElementById('form_day_of_week').value;
        const startSel = document.getElementById('select_jam_start');
        const endSel = document.getElementById('select_jam_end');

        const startJam = parseInt(startSel.value);
        let endJam = parseInt(endSel.value);

        if (!startJam) return;

        if (!endJam || endJam < startJam) {
            endJam = startJam;
            endSel.value = endJam;
        }

        const daySlots = allSlotsByDay[day] || [];
        const startSlot = daySlots.find(s => parseInt(s.jam_ke) === startJam);
        const endSlot = daySlots.find(s => parseInt(s.jam_ke) === endJam);

        if (startSlot && endSlot) {
            const startVal = (startSlot.start_time || '07:00').substring(0, 5);
            const endVal = (endSlot.end_time || '07:40').substring(0, 5);

            document.getElementById('form_start_time').value = startVal;
            document.getElementById('form_end_time').value = endVal;

            const jpCount = (endJam - startJam + 1);
            document.getElementById('multiJamBadge').textContent = jpCount + ' JP';
            document.getElementById('rangeTimeDisplay').textContent = `${startVal} - ${endVal}`;
        }
    }

    function syncRangeFromCustomTimes(day, start, end) {
        if (!day || !allSlotsByDay[day]) return;
        const daySlots = allSlotsByDay[day] || [];
        const startSlot = daySlots.find(s => (s.start_time || '').substring(0, 5) === start);
        const endSlot = daySlots.find(s => (s.end_time || '').substring(0, 5) === end);

        if (startSlot) {
            document.getElementById('select_jam_start').value = startSlot.jam_ke;
        }
        if (endSlot) {
            document.getElementById('select_jam_end').value = endSlot.jam_ke;
        }
        onJamRangeChange();
    }

    function onTeacherChange() {
        const teacherId = document.getElementById('form_teacher_id').value;
        const hintEl = document.getElementById('teacher_assignment_hint');
        const subjectSelect = document.getElementById('form_subject_id');

        if (!teacherId || !teacherAssignmentsMap[teacherId] || !teacherAssignmentsMap[teacherId].has_restrictions) {
            if (hintEl) hintEl.classList.add('hidden');
            Array.from(subjectSelect.options).forEach(opt => opt.disabled = false);
            return;
        }

        const restriction = teacherAssignmentsMap[teacherId];
        const allowedSubjects = restriction.allowed_subjects || [];
        const allowedClasses = restriction.allowed_classes || [];

        const isClassAllowed = allowedClasses.includes(currentClassId);

        if (hintEl) {
            hintEl.classList.remove('hidden');
            if (!isClassAllowed) {
                hintEl.className = 'mt-1 text-[11px] font-bold text-rose-900 bg-rose-100 border border-black p-2 rounded';
                hintEl.textContent = '⚠️ Peringatan: Guru ini tidak ditugaskan pada rombel/kelas saat ini.';
            } else {
                hintEl.className = 'mt-1 text-[11px] font-bold text-blue-900 bg-[#E7F5FF] border border-black p-2 rounded';
                hintEl.textContent = 'ℹ️ Guru ini memiliki penugasan khusus. Hanya mata pelajaran tertentu yang diizinkan.';
            }
        }

        Array.from(subjectSelect.options).forEach(opt => {
            if (!opt.value) return;
            const isSubjAllowed = allowedSubjects.includes(parseInt(opt.value));
            opt.disabled = (!isClassAllowed || !isSubjAllowed);
        });
    }
</script>

<style>
@media print {
    header, aside, #sidebar, .neo-btn, form, select, .breadcrumbs {
        display: none !important;
    }
    body {
        background: white !important;
        color: black !important;
    }
    .neo-box, .neo-box-lg {
        box-shadow: none !important;
        border-width: 1px !important;
    }
    table {
        min-width: 100% !important;
    }
}
</style>
