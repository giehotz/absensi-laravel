<!-- Tab 3: Pengaturan Kop Surat Lembaga -->
<div id="tab-kop" class="tab-panel hidden">
    <div class="bg-white neo-box-lg p-6 sm:p-8 space-y-6">
        <!-- Header Tab -->
        <div class="border-b-2 border-black pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <span class="neo-badge bg-[#FFD43B] text-black font-bold">KOP SURAT RESMI</span>
                <h2 class="font-heading font-black text-xl text-black mt-2">
                    Format & Identitas Kop Surat
                </h2>
                <p class="text-xs text-slate-600 font-semibold mt-0.5">
                    Konfigurasi kepala surat resmi untuk pencetakan dokumen, laporan absensi, dan file PDF kedinasan.
                </p>
            </div>

            <!-- Toggle Aktif Kop Surat -->
            <div class="flex items-center gap-2 bg-slate-50 border-2 border-black px-3 py-2 rounded shadow-[2px_2px_0px_0px_#000]">
                <input type="hidden" name="kop_is_active" value="0">
                <input type="checkbox" name="kop_is_active" id="kop_is_active" value="1" 
                       {{ old('kop_is_active', $setting->kop_is_active ?? true) ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-2 border-black text-black focus:ring-0 cursor-pointer">
                <label for="kop_is_active" class="text-xs font-bold text-black cursor-pointer select-none">
                    Aktifkan Kop Surat pada Cetakan
                </label>
            </div>
        </div>

        <!-- Formulir Teks Kop Surat -->
        <div class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Baris 1: Kementerian / Instansi Pusat -->
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">
                        Instansi Pembina / Kementerian (Opsional)
                    </label>
                    <input type="text" 
                           name="kop_government_name" 
                           id="input_kop_gov"
                           oninput="updateKopPreview()"
                           value="{{ old('kop_government_name', $setting->kop_government_name ?? 'KEMENTERIAN AGAMA REPUBLIK INDONESIA') }}" 
                           placeholder="Contoh: KEMENTERIAN AGAMA REPUBLIK INDONESIA" 
                           class="w-full px-3.5 py-2.5 neo-input bg-slate-50 font-bold text-xs text-black uppercase">
                    <span class="text-[10px] text-slate-500 font-semibold mt-0.5 block">
                        Kosongkan jika sekolah/lembaga swasta murni tanpa naungan kementerian.
                    </span>
                </div>

                <!-- Baris 2: Instansi Daerah / Yayasan -->
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">
                        Instansi Daerah / Kantor Kemenag / Yayasan (Opsional)
                    </label>
                    <input type="text" 
                           name="kop_institution_name" 
                           id="input_kop_inst"
                           oninput="updateKopPreview()"
                           value="{{ old('kop_institution_name', $setting->kop_institution_name ?? 'KANTOR KEMENTERIAN AGAMA KABUPATEN TANGGAMUS') }}" 
                           placeholder="Contoh: KANTOR KEMENTERIAN AGAMA KABUPATEN TANGGAMUS" 
                           class="w-full px-3.5 py-2.5 neo-input bg-slate-50 font-bold text-xs text-black uppercase">
                    <span class="text-[10px] text-slate-500 font-semibold mt-0.5 block">
                        Contoh: Kantor Kemenag Kab/Kota, Dinas Pendidikan, atau Yayasan Pengelola.
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Baris 3: Nama Sekolah (Heading Utama) -->
                <div class="sm:col-span-2">
                    <label class="block font-heading font-bold text-xs text-black mb-1">
                        Nama Satuan Pendidikan / Madrasah / Sekolah *
                    </label>
                    <input type="text" 
                           name="kop_school_name" 
                           id="input_kop_school"
                           oninput="updateKopPreview()"
                           value="{{ old('kop_school_name', $setting->kop_school_name ?? $setting->school_name ?? 'MADRASAH IBTIDAIYAH NEGERI 2 TANGGAMUS') }}" 
                           required 
                           placeholder="Contoh: MADRASAH IBTIDAIYAH NEGERI 2 TANGGAMUS" 
                           class="w-full px-3.5 py-2.5 neo-input bg-slate-50 font-black text-sm text-black uppercase">
                </div>

                <!-- Gaya Garis Pembatas -->
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">
                        Garis Pembatas Kop
                    </label>
                    <select name="kop_border_style" 
                            id="input_kop_border"
                            onchange="updateKopPreview()"
                            class="w-full px-3.5 py-2.5 neo-input bg-slate-50 font-bold text-xs text-black cursor-pointer">
                        <option value="double" {{ old('kop_border_style', $setting->kop_border_style ?? 'double') === 'double' ? 'selected' : '' }}>
                            Garis Ganda Standar Dinas (Tebal & Tipis)
                        </option>
                        <option value="single" {{ old('kop_border_style', $setting->kop_border_style) === 'single' ? 'selected' : '' }}>
                            Garis Tunggal Tebal
                        </option>
                        <option value="none" {{ old('kop_border_style', $setting->kop_border_style) === 'none' ? 'selected' : '' }}>
                            Tanpa Garis Pembatas
                        </option>
                    </select>
                </div>
            </div>

            <!-- Baris 4: Alamat & Kode Pos -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="sm:col-span-3">
                    <label class="block font-heading font-bold text-xs text-black mb-1">
                        Alamat Lengkap Satuan Pendidikan
                    </label>
                    <input type="text" 
                           name="kop_address" 
                           id="input_kop_address"
                           oninput="updateKopPreview()"
                           value="{{ old('kop_address', $setting->kop_address ?? 'Jl. Lapangan Ampera No. 109') }}" 
                           placeholder="Contoh: Jl. Lapangan Ampera No. 109" 
                           class="w-full px-3.5 py-2.5 neo-input bg-slate-50 text-xs text-black">
                </div>
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">
                        Kode Pos
                    </label>
                    <input type="text" 
                           name="kop_postal_code" 
                           id="input_kop_postal_code"
                           oninput="updateKopPreview()"
                           value="{{ old('kop_postal_code', $setting->kop_postal_code ?? '35384') }}" 
                           placeholder="35384" 
                           class="w-full px-3.5 py-2.5 neo-input bg-slate-50 font-mono text-xs text-black">
                </div>
            </div>

            <!-- Baris 5: Kontak & Media Komunikasi -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">
                        Nomor Telepon / Fax
                    </label>
                    <input type="text" 
                           name="kop_phone" 
                           id="input_kop_phone"
                           oninput="updateKopPreview()"
                           value="{{ old('kop_phone', $setting->kop_phone ?? '') }}" 
                           placeholder="(0722) 123456" 
                           class="w-full px-3.5 py-2.5 neo-input bg-slate-50 font-mono text-xs text-black">
                </div>
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">
                        Alamat Email Resmi
                    </label>
                    <input type="email" 
                           name="kop_email" 
                           id="input_kop_email"
                           oninput="updateKopPreview()"
                           value="{{ old('kop_email', $setting->kop_email ?? 'minduatanggamus@gmail.com') }}" 
                           placeholder="minduatanggamus@gmail.com" 
                           class="w-full px-3.5 py-2.5 neo-input bg-slate-50 text-xs text-black">
                </div>
                <div>
                    <label class="block font-heading font-bold text-xs text-black mb-1">
                        Website Resmi
                    </label>
                    <input type="text" 
                           name="kop_website" 
                           id="input_kop_website"
                           oninput="updateKopPreview()"
                           value="{{ old('kop_website', $setting->kop_website ?? '') }}" 
                           placeholder="www.min2tanggamus.sch.id" 
                           class="w-full px-3.5 py-2.5 neo-input bg-slate-50 font-mono text-xs text-black">
                </div>
            </div>
        </div>

        <!-- Bagian Upload 2 Logo Kop Surat -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
            <!-- Box Logo Kiri -->
            <div class="border-2 border-black bg-slate-50 p-4 neo-box space-y-3">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="neo-badge bg-[#5294FF] text-white text-[10px]">LOGO KIRI</span>
                        <span class="text-[10px] font-bold text-slate-500 uppercase">Instansi / Kementerian</span>
                    </div>
                    <label class="block font-heading font-bold text-xs text-black mt-1">
                        Logo Kementerian / Pembina (Kiri)
                    </label>
                    <p class="text-[11px] text-slate-600">
                        Contoh: Logo Ikhlas Beramal (Kemenag), Tut Wuri Handayani, atau Lambang Yayasan.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Preview Box Logo Kiri -->
                    <div class="w-20 h-20 bg-white rounded border-2 border-black p-1 shadow-[2px_2px_0px_0px_#000] flex items-center justify-center flex-shrink-0 overflow-hidden">
                        @php
                            $logoLeftExisting = !empty($setting->kop_logo_left) 
                                ? $setting->kop_logo_left 
                                : (!empty($setting->logo) ? $setting->logo : null);
                        @endphp
                        @if($logoLeftExisting && \Illuminate\Support\Facades\Storage::disk('public')->exists($logoLeftExisting))
                            <img id="kop_logo_left_preview" src="{{ asset('storage/' . $logoLeftExisting) }}" alt="Logo Kiri" class="w-full h-full object-contain">
                            <span id="kop_logo_left_placeholder" class="hidden text-xs font-bold text-slate-300">LOGO</span>
                        @else
                            <img id="kop_logo_left_preview" src="#" alt="Pratinjau" class="w-full h-full object-contain hidden">
                            <span id="kop_logo_left_placeholder" class="text-[10px] font-bold text-slate-400 text-center">Kiri</span>
                        @endif
                    </div>

                    <!-- Input Logo Kiri -->
                    <div class="flex-1 space-y-1.5">
                        <input type="file" 
                               name="kop_logo_left" 
                               id="kop_logo_left_input"
                               accept="image/png,image/jpeg,image/jpg,image/svg+xml,image/webp" 
                               onchange="previewKopLogo(event, 'kop_logo_left_preview', 'kop_logo_left_placeholder', 'kop_preview_logo_left')" 
                               class="block w-full text-xs text-slate-700 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-2 file:border-black file:text-xs file:font-bold file:bg-[#FFD43B] file:text-black hover:file:bg-[#fcc419] cursor-pointer">
                        <p class="text-[10px] text-slate-500">• PNG transparan disarankan (Maks 2MB).</p>
                        @if(!empty($setting->kop_logo_left))
                            <label class="inline-flex items-center gap-1.5 text-[11px] font-bold text-red-600 cursor-pointer">
                                <input type="checkbox" name="remove_kop_logo_left" value="1" class="rounded border-black text-red-600">
                                <span>Hapus logo kiri</span>
                            </label>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Box Logo Kanan -->
            <div class="border-2 border-black bg-slate-50 p-4 neo-box space-y-3">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="neo-badge bg-[#20C997] text-black text-[10px]">LOGO KANAN</span>
                        <span class="text-[10px] font-bold text-slate-500 uppercase">Sekolah / Daerah</span>
                    </div>
                    <label class="block font-heading font-bold text-xs text-black mt-1">
                        Logo Satuan Pendidikan (Kanan)
                    </label>
                    <p class="text-[11px] text-slate-600">
                        Contoh: Logo resmi madrasah/sekolah atau lambang Kabupaten/Kota.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Preview Box Logo Kanan -->
                    <div class="w-20 h-20 bg-white rounded border-2 border-black p-1 shadow-[2px_2px_0px_0px_#000] flex items-center justify-center flex-shrink-0 overflow-hidden">
                        @if(!empty($setting->kop_logo_right) && \Illuminate\Support\Facades\Storage::disk('public')->exists($setting->kop_logo_right))
                            <img id="kop_logo_right_preview" src="{{ asset('storage/' . $setting->kop_logo_right) }}" alt="Logo Kanan" class="w-full h-full object-contain">
                            <span id="kop_logo_right_placeholder" class="hidden text-xs font-bold text-slate-300">LOGO</span>
                        @else
                            <img id="kop_logo_right_preview" src="#" alt="Pratinjau" class="w-full h-full object-contain hidden">
                            <span id="kop_logo_right_placeholder" class="text-[10px] font-bold text-slate-400 text-center">Kanan</span>
                        @endif
                    </div>

                    <!-- Input Logo Kanan -->
                    <div class="flex-1 space-y-1.5">
                        <input type="file" 
                               name="kop_logo_right" 
                               id="kop_logo_right_input"
                               accept="image/png,image/jpeg,image/jpg,image/svg+xml,image/webp" 
                               onchange="previewKopLogo(event, 'kop_logo_right_preview', 'kop_logo_right_placeholder', 'kop_preview_logo_right')" 
                               class="block w-full text-xs text-slate-700 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-2 file:border-black file:text-xs file:font-bold file:bg-[#FFD43B] file:text-black hover:file:bg-[#fcc419] cursor-pointer">
                        <p class="text-[10px] text-slate-500">• Opsional (biarkan kosong jika hanya 1 logo).</p>
                        @if(!empty($setting->kop_logo_right))
                            <label class="inline-flex items-center gap-1.5 text-[11px] font-bold text-red-600 cursor-pointer">
                                <input type="checkbox" name="remove_kop_logo_right" value="1" class="rounded border-black text-red-600">
                                <span>Hapus logo kanan</span>
                            </label>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Interactive Preview (Pratinjau Nyata Kop Surat Standar Cetak) -->
        <div class="space-y-2 pt-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-black uppercase tracking-wider text-black flex items-center gap-1.5">
                    <span>👁️</span> Pratinjau Tampilan Dokumen Cetak / PDF:
                </span>
                <span class="text-[11px] font-semibold text-slate-500">
                    Sesuai standar tata persuratan kedinasan
                </span>
            </div>

            <div class="bg-white border-2 border-black p-6 rounded-lg shadow-[4px_4px_0px_0px_#000] overflow-x-auto">
                <div id="live_kop_preview_box" class="min-w-[650px] p-4 bg-white" style="font-family: 'Times New Roman', Times, serif;">
                    <table style="width: 100%; border-collapse: collapse; border: none; margin: 0; padding: 0;">
                        <tr>
                            <!-- Logo Kiri Preview -->
                            <td id="kop_preview_cell_left" style="width: 90px; vertical-align: middle; text-align: center; padding: 0 10px 0 0;">
                                <img id="kop_preview_logo_left" 
                                     src="{{ $logoLeftExisting && \Illuminate\Support\Facades\Storage::disk('public')->exists($logoLeftExisting) ? asset('storage/' . $logoLeftExisting) : '' }}" 
                                     alt="Logo Kiri" 
                                     class="{{ empty($logoLeftExisting) ? 'hidden' : '' }}"
                                     style="width: 75px; height: 75px; object-fit: contain; display: block; margin: 0 auto;">
                            </td>

                            <!-- Teks Kop Preview -->
                            <td style="vertical-align: middle; text-align: center; padding: 0 4px; line-height: 1.25;">
                                <div id="kop_preview_gov" style="font-size: 13pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; margin: 0;">
                                    {{ old('kop_government_name', $setting->kop_government_name ?? 'KEMENTERIAN AGAMA REPUBLIK INDONESIA') }}
                                </div>
                                <div id="kop_preview_inst" style="font-size: 12pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; margin: 1px 0 0 0;">
                                    {{ old('kop_institution_name', $setting->kop_institution_name ?? 'KANTOR KEMENTERIAN AGAMA KABUPATEN TANGGAMUS') }}
                                </div>
                                <div id="kop_preview_school" style="font-size: 14pt; font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px; margin: 3px 0 0 0;">
                                    {{ old('kop_school_name', $setting->kop_school_name ?? $setting->school_name ?? 'MADRASAH IBTIDAIYAH NEGERI 2 TANGGAMUS') }}
                                </div>
                                <div id="kop_preview_contact" style="font-size: 9pt; font-weight: normal; color: #222; margin-top: 4px; line-height: 1.3;">
                                    {{ $setting->kop_address ?? 'Jl. Lapangan Ampera No. 109' }} {{ !empty($setting->kop_postal_code) ? 'Kode Pos ' . $setting->kop_postal_code : 'Kode Pos 35384' }}<br>
                                    Email: {{ $setting->kop_email ?? 'minduatanggamus@gmail.com' }}
                                </div>
                            </td>

                            <!-- Logo Kanan Preview -->
                            <td id="kop_preview_cell_right" style="width: 90px; vertical-align: middle; text-align: center; padding: 0 0 0 10px;">
                                <img id="kop_preview_logo_right" 
                                     src="{{ !empty($setting->kop_logo_right) && \Illuminate\Support\Facades\Storage::disk('public')->exists($setting->kop_logo_right) ? asset('storage/' . $setting->kop_logo_right) : '' }}" 
                                     alt="Logo Kanan" 
                                     class="{{ empty($setting->kop_logo_right) ? 'hidden' : '' }}"
                                     style="width: 75px; height: 75px; object-fit: contain; display: block; margin: 0 auto;">
                            </td>
                        </tr>
                    </table>

                    <!-- Garis Batas Preview -->
                    <div id="kop_preview_border_double" class="{{ old('kop_border_style', $setting->kop_border_style ?? 'double') === 'double' ? '' : 'hidden' }}">
                        <div style="border-top: 2.5px solid #000; margin-top: 8px;"></div>
                        <div style="border-top: 1px solid #000; margin-top: 2px;"></div>
                    </div>
                    <div id="kop_preview_border_single" class="{{ old('kop_border_style', $setting->kop_border_style) === 'single' ? '' : 'hidden' }}">
                        <div style="border-top: 2px solid #000; margin-top: 8px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol Simpan -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t-2 border-slate-200">
            <button type="submit" 
                    name="tab" 
                    value="kop" 
                    class="neo-btn bg-[#20C997] hover:bg-[#12b886] text-black px-6 py-3 text-sm uppercase tracking-wider font-heading cursor-pointer shadow-[3px_3px_0px_0px_#000]">
                💾 Simpan Pengaturan Kop Surat
            </button>
        </div>
    </div>
</div>
