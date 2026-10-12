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

        <!-- Kartu Pelajar Format Resmi (Sesuai Desain Baru) -->
        <div class="border-2 border-black rounded-xl bg-white shadow-[3px_3px_0px_0px_#000] overflow-hidden text-black font-sans select-none">
            <!-- Kop Sekolah -->
            <div class="px-3 pt-2.5 pb-1.5 border-b-2 border-black bg-white flex items-center gap-2.5">
                @if(!empty($setting->card_logo_url))
                    <div class="w-8 h-8 rounded-md bg-white border border-black flex items-center justify-center p-0.5 shrink-0 overflow-hidden shadow-[1px_1px_0px_#000]">
                        <img src="{{ $setting->card_logo_url }}" alt="Logo" class="w-full h-full object-contain">
                    </div>
                @else
                    <div class="w-8 h-8 rounded-md bg-[#20C997] border border-black flex items-center justify-center font-heading font-black text-xs text-black shrink-0 shadow-[1px_1px_0px_#000]">
                        🏫
                    </div>
                @endif
                <div class="min-w-0 flex-1 leading-none">
                    <div class="font-heading font-black text-xs uppercase tracking-tight truncate text-black">
                        {{ $setting->resolved_card_school_name }}
                    </div>
                    <div class="font-heading font-black text-[9px] tracking-wider uppercase mt-0.5" style="color: {{ $setting->card_theme_color ?: '#20C997' }};">
                        {{ $setting->resolved_card_title }}
                    </div>
                </div>
                <span class="text-[7.5px] font-mono font-black bg-black text-white px-1.5 py-0.5 rounded uppercase shrink-0">
                    RESMI
                </span>
            </div>

            <!-- Konten Biodata Siswa & Real QR Code -->
            <div class="px-3 py-2 flex items-center justify-between gap-2.5 bg-gradient-to-br from-white via-white to-slate-50 min-h-[120px]">
                <!-- Pas Foto Siswa -->
                <div class="w-16 h-20 bg-rose-600 border-2 border-black rounded-md flex flex-col items-center justify-center shrink-0 relative overflow-hidden shadow-[1.5px_1.5px_0px_#000]">
                    <img id="qr_preview_photo" src="" alt="Foto" class="w-full h-full object-cover hidden">
                    <div id="qr_preview_placeholder" class="flex flex-col items-center justify-center text-white">
                        <span class="text-xl">👤</span>
                        <span class="text-[6.5px] font-bold uppercase mt-0.5">3×4</span>
                    </div>
                    <span id="qr_preview_gender_badge" class="absolute bottom-0 inset-x-0 bg-black text-white text-[6.5px] font-bold text-center py-0.2 uppercase">
                        LAKI-LAKI
                    </span>
                </div>

                <!-- Data Diri Siswa -->
                <div class="flex-1 min-w-0 leading-tight space-y-0.5 text-black">
                    <div id="qr_preview_name" class="font-heading font-black text-[9.5px] sm:text-[10px] uppercase line-clamp-2 break-words leading-tight pb-0.5 text-slate-900 border-b border-slate-200">
                        Nama Siswa
                    </div>
                    <div class="grid grid-cols-[45px_auto] gap-x-1 gap-y-0.5 text-[8px] font-bold text-slate-800 leading-tight pt-0.5">
                        <span class="text-slate-600 font-semibold">NISM</span>
                        <span id="qr_preview_nis" class="font-mono text-black truncate">: -</span>
                        
                        <span class="text-slate-600 font-semibold">NISN</span>
                        <span id="qr_preview_nisn" class="font-mono text-black truncate">: -</span>

                        <span class="text-slate-600 font-semibold">LAHIR</span>
                        <span id="qr_preview_birth" class="font-mono text-black truncate">: -</span>

                        <span id="qr_preview_class" class="hidden">: -</span>
                    </div>
                </div>

                <!-- Real QR Code Scanner Absensi (Ukuran Diperkecil) -->
                <div class="flex flex-col items-center justify-center shrink-0 text-center pl-1 border-l border-slate-200">
                    <div class="bg-white p-0.5 border-1.5 border-black rounded shadow-[1px_1px_0px_#000]">
                        <img id="qr_preview_image" 
                             src="" 
                             alt="QR Code Absensi" 
                             class="w-12 h-12 object-contain">
                    </div>
                    <span class="text-[5.5px] font-mono font-bold text-slate-600 uppercase mt-0.5 tracking-tight">
                        SCAN PRESENSI
                    </span>
                </div>
            </div>

            <!-- Footer Kartu -->
            <div class="px-3 py-1 border-t-2 border-black flex items-center justify-between text-[8px] font-bold" style="background-color: {{ ($setting->card_theme_color ?: '#20C997') }}20;">
                <div class="inline-flex items-center gap-1 text-black font-black uppercase tracking-wider text-[7px] px-1.5 py-0.5 rounded-full border border-black shadow-[1px_1px_0px_#000]" style="background-color: {{ $setting->card_theme_color ?: '#20C997' }};">
                    <span>●</span>
                    <span>{{ $setting->resolved_card_validity_text }}</span>
                </div>
                <span id="qr_preview_code" class="font-mono text-[7px] text-slate-600 font-semibold">-</span>
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

<!-- Modal Tarik Siswa dari SIS-EMIS -->
<div id="syncSisModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-lg w-full p-6 space-y-5 relative">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <h3 class="font-heading font-black text-lg text-black flex items-center gap-2">
                <span>🔄</span> Sinkronisasi Siswa dari SIS-EMIS
            </h3>
            <button onclick="closeModal('syncSisModal')" class="text-black font-black text-xl hover:opacity-75 cursor-pointer">✕</button>
        </div>

        <div class="bg-[#E7F5FF] border-2 border-black p-3.5 text-xs text-blue-950 space-y-1.5 font-medium shadow-[2px_2px_0px_#000]">
            <div class="font-bold flex items-center gap-1.5 font-heading">
                <span>⚡</span> Integrasi Otomatis via API:
            </div>
            <p class="text-slate-700">
                Fitur ini akan langsung menarik data siswa resmi dari database <b>SIS-EMIS</b> tanpa perlu ekspor-impor file Excel manual.
            </p>
            <ul class="list-disc list-inside space-y-0.5 text-slate-700 pl-1">
                <li>Siswa baru otomatis dibuatkan akun pengguna dan <b>kode QR absensi unik</b>.</li>
                <li>Siswa yang sudah ada akan diperbarui biodatanya secara idempoten.</li>
                <li>Rombel baru akan otomatis dibuat jika belum ada di daftar kelas.</li>
            </ul>
        </div>

        <form id="syncSisForm" action="{{ route('admin.students.sync-sis') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1.5">
                    Pilih Kelas / Rombel yang Ingin Ditarik dari SIS-EMIS:
                </label>
                <select id="syncSisRombelSelect" name="rombel" class="w-full px-3 py-2 neo-input text-xs bg-slate-50 font-bold">
                    <option value="">-- Tarik Seluruh Siswa Semua Rombel Sekaligus --</option>
                    @php
                        $groupedClasses = $classes->sortBy(['level', 'name'])->groupBy('level');
                    @endphp
                    @foreach($groupedClasses as $level => $classList)
                        <optgroup label="Tingkat {{ $level }} (Kelas {{ $level }})">
                            @foreach($classList as $cls)
                                <option value="{{ $cls->name }}">
                                    {{ $cls->name }} ({{ $cls->students->count() ?? 0 }} Siswa Terdaftar)
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                <span class="text-[11px] text-slate-500 font-semibold block mt-1.5 leading-relaxed">
                    💡 <b>Tips:</b> Pilih kelas spesifik untuk menarik siswa rombel tersebut saja, atau pilih opsi paling atas untuk <b>menarik seluruh siswa semua kelas</b> sekaligus.
                </span>
            </div>

            <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('syncSisModal')" class="neo-btn bg-white text-black px-4 py-2 text-xs cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#A5D8FF] hover:bg-[#74c0fc] text-black px-5 py-2 text-xs font-heading flex items-center gap-1.5 cursor-pointer shadow-[2px_2px_0px_#000]">
                    <span>🚀</span> Mulai Tarik Data Siswa
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Modal Dialog Progress Bar Sinkronisasi SIS-EMIS (Neo-brutalism Real-time) -->
<!-- ========================================================================= -->
<div id="syncProgressModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs hidden transition-opacity duration-200">
    <div class="bg-white border-3 border-black shadow-[6px_6px_0px_#000] w-full max-w-xl rounded-xl overflow-hidden flex flex-col">
        <!-- Header Modal -->
        <div id="syncModalHeader" class="bg-[#FFD43B] border-b-3 border-black p-4 flex items-center justify-between transition-colors duration-200">
            <div class="flex items-center gap-2">
                <span class="text-xl" id="syncModalIcon">🚀</span>
                <h3 class="font-heading font-black text-sm text-black" id="syncModalTitle">
                    Sinkronisasi Siswa dari SIS-EMIS
                </h3>
            </div>
            <div class="flex items-center gap-2">
                <span id="syncBadgeStatus" class="neo-badge bg-black text-[#20C997] text-[10px] font-mono font-black uppercase">
                    Menghubungkan...
                </span>
                <button type="button" id="btnHeaderClose" onclick="closeSyncModal()" class="text-black hover:text-red-600 font-black text-sm px-1 cursor-pointer hidden" title="Tutup Modal">
                    ✕
                </button>
            </div>
        </div>

        <!-- Body Modal -->
        <div class="p-5 space-y-4">
            <!-- Progress Bar Section -->
            <div>
                <div class="flex items-center justify-between text-xs font-bold text-black mb-1.5">
                    <span id="syncProgressLabel" class="flex items-center gap-1.5 font-heading">
                        <span class="animate-spin inline-block" id="syncSpinner">⏳</span>
                        <span id="syncStatusText">Menghubungkan ke API server SIS-EMIS...</span>
                    </span>
                    <span id="syncPercentText" class="font-mono font-black text-sm text-black bg-slate-100 px-2 py-0.5 border border-black rounded shadow-[1px_1px_0px_#000]">
                        0%
                    </span>
                </div>

                <!-- Bar Track & Fill -->
                <div class="w-full bg-slate-100 h-5 border-2 border-black rounded-lg overflow-hidden p-0.5 relative shadow-[2px_2px_0px_#000]">
                    <div id="syncProgressBar" 
                         class="bg-[#20C997] h-full rounded transition-all duration-150 ease-out flex items-center justify-end pr-1 text-[10px] font-black text-black" 
                         style="width: 0%;">
                    </div>
                </div>

                <!-- Sub-info: Counter & Current Student -->
                <div class="flex items-center justify-between text-[11px] font-semibold text-slate-600 mt-2">
                    <div class="truncate max-w-[280px]">
                        <span class="text-slate-400">Sedang diproses:</span>
                        <span id="syncCurrentStudent" class="font-bold text-black font-mono">Menyiapkan...</span>
                    </div>
                    <div>
                        <span class="text-slate-400">Progres:</span>
                        <span id="syncCounterText" class="font-mono font-bold text-black">0 / 0 Siswa</span>
                    </div>
                </div>
            </div>

            <!-- Mini Summary Cards (Baru, Diperbarui, Total Remote) -->
            <div class="grid grid-cols-3 gap-2 pt-1">
                <div class="p-2.5 bg-emerald-50 border-2 border-black rounded-lg text-center shadow-[1px_1px_0px_#000]">
                    <span class="text-[10px] font-bold text-emerald-800 uppercase block">Siswa Baru</span>
                    <span id="syncSummaryImported" class="font-mono font-black text-base text-emerald-700">0</span>
                </div>
                <div class="p-2.5 bg-blue-50 border-2 border-black rounded-lg text-center shadow-[1px_1px_0px_#000]">
                    <span class="text-[10px] font-bold text-blue-800 uppercase block">Diperbarui</span>
                    <span id="syncSummaryUpdated" class="font-mono font-black text-base text-blue-700">0</span>
                </div>
                <div class="p-2.5 bg-amber-50 border-2 border-black rounded-lg text-center shadow-[1px_1px_0px_#000]">
                    <span class="text-[10px] font-bold text-amber-800 uppercase block">Total EMIS</span>
                    <span id="syncSummaryTotal" class="font-mono font-black text-base text-amber-700">0</span>
                </div>
            </div>

            <!-- Terminal Log Box (Black background, green text, auto-scroll) -->
            <div class="space-y-1">
                <div class="flex items-center justify-between text-[11px] font-bold text-slate-600">
                    <span class="flex items-center gap-1 font-mono">
                        <span>📟</span> Log Aktivitas Real-time:
                    </span>
                    <button type="button" onclick="clearSyncLogs()" class="text-[10px] text-slate-500 hover:text-black underline cursor-pointer">
                        Bersihkan Log
                    </button>
                </div>
                <div id="syncLogContainer" class="bg-black text-[#20C997] p-3 rounded-lg border-2 border-black font-mono text-[11px] h-36 overflow-y-auto space-y-1 shadow-inner select-text">
                    <div class="text-slate-400 italic">[Menunggu stream data dari server...]</div>
                </div>
            </div>
        </div>

        <!-- Footer Modal -->
        <div class="bg-slate-50 border-t-2 border-black p-3.5 flex items-center justify-between gap-3">
            <span id="syncFooterHint" class="text-[11px] text-slate-500 font-medium">
                Mohon jangan menutup jendela ini selama proses sinkronisasi berlangsung.
            </span>
            <div class="flex items-center gap-2">
                <button type="button" id="btnCancelSync" onclick="cancelSync()" class="neo-btn bg-slate-200 hover:bg-slate-300 text-black px-3.5 py-1.5 text-xs font-bold border border-black rounded cursor-pointer">
                    Batal
                </button>
                <button type="button" id="btnRetrySync" onclick="retrySync()" class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-4 py-1.5 text-xs font-black border-2 border-black rounded cursor-pointer shadow-[2px_2px_0px_#000] hidden">
                    🔄 Coba Lagi
                </button>
                <button type="button" id="btnCloseReloadSync" onclick="finishAndReload()" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-4 py-1.5 text-xs font-black border-2 border-black rounded cursor-pointer shadow-[2px_2px_0px_#000] hidden">
                    ✅ Selesai & Muat Ulang
                </button>
                <button type="button" id="btnSimpleClose" onclick="closeSyncModal()" class="neo-btn bg-slate-200 hover:bg-slate-300 text-black px-3.5 py-1.5 text-xs font-bold border border-black rounded cursor-pointer hidden">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

