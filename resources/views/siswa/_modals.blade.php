<!-- ========================================================================= -->
<!-- MODAL 1: FULLSCREEN QR CODE SCANNER VIEW -->
<!-- ========================================================================= -->
<div id="modalFullscreenQr" class="fixed inset-0 z-50 hidden bg-black/80 flex items-center justify-center p-4">
    <div class="bg-white border-4 border-black max-w-sm w-full p-6 text-center space-y-4 shadow-[8px_8px_0px_0px_#000] relative">
        <button type="button" onclick="closeModal('modalFullscreenQr')" class="absolute top-3 right-3 text-black font-black text-xl hover:opacity-75 p-1">
            ✕
        </button>

        <div class="space-y-1">
            <span class="neo-badge bg-[#5294FF] text-white text-[10px]">SCANNER VIEW</span>
            <h3 class="font-heading font-black text-lg text-black">{{ $student->user->name }}</h3>
            <p class="text-xs font-mono font-bold text-slate-600">NIS: {{ $student->nis }} • {{ $student->schoolClass->name ?? '' }}</p>
        </div>

        <!-- Crisp QR Image -->
        <div class="bg-white border-3 border-black p-3 inline-block shadow-[4px_4px_0px_0px_#000]">
            <img src="{{ $qrCodeDataUri }}" alt="QR Code Fullscreen" class="w-64 h-64 mx-auto object-contain">
        </div>

        <div class="space-y-2">
            <div class="bg-[#FFF9DB] border-2 border-black p-2 text-xs font-mono font-black break-all">
                {{ $activeToken->token ?? $student->qr_code_identifier }}
            </div>
            <p class="text-[11px] font-bold text-slate-700">
                Arahkan layar ponsel ini ke kamera scanner sekolah. Pastikan kecerahan layar maksimal.
            </p>
        </div>

        <button type="button" onclick="closeModal('modalFullscreenQr')" class="neo-btn bg-[#FFD43B] text-black w-full py-2.5 text-xs font-black uppercase">
            Tutup Tampilan
        </button>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: FORM PENGAJUAN IZIN / SAKIT -->
<!-- ========================================================================= -->
<div id="modalLeaveRequest" class="fixed inset-0 z-50 hidden bg-black/70 flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white border-4 border-black max-w-md w-full p-5 sm:p-6 space-y-4 shadow-[8px_8px_0px_0px_#000] my-8 relative">
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <div>
                <h3 class="font-heading font-black text-lg text-black">Form Pengajuan Izin / Sakit</h3>
                <p class="text-xs text-slate-600 font-semibold">Kirim permohonan ke wali kelas untuk dicatat</p>
            </div>
            <button type="button" onclick="closeModal('modalLeaveRequest')" class="text-black font-black text-xl hover:opacity-75 p-1">
                ✕
            </button>
        </div>

        <form action="{{ route('siswa.leave-requests.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <!-- Jenis Permohonan -->
            <div>
                <label class="block text-xs font-black uppercase text-black mb-1.5">Jenis Permohonan <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="border-2 border-black p-3 flex items-center gap-2 cursor-pointer has-[:checked]:bg-[#FFE3E3] has-[:checked]:shadow-[2px_2px_0px_0px_#000]">
                        <input type="radio" name="type" value="sakit" required checked class="accent-black">
                        <span class="text-xs font-black uppercase">🤒 Sakit</span>
                    </label>
                    <label class="border-2 border-black p-3 flex items-center gap-2 cursor-pointer has-[:checked]:bg-[#E7F5FF] has-[:checked]:shadow-[2px_2px_0px_0px_#000]">
                        <input type="radio" name="type" value="izin" required class="accent-black">
                        <span class="text-xs font-black uppercase">📝 Izin Penting</span>
                    </label>
                </div>
            </div>

            <!-- Rentang Tanggal -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-black uppercase text-black mb-1">Mulai Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" name="date_from" value="{{ date('Y-m-d') }}" required
                        class="w-full neo-input text-xs font-mono font-bold bg-white">
                </div>
                <div>
                    <label class="block text-xs font-black uppercase text-black mb-1">Sampai Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" name="date_to" value="{{ date('Y-m-d') }}" required
                        class="w-full neo-input text-xs font-mono font-bold bg-white">
                </div>
            </div>

            <!-- Alasan / Keterangan -->
            <div>
                <label class="block text-xs font-black uppercase text-black mb-1">Alasan / Keterangan <span class="text-red-500">*</span></label>
                <textarea name="reason" rows="3" required placeholder="Tuliskan keterangan sakit atau alasan izin secara jelas..."
                    class="w-full neo-input text-xs font-medium"></textarea>
            </div>

            <!-- Lampiran Foto / Surat Dokter -->
            <div>
                <label class="block text-xs font-black uppercase text-black mb-1">
                    Foto Surat Dokter / Keterangan Wali <span class="text-xs font-normal text-slate-500">(Opsional)</span>
                </label>
                <input type="file" name="attachment" accept="image/jpeg,image/png,image/webp,application/pdf"
                    class="w-full text-xs font-bold file:mr-3 file:py-2 file:px-3 file:border-2 file:border-black file:bg-[#FFD43B] file:text-xs file:font-black file:uppercase file:cursor-pointer border-2 border-black p-1 bg-white">
                <p class="text-[10px] text-slate-500 font-semibold mt-1">Format: JPG, PNG, WEBP, atau PDF (Maks. 3MB)</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-2 pt-2 border-t-2 border-black">
                <button type="button" onclick="closeModal('modalLeaveRequest')" class="neo-btn bg-slate-200 text-black px-4 py-2 text-xs font-black uppercase">
                    Batal
                </button>
                <button type="submit" class="neo-btn bg-[#20C997] text-white px-5 py-2 text-xs font-black uppercase flex items-center gap-1.5 shadow-[2px_2px_0px_0px_#000]">
                    <span>📤</span> Kirim Pengajuan
                </button>
            </div>
        </form>
    </div>
</div>
