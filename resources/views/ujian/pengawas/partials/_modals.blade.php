{{-- ============================================================== --}}
{{-- MODALS MANAJEMEN PENGAWAS UJIAN (NEO-BRUTALISM)                 --}}
{{-- ============================================================== --}}
@if($activePeriode)
{{-- MODAL 1: TUGASKAN MANUAL / UBAH PENGAWAS --}}
<div id="modalTambahPengawas" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-slate-900 rounded-2xl max-w-lg w-full p-5 shadow-[8px_8px_0px_0px_#0f172a] relative animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b-2 border-slate-900 mb-4">
            <h3 class="text-sm font-black font-heading text-slate-900 flex items-center gap-2 uppercase tracking-wide">
                <span>👨‍🏫</span>
                <span>Penugasan Pengawas Ruang</span>
            </h3>
            <button type="button" onclick="document.getElementById('modalTambahPengawas').classList.add('hidden')" 
                    class="h-7 w-7 grid place-items-center rounded border-2 border-slate-900 bg-white font-black text-xs text-slate-900 shadow-[1px_1px_0px_0px_#0f172a] hover:bg-slate-900 hover:text-white transition-colors">
                ✕
            </button>
        </div>

        <form action="{{ route('ujian.pengawas.store') }}" method="POST" class="space-y-3.5 text-xs font-semibold">
            @csrf
            <div>
                <label class="block text-slate-900 font-black mb-1">Pilih Slot Jadwal Ujian <span class="text-red-500">*</span></label>
                <select id="modal_jadwal_id" name="ujian_jadwal_id" required class="w-full px-3 py-2 border-2 border-slate-900 rounded-lg bg-slate-50 text-xs font-bold shadow-[2px_2px_0px_0px_#0f172a] focus:outline-none">
                    <option value="">-- Pilih Jadwal --</option>
                    @foreach($jadwals as $j)
                        <option value="{{ $j->id }}">
                            {{ $j->tanggal_ujian->format('d/m/Y') }} ({{ substr($j->jam_mulai, 0, 5) }}-{{ substr($j->jam_selesai, 0, 5) }}) - {{ $j->nama_mapel }} [{{ $j->target_kelas }}]
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-slate-900 font-black mb-1">Pilih Ruang Ujian <span class="text-red-500">*</span></label>
                <select id="modal_ruang_id" name="ujian_ruang_id" required class="w-full px-3 py-2 border-2 border-slate-900 rounded-lg bg-slate-50 text-xs font-bold shadow-[2px_2px_0px_0px_#0f172a] focus:outline-none">
                    <option value="">-- Pilih Ruang --</option>
                    @foreach($ruangs as $r)
                        <option value="{{ $r->id }}">{{ $r->nama_ruang }} ({{ $r->kode_ruang }}) - Kapasitas: {{ $r->kapasitas_maksimal }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-900 font-black mb-1">Pengawas 1 (Utama)</label>
                    <select id="modal_pengawas_1" name="pengawas_1_id" class="w-full px-3 py-2 border-2 border-slate-900 rounded-lg bg-slate-50 text-xs font-bold shadow-[2px_2px_0px_0px_#0f172a] focus:outline-none">
                        <option value="">-- Pilih Guru --</option>
                        @foreach($teachers as $t)
                            @php
                                $isPanitia = in_array($t->id, $panitiaTeacherIds, true);
                                $allowSupervise = in_array($t->id, $panitiaAllowSuperviseTeacherIds ?? [], true);
                                $canAssign = ! $isPanitia || $allowSupervise;
                                $mapping = $mappings[$t->id] ?? null;
                            @endphp
                            <option value="{{ $t->id }}" {{ ! $canAssign ? 'disabled class=text-slate-400' : '' }}>
                                {{ $t->user?->name }} {{ $isPanitia ? ($allowSupervise ? '⚡ (Panitia - Boleh Mengawas)' : '⛔ (Panitia Ujian)') : ($mapping ? '['.$mapping->formatted_levels.']' : '[Belum Dipetakan]') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-slate-900 font-black mb-1">Pengawas 2 (Pendamping)</label>
                    <select id="modal_pengawas_2" name="pengawas_2_id" class="w-full px-3 py-2 border-2 border-slate-900 rounded-lg bg-slate-50 text-xs font-bold shadow-[2px_2px_0px_0px_#0f172a] focus:outline-none">
                        <option value="">-- Pilih Guru --</option>
                        @foreach($teachers as $t)
                            @php
                                $isPanitia = in_array($t->id, $panitiaTeacherIds, true);
                                $allowSupervise = in_array($t->id, $panitiaAllowSuperviseTeacherIds ?? [], true);
                                $canAssign = ! $isPanitia || $allowSupervise;
                                $mapping = $mappings[$t->id] ?? null;
                            @endphp
                            <option value="{{ $t->id }}" {{ ! $canAssign ? 'disabled class=text-slate-400' : '' }}>
                                {{ $t->user?->name }} {{ $isPanitia ? ($allowSupervise ? '⚡ (Panitia - Boleh Mengawas)' : '⛔ (Panitia Ujian)') : ($mapping ? '['.$mapping->formatted_levels.']' : '[Belum Dipetakan]') }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-slate-900 font-black mb-1">Status Kehadiran <span class="text-red-500">*</span></label>
                <select id="modal_kehadiran" name="status_kehadiran" required class="w-full px-3 py-2 border-2 border-slate-900 rounded-lg bg-slate-50 text-xs font-bold shadow-[2px_2px_0px_0px_#0f172a] focus:outline-none">
                    <option value="Terjadwal">Terjadwal</option>
                    <option value="Hadir">Hadir</option>
                    <option value="Digantikan">Digantikan</option>
                    <option value="Izin">Izin</option>
                </select>
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t-2 border-slate-900">
                <button type="button" onclick="document.getElementById('modalTambahPengawas').classList.add('hidden')" class="px-3.5 py-2 border-2 border-slate-900 rounded-lg text-slate-900 bg-slate-100 hover:bg-slate-200 font-bold text-xs shadow-[2px_2px_0px_0px_#0f172a]">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 border-2 border-slate-900 rounded-lg text-slate-900 bg-[#FFD43B] hover:bg-yellow-400 font-black text-xs shadow-[2px_2px_0px_0px_#0f172a] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all">
                    Simpan Penugasan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: EDIT PEMETAAN KELAS INDIVIDUAL PER GURU --}}
<div id="modalEditMapping" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-slate-900 rounded-2xl max-w-md w-full p-5 shadow-[8px_8px_0px_0px_#0f172a] relative animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b-2 border-slate-900 mb-4">
            <h3 class="text-sm font-black font-heading text-slate-900 flex items-center gap-2 uppercase tracking-wide">
                <svg class="w-4 h-4 text-slate-900" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0M3.75 18H7.5m6-6h6m-6 0a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 12h6"/>
                </svg>
                <span>Atur Pemetaan Kelas Guru</span>
            </h3>
            <button type="button" onclick="document.getElementById('modalEditMapping').classList.add('hidden')" 
                    class="h-7 w-7 grid place-items-center rounded border-2 border-slate-900 bg-white font-black text-xs text-slate-900 shadow-[1px_1px_0px_0px_#0f172a] hover:bg-slate-900 hover:text-white transition-colors">
                ✕
            </button>
        </div>

        <form action="{{ route('ujian.pengawas.mapping.store') }}" method="POST" class="space-y-4 text-xs font-semibold">
            @csrf
            <input type="hidden" name="ujian_periode_id" value="{{ $activePeriode->id }}">
            <input type="hidden" id="edit_map_teacher_id" name="teacher_id" value="">

            <div class="p-3 bg-slate-50 border-2 border-slate-900 rounded-xl shadow-[2px_2px_0px_0px_#0f172a]">
                <div class="text-slate-500 text-[10px] uppercase font-black">Guru Yang Diatur:</div>
                <div id="edit_map_teacher_name" class="text-sm font-black text-slate-900"></div>
                <div id="edit_map_teacher_nip" class="text-[10px] font-mono text-slate-600 font-bold"></div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-slate-900 font-black">Pilih Tingkat Kelas yang Boleh Diawasi <span class="text-red-500">*</span></label>
                    <div class="flex gap-1 text-[10px]">
                        <button type="button" onclick="setEditLevels(['1', '2', '3'])" class="px-2 py-0.5 bg-blue-100 hover:bg-blue-200 text-blue-950 border-2 border-slate-900 rounded font-black shadow-[1px_1px_0px_0px_#0f172a]">Rendah</button>
                        <button type="button" onclick="setEditLevels(['4', '5', '6'])" class="px-2 py-0.5 bg-orange-100 hover:bg-orange-200 text-orange-950 border-2 border-slate-900 rounded font-black shadow-[1px_1px_0px_0px_#0f172a]">Atas</button>
                        <button type="button" onclick="setEditLevels(['1', '2', '3', '4', '5', '6'])" class="px-2 py-0.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-950 border-2 border-slate-900 rounded font-black shadow-[1px_1px_0px_0px_#0f172a]">Semua</button>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    @foreach(['1' => 'Kelas 1', '2' => 'Kelas 2', '3' => 'Kelas 3', '4' => 'Kelas 4', '5' => 'Kelas 5', '6' => 'Kelas 6'] as $num => $lbl)
                        <label class="flex items-center gap-2 p-2 border-2 border-slate-900 rounded-lg cursor-pointer hover:bg-yellow-50 font-black bg-white shadow-[1.5px_1.5px_0px_0px_#0f172a]">
                            <input type="checkbox" name="assigned_levels[]" value="{{ $num }}" class="edit-level-check w-4 h-4 rounded border-2 border-slate-900 accent-slate-900">
                            <span>{{ $lbl }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="block text-slate-900 font-black mb-1">Mode Penugasan Pengawas</label>
                <select id="edit_map_mode_penugasan" name="mode_penugasan" onchange="toggleEditSubjectScope()"
                        class="w-full px-3 py-2 border-2 border-slate-900 rounded-lg bg-slate-50 text-xs font-bold shadow-[2px_2px_0px_0px_#0f172a] focus:outline-none">
                    <option value="rotasi">Rotasi Umum (Pemerataan Beban)</option>
                    <option value="wali_kelas">Wali Kelas (Fokus Kelas Perwalian)</option>
                    <option value="pengampu_mapel">Pengampu Mapel (Prioritas Kelas Diajar)</option>
                </select>
            </div>

            <div id="edit_map_homeroom_lock_wrapper" class="hidden rounded-lg border-2 border-slate-900 bg-sky-50 p-2.5">
                <label class="flex items-start gap-2 cursor-pointer text-[11px] font-bold text-slate-900">
                    <input type="hidden" name="lock_to_homeroom" value="0">
                    <input type="checkbox" id="edit_map_lock_to_homeroom" name="lock_to_homeroom" value="1" class="mt-0.5 w-4 h-4 rounded border-2 border-slate-900 accent-slate-900">
                    <span>
                        <span class="block">Kunci hanya mengawas di kelas perwalian (<span id="edit_map_homeroom_label" class="font-black text-sky-900">-</span>)</span>
                        <span class="block text-[10px] font-normal text-slate-600">Jika dicentang, guru ini tidak akan dijadikan cadangan di ruang kelas lain.</span>
                    </span>
                </label>
            </div>

            <div id="edit_map_subjects_wrapper" class="hidden space-y-1.5">
                <label class="block text-slate-900 font-black">Batasi Mapel Tertentu (Opsional)</label>
                <div class="max-h-32 overflow-y-auto grid grid-cols-1 sm:grid-cols-2 gap-1.5 p-2 border-2 border-slate-900 rounded-lg bg-slate-50">
                    @foreach($subjects ?? [] as $subj)
                        <label class="flex items-center gap-1.5 text-[11px] font-bold text-slate-800 cursor-pointer">
                            <input type="checkbox" name="assigned_subject_ids[]" value="{{ $subj->id }}" class="edit-subject-check w-3.5 h-3.5 rounded border border-slate-900 accent-slate-900">
                            <span class="truncate">{{ $subj->name }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="text-[10px] text-slate-500">Berlaku untuk mode Pengampu Mapel maupun Wali Kelas. Biarkan tidak dicentang untuk otomatis mengikuti jadwal mengajar guru (atau seluruh mapel perwalian bila belum ada data mengajar).</p>
            </div>

            <div>
                <label class="block text-slate-900 font-black mb-1">Catatan Pemetaan (Opsional)</label>
                <input type="text" id="edit_map_keterangan" name="keterangan" placeholder="Contoh: Fokus kelas bawah" class="w-full px-3 py-2 border-2 border-slate-900 rounded-lg bg-slate-50 text-xs font-bold shadow-[2px_2px_0px_0px_#0f172a] focus:outline-none">
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t-2 border-slate-900">
                <button type="button" onclick="document.getElementById('modalEditMapping').classList.add('hidden')" class="px-3.5 py-2 border-2 border-slate-900 rounded-lg text-slate-900 bg-slate-100 hover:bg-slate-200 font-bold text-xs shadow-[2px_2px_0px_0px_#0f172a]">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 border-2 border-slate-900 rounded-lg text-slate-900 bg-[#FFD43B] hover:bg-yellow-400 font-black text-xs shadow-[2px_2px_0px_0px_#0f172a] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all">
                    Simpan Pemetaan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 3: PERTUKARAN JADWAL PENGAWAS (SWAP) --}}
<div id="modalTukarPengawas" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-slate-900 rounded-2xl max-w-lg w-full p-5 shadow-[8px_8px_0px_0px_#0f172a] relative animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b-2 border-slate-900 mb-4">
            <h3 class="text-sm font-black font-heading text-slate-900 flex items-center gap-2 uppercase tracking-wide">
                <span>🔄</span>
                <span>Pertukaran Jadwal Pengawas (Swap)</span>
            </h3>
            <button type="button" onclick="document.getElementById('modalTukarPengawas').classList.add('hidden')" 
                    class="h-7 w-7 grid place-items-center rounded border-2 border-slate-900 bg-white font-black text-xs text-slate-900 shadow-[1px_1px_0px_0px_#0f172a] hover:bg-slate-900 hover:text-white transition-colors">
                ✕
            </button>
        </div>

        <form action="{{ route('ujian.pengawas.swap.execute') }}" method="POST" class="space-y-4 text-xs font-semibold">
            @csrf
            <input type="hidden" id="swap_source_assignment_id" name="source_assignment_id" value="">
            <input type="hidden" id="swap_source_slot" name="source_slot" value="pengawas_1">

            {{-- Kartu Data Tugas Asal --}}
            <div class="p-3 bg-purple-50 border-2 border-slate-900 rounded-xl shadow-[3px_3px_0px_0px_#0f172a]">
                <div class="text-purple-950 text-[10px] uppercase font-black mb-1">Tugas Pengawasan Yang Hendak Ditukar:</div>
                <div id="swap_info_teacher" class="text-sm font-black text-slate-900"></div>
                <div id="swap_info_mapel" class="text-xs font-bold text-blue-900 mt-0.5"></div>
                <div id="swap_info_datetime" class="text-[11px] font-mono text-slate-700 mt-0.5"></div>
            </div>

            {{-- Pilihan Lawan Tukar --}}
            <div>
                <label class="block text-slate-900 font-black mb-1">
                    Pilih Penugasan Lawan Tukar <span class="text-red-500">*</span>
                </label>
                <div id="swap_loading_box" class="p-4 text-center text-slate-700 italic bg-slate-50 border-2 border-dashed border-slate-900 rounded-xl hidden font-bold">
                    Sedang memverifikasi kelayakan dua arah dan bentrok jadwal...
                </div>
                <select id="swap_target_select" onchange="onSelectSwapTarget(this)" required class="w-full px-3 py-2 border-2 border-slate-900 rounded-lg bg-slate-50 font-bold text-xs shadow-[2px_2px_0px_0px_#0f172a] focus:outline-none">
                    <option value="">-- Pilih Penugasan Lawan Tukar --</option>
                </select>
                <input type="hidden" id="swap_target_assignment_id" name="target_assignment_id" value="">
                <input type="hidden" id="swap_target_slot" name="target_slot" value="pengawas_1">
                <p class="text-[10px] text-slate-600 mt-1 font-medium">
                    Hanya menampilkan jadwal yang memenuhi syarat pemetaan kelas kedua belah pihak dan bebas dari bentrok waktu.
                </p>
            </div>

            {{-- Alasan Pertukaran --}}
            <div>
                <label class="block text-slate-900 font-black mb-1">Alasan Pertukaran (Opsional)</label>
                <textarea name="alasan" rows="2" placeholder="Contoh: Ada keperluan dinas luar / rapat dinas pada jam tersebut." class="w-full px-3 py-2 border-2 border-slate-900 rounded-lg bg-slate-50 text-xs font-bold shadow-[2px_2px_0px_0px_#0f172a] focus:outline-none"></textarea>
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t-2 border-slate-900">
                <button type="button" onclick="document.getElementById('modalTukarPengawas').classList.add('hidden')" class="px-3.5 py-2 border-2 border-slate-900 rounded-lg text-slate-900 bg-slate-100 hover:bg-slate-200 font-bold text-xs shadow-[2px_2px_0px_0px_#0f172a]">
                    Batal
                </button>
                <button type="submit" id="btnConfirmSwap" class="px-4 py-2 border-2 border-slate-900 rounded-lg text-purple-950 bg-[#F3D9FA] hover:bg-purple-200 font-black text-xs shadow-[2px_2px_0px_0px_#0f172a] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all">
                    Konfirmasi Pertukaran
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 4: CETAK DAFTAR HADIR PENGAWAS --}}
<div id="modalCetakDaftarHadir" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 hidden p-4">
    <div class="bg-white border-4 border-slate-900 rounded-2xl max-w-md w-full p-6 shadow-[8px_8px_0px_0px_#0f172a] space-y-4">
        <div class="flex items-center justify-between border-b-2 border-slate-900 pb-3">
            <h3 class="text-sm font-black font-heading text-slate-900 flex items-center gap-2 uppercase tracking-wide">
                <span>🖨️</span> Cetak Daftar Hadir Pengawas
            </h3>
            <button type="button" onclick="closeModalCetakDaftarHadir()" 
                    class="h-7 w-7 grid place-items-center rounded border-2 border-slate-900 bg-white font-black text-xs text-slate-900 shadow-[1px_1px_0px_0px_#0f172a] hover:bg-slate-900 hover:text-white transition-colors">
                ✕
            </button>
        </div>

        <form method="GET" action="{{ route('ujian.pengawas.print-daftar-hadir') }}" target="_blank" onsubmit="closeModalCetakDaftarHadir()">
            <input type="hidden" name="periode_id" value="{{ $activePeriode->id }}">

            <div class="space-y-4 text-xs">
                <div>
                    <label class="block text-slate-900 font-black mb-1">
                        Pilih Hari / Tanggal Ujian <span class="text-red-500">*</span>
                    </label>
                    <select name="tanggal" required class="w-full px-3 py-2 border-2 border-slate-900 rounded-lg bg-slate-50 font-bold text-xs shadow-[2px_2px_0px_0px_#0f172a] focus:outline-none">
                        <option value="all">Semua Hari Ujian (Otomatis Pisah Halaman per Hari &amp; Sesi)</option>
                        @foreach($examDates as $date)
                            <option value="{{ $date->format('Y-m-d') }}">
                                {{ $date->locale('id')->translatedFormat('l, d F Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-slate-900 font-black mb-1">
                        Tanggal Cetak Dokumen <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tanggal_cetak" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 border-2 border-slate-900 rounded-lg bg-slate-50 font-bold text-xs shadow-[2px_2px_0px_0px_#0f172a] focus:outline-none">
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-2 border-t-2 border-slate-900 mt-5">
                <button type="button" onclick="closeModalCetakDaftarHadir()" class="px-3.5 py-2 border-2 border-slate-900 rounded-lg text-slate-900 bg-slate-100 hover:bg-slate-200 font-bold text-xs shadow-[2px_2px_0px_0px_#0f172a]">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 border-2 border-slate-900 rounded-lg text-slate-900 bg-[#FFD43B] hover:bg-yellow-400 font-black text-xs shadow-[2px_2px_0px_0px_#0f172a] flex items-center gap-1.5 cursor-pointer active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all">
                    <span>🖨️</span>
                    <span>Cetak Dokumen (Landscape)</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 5: KONFIGURASI AUTO-PLOTTER OTOMATIS --}}
<div id="modalAutoPlotter" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-slate-900 rounded-2xl max-w-md w-full p-5 shadow-[8px_8px_0px_0px_#0f172a] relative animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b-2 border-slate-900 mb-4">
            <h3 class="text-sm font-black font-heading text-slate-900 flex items-center gap-2 uppercase tracking-wide">
                <span>⚡</span>
                <span>Konfigurasi Auto-Plotter Pengawas</span>
            </h3>
            <button type="button" onclick="document.getElementById('modalAutoPlotter').classList.add('hidden')" 
                    class="h-7 w-7 grid place-items-center rounded border-2 border-slate-900 bg-white font-black text-xs text-slate-900 shadow-[1px_1px_0px_0px_#0f172a] hover:bg-slate-900 hover:text-white transition-colors">
                ✕
            </button>
        </div>

        <form action="{{ route('ujian.pengawas.auto-plot') }}" method="POST" class="space-y-4 text-xs font-semibold">
            @csrf
            <input type="hidden" name="ujian_periode_id" value="{{ $activePeriode->id }}">

            <div class="p-3 bg-purple-50 border-2 border-slate-900 rounded-xl shadow-[3px_3px_0px_0px_#0f172a] space-y-1">
                <div class="text-[10px] uppercase font-black text-purple-950">Periode Ujian Aktif:</div>
                <div class="text-xs font-black text-slate-900">{{ $activePeriode->nama_periode }}</div>
                <div class="text-[10px] text-slate-700 font-medium">Sistem otomatis menyelaraskan ruang dengan target kelas jadwal dan menjamin 0 bentrok guru & ruang.</div>
            </div>

            {{-- Target Kelompok Ujian --}}
            <div>
                <label class="block text-slate-900 font-black mb-1.5">Target Kelompok Ujian <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-3 gap-1.5">
                    <label class="flex items-center gap-1.5 p-2 border-2 border-slate-900 rounded-lg cursor-pointer bg-white font-bold text-xs hover:bg-yellow-50 shadow-[1.5px_1.5px_0px_0px_#0f172a]">
                        <input type="radio" name="kelompok" value="3-6" {{ ($kelompok ?? '3-6') === '3-6' ? 'checked' : '' }} class="accent-slate-900">
                        <span>Kelas 3–6</span>
                    </label>
                    <label class="flex items-center gap-1.5 p-2 border-2 border-slate-900 rounded-lg cursor-pointer bg-white font-bold text-xs hover:bg-yellow-50 shadow-[1.5px_1.5px_0px_0px_#0f172a]">
                        <input type="radio" name="kelompok" value="1-2" {{ ($kelompok ?? '3-6') === '1-2' ? 'checked' : '' }} class="accent-slate-900">
                        <span>Kelas 1–2</span>
                    </label>
                    <label class="flex items-center gap-1.5 p-2 border-2 border-slate-900 rounded-lg cursor-pointer bg-white font-bold text-xs hover:bg-yellow-50 shadow-[1.5px_1.5px_0px_0px_#0f172a]">
                        <input type="radio" name="kelompok" value="all" {{ ($kelompok ?? '3-6') === 'all' ? 'checked' : '' }} class="accent-slate-900">
                        <span>Semua</span>
                    </label>
                </div>
            </div>

            {{-- Opsi Jumlah Pengawas per Ruang --}}
            <div>
                <label class="block text-slate-900 font-black mb-2">Jumlah Pengawas per Ruang:</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-2 p-2.5 border-2 border-slate-900 rounded-xl cursor-pointer hover:bg-yellow-50 font-black bg-white shadow-[2px_2px_0px_0px_#0f172a]">
                        <input type="radio" name="two_supervisors" value="0" checked class="w-4 h-4 text-purple-600 border-2 border-slate-900 accent-slate-900">
                        <div>
                            <div>1 Pengawas</div>
                            <div class="text-[10px] text-slate-500 font-normal">Pengawas 1 (Utama)</div>
                        </div>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 border-2 border-slate-900 rounded-xl cursor-pointer hover:bg-yellow-50 font-black bg-white shadow-[2px_2px_0px_0px_#0f172a]">
                        <input type="radio" name="two_supervisors" value="1" class="w-4 h-4 text-purple-600 border-2 border-slate-900 accent-slate-900">
                        <div>
                            <div>2 Pengawas</div>
                            <div class="text-[10px] text-slate-500 font-normal">Pengawas 1 & 2</div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Opsi Reset vs Append --}}
            <div>
                <label class="block text-slate-900 font-black mb-2">Strategi Penugasan:</label>
                <div class="space-y-2">
                    <label class="flex items-start gap-2.5 p-2.5 border-2 border-slate-900 rounded-xl cursor-pointer hover:bg-yellow-50 font-black bg-white shadow-[2px_2px_0px_0px_#0f172a]">
                        <input type="radio" name="reset_existing" value="1" checked class="mt-0.5 w-4 h-4 text-purple-600 border-2 border-slate-900 accent-slate-900">
                        <div>
                            <div>Reset Seluruh Penugasan (Rekomendasi)</div>
                            <div class="text-[10px] text-slate-600 font-normal">Mengosongkan penugasan lama periode ini dan mengalokasikan ulang secara bersih & merata.</div>
                        </div>
                    </label>
                    <label class="flex items-start gap-2.5 p-2.5 border-2 border-slate-900 rounded-xl cursor-pointer hover:bg-yellow-50 font-black bg-white shadow-[2px_2px_0px_0px_#0f172a]">
                        <input type="radio" name="reset_existing" value="0" class="mt-0.5 w-4 h-4 text-purple-600 border-2 border-slate-900 accent-slate-900">
                        <div>
                            <div>Pertahankan yang Ada (Hanya Isi Slot Kosong)</div>
                            <div class="text-[10px] text-slate-600 font-normal">Penugasan yang sudah ada tidak diubah; hanya mengisi ruang/sesi yang masih kosong.</div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Opsi Wali Kelas, Pengampu Mapel & Lock --}}
            <div class="space-y-2 rounded-xl border-2 border-slate-900 bg-slate-50 p-3">
                <label class="flex items-start gap-2 cursor-pointer text-[11px] font-bold text-slate-900">
                    <input type="hidden" name="prioritize_homeroom_teachers" value="0">
                    <input type="checkbox" name="prioritize_homeroom_teachers" value="1" {{ in_array($kelompok ?? 'all', ['1-2', 'all'], true) ? 'checked' : '' }} class="mt-0.5 w-4 h-4 rounded border-2 border-slate-900 accent-slate-900">
                    <span>
                        <span class="block">Prioritaskan Wali Kelas Mengawas Mapel Ampuannya di Kelas Perwalian (Kelas 1–2)</span>
                        <span class="block text-[10px] font-normal text-slate-600">Mapel di luar ampuannya diserahkan ke guru lain yang ditetapkan pada cakupan Kelas 1–2.</span>
                    </span>
                </label>
                <label class="flex items-start gap-2 cursor-pointer text-[11px] font-bold text-slate-900">
                    <input type="hidden" name="prioritize_subject_teachers" value="0">
                    <input type="checkbox" name="prioritize_subject_teachers" value="1" {{ in_array($kelompok ?? 'all', ['1-2', 'all'], true) ? 'checked' : '' }} class="mt-0.5 w-4 h-4 rounded border-2 border-slate-900 accent-slate-900">
                    <span>Prioritaskan Guru Pengampu Mapel di Kelas yang Diajar</span>
                </label>
                <label class="flex items-start gap-2 cursor-pointer text-[11px] font-bold text-slate-900">
                    <input type="hidden" name="preserve_locked" value="0">
                    <input type="checkbox" name="preserve_locked" value="1" checked class="mt-0.5 w-4 h-4 rounded border-2 border-slate-900 accent-slate-900">
                    <span>Pertahankan Jadwal yang Sudah Ditukar / Diedit Manual (saat Non-Reset)</span>
                </label>
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t-2 border-slate-900">
                <button type="button" onclick="document.getElementById('modalAutoPlotter').classList.add('hidden')" class="px-3.5 py-2 border-2 border-slate-900 rounded-lg text-slate-900 bg-slate-100 hover:bg-slate-200 font-bold text-xs shadow-[2px_2px_0px_0px_#0f172a]">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 border-2 border-slate-900 rounded-lg text-purple-950 bg-[#F3D9FA] hover:bg-purple-200 font-black text-xs shadow-[2px_2px_0px_0px_#0f172a] flex items-center gap-1.5 cursor-pointer active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all">
                    <span>⚡</span>
                    <span>Mulai Auto-Plot</span>
                </button>
            </div>

            <div class="pt-3 border-t-2 border-dashed border-slate-900 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-black text-slate-900">Hanya Kosongkan Data:</div>
                    <div class="text-[10px] text-slate-600">Hapus seluruh jadwal pengawas periode ini tanpa alokasi baru.</div>
                </div>
                <button type="button" onclick="if(confirm('Apakah Anda yakin ingin MENGHAPUS / MERESET seluruh data penugasan pengawas periode ini? Tindakan ini tidak dapat dibatalkan.')) document.getElementById('formResetPenugasan').submit();" 
                        class="px-3 py-1.5 bg-[#FFE3E3] hover:bg-rose-200 text-rose-950 border-2 border-slate-900 rounded-lg text-xs font-black shadow-[1.5px_1.5px_0px_0px_#0f172a] flex items-center gap-1 cursor-pointer active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all">
                    <span>🗑️</span>
                    <span>Reset Data Saja</span>
                </button>
            </div>
        </form>

        <form id="formResetPenugasan" action="{{ route('ujian.pengawas.reset') }}" method="POST" class="hidden">
            @csrf
            <input type="hidden" name="ujian_periode_id" value="{{ $activePeriode->id }}">
        </form>
    </div>
</div>

{{-- MODAL 6: IMPORT PDF MATRIKS (JADWAL.PDF) --}}
<div id="modalImportPdfMatriks" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-slate-900 rounded-2xl max-w-md w-full p-5 shadow-[8px_8px_0px_0px_#0f172a] relative animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b-2 border-slate-900 mb-3">
            <h3 class="text-sm font-black font-heading text-slate-900 flex items-center gap-2 uppercase tracking-wide">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                </svg>
                <span>Import PDF Matriks Jadwal &amp; Pengawas</span>
            </h3>
            <button type="button" onclick="document.getElementById('modalImportPdfMatriks').classList.add('hidden')" 
                    class="h-7 w-7 grid place-items-center rounded border-2 border-slate-900 bg-white font-black text-xs text-slate-900 shadow-[1px_1px_0px_0px_#0f172a] hover:bg-slate-900 hover:text-white transition-colors">
                ✕
            </button>
        </div>

        <div class="p-3 bg-blue-50 border-2 border-slate-900 rounded-xl mb-3.5 text-xs shadow-[2px_2px_0px_0px_#0f172a]">
            <div class="font-black text-blue-950 flex items-center gap-1.5 mb-1">
                <span>📄</span>
                <span>Format Dokumen PDF Matriks (seperti jadwal.pdf)</span>
            </div>
            <p class="text-[11px] text-blue-900 font-semibold leading-relaxed">
                Sistem membaca tabel matriks tanggal, jam ujian, mata pelajaran, dan menelusuri nomor pengawas pada 16 ruangan lalu memetakan langsung ke ID User guru madrasah.
            </p>
        </div>

        <form action="{{ route('ujian.pengawas.import-pdf') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs font-semibold">
            @csrf
            <input type="hidden" name="ujian_periode_id" value="{{ $activePeriode->id }}">

            <div>
                <label class="block text-slate-900 font-black mb-1">Pilih File PDF Matriks <span class="text-red-500">*</span></label>
                <input type="file" name="file" required accept=".pdf"
                       class="w-full px-3 py-2 border-2 border-slate-900 rounded-lg bg-slate-50 text-xs font-bold shadow-[2px_2px_0px_0px_#0f172a] file:mr-3 file:py-1 file:px-2.5 file:rounded file:border-2 file:border-slate-900 file:text-xs file:font-black file:bg-[#FFD43B] file:hover:bg-yellow-400 file:cursor-pointer cursor-pointer">
                <p class="text-[10px] text-slate-600 mt-1 font-semibold">Maksimal 5MB (.pdf).</p>
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t-2 border-slate-900">
                <button type="button" onclick="document.getElementById('modalImportPdfMatriks').classList.add('hidden')" class="px-3.5 py-2 border-2 border-slate-900 rounded-lg text-slate-900 bg-slate-100 hover:bg-slate-200 font-bold text-xs shadow-[2px_2px_0px_0px_#0f172a]">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 border-2 border-slate-900 rounded-lg text-white bg-[#5294FF] hover:bg-blue-600 font-black text-xs shadow-[2px_2px_0px_0px_#0f172a] inline-flex items-center gap-1.5 cursor-pointer active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                    </svg>
                    <span>Import PDF Matriks</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 7: SINKRON PEMETAAN PENGAMPU MAPEL --}}
<div id="modalSyncTeachingMapping" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-slate-900 rounded-2xl max-w-2xl w-full p-5 shadow-[8px_8px_0px_0px_#0f172a] max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between pb-3 border-b-2 border-slate-900 mb-4 shrink-0">
            <h3 class="text-sm font-black font-heading text-slate-900 flex items-center gap-2 uppercase tracking-wide">
                <span>🔄</span>
                <span>Sinkron Wali Kelas &amp; Pengampu Mapel</span>
            </h3>
            <button type="button" onclick="document.getElementById('modalSyncTeachingMapping').classList.add('hidden')"
                    class="h-7 w-7 grid place-items-center rounded border-2 border-slate-900 bg-white font-black text-xs text-slate-900 shadow-[1px_1px_0px_0px_#0f172a] hover:bg-slate-900 hover:text-white transition-colors">
                ✕
            </button>
        </div>

        <form action="{{ route('ujian.pengawas.mapping.sync-teaching') }}" method="POST" class="space-y-4 text-xs font-semibold overflow-y-auto pr-1">
            @csrf
            <input type="hidden" name="ujian_periode_id" value="{{ $activePeriode->id }}">

            <div>
                <label class="block text-slate-900 font-black mb-1.5">Tingkat Kelas Target <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-1.5">
                    @foreach(['1', '2', '3', '4', '5', '6'] as $lvl)
                        <label class="flex items-center gap-1.5 p-2 border-2 border-slate-900 rounded-lg cursor-pointer bg-white font-black hover:bg-yellow-50">
                            <input type="checkbox" name="levels[]" value="{{ $lvl }}" {{ in_array($lvl, ['1', '2'], true) ? 'checked' : '' }}
                                   onchange="renderSyncTeachingPreview()"
                                   class="sync-level-check w-4 h-4 rounded border-2 border-slate-900 accent-slate-900">
                            <span>Kelas {{ $lvl }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="space-y-2 rounded-xl border-2 border-slate-900 bg-sky-50 p-3">
                <label class="flex items-start gap-2 cursor-pointer text-[11px] font-bold text-slate-900">
                    <input type="hidden" name="include_homeroom" value="0">
                    <input type="checkbox" id="sync_include_homeroom" name="include_homeroom" value="1" checked
                           onchange="renderSyncTeachingPreview()"
                           class="mt-0.5 w-4 h-4 rounded border-2 border-slate-900 accent-slate-900">
                    <span>Sertakan Wali Kelas dari Master Kelas (otomatis mode Wali Kelas sesuai mapel ampuannya)</span>
                </label>
                <label class="flex items-start gap-2 cursor-pointer text-[11px] font-bold text-slate-900">
                    <input type="hidden" name="lock_homeroom_only" value="0">
                    <input type="checkbox" id="sync_lock_homeroom_only" name="lock_homeroom_only" value="1"
                           class="mt-0.5 w-4 h-4 rounded border-2 border-slate-900 accent-slate-900">
                    <span>Kunci Wali Kelas hanya mengawas di kelas perwaliannya saja (tidak dijadikan cadangan di ruang lain)</span>
                </label>
            </div>

            <div>
                <label class="block text-slate-900 font-black mb-1">Filter Mata Pelajaran (Opsional)</label>
                <div class="max-h-32 overflow-y-auto grid grid-cols-1 sm:grid-cols-2 gap-1.5 p-2.5 border-2 border-slate-900 rounded-xl bg-slate-50">
                    @foreach($subjects ?? [] as $subj)
                        <label class="flex items-center gap-1.5 text-[11px] font-bold text-slate-800 cursor-pointer">
                            <input type="checkbox" name="subject_ids[]" value="{{ $subj->id }}"
                                   onchange="renderSyncTeachingPreview()"
                                   class="sync-subject-check w-3.5 h-3.5 rounded border border-slate-900 accent-slate-900">
                            <span class="truncate">{{ $subj->name }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="text-[10px] text-slate-600 mt-1">Kosongkan untuk mengunci semua mapel yang diajar guru, atau pilih mapel inti tertentu (mis. 6 mapel utama Kelas 1–2).</p>
            </div>

            <div>
                <div class="text-[11px] font-black uppercase text-slate-900 mb-1.5">Pratinjau Guru yang Akan Disinkronkan</div>
                <div class="max-h-48 overflow-y-auto border-2 border-slate-900 rounded-xl">
                    <table class="w-full text-left text-[11px] border-collapse">
                        <thead class="bg-slate-100 border-b-2 border-slate-900 font-black uppercase text-[10px] sticky top-0">
                            <tr>
                                <th class="px-3 py-2 border-r border-slate-300">Nama Guru</th>
                                <th class="px-3 py-2 border-r border-slate-300">Rombel Diajar</th>
                                <th class="px-3 py-2">Daftar Mapel Dikunci</th>
                            </tr>
                        </thead>
                        <tbody id="syncTeachingPreviewBody" class="divide-y divide-slate-200"></tbody>
                    </table>
                </div>
                <div id="syncOtherMappedPreviewBox" class="mt-2 hidden rounded-lg border-2 border-slate-900 bg-amber-50 p-2.5 text-[11px] text-slate-900"></div>
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t-2 border-slate-900">
                <button type="button" onclick="document.getElementById('modalSyncTeachingMapping').classList.add('hidden')" class="px-3.5 py-2 border-2 border-slate-900 rounded-lg text-slate-900 bg-slate-100 hover:bg-slate-200 font-bold text-xs shadow-[2px_2px_0px_0px_#0f172a]">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 border-2 border-slate-900 rounded-lg text-slate-900 bg-[#FFD43B] hover:bg-yellow-400 font-black text-xs shadow-[2px_2px_0px_0px_#0f172a] cursor-pointer">
                    Sinkronkan Sekarang
                </button>
            </div>
        </form>
    </div>
</div>
@endif
