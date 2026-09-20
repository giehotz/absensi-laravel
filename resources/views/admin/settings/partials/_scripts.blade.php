<script>
    const SETTINGS_TAB_STORAGE_KEY = 'admin_settings_active_tab';

    function switchTab(name) {
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
        const panel = document.getElementById('tab-' + name);
        if (panel) {
            panel.classList.remove('hidden');
        }

        document.querySelectorAll('.settings-tab').forEach(btn => {
            if (btn.getAttribute('data-tab') === name) {
                btn.classList.add('tab-active');
            } else {
                btn.classList.remove('tab-active');
            }
        });

        // Simpan ke localStorage agar tetap di tab yang dipilih saat refresh / simpan data
        try {
            localStorage.setItem(SETTINGS_TAB_STORAGE_KEY, name);
        } catch (e) {
            console.error('Gagal menyimpan tab aktif ke localStorage:', e);
        }
    }

    // Inisialisasi tab aktif dari localStorage atau hash URL saat halaman dimuat / refresh
    function initActiveTab() {
        let activeTab = 'absensi';

        try {
            const savedTab = localStorage.getItem(SETTINGS_TAB_STORAGE_KEY);
            if (savedTab && document.getElementById('tab-' + savedTab)) {
                activeTab = savedTab;
            }
        } catch (e) {
            console.error('Gagal membaca tab aktif dari localStorage:', e);
        }

        // Dukungan hash URL jika tersedia (misal: #profil, #periode, #database)
        if (window.location.hash) {
            const hashTab = window.location.hash.replace('#', '');
            if (document.getElementById('tab-' + hashTab)) {
                activeTab = hashTab;
            }
        }

        switchTab(activeTab);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initActiveTab);
    } else {
        initActiveTab();
    }

    function editAcademicYear(data) {
        document.getElementById('edit_ay_name').value = data.name;
        if (data.semester === 'genap') {
            document.getElementById('edit_ay_semester_genap').checked = true;
        } else {
            document.getElementById('edit_ay_semester_ganjil').checked = true;
        }
        document.getElementById('edit_ay_start_date').value = data.start_date;
        document.getElementById('edit_ay_end_date').value = data.end_date;
        document.getElementById('edit_ay_is_active').checked = !!data.is_active;
        document.getElementById('editAcademicYearForm').action = '/admin/academic-years/' + data.id;
        openModal('editAcademicYearModal');
    }

    function openArchiveModal(yearId) {
        document.getElementById('archive_academic_year_id').value = yearId;
        document.getElementById('archivePreviewDates').innerText = 'Memuat data...';
        document.getElementById('archivePreviewCount').innerText = 'Memuat...';
        document.getElementById('archivePreviewCountText').innerText = '...';
        openModal('archiveModal');

        fetch('/admin/database-maintenance/preview/' + yearId)
            .then(res => res.json())
            .then(data => {
                document.getElementById('archiveModalSubtitle').innerText = `${data.name} (${data.semester.toUpperCase()})`;
                document.getElementById('archivePreviewDates').innerText = `${data.start_date} s/d ${data.end_date}`;
                document.getElementById('archivePreviewCount').innerText = `${data.active_count} baris data`;
                document.getElementById('archivePreviewCountText').innerText = `${data.active_count}`;
            })
            .catch(err => {
                console.error(err);
                document.getElementById('archivePreviewDates').innerText = 'Gagal memuat pratinjau';
            });
    }

    function openRestoreModal(yearId, name, semester, count) {
        document.getElementById('restore_academic_year_id').value = yearId;
        document.getElementById('restoreYearName').innerText = `${name} (${semester.toUpperCase()})`;
        document.getElementById('restoreCount').innerText = `${count} baris data`;
        openModal('restoreModal');
    }

    // ─── SweetAlert2 Confirmation Dialogs ───

    // Hapus Tahun Ajaran
    document.querySelectorAll('.form-delete-academic-year').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Hapus Tahun Ajaran?',
                text: 'Data tahun ajaran yang dihapus tidak dapat dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#FF6B6B',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    // Optimasi Database
    document.querySelectorAll('.form-optimize-database').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Optimasi Database?',
                text: 'Jalankan proses optimasi & defragmentasi indeks database sekarang?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#FFD43B',
                cancelButtonColor: '#64748b',
                confirmButtonText: '⚡ Ya, Jalankan!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    // Pratinjau Logo Sekolah saat file dipilih
    function previewSchoolLogo(event) {
        const input = event.target;
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewImg = document.getElementById('logo-preview-image');
                const placeholder = document.getElementById('logo-preview-placeholder');
                const filenameText = document.getElementById('logo-filename');
                
                if (previewImg) {
                    previewImg.src = e.target.result;
                    previewImg.classList.remove('hidden');
                }
                if (placeholder) {
                    placeholder.classList.add('hidden');
                }
                if (filenameText) {
                    filenameText.textContent = '✓ Dipilih: ' + file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
                    filenameText.classList.remove('hidden');
                }
            };
            reader.readAsDataURL(file);
        }
    }
</script>
