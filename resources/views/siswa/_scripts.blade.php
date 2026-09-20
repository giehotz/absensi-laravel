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

    // Initialize tab from URL on page load
    document.addEventListener('DOMContentLoaded', function() {
        var urlParams = new URLSearchParams(window.location.search);
        var tabParam = urlParams.get('tab');
        var hashParam = window.location.hash;

        if (tabParam) {
            switchTab(tabParam);
        } else if (hashParam && hashParam.indexOf('tab=') !== -1) {
            var tabName = hashParam.split('tab=')[1];
            if (tabName) switchTab(tabName);
        }
    });
</script>
