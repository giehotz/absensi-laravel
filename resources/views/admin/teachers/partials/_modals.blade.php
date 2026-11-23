<!-- Modal 1: Import Excel Guru (Format 9 Kolom) -->
<div id="importTeacherModal"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm hidden"
     onclick="if (event.target === this) closeModal('importTeacherModal')">
    <div class="relative max-h-[90dvh] w-full max-w-lg overflow-y-auto rounded-xl border-2 border-black bg-white shadow-[8px_8px_0px_0px_#000]">
        <!-- Header -->
        <div class="flex items-start justify-between border-b-2 border-black bg-[#FFD43B] px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg border-2 border-black bg-white text-black shadow-[2px_2px_0px_0px_#000]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-black text-black">Import Data Guru dari Excel</h3>
                    <p class="text-xs font-semibold text-black/75">Unggah spreadsheet sesuai format resmi 9 kolom.</p>
                </div>
            </div>
            <button type="button"
                    onclick="closeModal('importTeacherModal')"
                    class="cursor-pointer rounded-lg border-2 border-black bg-white p-1.5 text-black shadow-[2px_2px_0px_0px_#000] transition-all hover:bg-[#FF6B6B] hover:text-white active:translate-x-0.5 active:translate-y-0.5 active:shadow-none"
                    aria-label="Tutup modal">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form action="{{ route('admin.teachers.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-5 px-6 py-5">
                <!-- Info Box -->
                <div class="space-y-2.5 rounded-xl border-2 border-black bg-[#E7F5FF] p-4 text-xs text-black shadow-[3px_3px_0px_0px_#000]">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="font-black uppercase tracking-wide">Format Kolom Excel (9 Kolom)</span>
                        <a href="{{ route('admin.teachers.template') }}"
                           class="inline-flex items-center gap-1 rounded border border-black bg-white px-2 py-0.5 text-[11px] font-extrabold text-black shadow-[1px_1px_0px_0px_#000] transition hover:bg-[#FFD43B]">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Unduh Template (.xlsx)
                        </a>
                    </div>
                    <div class="overflow-x-auto rounded-lg border-2 border-black bg-white px-3 py-2 font-mono text-[11px] font-bold text-black whitespace-nowrap">
                        No | NUPTK | NIP | NAMA | JENIS KELAMIN | Tempat Lahir | Tgl Lahir (dd-mm-yyyy) | Pendidikan Terakhir | Password
                    </div>
                    <ul class="list-disc space-y-1 pl-4 font-medium text-slate-800">
                        <li><strong class="font-extrabold text-black">Email tidak perlu diisi:</strong> Guru melengkapi email secara mandiri di akun mereka.</li>
                        <li>Kolom <strong class="font-extrabold text-black">Password</strong> opsional (default otomatis: <code class="rounded border border-black bg-white px-1 py-0.5 text-[11px] font-bold">password</code>).</li>
                        <li>Format tanggal lahir: <code class="rounded border border-black bg-white px-1 py-0.5 text-[11px] font-bold">dd-mm-yyyy</code> (contoh: <code class="rounded border border-black bg-white px-1 py-0.5 text-[11px] font-bold">14-05-1985</code>).</li>
                        <li>Jenis Kelamin: <code class="rounded border border-black bg-white px-1 py-0.5 text-[11px] font-bold">L</code> (Laki-laki) atau <code class="rounded border border-black bg-white px-1 py-0.5 text-[11px] font-bold">P</code> (Perempuan).</li>
                    </ul>
                </div>

                <!-- File Upload Dropzone -->
                <div>
                    <label class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">
                        Pilih File Excel / CSV <span class="text-rose-600">*</span>
                    </label>
                    <div class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-black bg-[#FFFDF5] px-6 py-7 text-center transition hover:bg-[#FFF9DB]">
                        <input type="file"
                               name="file"
                               accept=".xlsx,.xls,.csv"
                               required
                               class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                               onchange="document.getElementById('fileNameDisplay').textContent = this.files[0] ? this.files[0].name : 'Format .xlsx, .xls, .csv (Maks 5MB)'">
                        <div class="pointer-events-none space-y-2">
                            <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-lg border-2 border-black bg-[#FFD43B] text-black shadow-[2px_2px_0px_0px_#000]">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="text-xs font-extrabold text-black sm:text-sm">
                                Tarik & lepas file ke sini, atau klik untuk memilih
                            </div>
                            <div id="fileNameDisplay" class="mx-auto max-w-xs truncate font-mono text-[11px] font-semibold text-slate-600">
                                Format .xlsx, .xls, .csv (Maks 5MB)
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-2.5 border-t-2 border-black bg-slate-50 px-6 py-4">
                <button type="button"
                        onclick="closeModal('importTeacherModal')"
                        class="cursor-pointer rounded-lg border-2 border-black bg-white px-4 py-2 text-xs font-extrabold uppercase text-black shadow-[2px_2px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-slate-100 hover:shadow-[3px_3px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                    Batal
                </button>
                <button type="submit"
                        class="cursor-pointer rounded-lg border-2 border-black bg-[#FFD43B] px-5 py-2 text-xs font-black uppercase text-black shadow-[3px_3px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-[#FCC419] hover:shadow-[4px_4px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                    Upload & Proses Data
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Tambah Guru Baru -->
<div id="createTeacherModal"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm hidden"
     onclick="if (event.target === this) closeModal('createTeacherModal')">
    <div class="relative flex max-h-[90dvh] w-full max-w-xl flex-col overflow-hidden rounded-xl border-2 border-black bg-white shadow-[8px_8px_0px_0px_#000]">
        <!-- Header -->
        <div class="flex items-start justify-between border-b-2 border-black bg-[#20C997] px-6 py-4">
            <div>
                <h3 class="text-base font-black text-black">Tambah Guru Baru</h3>
                <p class="mt-0.5 text-xs font-semibold text-black/75">Lengkapi biodata dan informasi akun login guru.</p>
            </div>
            <button type="button"
                    onclick="closeModal('createTeacherModal')"
                    class="cursor-pointer rounded-lg border-2 border-black bg-white p-1.5 text-black shadow-[2px_2px_0px_0px_#000] transition-all hover:bg-[#FF6B6B] hover:text-white active:translate-x-0.5 active:translate-y-0.5 active:shadow-none"
                    aria-label="Tutup modal">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form action="{{ route('admin.teachers.store') }}" method="POST" class="flex min-h-0 flex-1 flex-col">
            @csrf
            <div class="flex-1 space-y-4 overflow-y-auto px-6 py-5">
                <div>
                    <label class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">
                        Nama Lengkap & Gelar <span class="text-rose-600">*</span>
                    </label>
                    <input type="text"
                           name="name"
                           required
                           placeholder="Contoh: Ahmad Dahlan, S.Pd., M.Pd."
                           class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">NUPTK (Opsional)</label>
                        <input type="text"
                               name="nuptk"
                               placeholder="16 digit NUPTK"
                               class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 font-mono text-sm font-semibold text-black placeholder:font-sans placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">NIP (Opsional)</label>
                        <input type="text"
                               name="nip"
                               placeholder="18 digit NIP"
                               class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 font-mono text-sm font-semibold text-black placeholder:font-sans placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">
                            Jenis Kelamin <span class="text-rose-600">*</span>
                        </label>
                        <select name="gender"
                                required
                                class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:outline-none">
                            <option value="L">Laki-laki (L)</option>
                            <option value="P">Perempuan (P)</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">Pendidikan Terakhir</label>
                        <select name="last_education"
                                class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:outline-none">
                            <option value="">-- Pilih Jenjang --</option>
                            <option value="D3">D3</option>
                            <option value="D4/S1" selected>D4 / S1</option>
                            <option value="S2">S2</option>
                            <option value="S3">S3</option>
                            <option value="SMA/SMK">SMA / SMK / Sederajat</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">Tempat Lahir</label>
                        <input type="text"
                               name="birth_place"
                               placeholder="Kota / Kabupaten"
                               class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">Tanggal Lahir</label>
                        <input type="date"
                               name="birth_date"
                               class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 font-mono text-sm font-semibold text-black shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">No. WhatsApp / HP</label>
                        <input type="text"
                               name="phone"
                               placeholder="081234567890"
                               class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 font-mono text-sm font-semibold text-black placeholder:font-sans placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">Kata Sandi (Opsional)</label>
                        <div class="relative">
                            <input type="password"
                                   id="create_password"
                                   name="password"
                                   placeholder="Default: password"
                                   class="w-full rounded-lg border-2 border-black bg-white py-2 pl-3.5 pr-10 font-mono text-sm font-semibold text-black placeholder:font-sans placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                            <button type="button"
                                    onclick="togglePasswordVisibility('create_password', 'create_eye_icon')"
                                    class="absolute inset-y-0 right-0 flex cursor-pointer items-center pr-3 text-slate-600 transition hover:text-black focus:outline-none"
                                    title="Tampilkan / Sembunyikan Password">
                                <span id="create_eye_icon">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex items-start gap-2.5 rounded-lg border-2 border-black bg-[#FFF9DB] p-3 text-xs font-medium text-black shadow-[2px_2px_0px_0px_#000]">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-black" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>
                        <strong class="font-black">Catatan Email:</strong> Email tidak diwajibkan di sini. Guru dapat melengkapi email pribadi secara mandiri saat login pertama kali.
                    </span>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-2.5 border-t-2 border-black bg-slate-50 px-6 py-4">
                <button type="button"
                        onclick="closeModal('createTeacherModal')"
                        class="cursor-pointer rounded-lg border-2 border-black bg-white px-4 py-2 text-xs font-extrabold uppercase text-black shadow-[2px_2px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-slate-100 hover:shadow-[3px_3px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                    Batal
                </button>
                <button type="submit"
                        class="cursor-pointer rounded-lg border-2 border-black bg-[#20C997] px-5 py-2 text-xs font-black uppercase text-black shadow-[3px_3px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-[#12B886] hover:shadow-[4px_4px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                    Simpan Data Guru
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Edit Data Guru -->
<div id="editTeacherModal"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm hidden"
     onclick="if (event.target === this) closeModal('editTeacherModal')">
    <div class="relative flex max-h-[90dvh] w-full max-w-xl flex-col overflow-hidden rounded-xl border-2 border-black bg-white shadow-[8px_8px_0px_0px_#000]">
        <!-- Header -->
        <div class="flex items-start justify-between border-b-2 border-black bg-[#A5D8FF] px-6 py-4">
            <div>
                <h3 class="text-base font-black text-black">Edit Data Guru</h3>
                <p class="mt-0.5 text-xs font-semibold text-black/75">Perbarui biodata, kontak, atau atur ulang kata sandi akun.</p>
            </div>
            <button type="button"
                    onclick="closeModal('editTeacherModal')"
                    class="cursor-pointer rounded-lg border-2 border-black bg-white p-1.5 text-black shadow-[2px_2px_0px_0px_#000] transition-all hover:bg-[#FF6B6B] hover:text-white active:translate-x-0.5 active:translate-y-0.5 active:shadow-none"
                    aria-label="Tutup modal">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="editTeacherForm" method="POST" class="flex min-h-0 flex-1 flex-col">
            @csrf
            @method('PUT')
            <div class="flex-1 space-y-4 overflow-y-auto px-6 py-5">
                <div>
                    <label class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">
                        Nama Lengkap & Gelar <span class="text-rose-600">*</span>
                    </label>
                    <input type="text"
                           id="edit_name"
                           name="name"
                           required
                           class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">NUPTK</label>
                        <input type="text"
                               id="edit_nuptk"
                               name="nuptk"
                               placeholder="16 digit NUPTK"
                               class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 font-mono text-sm font-semibold text-black placeholder:font-sans placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">NIP</label>
                        <input type="text"
                               id="edit_nip"
                               name="nip"
                               placeholder="18 digit NIP"
                               class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 font-mono text-sm font-semibold text-black placeholder:font-sans placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">
                            Jenis Kelamin <span class="text-rose-600">*</span>
                        </label>
                        <select id="edit_gender"
                                name="gender"
                                required
                                class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:outline-none">
                            <option value="L">Laki-laki (L)</option>
                            <option value="P">Perempuan (P)</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">Pendidikan Terakhir</label>
                        <select id="edit_last_education"
                                name="last_education"
                                class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:outline-none">
                            <option value="">-- Pilih Jenjang --</option>
                            <option value="D3">D3</option>
                            <option value="D4/S1">D4 / S1</option>
                            <option value="S2">S2</option>
                            <option value="S3">S3</option>
                            <option value="SMA/SMK">SMA / SMK / Sederajat</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">Tempat Lahir</label>
                        <input type="text"
                               id="edit_birth_place"
                               name="birth_place"
                               class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">Tanggal Lahir</label>
                        <input type="date"
                               id="edit_birth_date"
                               name="birth_date"
                               class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 font-mono text-sm font-semibold text-black shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">No. WhatsApp / HP</label>
                        <input type="text"
                               id="edit_phone"
                               name="phone"
                               class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 font-mono text-sm font-semibold text-black shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">Email (Opsional)</label>
                        <input type="email"
                               id="edit_email"
                               name="email"
                               placeholder="email@domain.com"
                               class="w-full rounded-lg border-2 border-black bg-white px-3.5 py-2 text-sm font-semibold text-black placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-black">Reset Kata Sandi (Opsional)</label>
                    <div class="relative">
                        <input type="password"
                               id="edit_password"
                               name="password"
                               placeholder="Biarkan kosong jika tidak ingin mengubah kata sandi"
                               class="w-full rounded-lg border-2 border-black bg-white py-2 pl-3.5 pr-10 font-mono text-sm font-semibold text-black placeholder:font-sans placeholder:font-normal placeholder:text-slate-400 shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:shadow-[3px_3px_0px_0px_#000] focus:outline-none">
                        <button type="button"
                                onclick="togglePasswordVisibility('edit_password', 'edit_eye_icon')"
                                class="absolute inset-y-0 right-0 flex cursor-pointer items-center pr-3 text-slate-600 transition hover:text-black focus:outline-none"
                                title="Tampilkan / Sembunyikan Password">
                            <span id="edit_eye_icon">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-2.5 border-t-2 border-black bg-slate-50 px-6 py-4">
                <button type="button"
                        onclick="closeModal('editTeacherModal')"
                        class="cursor-pointer rounded-lg border-2 border-black bg-white px-4 py-2 text-xs font-extrabold uppercase text-black shadow-[2px_2px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-slate-100 hover:shadow-[3px_3px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                    Batal
                </button>
                <button type="submit"
                        class="cursor-pointer rounded-lg border-2 border-black bg-[#74C0FC] px-5 py-2 text-xs font-black uppercase text-black shadow-[3px_3px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-[#4DABF7] hover:shadow-[4px_4px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 4: Atur Penugasan Mengajar Guru -->
<div id="assignmentTeacherModal"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm hidden"
     onclick="if (event.target === this) closeModal('assignmentTeacherModal')">
    <div class="relative flex max-h-[92dvh] w-full max-w-2xl flex-col overflow-hidden rounded-xl border-2 border-black bg-white shadow-[8px_8px_0px_0px_#000]">
        <!-- Header -->
        <div class="flex items-start justify-between border-b-2 border-black bg-[#E5DBFF] px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg border-2 border-black bg-white text-black shadow-[2px_2px_0px_0px_#000]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-black text-black">Atur Penugasan Mengajar Guru</h3>
                    <p id="assignment_teacher_subtitle" class="text-xs font-semibold text-black/75">
                        Memuat data guru...
                    </p>
                </div>
            </div>
            <button type="button"
                    onclick="closeModal('assignmentTeacherModal')"
                    class="cursor-pointer rounded-lg border-2 border-black bg-white p-1.5 text-black shadow-[2px_2px_0px_0px_#000] transition-all hover:bg-[#FF6B6B] hover:text-white active:translate-x-0.5 active:translate-y-0.5 active:shadow-none"
                    aria-label="Tutup modal">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="assignmentTeacherForm" method="POST" class="flex min-h-0 flex-1 flex-col" onsubmit="return handleAssignmentFormSubmit(event)">
            @csrf
            <div class="flex-1 space-y-4 overflow-y-auto px-6 py-5">
                <!-- Loading State Skeleton -->
                <div id="assignment_loading_skeleton" class="space-y-4">
                    <div class="h-14 w-full animate-pulse rounded-xl border-2 border-black/20 bg-slate-100"></div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="h-20 animate-pulse rounded-xl border-2 border-black/20 bg-slate-100"></div>
                        <div class="h-20 animate-pulse rounded-xl border-2 border-black/20 bg-slate-100"></div>
                    </div>
                    <div class="h-32 animate-pulse rounded-xl border-2 border-black/20 bg-slate-100"></div>
                </div>

                <!-- Error State -->
                <div id="assignment_error_state" class="hidden rounded-xl border-2 border-black bg-[#FFE3E3] p-4 text-xs font-bold text-black shadow-[3px_3px_0px_0px_#000]">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="text-base">⚠️</span>
                            <span id="assignment_error_msg">Gagal mengambil data penugasan dari server.</span>
                        </div>
                        <button type="button"
                                onclick="retryLoadAssignments()"
                                class="inline-flex cursor-pointer items-center gap-1 rounded-lg border-2 border-black bg-white px-3 py-1.5 text-xs font-black shadow-[2px_2px_0px_0px_#000] hover:bg-slate-100">
                            <span>🔄 Coba Lagi</span>
                        </button>
                    </div>
                </div>

                <!-- Main Content Wrapper -->
                <div id="assignment_content_wrapper" class="space-y-4">
                    <!-- Info Status Wali Kelas & Pintasan -->
                    <div id="assignment_homeroom_info" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl border-2 border-black bg-[#E7F5FF] p-3.5 text-xs shadow-[2px_2px_0px_0px_#000]">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md border-2 border-black bg-white text-black shadow-[1px_1px_0px_0px_#000]">
                                <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-extrabold uppercase tracking-wide text-slate-700">Wali Kelas:</span>
                                    <span id="assignment_homeroom_badge" class="hidden rounded-md border border-black bg-[#D3F9D8] px-1.5 py-0.2 text-[10px] font-black text-emerald-900">
                                        Aktif
                                    </span>
                                </div>
                                <div class="mt-0.5">
                                    <span id="assignment_homeroom_text" class="font-black text-black text-xs">—</span>
                                </div>
                            </div>
                        </div>

                        <!-- Selector Penetapan Wali Kelas & Tombol Terapkan -->
                        <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
                            <input type="hidden" name="has_homeroom_update" value="1">
                            <div class="flex items-center gap-1">
                                <label for="teacher_homeroom_class_select" class="text-[11px] font-bold text-slate-600 hidden md:inline">Tetapkan:</label>
                                <select id="teacher_homeroom_class_select"
                                        name="homeroom_class_id"
                                        onchange="onHomeroomClassSelectChanged()"
                                        class="rounded-lg border-2 border-black bg-white px-2.5 py-1 text-xs font-black text-black shadow-[2px_2px_0px_0px_#000] transition-all focus:bg-[#FFFDF0] focus:outline-none">
                                    <option value="">-- Bukan Wali Kelas --</option>
                                    @foreach($classes as $c)
                                        <option value="{{ $c->id }}" data-class-name="{{ $c->name }}">
                                            {{ $c->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="button"
                                    id="assignment_apply_homeroom_btn"
                                    onclick="applyHomeroomShortcut()"
                                    class="inline-flex cursor-pointer items-center gap-1 rounded-lg border-2 border-black bg-[#FFD43B] px-2.5 py-1 text-xs font-black text-black shadow-[2px_2px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-[#FCC419] hover:shadow-[3px_3px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none"
                                    title="Terapkan dan centang rombel wali kelas ini pada kartu penugasan mengajar">
                                <span>🎯</span>
                                <span>Terapkan ke Rombel</span>
                            </button>
                        </div>
                    </div>

                    <!-- Mode Selector -->
                    <div class="space-y-2">
                        <label class="block text-xs font-extrabold uppercase tracking-wide text-black">Aturan Pembatasan Mengajar</label>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border-2 border-black bg-white p-3.5 transition-all hover:bg-[#D3F9D8]/40 has-[:checked]:-translate-x-0.5 has-[:checked]:-translate-y-0.5 has-[:checked]:bg-[#D3F9D8] has-[:checked]:shadow-[3px_3px_0px_0px_#000]">
                                <input type="radio"
                                       name="mode"
                                       value="unrestricted"
                                       id="mode_unrestricted"
                                       onchange="toggleAssignmentMode()"
                                       class="mt-0.5 h-4 w-4 border-2 border-black text-black focus:ring-0">
                                <div class="space-y-0.5">
                                    <span class="block text-sm font-black text-black">Bebas (Fleksibel)</span>
                                    <span class="block text-xs font-medium text-slate-700">Dapat dijadwalkan mengajar mata pelajaran dan kelas apa saja.</span>
                                </div>
                            </label>

                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border-2 border-black bg-white p-3.5 transition-all hover:bg-[#E5DBFF]/50 has-[:checked]:-translate-x-0.5 has-[:checked]:-translate-y-0.5 has-[:checked]:bg-[#E5DBFF] has-[:checked]:shadow-[3px_3px_0px_0px_#000]">
                                <input type="radio"
                                       name="mode"
                                       value="restricted"
                                       id="mode_restricted"
                                       onchange="toggleAssignmentMode()"
                                       class="mt-0.5 h-4 w-4 border-2 border-black text-black focus:ring-0">
                                <div class="space-y-0.5">
                                    <span class="block text-sm font-black text-black">Dibatasi Khusus</span>
                                    <span class="block text-xs font-medium text-slate-700">Hanya mengajar mata pelajaran dan rombel yang ditentukan di bawah.</span>
                                </div>
                            </label>
                        </div>

                        <!-- Transition Warning Notice -->
                        <div id="assignment_transition_warning" class="hidden rounded-xl border-2 border-black bg-[#FFF3BF] p-3 text-xs font-bold text-black shadow-[2px_2px_0px_0px_#000]">
                            <div class="flex items-start gap-2">
                                <span class="text-sm">⚠️</span>
                                <div>
                                    <span class="font-black text-black">Perhatian Perubahan Mode:</span>
                                    <span class="text-slate-800">Guru ini sebelumnya memiliki daftar penugasan pembatasan. Jika disimpan dalam mode <strong>Bebas</strong>, seluruh daftar pembatasan tersebut akan dihapus permanen saat disimpan.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Container Baris Penugasan Khusus -->
                    <div id="restricted_container" class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-xs font-extrabold uppercase tracking-wide text-black">Daftar Mata Pelajaran & Rombel</label>
                                <p class="text-[11px] font-medium text-slate-600">Tentukan mata pelajaran yang diampu beserta rombel kelasnya.</p>
                            </div>
                            <button type="button"
                                    onclick="addAssignmentRow()"
                                    class="inline-flex cursor-pointer items-center gap-1 rounded-lg border-2 border-black bg-[#20C997] px-3 py-1.5 text-xs font-extrabold text-black shadow-[2px_2px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-[#12B886] hover:shadow-[3px_3px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.75" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>Tambah Mapel</span>
                            </button>
                        </div>

                        <div id="assignment_rows" class="space-y-3">
                            <!-- Dynamic rows inserted by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t-2 border-black bg-slate-50 px-6 py-3.5">
                <!-- Live Summary -->
                <div id="assignment_live_summary" class="inline-flex items-center gap-2 rounded-lg border-2 border-black bg-white px-3 py-1.5 text-xs font-bold text-black shadow-[2px_2px_0px_0px_#000]">
                    <span id="summary_icon">📚</span>
                    <span id="summary_text">Mode Bebas (Tanpa Batasan)</span>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button"
                            onclick="closeModal('assignmentTeacherModal')"
                            class="cursor-pointer rounded-lg border-2 border-black bg-white px-4 py-2 text-xs font-extrabold uppercase text-black shadow-[2px_2px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-slate-100 hover:shadow-[3px_3px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                        Batal
                    </button>
                    <button type="submit"
                            id="assignment_submit_btn"
                            class="inline-flex items-center gap-1.5 cursor-pointer rounded-lg border-2 border-black bg-[#D0BFFF] px-5 py-2 text-xs font-black uppercase text-black shadow-[3px_3px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-[#B197FC] hover:shadow-[4px_4px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                        <span id="submit_text">Simpan Penugasan</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal 5: Atur Pengelola Tabungan Siswa -->
<div id="savingsOfficerModal"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm hidden"
     onclick="if (event.target === this) closeModal('savingsOfficerModal')">
    <div class="relative flex max-h-[90dvh] w-full max-w-xl flex-col overflow-hidden rounded-xl border-2 border-black bg-white shadow-[8px_8px_0px_0px_#000]">
        <!-- Header -->
        <div class="flex items-start justify-between border-b-2 border-black bg-[#63E6BE] px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg border-2 border-black bg-white text-black shadow-[2px_2px_0px_0px_#000]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-black text-black">Atur Pengelola Tabungan Siswa</h3>
                    <p id="savings_modal_subtitle" class="text-xs font-semibold text-black/75">
                        Atur status pengelola dan rombel/kelas yang dikelola.
                    </p>
                </div>
            </div>
            <button type="button"
                    onclick="closeModal('savingsOfficerModal')"
                    class="cursor-pointer rounded-lg border-2 border-black bg-white p-1.5 text-black shadow-[2px_2px_0px_0px_#000] transition-all hover:bg-[#FF6B6B] hover:text-white active:translate-x-0.5 active:translate-y-0.5 active:shadow-none"
                    aria-label="Tutup modal">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="savingsOfficerForm" method="POST" class="flex min-h-0 flex-1 flex-col">
            @csrf
            @method('PATCH')
            <div class="flex-1 space-y-5 overflow-y-auto px-6 py-5">
                <!-- Status Pengelola Tabungan -->
                <div class="space-y-2.5">
                    <label class="block text-xs font-extrabold uppercase tracking-wide text-black">Status Penugasan Pengelola</label>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border-2 border-black bg-white p-3.5 transition-all hover:bg-[#D3F9D8]/50 has-[:checked]:-translate-x-0.5 has-[:checked]:-translate-y-0.5 has-[:checked]:bg-[#D3F9D8] has-[:checked]:shadow-[3px_3px_0px_0px_#000]">
                            <input type="radio"
                                   name="is_savings_officer"
                                   value="1"
                                   id="savings_officer_active"
                                   onchange="toggleSavingsOfficerStatus()"
                                   class="mt-0.5 h-4 w-4 border-2 border-black text-black focus:ring-0">
                            <div class="space-y-0.5">
                                <span class="block text-sm font-black text-black">Aktif (Ditugaskan)</span>
                                <span class="block text-xs font-medium text-slate-700">Berwenang melayani transaksi tabungan siswa.</span>
                            </div>
                        </label>

                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border-2 border-black bg-white p-3.5 transition-all hover:bg-[#FFE3E3]/60 has-[:checked]:-translate-x-0.5 has-[:checked]:-translate-y-0.5 has-[:checked]:bg-[#FFE3E3] has-[:checked]:shadow-[3px_3px_0px_0px_#000]">
                            <input type="radio"
                                   name="is_savings_officer"
                                   value="0"
                                   id="savings_officer_inactive"
                                   onchange="toggleSavingsOfficerStatus()"
                                   class="mt-0.5 h-4 w-4 border-2 border-black text-black focus:ring-0">
                            <div class="space-y-0.5">
                                <span class="block text-sm font-black text-black">Nonaktif</span>
                                <span class="block text-xs font-medium text-slate-700">Bukan / lepas jabatan pengelola tabungan siswa.</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Cakupan Wewenang Rombel / Kelas -->
                <div id="savings_scope_section" class="space-y-4 rounded-xl border-2 border-black bg-[#FFFDF5] p-4 shadow-[3px_3px_0px_0px_#000]">
                    <div class="space-y-2.5">
                        <label class="block text-xs font-extrabold uppercase tracking-wide text-black">Cakupan Rombel / Kelas</label>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border-2 border-black bg-white p-3 transition-all hover:bg-[#D0EBFF]/50 has-[:checked]:-translate-x-0.5 has-[:checked]:-translate-y-0.5 has-[:checked]:bg-[#D0EBFF] has-[:checked]:shadow-[2px_2px_0px_0px_#000]">
                                <input type="radio"
                                       name="savings_scope"
                                       value="all"
                                       id="savings_scope_all"
                                       onchange="toggleSavingsScope()"
                                       class="mt-0.5 h-4 w-4 border-2 border-black text-black focus:ring-0">
                                <div class="space-y-0.5">
                                    <span class="block text-xs font-black text-black">Semua Kelas</span>
                                    <span class="block text-[11px] font-medium text-slate-700">Dapat melayani seluruh rombel tanpa batasan.</span>
                                </div>
                            </label>

                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border-2 border-black bg-white p-3 transition-all hover:bg-[#FFF3BF]/60 has-[:checked]:-translate-x-0.5 has-[:checked]:-translate-y-0.5 has-[:checked]:bg-[#FFF3BF] has-[:checked]:shadow-[2px_2px_0px_0px_#000]">
                                <input type="radio"
                                       name="savings_scope"
                                       value="restricted"
                                       id="savings_scope_restricted"
                                       onchange="toggleSavingsScope()"
                                       class="mt-0.5 h-4 w-4 border-2 border-black text-black focus:ring-0">
                                <div class="space-y-0.5">
                                    <span class="block text-xs font-black text-black">Kelas Tertentu</span>
                                    <span class="block text-[11px] font-medium text-slate-700">Hanya mengelola rombel yang dipilih di bawah.</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Checklist Kelas Tertentu -->
                    <div id="savings_classes_checklist_container" class="space-y-2.5 border-t-2 border-black/15 pt-3">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-extrabold text-black">Pilih Kelas yang Dikelola</label>
                            <div class="flex items-center gap-2 text-xs">
                                <button type="button" onclick="toggleAllSavingsClasses(true)" class="cursor-pointer font-extrabold text-black underline decoration-2 hover:text-blue-700">
                                    Pilih Semua
                                </button>
                                <span class="font-black text-black/40">·</span>
                                <button type="button" onclick="toggleAllSavingsClasses(false)" class="cursor-pointer font-bold text-slate-600 hover:text-black hover:underline">
                                    Batalkan
                                </button>
                            </div>
                        </div>
                        <div class="grid max-h-48 grid-cols-2 gap-2 overflow-y-auto rounded-lg border-2 border-black bg-white p-2.5 sm:grid-cols-3">
                            @foreach($classes as $c)
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border-2 border-black/20 bg-slate-50 px-2.5 py-1.5 text-xs transition hover:border-black hover:bg-[#FFF9DB] has-[:checked]:border-black has-[:checked]:bg-[#FFD43B] has-[:checked]:shadow-[2px_2px_0px_0px_#000]">
                                    <input type="checkbox"
                                           name="class_ids[]"
                                           value="{{ $c->id }}"
                                           class="savings-class-checkbox h-3.5 w-3.5 rounded border-2 border-black text-black focus:ring-0">
                                    <span class="truncate font-extrabold text-black">{{ $c->name }}</span>
                                    <span class="ml-auto font-mono text-[10px] font-bold text-black/70 tabular-nums">({{ $c->students_count ?? $c->students()->count() }})</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-2.5 border-t-2 border-black bg-slate-50 px-6 py-4">
                <button type="button"
                        onclick="closeModal('savingsOfficerModal')"
                        class="cursor-pointer rounded-lg border-2 border-black bg-white px-4 py-2 text-xs font-extrabold uppercase text-black shadow-[2px_2px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-slate-100 hover:shadow-[3px_3px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                    Batal
                </button>
                <button type="submit"
                        class="cursor-pointer rounded-lg border-2 border-black bg-[#FFD43B] px-5 py-2 text-xs font-black uppercase text-black shadow-[3px_3px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-[#FCC419] hover:shadow-[4px_4px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 6: Konfirmasi Masuk sebagai Akun Guru (Impersonasi) -->
<div id="impersonationTeacherModal"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm hidden"
     onclick="if (event.target === this) cancelTeacherImpersonation()">
    <div class="relative max-h-[90dvh] w-full max-w-md overflow-y-auto rounded-xl border-2 border-black bg-white shadow-[8px_8px_0px_0px_#000]">
        <!-- Header -->
        <div class="flex items-start justify-between border-b-2 border-black bg-[#A5D8FF] px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border-2 border-black bg-white text-black shadow-[2px_2px_0px_0px_#000]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-black text-black">Masuk sebagai Akun Guru</h3>
                    <p class="text-xs font-semibold text-black/75">Konfirmasi impersonasi akun</p>
                </div>
            </div>
            <button type="button"
                    onclick="cancelTeacherImpersonation()"
                    class="cursor-pointer rounded-lg border-2 border-black bg-white p-1.5 text-black shadow-[2px_2px_0px_0px_#000] transition-all hover:bg-[#FF6B6B] hover:text-white active:translate-x-0.5 active:translate-y-0.5 active:shadow-none"
                    aria-label="Tutup modal">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Content -->
        <div class="space-y-4 px-6 py-5">
            <div class="rounded-xl border-2 border-black bg-[#E7F5FF] p-4 shadow-[3px_3px_0px_0px_#000]">
                <div class="text-[10px] font-black uppercase tracking-wider text-slate-600">Akun Guru yang Dipilih</div>
                <div id="impersonation_teacher_name" class="mt-1 text-sm font-black text-black">-</div>
            </div>

            <p class="text-xs font-semibold leading-relaxed text-slate-700">
                Masuk ke akun guru ini? Anda akan dialihkan ke Portal Guru dan dapat kembali ke akun admin kapan saja.
            </p>

            <div class="flex items-start gap-2.5 rounded-lg border-2 border-black bg-[#FFF3BF] px-3.5 py-3 text-xs text-black shadow-[2px_2px_0px_0px_#000]">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span class="font-semibold">Seluruh aktivitas selama impersonasi akan tercatat atas nama akun guru ini.</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-end gap-2.5 border-t-2 border-black bg-slate-50 px-6 py-4">
            <button type="button"
                    onclick="cancelTeacherImpersonation()"
                    class="cursor-pointer rounded-lg border-2 border-black bg-white px-4 py-2 text-xs font-extrabold uppercase text-black shadow-[2px_2px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-slate-100 hover:shadow-[3px_3px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                Batal
            </button>
            <button type="button"
                    onclick="submitTeacherImpersonation()"
                    class="cursor-pointer rounded-lg border-2 border-black bg-[#A5D8FF] px-5 py-2 text-xs font-black uppercase text-black shadow-[3px_3px_0px_0px_#000] transition-all hover:-translate-x-0.5 hover:-translate-y-0.5 hover:bg-[#74C0FC] hover:shadow-[4px_4px_0px_0px_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                Ya, Masuk Sekarang
            </button>
        </div>
    </div>
</div>
