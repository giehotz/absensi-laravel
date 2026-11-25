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
                title: 'Nonaktifkan Siswa?',
                html: `
                    <div class="text-left text-sm space-y-2 mt-1">
                        <p class="text-slate-700">Apakah Anda yakin ingin menonaktifkan data siswa <strong class="text-black font-bold">${studentName}</strong>?</p>
                        <p class="text-xs text-amber-800 font-bold bg-[#FFF9DB] border border-amber-300 p-2.5">
                            ℹ Info: Siswa akan dipindahkan ke tab <strong>Siswa Tidak Aktif</strong> dan tidak akan muncul di presensi maupun kartu ujian. Data profil dan histori presensi tetap aman dan dapat diaktifkan kembali sewaktu-waktu.
                        </p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#FF6B6B',
                cancelButtonColor: '#475569',
                confirmButtonText: '🛑 Ya, Nonaktifkan',
                cancelButtonText: 'Batal',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        } else {
            if (confirm(`Apakah Anda yakin ingin menonaktifkan siswa ${studentName}? Siswa akan dipindahkan ke tab Siswa Tidak Aktif.`)) {
                form.submit();
            }
        }
    }

    // Live Instant Filter on Student Table
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('studentTableSearch');
        if (!searchInput) return;

        let clientSearchNoMatchRow = null;

        searchInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            const rows = document.querySelectorAll('.student-row');
            if (!rows.length) return;

            let visibleCount = 0;
            rows.forEach(row => {
                const searchData = row.getAttribute('data-search') || row.innerText.toLowerCase();
                if (!query || searchData.includes(query)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            const tbody = document.getElementById('studentsTableBody');
            if (tbody) {
                if (visibleCount === 0 && query !== '') {
                    if (!clientSearchNoMatchRow) {
                        clientSearchNoMatchRow = document.createElement('tr');
                        clientSearchNoMatchRow.id = 'clientSearchNoMatchRow';
                        clientSearchNoMatchRow.innerHTML = `
                            <td colspan="7" class="p-6 text-center bg-amber-50/60 border-b border-black">
                                <p class="text-xs font-bold text-slate-800">
                                    Tidak ada siswa yang cocok di tampilan halaman ini.
                                </p>
                                <p class="text-[11px] text-slate-500 mt-1">
                                    Tekan <kbd class="px-1.5 py-0.5 bg-white border border-black rounded text-[10px] font-bold shadow-[1px_1px_0px_#000]">Enter</kbd> atau klik tombol <b>Cari</b> untuk mencari di seluruh data siswa.
                                </p>
                            </td>
                        `;
                        tbody.appendChild(clientSearchNoMatchRow);
                    }
                    clientSearchNoMatchRow.style.display = '';
                } else if (clientSearchNoMatchRow) {
                    clientSearchNoMatchRow.style.display = 'none';
                }
            }
        });
    });

    // ==========================================
    // LOGIKA STREAMING PROGRES SINKRONISASI (SSE)
    // ==========================================
    let syncEventSource = null;
    let syncCurrentRombel = '';
    let isSyncCompleted = false;

    document.addEventListener('DOMContentLoaded', function () {
        const syncForm = document.getElementById('syncSisForm');
        if (syncForm) {
            syncForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const rombelSelect = document.getElementById('syncSisRombelSelect');
                const rombelVal = rombelSelect ? rombelSelect.value : '';
                syncCurrentRombel = rombelVal;
                closeModal('syncSisModal');
                startStudentSync(rombelVal);
            });
        }
    });

    function getSyncTimestamp() {
        const now = new Date();
        return now.toTimeString().split(' ')[0];
    }

    function addSyncLog(message, type = 'info') {
        const container = document.getElementById('syncLogContainer');
        if (!container) return;

        const time = getSyncTimestamp();
        const line = document.createElement('div');
        line.className = 'leading-relaxed break-words';

        let colorClass = 'text-[#20C997]';
        let prefix = '[•]';

        if (type === 'add') {
            colorClass = 'text-[#51CF66] font-bold';
            prefix = '[+]';
        } else if (type === 'update') {
            colorClass = 'text-[#74C0FC]';
            prefix = '[~]';
        } else if (type === 'skip') {
            colorClass = 'text-[#FFA94D]';
            prefix = '[!]';
        } else if (type === 'error') {
            colorClass = 'text-[#FF6B6B] font-bold';
            prefix = '[✗]';
        } else if (type === 'success') {
            colorClass = 'text-[#FFD43B] font-bold';
            prefix = '[★]';
        }

        line.innerHTML = `<span class="text-slate-500">[${time}]</span> <span class="${colorClass}">${prefix} ${escapeHtmlSync(message)}</span>`;
        container.appendChild(line);
        container.scrollTop = container.scrollHeight;
    }

    function clearSyncLogs() {
        const container = document.getElementById('syncLogContainer');
        if (container) {
            container.innerHTML = '<div class="text-slate-400 italic">[Log telah dibersihkan]</div>';
        }
    }

    function escapeHtmlSync(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function resetSyncUI() {
        isSyncCompleted = false;
        document.getElementById('syncModalHeader').className = 'bg-[#FFD43B] border-b-3 border-black p-4 flex items-center justify-between transition-colors duration-200';
        document.getElementById('syncModalIcon').innerText = '🚀';
        document.getElementById('syncModalTitle').innerText = 'Sinkronisasi Siswa dari SIS-EMIS';
        
        document.getElementById('syncBadgeStatus').className = 'neo-badge bg-black text-[#20C997] text-[10px] font-mono font-black uppercase';
        document.getElementById('syncBadgeStatus').innerText = 'Menghubungkan...';

        document.getElementById('syncSpinner').classList.remove('hidden');
        document.getElementById('syncStatusText').innerText = 'Menghubungkan ke API server SIS-EMIS...';
        document.getElementById('syncPercentText').innerText = '0%';
        
        const bar = document.getElementById('syncProgressBar');
        bar.style.width = '0%';
        bar.className = 'bg-[#20C997] h-full rounded transition-all duration-150 ease-out flex items-center justify-end pr-1 text-[10px] font-black text-black';

        document.getElementById('syncCurrentStudent').innerText = 'Memulai proses...';
        document.getElementById('syncCounterText').innerText = '0 / 0 Siswa';

        document.getElementById('syncSummaryImported').innerText = '0';
        document.getElementById('syncSummaryUpdated').innerText = '0';
        document.getElementById('syncSummaryTotal').innerText = '0';

        document.getElementById('syncFooterHint').innerText = 'Mohon jangan menutup jendela ini selama proses sinkronisasi berlangsung.';

        document.getElementById('btnCancelSync').classList.remove('hidden');
        document.getElementById('btnRetrySync').classList.add('hidden');
        document.getElementById('btnCloseReloadSync').classList.add('hidden');
        document.getElementById('btnSimpleClose').classList.add('hidden');
        document.getElementById('btnHeaderClose').classList.add('hidden');

        clearSyncLogs();
    }

    function startStudentSync(rombel) {
        const modal = document.getElementById('syncProgressModal');
        modal.classList.remove('hidden');

        resetSyncUI();

        let streamUrl = "{{ route('admin.students.sync-sis-stream') }}";
        if (rombel) {
            streamUrl += '?rombel=' + encodeURIComponent(rombel);
            addSyncLog('Filter rombel: ' + rombel, 'info');
        } else {
            addSyncLog('Mode: Tarik seluruh siswa dari semua rombel', 'info');
        }

        if (syncEventSource) {
            syncEventSource.close();
        }

        syncEventSource = new EventSource(streamUrl);

        syncEventSource.addEventListener('log', function (e) {
            try {
                const data = JSON.parse(e.data);
                addSyncLog(data.message, data.type || 'info');
            } catch (err) {
                console.error(err);
            }
        });

        syncEventSource.addEventListener('init', function (e) {
            try {
                const data = JSON.parse(e.data);
                document.getElementById('syncSummaryTotal').innerText = data.total;
                document.getElementById('syncCounterText').innerText = '0 / ' + data.total + ' Siswa';
                document.getElementById('syncBadgeStatus').className = 'neo-badge bg-[#FFD43B] text-black text-[10px] font-mono font-black uppercase';
                document.getElementById('syncBadgeStatus').innerText = 'Memproses...';
                document.getElementById('syncStatusText').innerText = 'Mengunduh & memproses data siswa...';
                if (data.message) {
                    addSyncLog(data.message, 'success');
                }
            } catch (err) {
                console.error(err);
            }
        });

        syncEventSource.addEventListener('progress', function (e) {
            try {
                const data = JSON.parse(e.data);
                const percent = data.percent || 0;

                document.getElementById('syncPercentText').innerText = percent + '%';
                document.getElementById('syncProgressBar').style.width = percent + '%';
                document.getElementById('syncCounterText').innerText = data.current + ' / ' + data.total + ' Siswa';
                document.getElementById('syncCurrentStudent').innerText = (data.student_name || '-') + (data.rombel ? ' (' + data.rombel + ')' : '');

                document.getElementById('syncSummaryImported').innerText = data.imported_count || 0;
                document.getElementById('syncSummaryUpdated').innerText = data.updated_count || 0;

                if (data.message) {
                    const actionType = data.action === 'imported' ? 'add' : (data.action === 'updated' ? 'update' : 'skip');
                    addSyncLog(data.message, actionType);
                }
            } catch (err) {
                console.error(err);
            }
        });

        syncEventSource.addEventListener('complete', function (e) {
            isSyncCompleted = true;
            if (syncEventSource) {
                syncEventSource.close();
                syncEventSource = null;
            }

            try {
                const data = JSON.parse(e.data);
                setSyncSuccess(data);
            } catch (err) {
                setSyncSuccess({ message: 'Sinkronisasi berhasil selesai!' });
            }
        });

        syncEventSource.addEventListener('error', function (e) {
            let errorMsg = 'Terjadi kesalahan atau koneksi terputus ke server.';
            try {
                if (e.data) {
                    const data = JSON.parse(e.data);
                    if (data.message) errorMsg = data.message;
                }
            } catch (err) {}

            if (!isSyncCompleted) {
                setSyncError(errorMsg);
            }

            if (syncEventSource) {
                syncEventSource.close();
                syncEventSource = null;
            }
        });

        syncEventSource.onerror = function () {
            if (!isSyncCompleted) {
                setSyncError('Koneksi stream terputus secara tidak terduga.');
                if (syncEventSource) {
                    syncEventSource.close();
                    syncEventSource = null;
                }
            }
        };
    }

    function setSyncSuccess(data) {
        document.getElementById('syncModalHeader').className = 'bg-[#20C997] border-b-3 border-black p-4 flex items-center justify-between transition-colors duration-200';
        document.getElementById('syncModalIcon').innerText = '🎉';
        document.getElementById('syncModalTitle').innerText = 'Sinkronisasi Berhasil Selesai!';

        document.getElementById('syncBadgeStatus').className = 'neo-badge bg-black text-[#20C997] text-[10px] font-mono font-black uppercase';
        document.getElementById('syncBadgeStatus').innerText = 'Selesai 100%';

        document.getElementById('syncSpinner').classList.add('hidden');
        document.getElementById('syncStatusText').innerText = 'Seluruh data siswa berhasil diproses!';
        document.getElementById('syncPercentText').innerText = '100%';
        document.getElementById('syncProgressBar').style.width = '100%';

        if (data.imported !== undefined) document.getElementById('syncSummaryImported').innerText = data.imported;
        if (data.updated !== undefined) document.getElementById('syncSummaryUpdated').innerText = data.updated;
        if (data.total !== undefined) document.getElementById('syncSummaryTotal').innerText = data.total;

        document.getElementById('syncFooterHint').innerText = 'Data siswa dan akun telah diperbarui di database absensi.';
        addSyncLog(data.message || 'Sinkronisasi berhasil rampung!', 'success');

        document.getElementById('btnCancelSync').classList.add('hidden');
        document.getElementById('btnCloseReloadSync').classList.remove('hidden');
        document.getElementById('btnHeaderClose').classList.remove('hidden');
    }

    function setSyncError(message) {
        document.getElementById('syncModalHeader').className = 'bg-[#FF6B6B] border-b-3 border-black p-4 flex items-center justify-between transition-colors duration-200';
        document.getElementById('syncModalIcon').innerText = '⚠️';
        document.getElementById('syncModalTitle').innerText = 'Sinkronisasi Gagal';

        document.getElementById('syncBadgeStatus').className = 'neo-badge bg-black text-[#FF6B6B] text-[10px] font-mono font-black uppercase';
        document.getElementById('syncBadgeStatus').innerText = 'Gagal';

        document.getElementById('syncSpinner').classList.add('hidden');
        document.getElementById('syncStatusText').innerText = 'Terjadi kesalahan saat sinkronisasi.';

        const bar = document.getElementById('syncProgressBar');
        bar.className = 'bg-[#FF6B6B] h-full rounded transition-all duration-150 ease-out flex items-center justify-end pr-1 text-[10px] font-black text-black';

        document.getElementById('syncFooterHint').innerText = 'Silakan periksa log di atas atau coba kembali.';
        addSyncLog('GAGAL: ' + message, 'error');

        document.getElementById('btnCancelSync').classList.add('hidden');
        document.getElementById('btnRetrySync').classList.remove('hidden');
        document.getElementById('btnSimpleClose').classList.remove('hidden');
        document.getElementById('btnHeaderClose').classList.remove('hidden');
    }

    function cancelSync() {
        if (syncEventSource) {
            syncEventSource.close();
            syncEventSource = null;
        }
        addSyncLog('Sinkronisasi dibatalkan oleh pengguna.', 'skip');
        document.getElementById('syncBadgeStatus').innerText = 'Dibatalkan';
        document.getElementById('syncSpinner').classList.add('hidden');
        document.getElementById('syncStatusText').innerText = 'Proses dibatalkan.';
        document.getElementById('btnCancelSync').classList.add('hidden');
        document.getElementById('btnSimpleClose').classList.remove('hidden');
        document.getElementById('btnHeaderClose').classList.remove('hidden');
    }

    function retrySync() {
        startStudentSync(syncCurrentRombel);
    }

    function closeSyncModal() {
        if (syncEventSource) {
            syncEventSource.close();
            syncEventSource = null;
        }
        document.getElementById('syncProgressModal').classList.add('hidden');
    }

    function finishAndReload() {
        window.location.reload();
    }
</script>
@endpush
