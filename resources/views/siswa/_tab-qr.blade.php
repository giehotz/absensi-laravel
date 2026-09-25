<!-- ========================================================================= -->
<!-- TAB 2: QR PRESENSI (KARTU PELAJAR DIGITAL & TOKEN) -->
<!-- ========================================================================= -->
<div id="tabContent-qr" class="tab-pane hidden space-y-4">
    <!-- Digital Student ID Card -->
    <div class="bg-white neo-box-lg p-5 space-y-4 relative overflow-hidden">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-[#5294FF] text-white font-heading font-black text-base flex items-center justify-center border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                    K
                </div>
                <div>
                    <div class="font-heading font-black text-xs uppercase tracking-wider text-black">Kartu Pelajar Digital</div>
                    <div class="text-[10px] font-semibold text-slate-600">{{ $schoolSetting->school_name ?? 'Sistem Presensi Sekolah' }}</div>
                </div>
            </div>
            <span class="neo-badge bg-[#20C997] text-white text-[10px]">AKTIF</span>
        </div>

        <!-- Student Bio Card Row -->
        <div class="flex items-center gap-3.5">
            @if($student->photo_url)
                <img src="{{ $student->photo_url }}" alt="{{ $student->user->name }}" class="w-18 h-22 object-cover border-2 border-black shadow-[2px_2px_0px_0px_#000] shrink-0">
            @else
                <div class="w-18 h-22 bg-[#FFF3BF] border-2 border-black shadow-[2px_2px_0px_0px_#000] shrink-0 flex flex-col items-center justify-center text-slate-700">
                    <span class="text-2xl">👤</span>
                    <span class="text-[9px] font-bold mt-1">FOTO</span>
                </div>
            @endif
            <div class="min-w-0 space-y-1 text-xs">
                <div>
                    <div class="text-[10px] text-slate-500 font-bold uppercase">Nama Lengkap</div>
                    <div class="font-heading font-black text-sm text-black leading-tight truncate">{{ $student->user->name }}</div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <div class="text-[9px] text-slate-500 font-bold uppercase">NIS / NISN</div>
                        <div class="font-mono font-bold text-black">{{ $student->nis }} / {{ $student->nisn ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-[9px] text-slate-500 font-bold uppercase">Kelas</div>
                        <div class="font-bold text-black">{{ $student->schoolClass->name ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interactive QR Frame -->
        <div class="bg-[#FFF9DB] border-3 border-black p-4 text-center space-y-3 neo-box">
            <div class="flex items-center justify-between text-[11px] font-bold">
                <span class="text-slate-800">QR CODE PRESENSI RESMI</span>
                <span class="text-emerald-700 flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span> Live Token
                </span>
            </div>

            <!-- High-res Scalable SVG QR Code -->
            <div class="bg-white border-3 border-black p-3 inline-block shadow-[4px_4px_0px_0px_#000] cursor-pointer" onclick="openModal('modalFullscreenQr')" title="Ketuk untuk mode layar penuh">
                <img src="{{ $qrCodeDataUri }}" alt="QR Presensi Siswa" class="w-48 h-48 sm:w-56 sm:h-56 mx-auto object-contain">
            </div>

            <div class="space-y-1">
                <div class="text-xs font-mono font-black text-black tracking-widest break-all bg-white border border-black py-1 px-2 inline-block">
                    {{ $activeToken->token ?? $student->qr_code_identifier }}
                </div>
                <div class="text-[11px] text-slate-600 font-semibold">
                    Berlaku hingga: <span class="font-mono font-bold text-black">{{ $activeToken ? $activeToken->expires_at->format('H:i:s') . ' WIB' : 'Hari Ini' }}</span>
                </div>
            </div>

            <button type="button" onclick="openModal('modalFullscreenQr')" class="neo-btn bg-[#FFD43B] text-black w-full py-2.5 text-xs font-black uppercase flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                </svg>
                <span>Tampilkan Layar Penuh (Scan Cepat)</span>
            </button>
        </div>

        <div class="p-3 bg-[#E7F5FF] border-2 border-black text-[11px] font-bold text-slate-700 space-y-1">
            <div class="flex items-center gap-1.5 text-blue-950 font-black">
                <span>💡</span> Petunjuk Pemindaian:
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-slate-600 font-medium">
                <li>Arahkan layar ponsel menghadap scanner kamera di pos satpam / gerbang madrasah.</li>
                <li>Gunakan mode layar penuh agar kecerahan layar optimal dan pemindaian berlangsung instan.</li>
            </ul>
        </div>
    </div>
</div>
