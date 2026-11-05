@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.guru')

@section('title', 'Pengaturan Kop, Tanda Tangan & Aset Kartu Ujian')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-5 bg-white border-4 border-black rounded-xl shadow-[4px_4px_0px_0px_#000]">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-black uppercase tracking-wider bg-[#FFD43B] text-black px-2 py-0.5 rounded border border-black shadow-[1px_1px_0px_0px_#000]">
                    Sub-Menu 5
                </span>
                <span class="text-xs font-bold text-slate-500">Live Preview & Presisi Tanda Tangan</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black font-heading text-black">
                Tanda Tangan Kepala & Pengaturan Kartu
            </h1>
            <p class="text-xs font-semibold text-slate-600 mt-0.5">
                Konfigurasi 4 baris teks kop, stempel, opacity watermark, dan presisi posisi tanda tangan digital dengan pratinjau langsung.
            </p>
        </div>

        {{-- Switch Periode --}}
        <form method="GET" action="{{ route('ujian.pengaturan-ttd.index') }}" class="flex items-center gap-2 shrink-0">
            <span class="text-xs font-bold text-slate-700">Periode:</span>
            <select name="periode_id" onchange="this.form.submit()" class="px-3 py-2 text-xs font-bold border-2 border-black rounded-lg bg-slate-50 shadow-[2px_2px_0px_0px_#000]">
                @foreach($periodes as $p)
                    <option value="{{ $p->id }}" {{ $p->id == $activePeriode?->id ? 'selected' : '' }}>
                        {{ $p->nama_periode }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="p-4 bg-[#D3F9D8] border-3 border-black rounded-xl shadow-[3px_3px_0px_0px_#000] text-emerald-950 font-bold text-xs flex items-center justify-between">
            <div class="flex items-center gap-2"><span>✅</span><span>{{ session('success') }}</span></div>
            <button type="button" onclick="this.parentElement.remove()" class="text-xs font-black">✕</button>
        </div>
    @endif
    @if($errors->any())
        <div class="p-4 bg-[#FFE3E3] border-3 border-black rounded-xl shadow-[3px_3px_0px_0px_#000] text-rose-950 font-bold text-xs">
            <ul class="list-disc pl-4 space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($pengaturan)
    {{-- Form Aksi Tersembunyi untuk Hapus Aset TTD & Stempel --}}
    <form id="formDeleteSignature" action="{{ route('ujian.pengaturan-ttd.delete-signature', $pengaturan) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
    <form id="formDeleteStamp" action="{{ route('ujian.pengaturan-ttd.delete-stamp', $pengaturan) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- Form Pengaturan (Kiri - 7 Kolom) --}}
        <div class="lg:col-span-7 bg-white border-4 border-black rounded-xl p-5 shadow-[4px_4px_0px_0px_#000]">
            <form action="{{ route('ujian.pengaturan-ttd.update', $pengaturan) }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs font-semibold" id="formPengaturan">
                @csrf
                @method('PUT')

                {{-- 1. Teks 4 Baris Kop Kartu --}}
                <div class="border-b-2 border-black pb-3">
                    <h3 class="text-sm font-black font-heading text-black mb-2 flex items-center gap-1.5">
                        <span>🏷️</span>
                        <span>Identitas & Teks 4 Baris Kop Kartu</span>
                    </h3>
                    
                    <div class="space-y-2">
                        <div>
                            <label class="block text-slate-700 font-bold mb-0.5">Kop Baris 1 (Judul Dokumen)</label>
                            <input type="text" id="input_kop_1" name="kop_baris_1" value="{{ old('kop_baris_1', $pengaturan->kop_baris_1) }}" required class="w-full px-3 py-1.5 border-2 border-black rounded-lg bg-slate-50 uppercase text-xs" oninput="updateLivePreview()">
                        </div>
                        <div>
                            <label class="block text-slate-700 font-bold mb-0.5">Kop Baris 2 (Jenis Asesmen)</label>
                            <input type="text" id="input_kop_2" name="kop_baris_2" value="{{ old('kop_baris_2', $pengaturan->kop_baris_2) }}" placeholder="Contoh: ASESMEN SUMATIF AKHIR SEMESTER" class="w-full px-3 py-1.5 border-2 border-black rounded-lg bg-slate-50 uppercase text-xs" oninput="updateLivePreview()">
                        </div>
                        <div>
                            <label class="block text-slate-700 font-bold mb-0.5">Kop Baris 3 (Nama Madrasah / Sekolah)</label>
                            <input type="text" id="input_kop_3" name="kop_baris_3" value="{{ old('kop_baris_3', $pengaturan->kop_baris_3) }}" required class="w-full px-3 py-1.5 border-2 border-black rounded-lg bg-slate-50 uppercase text-xs" oninput="updateLivePreview()">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-slate-700 font-bold mb-0.5">Kop Baris 4 (Tahun Pelajaran)</label>
                                <input type="text" id="input_kop_4" name="kop_baris_4" value="{{ old('kop_baris_4', $pengaturan->kop_baris_4) }}" placeholder="Contoh: TAHUN PELAJARAN 2026/2027" class="w-full px-3 py-1.5 border-2 border-black rounded-lg bg-slate-50 text-xs" oninput="updateLivePreview()">
                            </div>
                            <div>
                                <label class="block text-slate-700 font-bold mb-0.5">Nama Instansi</label>
                                <input type="text" name="nama_instansi" value="{{ old('nama_instansi', $pengaturan->nama_instansi) }}" required class="w-full px-3 py-1.5 border-2 border-black rounded-lg bg-slate-50 text-xs">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Data Penandatangan --}}
                <div class="border-b-2 border-black pb-3">
                    <h3 class="text-sm font-black font-heading text-black mb-2 flex items-center gap-1.5">
                        <span>✍️</span>
                        <span>Pejabat Penandatangan</span>
                    </h3>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-slate-700 font-bold mb-0.5">Jabatan Penandatangan</label>
                            <input type="text" id="input_jabatan" name="jabatan_penandatangan" value="{{ old('jabatan_penandatangan', $pengaturan->jabatan_penandatangan) }}" required class="w-full px-3 py-1.5 border-2 border-black rounded-lg bg-slate-50 text-xs" oninput="updateLivePreview()">
                        </div>
                        <div>
                            <label class="block text-slate-700 font-bold mb-0.5">Nama Kepala (Lengkap Gelar)</label>
                            <input type="text" id="input_nama_kepala" name="nama_kepala" value="{{ old('nama_kepala', $pengaturan->nama_kepala) }}" required class="w-full px-3 py-1.5 border-2 border-black rounded-lg bg-slate-50 text-xs font-bold" oninput="updateLivePreview()">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 mt-2">
                        <div>
                            <label class="block text-slate-700 font-bold mb-0.5">NIP Kepala</label>
                            <input type="text" id="input_nip_kepala" name="nip_kepala" value="{{ old('nip_kepala', $pengaturan->nip_kepala) }}" class="w-full px-3 py-1.5 border-2 border-black rounded-lg bg-slate-50 text-xs font-mono" oninput="updateLivePreview()">
                        </div>
                        <div>
                            <label class="block text-slate-700 font-bold mb-0.5">Tempat Penetapan</label>
                            <input type="text" id="input_tempat" name="tempat_penetapan" value="{{ old('tempat_penetapan', $pengaturan->tempat_penetapan) }}" required class="w-full px-3 py-1.5 border-2 border-black rounded-lg bg-slate-50 text-xs" oninput="updateLivePreview()">
                        </div>
                        <div>
                            <label class="block text-slate-700 font-bold mb-0.5">Tanggal Penetapan</label>
                            <input type="date" name="tanggal_penetapan" value="{{ old('tanggal_penetapan', $pengaturan->tanggal_penetapan?->format('Y-m-d') ?: date('Y-m-d')) }}" class="w-full px-3 py-1.5 border-2 border-black rounded-lg bg-slate-50 text-xs">
                        </div>
                    </div>
                </div>

                {{-- 3. Pengaturan Visual: Watermark, Ukuran, Offset TTD & Stempel --}}
                <div class="border-b-2 border-black pb-3">
                    <h3 class="text-sm font-black font-heading text-black mb-2 flex items-center gap-1.5">
                        <span>📐</span>
                        <span>Presisi Posisi Tanda Tangan & Watermark</span>
                    </h3>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-slate-700 font-bold mb-1">
                                Opacity Watermark Logo: <span id="val_opacity" class="text-black font-black">{{ $pengaturan->opacity_watermark }}</span>
                            </label>
                            <input type="range" id="input_opacity" name="opacity_watermark" min="0.05" max="1.00" step="0.05" value="{{ old('opacity_watermark', $pengaturan->opacity_watermark) }}" class="w-full accent-black cursor-pointer" oninput="updateLivePreview()">
                        </div>
                        <div>
                            <label class="block text-slate-700 font-bold mb-1">
                                Ukuran Lebar TTD: <span id="val_lebar" class="text-black font-black">{{ $pengaturan->lebar_ttd_cm }} cm</span>
                            </label>
                            <input type="range" id="input_lebar" name="lebar_ttd_cm" min="1.0" max="6.0" step="0.2" value="{{ old('lebar_ttd_cm', $pengaturan->lebar_ttd_cm) }}" class="w-full accent-black cursor-pointer" oninput="updateLivePreview()">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mt-2">
                        <div>
                            <label class="block text-slate-700 font-bold mb-1">
                                Offset Vertikal TTD: <span id="val_offset_y" class="text-black font-black">{{ $pengaturan->offset_atas_ttd_cm }} cm</span>
                            </label>
                            <input type="range" id="input_offset_y" name="offset_atas_ttd_cm" min="-3.0" max="3.0" step="0.2" value="{{ old('offset_atas_ttd_cm', $pengaturan->offset_atas_ttd_cm) }}" class="w-full accent-black cursor-pointer" oninput="updateLivePreview()">
                        </div>
                        <div>
                            <label class="block text-slate-700 font-bold mb-1">
                                Offset Horizontal TTD: <span id="val_offset_x" class="text-black font-black">{{ $pengaturan->offset_kanan_ttd_cm }} cm</span>
                            </label>
                            <input type="range" id="input_offset_x" name="offset_kanan_ttd_cm" min="-3.0" max="3.0" step="0.2" value="{{ old('offset_kanan_ttd_cm', $pengaturan->offset_kanan_ttd_cm) }}" class="w-full accent-black cursor-pointer" oninput="updateLivePreview()">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mt-2">
                        <input type="hidden" id="input_tinggi" name="tinggi_ttd_cm" value="{{ $pengaturan->tinggi_ttd_cm }}">
                        <label class="flex items-center gap-2 p-2 bg-slate-50 border-2 border-black rounded-lg cursor-pointer">
                            <input type="checkbox" id="check_stempel" name="tampilkan_stempel" value="1" {{ $pengaturan->tampilkan_stempel ? 'checked' : '' }} onchange="updateLivePreview()" class="rounded border-black">
                            <span class="font-bold text-slate-800">Tampilkan Stempel Madrasah</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 bg-slate-50 border-2 border-black rounded-lg cursor-pointer">
                            <input type="checkbox" id="check_qr" name="tampilkan_qrcode_validasi" value="1" {{ $pengaturan->tampilkan_qrcode_validasi ? 'checked' : '' }} onchange="updateLivePreview()" class="rounded border-black">
                            <span class="font-bold text-slate-800">Tampilkan QR Code Kartu</span>
                        </label>
                    </div>
                </div>

                {{-- 4. Berkas Gambar & Digital Signature Pad --}}
                <div class="space-y-3">
                    <h3 class="text-sm font-black font-heading text-black flex items-center gap-1.5">
                        <span>🖼️</span>
                        <span>Aset Tanda Tangan & Stempel Resmi</span>
                    </h3>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 bg-slate-50 border-2 border-black rounded-lg">
                            <label class="block text-slate-700 font-bold mb-1">Upload File Tanda Tangan (PNG Transparan)</label>
                            <input type="file" name="gambar_ttd_file" accept="image/png,image/webp" class="text-xs w-full">
                            @if($pengaturan->signature_url)
                                <div class="mt-2 pt-2 border-t border-slate-200 flex items-center justify-between">
                                    <div class="text-[10px] text-emerald-700 font-bold flex items-center gap-1">
                                        <span>✓ TTD Tersedia</span>
                                        <a href="{{ $pengaturan->signature_url }}" target="_blank" class="underline">Lihat</a>
                                    </div>
                                    <button type="submit" form="formDeleteSignature" onclick="return confirm('Apakah Anda yakin ingin menghapus berkas tanda tangan ini? Kartu ujian untuk periode ini tidak akan menampilkan tanda tangan.')" 
                                            class="px-2 py-0.5 bg-[#FF6B6B] hover:bg-red-400 text-white rounded text-[10px] font-black border border-black shadow-[1px_1px_0px_0px_#000] cursor-pointer flex items-center gap-1">
                                        <span>🗑️</span>
                                        <span>Hapus TTD</span>
                                    </button>
                                </div>
                            @endif
                        </div>

                        <div class="p-3 bg-slate-50 border-2 border-black rounded-lg">
                            <label class="block text-slate-700 font-bold mb-1">Upload File Stempel (PNG Transparan)</label>
                            <input type="file" name="stempel_file" accept="image/png,image/webp" class="text-xs w-full">
                            @if($pengaturan->stempel_url)
                                <div class="mt-2 pt-2 border-t border-slate-200 flex items-center justify-between">
                                    <div class="text-[10px] text-emerald-700 font-bold flex items-center gap-1">
                                        <span>✓ Stempel Tersedia</span>
                                        <a href="{{ $pengaturan->stempel_url }}" target="_blank" class="underline">Lihat</a>
                                    </div>
                                    <button type="submit" form="formDeleteStamp" onclick="return confirm('Apakah Anda yakin ingin menghapus berkas stempel resmi ini? Kartu ujian untuk periode ini tidak akan menampilkan stempel madrasah.')" 
                                            class="px-2 py-0.5 bg-[#FF6B6B] hover:bg-red-400 text-white rounded text-[10px] font-black border border-black shadow-[1px_1px_0px_0px_#000] cursor-pointer flex items-center gap-1">
                                        <span>🗑️</span>
                                        <span>Hapus Stempel</span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- HTML5 Canvas Digital Signature Pad --}}
                    <div class="p-3.5 bg-amber-50/60 border-2 border-black rounded-xl space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-black text-black flex items-center gap-1">
                                <span>🖌️</span>
                                <span>Atau Gambar Tanda Tangan Digital di Layar</span>
                            </span>
                            <button type="button" onclick="clearSignaturePad()" class="px-2 py-0.5 bg-slate-200 hover:bg-slate-300 text-black rounded text-[10px] font-bold border border-black cursor-pointer">
                                Bersihkan Pad
                            </button>
                        </div>
                        <canvas id="signatureCanvas" width="500" height="150" class="w-full bg-white border-2 border-black rounded-lg shadow-inner cursor-crosshair h-28 touch-none"></canvas>
                        <input type="hidden" name="canvas_signature" id="canvas_signature_input">
                        <p class="text-[10px] text-slate-500 italic">
                            Goreskan tanda tangan menggunakan mouse atau stylus pada kotak di atas. Sistem akan menyimpannya sebagai PNG transparan otomatis.
                        </p>
                    </div>
                </div>

                <div class="pt-3 border-t-2 border-black flex items-center justify-end">
                    <button type="submit" onclick="prepareCanvasSubmission()" class="px-5 py-2.5 bg-[#FFD43B] hover:bg-yellow-400 text-black border-2 border-black rounded-lg font-black text-xs shadow-[3px_3px_0px_0px_#000] cursor-pointer">
                        💾 Simpan Pengaturan Kartu
                    </button>
                </div>
            </form>
        </div>

        {{-- Live Preview Kartu (Kanan - 5 Kolom Sticky) --}}
        <div class="lg:col-span-5 sticky top-6 space-y-3">
            <div class="bg-white border-4 border-black rounded-xl p-4 shadow-[4px_4px_0px_0px_#000]">
                <div class="flex items-center justify-between pb-2 border-b-2 border-black mb-3">
                    <h3 class="text-xs font-black font-heading text-black flex items-center gap-1.5 uppercase tracking-wide">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Live Preview Kartu Ujian (Ukuran Nyata)</span>
                    </h3>
                    <div class="flex items-center gap-1.5">
                        <button type="button" onclick="refreshPreviewIframe()" title="Muat ulang pratinjau" class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 border border-black rounded text-[10px] font-bold cursor-pointer">
                            🔄 Refresh
                        </button>
                        <a href="{{ route('ujian.kartu.print', ['periode_id' => $activePeriode->id]) }}" target="_blank" title="Buka cetak lengkap A4 di tab baru" class="px-2 py-0.5 bg-[#20C997] hover:bg-emerald-400 text-black border border-black rounded text-[10px] font-black shadow-[1px_1px_0px_0px_#000]">
                            🖨️ Cetak A4
                        </a>
                    </div>
                </div>

                {{-- Kontainer Iframe Pratinjau Cetak Ukuran Nyata --}}
                <div class="border-2 border-black rounded-lg overflow-x-auto bg-slate-100 flex items-center justify-center p-2 shadow-inner" style="min-height: 275px;">
                    <iframe id="iframePreviewKartu" 
                            src="{{ route('ujian.kartu.print', ['periode_id' => $activePeriode->id, 'preview' => 1]) }}" 
                            style="width: 9.85cm; height: 6.95cm; border: none; overflow: hidden; display: block;" 
                            scrolling="no"
                            onload="onIframeLoaded()">
                    </iframe>
                </div>

                <div class="p-2.5 bg-blue-50 border border-blue-300 rounded text-[11px] text-blue-900 font-medium">
                    💡 Pratinjau di atas dimuat langsung dari mesin cetak kartu ujian. Geser slider atau ubah teks formulir untuk melihat penyesuaian posisi secara instan.
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    function getIframeDoc() {
        const iframe = document.getElementById('iframePreviewKartu');
        if (!iframe) return null;
        try {
            return iframe.contentDocument || iframe.contentWindow?.document || null;
        } catch (e) {
            return null;
        }
    }

    function onIframeLoaded() {
        updateLivePreview();
    }

    function refreshPreviewIframe() {
        const iframe = document.getElementById('iframePreviewKartu');
        if (iframe) {
            iframe.src = iframe.src;
        }
    }

    // Live Preview Update (Kontrol DOM & CSS Iframe Pratinjau Cetak Langsung)
    function updateLivePreview() {
        // Update label teks slider di form utama
        const op = document.getElementById('input_opacity')?.value ?? '0.35';
        const valOp = document.getElementById('val_opacity');
        if (valOp) valOp.innerText = op;

        const lebar = document.getElementById('input_lebar')?.value ?? '3.0';
        const offsetY = document.getElementById('input_offset_y')?.value ?? '0';
        const offsetX = document.getElementById('input_offset_x')?.value ?? '0';
        const inputTinggi = document.getElementById('input_tinggi');
        if (inputTinggi) inputTinggi.value = lebar;

        const valLebar = document.getElementById('val_lebar');
        if (valLebar) valLebar.innerText = lebar + ' cm';
        const valOffsetY = document.getElementById('val_offset_y');
        if (valOffsetY) valOffsetY.innerText = offsetY + ' cm';
        const valOffsetX = document.getElementById('val_offset_x');
        if (valOffsetX) valOffsetX.innerText = offsetX + ' cm';

        const doc = getIframeDoc();
        if (!doc) return;

        // Teks Kop
        const kop1 = doc.getElementById('prev_kop_1'); if (kop1) kop1.innerText = document.getElementById('input_kop_1')?.value || '';
        const kop2 = doc.getElementById('prev_kop_2'); if (kop2) kop2.innerText = document.getElementById('input_kop_2')?.value || '';
        const kop3 = doc.getElementById('prev_kop_3'); if (kop3) kop3.innerText = document.getElementById('input_kop_3')?.value || '';
        const kop4 = doc.getElementById('prev_kop_4'); if (kop4) kop4.innerText = document.getElementById('input_kop_4')?.value || '';

        // Teks Penandatangan
        const jabatan = doc.getElementById('prev_jabatan'); if (jabatan) jabatan.innerText = document.getElementById('input_jabatan')?.value || '';
        const nama = doc.getElementById('prev_nama_kepala'); if (nama) nama.innerText = document.getElementById('input_nama_kepala')?.value || '';
        const nip = doc.getElementById('prev_nip_kepala'); if (nip) nip.innerText = document.getElementById('input_nip_kepala')?.value || '';
        const tempat = doc.getElementById('prev_tempat'); if (tempat) tempat.innerText = document.getElementById('input_tempat')?.value || '';

        // CSS Variables pada Bingkai Kartu Preview
        const cardWrapper = doc.getElementById('cardWrapper') || doc.querySelector('.kartu-wrapper');
        if (cardWrapper) {
            cardWrapper.style.setProperty('--watermark-opacity', op);

            const topVal = (0.1 + parseFloat(offsetY)) + 'cm';
            const rightVal = (1.0 + parseFloat(offsetX)) + 'cm';
            const stempelRight = (2.2 + parseFloat(offsetX)) + 'cm';

            cardWrapper.style.setProperty('--ttd-top', topVal);
            cardWrapper.style.setProperty('--ttd-right', rightVal);
            cardWrapper.style.setProperty('--ttd-width', lebar + 'cm');
            cardWrapper.style.setProperty('--ttd-height', lebar + 'cm');
            cardWrapper.style.setProperty('--stempel-top', topVal);
            cardWrapper.style.setProperty('--stempel-right', stempelRight);

            const showStempel = document.getElementById('check_stempel')?.checked ?? true;
            cardWrapper.classList.toggle('hide-stempel', !showStempel);
        }
    }

    // HTML5 Canvas Digital Signature Pad
    const canvas = document.getElementById('signatureCanvas');
    let ctx = null;
    let isDrawing = false;
    let hasDrawn = false;

    if (canvas) {
        ctx = canvas.getContext('2d');
        ctx.strokeStyle = '#000000';
        ctx.lineWidth = 2.5;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: (clientX - rect.left) * (canvas.width / rect.width),
                y: (clientY - rect.top) * (canvas.height / rect.height)
            };
        }

        function startDrawing(e) {
            isDrawing = true;
            hasDrawn = true;
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
            e.preventDefault();
        }

        function draw(e) {
            if (!isDrawing) return;
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            e.preventDefault();
        }

        function stopDrawing() {
            if (isDrawing) {
                isDrawing = false;
            }
        }

        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseleave', stopDrawing);

        canvas.addEventListener('touchstart', startDrawing);
        canvas.addEventListener('touchmove', draw);
        canvas.addEventListener('touchend', stopDrawing);
    }

    function clearSignaturePad() {
        if (canvas && ctx) {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            hasDrawn = false;
            document.getElementById('canvas_signature_input').value = '';
        }
    }

    function prepareCanvasSubmission() {
        if (hasDrawn && canvas) {
            const dataUrl = canvas.toDataURL('image/png');
            document.getElementById('canvas_signature_input').value = dataUrl;
        }
    }
</script>
@endpush
@endsection
