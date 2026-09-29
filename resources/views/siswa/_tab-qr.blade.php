<!-- ========================================================================= -->
<!-- TAB 2: QR PRESENSI (KARTU PELAJAR DIGITAL & LIVE TOKEN) -->
<!-- ========================================================================= -->
<div id="tabContent-qr" class="tab-pane hidden space-y-4 sm:space-y-5">
    
    <!-- Digital Student ID Card Container -->
    <div class="bg-white rounded-2xl border-2 sm:border-[2.5px] border-black p-5 sm:p-6 space-y-5 shadow-[5px_5px_0px_0px_#000] relative overflow-hidden">
        
        <!-- Playful Badge Slot (Lanyard Hole simulation) -->
        <div class="w-16 h-2.5 bg-slate-200 rounded-full border-2 border-black mx-auto shadow-[1px_1px_0px_0px_#000]"></div>

        <!-- Header Card: Identity & Status -->
        <div class="flex items-center justify-between border-b-2 border-black pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 sm:w-11 sm:h-11 bg-[#5294FF] text-white font-heading font-black text-lg sm:text-xl flex items-center justify-center rounded-xl border-2 border-black shadow-[2px_2px_0px_0px_#000] shrink-0">
                    🎓
                </div>
                <div>
                    <div class="font-heading font-black text-xs sm:text-sm uppercase tracking-wider text-black">
                        Kartu Pelajar Digital
                    </div>
                    <div class="text-[11px] font-bold text-slate-600 truncate max-w-[200px] sm:max-w-xs">
                        {{ $schoolSetting->school_name ?? 'Sistem Presensi Sekolah' }}
                    </div>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 bg-[#20C997] text-white text-[11px] font-black uppercase tracking-wider px-3 py-1 rounded-full border-2 border-black shadow-[2px_2px_0px_0px_#000]">
                <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                AKTIF
            </span>
        </div>

        <!-- Student Profile Bio Row -->
        <div class="flex items-center gap-4 bg-slate-50 rounded-xl border-2 border-black p-3.5 sm:p-4 shadow-[2px_2px_0px_0px_#000]">
            @if($student->photo_url)
                <img src="{{ $student->photo_url }}" alt="{{ $student->user->name }}" class="w-20 h-24 sm:w-24 sm:h-28 object-cover rounded-xl border-2 border-black shadow-[2.5px_2.5px_0px_0px_#000] shrink-0">
            @else
                <div class="w-20 h-24 sm:w-24 sm:h-28 bg-[#FFF3BF] rounded-xl border-2 border-black shadow-[2.5px_2.5px_0px_0px_#000] shrink-0 flex flex-col items-center justify-center text-slate-700">
                    <span class="text-3xl">👤</span>
                    <span class="text-[10px] font-black tracking-wider uppercase mt-1 text-slate-600">FOTO</span>
                </div>
            @endif

            <div class="min-w-0 space-y-2 flex-1">
                <div>
                    <div class="text-[10px] text-slate-500 font-black uppercase tracking-wider">Nama Lengkap Siswa</div>
                    <div class="font-heading font-black text-base sm:text-lg text-black leading-tight truncate mt-0.5">
                        {{ $student->user->name }}
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <div class="bg-white rounded-lg border border-black p-1.5 shadow-[1px_1px_0px_0px_#000]">
                        <div class="text-[9px] text-slate-500 font-black uppercase">NIS / NISN</div>
                        <div class="font-mono text-xs font-bold text-black truncate">
                            {{ $student->nis }} @if($student->nisn) / {{ $student->nisn }} @endif
                        </div>
                    </div>
                    <div class="bg-white rounded-lg border border-black p-1.5 shadow-[1px_1px_0px_0px_#000]">
                        <div class="text-[9px] text-slate-500 font-black uppercase">Kelas Aktif</div>
                        <div class="text-xs font-black text-black truncate">
                            {{ $student->schoolClass->name ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interactive QR Display Frame -->
        <div class="bg-[#FFF9DB] rounded-2xl border-2 sm:border-[2.5px] border-black p-5 sm:p-6 text-center space-y-4 shadow-[3px_3px_0px_0px_#000]">
            <div class="flex items-center justify-between text-xs font-black">
                <span class="text-slate-800 uppercase tracking-wider">KODE QR RESMI PRESENSI</span>
                <span class="inline-flex items-center gap-1.5 bg-[#D3F9D8] px-2.5 py-0.5 rounded-full border border-black text-[10px] font-black text-emerald-900">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    Live Token
                </span>
            </div>

            <!-- High-Res Scalable QR Image Frame -->
            <div class="bg-white rounded-2xl border-2 sm:border-3 border-black p-3 sm:p-4 inline-block shadow-[4px_4px_0px_0px_#000] cursor-pointer hover:scale-105 transition-transform" onclick="openModal('modalFullscreenQr')" title="Ketuk untuk perbesar layar penuh">
                <img src="{{ $qrCodeDataUri }}" alt="QR Presensi Siswa" class="w-48 h-48 sm:w-60 sm:h-60 mx-auto object-contain">
            </div>

            <!-- Token Code Display Pill -->
            <div class="space-y-1.5">
                <div class="font-mono font-black text-sm sm:text-base text-black tracking-widest break-all bg-white rounded-xl border-2 border-black py-1.5 px-4 inline-block shadow-[2px_2px_0px_0px_#000]">
                    {{ $activeToken->token ?? $student->qr_code_identifier }}
                </div>
                <div class="text-xs text-slate-600 font-bold">
                    Berlaku hingga: <span class="font-mono font-black text-black bg-[#FFE8CC] px-2 py-0.5 rounded border border-black">{{ $activeToken ? $activeToken->expires_at->format('H:i:s') . ' WIB' : 'Hari Ini' }}</span>
                </div>
            </div>

            <!-- Fullscreen CTA Button -->
            <button type="button" onclick="openModal('modalFullscreenQr')" class="bg-[#FFD43B] hover:bg-[#fcc419] text-black w-full py-3.5 rounded-xl border-2 border-black shadow-[3px_3px_0px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[1px_1px_0px_0px_#000] transition-all text-xs sm:text-sm font-black uppercase tracking-wider flex items-center justify-center gap-2 cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                </svg>
                <span>Tampilkan Layar Penuh (Scan Cepat di Scanner)</span>
            </button>
        </div>

        <!-- Scanning Instructions Card -->
        <div class="p-4 bg-[#E7F5FF] rounded-xl border-2 border-black text-xs font-bold text-slate-800 space-y-2 shadow-[2px_2px_0px_0px_#000]">
            <div class="flex items-center gap-2 text-blue-950 font-black uppercase tracking-wide text-xs sm:text-sm">
                <span>💡</span> Petunjuk Pemindaian Gerbang:
            </div>
            <ul class="list-disc list-inside space-y-1 text-slate-700 font-semibold leading-relaxed">
                <li>Arahkan layar ponsel menghadap kamera scanner di gerbang masuk / pos sekolah.</li>
                <li>Gunakan tombol <strong class="text-black">Layar Penuh</strong> di atas agar kecerahan layar optimal dan pemindaian berjalan seketika.</li>
                <li>QR Token diperbarui secara dinamis oleh sistem demi menjaga keamanan kehadiran Anda.</li>
            </ul>
        </div>
    </div>
</div>
