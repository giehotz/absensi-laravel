@push('scripts')
<script>
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

    function editStudent(data) {
        // Tab 1: Identitas & Foto
        document.getElementById('edit_stu_name').value = data.name || '';
        document.getElementById('edit_stu_nis').value = data.nis || '';
        document.getElementById('edit_stu_nisn').value = data.nisn || '';
        document.getElementById('edit_stu_gender').value = data.gender || 'L';
        document.getElementById('edit_stu_birth_place').value = data.birth_place || '';
        document.getElementById('edit_stu_birth').value = data.birth_date || '';
        document.getElementById('edit_stu_religion').value = data.religion || '';

        const classSelect = document.getElementById('edit_stu_class');
        if (classSelect) classSelect.value = data.school_class_id || '';

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

        document.getElementById('editStudentForm').action = '/admin/students/' + data.id;

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

        // Buka tab pertama
        switchEditTab('identitas');

        openModal('editStudentModal');
    }

    function previewQr(data) {
        document.getElementById('qr_preview_name').innerText = data.name;
        document.getElementById('qr_preview_nis').innerText = ': ' + data.nis;
        document.getElementById('qr_preview_nisn').innerText = ': ' + (data.nisn || '-');
        document.getElementById('qr_preview_class').innerText = ': ' + data.class_name;
        document.getElementById('qr_preview_birth').innerText = ': ' + (data.birth_date || '-');
        document.getElementById('qr_preview_code').innerText = data.qr;
        document.getElementById('qr_preview_gender_badge').innerText = data.gender || 'LAKI-LAKI';
        document.getElementById('qr_print_single_btn').href = '/admin/students/' + data.id + '/card';

        // Real QR image dari /admin/students/cards
        document.getElementById('qr_preview_image').src = data.qr_image;

        // Foto preview
        const photoEl = document.getElementById('qr_preview_photo');
        const placeholderEl = document.getElementById('qr_preview_placeholder');
        if (data.photo_url) {
            photoEl.src = data.photo_url;
            photoEl.classList.remove('hidden');
            placeholderEl.classList.add('hidden');
        } else {
            photoEl.src = '';
            photoEl.classList.add('hidden');
            placeholderEl.classList.remove('hidden');
        }

        openModal('qrPreviewModal');
    }

    function confirmResetPassword(button, studentName, defaultPassword, label) {
        const form = button.closest('form');
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Reset Kata Sandi?',
                html: `
                    <div class="text-left text-sm space-y-3 mt-1">
                        <p class="text-slate-700">Kata sandi akun siswa <strong class="text-black font-bold">${studentName}</strong> akan di-reset menggunakan <strong>${label}</strong>:</p>
                        <div class="p-3 bg-[#FFF9DB] border-2 border-black font-mono font-black text-center text-black text-lg shadow-[2px_2px_0px_#000] tracking-wider">
                            ${defaultPassword}
                        </div>
                        <p class="text-xs text-slate-500 italic text-center">Setelah di-reset, siswa dapat langsung login menggunakan kata sandi default di atas.</p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#5294FF',
                cancelButtonColor: '#475569',
                confirmButtonText: '🔑 Ya, Reset Sekarang',
                cancelButtonText: 'Batal',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        } else {
            if (confirm(`Reset kata sandi siswa ${studentName} ke ${label} (${defaultPassword})?`)) {
                form.submit();
            }
        }
    }

    function confirmDeleteStudent(button, studentName) {
        const form = button.closest('form');
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus Data Siswa?',
                html: `
                    <div class="text-left text-sm space-y-2 mt-1">
                        <p class="text-slate-700">Apakah Anda yakin ingin menghapus data siswa <strong class="text-black font-bold">${studentName}</strong>?</p>
                        <p class="text-xs text-rose-700 font-bold bg-[#FFE3E3] border border-rose-300 p-2.5">
                            ⚠ Perhatian: Tindakan ini akan menghapus akun login dan seluruh histori presensi siswa ini secara permanen.
                        </p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#FF6B6B',
                cancelButtonColor: '#475569',
                confirmButtonText: '🗑️ Ya, Hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        } else {
            if (confirm(`Apakah Anda yakin ingin menghapus data siswa ${studentName}?`)) {
                form.submit();
            }
        }
    }
</script>
@endpush
