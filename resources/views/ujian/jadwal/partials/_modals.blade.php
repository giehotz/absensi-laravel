@if($activePeriode)
{{-- Modal Tambah Jadwal --}}
<div id="modalTambahJadwal" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-black rounded-xl max-w-lg w-full p-5 shadow-[6px_6px_0px_0px_#000] relative animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-4">
            <h3 class="text-sm font-black font-heading text-black flex items-center gap-2">
                <span>➕</span>
                <span>Tambah Slot Jadwal Ujian</span>
            </h3>
            <button type="button" onclick="document.getElementById('modalTambahJadwal').classList.add('hidden')" class="text-sm font-black text-slate-500 hover:text-black">✕</button>
        </div>

        <form action="{{ route('ujian.jadwal.store') }}" method="POST" class="space-y-3 text-xs font-semibold">
            @csrf
            <input type="hidden" name="ujian_periode_id" value="{{ $activePeriode->id }}">

            <div>
                <label class="block text-slate-700 font-bold mb-1">Mata Pelajaran</label>
                <select name="subject_id" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold">
                    <option value="">-- Pilih dari {{ $subjects->count() }} Mapel Terdaftar --</option>
                    @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}">{{ $sub->name }} ({{ $sub->code ?: '-' }})</option>
                    @endforeach
                </select>
                <div class="mt-1">
                    <input type="text" name="nama_mapel_kustom" placeholder="Atau ketik nama mapel kustom (jika tidak ada di daftar)" class="w-full px-3 py-1.5 border border-slate-400 rounded bg-white text-[11px]">
                </div>
            </div>

            <div class="space-y-2.5">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Target Rombel Spesifik (Opsional jika berlaku per tingkat)</label>
                    <select name="school_class_id" id="add_school_class_id" onchange="updateBentukUjianAddForm()" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                        <option value="">-- Semua Kelas / Sesuai Pilihan Tingkat --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} (Tingkat {{ $classLevels[$c->id] ?? $c->level }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Ditetapkan di Tingkat Ini (Bisa Pilih > 1 Tingkat)</label>
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-1.5 p-2 bg-slate-50 border-2 border-black rounded-lg">
                        @for($t = 1; $t <= 6; $t++)
                            <label class="flex items-center justify-center gap-1.5 px-2 py-1.5 bg-white border border-black rounded cursor-pointer hover:bg-amber-100 text-[11px] font-bold">
                                <input type="checkbox" name="tingkat[]" value="{{ $t }}" onchange="updateBentukUjianAddForm()" class="rounded border-black accent-black">
                                <span>Kls {{ $t }}</span>
                            </label>
                        @endfor
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Tanggal</label>
                    <input type="date" name="tanggal_ujian" required value="{{ date('Y-m-d') }}" class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-semibold">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Sesi Ke-</label>
                    <select name="sesi_ke" id="add_sesi_ke" onchange="applySesiTime('add', this)" required class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold">
                        @forelse($sesis as $s)
                            <option value="{{ $s->sesi_ke }}"
                                    data-mulai="{{ substr($s->jam_mulai, 0, 5) }}"
                                    data-selesai="{{ substr($s->jam_selesai, 0, 5) }}"
                                    data-durasi="{{ $s->durasi_menit }}"
                                    {{ $loop->first ? 'selected' : '' }}>
                                Sesi {{ $s->sesi_ke }} ({{ substr($s->jam_mulai, 0, 5) }} - {{ substr($s->jam_selesai, 0, 5) }})
                            </option>
                        @empty
                            <option value="1" data-mulai="07:30" data-selesai="09:30" data-durasi="120" selected>Sesi 1 (07:30 - 09:30)</option>
                            <option value="2" data-mulai="10:00" data-selesai="12:00" data-durasi="120">Sesi 2 (10:00 - 12:00)</option>
                        @endforelse
                        @for($extra = 1; $extra <= 6; $extra++)
                            @if(!$sesis->contains('sesi_ke', $extra))
                                <option value="{{ $extra }}">Sesi {{ $extra }} (Manual)</option>
                            @endif
                        @endfor
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Jam Mulai</label>
                    <input type="time" name="jam_mulai" id="add_jam_mulai" required value="{{ $sesis->first()?->jam_mulai ? substr($sesis->first()->jam_mulai, 0, 5) : '07:30' }}" onchange="autoCalcJadwalDuration('add')" class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-semibold">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Jam Selesai</label>
                    <input type="time" name="jam_selesai" id="add_jam_selesai" required value="{{ $sesis->first()?->jam_selesai ? substr($sesis->first()->jam_selesai, 0, 5) : '09:30' }}" onchange="autoCalcJadwalDuration('add')" class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-semibold">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Durasi (Menit)</label>
                    <input type="number" name="durasi_menit" id="add_durasi_menit" min="15" value="{{ $sesis->first()?->durasi_menit ?? 120 }}" required class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold">
                </div>
            </div>

            {{-- Bagian Bentuk Ujian (Single / Split Bawah & Atas) --}}
            <div id="wrapper_bentuk_ujian_single" class="space-y-1">
                <label class="block text-slate-700 font-bold mb-1" id="label_bentuk_ujian_single">Bentuk Ujian</label>
                <select name="jenis_soal" id="add_jenis_soal_single" onchange="toggleCustomBentukUjian(this, 'add_jenis_soal_kustom_single_wrapper')" required class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold">
                    <option value="Tertulis" selected>Tertulis</option>
                    <option value="CBT / Digital">CBT / Digital</option>
                    <option value="Praktik">Praktik</option>
                    <option value="Portofolio">Portofolio</option>
                    <option value="Lisan">Lisan</option>
                    <option value="Lainnya">Lainnya...</option>
                </select>
                <div id="add_jenis_soal_kustom_single_wrapper" class="hidden mt-1">
                    <input type="text" name="jenis_soal_kustom" id="add_jenis_soal_kustom_single" placeholder="Ketik bentuk ujian kustom (misal: Proyek, Observasi)..." class="w-full px-2.5 py-1.5 border-2 border-black rounded-lg bg-white text-xs font-semibold">
                </div>
            </div>

            <div id="wrapper_bentuk_ujian_split" class="hidden space-y-2 p-3 bg-amber-50 border-2 border-black rounded-lg shadow-[2px_2px_0px_0px_#000]">
                <div class="flex items-center gap-1.5 text-xs font-black text-slate-900 border-b border-black pb-1.5">
                    <span>⚡</span>
                    <span>Pilihan Bentuk Ujian Kelas Bawah &amp; Kelas Atas</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-slate-800 font-black text-[11px] mb-1">
                            📘 Kelas Bawah (Tingkat 1–3)
                        </label>
                        <select name="jenis_soal_bawah" id="add_jenis_soal_bawah" onchange="toggleCustomBentukUjian(this, 'add_jenis_soal_bawah_kustom_wrapper')" class="w-full px-2.5 py-1.5 border-2 border-black rounded-lg bg-white text-xs font-bold">
                            <option value="Tertulis" selected>Tertulis</option>
                            <option value="CBT / Digital">CBT / Digital</option>
                            <option value="Praktik">Praktik</option>
                            <option value="Portofolio">Portofolio</option>
                            <option value="Lisan">Lisan</option>
                            <option value="Lainnya">Lainnya...</option>
                        </select>
                        <div id="add_jenis_soal_bawah_kustom_wrapper" class="hidden mt-1">
                            <input type="text" name="jenis_soal_bawah_kustom" id="add_jenis_soal_bawah_kustom" placeholder="Bentuk kustom kelas bawah..." class="w-full px-2 py-1 border border-black rounded text-[11px]">
                        </div>
                    </div>
                    <div>
                        <label class="block text-slate-800 font-black text-[11px] mb-1">
                            💻 Kelas Atas (Tingkat 4–6)
                        </label>
                        <select name="jenis_soal_atas" id="add_jenis_soal_atas" onchange="toggleCustomBentukUjian(this, 'add_jenis_soal_atas_kustom_wrapper')" class="w-full px-2.5 py-1.5 border-2 border-black rounded-lg bg-white text-xs font-bold">
                            <option value="CBT / Digital" selected>CBT / Digital</option>
                            <option value="Tertulis">Tertulis</option>
                            <option value="Praktik">Praktik</option>
                            <option value="Portofolio">Portofolio</option>
                            <option value="Lisan">Lisan</option>
                            <option value="Lainnya">Lainnya...</option>
                        </select>
                        <div id="add_jenis_soal_atas_kustom_wrapper" class="hidden mt-1">
                            <input type="text" name="jenis_soal_atas_kustom" id="add_jenis_soal_atas_kustom" placeholder="Bentuk kustom kelas atas..." class="w-full px-2 py-1 border border-black rounded text-[11px]">
                        </div>
                    </div>
                </div>
                <p class="text-[10px] font-semibold text-slate-600 italic">
                    * Jika bentuk ujian kelas bawah dan atas berbeda, sistem akan otomatis membuat 2 slot jadwal terpisah. Jika sama, akan digabung menjadi 1 slot.
                </p>
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Catatan (Opsional)</label>
                <input type="text" name="catatan" placeholder="Catatan khusus slot jadwal..." class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-black">
                <button type="button" onclick="document.getElementById('modalTambahJadwal').classList.add('hidden')" class="px-3 py-2 border-2 border-black rounded-lg text-black bg-slate-100 hover:bg-slate-200 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 border-2 border-black rounded-lg text-black bg-[#FFD43B] hover:bg-yellow-400 font-black text-xs shadow-[2px_2px_0px_0px_#000]">
                    Simpan Jadwal
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Jadwal --}}
<div id="modalEditJadwal" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-black rounded-xl max-w-lg w-full p-5 shadow-[6px_6px_0px_0px_#000] relative animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-4">
            <h3 class="text-sm font-black font-heading text-black flex items-center gap-2">
                <span>✏️</span>
                <span>Edit Slot Jadwal Ujian</span>
            </h3>
            <button type="button" onclick="document.getElementById('modalEditJadwal').classList.add('hidden')" class="text-sm font-black text-slate-500 hover:text-black">✕</button>
        </div>

        <form id="formEditJadwal" method="POST" action="" class="space-y-3 text-xs font-semibold">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-slate-700 font-bold mb-1">Mata Pelajaran</label>
                <select id="edit_subject_id" name="subject_id" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold">
                    <option value="">-- Pilih dari {{ $subjects->count() }} Mapel Terdaftar --</option>
                    @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}">{{ $sub->name }} ({{ $sub->code ?: '-' }})</option>
                    @endforeach
                </select>
                <div class="mt-1">
                    <input type="text" id="edit_nama_mapel_kustom" name="nama_mapel_kustom" placeholder="Atau ketik nama mapel kustom (jika tidak ada di daftar)" class="w-full px-3 py-1.5 border border-slate-400 rounded bg-white text-[11px]">
                </div>
            </div>

            <div class="space-y-2.5">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Target Rombel Spesifik (Opsional jika berlaku per tingkat)</label>
                    <select id="edit_school_class_id" name="school_class_id" class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
                        <option value="">-- Semua Kelas / Sesuai Pilihan Tingkat --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} (Tingkat {{ $classLevels[$c->id] ?? $c->level }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Ditetapkan di Tingkat Ini (Bisa Pilih > 1 Tingkat)</label>
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-1.5 p-2 bg-slate-50 border-2 border-black rounded-lg">
                        @for($t = 1; $t <= 6; $t++)
                            <label class="flex items-center justify-center gap-1.5 px-2 py-1.5 bg-white border border-black rounded cursor-pointer hover:bg-amber-100 text-[11px] font-bold">
                                <input type="checkbox" name="tingkat[]" value="{{ $t }}" class="edit-tingkat-cb rounded border-black accent-black">
                                <span>Kls {{ $t }}</span>
                            </label>
                        @endfor
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Tanggal</label>
                    <input type="date" id="edit_tanggal_ujian" name="tanggal_ujian" required class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-semibold">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Sesi Ke-</label>
                    <select name="sesi_ke" id="edit_sesi_ke" onchange="applySesiTime('edit', this)" required class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold">
                        @forelse($sesis as $s)
                            <option value="{{ $s->sesi_ke }}"
                                    data-mulai="{{ substr($s->jam_mulai, 0, 5) }}"
                                    data-selesai="{{ substr($s->jam_selesai, 0, 5) }}"
                                    data-durasi="{{ $s->durasi_menit }}">
                                Sesi {{ $s->sesi_ke }} ({{ substr($s->jam_mulai, 0, 5) }} - {{ substr($s->jam_selesai, 0, 5) }})
                            </option>
                        @empty
                            <option value="1" data-mulai="07:30" data-selesai="09:30" data-durasi="120">Sesi 1 (07:30 - 09:30)</option>
                            <option value="2" data-mulai="10:00" data-selesai="12:00" data-durasi="120">Sesi 2 (10:00 - 12:00)</option>
                        @endforelse
                        @for($extra = 1; $extra <= 6; $extra++)
                            @if(!$sesis->contains('sesi_ke', $extra))
                                <option value="{{ $extra }}">Sesi {{ $extra }} (Manual)</option>
                            @endif
                        @endfor
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Jam Mulai</label>
                    <input type="time" id="edit_jam_mulai" name="jam_mulai" required onchange="autoCalcJadwalDuration('edit')" class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-semibold">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Jam Selesai</label>
                    <input type="time" id="edit_jam_selesai" name="jam_selesai" required onchange="autoCalcJadwalDuration('edit')" class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-semibold">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Durasi (Menit)</label>
                    <input type="number" id="edit_durasi_menit" name="durasi_menit" min="15" required class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold">
                </div>
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Bentuk Ujian</label>
                <select id="edit_jenis_soal" name="jenis_soal" onchange="toggleCustomBentukUjian(this, 'edit_jenis_soal_kustom_wrapper')" required class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold">
                    <option value="Tertulis">Tertulis</option>
                    <option value="CBT / Digital">CBT / Digital</option>
                    <option value="Praktik">Praktik</option>
                    <option value="Portofolio">Portofolio</option>
                    <option value="Lisan">Lisan</option>
                    <option value="Lainnya">Lainnya...</option>
                </select>
                <div id="edit_jenis_soal_kustom_wrapper" class="hidden mt-1">
                    <input type="text" name="jenis_soal_kustom" id="edit_jenis_soal_kustom" placeholder="Ketik bentuk ujian kustom..." class="w-full px-2.5 py-1.5 border-2 border-black rounded-lg bg-white text-xs font-semibold">
                </div>
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Catatan (Opsional)</label>
                <input type="text" id="edit_catatan" name="catatan" placeholder="Catatan khusus slot jadwal..." class="w-full px-2.5 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs">
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-black">
                <button type="button" onclick="document.getElementById('modalEditJadwal').classList.add('hidden')" class="px-3 py-2 border-2 border-black rounded-lg text-black bg-slate-100 hover:bg-slate-200 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 border-2 border-black rounded-lg text-black bg-[#FFD43B] hover:bg-yellow-400 font-black text-xs shadow-[2px_2px_0px_0px_#000]">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Salin Jadwal Antar Rombel --}}
<div id="modalSalinJadwal" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-black rounded-xl max-w-lg w-full p-5 shadow-[6px_6px_0px_0px_#000] relative animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-4">
            <h3 class="text-sm font-black font-heading text-black flex items-center gap-2">
                <span>📑</span>
                <span>Salin Jadwal Antar Rombel Paralel</span>
            </h3>
            <button type="button" onclick="document.getElementById('modalSalinJadwal').classList.add('hidden')" class="text-sm font-black text-slate-500 hover:text-black">✕</button>
        </div>

        <form action="{{ route('ujian.jadwal.copy') }}" method="POST" class="space-y-3.5 text-xs font-semibold">
            @csrf
            <input type="hidden" name="ujian_periode_id" value="{{ $activePeriode->id }}">

            <div>
                <label class="block text-slate-700 font-bold mb-1">Rombel Sumber (Yang Sudah Memiliki Jadwal)</label>
                <select name="source_class_id" required class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold">
                    <option value="">-- Pilih Rombel Sumber --</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} (Tingkat {{ $classLevels[$c->id] ?? $c->level }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-slate-700 font-bold mb-1">Pilih Rombel Tujuan (Bisa Lebih Dari Satu)</label>
                <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto p-2.5 border-2 border-black rounded-lg bg-slate-50">
                    @foreach($classes as $c)
                        <label class="flex items-center gap-2 text-xs font-bold text-slate-800 p-1 hover:bg-slate-200 rounded cursor-pointer">
                            <input type="checkbox" name="target_class_ids[]" value="{{ $c->id }}" class="rounded border-black text-[#5294FF]">
                            <span>{{ $c->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-black">
                <button type="button" onclick="document.getElementById('modalSalinJadwal').classList.add('hidden')" class="px-3 py-2 border-2 border-black rounded-lg text-black bg-slate-100 hover:bg-slate-200 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 border-2 border-black rounded-lg text-white bg-[#5294FF] hover:bg-blue-400 font-black text-xs shadow-[2px_2px_0px_0px_#000]">
                    Duplikasi Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Upload / Import Template Jadwal Ujian (Excel & PDF) --}}
<div id="modalImportJadwal" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-black rounded-xl max-w-lg w-full p-5 shadow-[6px_6px_0px_0px_#000] relative animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-3">
            <h3 class="text-sm font-black font-heading text-black flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                </svg>
                <span>Upload & Import Jadwal Ujian</span>
            </h3>
            <button type="button" onclick="document.getElementById('modalImportJadwal').classList.add('hidden')" class="text-sm font-black text-slate-500 hover:text-black">✕</button>
        </div>

        {{-- Tab Switcher: Excel vs PDF --}}
        <div class="flex gap-2 mb-4 border-b-2 border-black pb-2">
            <button type="button" id="tabBtnExcel" onclick="switchImportJadwalTab('excel')"
                    class="px-3 py-1.5 rounded-lg border-2 border-black font-black text-xs bg-[#FFD43B] text-black shadow-[1px_1px_0px_#000] cursor-pointer">
                📗 File Excel (.xlsx / .csv)
            </button>
            <button type="button" id="tabBtnPdf" onclick="switchImportJadwalTab('pdf')"
                    class="px-3 py-1.5 rounded-lg border-2 border-black font-bold text-xs bg-slate-100 text-slate-700 hover:bg-slate-200 cursor-pointer">
                📕 File PDF Matriks (jadwal.pdf)
            </button>
        </div>

        {{-- Tab 1: Form Excel --}}
        <div id="tabContentExcel">
            <div class="p-3.5 bg-amber-50 border-2 border-black rounded-lg mb-3.5 text-xs space-y-2.5">
                <div class="font-black text-black flex items-center justify-between">
                    <span>Unduh Template Excel Resmi:</span>
                    <span class="text-[10px] text-slate-500 font-normal">Sesuai Kalender Pemetaan</span>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('ujian.jadwal.template', ['periode_id' => $activePeriode->id, 'kelompok' => 'bawah']) }}"
                       class="px-2.5 py-1.5 bg-[#FFD43B] hover:bg-yellow-400 text-black border-2 border-black rounded-lg font-black text-[11px] shadow-[1.5px_1.5px_0px_0px_#000] inline-flex items-center gap-1.5 cursor-pointer">
                        <span>📥</span>
                        <span>Template Kelas Bawah (1–2)</span>
                    </a>
                    <a href="{{ route('ujian.jadwal.template', ['periode_id' => $activePeriode->id, 'kelompok' => 'atas']) }}"
                       class="px-2.5 py-1.5 bg-[#4DABF7] hover:bg-sky-400 text-black border-2 border-black rounded-lg font-black text-[11px] shadow-[1.5px_1.5px_0px_0px_#000] inline-flex items-center gap-1.5 cursor-pointer">
                        <span>📥</span>
                        <span>Template Kelas Atas (3–6)</span>
                    </a>
                    <a href="{{ route('ujian.jadwal.template') }}" title="Template Legacy 5 Kolom"
                       class="px-2 py-1 bg-white hover:bg-slate-100 text-slate-700 border border-black rounded font-bold text-[10px] inline-flex items-center gap-1">
                        <span>Format Lama (5 Kolom)</span>
                    </a>
                </div>

                @php
                    $hasBawahConfig = isset($pemetaanConfigs['bawah']);
                    $hasAtasConfig = isset($pemetaanConfigs['atas']);
                @endphp

                @if(! $hasBawahConfig || ! $hasAtasConfig)
                    <div class="p-2 bg-yellow-100/80 border border-yellow-800 rounded text-[11px] text-yellow-950 flex items-start gap-1.5">
                        <span class="shrink-0">⚠️</span>
                        <div class="leading-tight">
                            <span>Pemetaan kalender belum lengkap di Sub-Menu 1. Template akan menggunakan tanggal periode sebagai fallback.</span>
                            <a href="{{ route('ujian.jenis.index', ['tab' => 'pemetaan-hari', 'periode_id' => $activePeriode->id]) }}" class="font-bold underline ml-1 text-black">
                                Atur Pemetaan Hari & Sesi →
                            </a>
                        </div>
                    </div>
                @endif

                <p class="text-[11px] text-slate-600 font-medium leading-relaxed">
                    Sistem otomatis mendeteksi format template (v2 Bawah per rombel, v2 Atas per tingkat, atau template lama).
                </p>

                <div class="p-2.5 bg-blue-50 border-2 border-black rounded-lg text-[11px] text-blue-950 flex items-start gap-2">
                    <span class="text-sm shrink-0">ℹ️</span>
                    <div>
                        <span class="font-bold">Mode Timpa Otomatis:</span>
                        <span class="font-medium text-slate-700">
                            Mengunggah template akan secara otomatis mengganti (menimpa) seluruh jadwal yang ada pada kelompok kelas tersebut (misal template Kelas Atas menimpa jadwal kelas 3–6), sehingga tidak memicu bentrok dengan jadwal lama.
                        </span>
                    </div>
                </div>
            </div>

            <form action="{{ route('ujian.jadwal.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs font-semibold">
                @csrf
                <input type="hidden" name="ujian_periode_id" value="{{ $activePeriode->id }}">

                <div>
                    <label class="block text-slate-800 font-bold mb-1">Pilih File Spreadsheet (Excel / CSV) <span class="text-red-500">*</span></label>
                    <input type="file" name="file" required accept=".xlsx,.xls,.csv"
                           class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold file:mr-3 file:py-1 file:px-2.5 file:rounded file:border file:border-black file:text-xs file:font-black file:bg-[#FFD43B] file:hover:bg-yellow-400 file:cursor-pointer cursor-pointer">
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-black">
                    <button type="button" onclick="document.getElementById('modalImportJadwal').classList.add('hidden')" class="px-3.5 py-2 border-2 border-black rounded-lg text-black bg-slate-100 hover:bg-slate-200 font-bold text-xs">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 border-2 border-black rounded-lg text-black bg-[#51CF66] hover:bg-green-500 font-black text-xs shadow-[2px_2px_0px_0px_#000] inline-flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                        </svg>
                        <span>Import Spreadsheet</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Tab 2: Form PDF Matriks --}}
        <div id="tabContentPdf" class="hidden">
            <div class="p-3 bg-blue-50 border-2 border-black rounded-lg mb-3.5 text-xs">
                <div class="font-black text-blue-950 flex items-center gap-1.5 mb-1">
                    <span>📄</span>
                    <span>Format Matriks PDF Resmi (seperti jadwal.pdf)</span>
                </div>
                <p class="text-[11px] text-blue-900 font-medium leading-relaxed">
                    Sistem akan membaca matriks tanggal, jam ujian, mata pelajaran, serta menelusuri nomor pengawas di 16 ruangan lalu memetakannya langsung ke ID User guru madrasah.
                </p>
            </div>

            <form action="{{ route('ujian.jadwal.import-pdf') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs font-semibold">
                @csrf
                <input type="hidden" name="ujian_periode_id" value="{{ $activePeriode->id }}">

                <div>
                    <label class="block text-slate-800 font-bold mb-1">Pilih File PDF Matriks Jadwal <span class="text-red-500">*</span></label>
                    <input type="file" name="file" required accept=".pdf"
                           class="w-full px-3 py-2 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold file:mr-3 file:py-1 file:px-2.5 file:rounded file:border file:border-black file:text-xs file:font-black file:bg-[#FFD43B] file:hover:bg-yellow-400 file:cursor-pointer cursor-pointer">
                    <p class="text-[10px] text-slate-500 mt-1 font-normal">Format file .pdf matriks jadwal (Maksimal 5MB).</p>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-black">
                    <button type="button" onclick="document.getElementById('modalImportJadwal').classList.add('hidden')" class="px-3.5 py-2 border-2 border-black rounded-lg text-black bg-slate-100 hover:bg-slate-200 font-bold text-xs">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 border-2 border-black rounded-lg text-white bg-[#5294FF] hover:bg-blue-600 font-black text-xs shadow-[2px_2px_0px_0px_#000] inline-flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                        </svg>
                        <span>Import PDF Matriks</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Salin Jadwal Antar-Tingkat (Tingkat Asal -> Tingkat Tujuan) --}}
<div id="modalSalinJadwalTingkat" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 hidden">
    <div class="bg-white border-4 border-black rounded-xl max-w-2xl w-full p-5 shadow-[6px_6px_0px_0px_#000] relative max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b-2 border-black mb-4">
            <h3 class="text-sm font-black font-heading text-black flex items-center gap-2">
                <span class="p-1 bg-[#9B51E0] text-white rounded border border-black text-xs">⚡</span>
                <span>Salin Jadwal Antar-Tingkat (Copy & Sync Levels)</span>
            </h3>
            <button type="button" onclick="document.getElementById('modalSalinJadwalTingkat').classList.add('hidden')" class="text-sm font-black text-slate-500 hover:text-black">✕</button>
        </div>

        <form id="formSalinJadwalTingkat" action="{{ route('ujian.jadwal.sync-levels') }}" method="POST" class="space-y-4 text-xs font-semibold">
            @csrf
            <input type="hidden" name="ujian_periode_id" value="{{ $activePeriode->id }}">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-800 font-bold mb-1">Tingkat Asal (Sumber Jadwal) <span class="text-red-500">*</span></label>
                    <select name="source_level" id="sync_source_level" required onchange="updateSyncTargetOptions()"
                            class="w-full px-3 py-2 border-2 border-black rounded-lg bg-white text-xs font-bold shadow-[2px_2px_0px_0px_#000]">
                        <option value="">-- Pilih Tingkat Sumber --</option>
                        @foreach([1, 2, 3, 4, 5, 6] as $lvl)
                            <option value="{{ $lvl }}">Kelas {{ $lvl }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-slate-800 font-bold mb-1">Mode Penanganan Bentrok <span class="text-red-500">*</span></label>
                    <div class="flex flex-col gap-1.5 p-2 bg-slate-50 border-2 border-black rounded-lg">
                        <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800">
                            <input type="radio" name="mode" value="skip" checked class="text-black focus:ring-0">
                            <span>Lewati slot bentrok (Skip - Rekomendasi)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer font-bold text-rose-700">
                            <input type="radio" name="mode" value="replace" class="text-rose-600 focus:ring-0">
                            <span>Timpa / Hapus jadwal lama di slot bentrok (Replace)</span>
                        </label>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-slate-800 font-bold mb-1">Tingkat Sasaran (Target Salin) <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 p-3 bg-amber-50 border-2 border-black rounded-lg">
                    @foreach([1, 2, 3, 4, 5, 6] as $lvl)
                        <label id="lbl_target_level_{{ $lvl }}" class="flex items-center gap-1.5 p-1.5 rounded border border-black bg-white cursor-pointer hover:bg-yellow-100 font-bold">
                            <input type="checkbox" name="target_levels[]" value="{{ $lvl }}" class="sync-target-cb rounded text-[#9B51E0] focus:ring-0">
                            <span>Kelas {{ $lvl }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Tombol Cek Preview --}}
            <div class="flex items-center justify-between pt-1">
                <button type="button" onclick="loadSyncPreview()" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 border-2 border-black rounded-lg font-black text-xs text-black shadow-[2px_2px_0px_0px_#000] flex items-center gap-1.5 cursor-pointer">
                    <span>🔍 Cek Preview Slot Jadwal</span>
                </button>
                <span id="previewLoadingNotice" class="text-xs font-bold text-purple-700 hidden animate-pulse">Memuat data preview…</span>
            </div>

            {{-- Container Preview Table --}}
            <div id="syncPreviewWrapper" class="hidden border-2 border-black rounded-lg p-3 bg-slate-50 max-h-56 overflow-y-auto">
                <div class="text-[11px] font-black uppercase text-slate-700 mb-2 flex items-center justify-between">
                    <span>Hasil Analisis Jadwal Sumber & Target:</span>
                    <span id="previewSummaryBadge" class="px-2 py-0.5 rounded bg-purple-200 border border-black font-black text-[10px]"></span>
                </div>
                <table class="w-full text-[11px] text-left border-collapse border border-black bg-white">
                    <thead class="bg-slate-200 font-bold border-b border-black">
                        <tr>
                            <th class="p-1.5 border-r border-black">Tanggal & Jam</th>
                            <th class="p-1.5 border-r border-black">Mapel Sumber</th>
                            <th class="p-1.5 border-r border-black text-center">Ke Tingkat</th>
                            <th class="p-1.5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody id="syncPreviewTbody" class="divide-y divide-black"></tbody>
                </table>
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t-2 border-black">
                <button type="button" onclick="document.getElementById('modalSalinJadwalTingkat').classList.add('hidden')" class="px-3 py-2 border-2 border-black rounded-lg text-black bg-slate-100 hover:bg-slate-200 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 border-2 border-black rounded-lg text-white bg-[#9B51E0] hover:bg-purple-600 font-black text-xs shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                    ⚡ Eksekusi Salin Sekarang
                </button>
            </div>
        </form>
    </div>
</div>
@endif
