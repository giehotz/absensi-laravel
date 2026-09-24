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

        // Dukungan query param URL (misal: ?tab=kop)
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('tab')) {
            const queryTab = urlParams.get('tab');
            if (document.getElementById('tab-' + queryTab)) {
                activeTab = queryTab;
            }
        }

        // Dukungan hash URL jika tersedia (misal: #profil, #periode, #database, #kop)
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

    // Pratinjau Favicon Aplikasi saat file dipilih
    function previewAppFavicon(event) {
        const input = event.target;
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewImg = document.getElementById('favicon-preview-image');
                const placeholder = document.getElementById('favicon-preview-placeholder');
                const filenameText = document.getElementById('favicon-filename');
                
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

    // Pratinjau Logo Kop Surat (Kiri / Kanan)
    function previewKopLogo(event, previewId, placeholderId, livePreviewImgId) {
        const input = event.target;
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewImg = document.getElementById(previewId);
                const placeholder = document.getElementById(placeholderId);
                const liveImg = document.getElementById(livePreviewImgId);

                if (previewImg) {
                    previewImg.src = e.target.result;
                    previewImg.classList.remove('hidden');
                }
                if (placeholder) {
                    placeholder.classList.add('hidden');
                }
                if (liveImg) {
                    liveImg.src = e.target.result;
                    liveImg.classList.remove('hidden');
                }
            };
            reader.readAsDataURL(file);
        }
    }

    // Live update teks kop surat di kotak pratinjau cetak
    function updateKopPreview() {
        const govInput = document.getElementById('input_kop_gov');
        const instInput = document.getElementById('input_kop_inst');
        const schoolInput = document.getElementById('input_kop_school');
        const addressInput = document.getElementById('input_kop_address');
        const postalInput = document.getElementById('input_kop_postal_code');
        const phoneInput = document.getElementById('input_kop_phone');
        const emailInput = document.getElementById('input_kop_email');
        const websiteInput = document.getElementById('input_kop_website');
        const borderInput = document.getElementById('input_kop_border');

        const previewGov = document.getElementById('kop_preview_gov');
        const previewInst = document.getElementById('kop_preview_inst');
        const previewSchool = document.getElementById('kop_preview_school');
        const previewContact = document.getElementById('kop_preview_contact');
        const borderDouble = document.getElementById('kop_preview_border_double');
        const borderSingle = document.getElementById('kop_preview_border_single');

        if (previewGov && govInput) {
            previewGov.textContent = govInput.value || '';
            previewGov.style.display = govInput.value ? 'block' : 'none';
        }
        if (previewInst && instInput) {
            previewInst.textContent = instInput.value || '';
            previewInst.style.display = instInput.value ? 'block' : 'none';
        }
        if (previewSchool && schoolInput) {
            previewSchool.textContent = schoolInput.value || '';
        }

        if (previewContact) {
            let addr = addressInput ? addressInput.value : '';
            let postal = postalInput && postalInput.value ? ' Kode Pos ' + postalInput.value : '';
            let phone = phoneInput && phoneInput.value ? 'Telp: ' + phoneInput.value : '';
            let email = emailInput && emailInput.value ? 'Email: ' + emailInput.value : '';
            let web = websiteInput && websiteInput.value ? 'Website: ' + websiteInput.value : '';

            let line1 = addr + postal;
            let comms = [phone, email, web].filter(Boolean).join(' | ');

            let html = '';
            if (line1) html += line1 + '<br>';
            if (comms) html += comms;
            previewContact.innerHTML = html;
        }

        if (borderInput) {
            const val = borderInput.value;
            if (borderDouble) borderDouble.className = val === 'double' ? '' : 'hidden';
            if (borderSingle) borderSingle.className = val === 'single' ? '' : 'hidden';
        }
    }
</script>

