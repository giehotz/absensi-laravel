@extends('layouts.admin')

@section('title', 'Dashboard Administrator')
@section('page-title', 'Pusat Kontrol & Monitoring Absensi')

@push('styles')
<style>
    /* Neo-Brutalism Design Utilities */
    .neo-card {
        border: 2px solid #0F172A;
        box-shadow: 4px 4px 0px 0px #0F172A;
    }
    .neo-card-sm {
        border: 2px solid #0F172A;
        box-shadow: 2px 2px 0px 0px #0F172A;
    }
    .neo-btn {
        border: 2px solid #0F172A;
        box-shadow: 2px 2px 0px 0px #0F172A;
        transition: transform 0.12s ease, box-shadow 0.12s ease;
    }
    .neo-btn:hover {
        transform: translate(-1px, -1px);
        box-shadow: 3px 3px 0px 0px #0F172A;
    }
    .neo-btn:active {
        transform: translate(1px, 1px);
        box-shadow: 1px 1px 0px 0px #0F172A;
    }
    /* Custom Smooth Scrollbar */
    .custom-scroll::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .custom-scroll::-webkit-scrollbar-track {
        background: #F1F5F9;
        border-radius: 4px;
    }
    .custom-scroll::-webkit-scrollbar-thumb {
        background: #94A3B8;
        border-radius: 4px;
    }
    .custom-scroll::-webkit-scrollbar-thumb:hover {
        background: #475569;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- SECTION 2: HERO INSTITUTION BANNER & QUICK ACTIONS -->
    @include('dashboard.partials._hero-banner')

    <!-- SECTION 3: DATE FILTER & MONITORING CONTEXT BAR -->
    @include('dashboard.partials._date-bar')

    <!-- SECTION 4: KPI & INSTITUTIONAL SUMMARY METRICS (DUAL 5-COLUMN GRID) -->
    @include('dashboard.partials._metrics-grid')

    <!-- SECTION 5: MAIN WORKSPACE (8-COL TABLE + 4-COL STICKY SIDEBAR) -->
    <section class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Monitoring Presensi 17 Kelas (8 Kolom) -->
        @include('dashboard.partials._class-table')

        <!-- Sidebar Aktivitas & Kepatuhan (4 Kolom Sticky) -->
        @include('dashboard.partials._sidebar-widgets')
    </section>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Live Search & Status Filter for Class Monitoring Table
        const searchInput = document.getElementById('classSearch');
        const filterBtns = document.querySelectorAll('.filter-btn');
        const classRows = document.querySelectorAll('.class-row');
        const noClassRow = document.getElementById('noClassRow');

        let currentFilter = 'all';

        function filterClasses() {
            const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
            let visibleCount = 0;

            classRows.forEach(function (row) {
                const name = row.getAttribute('data-name') || '';
                const teacher = row.getAttribute('data-teacher') || '';
                const status = row.getAttribute('data-status') || '';

                const matchesQuery = !query || name.includes(query) || teacher.includes(query);
                const matchesStatus = (currentFilter === 'all') || (status === currentFilter);

                if (matchesQuery && matchesStatus) {
                    row.classList.remove('hidden');
                    visibleCount++;
                } else {
                    row.classList.add('hidden');
                }
            });

            if (noClassRow) {
                if (visibleCount === 0) {
                    noClassRow.classList.remove('hidden');
                } else {
                    noClassRow.classList.add('hidden');
                }
            }
        }

        if (searchInput) {
            searchInput.addEventListener('input', filterClasses);
        }

        filterBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                currentFilter = btn.getAttribute('data-filter') || 'all';

                // Update active button styles
                filterBtns.forEach(function (b) {
                    b.classList.remove('bg-slate-900', 'text-white');
                    const bFilter = b.getAttribute('data-filter');
                    if (bFilter === 'belum') {
                        b.classList.add('bg-rose-100', 'text-rose-900');
                    } else {
                        b.classList.add('bg-white', 'text-slate-700');
                    }
                });

                btn.classList.remove('bg-white', 'bg-rose-100', 'text-slate-700', 'text-rose-900');
                btn.classList.add('bg-slate-900', 'text-white');

                filterClasses();
            });
        });
    });

    // Tab Switcher for Widget 1 in Right Sidebar
    function switchActivityTab(tab) {
        const tabScan = document.getElementById('tabScan');
        const tabJurnal = document.getElementById('tabJurnal');
        const panelScan = document.getElementById('panelScan');
        const panelJurnal = document.getElementById('panelJurnal');
        const titleEl = document.getElementById('activityHeaderTitle');

        if (!tabScan || !tabJurnal || !panelScan || !panelJurnal) return;

        if (tab === 'scan') {
            tabScan.classList.remove('bg-white', 'text-slate-800');
            tabScan.classList.add('bg-slate-900', 'text-white');

            tabJurnal.classList.remove('bg-slate-900', 'text-white');
            tabJurnal.classList.add('bg-white', 'text-slate-800');

            panelScan.classList.remove('hidden');
            panelJurnal.classList.add('hidden');

            if (titleEl) titleEl.textContent = 'Aktivitas Presensi Terakhir';
        } else {
            tabJurnal.classList.remove('bg-white', 'text-slate-800');
            tabJurnal.classList.add('bg-slate-900', 'text-white');

            tabScan.classList.remove('bg-slate-900', 'text-white');
            tabScan.classList.add('bg-white', 'text-slate-800');

            panelJurnal.classList.remove('hidden');
            panelScan.classList.add('hidden');

            if (titleEl) titleEl.textContent = 'Pengajuan Izin & Jurnal Guru';
        }

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    // Modal / Prompt for Reminding Unfilled Teachers
    function notifyUnfilledTeachers() {
        const unfilledRows = Array.from(document.querySelectorAll('.class-row[data-status="belum"]'));
        const classNames = unfilledRows.map(function (row) {
            const nameEl = row.querySelector('.font-extrabold');
            const teacherEl = row.querySelector('.text-slate-700.font-semibold') || row.querySelector('.italic');
            const name = nameEl ? nameEl.textContent.trim() : 'Kelas';
            const teacher = teacherEl ? teacherEl.textContent.trim() : 'Wali Kelas';
            return `${name} (${teacher})`;
        });

        if (classNames.length === 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Semua Kelas Sudah Terisi!',
                    text: 'Luar biasa! Seluruh rombel kelas telah menyelesaikan pengisian presensi hari ini.',
                    confirmButtonColor: '#20C997'
                });
            } else {
                alert('Luar biasa! Seluruh rombel kelas telah menyelesaikan pengisian presensi hari ini.');
            }
            return;
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'info',
                title: 'Ingatkan Wali Kelas',
                html: `
                    <p class="text-sm text-slate-700 mb-2">Terdapat <b>${classNames.length} kelas</b> yang belum mengisi presensi hari ini:</p>
                    <div class="text-xs text-left bg-slate-50 border border-slate-300 p-2.5 rounded max-h-40 overflow-y-auto space-y-1 font-mono">
                        ${classNames.map(c => `<div>• ${c}</div>`).join('')}
                    </div>
                    <p class="text-[11px] text-slate-500 mt-2">Kirim broadcast pengingat presensi ke nomor WhatsApp wali kelas terkait?</p>
                `,
                showCancelButton: true,
                confirmButtonText: 'Kirim Pengingat',
                cancelButtonText: 'Tutup',
                confirmButtonColor: '#3563E9',
                cancelButtonColor: '#64748B'
            }).then(function (result) {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Pengingat Terkirim',
                        text: 'Notifikasi pengingat berhasil dikirim kepada wali kelas yang belum mengisi presensi.',
                        confirmButtonColor: '#20C997',
                        timer: 2500,
                        timerProgressBar: true
                    });
                }
            });
        } else {
            alert(`Terdapat ${classNames.length} kelas yang belum mengisi:\n` + classNames.join('\n'));
        }
    }
</script>
@endpush
