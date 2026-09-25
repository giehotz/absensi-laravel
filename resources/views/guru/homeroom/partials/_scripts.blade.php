<script>
    let currentModalStudentId = null;

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.add('hidden');
    }

    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.remove('hidden');
    }

    function previewImage(input, previewImgId, placeholderId) {
        const previewImg = document.getElementById(previewImgId);
        const placeholder = document.getElementById(placeholderId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewImg.classList.remove('hidden');
                if (placeholder) placeholder.classList.add('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function switchEditTab(tabName) {
        document.querySelectorAll('.edit-tab-pane').forEach(el => el.classList.add('hidden'));
        const pane = document.getElementById('editTabPane-' + tabName);
        if (pane) pane.classList.remove('hidden');

        document.querySelectorAll('.edit-tab-btn').forEach(btn => {
            btn.classList.remove('bg-black', 'text-white', 'shadow-[2px_2px_0px_#FFD43B]');
            btn.classList.add('bg-white', 'text-black', 'hover:bg-slate-100');
        });

        const activeBtn = document.getElementById('editTabBtn-' + tabName);
        if (activeBtn) {
            activeBtn.classList.remove('bg-white', 'text-black', 'hover:bg-slate-100');
            activeBtn.classList.add('bg-black', 'text-white', 'shadow-[2px_2px_0px_#FFD43B]');
        }
    }

    function editStudentHomeroom(data) {
        // Tab 1: Identitas & Foto
        document.getElementById('edit_stu_name').value = data.name || '';
        document.getElementById('edit_stu_nis').value = data.nis || '';
        document.getElementById('edit_stu_nisn').value = data.nisn || '';
        document.getElementById('edit_stu_gender').value = data.gender || 'L';
        document.getElementById('edit_stu_birth_place').value = data.birth_place || '';
        document.getElementById('edit_stu_birth').value = data.birth_date || '';
        document.getElementById('edit_stu_religion').value = data.religion || '';

        const classInput = document.getElementById('edit_stu_class');
        if (classInput) classInput.value = data.school_class_id || '';
        const classNameInput = document.getElementById('edit_stu_class_name');
        if (classNameInput) classNameInput.value = data.class_name || '';

        // Tab 2: Kontak & Alamat
        document.getElementById('edit_stu_phone').value = data.phone || '';
        document.getElementById('edit_stu_email').value = data.email || '';
        const passwordInput = document.getElementById('edit_stu_password');
        if (passwordInput) passwordInput.value = '';
        document.getElementById('edit_stu_address').value = data.address || '';

        // Tab 3: Status & Riwayat Masuk
        document.getElementById('edit_stu_family_status').value = data.family_status || '';
        document.getElementById('edit_stu_child_number').value = data.child_number || '';
        document.getElementById('edit_stu_previous_school').value = data.previous_school || '';
        document.getElementById('edit_stu_admission_date').value = data.admission_date || '';
        document.getElementById('edit_stu_entry_grade').value = data.entry_grade || '';

        // Tab 4: Orang Tua & Wali
        document.getElementById('edit_stu_father_name').value = data.father_name || '';
        document.getElementById('edit_stu_father_job').value = data.father_job || '';
        document.getElementById('edit_stu_mother_name').value = data.mother_name || '';
        document.getElementById('edit_stu_mother_job').value = data.mother_job || '';
        document.getElementById('edit_stu_parent_address').value = data.parent_address || '';
        document.getElementById('edit_stu_guardian_name').value = data.guardian_name || '';
        document.getElementById('edit_stu_guardian_job').value = data.guardian_job || '';
        document.getElementById('edit_stu_guardian_address').value = data.guardian_address || '';

        document.getElementById('editStudentForm').action = '/guru/kelas-binaan/students/' + data.id;

        // Reset foto input & preview
        const photoInput = document.getElementById('edit_photo_input');
        if (photoInput) photoInput.value = '';

        const photoPreview = document.getElementById('edit_photo_preview');
        const photoPlaceholder = document.getElementById('edit_photo_placeholder');
        const removePhotoWrapper = document.getElementById('remove_photo_wrapper');
        const removePhotoCheckbox = document.getElementById('edit_remove_photo');

        if (removePhotoCheckbox) removePhotoCheckbox.checked = false;

        if (data.photo_url) {
            photoPreview.src = data.photo_url;
            photoPreview.classList.remove('hidden');
            if (photoPlaceholder) photoPlaceholder.classList.add('hidden');
            if (removePhotoWrapper) {
                removePhotoWrapper.classList.remove('hidden');
                removePhotoWrapper.classList.add('flex');
            }
        } else {
            photoPreview.src = '';
            photoPreview.classList.add('hidden');
            if (photoPlaceholder) photoPlaceholder.classList.remove('hidden');
            if (removePhotoWrapper) {
                removePhotoWrapper.classList.add('hidden');
                removePhotoWrapper.classList.remove('flex');
            }
        }

        switchEditTab('identitas');
        openModal('editStudentModal');
    }

    function confirmResetPasswordHomeroom(button, studentName, passLabel) {
        Swal.fire({
            title: 'Reset Password Siswa?',
            html: `Kata sandi akun siswa <b>${studentName}</b> akan di-reset ke nilai default:<br><span class="badge font-mono font-bold text-black bg-[#FFD43B] px-2 py-0.5 mt-2 inline-block border border-black">${passLabel}</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#5294FF',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '🔑 Ya, Reset Password!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                button.closest('form').submit();
            }
        });
    }

    // Tab Switcher
    function switchTab(tabId) {
        document.querySelectorAll('.tab-pane').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('bg-white', 'text-black', '-mb-[2px]', 'font-black');
            btn.classList.add('text-slate-600', 'border-transparent', 'font-bold');
        });

        const activeContent = document.getElementById('tabContent-' + tabId);
        const activeBtn = document.getElementById('tabBtn-' + tabId);

        if (activeContent) activeContent.classList.remove('hidden');
        if (activeBtn) {
            activeBtn.classList.remove('text-slate-600', 'border-transparent', 'font-bold');
            activeBtn.classList.add('bg-white', 'text-black', '-mb-[2px]', 'font-black');
        }
    }

    // Student Detail Modal
    function openStudentDetailModal(data) {
        currentModalStudentId = data.id;

        document.getElementById('modal-student-name').innerText = data.name;
        document.getElementById('modal-student-nis').innerText = data.nis;
        document.getElementById('modal-student-nisn').innerText = data.nisn;
        document.getElementById('modal-student-gender').innerText = data.gender;
        document.getElementById('modal-student-birth').innerText = data.birth_date;
        document.getElementById('modal-student-class').innerText = data.class_name;
        document.getElementById('modal-student-phone').innerText = data.phone;

        const photoEl = document.getElementById('modal-student-photo');
        const photoPlaceholderEl = document.getElementById('modal-student-photo-placeholder');
        if (photoEl && photoPlaceholderEl) {
            if (data.photo_url) {
                photoEl.src = data.photo_url;
                photoEl.classList.remove('hidden');
                photoPlaceholderEl.classList.add('hidden');
            } else {
                photoEl.src = '';
                photoEl.classList.add('hidden');
                photoPlaceholderEl.innerText = data.initials || 'S';
                photoPlaceholderEl.classList.remove('hidden');
            }
        }

        document.getElementById('modal-parent-name').innerText = data.parent_name;
        document.getElementById('modal-parent-relation').innerText = data.parent_relation;
        document.getElementById('modal-parent-phone').innerText = data.parent_phone || '-';

        const waContainer = document.getElementById('modal-wa-container');
        const waBtn = document.getElementById('modal-wa-btn');
        if (data.wa_url) {
            waBtn.href = data.wa_url;
            waContainer.classList.remove('hidden');
        } else {
            waContainer.classList.add('hidden');
        }

        document.getElementById('modal-m-hadir').innerText = data.m_hadir;
        document.getElementById('modal-m-terlambat').innerText = data.m_terlambat;
        document.getElementById('modal-m-izin').innerText = data.m_izin;
        document.getElementById('modal-m-sakit').innerText = data.m_sakit;
        document.getElementById('modal-m-alpa').innerText = data.m_alpa;

        document.getElementById('studentDetailModal').classList.remove('hidden');
    }

    function closeStudentDetailModal() {
        document.getElementById('studentDetailModal').classList.add('hidden');
    }

    // Modal Tambah Catatan Siswa
    function openAddNoteModal(studentId = null) {
        const select = document.getElementById('note-student-id');
        if (select && studentId) {
            select.value = studentId;
        }
        document.getElementById('addNoteModal').classList.remove('hidden');
    }

    function closeAddNoteModal() {
        document.getElementById('addNoteModal').classList.add('hidden');
    }

    function openAddNoteForStudent() {
        closeStudentDetailModal();
        if (currentModalStudentId) {
            openAddNoteModal(currentModalStudentId);
        } else {
            openAddNoteModal();
        }
    }

    // Konfirmasi SweetAlert2 untuk Aksi Approve / Reject / Delete
    function confirmAction(e, title, text) {
        e.preventDefault();
        const form = e.target;
        Swal.fire({
            title: title,
            text: text,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#20C997',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
        return false;
    }
</script>
