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
            ⚙️ Template Slot Jam KBM (EMIS GTK)
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
                Atur waktu jam tatap muka dan kegiatan terjadwal (Upacara, Istirahat, Pembiasaan, Religi) per hari secara seragam untuk seluruh rombel.
            </p>
        </div>

        <div class="z-10 flex flex-wrap items-center gap-2.5 shrink-0">
            <form action="{{ route('admin.schedules.slots.reset-default') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memuat ulang template standar Madrasah/Kemenag? Konfigurasi slot saat ini akan diganti.')">
                @csrf
                <button type="submit" class="neo-btn bg-white hover:bg-slate-100 text-black text-xs font-bold px-3.5 py-2.5 flex items-center gap-1.5 cursor-pointer shadow-[2px_2px_0px_0px_#000]">
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

    <!-- Legend Kategori Kegiatan -->
    <div class="bg-white neo-box p-4">
        <div class="text-[11px] font-black uppercase tracking-wider text-slate-600 mb-2.5 flex items-center gap-1.5">
            <span>🏷️</span> Petunjuk Warna Kategori Kegiatan:
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2">
            @foreach($kegiatanColors as $kode => $color)
            <div class="flex items-center gap-2 p-2 border-2 border-black rounded {{ $color['bg'] }} text-xs font-bold shadow-[1.5px_1.5px_0px_0px_#000]">
                <span class="w-3 h-3 rounded-full border border-black {{ $color['badge_bg'] }} shrink-0"></span>
                <span class="truncate {{ $color['text'] }}">{{ $color['name'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Matriks Grid Slot Hari x Jam Pelajaran -->
    <div class="bg-white neo-box overflow-hidden">
        <div class="p-4 border-b-2 border-black bg-[#FFF9DB] flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="font-heading font-black text-sm uppercase text-black flex items-center gap-1.5">
                    <span>📋</span> Matriks Jam Mingguan (Senin s.d Sabtu)
                </h3>
                <p class="text-[11px] font-semibold text-slate-600">
                    Klik sel slot jam untuk mengubah waktu atau jenis kegiatan. Gunakan fitur Cascade Update untuk menggeser jam berikutnya secara otomatis.
                </p>
            </div>
            <div class="text-xs font-mono font-bold bg-white px-2.5 py-1 border border-black">
                Waktu Sinkron: Auto-Cascade
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[850px]">
                <thead>
                    <tr class="bg-slate-100 border-b-2 border-black text-xs font-black uppercase text-black">
                        <th class="p-3 border-r-2 border-black text-center w-20">Hari</th>
                        <th class="p-3 border-r-2 border-black text-center w-24">Jumlah</th>
                        <th class="p-3 border-r-2 border-black">Daftar Slot Jam KBM & Non-KBM</th>
                        <th class="p-3 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black font-medium text-xs">
                    @foreach([1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'] as $dayNum => $dayName)
                    <tr class="hover:bg-slate-50/75 transition-colors">
                        <!-- Kolom Nama Hari -->
                        <td class="p-3 border-r-2 border-black text-center font-heading font-black text-sm bg-slate-50">
                            {{ $dayName }}
                        </td>

                        <!-- Total Slot Counter -->
                        <td class="p-3 border-r-2 border-black text-center font-mono font-bold">
                            <span id="slot_count_{{ $dayNum }}">{{ count($slotsByDay[$dayNum] ?? []) }}</span> Jam
                        </td>

                        <!-- Grid Sel Slot per Hari (Dirender oleh JS) -->
                        <td class="p-3 border-r-2 border-black">
                            <div id="day_slots_{{ $dayNum }}" class="flex flex-wrap gap-1.5 items-center">
                                <!-- Sel slot akan dirender oleh renderDaySlots({{ $dayNum }}) -->
                            </div>
                        </td>

                        <!-- Aksi Cepat per Hari -->
                        <td class="p-3 text-center">
                            <button type="button" onclick="addNewSlot({{ $dayNum }})" 
                                    class="w-full neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black text-[10px] font-bold px-2 py-1 cursor-pointer">
                                + Slot Jam
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t-2 border-black bg-slate-50 flex items-center justify-between">
            <span class="text-xs font-bold text-slate-600">Pastikan untuk menekan tombol "Simpan Template Slot" setelah selesai melakukan penyesuaian.</span>
            <button type="button" onclick="saveSlots(false)" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black text-xs font-black px-4 py-2 flex items-center gap-1.5 cursor-pointer shadow-[2px_2px_0px_0px_#000]">
                <span>💾</span> Simpan Template Slot
            </button>
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
                <input type="hidden" id="edit_slot_idx" value="0">
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
                    <input type="text" id="edit_name" placeholder="Contoh: Jam ke-1, Istirahat 1, Upacara" 
                           class="w-full border-2 border-black rounded p-2 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#FFD43B]">
                </div>

                <!-- Waktu Mulai & Selesai -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-black uppercase text-black mb-1">Jam Mulai:</label>
                        <input type="time" id="edit_start_time" 
                               class="w-full border-2 border-black rounded p-2 text-xs font-mono font-bold focus:outline-none focus:ring-2 focus:ring-[#FFD43B]">
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase text-black mb-1">Jam Selesai:</label>
                        <input type="time" id="edit_end_time" 
                               class="w-full border-2 border-black rounded p-2 text-xs font-mono font-bold focus:outline-none focus:ring-2 focus:ring-[#FFD43B]">
                    </div>
                </div>

                <!-- Opsi Cascade Update Checkbox -->
                <div class="p-3 border-2 border-black rounded bg-[#E7F5FF] text-blue-950 text-xs">
                    <label class="flex items-start gap-2 cursor-pointer font-bold">
                        <input type="checkbox" id="edit_enable_cascade" checked class="mt-0.5 w-4 h-4 rounded border-2 border-black text-blue-600 focus:ring-0">
                        <span>Aktifkan Cascade Time Update (Geser jam mulai dan selesai slot berikutnya di hari yang sama secara otomatis).</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-between border-t-2 border-black pt-4">
                <button type="button" onclick="deleteActiveSlot()" class="neo-btn bg-[#FF6B6B] hover:bg-[#fa5252] text-white text-xs font-bold px-3 py-2 cursor-pointer">
                    Hapus Slot
                </button>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="closeEditModal()" class="neo-btn bg-slate-200 hover:bg-slate-300 text-black text-xs font-bold px-3 py-2 cursor-pointer">
                        Batal
                    </button>
                    <button type="button" onclick="applySlotChanges()" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black text-xs font-black px-4 py-2 cursor-pointer">
                        Terapkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Resolusi Konflik Jadwal Interaktif -->
    <div id="conflictModal" class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white neo-box-lg max-w-2xl w-full p-6 space-y-4 relative" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between border-b-2 border-black pb-3">
                <h3 class="font-heading font-black text-lg text-rose-700 flex items-center gap-2">
                    <span>⚠️</span> Konflik Jadwal Terdeteksi (<span id="conflictCountBadge">0</span> Jadwal Terdampak)
                </h3>
                <button type="button" onclick="closeConflictModal()" class="text-black font-black text-lg hover:opacity-75 cursor-pointer">✕</button>
            </div>

            <div class="bg-[#FFE3E3] border-2 border-black p-3.5 rounded text-xs text-rose-950 font-medium leading-relaxed">
                Template slot baru yang Anda ubah bertabrakan dengan jadwal pelajaran aktif yang sudah pernah diplot pada rombel kelas.
                Pilih opsi di bawah untuk menyelesaikan konflik ini:
            </div>

            <!-- Daftar Konflik Detail -->
            <div class="max-h-60 overflow-y-auto border-2 border-black rounded">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#FFF9DB] border-b border-black font-black uppercase text-[11px]">
                        <tr>
                            <th class="p-2.5 border-r border-black">Kelas</th>
                            <th class="p-2.5 border-r border-black">Hari & Jam Aktif</th>
                            <th class="p-2.5 border-r border-black">Mata Pelajaran & Guru</th>
                            <th class="p-2.5">Keterangan Konflik</th>
                        </tr>
                    </thead>
                    <tbody id="conflictTableBody" class="divide-y divide-black/20 font-medium">
                        <!-- Baris tabel konflik akan diisi secara dinamis oleh JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Tombol Aksi Resolusi -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-t-2 border-black pt-4">
                <button type="button" onclick="closeConflictModal()" class="neo-btn bg-slate-200 hover:bg-slate-300 text-black text-xs font-bold px-4 py-2.5 cursor-pointer">
                    ✕ Batalkan & Periksa Kembali Template
                </button>
                <button type="button" id="btnForceSync" onclick="saveSlots(true)" class="neo-btn bg-[#FF6B6B] hover:bg-[#fa5252] text-white text-xs font-black px-4 py-2.5 cursor-pointer shadow-[2px_2px_0px_0px_#000]">
                    ⚡ Sesuaikan Jadwal & Simpan (Auto-Sync)
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Master Data Slot per Hari (Senin s.d Sabtu)
let slotsByDay = @json($slotsByDay);

const dayNames = {
    1: 'Senin',
    2: 'Selasa',
    3: 'Rabu',
    4: 'Kamis',
    5: 'Jumat',
    6: 'Sabtu'
};

const kegiatanMeta = {
    0: { name: 'KBM', card: 'bg-white text-black', badge: 'bg-[#FFF9DB] text-amber-950' },
    3: { name: 'Upacara', card: 'bg-[#FFE3E3] text-rose-950', badge: 'bg-[#FF8787] text-white' },
    4: { name: 'Istirahat', card: 'bg-[#D0EBFF] text-blue-950', badge: 'bg-[#74C0FC] text-blue-950' },
    5: { name: 'Senam', card: 'bg-[#FFE8CC] text-amber-950', badge: 'bg-[#FFA94D] text-black' },
    6: { name: 'Pembiasaan', card: 'bg-[#C3FAE8] text-teal-950', badge: 'bg-[#63E6BE] text-teal-950' },
    7: { name: 'Religi', card: 'bg-[#EEBEFA] text-purple-950', badge: 'bg-[#DA77F2] text-white' }
};

function renderDaySlots(day) {
    const container = document.getElementById(`day_slots_${day}`);
    const counter = document.getElementById(`slot_count_${day}`);
    if (!container) return;

    const slots = slotsByDay[day] || [];
    if (counter) {
        counter.textContent = slots.length;
    }

    let html = '';

    slots.forEach((slot, sIndex) => {
        const k = parseInt(slot.k_jadwal);
        const meta = kegiatanMeta[k] || kegiatanMeta[0];
        const slotName = slot.name || (k === 0 ? `Jam ke-${slot.jam_ke}` : meta.name);
        const timeRange = `${slot.start_time} - ${slot.end_time}`;

        html += `
            <div onclick="openEditModal(${day}, ${sIndex})"
                 class="p-2 border-2 border-black rounded shadow-[2px_2px_0px_0px_#000] cursor-pointer hover:-translate-y-0.5 transition-all text-center min-w-[105px] max-w-[130px] flex-1 ${meta.card}">
                <div class="flex items-center justify-between gap-1 text-[10px] font-bold border-b border-black/20 pb-0.5 mb-1">
                    <span class="font-mono">J-${slot.jam_ke}</span>
                    <span class="px-1 py-0.2 rounded text-[9px] font-black uppercase truncate max-w-[65px] ${meta.badge}">
                        ${meta.name}
                    </span>
                </div>
                <div class="font-bold text-[11px] truncate" title="${slotName}">${slotName}</div>
                <div class="font-mono text-[10px] font-semibold text-slate-700 mt-0.5">${timeRange}</div>
            </div>
        `;
    });

    html += `
        <button type="button" onclick="addNewSlot(${day})" 
                class="p-2 border-2 border-dashed border-slate-400 hover:border-black rounded text-slate-500 hover:text-black font-bold text-[11px] flex flex-col items-center justify-center min-w-[70px] min-h-[60px] bg-slate-50 hover:bg-white cursor-pointer transition-all">
            <span>➕</span>
            <span>Tambah</span>
        </button>
    `;

    container.innerHTML = html;
}

function renderAllDays() {
    for (let day = 1; day <= 6; day++) {
        renderDaySlots(day);
    }
}

function openEditModal(day, sIndex) {
    const slot = slotsByDay[day][sIndex];
    if (!slot) return;

    document.getElementById('edit_day_num').value = day;
    document.getElementById('edit_slot_idx').value = sIndex;
    document.getElementById('edit_jam_ke').value = slot.jam_ke;
    document.getElementById('modalHeaderTitle').textContent = `${dayNames[day]} (Jam ke-${slot.jam_ke})`;

    document.getElementById('edit_k_jadwal').value = slot.k_jadwal;
    document.getElementById('edit_name').value = slot.name || '';
    document.getElementById('edit_start_time').value = slot.start_time;
    document.getElementById('edit_end_time').value = slot.end_time;
    document.getElementById('edit_enable_cascade').checked = true;

    document.getElementById('editSlotModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeEditModal() {
    document.getElementById('editSlotModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function onKategoriSelectChange() {
    const k = parseInt(document.getElementById('edit_k_jadwal').value);
    const nameInput = document.getElementById('edit_name');
    const jamKe = document.getElementById('edit_jam_ke').value;

    if (k === 3) nameInput.value = 'Upacara Bendera';
    else if (k === 4) nameInput.value = 'Istirahat';
    else if (k === 5) nameInput.value = 'Senam Pagi';
    else if (k === 6) nameInput.value = 'Pembiasaan Pagi';
    else if (k === 7) nameInput.value = 'Shalat / Religi';
    else if (k === 0 && (!nameInput.value || nameInput.value.includes('Upacara') || nameInput.value.includes('Istirahat'))) {
        nameInput.value = `Jam ke-${jamKe}`;
    }
}

function timeToMinutes(timeStr) {
    const parts = timeStr.split(':').map(Number);
    return parts[0] * 60 + parts[1];
}

function minutesToTime(totalMinutes) {
    const h = Math.floor(totalMinutes / 60) % 24;
    const m = totalMinutes % 60;
    return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
}

function applySlotChanges() {
    const day = parseInt(document.getElementById('edit_day_num').value);
    const index = parseInt(document.getElementById('edit_slot_idx').value);
    const slots = slotsByDay[day];

    const newStart = document.getElementById('edit_start_time').value;
    const newEnd = document.getElementById('edit_end_time').value;

    if (!newStart || !newEnd) {
        alert('Jam mulai dan jam selesai wajib diisi!');
        return;
    }

    if (newStart >= newEnd) {
        alert('Jam selesai harus lebih besar dari jam mulai!');
        return;
    }

    const enableCascade = document.getElementById('edit_enable_cascade').checked;

    // Perbarui slot saat ini
    slots[index].k_jadwal = parseInt(document.getElementById('edit_k_jadwal').value);
    slots[index].name = document.getElementById('edit_name').value.trim();
    slots[index].start_time = newStart;
    slots[index].end_time = newEnd;

    // CASCADE TIME UPDATE: Geser slot-slot berikutnya secara berantai jika dicentang
    if (enableCascade && index < slots.length - 1) {
        let currentEndMinutes = timeToMinutes(newEnd);

        for (let i = index + 1; i < slots.length; i++) {
            const nextDur = timeToMinutes(slots[i].end_time) - timeToMinutes(slots[i].start_time);
            const safeDuration = nextDur > 0 ? nextDur : 40;

            slots[i].start_time = minutesToTime(currentEndMinutes);
            currentEndMinutes += safeDuration;
            slots[i].end_time = minutesToTime(currentEndMinutes);
        }
    }

    closeEditModal();
    renderDaySlots(day);
}

function addNewSlot(day) {
    if (!slotsByDay[day]) {
        slotsByDay[day] = [];
    }
    const slots = slotsByDay[day];
    const nextJamKe = slots.length + 1;

    let startTime = '07:00';
    let endTime = '07:40';

    if (slots.length > 0) {
        const lastSlot = slots[slots.length - 1];
        startTime = lastSlot.end_time;
        const startMins = timeToMinutes(startTime);
        endTime = minutesToTime(startMins + 40);
    }

    slots.push({
        id: null,
        day_of_week: day,
        jam_ke: nextJamKe,
        k_jadwal: 0,
        name: `Jam ke-${nextJamKe}`,
        start_time: startTime,
        end_time: endTime
    });

    renderDaySlots(day);
    openEditModal(day, slots.length - 1);
}

function deleteActiveSlot() {
    const day = parseInt(document.getElementById('edit_day_num').value);
    const index = parseInt(document.getElementById('edit_slot_idx').value);
    const jamKe = document.getElementById('edit_jam_ke').value;

    if (!confirm(`Hapus slot jam ke-${jamKe} pada hari ${dayNames[day]}?`)) {
        return;
    }

    slotsByDay[day].splice(index, 1);

    // Re-index jam_ke
    slotsByDay[day].forEach((s, idx) => {
        s.jam_ke = idx + 1;
        if (s.k_jadwal === 0 && s.name.startsWith('Jam ke-')) {
            s.name = `Jam ke-${idx + 1}`;
        }
    });

    closeEditModal();
    renderDaySlots(day);
}

function closeConflictModal() {
    document.getElementById('conflictModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

async function saveSlots(forceSync = false) {
    const btnSave = document.getElementById('btnSaveSlotsTop');
    const btnForce = document.getElementById('btnForceSync');

    // Flatten all slots from all days
    const allSlots = [];
    for (let day = 1; day <= 6; day++) {
        const daySlots = slotsByDay[day] || [];
        daySlots.forEach(s => {
            allSlots.push({
                day_of_week: day,
                jam_ke: s.jam_ke,
                k_jadwal: parseInt(s.k_jadwal),
                name: s.name,
                start_time: s.start_time,
                end_time: s.end_time
            });
        });
    }

    if (allSlots.length === 0) {
        alert('Tidak ada slot jam untuk disimpan.');
        return;
    }

    if (btnSave) btnSave.disabled = true;
    if (btnForce) btnForce.disabled = true;

    try {
        const res = await fetch("{{ route('admin.schedules.slots.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                slots: allSlots,
                force_sync: forceSync
            })
        });

        const data = await res.json();

        if (res.status === 422 && data.status === 'conflict') {
            // Render tabel konflik
            document.getElementById('conflictCountBadge').textContent = data.conflict_count || 0;
            const tbody = document.getElementById('conflictTableBody');
            tbody.innerHTML = '';

            (data.conflicts || []).forEach(c => {
                tbody.innerHTML += `
                    <tr class="hover:bg-slate-50">
                        <td class="p-2.5 border-r border-black font-bold">${c.class_name}</td>
                        <td class="p-2.5 border-r border-black font-mono">${c.day} (${c.current_time})</td>
                        <td class="p-2.5 border-r border-black">
                            <div class="font-bold text-black">${c.subject_name}</div>
                            <div class="text-[10px] text-slate-600">${c.teacher_name}</div>
                        </td>
                        <td class="p-2.5 text-rose-800 font-bold">${c.description}</td>
                    </tr>
                `;
            });

            document.getElementById('conflictModal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            return;
        }

        if (!res.ok) {
            alert(data.message || 'Gagal menyimpan template slot.');
            return;
        }

        closeConflictModal();
        alert('✅ Template slot jam KBM berhasil disimpan!');
        window.location.reload();
    } catch (err) {
        console.error(err);
        alert('Terjadi kesalahan jaringan saat menyimpan slot template.');
    } finally {
        if (btnSave) btnSave.disabled = false;
        if (btnForce) btnForce.disabled = false;
    }
}

// Inisialisasi tampilan matriks awal saat DOM siap
document.addEventListener('DOMContentLoaded', () => {
    renderAllDays();
});
</script>
@endsection
