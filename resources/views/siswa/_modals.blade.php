<!-- ========================================================================= -->
<!-- MODAL 1: FULLSCREEN QR CODE SCANNER VIEW (PLAYFUL NEO-BRUTALISM) -->
<!-- ========================================================================= -->
<div id="modalFullscreenQr" class="fixed inset-0 z-50 hidden bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl border-3 border-black max-w-sm w-full p-6 text-center space-y-4 shadow-[8px_8px_0px_0px_#000] relative">
        <button type="button" onclick="closeModal('modalFullscreenQr')" class="absolute top-3.5 right-3.5 w-8 h-8 rounded-full bg-slate-100 hover:bg-rose-100 border-2 border-black flex items-center justify-center text-black font-black text-sm shadow-[1.5px_1.5px_0px_0px_#000] cursor-pointer transition-all" title="Tutup">
            ✕
        </button>

        <div class="space-y-1 pt-1">
            <span class="inline-flex items-center gap-1.5 bg-[#5294FF] text-white text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-full border border-black shadow-[1.5px_1.5px_0px_0px_#000]">
                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                SCANNER VIEW
            </span>
            <h3 class="font-heading font-black text-lg text-black mt-1">{{ $student->user->name }}</h3>
            <p class="text-xs font-mono font-bold text-slate-600">NIS: {{ $student->nis }} • {{ $student->schoolClass->name ?? '' }}</p>
        </div>

        <!-- High-Res Scalable QR Image -->
        <div class="bg-white rounded-xl border-2 sm:border-3 border-black p-3 inline-block shadow-[4px_4px_0px_0px_#000]">
            <img src="{{ $qrCodeDataUri }}" alt="QR Code Fullscreen" class="w-64 h-64 mx-auto object-contain">
        </div>

        <div class="space-y-2">
            <div class="bg-[#FFF9DB] rounded-xl border-2 border-black p-2.5 text-xs font-mono font-black text-black break-all shadow-[2px_2px_0px_0px_#000]">
                {{ $activeToken->token ?? $student->qr_code_identifier }}
            </div>
            <p class="text-xs font-bold text-slate-700 leading-snug">
                Arahkan layar ponsel ini ke kamera scanner sekolah. Pastikan kecerahan layar maksimal.
            </p>
        </div>

        <button type="button" onclick="closeModal('modalFullscreenQr')" class="bg-[#FFD43B] hover:bg-[#fcc419] text-black w-full py-3 rounded-xl border-2 border-black shadow-[3px_3px_0px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[1px_1px_0px_0px_#000] text-xs font-black uppercase tracking-wider cursor-pointer transition-all">
            Tutup Tampilan
        </button>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: FORM PENGAJUAN IZIN / SAKIT (PLAYFUL NEO-BRUTALISM) -->
<!-- ========================================================================= -->
<div id="modalLeaveRequest" class="fixed inset-0 z-50 hidden bg-black/75 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl border-3 border-black max-w-md w-full p-5 sm:p-6 space-y-4 shadow-[8px_8px_0px_0px_#000] my-8 relative">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <div>
                <h3 class="font-heading font-black text-base sm:text-lg text-black">Form Pengajuan Izin / Sakit</h3>
                <p class="text-xs text-slate-600 font-semibold mt-0.5">Kirim surat permohonan ke wali kelas Anda</p>
            </div>
            <button type="button" onclick="closeModal('modalLeaveRequest')" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-rose-100 border-2 border-black flex items-center justify-center text-black font-black text-sm shadow-[1.5px_1.5px_0px_0px_#000] cursor-pointer transition-all" title="Tutup">
                ✕
            </button>
        </div>

        <form action="{{ route('siswa.leave-requests.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <!-- Jenis Permohonan -->
            <div>
                <label class="block text-xs font-black uppercase text-black mb-1.5">
                    Jenis Permohonan <span class="text-rose-600">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="rounded-xl border-2 border-black p-3 flex items-center gap-2.5 cursor-pointer has-[:checked]:bg-[#FFE3E3] has-[:checked]:shadow-[3px_3px_0px_0px_#000] transition-all bg-slate-50">
                        <input type="radio" name="type" value="sakit" required {{ old('type', 'sakit') === 'sakit' ? 'checked' : '' }} class="w-4 h-4 accent-black cursor-pointer">
                        <span class="text-xs font-black uppercase">🤒 Sakit</span>
                    </label>
                    <label class="rounded-xl border-2 border-black p-3 flex items-center gap-2.5 cursor-pointer has-[:checked]:bg-[#E7F5FF] has-[:checked]:shadow-[3px_3px_0px_0px_#000] transition-all bg-slate-50">
                        <input type="radio" name="type" value="izin" required {{ old('type') === 'izin' ? 'checked' : '' }} class="w-4 h-4 accent-black cursor-pointer">
                        <span class="text-xs font-black uppercase">📝 Izin Penting</span>
                    </label>
                </div>
                @error('type')
                    <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Rentang Tanggal -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label class="block text-xs font-black uppercase text-black">
                        Mulai Tanggal <span class="text-rose-600">*</span>
                    </label>
                    <input type="date" name="date_from" value="{{ old('date_from', date('Y-m-d')) }}" required
                        class="w-full px-3 py-2.5 rounded-xl border-2 border-black shadow-[2px_2px_0px_0px_#000] focus:shadow-[4px_4px_0px_0px_#000] text-xs font-mono font-bold bg-white outline-none transition-all">
                    @error('date_from')
                        <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="space-y-1">
                    <label class="block text-xs font-black uppercase text-black">
                        Sampai Tanggal <span class="text-rose-600">*</span>
                    </label>
                    <input type="date" name="date_to" value="{{ old('date_to', date('Y-m-d')) }}" required
                        class="w-full px-3 py-2.5 rounded-xl border-2 border-black shadow-[2px_2px_0px_0px_#000] focus:shadow-[4px_4px_0px_0px_#000] text-xs font-mono font-bold bg-white outline-none transition-all">
                    @error('date_to')
                        <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Alasan / Keterangan -->
            <div class="space-y-1">
                <label class="block text-xs font-black uppercase text-black">
                    Alasan / Keterangan Lengkap <span class="text-rose-600">*</span>
                </label>
                <textarea name="reason" rows="3" required placeholder="Tuliskan keterangan sakit atau alasan izin secara jelas..."
                    class="w-full p-3 rounded-xl border-2 border-black shadow-[2px_2px_0px_0px_#000] focus:shadow-[4px_4px_0px_0px_#000] text-xs sm:text-sm font-medium bg-white outline-none transition-all">{{ old('reason') }}</textarea>
                @error('reason')
                    <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Lampiran Foto / Surat Dokter -->
            <div class="space-y-1">
                <label class="block text-xs font-black uppercase text-black">
                    Foto Surat Dokter / Keterangan Wali <span class="text-xs font-normal text-slate-500">(Opsional)</span>
                </label>
                <input type="file" name="attachment" accept="image/jpeg,image/png,image/webp,application/pdf"
                    class="block w-full text-xs text-slate-700 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-2 file:border-black file:text-xs file:font-black file:bg-[#FFD43B] hover:file:bg-[#fcc419] file:cursor-pointer file:shadow-[1.5px_1.5px_0px_#000] rounded-xl border-2 border-black p-1.5 bg-white shadow-[2px_2px_0px_0px_#000] cursor-pointer">
                @error('attachment')
                    <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p>
                @enderror
                <p class="text-[11px] text-slate-500 font-semibold mt-1">Format: JPG, PNG, WEBP, atau PDF (Maks. 3MB)</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-2.5 pt-3 border-t-2 border-black">
                <button type="button" onclick="closeModal('modalLeaveRequest')" class="bg-slate-100 hover:bg-slate-200 text-black px-4 py-2.5 rounded-xl border-2 border-black font-black uppercase text-xs cursor-pointer transition-all">
                    Batal
                </button>
                <button type="submit" class="bg-[#20C997] hover:bg-[#12b886] text-black px-5 py-2.5 rounded-xl border-2 border-black shadow-[3px_3px_0px_0px_#000] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[1px_1px_0px_0px_#000] text-xs font-black uppercase flex items-center gap-1.5 cursor-pointer transition-all">
                    <span>📤 Kirim Pengajuan</span>
                </button>
            </div>
        </form>
    </div>
</div>
