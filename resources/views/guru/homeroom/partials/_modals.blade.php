{{-- Modal Detail Siswa & Kontak Ortu --}}
<div id="studentDetailModal" class="fixed inset-0 z-50 bg-black/60 hidden flex items-center justify-center p-4">
    <div class="bg-white neo-box-lg w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-200">
        <!-- Modal Header -->
        <div class="bg-[#5294FF] p-4 text-white border-b-2 border-black flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xl">👤</span>
                <h3 class="font-heading font-black text-base uppercase">Detail Siswa Kelas Binaan</h3>
            </div>
            <button type="button" onclick="closeStudentDetailModal()" class="font-black text-lg hover:opacity-75">✕</button>
        </div>

        <!-- Modal Body -->
        <div class="p-5 space-y-4 max-h-[80vh] overflow-y-auto">
            <!-- Profil Singkat Siswa -->
            <div class="bg-slate-50 border-2 border-black p-3 rounded-sm flex items-start gap-4">
                <div class="w-20 h-24 border-2 border-black overflow-hidden bg-white shrink-0 shadow-[2px_2px_0px_#000] flex items-center justify-center">
                    <img id="modal-student-photo" src="" alt="Foto Siswa" class="w-full h-full object-cover hidden">
                    <div id="modal-student-photo-placeholder" class="w-full h-full bg-[#5294FF] text-white font-black text-xl flex items-center justify-center">
                        -
                    </div>
                </div>
                <div class="flex-1 min-w-0 space-y-2">
                    <div class="font-heading font-black text-base text-black truncate" id="modal-student-name">Nama Siswa</div>
                    <div class="grid grid-cols-2 gap-2 text-xs font-semibold text-slate-700">
                        <div>NIS: <span class="font-mono font-bold text-black" id="modal-student-nis">-</span></div>
                        <div>NISN: <span class="font-mono font-bold text-black" id="modal-student-nisn">-</span></div>
                        <div>Jenis Kelamin: <span class="font-bold text-black" id="modal-student-gender">-</span></div>
                        <div>Tanggal Lahir: <span class="font-bold text-black" id="modal-student-birth">-</span></div>
                        <div>Kelas: <span class="font-bold text-black" id="modal-student-class">-</span></div>
                        <div>No HP Siswa: <span class="font-mono font-bold text-black" id="modal-student-phone">-</span></div>
                    </div>
                </div>
            </div>

            <!-- Data Orang Tua & Tombol WhatsApp -->
            <div class="border-2 border-black p-3 rounded-sm bg-[#FFF9DB] space-y-2">
                <div class="text-xs font-black uppercase text-black flex items-center gap-1.5">
                    <span>👨‍👩‍👧‍👦</span> Kontak Orang Tua / Wali Murid:
                </div>
                <div class="text-xs text-slate-800 space-y-1">
                    <div>Nama: <b id="modal-parent-name">-</b> (<span id="modal-parent-relation">-</span>)</div>
                    <div>No Telepon / WhatsApp: <b class="font-mono" id="modal-parent-phone">-</b></div>
                </div>
                <div class="pt-2" id="modal-wa-container">
                    <a id="modal-wa-btn" href="#" target="_blank" 
                       class="neo-btn bg-[#20C997] text-white text-xs font-bold px-3 py-2 w-full flex items-center justify-center gap-2 hover:bg-emerald-400">
                        <span>💬</span> Buka Chat WhatsApp Langsung
                    </a>
                </div>
            </div>

            <!-- Akumulasi Presensi Bulan Ini -->
            <div class="border-2 border-black p-3 rounded-sm bg-white space-y-2">
                <div class="text-xs font-black uppercase text-black flex items-center gap-1.5">
                    <span>📊</span> Kehadiran Bulan Berjalan:
                </div>
                <div class="grid grid-cols-5 gap-1.5 text-center text-xs font-mono font-bold">
                    <div class="p-1.5 bg-[#D3F9D8] border border-black rounded-sm">
                        <div class="text-[10px] text-emerald-900">Hadir</div>
                        <div class="text-sm font-black text-emerald-950" id="modal-m-hadir">0</div>
                    </div>
                    <div class="p-1.5 bg-[#D0EBFF] border border-black rounded-sm">
                        <div class="text-[10px] text-blue-900">Telat</div>
                        <div class="text-sm font-black text-blue-950" id="modal-m-terlambat">0</div>
                    </div>
                    <div class="p-1.5 bg-[#E9ECEF] border border-black rounded-sm">
                        <div class="text-[10px] text-slate-700">Izin</div>
                        <div class="text-sm font-black text-slate-900" id="modal-m-izin">0</div>
                    </div>
                    <div class="p-1.5 bg-[#FFF3BF] border border-black rounded-sm">
                        <div class="text-[10px] text-amber-900">Sakit</div>
                        <div class="text-sm font-black text-amber-950" id="modal-m-sakit">0</div>
                    </div>
                    <div class="p-1.5 bg-[#FFE3E3] border border-black rounded-sm">
                        <div class="text-[10px] text-rose-900">Alpa</div>
                        <div class="text-sm font-black text-rose-950" id="modal-m-alpa">0</div>
                    </div>
                </div>
            </div>

            <!-- Tombol Cepat Tambah Catatan -->
            <div class="pt-2">
                <button type="button" onclick="openAddNoteForStudent()" 
                        class="neo-btn bg-[#20C997] text-black text-xs font-bold px-3 py-2 w-full flex items-center justify-center gap-2 hover:bg-emerald-400">
                    <span>📌</span> Buat Catatan Khusus Untuk Siswa Ini
                </button>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-3 bg-slate-100 border-t-2 border-black flex justify-end">
            <button type="button" onclick="closeStudentDetailModal()" class="neo-btn bg-black text-white text-xs font-bold px-4 py-2 hover:bg-slate-800">
                Tutup
            </button>
        </div>
    </div>
</div>

{{-- Modal Tambah Catatan Khusus Siswa --}}
<div id="addNoteModal" class="fixed inset-0 z-50 bg-black/60 hidden flex items-center justify-center p-4">
    <div class="bg-white neo-box-lg w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-200">
        <!-- Modal Header -->
        <div class="bg-[#20C997] p-4 text-black border-b-2 border-black flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xl">📌</span>
                <h3 class="font-heading font-black text-base uppercase">Tambah Catatan Khusus Siswa</h3>
            </div>
            <button type="button" onclick="closeAddNoteModal()" class="font-black text-lg hover:opacity-75">✕</button>
        </div>

        <!-- Modal Body -->
        <form action="{{ route('guru.classes.binaan.notes.store') }}" method="POST" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-black uppercase text-black mb-1">Pilih Siswa *</label>
                <select name="student_id" id="note-student-id" required
                        class="w-full bg-white border-2 border-black px-3 py-2 text-xs font-bold text-black focus:outline-hidden">
                    <option value="">-- Pilih Siswa Kelas Binaan --</option>
                    @foreach($students as $st)
                        <option value="{{ $st->id }}">{{ $st->nis }} — {{ $st->user->name ?? '-' }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-black uppercase text-black mb-1">Tanggal *</label>
                    <input type="date" name="date" value="{{ $today }}" required
                           class="w-full bg-white border-2 border-black px-3 py-1.5 text-xs font-bold font-mono focus:outline-hidden">
                </div>
                <div>
                    <label class="block text-xs font-black uppercase text-black mb-1">Kategori *</label>
                    <select name="category" required
                            class="w-full bg-white border-2 border-black px-3 py-1.5 text-xs font-bold text-black focus:outline-hidden">
                        <option value="kedisiplinan">⚠️ Kedisiplinan / Pelanggaran</option>
                        <option value="prestasi">⭐ Prestasi & Keaktifan</option>
                        <option value="pembinaan">🤝 Pembinaan / Konseling (BK)</option>
                        <option value="kesehatan">🩺 Kesehatan / Kendala Fisik</option>
                        <option value="umum" selected>📝 Catatan Umum</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-black uppercase text-black mb-1">Judul / Topik Catatan (Opsional)</label>
                <input type="text" name="title" placeholder="Contoh: Sering Terlambat Upacara, Juara Lomba Matematika, dsb..."
                       class="w-full bg-white border-2 border-black px-3 py-1.5 text-xs font-bold text-black focus:outline-hidden">
            </div>

            <div>
                <label class="block text-xs font-black uppercase text-black mb-1">Isi Catatan Khusus *</label>
                <textarea name="content" rows="3" required placeholder="Tuliskan uraian detail kejadian, pembinaan, atau catatan wali kelas..."
                          class="w-full bg-white border-2 border-black px-3 py-2 text-xs font-medium text-black focus:outline-hidden"></textarea>
            </div>

            <div>
                <label class="block text-xs font-black uppercase text-black mb-1">Tindak Lanjut / Status Penanganan (Opsional)</label>
                <input type="text" name="follow_up" placeholder="Contoh: Sudah ditelepon ortu, Dijadwalkan konseling BK, dsb..."
                       class="w-full bg-white border-2 border-black px-3 py-1.5 text-xs font-bold text-black focus:outline-hidden">
            </div>

            <!-- Modal Footer -->
            <div class="pt-3 border-t-2 border-black/20 flex items-center justify-end gap-2">
                <button type="button" onclick="closeAddNoteModal()" class="neo-btn bg-slate-200 text-black text-xs font-bold px-4 py-2 hover:bg-slate-300">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#20C997] text-black text-xs font-black px-5 py-2 hover:bg-emerald-400">
                    💾 Simpan Catatan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Siswa Bertab (Wali Kelas) --}}
@include('partials._edit-student-modal', ['isHomeroom' => true])
