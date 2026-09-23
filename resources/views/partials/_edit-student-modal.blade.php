@php
    $isHomeroom = $isHomeroom ?? false;
@endphp

<!-- Modal Edit Siswa Bertab (Neo-Brutalism) -->
<div id="editStudentModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white neo-box-lg max-w-2xl w-full p-6 space-y-4 relative max-h-[92vh] flex flex-col">
        <!-- Header Modal -->
        <div class="flex items-center justify-between border-b-2 border-black pb-3 shrink-0">
            <div>
                <span class="neo-badge bg-[#FFD43B] text-black text-[10px]">
                    {{ $isHomeroom ? 'WALI KELAS' : 'ADMINISTRATOR' }}
                </span>
                <h3 class="font-heading font-black text-lg text-black flex items-center gap-2 mt-1">
                    <span>✏️</span> Edit Data Lengkap Siswa
                </h3>
            </div>
            <button type="button" onclick="closeModal('editStudentModal')" class="text-black font-black text-xl hover:opacity-75 cursor-pointer">✕</button>
        </div>

        <!-- Form Edit Siswa -->
        <form id="editStudentForm" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 min-h-0 space-y-4">
            @csrf
            @method('PUT')

            <!-- Navigasi 4 Tab Kategori Data Siswa -->
            <div class="border-b-2 border-black flex items-center gap-1.5 overflow-x-auto pb-1 shrink-0">
                <button type="button" onclick="switchEditTab('identitas')" id="editTabBtn-identitas"
                        class="edit-tab-btn neo-btn bg-black text-white px-3 py-2 text-[11px] font-heading font-black uppercase flex items-center gap-1.5 shadow-[2px_2px_0px_#FFD43B] shrink-0">
                    <span>👤</span> 1. Identitas & Foto
                </button>
                <button type="button" onclick="switchEditTab('kontak')" id="editTabBtn-kontak"
                        class="edit-tab-btn neo-btn bg-white hover:bg-slate-100 text-black px-3 py-2 text-[11px] font-heading font-bold uppercase flex items-center gap-1.5 shrink-0">
                    <span>📍</span> 2. Kontak & Alamat
                </button>
                <button type="button" onclick="switchEditTab('riwayat')" id="editTabBtn-riwayat"
                        class="edit-tab-btn neo-btn bg-white hover:bg-slate-100 text-black px-3 py-2 text-[11px] font-heading font-bold uppercase flex items-center gap-1.5 shrink-0">
                    <span>🎓</span> 3. Riwayat Masuk
                </button>
                <button type="button" onclick="switchEditTab('ortu')" id="editTabBtn-ortu"
                        class="edit-tab-btn neo-btn bg-white hover:bg-slate-100 text-black px-3 py-2 text-[11px] font-heading font-bold uppercase flex items-center gap-1.5 shrink-0">
                    <span>👨‍👩‍👦</span> 4. Orang Tua & Wali
                </button>
            </div>

            <!-- Konten Tab (Scrollable) -->
            <div class="flex-1 overflow-y-auto pr-1 space-y-4">
                
                <!-- ========================================== -->
                <!-- TAB 1: IDENTITAS & FOTO                    -->
                <!-- ========================================== -->
                <div id="editTabPane-identitas" class="edit-tab-pane space-y-4">
                    <div>
                        <label class="block font-heading font-bold text-xs text-black mb-1">Nama Lengkap Siswa *</label>
                        <input type="text" id="edit_stu_name" name="name" required class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-bold">
                    </div>

                    <!-- Upload Foto Siswa -->
                    <div class="border-2 border-black bg-slate-50 p-3 neo-box space-y-2">
                        <label class="block font-heading font-bold text-xs text-black">Foto Siswa (Format 3×4)</label>
                        <div class="flex items-start gap-3">
                            <div id="edit_photo_preview_container" class="w-14 h-16 border-2 border-black bg-white flex items-center justify-center overflow-hidden shrink-0 shadow-[2px_2px_0px_0px_#000]">
                                <span id="edit_photo_placeholder" class="text-[9px] text-slate-400 font-bold uppercase text-center leading-tight">Foto<br>3×4</span>
                                <img id="edit_photo_preview" src="" alt="Foto Siswa" class="w-full h-full object-cover hidden">
                            </div>
                            <div class="flex-1 space-y-1.5">
                                <input type="file" name="photo" id="edit_photo_input" accept="image/jpeg,image/png,image/jpg,image/webp" 
                                       class="w-full text-xs file:mr-3 file:py-1.5 file:px-3 file:border-2 file:border-black file:text-xs file:font-heading file:font-bold file:bg-[#FFD43B] file:text-black hover:file:bg-[#fcc419] cursor-pointer" 
                                       onchange="previewImage(this, 'edit_photo_preview', 'edit_photo_placeholder')">
                                <div id="remove_photo_wrapper" class="hidden items-center gap-2 pt-0.5">
                                    <label class="inline-flex items-center gap-1.5 text-xs font-bold text-red-600 cursor-pointer">
                                        <input type="checkbox" name="remove_photo" id="edit_remove_photo" value="1" class="accent-red-600 w-3.5 h-3.5">
                                        Hapus foto siswa saat ini
                                    </label>
                                </div>
                                <span class="text-[11px] text-slate-500 font-semibold block">Format: JPG, PNG, WEBP. Maksimal 2MB.</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-heading font-bold text-xs text-black mb-1">NIS (Nomor Induk Siswa) *</label>
                            <input type="text" id="edit_stu_nis" name="nis" required class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-mono font-bold">
                        </div>
                        <div>
                            <label class="block font-heading font-bold text-xs text-black mb-1">NISN (Nomor Induk Siswa Nasional)</label>
                            <input type="text" id="edit_stu_nisn" name="nisn" placeholder="00..." class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-heading font-bold text-xs text-black mb-1">
                                Kelas * @if($isHomeroom)<span class="text-amber-600 font-normal">(Terkunci untuk Wali Kelas)</span>@endif
                            </label>
                            @if($isHomeroom)
                                <input type="text" id="edit_stu_class_name" readonly class="w-full px-3 py-2 neo-input text-sm bg-slate-200 text-slate-700 font-bold cursor-not-allowed">
                                <input type="hidden" id="edit_stu_class" name="school_class_id">
                            @else
                                <select id="edit_stu_class" name="school_class_id" required class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-bold">
                                    @if(isset($classes))
                                        @foreach($classes as $cls)
                                            <option value="{{ $cls->id }}">{{ $cls->name }} ({{ $cls->level }})</option>
                                        @endforeach
                                    @endif
                                </select>
                            @endif
                        </div>
                        <div>
                            <label class="block font-heading font-bold text-xs text-black mb-1">Jenis Kelamin *</label>
                            <select id="edit_stu_gender" name="gender" required class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-bold">
                                <option value="L">Laki-laki (L)</option>
                                <option value="P">Perempuan (P)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block font-heading font-bold text-xs text-black mb-1">Tempat Lahir</label>
                            <input type="text" id="edit_stu_birth_place" name="birth_place" placeholder="Contoh: Jakarta" class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
                        </div>
                        <div>
                            <label class="block font-heading font-bold text-xs text-black mb-1">Tanggal Lahir</label>
                            <input type="date" id="edit_stu_birth" name="birth_date" class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-medium">
                        </div>
                        <div>
                            <label class="block font-heading font-bold text-xs text-black mb-1">Agama</label>
                            <select id="edit_stu_religion" name="religion" class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-medium">
                                <option value="">-- Pilih Agama --</option>
                                @foreach(['Islam', 'Kristen Protestan', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu'] as $rel)
                                    <option value="{{ $rel }}">{{ $rel }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- TAB 2: KONTAK & ALAMAT                     -->
                <!-- ========================================== -->
                <div id="editTabPane-kontak" class="edit-tab-pane hidden space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-heading font-bold text-xs text-black mb-1">Nomor HP / WhatsApp Siswa</label>
                            <input type="text" id="edit_stu_phone" name="phone" placeholder="08..." class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-mono">
                        </div>
                        <div>
                            <label class="block font-heading font-bold text-xs text-black mb-1">Alamat Email Siswa</label>
                            <input type="email" id="edit_stu_email" name="email" placeholder="nama@siswa.sekolah.sch.id" class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="block font-heading font-bold text-xs text-black mb-1">
                            Password Akun Siswa Baru <span class="text-slate-500 font-normal">(Kosongkan jika tidak ingin mengubah password)</span>
                        </label>
                        <input type="password" id="edit_stu_password" name="password" placeholder="Masukkan password baru (minimal 6 karakter)" class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
                        <span class="text-[11px] text-slate-500 font-semibold block mt-1">
                            💡 Siswa juga dapat di-reset password-nya ke NIS/NISN melalui tombol kunci (🔑) di tabel.
                        </span>
                    </div>

                    <div>
                        <label class="block font-heading font-bold text-xs text-black mb-1">Alamat Lengkap Tempat Tinggal Siswa</label>
                        <textarea id="edit_stu_address" name="address" rows="3" placeholder="Jl. Raya No..., RT/RW, Kelurahan, Kecamatan, Kota/Kabupaten" class="w-full px-3 py-2 neo-input text-sm bg-slate-50"></textarea>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- TAB 3: STATUS & RIWAYAT MASUK              -->
                <!-- ========================================== -->
                <div id="editTabPane-riwayat" class="edit-tab-pane hidden space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-heading font-bold text-xs text-black mb-1">Status dalam Keluarga</label>
                            <input type="text" id="edit_stu_family_status" name="family_status" placeholder="Contoh: Anak Kandung, Anak Angkat" class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
                        </div>
                        <div>
                            <label class="block font-heading font-bold text-xs text-black mb-1">Anak Ke-</label>
                            <input type="text" id="edit_stu_child_number" name="child_number" placeholder="Contoh: 1, 2, dst." class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="block font-heading font-bold text-xs text-black mb-1">Sekolah Asal</label>
                        <input type="text" id="edit_stu_previous_school" name="previous_school" placeholder="Contoh: SD Negeri 01 Pagi" class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-heading font-bold text-xs text-black mb-1">Tanggal Diterima di Sekolah</label>
                            <input type="date" id="edit_stu_admission_date" name="admission_date" class="w-full px-3 py-2 neo-input text-sm bg-slate-50 font-medium">
                        </div>
                        <div>
                            <label class="block font-heading font-bold text-xs text-black mb-1">Diterima di Kelas / Tingkat Awal</label>
                            <input type="text" id="edit_stu_entry_grade" name="entry_grade" placeholder="Contoh: VII, X, 1" class="w-full px-3 py-2 neo-input text-sm bg-slate-50">
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- TAB 4: ORANG TUA & WALI                    -->
                <!-- ========================================== -->
                <div id="editTabPane-ortu" class="edit-tab-pane hidden space-y-4">
                    <!-- Data Ayah & Ibu -->
                    <div class="border-2 border-black bg-slate-50 p-3 neo-box space-y-3">
                        <div class="font-heading font-black text-xs uppercase text-black flex items-center gap-1.5">
                            <span>👨‍👩‍👧</span> Identitas Orang Tua Kandung
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-heading font-bold text-xs text-black mb-1">Nama Lengkap Ayah</label>
                                <input type="text" id="edit_stu_father_name" name="father_name" placeholder="Nama Ayah" class="w-full px-3 py-2 neo-input text-sm bg-white font-medium">
                            </div>
                            <div>
                                <label class="block font-heading font-bold text-xs text-black mb-1">Pekerjaan Ayah</label>
                                <input type="text" id="edit_stu_father_job" name="father_job" placeholder="PNS, Wiraswasta, Buruh, dll." class="w-full px-3 py-2 neo-input text-sm bg-white">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-heading font-bold text-xs text-black mb-1">Nama Lengkap Ibu</label>
                                <input type="text" id="edit_stu_mother_name" name="mother_name" placeholder="Nama Ibu" class="w-full px-3 py-2 neo-input text-sm bg-white font-medium">
                            </div>
                            <div>
                                <label class="block font-heading font-bold text-xs text-black mb-1">Pekerjaan Ibu</label>
                                <input type="text" id="edit_stu_mother_job" name="mother_job" placeholder="Ibu Rumah Tangga, Guru, dll." class="w-full px-3 py-2 neo-input text-sm bg-white">
                            </div>
                        </div>

                        <div>
                            <label class="block font-heading font-bold text-xs text-black mb-1">Alamat Lengkap Orang Tua</label>
                            <textarea id="edit_stu_parent_address" name="parent_address" rows="2" placeholder="Kosongkan jika sama dengan alamat siswa" class="w-full px-3 py-2 neo-input text-sm bg-white"></textarea>
                        </div>
                    </div>

                    <!-- Data Wali Murid (Jika ada) -->
                    <div class="border-2 border-black bg-slate-50 p-3 neo-box space-y-3">
                        <div class="font-heading font-black text-xs uppercase text-black flex items-center gap-1.5">
                            <span>🛡️</span> Identitas Wali Murid (Opsional)
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-heading font-bold text-xs text-black mb-1">Nama Lengkap Wali</label>
                                <input type="text" id="edit_stu_guardian_name" name="guardian_name" placeholder="Nama Wali Siswa" class="w-full px-3 py-2 neo-input text-sm bg-white font-medium">
                            </div>
                            <div>
                                <label class="block font-heading font-bold text-xs text-black mb-1">Pekerjaan Wali</label>
                                <input type="text" id="edit_stu_guardian_job" name="guardian_job" placeholder="Pekerjaan Wali" class="w-full px-3 py-2 neo-input text-sm bg-white">
                            </div>
                        </div>

                        <div>
                            <label class="block font-heading font-bold text-xs text-black mb-1">Alamat Lengkap Wali</label>
                            <textarea id="edit_stu_guardian_address" name="guardian_address" rows="2" placeholder="Alamat tinggal wali murid" class="w-full px-3 py-2 neo-input text-sm bg-white"></textarea>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer / Action Buttons -->
            <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-between shrink-0">
                <button type="button" onclick="closeModal('editStudentModal')" class="neo-btn bg-white hover:bg-slate-100 text-black px-4 py-2 text-xs font-bold cursor-pointer">
                    Batal
                </button>
                <div class="flex items-center gap-2">
                    <button type="submit" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-6 py-2.5 text-xs font-heading font-black uppercase tracking-wider cursor-pointer shadow-[3px_3px_0px_0px_#000]">
                        💾 Simpan Perubahan Siswa
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
