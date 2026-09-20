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
            <label class="block font-heading font-bold text-xs text-black mb-1">Alamat Lengkap</label>
            <input type="text" name="school_address" value="{{ old('school_address', $setting->school_address ?? 'Jl. Pendidikan No. 45, Kompleks Pelajar Mandiri') }}" class="w-full px-3.5 py-2.5 neo-input bg-slate-50 text-sm text-black">
            <span class="text-[11px] text-slate-500 font-semibold mt-1 block">
                Jenjang di atas berfungsi sebagai <strong>sumber data utama</strong> yang otomatis diterapkan pada seluruh rombel/kelas.
            </span>
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

        <!-- Submit Profil -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t-2 border-slate-200">
            <button type="submit" class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-6 py-3 text-sm uppercase tracking-wider font-heading cursor-pointer">
                💾 Simpan Profil Sekolah
            </button>
        </div>
    </div>
</div>
