@props([
    'student',
    'setting' => null,
    'side' => 'both', // 'front', 'back', 'both'
    'orientation' => 'auto', // 'landscape', 'portrait', 'auto'
    'size' => 'standard', // 'standard', 'compact', 'print'
    'interactive' => false, // jika true, ada tombol flip interaktif depan/belakang
    'cardId' => null,
])

@php
    $cardService = app(\App\Services\StudentCardService::class);
    $setting = $setting ?? $cardService->getSetting();

    // Pastikan QR data URI dan relasi siswa siap
    if (empty($student->front_qr_data_uri) || empty($student->back_qr_data_uri)) {
        $student = $cardService->prepareStudent($student);
    }

    $uniqueId = $cardId ?? 'card_' . $student->id . '_' . \Illuminate\Support\Str::random(4);

    // Resolusi data sekolah & kartu
    $schoolName = $setting->resolved_card_school_name;
    $cardTitle = $setting->resolved_card_title;
    $validityText = $setting->resolved_card_validity_text;
    $backInstructions = $setting->resolved_card_back_instructions;
    $themeColor = $setting->card_theme_color ?: '#20C997';
    $cardLogoUrl = $setting->card_logo_url;

    // Format Data Siswa
    $studentName = strtoupper($student->user?->name ?? 'NAMA SISWA');
    $genderLabel = match($student->gender) {
        'L' => 'Laki-laki',
        'P' => 'Perempuan',
        default => '-'
    };
    $ttl = trim(($student->birth_place ?? '') . ($student->birth_date ? ', ' . $student->birth_date->format('d-m-Y') : ''), ', ');
    $ttl = !empty($ttl) ? $ttl : '-';
    $address = !empty($student->address) ? \Illuminate\Support\Str::limit($student->address, 55) : '-';
@endphp

<div class="student-card-wrapper inline-block text-black font-sans select-none" id="{{ $uniqueId }}">
    @if($interactive)
        {{-- Mode Interaktif: Tombol Toggle Flip Sisi Kartu --}}
        <div class="flex items-center justify-between gap-2 mb-2 print:hidden">
            <span class="text-[11px] font-bold text-slate-600 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Ukuran Fisik: {{ $setting->card_width_cm }} × {{ $setting->card_height_cm }} cm</span>
            </span>
            <button type="button" 
                    onclick="toggleCardFlip('{{ $uniqueId }}')" 
                    class="neo-btn bg-[#FFD43B] hover:bg-[#fcc419] text-black px-2.5 py-1 text-xs font-black flex items-center gap-1.5 shadow-[2px_2px_0px_#000] cursor-pointer">
                <span>🔄</span>
                <span id="{{ $uniqueId }}_btn_text">Lihat Sisi Belakang</span>
            </button>
        </div>
    @endif

    <div class="flex flex-wrap items-start justify-center gap-4">
        {{-- ========================================================================= --}}
        {{-- SISI DEPAN: UNTUK INFORMASI & VERIFIKASI RESMI                             --}}
        {{-- ========================================================================= --}}
        @if($side === 'front' || $side === 'both' || $interactive)
            <div id="{{ $uniqueId }}_front" 
                 class="student-card-side card-front bg-white border-2 border-black rounded-xl shadow-[3px_3px_0px_0px_#000] overflow-hidden flex flex-col justify-between relative transition-all duration-200"
                 style="width: 8.7cm; min-width: 8.7cm; height: 5.4cm; min-height: 5.4cm; max-width: 100%; box-sizing: border-box;">
                
                {{-- Header Kartu --}}
                <div class="px-3 pt-2.5 pb-1.5 border-b-2 border-black bg-white flex items-center gap-2.5 shrink-0">
                    {{-- Logo Instansi / Kemenag / Sekolah --}}
                    @if($cardLogoUrl)
                        <div class="w-9 h-9 rounded-md bg-white border border-black flex items-center justify-center p-0.5 shrink-0 overflow-hidden shadow-[1px_1px_0px_#000]">
                            <img src="{{ $cardLogoUrl }}" alt="Logo" class="w-full h-full object-contain">
                        </div>
                    @else
                        <div class="w-9 h-9 rounded-md bg-[#20C997] border border-black flex items-center justify-center font-heading font-black text-sm text-black shrink-0 shadow-[1px_1px_0px_#000]">
                            🏫
                        </div>
                    @endif

                    {{-- Nama Madrasah / Sekolah & Pita Kartu Siswa --}}
                    <div class="min-w-0 flex-1 leading-none">
                        <div class="font-heading font-black text-xs sm:text-[13px] uppercase tracking-tight truncate text-black">
                            {{ $schoolName }}
                        </div>
                        <div class="font-heading font-black text-[10px] tracking-wider uppercase mt-0.5" style="color: {{ $themeColor }};">
                            {{ $cardTitle }}
                        </div>
                    </div>

                    {{-- Badge Status Aktif --}}
                    <span class="text-[7.5px] font-mono font-black bg-black text-white px-1.5 py-0.5 rounded uppercase shrink-0">
                        OFFICIAL
                    </span>
                </div>

                {{-- Konten Biodata Siswa & QR Verifikasi --}}
                <div class="px-3 py-1 flex-1 flex items-center justify-between gap-2 bg-gradient-to-br from-white via-white to-slate-50 min-h-0">
                    {{-- Foto Siswa (3x4) --}}
                    <div class="w-[19mm] h-[25mm] bg-rose-600 border-2 border-black rounded-md flex flex-col items-center justify-center shrink-0 relative overflow-hidden shadow-[1.5px_1.5px_0px_#000]">
                        @if($student->photo_url)
                            <img src="{{ $student->photo_url }}" alt="{{ $studentName }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-[#E03131] flex flex-col items-center justify-center text-white">
                                <span class="text-xl">👤</span>
                                <span class="text-[6.5px] font-bold uppercase mt-0.5">3×4</span>
                            </div>
                        @endif
                    </div>

                    {{-- Identitas Biodata Siswa --}}
                    <div class="flex-1 min-w-0 leading-tight space-y-0.5 text-black">
                        {{-- Nama Siswa (Tampil penuh, wrap 2 baris jika panjang tanpa terpotong) --}}
                        <div class="font-heading font-black text-[9.5px] sm:text-[10px] leading-[1.2] uppercase pb-0.5 text-slate-900 border-b border-slate-200 line-clamp-2 break-words">
                            {{ $studentName }}
                        </div>

                        {{-- Tabel Data Diri: NISM dan NISN Terpisah, Tanpa Kelas --}}
                        <div class="grid grid-cols-[50px_auto] gap-x-1 gap-y-0.5 text-[7.5px] sm:text-[8px] font-bold text-slate-800 leading-tight pt-0.5">
                            <span class="text-slate-600 font-semibold">NISM</span>
                            <span class="font-mono text-black truncate">: {{ $student->nis ?? '-' }}</span>

                            <span class="text-slate-600 font-semibold">NISN</span>
                            <span class="font-mono text-black truncate">: {{ $student->nisn ?? '-' }}</span>

                            <span class="text-slate-600 font-semibold">JENIS KELAMIN</span>
                            <span class="text-black">: {{ $genderLabel }}</span>

                            <span class="text-slate-600 font-semibold">TTL</span>
                            <span class="text-black truncate">: {{ $ttl }}</span>

                            <span class="text-slate-600 font-semibold">ALAMAT</span>
                            <span class="text-black truncate" title="{{ $student->address }}">: {{ $address }}</span>
                        </div>
                    </div>

                    {{-- QR Code Verifikasi Siswa (Ukuran Diperkecil agar Proporsional) --}}
                    <div class="flex flex-col items-center justify-center shrink-0 text-center pl-1 border-l border-slate-200">
                        <div class="bg-white p-0.5 border-1.5 border-black rounded shadow-[1px_1px_0px_#000]">
                            <img src="{{ $student->front_qr_data_uri }}" 
                                 alt="QR Validasi" 
                                 class="w-[13.5mm] h-[13.5mm] object-contain">
                        </div>
                        <span class="text-[5.5px] font-mono font-bold text-slate-600 uppercase mt-0.5 tracking-tighter">
                            SCAN VALIDASI
                        </span>
                    </div>
                </div>

                {{-- Footer Masa Berlaku (Tanpa Menampilkan Kelas) --}}
                <div class="px-3 py-1 border-t-2 border-black flex items-center justify-between text-[8px] font-bold" style="background-color: {{ $themeColor }}20;">
                    <div class="inline-flex items-center gap-1 text-black font-black uppercase tracking-wider text-[7.5px] px-2 py-0.5 rounded-full border border-black shadow-[1px_1px_0px_#000]" style="background-color: {{ $themeColor }};">
                        <span>●</span>
                        <span>{{ $validityText }}</span>
                    </div>
                    <span class="font-mono text-[7px] text-slate-500 font-semibold uppercase">
                        {{ $schoolName }}
                    </span>
                </div>
            </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- SISI BELAKANG: UNTUK SCAN MASUK / PRESENSI (TANPA KELAS)                   --}}
        {{-- ========================================================================= --}}
        @if($side === 'back' || $side === 'both' || $interactive)
            @php
                // Tentukan layout belakang: landscape atau portrait
                $isLandscapeBack = ($orientation === 'landscape') || ($size === 'print');
            @endphp
            <div id="{{ $uniqueId }}_back" 
                 class="student-card-side card-back bg-white border-2 border-black rounded-xl shadow-[3px_3px_0px_0px_#000] overflow-hidden flex flex-col justify-between relative transition-all duration-200 {{ $interactive ? 'hidden' : '' }}"
                 style="{{ $isLandscapeBack ? 'width: 8.7cm; min-width: 8.7cm; height: 5.4cm; min-height: 5.4cm;' : 'width: 5.4cm; min-width: 5.4cm; height: 8.7cm; min-height: 8.7cm;' }} max-width: 100%; box-sizing: border-box;">
                
                @if($isLandscapeBack)
                    {{-- Sisi Belakang: Versi Landscape (Sempurna untuk Cetak Fisik Duplex 8,7 x 5,4 cm) --}}
                    <div class="p-3.5 flex items-center justify-between gap-3 h-full">
                        {{-- Sisi Kiri: Petunjuk & Tanda Tangan (Tanpa Kelas) --}}
                        <div class="flex-1 min-w-0 space-y-1.5 leading-tight">
                            <div class="font-heading font-black text-xs uppercase text-black border-b border-black pb-1 truncate">
                                {{ $studentName }}
                            </div>
                            <div class="text-[9px] font-mono font-bold text-slate-700">
                                NIS: {{ $student->nis }}
                            </div>
                            <div class="bg-amber-50 border border-black p-1.5 rounded text-[8px] leading-relaxed text-slate-800">
                                {{ $backInstructions }}
                            </div>

                            {{-- Tanda Tangan Kepala Sekolah (jika aktif) --}}
                            @if($setting->card_show_signature && !empty($setting->card_principal_name))
                                <div class="pt-1 text-[7.5px] leading-tight text-right font-medium">
                                    <div>Mengetahui, Kepala Madrasah</div>
                                    @if($setting->card_signature_url)
                                        <div class="h-6 flex justify-end items-center my-0.5">
                                            <img src="{{ $setting->card_signature_url }}" alt="TTD" class="h-full object-contain">
                                        </div>
                                    @else
                                        <div class="h-4"></div>
                                    @endif
                                    <div class="font-bold underline text-black">{{ $setting->card_principal_name }}</div>
                                    <div class="text-[6.5px] font-mono">NIP. {{ $setting->card_principal_nip ?? '-' }}</div>
                                </div>
                            @endif
                        </div>

                        {{-- Sisi Kanan: QR Presensi Besar & Token --}}
                        <div class="flex flex-col items-center justify-center shrink-0">
                            <div class="bg-white p-1 border-2 border-black rounded shadow-[2px_2px_0px_#000]">
                                <img src="{{ $student->back_qr_data_uri }}" 
                                     alt="QR Presensi Masuk" 
                                     class="w-[28mm] h-[28mm] object-contain">
                            </div>
                            @if($setting->card_show_back_token)
                                <div class="mt-1 bg-amber-100 border border-black px-1.5 py-0.5 rounded text-[8px] font-mono font-black text-black">
                                    {{ $student->qr_code_identifier }}
                                </div>
                            @endif
                            <span class="text-[7px] font-black uppercase text-black tracking-wider mt-0.5">
                                SCAN PRESENSI MASUK
                            </span>
                        </div>
                    </div>
                @else
                    {{-- Sisi Belakang: Versi Portrait (Tanpa Kelas) --}}
                    <div class="p-3 flex flex-col items-center justify-between text-center h-full">
                        {{-- Header Siswa --}}
                        <div class="w-full">
                            <div class="font-heading font-black text-xs uppercase text-black truncate">
                                {{ $studentName }}
                            </div>
                            <div class="text-[8.5px] font-mono font-bold text-slate-700 mt-0.5">
                                NIS: {{ $student->nis }}
                            </div>
                        </div>

                        {{-- QR Presensi Besar --}}
                        <div class="my-auto flex flex-col items-center">
                            <div class="bg-white p-1.5 border-2 border-black rounded-lg shadow-[2px_2px_0px_#000]">
                                <img src="{{ $student->back_qr_data_uri }}" 
                                     alt="QR Presensi Masuk" 
                                     class="w-[34mm] h-[34mm] object-contain">
                            </div>

                            @if($setting->card_show_back_token)
                                <div class="mt-1.5 bg-[#FFF3BF] border border-black px-2 py-0.5 rounded text-[8.5px] font-mono font-black text-black shadow-[1px_1px_0px_#000]">
                                    {{ $student->qr_code_identifier }}
                                </div>
                            @endif
                        </div>

                        {{-- Petunjuk Bawah --}}
                        <div class="text-[7.5px] leading-tight text-slate-600 px-1 pt-1 border-t border-slate-200 w-full">
                            {{ $backInstructions }}
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>

@pushOnce('scripts')
<script>
    function toggleCardFlip(cardId) {
        const front = document.getElementById(cardId + '_front');
        const back = document.getElementById(cardId + '_back');
        const btnText = document.getElementById(cardId + '_btn_text');

        if (!front || !back) return;

        if (front.classList.contains('hidden')) {
            front.classList.remove('hidden');
            back.classList.add('hidden');
            if (btnText) btnText.innerText = 'Lihat Sisi Belakang';
        } else {
            front.classList.add('hidden');
            back.classList.remove('hidden');
            if (btnText) btnText.innerText = 'Lihat Sisi Depan';
        }
    }
</script>
@endPushOnce
