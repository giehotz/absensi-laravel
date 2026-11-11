<style>
    /* Modal Backdrop */
    .modal-editor-backdrop {
        position: fixed;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.7);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        font-family: Arial, sans-serif;
    }
    .modal-editor-backdrop.hidden {
        display: none !important;
    }
    .modal-editor-window {
        background: #ffffff;
        border: 4px solid #000000;
        border-radius: 12px;
        box-shadow: 8px 8px 0 #000000;
        width: 100%;
        max-width: 950px;
        max-height: 92vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .modal-editor-header {
        background: #F8FAFC;
        border-bottom: 2px solid #000;
        padding: 12px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .modal-editor-toolbar {
        background: #F1F5F9;
        border-bottom: 2px solid #000;
        padding: 8px 12px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 4px;
    }
    .tb-btn {
        background: #ffffff;
        border: 1.5px solid #000000;
        border-radius: 4px;
        padding: 4px 8px;
        font-size: 11px;
        font-weight: bold;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 26px;
        height: 26px;
        box-shadow: 1px 1px 0 #000;
    }
    .tb-btn:hover {
        background: #FFD43B;
    }
    .tb-btn:active {
        box-shadow: none;
        transform: translate(1px, 1px);
    }
    .tb-select {
        border: 1.5px solid #000000;
        border-radius: 4px;
        padding: 3px 6px;
        font-size: 11px;
        font-weight: bold;
        height: 26px;
        background: white;
    }
    .tb-divider {
        width: 1px;
        height: 22px;
        background: #CBD5E1;
        margin: 0 4px;
    }
    .modal-editor-body {
        padding: 16px;
        overflow-y: auto;
        flex: 1;
        background: #F8FAFC;
    }
    #wysiwygCanvas {
        background: #ffffff;
        border: 2px solid #000000;
        border-radius: 6px;
        padding: 20px;
        min-height: 380px;
        font-family: 'Times New Roman', Times, serif;
        font-size: 11pt;
        line-height: 1.4;
        color: #000;
        outline: none;
        box-shadow: inset 1px 1px 3px rgba(0,0,0,0.05);
    }
    #wysiwygCanvas:focus {
        border-color: #5294FF;
        box-shadow: 0 0 0 2px #5294FF;
    }
    #wysiwygCanvas table {
        width: 100%;
        border-collapse: collapse;
        margin: 8px 0;
    }
    #wysiwygCanvas table, #wysiwygCanvas th, #wysiwygCanvas td {
        border: 1px solid #000;
        padding: 4px 6px;
    }
    #wysiwygRawHtml {
        width: 100%;
        height: 380px;
        border: 2px solid #000;
        border-radius: 6px;
        padding: 12px;
        font-family: 'Courier New', Courier, monospace;
        font-size: 11px;
        box-sizing: border-box;
        display: none;
        background: #1E293B;
        color: #E2E8F0;
        resize: vertical;
    }
    .modal-editor-footer {
        background: #F8FAFC;
        border-top: 2px solid #000;
        padding: 12px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
</style>

<!-- Hidden Content Source Containers -->
<div id="konten_source_tatib" class="hidden">
    @if(!empty($ttdSetting?->konten_tata_tertib))
        {!! $ttdSetting->konten_tata_tertib !!}
    @else
        <div class="judul-tatib" style="text-align: center; margin-bottom: 15px;">
            <h3 style="margin: 0; font-size: 13pt; text-decoration: underline;">TATA TERTIB PESERTA DAN PENGAWAS UJIAN</h3>
            <div class="sub-judul" style="font-size: 10.5pt; font-weight: bold; margin-top: 2px;">
                {{ strtoupper($activePeriode->nama_periode ?? 'UJIAN MADRASAH') }}
                @if($activePeriode && $activePeriode->tahun_pelajaran)
                    - TAHUN PELAJARAN {{ $activePeriode->tahun_pelajaran }}
                @endif
            </div>
        </div>

        <div class="section-title" style="font-weight: bold; font-size: 10.5pt; margin-top: 10px; margin-bottom: 4px; text-decoration: underline;">A. TATA TERTIB PESERTA UJIAN</div>
        <ol style="margin: 0 0 10px 0; padding-left: 20px; line-height: 1.4;">
            <li>Peserta memasuki ruangan ujian setelah tanda bel masuk dibunyikan, selambat-lambatnya 15 (lima belas) menit sebelum ujian dimulai.</li>
            <li>Peserta yang terlambat hadir hanya diperkenankan mengikuti ujian setelah mendapat izin dari Ketua Panitia tanpa penambahan waktu pengerjaan.</li>
            <li>Peserta wajib mengenakan seragam madrasah lengkap dan rapi sesuai ketentuan hari yang berlaku serta membawa Kartu Peserta Ujian.</li>
            <li>Peserta wajib membawa alat tulis sendiri (pensil, pulpen, penghapus, peraut) dan dilarang saling meminjamkan alat tulis saat ujian berlangsung.</li>
            <li>Peserta dilarang membawa tas, buku teks, catatan, kalkulator, handphone (HP), jam pintar (smartwatch), atau perangkat elektronik lainnya ke dalam ruang ujian. Semua barang bawaan diletakkan di depan kelas.</li>
            <li>Peserta menempati tempat duduk yang telah ditentukan sesuai dengan Nomor Meja dan Nomor Peserta masing-masing.</li>
            <li>Peserta mengisi identitas dan menandatangani Daftar Hadir Peserta Ujian yang telah disediakan oleh pengawas ruang.</li>
            <li>Peserta memeriksa dan memastikan kelengkapan naskah soal serta lembar jawaban; jika ditemukan naskah yang rusak, cacat, atau tidak lengkap, segera melapor kepada pengawas.</li>
            <li>Selama ujian berlangsung, peserta hanya dapat meninggalkan ruangan dengan seizin dan pengawasan dari pengawas ruang ujian.</li>
            <li>Peserta yang telah selesai mengerjakan soal sebelum waktu berakhir diperkenankan meninggalkan ruangan setelah menyerahkan lembar jawaban kepada pengawas dan tidak boleh berada di sekitar ruang ujian.</li>
            <li>Peserta yang melanggar tata tertib akan dikenakan sanksi berupa teguran lisan, pembatalan hasil ujian pada mata pelajaran yang bersangkutan, hingga diskualifikasi.</li>
        </ol>

        <div class="section-title" style="font-weight: bold; font-size: 10.5pt; margin-top: 10px; margin-bottom: 4px; text-decoration: underline;">B. TATA TERTIB PENGAWAS RUANG UJIAN</div>
        <ol style="margin: 0 0 10px 0; padding-left: 20px; line-height: 1.4;">
            <li>Pengawas ruang hadir di Ruang Sekretariat Panitia Ujian selambat-lambatnya 30 (tiga puluh) menit sebelum waktu pelaksanaan ujian dimulai.</li>
            <li>Pengawas menerima pengarahan (briefing) dari Ketua Panitia dan menerima berkas ujian berupa: Amplop Soal, Lembar Jawaban, Daftar Hadir Peserta, dan Berita Acara Pelaksanaan (BAP).</li>
            <li>Pengawas memasuki ruangan ujian 15 menit sebelum bel pengerjaan dimulai untuk memeriksa kesiapan ruang, kebersihan, dan tempat duduk peserta.</li>
            <li>Pengawas memeriksa identitas peserta dan mencocokkan wajah peserta dengan Kartu Peserta Ujian.</li>
            <li>Pengawas membacakan petunjuk umum dan tata tertib ujian serta memimpin doa sebelum pengerjaan soal dimulai.</li>
            <li>Pengawas membuka amplop naskah soal di depan peserta ujian dan membagikan naskah soal serta lembar jawaban kepada seluruh peserta secara tertib.</li>
            <li>Pengawas mengedarkan Daftar Hadir Peserta Ujian dan memastikan setiap siswa menandatangani kolom yang sesuai.</li>
            <li>Selama ujian berlangsung, pengawas wajib menjaga ketenangan dan ketertiban ruang serta dilarang membaca buku, merokok, menggunakan HP/gadget, atau melakukan aktivitas lain yang mengganggu fokus peserta.</li>
            <li>Sepuluh menit sebelum waktu ujian berakhir, pengawas memberi peringatan waktu kepada peserta ujian.</li>
            <li>Setelah waktu ujian selesai, pengawas meminta peserta berhenti mengerjakan soal, mengumpulkan lembar jawaban serta naskah soal, dan menghitung kecocokan jumlah berkas dengan jumlah peserta hadir.</li>
            <li>Pengawas mengisi dan menandatangani Berita Acara Pelaksanaan (BAP), memasukkan seluruh berkas ke dalam amplop ujian, dan menyerahkannya kembali ke Ruang Sekretariat Panitia.</li>
        </ol>
    @endif
</div>

<div id="konten_source_sk" class="hidden">
    @if(!empty($ttdSetting?->konten_sk_panitia))
        {!! $ttdSetting->konten_sk_panitia !!}
    @else
        <div class="judul-sk" style="text-align: center; margin-bottom: 12px;">
            <h3 style="margin: 0; font-size: 11pt;">KEPUTUSAN KEPALA MADRASAH IBTIDAIYAH NEGERI 2 TANGGAMUS</h3>
            <div class="nomor-sk" style="font-size: 10pt; font-weight: bold; margin: 3px 0;">NOMOR: B-&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/Mi.08.04/PP.01.1/{{ date('m/Y') }}</div>
            <div class="tentang" style="font-size: 10pt; font-weight: bold; margin-top: 5px; line-height: 1.3;">
                TENTANG<br>
                PENETAPAN PANITIA PELAKSANA {{ strtoupper($activePeriode->nama_periode ?? 'UJIAN MADRASAH') }}<br>
                TAHUN PELAJARAN {{ $activePeriode->tahun_pelajaran ?? '-' }}
            </div>
            <div style="font-size: 10pt; font-weight: bold; margin-top: 6px;">
                DENGAN RAHMAT TUHAN YANG MAHA ESA<br>
                KEPALA MADRASAH IBTIDAIYAH NEGERI 2 TANGGAMUS,
            </div>
        </div>

        <table class="konsiderans-table" style="width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 10pt; line-height: 1.35;">
            <tr>
                <td style="width: 15%; vertical-align: top; font-weight: bold;">Menimbang</td>
                <td style="width: 2%; vertical-align: top;">:</td>
                <td style="width: 83%; text-align: justify; vertical-align: top;">
                    a. Bahwa dalam rangka menjamin kelancaran, ketertiban, dan mutu penyelenggaraan {{ $activePeriode->nama_periode ?? 'Ujian Madrasah' }}, dipandang perlu membentuk Panitia Pelaksana;<br>
                    b. Bahwa guru/pegawai yang namanya tercantum dalam lampiran keputusan ini dipandang cakap dan memenuhi syarat untuk ditugaskan sebagai panitia pelaksana.
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top; font-weight: bold;">Mengingat</td>
                <td style="vertical-align: top;">:</td>
                <td style="text-align: justify; vertical-align: top;">
                    1. Undang-Undang Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional;<br>
                    2. Peraturan Menteri Agama RI Nomor 90 Tahun 2013 tentang Penyelenggaraan Pendidikan Madrasah;<br>
                    3. Keputusan Direktur Jenderal Pendidikan Islam Kementerian Agama RI tentang Petunjuk Teknis Penyelenggaraan Asesmen Madrasah.
                </td>
            </tr>
        </table>

        <div style="text-align: center; font-weight: bold; margin: 8px 0; font-size: 10pt;">MEMUTUSKAN:</div>

        <table class="diktum-table" style="width: 100%; border-collapse: collapse; font-size: 10pt; line-height: 1.35;">
            <tr>
                <td style="width: 15%; vertical-align: top; font-weight: bold;">Menetapkan</td>
                <td style="width: 2%; vertical-align: top;">:</td>
                <td style="width: 83%; vertical-align: top; font-weight: bold;">
                    KEPUTUSAN KEPALA MADRASAH TENTANG PENETAPAN PANITIA PELAKSANA {{ strtoupper($activePeriode->nama_periode ?? 'UJIAN MADRASAH') }} TAHUN PELAJARAN {{ $activePeriode->tahun_pelajaran ?? '-' }}.
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top; font-weight: bold;">KESATU</td>
                <td style="vertical-align: top;">:</td>
                <td style="text-align: justify; vertical-align: top;">
                    Menetapkan susunan Panitia Pelaksana Ujian sebagaimana tercantum dalam lampiran yang merupakan bagian tidak terpisahkan dari keputusan ini.
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top; font-weight: bold;">KEDUA</td>
                <td style="vertical-align: top;">:</td>
                <td style="text-align: justify; vertical-align: top;">
                    Panitia Pelaksana bertugas merencanakan, mengorganisasikan, mengamankan naskah, mengawasi pelaksanaan, serta melaporkan hasil evaluasi pelaksanaan ujian kepada Kepala Madrasah.
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top; font-weight: bold;">KETIGA</td>
                <td style="vertical-align: top;">:</td>
                <td style="text-align: justify; vertical-align: top;">
                    Segala biaya yang timbul akibat diterbitkannya keputusan ini dibebankan pada anggaran Bantuan Operasional Sekolah (BOS) yang sah.
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top; font-weight: bold;">KEEMPAT</td>
                <td style="vertical-align: top;">:</td>
                <td style="text-align: justify; vertical-align: top;">
                    Keputusan ini berlaku sejak tanggal ditetapkan, dan apabila terdapat kekeliruan akan diperbaiki sebagaimana mestinya.
                </td>
            </tr>
        </table>
    @endif
</div>

<!-- Modal WYSIWYG Editor Universal -->
<div id="modalEditorWysiwyg" class="modal-editor-backdrop hidden">
    <div class="modal-editor-window animate-in fade-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="modal-editor-header">
            <div>
                <h3 style="margin: 0; font-size: 14px; font-weight: 900; display: flex; align-items: center; gap: 6px;">
                    <span>✏️</span>
                    <span id="modalEditorDocTitle">Editor Dokumen Resmi</span>
                </h3>
                <p id="modalEditorDocSub" style="margin: 2px 0 0 0; font-size: 11px; color: #64748B;">
                    Ubah isi format teks dokumen sesuai kebutuhan madrasah. Hasil pembaruan akan langsung tersimpan dan tercetak.
                </p>
            </div>
            <button type="button" onclick="tutupEditorWysiwyg()" style="background: none; border: none; font-size: 18px; font-weight: bold; cursor: pointer; color: #64748B;">✕</button>
        </div>

        <!-- Toolbar WYSIWYG -->
        <div class="modal-editor-toolbar" id="toolbarContainer">
            <!-- Format Block -->
            <select onchange="execFormatBlock(this.value); this.selectedIndex=0;" class="tb-select" title="Gaya Teks">
                <option value="">-- Gaya Format --</option>
                <option value="p">Paragraf Normal</option>
                <option value="h2">Judul Besar (H2)</option>
                <option value="h3">Sub-Judul (H3)</option>
                <option value="h4">Header Kecil (H4)</option>
            </select>

            <div class="tb-divider"></div>

            <!-- Basic Formatting -->
            <button type="button" class="tb-btn" onclick="execCmd('bold')" title="Tebal (Ctrl+B)"><strong>B</strong></button>
            <button type="button" class="tb-btn" onclick="execCmd('italic')" title="Miring (Ctrl+I)"><em>I</em></button>
            <button type="button" class="tb-btn" onclick="execCmd('underline')" title="Garis Bawah (Ctrl+U)"><u>U</u></button>
            <button type="button" class="tb-btn" onclick="execCmd('strikeThrough')" title="Coret"><s>S</s></button>

            <div class="tb-divider"></div>

            <!-- Alignment -->
            <button type="button" class="tb-btn" onclick="execCmd('justifyLeft')" title="Rata Kiri">⫷</button>
            <button type="button" class="tb-btn" onclick="execCmd('justifyCenter')" title="Rata Tengah">≡</button>
            <button type="button" class="tb-btn" onclick="execCmd('justifyRight')" title="Rata Kanan">⫸</button>
            <button type="button" class="tb-btn" onclick="execCmd('justifyFull')" title="Rata Kanan-Kiri">≣</button>

            <div class="tb-divider"></div>

            <!-- Lists -->
            <button type="button" class="tb-btn" onclick="execCmd('insertOrderedList')" title="Daftar Bernomor (1, 2, 3)">1. 2.</button>
            <button type="button" class="tb-btn" onclick="execCmd('insertUnorderedList')" title="Daftar Poin (Bullet)">• •</button>
            <button type="button" class="tb-btn" onclick="execCmd('outdent')" title="Kurangi Indentasi">⇤</button>
            <button type="button" class="tb-btn" onclick="execCmd('indent')" title="Tambah Indentasi">⇥</button>

            <div class="tb-divider"></div>

            <!-- Table & Helper -->
            <button type="button" class="tb-btn" onclick="sisipTabel()" title="Sisipkan Tabel 2x2">▦ Tabel</button>
            <button type="button" class="tb-btn" onclick="execCmd('removeFormat')" title="Hapus Format">🧹 Bersihkan</button>
            <button type="button" class="tb-btn" onclick="execCmd('undo')" title="Undo">↩</button>
            <button type="button" class="tb-btn" onclick="execCmd('redo')" title="Redo">↪</button>

            <div class="tb-divider"></div>

            <!-- Mode HTML Source Toggle -->
            <button type="button" id="btnToggleSource" class="tb-btn" onclick="toggleSourceMode()" title="Lihat / Edit Kode HTML" style="padding: 4px 10px; background: #E2E8F0;">
                &lt;/&gt; HTML Source
            </button>
        </div>

        <!-- Editor Body -->
        <div class="modal-editor-body">
            <div id="wysiwygCanvas" contenteditable="true" spellcheck="false"></div>
            <textarea id="wysiwygRawHtml" spellcheck="false" placeholder="Ketik atau edit kode HTML dokumen..."></textarea>
        </div>

        <!-- Footer -->
        <div class="modal-editor-footer">
            <div>
                <button type="button" onclick="konfirmasiResetStandar()" style="background: #F1F5F9; border: 1.5px solid #000; border-radius: 6px; padding: 6px 12px; font-size: 11px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 4px; box-shadow: 1px 1px 0 #000;">
                    <span>🔄</span>
                    <span>Reset ke Standar Bawaan</span>
                </button>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <button type="button" onclick="tutupEditorWysiwyg()" style="background: #FFFFFF; border: 1.5px solid #000; border-radius: 6px; padding: 6px 14px; font-size: 12px; font-weight: bold; cursor: pointer;">
                    Batal
                </button>
                <button type="button" id="btnSimpanWysiwyg" onclick="simpanKontenWysiwyg()" style="background: #FFD43B; border: 2px solid #000; border-radius: 6px; padding: 6px 16px; font-size: 12px; font-weight: 900; cursor: pointer; box-shadow: 2px 2px 0 #000; display: flex; align-items: center; gap: 6px;">
                    <span>💾</span>
                    <span>Simpan Pembaruan</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let isSourceMode = false;
    let activeDocType = 'tatib';

    const docConfigs = {
        'tatib': {
            title: 'Tata Tertib Peserta & Pengawas',
            sub: 'Perbarui butir aturan tata tertib untuk {{ $activePeriode->nama_periode ?? "Ujian Madrasah" }}',
            updateRoute: '{{ route("ujian.administrasi.tata-tertib.update") }}',
            resetRoute: '{{ route("ujian.administrasi.tata-tertib.reset") }}',
            fieldKey: 'konten_tata_tertib',
            sourceId: 'konten_source_tatib'
        },
        'sk': {
            title: 'SK Panitia Ujian Resmi',
            sub: 'Perbarui nomor surat, konsiderans, dan diktum SK untuk {{ $activePeriode->nama_periode ?? "Ujian Madrasah" }}',
            updateRoute: '{{ route("ujian.administrasi.sk-panitia.update") }}',
            resetRoute: '{{ route("ujian.administrasi.sk-panitia.reset") }}',
            fieldKey: 'konten_sk_panitia',
            sourceId: 'konten_source_sk'
        }
    };

    function execCmd(command, value = null) {
        if (isSourceMode) {
            alert('Silakan keluar dari mode kode HTML terlebih dahulu untuk menggunakan tombol pemformatan.');
            return;
        }
        document.getElementById('wysiwygCanvas').focus();
        document.execCommand(command, false, value);
    }

    function execFormatBlock(tag) {
        if (!tag) return;
        execCmd('formatBlock', `<${tag}>`);
    }

    function sisipTabel() {
        if (isSourceMode) return;
        const html = '<table style="width: 100%; border-collapse: collapse; margin: 8px 0;"><tr><td style="border: 1px solid #000; padding: 4px;">Kolom 1</td><td style="border: 1px solid #000; padding: 4px;">Kolom 2</td></tr><tr><td style="border: 1px solid #000; padding: 4px;">Isi 1</td><td style="border: 1px solid #000; padding: 4px;">Isi 2</td></tr></table>';
        execCmd('insertHTML', html);
    }

    function toggleSourceMode() {
        const canvas = document.getElementById('wysiwygCanvas');
        const raw = document.getElementById('wysiwygRawHtml');
        const btn = document.getElementById('btnToggleSource');

        if (!isSourceMode) {
            raw.value = canvas.innerHTML;
            canvas.style.display = 'none';
            raw.style.display = 'block';
            btn.style.background = '#FFD43B';
            btn.textContent = '👁️ Mode Visual';
            isSourceMode = true;
        } else {
            canvas.innerHTML = raw.value;
            raw.style.display = 'none';
            canvas.style.display = 'block';
            btn.style.background = '#E2E8F0';
            btn.textContent = '</> HTML Source';
            isSourceMode = false;
        }
    }

    function bukaEditorDokumen(type) {
        if (!docConfigs[type]) return;
        activeDocType = type;
        const conf = docConfigs[type];

        document.getElementById('modalEditorDocTitle').textContent = 'Editor: ' + conf.title;
        document.getElementById('modalEditorDocSub').textContent = conf.sub;

        const sourceEl = document.getElementById(conf.sourceId);
        const canvas = document.getElementById('wysiwygCanvas');
        const raw = document.getElementById('wysiwygRawHtml');

        if (sourceEl) {
            canvas.innerHTML = sourceEl.innerHTML.trim();
            raw.value = sourceEl.innerHTML.trim();
        }

        if (isSourceMode) {
            toggleSourceMode();
        }

        document.getElementById('modalEditorWysiwyg').classList.remove('hidden');
    }

    function tutupEditorWysiwyg() {
        document.getElementById('modalEditorWysiwyg').classList.add('hidden');
    }

    async function simpanKontenWysiwyg() {
        const conf = docConfigs[activeDocType];
        const canvas = document.getElementById('wysiwygCanvas');
        const raw = document.getElementById('wysiwygRawHtml');
        const btn = document.getElementById('btnSimpanWysiwyg');

        let kontenFinal = isSourceMode ? raw.value : canvas.innerHTML;

        btn.disabled = true;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span>⏳ Menyimpan...</span>';

        try {
            const response = await fetch(conf.updateRoute, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    periode_id: '{{ $activePeriode?->id }}',
                    [conf.fieldKey]: kontenFinal
                })
            });

            const data = await response.json();
            if (response.ok && data.success) {
                const sourceEl = document.getElementById(conf.sourceId);
                if (sourceEl) {
                    sourceEl.innerHTML = kontenFinal;
                }
                tutupEditorWysiwyg();
                alert(data.message || 'Pembaruan dokumen berhasil disimpan!');
            } else {
                alert('Gagal menyimpan: ' + (data.message || 'Terjadi kesalahan sistem.'));
            }
        } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan koneksi saat menyimpan dokumen.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }

    async function konfirmasiResetStandar() {
        const conf = docConfigs[activeDocType];
        if (!confirm('Apakah Anda yakin ingin mengembalikan isi ' + conf.title + ' ini ke format standar bawaan madrasah? Perubahan kustom yang belum disimpan akan hilang.')) {
            return;
        }

        try {
            const response = await fetch(conf.resetRoute, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    periode_id: '{{ $activePeriode?->id }}'
                })
            });

            const data = await response.json();
            if (response.ok && data.success) {
                alert(data.message || 'Berhasil direset!');
                window.location.reload();
            } else {
                alert('Gagal mereset: ' + (data.message || 'Terjadi kesalahan sistem.'));
            }
        } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan koneksi saat mereset dokumen.');
        }
    }
</script>
