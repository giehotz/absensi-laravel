<x-print-preview-layout 
    :title="'Tata Tertib Ujian - ' . ($activePeriode->nama_periode ?? 'Madrasah')"
    orientation="portrait"
    paperSize="A4"
    :backUrl="route('ujian.administrasi.index')"
    pageMargin="1.2cm"
    sheetClass="p-6 sm:p-10"
>
    <style>
        .sheet-tatib {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            line-height: 1.35;
            color: #000;
            width: 100%;
        }
        .judul-tatib {
            text-align: center;
            margin: 12px 0 14px 0;
        }
        .judul-tatib h3 {
            margin: 0;
            font-size: 12.5pt;
            text-transform: uppercase;
            text-decoration: underline;
            font-weight: bold;
        }
        .judul-tatib .sub-judul {
            font-size: 10.5pt;
            font-weight: bold;
            margin-top: 2px;
        }
        .section-title {
            font-weight: bold;
            font-size: 10.5pt;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
            margin-top: 10px;
            margin-bottom: 5px;
        }
        ol {
            margin-top: 3px;
            margin-bottom: 8px;
            padding-left: 20px;
        }
        li {
            margin-bottom: 2.5px;
            text-align: justify;
        }
        .ttd-block {
            margin-top: 15px;
            width: 100%;
            display: flex;
            justify-content: flex-end;
        }
        .ttd-box {
            width: 250px;
            text-align: center;
            font-size: 10pt;
        }
    </style>

    <div class="sheet-tatib">
        <!-- Kop Surat Resmi -->
        <x-kop-surat :useBase64="true" />

        <div id="konten-tata-tertib-container">
            @if(!empty($ttdSetting?->konten_tata_tertib))
                {!! $ttdSetting->konten_tata_tertib !!}
            @else
                <div class="judul-tatib">
                    <h3>TATA TERTIB PESERTA DAN PENGAWAS UJIAN</h3>
                    <div class="sub-judul">
                        {{ strtoupper($activePeriode->nama_periode ?? 'UJIAN MADRASAH') }}
                        @if($activePeriode && $activePeriode->tahun_pelajaran)
                            - TAHUN PELAJARAN {{ $activePeriode->tahun_pelajaran }}
                        @endif
                    </div>
                </div>

                <!-- Bagian 1: Tata Tertib Peserta -->
                <div class="section-title">A. TATA TERTIB PESERTA UJIAN</div>
                <ol>
                    <li>Peserta memasuki ruangan ujian setelah tanda bel masuk dibunyikan, selambat-lambatnya 15 (lima belas) menit sebelum ujian dimulai.</li>
                    <li>Peserta yang terlambat hadir hanya diperkenankan mengikuti ujian setelah mendapat izin dari Ketua Panitia tanpa penambahan waktu pengerjaan.</li>
                    <li>Peserta wajib mengenakan seragam madrasah lengkap dan rapi sesuai ketentuan hari yang berlaku serta membawa Kartu Peserta Ujian.</li>
                    <li>Peserta wajib membawa alat tulis sendiri (pensil, pulpen, penghapus, peraut) dan dilarang saling meminjamkan alat tulis saat ujian berlangsung.</li>
                    <li>Peserta dilarang membawa tas, buku teks, catatan, kalkulator, handphone (HP), jam pintar (*smartwatch*), atau perangkat elektronik lainnya ke dalam ruang ujian. Semua barang bawaan diletakkan di depan kelas.</li>
                    <li>Peserta menempati tempat duduk yang telah ditentukan sesuai dengan Nomor Meja dan Nomor Peserta masing-masing.</li>
                    <li>Peserta mengisi identitas dan menandatangani Daftar Hadir Peserta Ujian yang telah disediakan oleh pengawas ruang.</li>
                    <li>Peserta memeriksa dan memastikan kelengkapan naskah soal serta lembar jawaban; jika ditemukan naskah yang rusak, cacat, atau tidak lengkap, segera melapor kepada pengawas.</li>
                    <li>Selama ujian berlangsung, peserta hanya dapat meninggalkan ruangan dengan seizin dan pengawasan dari pengawas ruang ujian.</li>
                    <li>Peserta yang telah selesai mengerjakan soal sebelum waktu berakhir diperkenankan meninggalkan ruangan setelah menyerahkan lembar jawaban kepada pengawas dan tidak boleh berada di sekitar ruang ujian.</li>
                    <li>Peserta yang melanggar tata tertib akan dikenakan sanksi berupa teguran lisan, pembatalan hasil ujian pada mata pelajaran yang bersangkutan, hingga diskualifikasi.</li>
                </ol>

                <!-- Bagian 2: Tata Tertib Pengawas -->
                <div class="section-title">B. TATA TERTIB PENGAWAS RUANG UJIAN</div>
                <ol>
                    <li>Pengawas ruang hadir di Ruang Sekretariat Panitia Ujian selambat-lambatnya 30 (tiga puluh) menit sebelum waktu pelaksanaan ujian dimulai.</li>
                    <li>Pengawas menerima pengarahan (*briefing*) dari Ketua Panitia dan menerima berkas ujian berupa: Amplop Soal, Lembar Jawaban, Daftar Hadir Peserta, dan Berita Acara Pelaksanaan (BAP).</li>
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

        <div class="ttd-block">
            <div class="ttd-box">
                <div>{{ $activePeriode->tempat_cetak ?? 'Tanggamus' }}, {{ date('d F Y') }}</div>
                <div style="font-weight: bold; margin-top: 2px;">Kepala Madrasah,</div>
                <div style="height: 50px;"></div>
                <div style="font-weight: bold; text-decoration: underline;">
                    {{ $ttdSetting->nama_kepala ?? ($setting->card_principal_name ?? 'Sipulloh, M.Pd') }}
                </div>
                <div style="font-size: 9pt;">
                    NIP. {{ $ttdSetting->nip_kepala ?? ($setting->card_principal_nip ?? '197005272007011022') }}
                </div>
            </div>
        </div>
    </div>
</x-print-preview-layout>