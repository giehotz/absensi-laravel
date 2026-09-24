@php
    $defaultSchoolLogo = !empty($schoolSetting->logo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($schoolSetting->logo)
        ? asset('storage/' . $schoolSetting->logo)
        : null;
@endphp

<form action="{{ $actionRoute }}" method="POST" enctype="multipart/form-data" id="qrGeneratorForm">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- KOLOM KIRI: FORMULIR INPUT --}}
        <div class="lg:col-span-8 space-y-6">
            <div class="bg-white border-3 border-black rounded-2xl p-6 shadow-[5px_5px_0px_0px_#000000]">
                {{-- Judul Card Form --}}
                <div class="flex items-center gap-2 pb-4 mb-5 border-b-2 border-slate-200">
                    <span class="w-3.5 h-3.5 rounded-full bg-[#5294FF] border border-black shadow-[1px_1px_0px_#000]"></span>
                    <h2 class="font-heading font-black text-lg text-slate-900 tracking-tight">Form QR Code</h2>
                </div>

                {{-- Alert Error Validasi --}}
                @if ($errors->any())
                    <div class="mb-5 p-4 bg-[#FFE3E3] border-2 border-black rounded-xl shadow-[3px_3px_0px_0px_#000]">
                        <div class="font-heading font-bold text-xs text-rose-900 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-rose-700" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            Terdapat kesalahan pengisian:
                        </div>
                        <ul class="text-xs text-rose-800 list-disc list-inside space-y-0.5 font-medium">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="space-y-5">
                    {{-- 1. URL Tujuan --}}
                    <div>
                        <label for="target_url" class="block font-heading font-bold text-xs uppercase tracking-wider text-slate-800 mb-1.5">
                            URL Tujuan <span class="text-rose-600 font-black">*</span>
                        </label>
                        <input type="url" 
                               name="target_url" 
                               id="target_url" 
                               required
                               placeholder="https://drive.google.com/drive/folders/"
                               value="{{ old('target_url') }}"
                               class="w-full px-4 py-2.5 bg-slate-50 border-2 border-black rounded-xl font-medium text-sm text-slate-900 shadow-[2px_2px_0px_0px_#000] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#5294FF] focus:border-black transition-all">
                        <p class="text-[11px] text-slate-500 mt-1 font-medium">Masukkan link/tautan website, form presensi, materi, atau drive tujuan.</p>
                    </div>

                    {{-- 2. Nama Label --}}
                    <div>
                        <label for="title" class="block font-heading font-bold text-xs uppercase tracking-wider text-slate-800 mb-1.5">
                            Nama Label (Opsional)
                        </label>
                        <input type="text" 
                               name="title" 
                               id="title" 
                               placeholder="Contoh: Modul Bahasa...."
                               value="{{ old('title') }}"
                               maxlength="100"
                               class="w-full px-4 py-2.5 bg-slate-50 border-2 border-black rounded-xl font-medium text-sm text-slate-900 shadow-[2px_2px_0px_0px_#000] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#5294FF] focus:border-black transition-all">
                    </div>

                    {{-- Section Divider: Kustomisasi --}}
                    <div class="pt-4 border-t-2 border-slate-200">
                        <h3 class="font-heading font-black text-sm text-slate-900 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <span>🎨</span> Kustomisasi (Opsional)
                        </h3>

                        {{-- Ukuran & Warna Grid --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            {{-- Ukuran (px) --}}
                            <div>
                                <label for="size" class="block font-heading font-bold text-xs text-slate-700 mb-1.5">
                                    Ukuran (px)
                                </label>
                                <input type="number" 
                                       name="size" 
                                       id="size" 
                                       value="{{ old('size', 300) }}" 
                                       min="100" 
                                       max="1000" 
                                       step="10"
                                       class="w-full px-3 py-2 bg-slate-50 border-2 border-black rounded-xl font-bold text-sm text-slate-900 shadow-[2px_2px_0px_0px_#000] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#5294FF] focus:border-black transition-all">
                                <span class="text-[10px] text-slate-500 font-bold block mt-1">Min: 100, Max: 1000</span>
                            </div>

                            {{-- Warna QR --}}
                            <div>
                                <label for="qr_color" class="block font-heading font-bold text-xs text-slate-700 mb-1.5">
                                    Warna QR
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="color" 
                                           name="qr_color" 
                                           id="qr_color" 
                                           value="{{ old('qr_color', '#000000') }}"
                                           class="w-12 h-10 p-0.5 bg-white border-2 border-black rounded-xl shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                                    <input type="text" 
                                           id="qr_color_text" 
                                           value="{{ old('qr_color', '#000000') }}" 
                                           readonly 
                                           class="flex-1 px-3 py-2 bg-slate-100 border-2 border-black rounded-xl font-mono text-xs font-bold text-slate-800 shadow-[2px_2px_0px_0px_#000]">
                                </div>
                            </div>

                            {{-- Warna Background --}}
                            <div>
                                <label for="bg_color" class="block font-heading font-bold text-xs text-slate-700 mb-1.5">
                                    Warna Background
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="color" 
                                           name="bg_color" 
                                           id="bg_color" 
                                           value="{{ old('bg_color', '#ffffff') }}"
                                           class="w-12 h-10 p-0.5 bg-white border-2 border-black rounded-xl shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                                    <input type="text" 
                                           id="bg_color_text" 
                                           value="{{ old('bg_color', '#ffffff') }}" 
                                           readonly 
                                           class="flex-1 px-3 py-2 bg-slate-100 border-2 border-black rounded-xl font-mono text-xs font-bold text-slate-800 shadow-[2px_2px_0px_0px_#000]">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Opsi Logo --}}
                    <div class="pt-3">
                        <label class="block font-heading font-bold text-xs uppercase tracking-wider text-slate-800 mb-2">
                            Opsi Logo
                        </label>
                        <div class="space-y-2 bg-slate-50 p-4 border-2 border-black rounded-xl shadow-[2px_2px_0px_0px_#000]">
                            {{-- 1. Tanpa Logo --}}
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="radio" 
                                       name="logo_type" 
                                       value="none" 
                                       {{ old('logo_type', empty($defaultSchoolLogo) ? 'none' : 'default') === 'none' ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 border-2 border-black focus:ring-0 cursor-pointer">
                                <span class="font-bold text-xs text-slate-800 group-hover:text-black">Tanpa Logo</span>
                            </label>

                            {{-- 2. Logo Default Sekolah --}}
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="radio" 
                                       name="logo_type" 
                                       value="default" 
                                       {{ old('logo_type', !empty($defaultSchoolLogo) ? 'default' : 'none') === 'default' ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 border-2 border-black focus:ring-0 cursor-pointer">
                                <span class="font-bold text-xs text-slate-800 group-hover:text-black flex items-center gap-2">
                                    Gunakan Logo Default Sekolah
                                    @if(!empty($defaultSchoolLogo))
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 bg-white border border-black rounded text-[10px] font-bold shadow-[1px_1px_0px_#000]">
                                            <img src="{{ $defaultSchoolLogo }}" alt="Default Logo" class="w-4 h-4 object-contain">
                                            <span>Default Logo</span>
                                        </span>
                                    @else
                                        <span class="text-[10px] text-slate-400 italic">(Belum diatur di Pengaturan)</span>
                                    @endif
                                </span>
                            </label>

                            {{-- 3. Upload Logo Kustom --}}
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="radio" 
                                       name="logo_type" 
                                       value="custom" 
                                       {{ old('logo_type') === 'custom' ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 border-2 border-black focus:ring-0 cursor-pointer">
                                <span class="font-bold text-xs text-slate-800 group-hover:text-black">Upload Logo Kustom</span>
                            </label>

                            {{-- Input File Kustom (Muncul jika opsi custom dipilih) --}}
                            <div id="custom_logo_container" class="{{ old('logo_type') === 'custom' ? '' : 'hidden' }} pt-2 pl-7">
                                <input type="file" 
                                       name="custom_logo" 
                                       id="custom_logo" 
                                       accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml"
                                       class="block w-full text-xs text-slate-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-2 file:border-black file:font-bold file:text-xs file:bg-[#FFD43B] file:text-black hover:file:bg-yellow-400 file:cursor-pointer cursor-pointer border border-dashed border-slate-400 rounded-lg p-2 bg-white">
                                <p class="text-[10px] text-slate-500 mt-1 font-medium">Format: PNG, JPG, WEBP, SVG (Maks. 2MB). Disarankan rasio 1:1 (persegi).</p>
                            </div>
                        </div>
                    </div>

                    {{-- Gaya Frame --}}
                    <div>
                        <label for="frame_style" class="block font-heading font-bold text-xs uppercase tracking-wider text-slate-800 mb-1.5">
                            Gaya Frame
                        </label>
                        <select name="frame_style" 
                                id="frame_style" 
                                class="w-full px-4 py-2.5 bg-slate-50 border-2 border-black rounded-xl font-bold text-sm text-slate-900 shadow-[2px_2px_0px_0px_#000] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#5294FF] focus:border-black transition-all cursor-pointer">
                            <option value="default" {{ old('frame_style') === 'default' ? 'selected' : '' }}>Kotak (Default)</option>
                            <option value="neo_brutalism" {{ old('frame_style') === 'neo_brutalism' ? 'selected' : '' }}>Frame Neo-Brutalism (Border Tebal & Solid Shadow)</option>
                            <option value="scan_me" {{ old('frame_style') === 'scan_me' ? 'selected' : '' }}>Frame Banner "Scan Me" / "Pindai Saya"</option>
                        </select>
                    </div>

                    {{-- Checkbox Tampilkan Label pada QR --}}
                    <div class="pt-1">
                        <label class="flex items-center gap-3 cursor-pointer group select-none">
                            <input type="checkbox" 
                                   name="show_label" 
                                   id="show_label" 
                                   value="1" 
                                   {{ old('show_label') ? 'checked' : '' }}
                                   class="w-4 h-4 rounded text-blue-600 border-2 border-black focus:ring-0 cursor-pointer shadow-[1px_1px_0px_#000]">
                            <span class="font-bold text-xs text-slate-800 group-hover:text-black">
                                Tampilkan Label (Nama) pada QR
                            </span>
                        </label>
                    </div>

                    {{-- Tombol Submit Generate --}}
                    <div class="pt-4">
                        <button type="submit" 
                                class="w-full py-3.5 px-6 bg-[#1877F2] hover:bg-[#166fe5] text-white font-heading font-black text-sm uppercase tracking-wider rounded-xl border-3 border-black shadow-[4px_4px_0px_0px_#000000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[2px_2px_0px_0px_#000000] active:translate-x-1 active:translate-y-1 active:shadow-none transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M3 3h8v8H3V3zm2 2v4h4V5H5zm8-2h8v8h-8V3zm2 2v4h4V5h-4zM3 13h8v8H3v-8zm2 2v4h4v-4H5zm13-2h3v2h-3v-2zm-3 2h2v3h-2v-3zm2 3h3v3h-3v-3zm3-3h2v2h-2v-2zm-5 3h2v3h-2v-3zm-2-2h2v2h-2v-2z"/>
                            </svg>
                            <span>Generate QR Code</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: LIVE PREVIEW & INFORMASI --}}
        <div class="lg:col-span-4 space-y-6">
            {{-- Card 1: Live Preview --}}
            <div class="bg-white border-3 border-black rounded-2xl p-6 shadow-[5px_5px_0px_0px_#000000]">
                <div class="flex items-center gap-2 pb-3 mb-4 border-b-2 border-slate-200">
                    <span class="w-3 h-3 rounded-full bg-[#20C997] border border-black shadow-[1px_1px_0px_#000]"></span>
                    <h3 class="font-heading font-black text-sm uppercase tracking-wider text-slate-900">Live Preview</h3>
                </div>

                <div id="previewBoxWrapper" class="flex flex-col items-center justify-center min-h-[300px] p-4 bg-slate-50 border-2 border-dashed border-slate-300 rounded-xl transition-all">
                    {{-- Empty State --}}
                    <div id="previewEmptyState" class="text-center py-10 px-4">
                        <div class="w-12 h-12 mx-auto mb-3 text-slate-300">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-full h-full">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                            </svg>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">Masukkan URL untuk melihat preview</p>
                    </div>

                    {{-- Container QR Preview Dinamis --}}
                    <div id="previewQrContainer" class="hidden flex flex-col items-center">
                        {{-- Frame Banner Top (jika scan_me) --}}
                        <div id="previewFrameBannerTop" class="hidden w-full bg-[#FFD43B] text-black font-heading font-black text-[11px] text-center uppercase tracking-wider py-1.5 px-3 border-2 border-black rounded-t-xl shadow-[2px_2px_0px_#000] mb-[-2px] z-10">
                            ⚡ SCAN ME / PINDAI SAYA
                        </div>

                        {{-- Frame Box Utama --}}
                        <div id="previewFrameBox" class="relative p-4 bg-white transition-all">
                            {{-- Area Canvas QRCode --}}
                            <div id="qrcodeCanvas" class="flex items-center justify-center relative"></div>

                            {{-- Overlay Logo di Tengah --}}
                            <div id="previewLogoOverlay" class="hidden absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg p-1 border border-black shadow-[1px_1px_0px_#000] pointer-events-none">
                                <img id="previewLogoImg" src="" alt="Logo" class="w-8 h-8 object-contain rounded">
                            </div>
                        </div>

                        {{-- Label Bawah --}}
                        <div id="previewLabelText" class="hidden font-heading font-bold text-xs text-center text-slate-900 mt-2 px-2 max-w-[220px] truncate"></div>

                        {{-- Frame Banner Bottom (jika scan_me) --}}
                        <div id="previewFrameBannerBottom" class="hidden w-full bg-[#1877F2] text-white font-heading font-black text-[10px] text-center uppercase tracking-wider py-1 px-3 border-2 border-black rounded-b-xl shadow-[2px_2px_0px_#000] mt-[-2px]">
                            Absensi & KBM Digital
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 2: Informasi --}}
            <div class="bg-white border-3 border-black rounded-2xl p-6 shadow-[5px_5px_0px_0px_#000000]">
                <div class="flex items-center gap-2 pb-3 mb-4 border-b-2 border-slate-200">
                    <span class="w-3 h-3 rounded-full bg-[#38D9A9] border border-black shadow-[1px_1px_0px_#000]"></span>
                    <h3 class="font-heading font-black text-sm uppercase tracking-wider text-[#0CA678]">Informasi</h3>
                </div>

                <div class="text-xs text-slate-700 space-y-3 leading-relaxed">
                    <p class="font-medium">
                        Fitur ini memungkinkan Anda membuat QR Code untuk berbagai keperluan, seperti:
                    </p>
                    <ul class="list-disc list-inside space-y-1.5 text-slate-800 font-semibold pl-1">
                        <li>Link Absensi Siswa</li>
                        <li>Materi Pembelajaran</li>
                        <li>Pengumuman Kelas</li>
                        <li>Formulir Online</li>
                    </ul>
                    <div class="p-3 bg-[#E7F5FF] border-2 border-black rounded-xl text-blue-950 font-medium text-[11px] shadow-[2px_2px_0px_#000] mt-3">
                        ℹ️ <strong>QR Code yang dibuat</strong> akan tersimpan otomatis di sistem dan dapat diunduh (PNG & SVG) kapan saja.
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Client-Side QR Library for Live Preview --}}
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const targetUrlInput = document.getElementById('target_url');
    const titleInput = document.getElementById('title');
    const sizeInput = document.getElementById('size');
    const qrColorInput = document.getElementById('qr_color');
    const qrColorText = document.getElementById('qr_color_text');
    const bgColorInput = document.getElementById('bg_color');
    const bgColorText = document.getElementById('bg_color_text');
    const frameStyleSelect = document.getElementById('frame_style');
    const showLabelCheckbox = document.getElementById('show_label');
    const customLogoInput = document.getElementById('custom_logo');
    const customLogoContainer = document.getElementById('custom_logo_container');
    const radioLogoTypes = document.querySelectorAll('input[name="logo_type"]');

    const previewBoxWrapper = document.getElementById('previewBoxWrapper');
    const previewEmptyState = document.getElementById('previewEmptyState');
    const previewQrContainer = document.getElementById('previewQrContainer');
    const previewFrameBox = document.getElementById('previewFrameBox');
    const previewFrameBannerTop = document.getElementById('previewFrameBannerTop');
    const previewFrameBannerBottom = document.getElementById('previewFrameBannerBottom');
    const qrcodeCanvas = document.getElementById('qrcodeCanvas');
    const previewLogoOverlay = document.getElementById('previewLogoOverlay');
    const previewLogoImg = document.getElementById('previewLogoImg');
    const previewLabelText = document.getElementById('previewLabelText');

    const defaultSchoolLogoUrl = @json($defaultSchoolLogo);
    let customLogoUrl = null;
    let qrInstance = null;

    // Sinkronisasi teks color picker
    qrColorInput.addEventListener('input', function() {
        qrColorText.value = this.value;
        updatePreview();
    });
    bgColorInput.addEventListener('input', function() {
        bgColorText.value = this.value;
        updatePreview();
    });

    // Opsi Logo Radio Handler
    radioLogoTypes.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'custom') {
                customLogoContainer.classList.remove('hidden');
            } else {
                customLogoContainer.classList.add('hidden');
            }
            updatePreview();
        });
    });

    // Upload Logo Kustom Preview
    if (customLogoInput) {
        customLogoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    customLogoUrl = evt.target.result;
                    updatePreview();
                };
                reader.readAsDataURL(file);
            } else {
                customLogoUrl = null;
                updatePreview();
            }
        });
    }

    // Input listeners
    [targetUrlInput, titleInput, sizeInput, frameStyleSelect, showLabelCheckbox].forEach(el => {
        if (el) {
            el.addEventListener('input', updatePreview);
            el.addEventListener('change', updatePreview);
        }
    });

    function getSelectedLogoType() {
        const checked = document.querySelector('input[name="logo_type"]:checked');
        return checked ? checked.value : 'none';
    }

    function updatePreview() {
        const url = (targetUrlInput.value || '').trim();

        if (!url) {
            previewEmptyState.classList.remove('hidden');
            previewQrContainer.classList.add('hidden');
            previewBoxWrapper.classList.add('border-dashed');
            previewBoxWrapper.classList.remove('border-solid');
            return;
        }

        previewEmptyState.classList.add('hidden');
        previewQrContainer.classList.remove('hidden');
        previewBoxWrapper.classList.remove('border-dashed');
        previewBoxWrapper.classList.add('border-solid');

        const qrColor = qrColorInput.value || '#000000';
        const bgColor = bgColorInput.value || '#ffffff';
        const frameStyle = frameStyleSelect.value || 'default';
        const showLabel = showLabelCheckbox.checked;
        const titleText = (titleInput.value || '').trim();
        const logoType = getSelectedLogoType();

        // Render QR Code via QRCode.js
        qrcodeCanvas.innerHTML = '';
        if (typeof QRCode !== 'undefined') {
            qrInstance = new QRCode(qrcodeCanvas, {
                text: url,
                width: 180,
                height: 180,
                colorDark: qrColor,
                colorLight: bgColor,
                correctLevel: (logoType !== 'none') ? QRCode.CorrectLevel.H : QRCode.CorrectLevel.M
            });
        }

        // Terapkan Gaya Frame pada Box
        previewFrameBox.style.backgroundColor = bgColor;
        if (frameStyle === 'neo_brutalism') {
            previewFrameBox.className = 'relative p-4 border-3 border-black rounded-2xl shadow-[4px_4px_0px_#000]';
            previewFrameBannerTop.classList.add('hidden');
            previewFrameBannerBottom.classList.add('hidden');
        } else if (frameStyle === 'scan_me') {
            previewFrameBox.className = 'relative p-4 border-2 border-black';
            previewFrameBannerTop.classList.remove('hidden');
            previewFrameBannerBottom.classList.remove('hidden');
        } else {
            // Default Kotak
            previewFrameBox.className = 'relative p-3 border-2 border-slate-300 rounded-xl shadow-sm';
            previewFrameBannerTop.classList.add('hidden');
            previewFrameBannerBottom.classList.add('hidden');
        }

        // Terapkan Logo Overlay
        let currentLogo = null;
        if (logoType === 'default' && defaultSchoolLogoUrl) {
            currentLogo = defaultSchoolLogoUrl;
        } else if (logoType === 'custom' && customLogoUrl) {
            currentLogo = customLogoUrl;
        }

        if (currentLogo) {
            previewLogoImg.src = currentLogo;
            previewLogoOverlay.classList.remove('hidden');
        } else {
            previewLogoOverlay.classList.add('hidden');
        }

        // Terapkan Label Text
        if (showLabel && titleText) {
            previewLabelText.textContent = titleText;
            previewLabelText.style.color = qrColor;
            previewLabelText.classList.remove('hidden');
        } else {
            previewLabelText.classList.add('hidden');
        }
    }

    // Trigger update pertama kali jika ada old input
    if (targetUrlInput.value) {
        updatePreview();
    }
});
</script>
@endpush
