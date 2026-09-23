    <!-- Modal Upload Excel Siswa -->
<div id="importStudentModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-lg w-full p-6 space-y-5 relative">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>📑</span> Import Data Siswa dari Excel
            </h3>
            <button onclick="closeModal('importStudentModal')" class="text-black font-black text-xl hover:opacity-75 cursor-pointer">✕</button>
        </div>

        <div class="bg-[#E7F5FF] border-2 border-black p-3.5 text-xs text-blue-950 space-y-1.5 font-medium shadow-[2px_2px_0px_#000]">
            <div class="font-bold flex items-center gap-1.5 font-heading">
                <span>💡</span> Petunjuk Pengisian File:
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-slate-700 pl-1">
                <li>Gunakan template resmi 24 kolom standar (No, NIS, NISN, Nama, JK, Tgl Lahir, dsb).</li>
                <li>Kolom <b>NAMA</b> dan <b>NIS</b> wajib terisi pada setiap baris data.</li>
                <li>Jika kolom <b>Password</b> kosong, sistem akan otomatis menggunakan NIS sebagai kata sandi.</li>
                <li>Format file yang didukung: <b>.xlsx, .xls, .csv</b> (Maksimal 5MB).</li>
            </ul>
            <div class="pt-1">
                <a href="{{ route('admin.students.template') }}" class="inline-flex items-center gap-1.5 text-blue-700 font-bold underline hover:text-blue-900">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Unduh file template siswa standar (.xlsx)
                </a>
            </div>
        </div>

        <form action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1.5">
                    Pilih Kelas Tujuan / Default <span class="text-slate-500 font-normal">(opsional jika di Excel sudah ada kolom Kelas)</span>
                </label>
                <select name="school_class_id" class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-bold">
                    <option value="">-- Otomatis Dari Kolom Kelas di Excel --</option>
                    @foreach($classes as $cls)
                        <option value="{{ $cls->id }}" {{ $selectedClassId == $cls->id ? 'selected' : '' }}>
                            {{ $cls->name }} ({{ $cls->level }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1.5">Pilih File Excel / CSV *</label>
                <div class="border-2 border-dashed border-black rounded-lg p-5 bg-slate-50 text-center hover:bg-slate-100 transition-all cursor-pointer relative">
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="absolute inset-0 opacity-0 cursor-pointer w-full h-full" onchange="document.getElementById('fileNameDisplayStudents').textContent = this.files[0] ? this.files[0].name : 'Belum ada file dipilih'">
                    <div class="space-y-1.5 pointer-events-none">
                        <div class="text-3xl">📊</div>
                        <div class="text-xs font-bold text-black font-heading">Tarik & Lepas File ke Sini atau Klik untuk Memilih</div>
                        <div id="fileNameDisplayStudents" class="text-[11px] font-mono text-slate-500 font-semibold truncate max-w-xs mx-auto">
                            Format .xlsx, .xls, .csv (Maks 5MB)
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-[#FFF9DB] border-2 border-black p-3 flex items-start gap-2.5">
                <input type="checkbox" name="upsert" id="student_upsert_check" value="1" checked class="mt-0.5 border-2 border-black text-black focus:ring-0 cursor-pointer">
                <label for="student_upsert_check" class="text-xs font-semibold text-black cursor-pointer leading-tight">
                    <span class="font-bold block">Perbarui data jika NIS sudah terdaftar (Upsert)</span>
                    <span class="text-[11px] text-slate-600 block mt-0.5">Jika dicentang, siswa dengan NIS yang sama akan diperbarui biodatanya, bukan dilewati.</span>
                </label>
            </div>

            <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('importStudentModal')" class="neo-btn bg-white text-black px-4 py-2 text-xs cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-5 py-2 text-xs font-heading flex items-center gap-1.5 cursor-pointer shadow-[2px_2px_0px_#000]">
                    <span>🚀</span> Upload & Proses Data Siswa
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Siswa -->
<div id="createStudentModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-lg w-full p-6 space-y-5 relative max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>➕</span> Tambah Siswa Baru
            </h3>
            <button onclick="closeModal('createStudentModal')" class="text-black font-black text-xl hover:opacity-75">✕</button>
        </div>

        <form action="{{ route('admin.students.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Nama Lengkap Siswa *</label>
                <input type="text" name="name" required placeholder="Contoh: Muhammad Rizky" class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
            </div>

            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Foto Siswa (Opsional)</label>
                <div class="flex items-center gap-3">
                    <div id="create_photo_preview_container" class="w-14 h-16 border-2 border-black bg-slate-100 flex items-center justify-center overflow-hidden shrink-0 shadow-[2px_2px_0px_0px_#000]">
                        <span id="create_photo_placeholder" class="text-[10px] text-slate-400 font-bold uppercase">Foto 3×4</span>
                        <img id="create_photo_preview" src="" alt="Preview" class="w-full h-full object-cover hidden">
                    </div>
                    <div class="flex-1">
                        <input type="file" name="photo" id="create_photo_input" accept="image/jpeg,image/png,image/jpg,image/webp" class="w-full text-xs file:mr-3 file:py-1.5 file:px-3 file:border-2 file:border-black file:text-xs file:font-heading file:font-bold file:bg-[#FFD43B] file:text-black hover:file:bg-[#fcc419] cursor-pointer" onchange="previewImage(this, 'create_photo_preview', 'create_photo_placeholder')">
                        <span class="text-[11px] text-slate-500 font-semibold block mt-1">Format: JPG, PNG, WEBP. Maksimal 2MB.</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">NIS (Nomor Induk Siswa) *</label>
                    <input type="text" name="nis" required placeholder="1234..." class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
                </div>
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">NISN (Opsional)</label>
                    <input type="text" name="nisn" placeholder="0081..." class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">Pilih Kelas *</label>
                    <select name="school_class_id" required class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-medium">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($classes as $cls)
                            <option value="{{ $cls->id }}">{{ $cls->name }} ({{ $cls->level }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">Jenis Kelamin *</label>
                    <select name="gender" required class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-medium">
                        <option value="L">Laki-laki (L)</option>
                        <option value="P">Perempuan (P)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">Tanggal Lahir</label>
                    <input type="date" name="birth_date" class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-medium">
                </div>
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">No. Kontak Siswa</label>
                    <input type="text" name="phone" placeholder="08..." class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
                </div>
            </div>

            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Alamat Email Login (Opsional)</label>
                <input type="email" name="email" placeholder="Kosongkan untuk otomatis dibuat dari NIS" class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
                <span class="text-[11px] text-slate-500 font-semibold mt-0.5 block">
                    Password akun otomatis diset: <code>password</code>
                </span>
            </div>

            <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('createStudentModal')" class="neo-btn bg-white text-black px-4 py-2 text-xs">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#20C997] text-black px-5 py-2 text-xs font-heading">
                    Simpan & Generate QR
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Siswa Bertab -->
@include('partials._edit-student-modal', ['isHomeroom' => false, 'classes' => $classes])

<!-- Modal Preview Kartu QR Siswa -->
<div id="qrPreviewModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-md w-full p-6 space-y-4 relative max-h-[95vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b-2 border-black pb-2">
            <span class="neo-badge bg-[#FFD43B] text-black flex items-center gap-1.5">
                <span>🪪</span> PRATINJAU KARTU PELAJAR & QR
            </span>
            <button onclick="closeModal('qrPreviewModal')" class="text-black font-black text-xl hover:opacity-75 cursor-pointer">✕</button>
        </div>

        <!-- Kartu Pelajar Format Resmi (Diambil dari /admin/students/cards) -->
        <div class="border-2 border-black rounded bg-white shadow-[3px_3px_0px_0px_#000] overflow-hidden text-black font-sans select-none">
            <!-- Kop Sekolah -->
            <div class="bg-slate-900 text-white px-2.5 py-1.5 flex items-center justify-between border-b-2 border-black">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-[#FFD43B] border border-black flex items-center justify-center font-black text-xs text-black font-heading shrink-0">
                        🎓
                    </div>
                    <div class="leading-tight truncate">
                        <div class="font-heading font-black text-[11px] uppercase tracking-tight truncate text-[#FFD43B]">
                            {{ $setting->school_name ?? 'SMP NEGERI 1 GARUDA' }}
                        </div>
                        <div class="text-[8px] font-mono text-slate-300">
                            NPSN: {{ $setting->npsn ?? '-' }} • {{ $setting->level ?? 'SMP' }}
                        </div>
                    </div>
                </div>
                <span class="text-[8px] font-mono font-bold bg-[#20C997] text-black px-1.5 py-0.5 rounded border border-black uppercase shrink-0">
                    RESMI
                </span>
            </div>

            <!-- Pita Judul Kartu -->
            <div class="bg-[#FFD43B] text-black border-b border-black text-center py-0.5 font-heading font-black text-[9px] uppercase tracking-widest">
                KARTU TANDA PELAJAR & PRESENSI
            </div>

            <!-- Badan Kartu: Foto, Identitas, & Real QR Code dari /admin/students/cards -->
            <div class="p-3 flex items-center justify-between gap-2.5 bg-gradient-to-br from-white to-slate-50 min-h-[120px]">
                <!-- Foto Siswa -->
                <div class="w-16 h-20 bg-slate-100 border-2 border-black rounded shadow-[1.5px_1.5px_0px_#000] flex flex-col items-center justify-center shrink-0 relative overflow-hidden">
                    <img id="qr_preview_photo" src="" alt="Foto" class="w-full h-full object-cover hidden">
                    <div id="qr_preview_placeholder" class="flex flex-col items-center justify-center">
                        <span class="text-xl">👤</span>
                        <span class="text-[7px] font-bold text-slate-500 uppercase mt-0.5">FOTO 3×4</span>
                    </div>
                    <span id="qr_preview_gender_badge" class="absolute bottom-0 inset-x-0 bg-black text-white text-[7px] font-bold text-center py-0.2 uppercase">
                        LAKI-LAKI
                    </span>
                </div>

                <!-- Data Diri Siswa -->
                <div class="flex-1 min-w-0 space-y-0.5 text-[10px]">
                    <div id="qr_preview_name" class="font-heading font-black text-xs text-black truncate leading-snug">
                        Nama Siswa
                    </div>
                    <div class="grid grid-cols-3 gap-0.5 font-medium text-[9px] text-slate-700">
                        <span class="text-slate-500">NIS</span>
                        <span id="qr_preview_nis" class="col-span-2 font-mono font-bold text-black">: -</span>
                        
                        <span class="text-slate-500">NISN</span>
                        <span id="qr_preview_nisn" class="col-span-2 font-mono font-bold text-black">: -</span>

                        <span class="text-slate-500">Kelas</span>
                        <span id="qr_preview_class" class="col-span-2 font-bold text-black">: -</span>

                        <span class="text-slate-500">Lahir</span>
                        <span id="qr_preview_birth" class="col-span-2 font-mono text-black">: -</span>
                    </div>
                </div>

                <!-- Real QR Code Scanner Absensi (Diambil dari /admin/students/cards) -->
                <div class="flex flex-col items-center justify-center shrink-0">
                    <div class="bg-white p-1.5 border-2 border-black rounded shadow-[1.5px_1.5px_0px_#000]">
                        <img id="qr_preview_image" 
                             src="" 
                             alt="QR Code Absensi" 
                             class="w-16 h-16 object-contain">
                    </div>
                    <span class="text-[7px] font-black uppercase text-black mt-0.5 tracking-wider">
                        SCAN PRESENSI
                    </span>
                </div>
            </div>

            <!-- Footer Kartu -->
            <div class="bg-slate-100 px-2.5 py-1 border-t border-black flex items-center justify-between text-[8px] font-medium text-slate-600">
                <span>Berlaku Selama Menjadi Siswa Aktif</span>
                <span id="qr_preview_code" class="font-mono font-bold text-black">-</span>
            </div>
        </div>

        <div class="flex items-center justify-between pt-2 border-t border-slate-200">
            <a href="{{ route('admin.students.cards') }}" class="text-xs font-bold text-blue-700 hover:underline flex items-center gap-1">
                <span>🪪</span> Buka Studio Kartu
            </a>
            <div class="flex items-center gap-2">
                <a id="qr_print_single_btn" href="#" target="_blank" class="neo-btn bg-[#20C997] hover:bg-[#1bb386] text-black px-4 py-2 text-xs font-heading font-bold flex items-center gap-1.5 cursor-pointer shadow-[2px_2px_0px_0px_#000]">
                    <span>🖨️</span> Cetak Kartu Pelajar
                </a>
                <button onclick="closeModal('qrPreviewModal')" class="neo-btn bg-white text-black px-4 py-2 text-xs cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
