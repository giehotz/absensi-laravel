@extends('layouts.admin')

@section('title', 'Pengaturan Desain Kartu Siswa')
@section('page-title', 'Studio Desain & Pengaturan Kartu Pelajar')

@section('content')
<div class="space-y-6 pb-12">
    {{-- Header Banner & Tombol Kembali --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white border-2 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000]">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                <a href="{{ route('admin.students.cards') }}" class="hover:text-black">Kartu Siswa</a>
                <span>/</span>
                <span class="text-black">Pengaturan Desain</span>
            </div>
            <h2 class="font-heading font-black text-2xl text-black">Studio Desain Kartu Tanda Pelajar</h2>
            <p class="text-xs text-slate-600 mt-0.5">
                Sesuaikan identitas, dimensi standar (8,7 × 5,4 cm), warna tema, QR verifikasi, dan format sisi belakang kartu.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.students.cards') }}" 
               class="neo-btn bg-slate-100 hover:bg-slate-200 text-black px-4 py-2 text-xs font-bold flex items-center gap-1.5 shadow-[2px_2px_0px_#000]">
                <span>←</span>
                <span>Kembali ke Studio Kartu</span>
            </a>
        </div>
    </div>

    {{-- Alert Pesan Sukses / Error --}}
    @if(session('success'))
        <div class="bg-[#D3F9D8] border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_#000] flex items-center gap-2.5 text-xs font-bold text-emerald-950">
            <span class="text-lg">✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-[#FFE3E3] border-2 border-black p-4 rounded-lg shadow-[3px_3px_0px_#000] text-xs font-bold text-rose-950 space-y-1">
            <div class="flex items-center gap-2 font-black">
                <span>⚠️</span> Terdapat beberapa kesalahan input:
            </div>
            <ul class="list-disc list-inside pl-4 font-normal">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- KOLOM KIRI: Form Pengaturan Desain Kartu --}}
        <div class="lg:col-span-7 bg-white border-2 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000] space-y-5">
            <div class="border-b-2 border-black pb-3">
                <h3 class="font-heading font-black text-base text-black flex items-center gap-2">
                    <span class="w-3 h-3 bg-[#FFD43B] border border-black inline-block"></span>
                    Konfigurasi Template Kartu Fisik & Digital
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Pengaturan di bawah ini menjadi <b>sumber tunggal (Single Source of Truth)</b> untuk seluruh kartu di portal Admin, Guru, Siswa, dan Lembar Cetak.
                </p>
            </div>

            <form action="{{ route('admin.students.cards.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')

                {{-- Grup 1: Identitas Header Kartu --}}
                <div class="space-y-3 p-3.5 bg-slate-50 border-2 border-black rounded-lg">
                    <div class="text-xs font-black uppercase text-black flex items-center gap-1.5">
                        <span>🏫</span> Identitas Header Bagian Depan
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-black uppercase mb-1">
                            Nama Madrasah / Sekolah di Kartu <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="card_school_name" 
                               value="{{ old('card_school_name', $setting->card_school_name ?: $setting->school_name) }}" 
                               placeholder="Contoh: MIN 2 TANGGAMUS" 
                               required
                               class="w-full bg-white border-2 border-black px-3 py-2 text-xs font-bold focus:outline-hidden focus:ring-2 focus:ring-black">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-black uppercase mb-1">
                                Judul / Jenis Kartu <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="card_title" 
                                   value="{{ old('card_title', $setting->resolved_card_title) }}" 
                                   placeholder="Contoh: KARTU SISWA" 
                                   required
                                   class="w-full bg-white border-2 border-black px-3 py-2 text-xs font-bold focus:outline-hidden focus:ring-2 focus:ring-black">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-black uppercase mb-1">
                                Teks Masa Berlaku (Footer Depan) <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="card_validity_text" 
                                   value="{{ old('card_validity_text', $setting->resolved_card_validity_text) }}" 
                                   placeholder="Contoh: BERLAKU SELAMA MENJADI SISWA" 
                                   required
                                   class="w-full bg-white border-2 border-black px-3 py-2 text-xs font-bold focus:outline-hidden focus:ring-2 focus:ring-black">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-black uppercase mb-1">
                            Logo Header Kartu (Opsional, timpa logo sekolah utama)
                        </label>
                        <input type="file" 
                               name="card_logo" 
                               accept="image/png,image/jpeg,image/svg+xml,image/webp"
                               class="w-full bg-white border-2 border-black px-3 py-1.5 text-xs font-bold">
                        <span class="text-[10px] text-slate-500 block mt-1">Format: PNG, JPG, SVG, WebP. Maks 2MB. Disarankan logo transparan.</span>
                    </div>
                </div>

                {{-- Grup 2: Dimensi & Warna Tema --}}
                <div class="space-y-3 p-3.5 bg-slate-50 border-2 border-black rounded-lg">
                    <div class="text-xs font-black uppercase text-black flex items-center gap-1.5">
                        <span>📏</span> Dimensi Fisik & Warna Aksen Kartu
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-black uppercase mb-1">
                                Lebar Kartu (cm) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" 
                                   step="0.01" 
                                   name="card_width_cm" 
                                   value="{{ old('card_width_cm', $setting->card_width_cm ?: 8.70) }}" 
                                   required
                                   class="w-full bg-white border-2 border-black px-3 py-2 text-xs font-mono font-bold focus:outline-hidden focus:ring-2 focus:ring-black">
                            <span class="text-[10px] text-slate-500">Standar ID: 8.70 cm</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-black uppercase mb-1">
                                Tinggi Kartu (cm) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" 
                                   step="0.01" 
                                   name="card_height_cm" 
                                   value="{{ old('card_height_cm', $setting->card_height_cm ?: 5.40) }}" 
                                   required
                                   class="w-full bg-white border-2 border-black px-3 py-2 text-xs font-mono font-bold focus:outline-hidden focus:ring-2 focus:ring-black">
                            <span class="text-[10px] text-slate-500">Standar ID: 5.40 cm</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-black uppercase mb-1">
                                Warna Aksen Tema <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <input type="color" 
                                       id="themeColorPicker"
                                       name="card_theme_color" 
                                       value="{{ old('card_theme_color', $setting->card_theme_color ?: '#20C997') }}" 
                                       class="w-10 h-9 p-0.5 border-2 border-black rounded cursor-pointer shrink-0">
                                <input type="text" 
                                       id="themeColorText"
                                       value="{{ old('card_theme_color', $setting->card_theme_color ?: '#20C997') }}" 
                                       readonly
                                       class="w-full bg-white border-2 border-black px-2 py-2 text-xs font-mono font-bold text-center">
                            </div>
                        </div>
                    </div>

                    {{-- Chips Pilihan Warna Populer --}}
                    <div class="flex items-center gap-2 pt-1 text-[11px] font-bold">
                        <span class="text-slate-500 text-[10px] uppercase">Preset:</span>
                        <button type="button" onclick="setPresetColor('#20C997')" class="px-2 py-0.5 bg-[#20C997] text-black border border-black rounded text-[10px] font-bold shadow-[1px_1px_0px_#000]">Hijau Kemenag</button>
                        <button type="button" onclick="setPresetColor('#5294FF')" class="px-2 py-0.5 bg-[#5294FF] text-white border border-black rounded text-[10px] font-bold shadow-[1px_1px_0px_#000]">Biru Madrasah</button>
                        <button type="button" onclick="setPresetColor('#FFD43B')" class="px-2 py-0.5 bg-[#FFD43B] text-black border border-black rounded text-[10px] font-bold shadow-[1px_1px_0px_#000]">Kuning Emas</button>
                        <button type="button" onclick="setPresetColor('#000000')" class="px-2 py-0.5 bg-black text-white border border-black rounded text-[10px] font-bold shadow-[1px_1px_0px_#000]">Hitam Monokrom</button>
                    </div>
                </div>

                {{-- Grup 3: Sisi Belakang (Scan Presensi) --}}
                <div class="space-y-3 p-3.5 bg-slate-50 border-2 border-black rounded-lg">
                    <div class="text-xs font-black uppercase text-black flex items-center gap-1.5">
                        <span>📲</span> Konfigurasi Sisi Belakang (Scan Presensi)
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-black uppercase mb-1">
                            Petunjuk / Instruksi Scan di Bawah QR
                        </label>
                        <textarea name="card_back_instructions" 
                                  rows="2" 
                                  class="w-full bg-white border-2 border-black px-3 py-2 text-xs font-medium focus:outline-hidden focus:ring-2 focus:ring-black">{{ old('card_back_instructions', $setting->resolved_card_back_instructions) }}</textarea>
                    </div>

                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-2 text-xs font-bold text-black cursor-pointer select-none">
                            <input type="checkbox" 
                                   name="card_show_back_token" 
                                   value="1" 
                                   {{ old('card_show_back_token', $setting->card_show_back_token ?? true) ? 'checked' : '' }}
                                   class="w-4 h-4 border-2 border-black rounded text-black cursor-pointer">
                            <span>Tampilkan Kotak Token / Identifier Siswa di Bawah QR</span>
                        </label>
                    </div>
                </div>

                {{-- Grup 4: Tanda Tangan / Kepala Madrasah (Opsional) --}}
                <div class="space-y-3 p-3.5 bg-slate-50 border-2 border-black rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="text-xs font-black uppercase text-black flex items-center gap-1.5">
                            <span>✍️</span> Pengesahan Kepala Madrasah / Sekolah (Opsional)
                        </div>
                        <label class="flex items-center gap-1.5 text-xs font-bold text-black cursor-pointer">
                            <input type="checkbox" 
                                   id="toggleSignature"
                                   name="card_show_signature" 
                                   value="1" 
                                   {{ old('card_show_signature', $setting->card_show_signature) ? 'checked' : '' }}
                                   onchange="toggleSignatureFields(this.checked)"
                                   class="w-4 h-4 border-2 border-black rounded text-black cursor-pointer">
                            <span>Aktifkan</span>
                        </label>
                    </div>

                    <div id="signatureFieldsContainer" class="{{ old('card_show_signature', $setting->card_show_signature) ? '' : 'hidden' }} space-y-3 pt-2 border-t border-slate-200">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-black uppercase mb-1">
                                    Nama Kepala Madrasah
                                </label>
                                <input type="text" 
                                       name="card_principal_name" 
                                       value="{{ old('card_principal_name', $setting->card_principal_name) }}" 
                                       placeholder="Contoh: Drs. H. Ahmad Subarjo, M.Pd." 
                                       class="w-full bg-white border-2 border-black px-3 py-2 text-xs font-bold">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-black uppercase mb-1">
                                    NIP Kepala Madrasah
                                </label>
                                <input type="text" 
                                       name="card_principal_nip" 
                                       value="{{ old('card_principal_nip', $setting->card_principal_nip) }}" 
                                       placeholder="Contoh: 19750812 200003 1 002" 
                                       class="w-full bg-white border-2 border-black px-3 py-2 text-xs font-mono font-bold">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-black uppercase mb-1">
                                Gambar Tanda Tangan / Stempel Digital (Transparan PNG)
                            </label>
                            <input type="file" 
                                   name="card_signature_image" 
                                   accept="image/png,image/webp"
                                   class="w-full bg-white border-2 border-black px-3 py-1.5 text-xs font-bold">
                        </div>
                    </div>
                </div>

                {{-- Tombol Aksi Simpan --}}
                <div class="pt-2 flex items-center justify-end gap-3 border-t-2 border-black">
                    <a href="{{ route('admin.students.cards') }}" class="neo-btn bg-slate-100 hover:bg-slate-200 text-black px-4 py-2 text-xs font-bold">
                        Batal
                    </a>
                    <button type="submit" 
                            class="neo-btn bg-[#20C997] hover:bg-[#1bb386] text-black px-6 py-2.5 text-xs font-black flex items-center gap-2 shadow-[3px_3px_0px_#000] cursor-pointer">
                        <span>💾</span>
                        <span>Simpan Perubahan Desain Kartu</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- KOLOM KANAN: Live Interactive Preview 2 Sisi --}}
        <div class="lg:col-span-5 space-y-4 lg:sticky lg:top-4">
            <div class="bg-white border-2 border-black p-5 rounded-lg shadow-[4px_4px_0px_0px_#000] space-y-4">
                <div class="flex items-center justify-between border-b-2 border-black pb-2.5">
                    <div>
                        <h3 class="font-heading font-black text-sm uppercase text-black flex items-center gap-1.5">
                            <span>👁️</span> Pratinjau Interaktif 2 Sisi
                        </h3>
                        <p class="text-[11px] text-slate-500 font-medium">
                            Tampilan visual kartu fisik dengan ukuran skala 8,7 × 5,4 cm.
                        </p>
                    </div>
                    <span class="text-[9px] font-mono font-black bg-black text-white px-2 py-0.5 rounded uppercase">
                        LIVE PREVIEW
                    </span>
                </div>

                {{-- Komponen Kartu Siswa --}}
                <div class="p-3 bg-slate-100 border-2 border-black rounded-lg flex flex-col items-center justify-center min-h-[300px] overflow-hidden">
                    <x-student-card :student="$sampleStudent" :setting="$setting" side="both" interactive="true" />
                </div>

                {{-- Info Fitur QR --}}
                <div class="space-y-2 text-xs bg-[#E7F5FF] border-2 border-black p-3.5 rounded-lg text-blue-950 font-medium">
                    <div class="font-heading font-black text-xs uppercase flex items-center gap-1.5">
                        <span>💡</span> Mekanisme Dua QR Code:
                    </div>
                    <ul class="space-y-1 text-[11px] list-disc list-inside">
                        <li><b>QR Bagian Depan (Validasi):</b> Saat di-scan menggunakan kamera HP publik, otomatis membuka tautan verifikasi keabsahan data siswa di portal madrasah.</li>
                        <li><b>QR Bagian Belakang (Presensi):</b> Digunakan siswa untuk di-scan pada scanner gerbang/kios presensi sekolah saat datang dan pulang.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function setPresetColor(hex) {
        document.getElementById('themeColorPicker').value = hex;
        document.getElementById('themeColorText').value = hex;
    }

    document.getElementById('themeColorPicker').addEventListener('input', function(e) {
        document.getElementById('themeColorText').value = e.target.value;
    });

    function toggleSignatureFields(show) {
        const container = document.getElementById('signatureFieldsContainer');
        if (show) {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
    }
</script>
@endpush
@endsection
