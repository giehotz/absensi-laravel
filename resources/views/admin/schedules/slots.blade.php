@extends('layouts.admin')

@section('title', 'Template Slot Jam KBM (EMIS GTK)')
@section('page-title', 'Template Slot Jam KBM Kolektif')

@section('content')
<div class="space-y-6">
    <!-- Navigation Tabs -->
    <div class="flex border-b-2 border-black gap-2">
        <a href="{{ route('admin.schedules.index') }}" 
           class="px-5 py-2.5 font-heading font-black text-xs uppercase border-t-2 border-x-2 border-black transition-all bg-slate-100 text-slate-600 hover:bg-slate-200">
            📅 Plot Jadwal Kelas
        </a>
        <a href="{{ route('admin.schedules.slots.index') }}" 
           class="px-5 py-2.5 font-heading font-black text-xs uppercase border-t-2 border-x-2 border-black transition-all bg-white -mb-[2px] border-b-2 border-b-white z-10 text-black shadow-sm">
            ⚙️ Template Jam Simpatika / EMIS GTK
        </a>
    </div>

    <!-- Header Title Card -->
    <div class="bg-[#FFF3BF] neo-box-lg p-6 sm:p-7 text-black relative overflow-hidden flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5">
        <div class="space-y-2 z-10 max-w-2xl">
            <div class="flex items-center gap-2">
                <span class="neo-badge bg-[#CC5DE8] text-white font-bold text-[11px]">EMIS GTK / SIMPATIKA</span>
                <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border border-black">
                    {{ $academicYear->name ?? 'Tahun Ajaran Aktif' }}
                </span>
                <span class="text-xs font-mono font-bold bg-white px-2 py-0.5 border border-black hidden sm:inline-block">
                    Semester {{ ucfirst($academicYear->semester ?? 'Ganjil') }}
                </span>
            </div>
            <h1 class="font-heading text-2xl sm:text-3xl font-black tracking-tight text-black uppercase">
                MATRIKS SLOT JAM KBM & KEGIATAN
            </h1>
            <p class="text-xs sm:text-sm font-semibold text-slate-800">
                Atur waktu jam tatap muka dan kegiatan terjadwal (Upacara, Istirahat, Pembiasaan, Religi) per hari secara fleksibel sesuai kalender jam madrasah/sekolah.
            </p>
        </div>

        <div class="z-10 flex flex-wrap items-center gap-2.5 shrink-0">
            <form id="resetDefaultSlotsForm" action="{{ route('admin.schedules.slots.reset-default') }}" method="POST" onsubmit="return false;">
                @csrf
                <button type="button" onclick="confirmResetDefaultSlots()" class="neo-btn bg-white hover:bg-slate-100 text-black text-xs font-bold px-3.5 py-2.5 flex items-center gap-1.5 cursor-pointer shadow-[2px_2px_0px_0px_#000]">
                    <span>🔄</span> Muat Standar Madrasah
                </button>
            </form>

            <button type="button" id="btnSaveSlotsTop" onclick="saveSlots(false)" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black text-xs font-black px-4 py-2.5 flex items-center gap-1.5 cursor-pointer shadow-[2px_2px_0px_0px_#000]">
                <span>💾</span> Simpan Template Slot
            </button>
        </div>
    </div>

    <!-- Alert / Status Notifikasi -->
    @if(session('success'))
    <div class="bg-[#D3F9D8] border-2 border-black p-4 neo-box text-emerald-950 font-bold text-xs flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span>✅</span> {{ session('success') }}
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-black font-black hover:opacity-75">✕</button>
    </div>
    @endif

    <div id="unsavedAlert" class="bg-[#FFE3E3] border-2 border-black p-3.5 neo-box text-rose-950 font-bold text-xs flex items-center justify-between hidden">
        <div class="flex items-center gap-2">
            <span class="text-base">⚠️</span> 
            <span>Ada perubahan slot yang belum disimpan ke database. Tekan tombol <b>"Simpan Template Slot"</b> untuk menerapkan perubahan.</span>
        </div>
        <button type="button" onclick="saveSlots(false)" class="neo-btn bg-black text-white text-[11px] font-bold px-3 py-1 cursor-pointer">
            Simpan Sekarang
        </button>
    </div>

    <!-- Quick Tools & Legend Bar -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        <!-- Quick Duration Presets -->
        <div class="lg:col-span-6 bg-white neo-box p-4 space-y-2">
            <div class="text-[11px] font-black uppercase tracking-wider text-slate-700 flex items-center justify-between">
                <span class="flex items-center gap-1.5">
                    <span>⚡</span> Preset Durasi KBM Cepat:
                </span>
                <span class="text-[10px] font-mono text-slate-500">Otomatis hitung jam tatap muka</span>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="applyQuickDuration(35)" class="neo-btn bg-[#E7F5FF] hover:bg-[#d0ebff] text-blue-900 border border-black text-xs font-bold px-3 py-1.5 cursor-pointer">
                    ⚡ 35 Menit (MI / SD)
                </button>
                <button type="button" onclick="applyQuickDuration(40)" class="neo-btn bg-[#FFF9DB] hover:bg-[#fff3bf] text-amber-950 border border-black text-xs font-bold px-3 py-1.5 cursor-pointer">
                    ⚡ 40 Menit (MTs / SMP)
                </button>
                <button type="button" onclick="applyQuickDuration(45)" class="neo-btn bg-[#F3F0FF] hover:bg-[#e5dbff] text-purple-900 border border-black text-xs font-bold px-3 py-1.5 cursor-pointer">
                    ⚡ 45 Menit (MA / SMA)
                </button>
                <button type="button" onclick="addMaxJamRow()" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black border border-black text-xs font-bold px-3 py-1.5 cursor-pointer">
                    ➕ Tambah Baris Jam
                </button>
            </div>
        </div>

        <!-- Legend Kategori Kegiatan -->
        <div class="lg:col-span-6 bg-white neo-box p-4 space-y-2">
            <div class="text-[11px] font-black uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                <span>🏷️</span> Kategori Kegiatan (EMIS GTK):
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5">
                @foreach($kegiatanColors as $kode => $color)
                <div class="flex items-center gap-1.5 px-2 py-1 border border-black rounded text-[11px] font-bold {{ $color['bg'] }} {{ $color['text'] }}">
                    <span class="w-2.5 h-2.5 rounded-full border border-black {{ $color['badge_bg'] }} shrink-0"></span>
                    <span class="truncate">{{ $color['name'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Matriks Mingguan EMIS GTK (Kolom: Hari, Baris: Jam) -->
    <div class="bg-white neo-box overflow-hidden">
        <div class="p-4 border-b-2 border-black bg-[#FFF9DB] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="font-heading font-black text-sm uppercase text-black flex items-center gap-1.5">
                    <span>📋</span> MATRIKS JADWAL MINGGUAN (EMIS GTK)
                </h3>
                <p class="text-[11px] font-semibold text-slate-600">
                    Klik pada sel jam untuk mengubah waktu atau jenis kegiatan. Setiap hari dapat memiliki jumlah jam aktif berbeda.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-mono font-bold bg-white px-2.5 py-1 border border-black">
                    Auto-Cascade: <span class="text-emerald-700 font-black">Aktif</span>
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[980px]" id="slotMatrixTable">
                <thead>
                    <tr class="bg-slate-100 border-b-2 border-black text-xs font-black uppercase text-black">
                        <th class="p-3 border-r-2 border-black text-center w-24 bg-slate-200/70">Jam</th>
                        @foreach([1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'] as $dNum => $dName)
                            <th class="p-3 border-r-2 border-black text-center min-w-[130px] {{ $dNum === 7 ? 'bg-rose-50 text-rose-900' : ($dNum === 5 ? 'bg-emerald-50 text-emerald-950' : '') }}">
                                <div>{{ $dName }}</div>
                                <div class="text-[10px] font-mono font-semibold opacity-75" id="header_count_{{ $dNum }}">
                                    {{ $dNum === 7 ? '(Libur)' : (count($slotsByDay[$dNum] ?? []).' Jam Aktif') }}
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black text-xs font-medium" id="slotMatrixBody">
                    <!-- Matrix rows (Jam 1 s.d. Jam N) will be rendered by renderMatrixGrid() -->
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t-2 border-black bg-slate-50 flex flex-col sm:flex-row items-center justify-between gap-3">
            <span class="text-xs font-bold text-slate-600">
                💡 Klik sel slot untuk mengatur waktu atau kegiatan. Gunakan tombol simpan di bawah setelah selesai melakukan konfigurasi.
            </span>
            <div class="flex items-center gap-2">
                <button type="button" onclick="saveSlots(false)" id="btnSaveSlotsBottom" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black text-xs font-black px-5 py-2.5 flex items-center gap-1.5 cursor-pointer shadow-[2px_2px_0px_0px_#000]">
                    <span>💾</span> Simpan Template Slot
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Edit Slot Jam Interaktif -->
    <div id="editSlotModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white neo-box-lg max-w-md w-full p-6 space-y-4 relative" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between border-b-2 border-black pb-3">
                <h3 class="font-heading font-black text-base text-black flex items-center gap-1.5">
                    <span>⏱️</span> <span id="modalHeaderTitle">Edit Slot Jam</span>
                </h3>
                <button type="button" onclick="closeEditModal()" class="text-black font-black text-lg hover:opacity-75 cursor-pointer">✕</button>
            </div>

            <div class="space-y-3.5">
                <input type="hidden" id="edit_day_num" value="1">
                <input type="hidden" id="edit_jam_ke" value="1">

                <!-- Jenis Kegiatan (k_jadwal) -->
                <div>
                    <label class="block text-xs font-black uppercase text-black mb-1">
                        Kategori Kegiatan (EMIS GTK):
                    </label>
                    <select id="edit_k_jadwal" onchange="onKategoriSelectChange()" class="w-full border-2 border-black rounded p-2 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-[#FFD43B]">
                        <option value="0">0 - Kegiatan Belajar Mengajar (KBM)</option>
                        <option value="3">3 - Upacara Bendera</option>
                        <option value="4">4 - Istirahat</option>
                        <option value="5">5 - Senam Pagi</option>
                        <option value="6">6 - Pembiasaan / Literasi</option>
                        <option value="7">7 - Religi / Shalat Berjamaah</option>
                    </select>
                </div>

                <!-- Label / Keterangan Kustom -->
                <div>
                    <label class="block text-xs font-black uppercase text-black mb-1">
                        Nama / Keterangan Slot:
                    </label>
                    <input type="text" id="edit_name" placeholder="Contoh: Jam ke-1, Istirahat 1, dll" 
                           class="w-full border-2 border-black rounded p-2 text-xs font-bold text-black focus:outline-none focus:ring-2 focus:ring-[#FFD43B]">
                </div>

                <!-- Rentang Waktu (Mulai & Selesai) -->
                <div class="grid grid-cols-2 gap-3 bg-slate-50 border-2 border-black p-3 rounded">
                    <div>
                        <label class="block text-[11px] font-black uppercase text-slate-700 mb-1">
                            Jam Mulai:
                        </label>
                        <input type="time" id="edit_start_time" onchange="onTimeInputChange()" 
                               class="w-full border-2 border-black rounded p-1.5 text-xs font-mono font-bold bg-white text-black">
                    </div>
                    <div>
                        <label class="block text-[11px] font-black uppercase text-slate-700 mb-1">
                            Jam Berakhir:
                        </label>
                        <input type="time" id="edit_end_time" onchange="onTimeInputChange()" 
                               class="w-full border-2 border-black rounded p-1.5 text-xs font-mono font-bold bg-white text-black">
                    </div>
                </div>

                <!-- Info Durasi Slot -->
                <div class="flex items-center justify-between text-xs px-1">
                    <span class="text-slate-600 font-bold">Durasi Slot:</span>
                    <span id="edit_duration_badge" class="font-mono font-black text-blue-900 bg-[#E7F5FF] border border-black px-2 py-0.5 rounded">
                        40 Menit
                    </span>
                </div>

                <!-- Opsi Auto-Cascade -->
                <div class="bg-[#FFF9DB] border-2 border-black p-3 rounded space-y-1">
                    <label class="flex items-start gap-2 cursor-pointer select-none">
                        <input type="checkbox" id="edit_cascade" checked class="w-4 h-4 mt-0.5 rounded border-2 border-black text-[#5294FF] focus:ring-0">
                        <div class="text-xs">
                            <span class="font-black text-black block">Geser Jam Berikutnya (Auto-Cascade)</span>
                            <span class="text-[11px] font-semibold text-slate-700 block leading-tight">
                                Jam setelah slot ini pada hari yang sama akan otomatis disesuaikan waktunya.
                            </span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Tombol Aksi Modal -->
            <div class="flex items-center justify-between border-t-2 border-black pt-3">
                <button type="button" id="btnToggleSlotState" onclick="toggleCurrentSlotActive()" 
                        class="neo-btn bg-[#FFE3E3] hover:bg-[#ffc9c9] text-rose-950 text-xs font-bold px-3 py-2 cursor-pointer border border-black">
                    🗑️ Nonaktifkan (Pulang)
                </button>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="closeEditModal()" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-xs font-bold px-3.5 py-2 cursor-pointer">
                        Batal
                    </button>
                    <button type="button" onclick="applySlotChanges()" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black text-xs font-black px-4 py-2 cursor-pointer">
                        Terapkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Peringatan Bentrok Jadwal Kelas (Conflict Resolution) -->
    <div id="conflictModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white neo-box-lg max-w-2xl w-full p-6 space-y-4 max-h-[90vh] flex flex-col" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between border-b-2 border-black pb-3 shrink-0">
                <div class="flex items-center gap-2">
                    <span class="text-2xl">⚠️</span>
                    <div>
                        <h3 class="font-heading font-black text-base text-rose-600 uppercase">
                            Bentrok dengan Jadwal Pelajaran Aktif
                        </h3>
                        <p class="text-xs font-medium text-slate-600">
                            Perubahan template jam bertabrakan dengan jadwal pelajaran yang sudah terpasang.
                        </p>
                    </div>
                </div>
                <button type="button" onclick="closeConflictModal()" class="text-black font-black text-lg hover:opacity-75 cursor-pointer">✕</button>
            </div>

            <div class="overflow-y-auto space-y-3 pr-1 flex-1" id="conflictListContainer">
                <!-- Daftar bentrok dirender via JS -->
            </div>

            <div class="bg-[#FFF9DB] p-3 border-2 border-black rounded text-xs space-y-1 shrink-0">
                <span class="font-black text-black block">Pilihan Tindakan:</span>
                <p class="text-[11px] text-slate-700 leading-tight">
                    Jika Anda menekan <b>"Terapkan & Timpa (Force Sync)"</b>, jadwal pelajaran yang bertabrakan akan otomatis disesuaikan waktunya mengikuti template slot baru.
                </p>
            </div>

            <div class="flex items-center justify-end gap-2 border-t-2 border-black pt-3 shrink-0">
                <button type="button" onclick="closeConflictModal()" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-xs font-bold px-4 py-2 cursor-pointer">
                    Batal & Cek Ulang
                </button>
                <button type="button" onclick="saveSlots(true)" class="neo-btn bg-[#FF6B6B] hover:bg-rose-500 text-white text-xs font-black px-4 py-2 cursor-pointer">
                    ⚠️ Terapkan & Timpa (Force Sync)
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // State Template Slots per Hari (1 = Senin s.d. 6 = Sabtu)
    let slotsByDay = @json($slotsByDay);
    let maxJamPerWeek = {{ $maxJamPerWeek ?? 10 }};
    let hasUnsavedChanges = false;

    const DAYS_MAP = {
        1: 'Senin',
        2: 'Selasa',
        3: 'Rabu',
        4: 'Kamis',
        5: 'Jumat',
        6: 'Sabtu'
    };

    const KEGIATAN_COLORS = @json($kegiatanColors);
    const KEGIATAN_LABELS = @json($kegiatanLabels);

    // Initial render
    document.addEventListener('DOMContentLoaded', function() {
        renderMatrixGrid();
    });

    function setUnsaved(state) {
        hasUnsavedChanges = state;
        const alertBox = document.getElementById('unsavedAlert');
        if (alertBox) {
            if (state) {
                alertBox.classList.remove('hidden');
            } else {
                alertBox.classList.add('hidden');
            }
        }
    }

    window.addEventListener('beforeunload', function(e) {
        if (hasUnsavedChanges) {
            e.preventDefault();
            e.returnValue = 'Ada perubahan slot yang belum disimpan. Yakin ingin keluar?';
        }
    });

    function confirmResetDefaultSlots() {
        const form = document.getElementById('resetDefaultSlotsForm');
        if (!form) return;

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Muat Standar Madrasah?',
                text: 'Seluruh konfigurasi slot jam saat ini akan diganti ke preset default Madrasah/Kemenag. Tindakan ini akan menimpa pengaturan jam.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#FF6B6B',
                cancelButtonColor: '#0F172A',
                confirmButtonText: 'Ya, Muat Standar!',
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
            if (confirm('Apakah Anda yakin ingin memuat ulang template standar Madrasah/Kemenag? Seluruh konfigurasi slot saat ini akan diganti ke preset default.')) {
                form.submit();
            }
        }
    }

    function addMaxJamRow() {
        if (maxJamPerWeek >= 12) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Batas Jam Maksimum',
                    text: 'Maksimal baris jam adalah 12 jam per hari.',
                    confirmButtonColor: '#0F172A',
                    customClass: {
                        popup: 'neo-box-lg border-3 border-black',
                        confirmButton: 'neo-btn border-2 border-black font-black'
                    }
                });
            } else {
                alert('Maksimal baris jam adalah 12 jam per hari.');
            }
            return;
        }
        maxJamPerWeek++;
        renderMatrixGrid();
    }

    function timeToMinutes(timeStr) {
        if (!timeStr) return 0;
        const [h, m] = timeStr.split(':').map(Number);
        return (h * 60) + m;
    }

    function minutesToTime(mins) {
        const h = Math.floor(mins / 60);
        const m = mins % 60;
        return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
    }

    function renderMatrixGrid() {
        const tbody = document.getElementById('slotMatrixBody');
        if (!tbody) return;

        // Recalculate max active jam
        let calculatedMax = 10;
        for (let d = 1; d <= 6; d++) {
            const slots = slotsByDay[d] || [];
            if (slots.length > 0) {
                const dayMax = Math.max(...slots.map(s => parseInt(s.jam_ke) || 0));
                if (dayMax > calculatedMax) calculatedMax = dayMax;
            }
        }
        maxJamPerWeek = Math.max(maxJamPerWeek, calculatedMax);

        // Update header counts
        for (let d = 1; d <= 6; d++) {
            const countEl = document.getElementById('header_count_' + d);
            if (countEl) {
                const count = (slotsByDay[d] || []).length;
                countEl.textContent = count + ' Jam Aktif';
            }
        }

        let html = '';

        for (let jam = 1; jam <= maxJamPerWeek; jam++) {
            html += `<tr class="hover:bg-slate-50/75 transition-colors">`;
            
            // Kolom Nomor Jam
            html += `
                <td class="p-2.5 border-r-2 border-black text-center font-heading font-black text-xs bg-slate-100">
                    <div>Jam ${jam}</div>
                </td>
            `;

            // Kolom Senin s.d. Sabtu
            for (let day = 1; day <= 6; day++) {
                const daySlots = slotsByDay[day] || [];
                const slot = daySlots.find(s => parseInt(s.jam_ke) === jam);

                if (slot) {
                    const kCode = slot.k_jadwal || 0;
                    const color = KEGIATAN_COLORS[kCode] || KEGIATAN_COLORS[0];
                    const startTime = (slot.start_time || '07:00').substring(0, 5);
                    const endTime = (slot.end_time || '07:40').substring(0, 5);
                    const slotName = slot.name || color.name;

                    html += `
                        <td class="p-1.5 border-r-2 border-black text-center align-middle cursor-pointer" onclick="openEditSlotModal(${day}, ${jam})">
                            <div class="border-2 border-black p-2 rounded shadow-[2px_2px_0px_0px_#000] hover:shadow-[3px_3px_0px_0px_#000] transition-all flex flex-col justify-between h-20 ${color.bg}">
                                <div class="flex items-center justify-between gap-1 text-[10px] font-mono font-black text-slate-800 border-b border-black/30 pb-0.5">
                                    <span>${startTime} - ${endTime}</span>
                                    <span class="w-2 h-2 rounded-full border border-black ${color.badge_bg}"></span>
                                </div>
                                <div class="font-heading font-black text-xs ${color.text} truncate text-center my-auto">
                                    ${escapeHtml(slotName)}
                                </div>
                                <div class="text-[9px] font-bold text-slate-600 truncate">
                                    ${color.name}
                                </div>
                            </div>
                        </td>
                    `;
                } else {
                    // Non-Aktif / Pulang
                    html += `
                        <td class="p-1.5 border-r-2 border-black text-center align-middle bg-slate-50/70">
                            <div onclick="activateSlot(${day}, ${jam})" 
                                 class="border-2 border-dashed border-slate-300 hover:border-black p-2 rounded flex flex-col items-center justify-center h-20 text-slate-400 hover:text-black hover:bg-[#FFF9DB] transition-all cursor-pointer group">
                                <span class="text-[10px] font-bold uppercase tracking-wider block group-hover:hidden">Pulang</span>
                                <span class="text-[10px] font-black text-emerald-800 hidden group-hover:block">+ Aktifkan</span>
                            </div>
                        </td>
                    `;
                }
            }

            // Kolom Minggu (Libur)
            html += `
                <td class="p-1.5 border-r-2 border-black text-center align-middle bg-rose-50/30">
                    <div class="border-2 border-dashed border-rose-200 p-2 rounded flex flex-col items-center justify-center h-20 text-rose-300">
                        <span class="text-[10px] font-bold uppercase tracking-wider">Libur</span>
                    </div>
                </td>
            `;

            html += `</tr>`;
        }

        tbody.innerHTML = html;
    }

    function openEditSlotModal(day, jam) {
        const daySlots = slotsByDay[day] || [];
        let slot = daySlots.find(s => parseInt(s.jam_ke) === jam);

        if (!slot) {
            activateSlot(day, jam);
            return;
        }

        document.getElementById('edit_day_num').value = day;
        document.getElementById('edit_jam_ke').value = jam;
        document.getElementById('modalHeaderTitle').textContent = `${DAYS_MAP[day]} - Jam ke-${jam}`;

        document.getElementById('edit_k_jadwal').value = slot.k_jadwal;
        document.getElementById('edit_name').value = slot.name || '';
        document.getElementById('edit_start_time').value = (slot.start_time || '07:00').substring(0, 5);
        document.getElementById('edit_end_time').value = (slot.end_time || '07:40').substring(0, 5);

        document.getElementById('btnToggleSlotState').textContent = '🗑️ Nonaktifkan Jam Ini (Tandai Pulang)';
        document.getElementById('btnToggleSlotState').className = 'neo-btn bg-[#FFE3E3] hover:bg-[#ffc9c9] text-rose-950 text-xs font-bold px-3 py-2 cursor-pointer border border-black';

        updateDurationBadge();
        document.getElementById('editSlotModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editSlotModal').classList.add('hidden');
    }

    function onKategoriSelectChange() {
        const sel = document.getElementById('edit_k_jadwal');
        const nameInput = document.getElementById('edit_name');
        const kCode = parseInt(sel.value);
        if (kCode !== 0) {
            nameInput.value = KEGIATAN_LABELS[kCode] || '';
        } else {
            const jam = document.getElementById('edit_jam_ke').value;
            nameInput.value = 'Jam ke-' + jam;
        }
    }

    function onTimeInputChange() {
        updateDurationBadge();
    }

    function updateDurationBadge() {
        const start = document.getElementById('edit_start_time').value;
        const end = document.getElementById('edit_end_time').value;
        if (!start || !end) return;

        const dur = timeToMinutes(end) - timeToMinutes(start);
        const badge = document.getElementById('edit_duration_badge');
        if (dur > 0) {
            badge.textContent = dur + ' Menit';
            badge.className = 'font-mono font-black text-blue-900 bg-[#E7F5FF] border border-black px-2 py-0.5 rounded';
        } else {
            badge.textContent = 'Tidak Valid';
            badge.className = 'font-mono font-black text-rose-700 bg-rose-100 border border-black px-2 py-0.5 rounded';
        }
    }

    function applySlotChanges() {
        const day = parseInt(document.getElementById('edit_day_num').value);
        const jam = parseInt(document.getElementById('edit_jam_ke').value);
        const kCode = parseInt(document.getElementById('edit_k_jadwal').value);
        const name = document.getElementById('edit_name').value.trim();
        const start = document.getElementById('edit_start_time').value;
        const end = document.getElementById('edit_end_time').value;
        const cascade = document.getElementById('edit_cascade').checked;

        if (timeToMinutes(end) <= timeToMinutes(start)) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Waktu Tidak Valid',
                    text: 'Jam berakhir harus lebih besar daripada jam mulai.',
                    confirmButtonColor: '#FF6B6B',
                    customClass: {
                        popup: 'neo-box-lg border-3 border-black',
                        confirmButton: 'neo-btn border-2 border-black font-black'
                    }
                });
            } else {
                alert('Jam berakhir harus lebih besar daripada jam mulai.');
            }
            return;
        }

        const daySlots = slotsByDay[day] || [];
        let slot = daySlots.find(s => parseInt(s.jam_ke) === jam);

        if (!slot) {
            slot = {
                id: null,
                day_of_week: day,
                jam_ke: jam,
                k_jadwal: kCode,
                name: name,
                start_time: start,
                end_time: end
            };
            daySlots.push(slot);
        } else {
            slot.k_jadwal = kCode;
            slot.name = name;
            slot.start_time = start;
            slot.end_time = end;
        }

        // Sort slots by jam_ke
        daySlots.sort((a, b) => parseInt(a.jam_ke) - parseInt(b.jam_ke));

        // Auto-Cascade jika dicentang
        if (cascade) {
            cascadeDaySlots(day, jam, end);
        }

        slotsByDay[day] = daySlots;
        setUnsaved(true);
        closeEditModal();
        renderMatrixGrid();
    }

    function cascadeDaySlots(day, fromJam, newStartTime) {
        const daySlots = slotsByDay[day] || [];
        let currentStart = newStartTime;

        for (let i = 0; i < daySlots.length; i++) {
            const s = daySlots[i];
            if (parseInt(s.jam_ke) > fromJam) {
                const oldDur = Math.max(15, timeToMinutes(s.end_time) - timeToMinutes(s.start_time));
                s.start_time = currentStart;
                const newEndMins = timeToMinutes(currentStart) + oldDur;
                s.end_time = minutesToTime(newEndMins);
                currentStart = s.end_time;
            }
        }
    }

    function activateSlot(day, jam) {
        const daySlots = slotsByDay[day] || [];
        
        // Find previous slot to determine default start time
        let prevEnd = '07:00';
        const prevSlot = daySlots.filter(s => parseInt(s.jam_ke) < jam).pop();
        if (prevSlot && prevSlot.end_time) {
            prevEnd = prevSlot.end_time;
        }

        const newEnd = minutesToTime(timeToMinutes(prevEnd) + 40);

        const newSlot = {
            id: null,
            day_of_week: day,
            jam_ke: jam,
            k_jadwal: 0,
            name: 'Jam ke-' + jam,
            start_time: prevEnd,
            end_time: newEnd
        };

        daySlots.push(newSlot);
        daySlots.sort((a, b) => parseInt(a.jam_ke) - parseInt(b.jam_ke));
        slotsByDay[day] = daySlots;

        setUnsaved(true);
        renderMatrixGrid();
        openEditSlotModal(day, jam);
    }

    function toggleCurrentSlotActive() {
        const day = parseInt(document.getElementById('edit_day_num').value);
        const jam = parseInt(document.getElementById('edit_jam_ke').value);

        const executeDeactivate = () => {
            const daySlots = slotsByDay[day] || [];
            slotsByDay[day] = daySlots.filter(s => parseInt(s.jam_ke) !== jam);

            setUnsaved(true);
            closeEditModal();
            renderMatrixGrid();
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Nonaktifkan Jam Ini?',
                html: `Nonaktifkan jam ke-<b>${jam}</b> pada hari <b>${DAYS_MAP[day]}</b>?<br><span class="text-xs text-slate-500 mt-1 block">Slot ini akan ditandai sebagai waktu pulang sekolah.</span>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#FF6B6B',
                cancelButtonColor: '#0F172A',
                confirmButtonText: 'Ya, Tandai Pulang',
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
                    executeDeactivate();
                }
            });
        } else {
            if (confirm(`Nonaktifkan jam ke-${jam} pada hari ${DAYS_MAP[day]}? Slot ini akan ditandai sebagai waktu pulang.`)) {
                executeDeactivate();
            }
        }
    }

    // Quick Duration Setter (35m, 40m, 45m)
    function applyQuickDuration(durationMinutes) {
        const executeQuickApply = () => {
            for (let d = 1; d <= 6; d++) {
                const daySlots = slotsByDay[d] || [];
                if (daySlots.length === 0) continue;

                daySlots.sort((a, b) => parseInt(a.jam_ke) - parseInt(b.jam_ke));

                let currentStart = daySlots[0].start_time || '07:00';

                for (let i = 0; i < daySlots.length; i++) {
                    const slot = daySlots[i];
                    slot.start_time = currentStart;

                    let dur = durationMinutes;
                    // If it is non-kbm (Upacara, Istirahat, etc), keep its designated duration
                    if (parseInt(slot.k_jadwal) !== 0) {
                        const originalDur = timeToMinutes(slot.end_time) - timeToMinutes(slot.start_time);
                        dur = originalDur > 0 ? originalDur : (slot.k_jadwal === 4 ? 30 : 45);
                    }

                    const endMins = timeToMinutes(currentStart) + dur;
                    slot.end_time = minutesToTime(endMins);
                    currentStart = slot.end_time;
                }
            }

            setUnsaved(true);
            renderMatrixGrid();
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: `Preset Durasi ${durationMinutes} Menit`,
                html: `Atur seluruh jam KBM menjadi <b>${durationMinutes} menit</b> per jam tatap muka?<br><span class="text-xs text-slate-500 mt-1 block">Waktu slot berikutnya akan otomatis menyesuaikan berantai.</span>`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#20C997',
                cancelButtonColor: '#0F172A',
                confirmButtonText: 'Terapkan Durasi',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: {
                    popup: 'neo-box-lg border-3 border-black',
                    confirmButton: 'neo-btn border-2 border-black font-black',
                    cancelButton: 'neo-btn border-2 border-black font-bold'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    executeQuickApply();
                }
            });
        } else {
            if (confirm(`Atur seluruh jam KBM menjadi ${durationMinutes} menit per jam tatap muka? Waktu slot berikutnya akan otomatis menyesuaikan berantai.`)) {
                executeQuickApply();
            }
        }
    }

    // Save Template to Server
    function saveSlots(forceSync = false) {
        const saveBtnTop = document.getElementById('btnSaveSlotsTop');
        const saveBtnBottom = document.getElementById('btnSaveSlotsBottom');
        
        const origTextTop = saveBtnTop ? saveBtnTop.innerHTML : '';
        const origTextBottom = saveBtnBottom ? saveBtnBottom.innerHTML : '';

        if (saveBtnTop) {
            saveBtnTop.disabled = true;
            saveBtnTop.innerHTML = '<span>⏳</span> Menyimpan...';
        }
        if (saveBtnBottom) {
            saveBtnBottom.disabled = true;
            saveBtnBottom.innerHTML = '<span>⏳</span> Menyimpan...';
        }

        // Flatten slots into a clean payload array
        const flatSlots = [];
        for (let d = 1; d <= 6; d++) {
            const daySlots = slotsByDay[d] || [];
            daySlots.forEach(s => {
                flatSlots.push({
                    day_of_week: parseInt(s.day_of_week || d),
                    jam_ke: parseInt(s.jam_ke),
                    k_jadwal: parseInt(s.k_jadwal),
                    start_time: (s.start_time || '07:00').substring(0, 5),
                    end_time: (s.end_time || '07:40').substring(0, 5),
                    name: s.name || ''
                });
            });
        }

        fetch("{{ route('admin.schedules.slots.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                slots: flatSlots,
                force_sync: forceSync
            })
        })
        .then(response => {
            if (response.status === 422) {
                return response.json().then(data => {
                    throw { isConflict: true, data: data };
                });
            }
            if (!response.ok) {
                throw new Error('Gagal menyimpan konfigurasi slot.');
            }
            return response.json();
        })
        .then(data => {
            setUnsaved(false);
            if (data.status === 'success') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Tersimpan!',
                        text: data.message || 'Template slot jam KBM berhasil disimpan.',
                        confirmButtonColor: '#20C997',
                        timer: 2000,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'neo-box-lg border-3 border-black',
                            confirmButton: 'neo-btn border-2 border-black font-black'
                        }
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    alert(data.message || 'Template slot jam KBM berhasil disimpan.');
                    window.location.reload();
                }
            }
        })
        .catch(err => {
            if (saveBtnTop) {
                saveBtnTop.disabled = false;
                saveBtnTop.innerHTML = origTextTop;
            }
            if (saveBtnBottom) {
                saveBtnBottom.disabled = false;
                saveBtnBottom.innerHTML = origTextBottom;
            }

            if (err.isConflict && err.data && err.data.conflicts) {
                showConflictDialog(err.data);
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan',
                        text: err.message || 'Terjadi kesalahan sistem saat menyimpan slot.',
                        confirmButtonColor: '#FF6B6B',
                        customClass: {
                            popup: 'neo-box-lg border-3 border-black',
                            confirmButton: 'neo-btn border-2 border-black font-black'
                        }
                    });
                } else {
                    alert(err.message || 'Terjadi kesalahan sistem saat menyimpan slot.');
                }
            }
        });
    }

    function showConflictDialog(conflictData) {
        const container = document.getElementById('conflictListContainer');
        if (!container) return;

        let html = '';
        conflictData.conflicts.forEach(c => {
            html += `
                <div class="border-2 border-black bg-rose-50 p-3 rounded text-xs space-y-1">
                    <div class="flex items-center justify-between font-black text-rose-950">
                        <span>${escapeHtml(c.class_name)} - ${escapeHtml(c.day)} (${escapeHtml(c.current_time)})</span>
                        <span class="bg-rose-200 border border-black px-1.5 py-0.2 rounded text-[10px]">Bentrok</span>
                    </div>
                    <div class="text-slate-800 font-bold">
                        Mata Pelajaran: <u>${escapeHtml(c.subject_name)}</u> (Guru: ${escapeHtml(c.teacher_name)})
                    </div>
                    <div class="text-[11px] text-rose-700">
                        ${escapeHtml(c.description)}
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        document.getElementById('conflictModal').classList.remove('hidden');
    }

    function closeConflictModal() {
        document.getElementById('conflictModal').classList.add('hidden');
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
</script>
@endsection
