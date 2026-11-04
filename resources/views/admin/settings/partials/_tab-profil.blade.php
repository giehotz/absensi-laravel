<!-- Tab 2: Informasi Profil Sekolah -->
<div id="tab-profil" class="tab-panel hidden">
    <div class="bg-white neo-box-lg p-6 sm:p-8 space-y-5">
        <div class="border-b-2 border-black pb-3">
            <span class="neo-badge bg-[#FFD43B] text-black">PROFIL LEMBAGA</span>
            <h2 class="font-heading font-black text-xl text-black mt-2">
                Identitas Satuan Pendidikan
            </h2>
            <p class="text-xs text-slate-600 font-semibold mt-0.5">
                Informasi ini dicetak pada kartu identitas QR dan header laporan rekapitulasi.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Nama Lembaga / Sekolah *</label>
                <input type="text" name="school_name" value="{{ old('school_name', $setting->school_name ?? 'SMP Negeri 1 Garuda') }}" required class="w-full px-3.5 py-2.5 neo-input bg-slate-50 font-bold text-sm text-black">
            </div>
            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">NPSN</label>
                <input type="text" name="npsn" value="{{ old('npsn', $setting->npsn ?? '20102030') }}" class="w-full px-3.5 py-2.5 neo-input bg-slate-50 font-mono text-sm text-black">
            </div>
            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Jenjang Satuan Pendidikan *</label>
                <select name="level" required class="w-full px-3.5 py-2.5 neo-input bg-slate-50 font-bold text-sm text-black">
                    @foreach(['SD', 'MI', 'SMP', 'MTs', 'SMA', 'MA', 'SMK'] as $lvl)
                        <option value="{{ $lvl }}" {{ old('level', $setting->level ?? 'SMP') === $lvl ? 'selected' : '' }}>
                            {{ $lvl }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block font-heading font-bold text-xs text-black mb-1">Alamat Lengkap (Jalan / Dusun / RT / RW)</label>
            <input type="text" name="school_address" value="{{ old('school_address', $setting->school_address ?? 'Jl. Pendidikan No. 45, Kompleks Pelajar Mandiri') }}" class="w-full px-3.5 py-2.5 neo-input bg-slate-50 text-sm text-black" placeholder="Contoh: Jl. Lapangan Ampera No. 109, RT 02/RW 01">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Provinsi</label>
                <input type="text" name="school_province" value="{{ old('school_province', $setting->school_province ?? '') }}" class="w-full px-3.5 py-2.5 neo-input bg-slate-50 text-sm text-black" placeholder="Contoh: Lampung">
            </div>
            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Kabupaten / Kota</label>
                <input type="text" name="school_city" value="{{ old('school_city', $setting->school_city ?? '') }}" class="w-full px-3.5 py-2.5 neo-input bg-slate-50 text-sm text-black" placeholder="Contoh: Tanggamus">
            </div>
            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Kecamatan</label>
                <input type="text" name="school_district" value="{{ old('school_district', $setting->school_district ?? '') }}" class="w-full px-3.5 py-2.5 neo-input bg-slate-50 text-sm text-black" placeholder="Contoh: Talang Padang">
            </div>
            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">Desa / Kelurahan</label>
                <input type="text" name="school_village" value="{{ old('school_village', $setting->school_village ?? '') }}" class="w-full px-3.5 py-2.5 neo-input bg-slate-50 text-sm text-black" placeholder="Contoh: Negeri Agung">
            </div>
        </div>

        <div>
            <span class="text-[11px] text-slate-500 font-semibold block">
                Jenjang di atas berfungsi sebagai <strong>sumber data utama</strong> yang otomatis diterapkan pada seluruh rombel/kelas.
            </span>
        </div>

        <!-- Konfigurasi Kepala Madrasah / Penanggung Jawab Lembaga -->
        <div class="border-2 border-black bg-white p-5 neo-box space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b-2 border-black pb-3">
                <div>
                    <span class="neo-badge bg-[#20C997] text-black text-[10px]">PIMPINAN LEMBAGA</span>
                    <h3 class="font-heading font-black text-base text-black mt-1 flex items-center gap-2">
                        <span>👨‍🏫</span> Kepala Madrasah / Sekolah
                    </h3>
                    <p class="text-xs text-slate-600 font-semibold mt-0.5">
                        Nama dan NIP otomatis dicetak pada tanda tangan Kartu Siswa, Rekap MBG, Jurnal Guru, dan Laporan Resmi.
                    </p>
                </div>
                <div id="headmaster_mode_badge_container">
                    <span id="headmaster_mode_badge" class="neo-badge bg-slate-100 text-slate-700 text-[10px]">
                        Input Manual Bebas
                    </span>
                </div>
            </div>

            <!-- Pilih Sumber Data Guru atau Manual -->
            <div>
                <label class="block font-heading font-bold text-xs text-black mb-1">
                    Ambil dari Data Guru (Master Data)
                </label>
                <select name="headmaster_teacher_id" id="headmaster_teacher_id" onchange="handleHeadmasterSelectChange(this)" class="w-full px-3.5 py-2.5 neo-input bg-slate-50 font-medium text-sm text-black cursor-pointer">
                    <option value="" data-name="" data-nip="">-- Input Manual / Ketik Sendiri --</option>
                    @if(isset($teachers))
                        @foreach($teachers as $t)
                            @php
                                $tName = $t->user?->name ?? 'Guru';
                                $tNip = $t->nip ?? '';
                            @endphp
                            <option value="{{ $t->id }}" 
                                    data-name="{{ $tName }}" 
                                    data-nip="{{ $tNip }}"
                                    {{ (string) old('headmaster_teacher_id', $setting->headmaster_teacher_id) === (string) $t->id ? 'selected' : '' }}>
                                {{ $tName }} (NIP: {{ $tNip ?: '-' }})
                            </option>
                        @endforeach
                    @endif
                </select>
                <span class="text-[11px] text-slate-500 font-semibold mt-1 block">
                    Pilih nama guru di atas untuk mengisi Nama & NIP secara otomatis dari master data guru, atau pilih <em>Input Manual</em> untuk mengetik nama pimpinan luar/Plt secara bebas.
                </span>
            </div>

            <!-- Input Nama & NIP Kepala Madrasah -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">
                        Nama Kepala Madrasah / Sekolah *
                    </label>
                    <input type="text" 
                           name="card_principal_name" 
                           id="card_principal_name_input"
                           value="{{ old('card_principal_name', $setting->card_principal_name ?? '') }}" 
                           placeholder="Contoh: Drs. H. Ahmad Subarjo, M.Pd." 
                           class="w-full px-3.5 py-2.5 neo-input text-sm text-black font-bold transition-all">
                    <span id="headmaster_name_hint" class="text-[10px] text-slate-500 mt-0.5 block font-medium"></span>
                </div>
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">
                        NIP Kepala Madrasah / Sekolah
                    </label>
                    <input type="text" 
                           name="card_principal_nip" 
                           id="card_principal_nip_input"
                           value="{{ old('card_principal_nip', $setting->card_principal_nip ?? '') }}" 
                           placeholder="Contoh: 19750812 200003 1 002" 
                           class="w-full px-3.5 py-2.5 neo-input text-sm text-black font-mono transition-all">
                    <span id="headmaster_nip_hint" class="text-[10px] text-slate-500 mt-0.5 block font-medium"></span>
                </div>
            </div>
        </div>

        <!-- Upload Logo Satuan Pendidikan -->
        <div class="border-2 border-black bg-slate-50 p-4 neo-box space-y-3">
            <div>
                <span class="neo-badge bg-[#5294FF] text-white text-[10px]">LOGO LEMBAGA</span>
                <label class="block font-heading font-black text-sm text-black mt-1">
                    Logo Resmi Satuan Pendidikan
                </label>
                <p class="text-xs text-slate-600 font-semibold">
                    Logo ini akan ditampilkan pada sidebar admin, portal guru, navigasi layout, serta kartu presensi/laporan.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 pt-1">
                <!-- Preview Box -->
                <div class="flex-shrink-0">
                    <div id="logo-preview-box" class="w-24 h-24 bg-white rounded-lg border-2 border-black shadow-[3px_3px_0px_0px_#000] p-1.5 flex items-center justify-center overflow-hidden relative group">
                        @if(!empty($setting->logo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($setting->logo))
                            <img id="logo-preview-image" src="{{ asset('storage/' . $setting->logo) }}" alt="Logo Lembaga" class="w-full h-full object-contain">
                            <span id="logo-preview-placeholder" class="hidden font-heading font-black text-3xl text-slate-300">LOGO</span>
                        @else
                            <img id="logo-preview-image" src="#" alt="Pratinjau Logo" class="w-full h-full object-contain hidden">
                            <div id="logo-preview-placeholder" class="text-center text-slate-400">
                                <svg class="w-8 h-8 mx-auto text-slate-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-[10px] font-bold uppercase tracking-wider block">Belum Ada</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Input & Controls -->
                <div class="flex-1 w-full space-y-2">
                    <div>
                        <input type="file" name="logo" id="school_logo_input" accept="image/png,image/jpeg,image/jpg,image/svg+xml,image/webp" onchange="previewSchoolLogo(event)" class="block w-full text-xs text-slate-700 file:mr-3 file:py-2.5 file:px-4 file:rounded file:border-2 file:border-black file:text-xs file:font-heading file:font-bold file:bg-[#FFD43B] file:text-black hover:file:bg-[#fcc419] cursor-pointer">
                        <span id="logo-filename" class="text-[11px] font-mono text-emerald-700 font-bold mt-1 block hidden"></span>
                        @error('logo')
                            <p class="text-xs font-bold text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="text-[11px] text-slate-500 font-medium space-y-0.5">
                        <p>• Format yang didukung: <span class="font-bold text-black">PNG, JPG, JPEG, SVG, WebP</span> (Maksimal 2 MB).</p>
                        <p>• Disarankan menggunakan gambar dengan rasio 1:1 atau berlatar belakang transparan untuk tampilan optimal.</p>
                    </div>

                    @if(!empty($setting->logo))
                        <div class="pt-2 border-t border-slate-200">
                            <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-bold text-red-600 hover:text-red-700 bg-red-50 px-2.5 py-1.5 rounded border border-red-200">
                                <input type="checkbox" name="remove_logo" value="1" class="rounded border-2 border-black text-red-600 focus:ring-0">
                                <span>Hapus logo saat ini (kembalikan ke inisial huruf)</span>
                            </label>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Upload Favicon Satuan Pendidikan / Browser -->
        <div class="border-2 border-black bg-slate-50 p-4 neo-box space-y-3">
            <div>
                <span class="neo-badge bg-[#FF6B6B] text-white text-[10px]">FAVICON BROWSER</span>
                <label class="block font-heading font-black text-sm text-black mt-1">
                    Favicon Aplikasi / Browser
                </label>
                <p class="text-xs text-slate-600 font-semibold">
                    Ikon kecil yang muncul pada tab browser, shortcut bookmark, dan jendela aplikasi web.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 pt-1">
                <!-- Preview Box -->
                <div class="flex-shrink-0">
                    <div id="favicon-preview-box" class="w-16 h-16 bg-white rounded-lg border-2 border-black shadow-[3px_3px_0px_0px_#000] p-1.5 flex items-center justify-center overflow-hidden relative group">
                        @if(!empty($setting->favicon) && \Illuminate\Support\Facades\Storage::disk('public')->exists($setting->favicon))
                            <img id="favicon-preview-image" src="{{ asset('storage/' . $setting->favicon) }}" alt="Favicon Aplikasi" class="w-8 h-8 object-contain">
                            <span id="favicon-preview-placeholder" class="hidden text-xs font-bold text-slate-400">ICO</span>
                        @else
                            <img id="favicon-preview-image" src="#" alt="Pratinjau Favicon" class="w-8 h-8 object-contain hidden">
                            <div id="favicon-preview-placeholder" class="text-center text-slate-400">
                                <span class="text-xl">🌐</span>
                                <span class="text-[9px] font-bold uppercase tracking-wider block">Default</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Input & Controls -->
                <div class="flex-1 w-full space-y-2">
                    <div>
                        <input type="file" name="favicon" id="app_favicon_input" accept=".ico,image/x-icon,image/png,image/svg+xml,image/webp,image/jpeg,image/jpg" onchange="previewAppFavicon(event)" class="block w-full text-xs text-slate-700 file:mr-3 file:py-2.5 file:px-4 file:rounded file:border-2 file:border-black file:text-xs file:font-heading file:font-bold file:bg-[#FFD43B] file:text-black hover:file:bg-[#fcc419] cursor-pointer">
                        <span id="favicon-filename" class="text-[11px] font-mono text-emerald-700 font-bold mt-1 block hidden"></span>
                        @error('favicon')
                            <p class="text-xs font-bold text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="text-[11px] text-slate-500 font-medium space-y-0.5">
                        <p>• Format yang didukung: <span class="font-bold text-black">ICO, PNG, SVG, WebP, JPG</span> (Maksimal 1 MB).</p>
                        <p>• Ukuran yang disarankan: <span class="font-bold text-black">32x32 px</span> atau <span class="font-bold text-black">64x64 px</span> (persegi).</p>
                    </div>

                    @if(!empty($setting->favicon))
                        <div class="pt-2 border-t border-slate-200">
                            <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-bold text-red-600 hover:text-red-700 bg-red-50 px-2.5 py-1.5 rounded border border-red-200">
                                <input type="checkbox" name="remove_favicon" value="1" class="rounded border-2 border-black text-red-600 focus:ring-0">
                                <span>Hapus favicon kustom (kembalikan ke default)</span>
                            </label>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Submit Profil -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t-2 border-slate-200">
            <button type="submit" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-6 py-3 text-sm uppercase tracking-wider font-heading cursor-pointer">
                💾 Simpan Profil Sekolah
            </button>
        </div>
    </div>
</div>
