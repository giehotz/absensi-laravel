<!-- ========================================================================= -->
<!-- JAVASCRIPT: TAB SWITCHER & MODAL LOGIC -->
<!-- ========================================================================= -->
<script>
    // Tab Switcher
    function switchTab(tabId) {
        // Hide all tabs
        document.querySelectorAll('.tab-pane').forEach(function(pane) {
            pane.classList.add('hidden');
        });

        // Show target tab
        var targetPane = document.getElementById('tabContent-' + tabId);
        if (targetPane) {
            targetPane.classList.remove('hidden');
        }

        // Reset all nav button styles
        document.querySelectorAll('.nav-tab-btn').forEach(function(btn) {
            btn.classList.remove('bg-[#FFD43B]', 'text-black', 'border-black', 'shadow-[2px_2px_0px_0px_#000]');
            btn.classList.add('text-slate-600', 'border-transparent');
        });

        // Highlight active nav button
        var activeBtn = document.getElementById('navBtn-' + tabId);
        if (activeBtn) {
            activeBtn.classList.remove('text-slate-600', 'border-transparent');
            activeBtn.classList.add('bg-[#FFD43B]', 'text-black', 'border-black', 'shadow-[2px_2px_0px_0px_#000]');
        }

        // Scroll to top smoothly
        window.scrollTo({ top: 0, behavior: 'smooth' });

        // Update URL hash without reload
        if (history.pushState) {
            history.pushState(null, null, '#tab=' + tabId);
        }
    }

    // Schedule Day Selector
    function selectScheduleDay(dayNum) {
        document.querySelectorAll('.schedule-day-pane').forEach(function(pane) {
            pane.classList.add('hidden');
        });
        var targetDay = document.getElementById('scheduleDayContainer-' + dayNum);
        if (targetDay) {
            targetDay.classList.remove('hidden');
        }

        document.querySelectorAll('.day-btn').forEach(function(btn) {
            btn.classList.remove('bg-[#FFD43B]', 'shadow-[2px_2px_0px_0px_#000]');
            btn.classList.add('bg-slate-100');
        });
        var activeDayBtn = document.getElementById('dayBtn-' + dayNum);
        if (activeDayBtn) {
            activeDayBtn.classList.remove('bg-slate-100');
            activeDayBtn.classList.add('bg-[#FFD43B]', 'shadow-[2px_2px_0px_0px_#000]');
        }
    }

    // History Sub-tab Switcher
    function switchHistorySubTab(subTab) {
        var presensiPane = document.getElementById('historySubPane-presensi');
        var catatanPane = document.getElementById('historySubPane-catatan');
        var btnPresensi = document.getElementById('btnSubTab-presensi');
        var btnCatatan = document.getElementById('btnSubTab-catatan');

        if (subTab === 'presensi') {
            presensiPane.classList.remove('hidden');
            catatanPane.classList.add('hidden');
            btnPresensi.classList.add('bg-[#FFD43B]', 'shadow-[2px_2px_0px_0px_#000]');
            btnPresensi.classList.remove('border-transparent');
            btnCatatan.classList.remove('bg-[#FFD43B]', 'shadow-[2px_2px_0px_0px_#000]');
            btnCatatan.classList.add('border-transparent');
        } else {
            presensiPane.classList.add('hidden');
            catatanPane.classList.remove('hidden');
            btnCatatan.classList.add('bg-[#FFD43B]', 'shadow-[2px_2px_0px_0px_#000]');
            btnCatatan.classList.remove('border-transparent');
            btnPresensi.classList.remove('bg-[#FFD43B]', 'shadow-[2px_2px_0px_0px_#000]');
            btnPresensi.classList.add('border-transparent');
        }
    }

    // Modal Helpers
    function openModal(modalId) {
        var modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
    }

    function closeModal(modalId) {
        var modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }

    // Profile Sub-tab Switcher
    function switchProfileSubTab(subTabId) {
        var subTabs = ['card', 'biodata', 'ortu', 'keamanan'];
        subTabs.forEach(function(tab) {
            var pane = document.getElementById('profileSubPane-' + tab);
            var btn = document.getElementById('btnProfileSubTab-' + tab);
            if (pane) {
                if (tab === subTabId) {
                    pane.classList.remove('hidden');
                } else {
                    pane.classList.add('hidden');
                }
            }
            if (btn) {
                if (tab === subTabId) {
                    btn.className = 'profile-subtab-btn bg-[#FFD43B] text-black border-2 border-black py-2.5 px-2 rounded-xl text-xs font-black flex items-center justify-center gap-1.5 shadow-[2px_2px_0px_0px_#000] cursor-pointer transition-all';
                } else {
                    btn.className = 'profile-subtab-btn bg-slate-100 hover:bg-slate-200 text-slate-700 border-2 border-transparent hover:border-black py-2.5 px-2 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 cursor-pointer transition-all';
                }
            }
        });
    }

    // Preview Siswa Photo Upload
    function previewSiswaPhoto(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var preview = document.getElementById('siswaPhotoPreview');
                var placeholder = document.getElementById('siswaPhotoPlaceholder');
                if (preview) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                }
                if (placeholder) {
                    placeholder.classList.add('hidden');
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Print Student ID Card Only
    function printStudentCardOnly() {
        window.print();
    }

    // Password Visibility Toggle for Student
    function toggleSiswaPassVisibility(inputId, iconId) {
        var input = document.getElementById(inputId);
        var icon = document.getElementById(iconId);
        if (!input) return;

        if (input.type === 'password') {
            input.type = 'text';
            if (icon) icon.textContent = '🙈';
        } else {
            input.type = 'password';
            if (icon) icon.textContent = '👁️';
        }
    }

    // Initialize tab from URL or session on page load
    document.addEventListener('DOMContentLoaded', function() {

        var urlParams = new URLSearchParams(window.location.search);
        var tabParam = urlParams.get('tab');
        var hashParam = window.location.hash;

        @if($errors->has('current_password') || $errors->has('password'))
            switchTab('profil');
            switchProfileSubTab('keamanan');
        @elseif($errors->has('photo') || $errors->has('phone') || $errors->has('email') || (old('phone') || old('email')))
            switchTab('profil');
            switchProfileSubTab('biodata');
        @elseif($errors->has('type') || $errors->has('date_from') || $errors->has('date_to') || $errors->has('reason') || $errors->has('attachment'))
            switchTab('izin');
            openModal('modalLeaveRequest');
        @elseif((session('status') && str_contains(session('status'), 'Kata sandi')) || (session('success') && str_contains(session('success'), 'Kata sandi')))
            switchTab('profil');
            switchProfileSubTab('keamanan');
        @elseif((session('status') && str_contains(session('status'), 'Profil')) || (session('success') && str_contains(session('success'), 'Profil')))
            switchTab('profil');
            switchProfileSubTab('biodata');
        @else
            if (tabParam) {
                switchTab(tabParam);
            } else if (hashParam && hashParam.indexOf('tab=') !== -1) {
                var tabName = hashParam.split('tab=')[1];
                if (tabName) switchTab(tabName);
            }
        @endif
    });
</script>
