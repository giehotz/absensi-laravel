<!-- Modal Detail Jadwal (Lihat, Edit, Hapus) -->
<div id="detailScheduleModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-md w-full p-6 space-y-4 relative" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <div class="flex items-center gap-2">
                <span class="text-2xl">📋</span>
                <div>
                    <h3 class="font-heading font-black text-base text-black uppercase" id="detailModalTitle">
                        Detail Jadwal Pelajaran
                    </h3>
                    <p class="text-xs font-semibold text-slate-600" id="detailModalSubtitle">
                        Informasi Sesi Mengajar
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeDetailModal()" class="text-black font-black text-lg hover:opacity-75 cursor-pointer">✕</button>
        </div>

        <!-- Detail Info Card -->
        <div class="bg-slate-50 border-2 border-black p-4 rounded space-y-2.5 text-xs">
            <div class="flex items-start justify-between border-b border-slate-200 pb-2">
                <span class="text-slate-600 font-bold">Mata Pelajaran:</span>
                <span class="font-heading font-black text-sm text-black text-right" id="detailSubjectName">-</span>
            </div>
            <div class="flex items-start justify-between border-b border-slate-200 pb-2">
                <span class="text-slate-600 font-bold">Guru Pengampu:</span>
                <div class="text-right">
                    <span class="font-bold text-black block" id="detailTeacherName">-</span>
                    <span class="font-mono text-[10px] text-slate-500 block" id="detailTeacherNip">NIP: -</span>
                </div>
            </div>
            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                <span class="text-slate-600 font-bold">Rombel / Kelas:</span>
                <span class="font-bold text-black" id="detailClassName">-</span>
            </div>
            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                <span class="text-slate-600 font-bold">Hari & Waktu:</span>
                <span class="font-mono font-black text-blue-900 bg-[#E7F5FF] border border-black px-2 py-0.5 rounded" id="detailTimeRange">-</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-600 font-bold">Beban JP:</span>
                <span class="neo-badge bg-[#FFD43B] text-black font-black text-xs" id="detailJpBadge">1 JP</span>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex items-center justify-between border-t-2 border-black pt-3">
            <form id="deleteScheduleForm" method="POST" action="" onsubmit="return false;">
                @csrf
                @method('DELETE')
                <button type="button" onclick="confirmDeleteSchedule()" class="neo-btn bg-[#FFE3E3] hover:bg-[#ffc9c9] text-rose-950 text-xs font-bold px-3 py-2 cursor-pointer border border-black flex items-center gap-1">
                    <span>🗑️</span> Hapus
                </button>
            </form>

            <div class="flex items-center gap-2">
                <button type="button" onclick="closeDetailModal()" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-xs font-bold px-3.5 py-2 cursor-pointer">
                    Tutup
                </button>
                <button type="button" onclick="triggerEditFromDetail()" class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black text-xs font-black px-4 py-2 cursor-pointer shadow-[2px_2px_0px_0px_#000] flex items-center gap-1">
                    <span>✏️</span> Edit Jadwal
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form Tambah / Edit Jadwal Pelajaran (Dengan Multi-Jam Support) -->
<div id="scheduleModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-lg w-full p-6 space-y-4 max-h-[92vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-base text-black flex items-center gap-1.5" id="modalTitle">
                <span>📅</span> Tambah Jadwal Pelajaran
            </h3>
            <button type="button" onclick="closeScheduleModal()" class="text-black font-black text-lg hover:opacity-75 cursor-pointer">✕</button>
        </div>

        <form id="scheduleForm" method="POST" action="{{ route('admin.schedules.store') }}" class="space-y-4">
            @csrf
            <div id="methodContainer"></div>
            <input type="hidden" name="school_class_id" value="{{ $selectedClassId }}">

            <!-- Pilih Hari -->
            <div>
                <label for="form_day_of_week" class="block text-xs font-black uppercase text-black mb-1">
                    Hari Tatap Muka: <span class="text-rose-600">*</span>
                </label>
                <select name="day_of_week" id="form_day_of_week" required onchange="onDayChange()"
                        class="w-full border-2 border-black rounded p-2 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-[#FFD43B]">
                    <option value="">-- Pilih Hari --</option>
                    @foreach($daysMap as $dNum => $dName)
                        <option value="{{ $dNum }}">{{ $dName }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Multi-Jam Selector (Pilih Rentang Jam ke-X s.d. Jam ke-Y) -->
            <div class="bg-[#FFF9DB] border-2 border-black p-3.5 rounded space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-black uppercase tracking-wider text-black flex items-center gap-1">
                        <span>⏱️</span> Pilih Rentang Jam Pelajaran (Multi-Jam):
                    </span>
                    <span id="multiJamBadge" class="text-[10px] font-mono font-black bg-[#FFD43B] text-black border border-black px-1.5 py-0.2 rounded">
                        1 JP
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase mb-0.5">Mulai Jam ke-:</label>
                        <select id="select_jam_start" onchange="onJamRangeChange()" class="w-full bg-white border-2 border-black rounded p-1.5 text-xs font-bold">
                            <option value="">-- Jam Awal --</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase mb-0.5">Sampai Jam ke-:</label>
                        <select id="select_jam_end" onchange="onJamRangeChange()" class="w-full bg-white border-2 border-black rounded p-1.5 text-xs font-bold">
                            <option value="">-- Jam Akhir --</option>
                        </select>
                    </div>
                </div>

                <div id="rangeTimeSummary" class="text-[11px] font-mono font-bold text-blue-900 bg-white border border-black p-1.5 rounded flex items-center justify-between">
                    <span>Rentang Waktu: <b id="rangeTimeDisplay">07:00 - 07:40</b></span>
                    <span class="text-[10px] text-slate-500">Auto-Sync</span>
                </div>
            </div>

            <!-- Input Waktu Eksplisit (Sinkron Otomatis dari Slot) -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="form_start_time" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Jam Mulai (HH:mm): <span class="text-rose-600">*</span>
                    </label>
                    <input type="time" name="start_time" id="form_start_time" required
                           class="w-full border-2 border-black rounded p-2 text-xs font-mono font-bold bg-white">
                </div>
                <div>
                    <label for="form_end_time" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Jam Selesai (HH:mm): <span class="text-rose-600">*</span>
                    </label>
                    <input type="time" name="end_time" id="form_end_time" required
                           class="w-full border-2 border-black rounded p-2 text-xs font-mono font-bold bg-white">
                </div>
            </div>

            <!-- Pilih Mata Pelajaran -->
            <div>
                <label for="form_subject_id" class="block text-xs font-black uppercase text-black mb-1">
                    Mata Pelajaran: <span class="text-rose-600">*</span>
                </label>
                <select name="subject_id" id="form_subject_id" required
                        class="w-full border-2 border-black rounded p-2 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-[#FFD43B]">
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    @foreach($subjects as $subj)
                        <option value="{{ $subj->id }}">{{ $subj->name }} ({{ $subj->code }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Pilih Guru Pengampu -->
            <div>
                <label for="form_teacher_id" class="block text-xs font-black uppercase text-black mb-1">
                    Guru Pengampu: <span class="text-rose-600">*</span>
                </label>
                <select name="teacher_id" id="form_teacher_id" required onchange="onTeacherChange()"
                        class="w-full border-2 border-black rounded p-2 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-[#FFD43B]">
                    <option value="">-- Pilih Guru --</option>
                    @foreach($teachers as $tch)
                        <option value="{{ $tch->id }}">
                            {{ $tch->user->name ?? 'Guru' }} (NIP: {{ $tch->nip ?? '-' }})
                        </option>
                    @endforeach
                </select>

                <div id="teacher_assignment_hint" class="mt-1 text-[11px] font-bold text-blue-900 bg-[#E7F5FF] border border-black p-2 rounded hidden">
                    <span>ℹ️ Guru ini memiliki penugasan mata pelajaran khusus.</span>
                </div>
            </div>

            <!-- Tombol Submit -->
            <div class="flex items-center justify-end gap-2 border-t-2 border-black pt-3">
                <button type="button" onclick="closeScheduleModal()" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black text-xs font-bold px-4 py-2 cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black text-xs font-black px-5 py-2 cursor-pointer shadow-[2px_2px_0px_0px_#000]">
                    <span>💾</span> Simpan Jadwal
                </button>
            </div>
        </form>
    </div>
</div>
