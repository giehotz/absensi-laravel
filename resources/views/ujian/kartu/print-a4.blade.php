<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Kartu Ujian (A4 Grid 2x4) - {{ $pengaturan->nama_instansi ?? 'MIN 2 Tanggamus' }}</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        @if($isPreview ?? false)
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: transparent !important;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 !important;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
        }
        .preview-card-container {
            width: 9.8cm;
            height: 6.875cm;
            box-sizing: border-box;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.15);
        }
        @else
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #e0e0e0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 0;
        }
        .kertas-a4 {
            width: 21cm;
            height: 29.7cm;
            background: white;
            padding: 0.5cm;
            box-sizing: border-box;
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: repeat(4, 1fr);
            gap: 0.4cm;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            page-break-after: always;
        }
        @endif

        /* WATERMARK pada setiap kartu */
        .kartu-wrapper {
            position: relative;
            width: 100%;
            height: 100%;
            border: 2px solid #000;
            box-sizing: border-box;
            padding: 5px;
            overflow: hidden;
            border-radius: 5px;
            background-color: #ffffff;
        }
        /* Mode Garis Potong (Cut Guides) */
        .show-cut-guides .kartu-wrapper {
            outline: 1px dashed #999;
            outline-offset: 1px;
        }
        /* Pseudo-element untuk watermark logo */
        .kartu-wrapper::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            @if($pengaturan->watermark_url)
            background-image: url("{{ $pengaturan->watermark_url }}");
            @endif
            background-repeat: no-repeat;
            background-position: center 60%;
            background-size: 40% auto;
            opacity: var(--watermark-opacity, {{ $pengaturan->opacity_watermark ?? 0.35 }});
            pointer-events: none;
            z-index: 0;
        }
        /* Konten kartu berada di atas watermark */
        .kartu-wrapper > * {
            position: relative;
            z-index: 1;
        }
        .header-kartu {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }
        .header-logo {
            width: 40px;
            height: auto;
            margin-right: 10px;
            object-fit: contain;
        }
        .header-teks {
            flex: 1;
            text-align: center;
            margin-right: 15px;
        }
        .header-teks h3 {
            margin: 0;
            font-size: 14px;
            text-transform: uppercase;
        }
        .header-teks p {
            margin: 2px 0 0 0;
            font-size: 11px;
            font-weight: bold;
        }
        .tabel-data {
            width: 100%;
            font-size: 11px;
            border-collapse: collapse;
        }
        .tabel-data td {
            padding: 3px 2px;
            vertical-align: top;
        }
        .tabel-data .label {
            width: 35%;
            font-weight: bold;
        }
        .tabel-data .titik-dua {
            width: 5%;
            text-align: center;
        }
        .tabel-data .nilai {
            width: 60%;
        }

        /* Area Bawah: Foto di Kiri Bawah, TTD di Kanan Bawah */
        .area-bawah {
            display: flex;
            width: 100%;
            margin-top: 4px;
            align-items: flex-end;
            justify-content: space-between;
        }
        .foto-area {
            width: 52px;
            flex: 0 0 52px;
            display: flex;
            align-items: flex-end;
            justify-content: flex-start;
            order: 1;
        }
        .foto-box {
            width: 52px;
            height: 68px;
            border: 1px dashed #94a3b8;
            background: #fff;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-bottom: 1px;
        }
        .foto-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .ttd-area {
            position: relative;
            font-size: 11px;
            padding-right: 4px;
            text-align: left;
            order: 2;
            max-width: 58%;
            min-height: 68px;
        }
        .ttd-area::before {
            content: "";
            position: absolute;
            top: var(--ttd-top, {{ ($pengaturan->offset_atas_ttd_cm ?? 0) != 0 ? (0.1 + $pengaturan->offset_atas_ttd_cm) . 'cm' : '0.1cm' }});
            right: var(--ttd-right, {{ ($pengaturan->offset_kanan_ttd_cm ?? 0) != 0 ? (1.0 + $pengaturan->offset_kanan_ttd_cm) . 'cm' : '1cm' }});
            width: var(--ttd-width, {{ $pengaturan->lebar_ttd_cm ?? 3 }}cm);
            height: var(--ttd-height, {{ $pengaturan->tinggi_ttd_cm ?? 3 }}cm);
            @if($pengaturan->signature_url)
            background-image: url("{{ $pengaturan->signature_url }}");
            @endif
            background-size: contain;
            background-repeat: no-repeat;
            pointer-events: none;
            z-index: 2;
        }
        @if($pengaturan->tampilkan_stempel && $pengaturan->stempel_url)
        .ttd-area::after {
            content: "";
            position: absolute;
            top: var(--stempel-top, {{ ($pengaturan->offset_atas_ttd_cm ?? 0) != 0 ? (0.1 + $pengaturan->offset_atas_ttd_cm) . 'cm' : '0.1cm' }});
            right: var(--stempel-right, {{ ($pengaturan->offset_kanan_ttd_cm ?? 0) != 0 ? (2.2 + $pengaturan->offset_kanan_ttd_cm) . 'cm' : '2.2cm' }});
            width: 2.8cm;
            height: 2.8cm;
            background-image: url("{{ $pengaturan->stempel_url }}");
            background-size: contain;
            background-repeat: no-repeat;
            opacity: 0.85;
            pointer-events: none;
            z-index: 1;
        }
        @endif
        .hide-stempel .ttd-area::after {
            display: none !important;
        }

        /* Floating Toolbar (Layar Monitor Sahaja) */
        .floating-toolbar {
            position: fixed;
            top: 15px;
            right: 20px;
            background: #ffffff;
            border: 3px solid #000;
            border-radius: 10px;
            padding: 8px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 4px 4px 0 #000;
            z-index: 1000;
        }
        .btn-toolbar {
            font-family: inherit;
            font-weight: 900;
            font-size: 11px;
            padding: 6px 12px;
            border: 2px solid #000;
            border-radius: 6px;
            cursor: pointer;
            box-shadow: 2px 2px 0 #000;
            text-decoration: none;
            color: #000;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .btn-print { background: #20C997; }
        .btn-print:hover { background: #12b886; }
        .btn-guides { background: #FFD43B; }
        .btn-close { background: #f1f5f9; }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            .floating-toolbar {
                display: none !important;
            }
            .kertas-a4 {
                margin-bottom: 0 !important;
                box-shadow: none !important;
                border: none !important;
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    @if(!($isPreview ?? false))
        {{-- Floating Control Bar (Hidden on print) --}}
        <div class="floating-toolbar">
            <span style="font-size: 11px; font-weight: bold;">
                Total: <strong>{{ $pesertaList->count() }}</strong> Kartu ({{ ceil($pesertaList->count() / 8) }} Lembar A4)
            </span>
            <button type="button" onclick="toggleCutGuides()" class="btn-toolbar btn-guides" title="Tampilkan garis bantu gunting">
                ✂️ Garis Potong
            </button>
            <button type="button" onclick="window.print()" class="btn-toolbar btn-print">
                🖨️ Cetak / Simpan PDF
            </button>
            <button type="button" onclick="window.close()" class="btn-toolbar btn-close">
                ✕ Tutup
            </button>
        </div>

        @php
            $pages = $pesertaList->chunk(8);
        @endphp

        @forelse($pages as $page)
            <div class="kertas-a4">
                @foreach($page as $peserta)
                    <div class="kartu-wrapper">
                        {{-- 1. Bagian Kop / Header Kartu --}}
                        <div class="header-kartu">
                            @if($pengaturan->logo_url)
                                <img class="header-logo" src="{{ $pengaturan->logo_url }}" alt="Logo">
                            @endif
                            <div class="header-teks">
                                <h3>{{ $pengaturan->kop_baris_1 ?: 'KARTU PESERTA' }}</h3>
                                <p>{{ $pengaturan->kop_baris_2 ?: ($periode->jenis?->judul_kop_kartu ?: 'ASESMEN SUMATIF') }}</p>
                                <p>{{ $pengaturan->kop_baris_3 ?: ($pengaturan->nama_instansi ?: 'MIN 2 TANGGAMUS') }}</p>
                                <p>{{ $pengaturan->kop_baris_4 ?: ('TAHUN PELAJARAN ' . $periode->tahun_pelajaran) }}</p>
                            </div>
                        </div>

                        {{-- 2. Bagian Tabel Identitas Peserta (Maksimal 5 Baris Ketat) --}}
                        <table class="tabel-data">
                            <tr>
                                <td class="label">Nama Peserta</td>
                                <td class="titik-dua">:</td>
                                <td class="nilai"><b>{{ $peserta->nama_peserta }}</b></td>
                            </tr>
                            <tr>
                                <td class="label">Tanggal Lahir</td>
                                <td class="titik-dua">:</td>
                                <td class="nilai">{{ $peserta->tempat_tanggal_lahir ?: ($peserta->tanggal_lahir ? $peserta->tanggal_lahir->format('d-m-Y') : '-') }}</td>
                            </tr>
                            <tr>
                                <td class="label">NISN</td>
                                <td class="titik-dua">:</td>
                                <td class="nilai">{{ $peserta->nisn ?: ($peserta->nis_lokal ?: '-') }}</td>
                            </tr>
                            <tr>
                                <td class="label">Nomor Peserta</td>
                                <td class="titik-dua">:</td>
                                <td class="nilai">{{ $peserta->nomor_peserta }}</td>
                            </tr>
                            <tr>
                                <td class="label">Kelas / Ruang</td>
                                <td class="titik-dua">:</td>
                                <td class="nilai">
                                    {{ $peserta->nama_kelas }}@if($peserta->ruang) / {{ $peserta->ruang->nama_ruang }}@endif
                                </td>
                            </tr>
                        </table>

                        {{-- 3. Bagian Bawah Kartu: Area Pasfoto (Kiri) & Blok TTD (Kanan) --}}
                        <div class="area-bawah">
                            <div class="foto-area">
                                <div class="foto-box">
                                    @if($peserta->foto_url)
                                        <img src="{{ $peserta->foto_url }}" alt="Foto">
                                    @elseif($pengaturan->tampilkan_qrcode_validasi && !empty($peserta->qr_data_uri))
                                        <img src="{{ $peserta->qr_data_uri }}" alt="QR" style="width: 100%; height: auto; object-fit: contain;">
                                    @else
                                        <div style="font-size: 8px; color: #999; text-align: center; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                                            FOTO<br>3x4
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="ttd-area">
                                <div class="ttd-text">
                                    {{ $pengaturan->tempat_penetapan ?: 'Tanggamus' }}, {{ $pengaturan->tanggal_penetapan ? $pengaturan->tanggal_penetapan->locale('id')->translatedFormat('d F Y') : date('d F Y') }}<br>
                                    {{ $pengaturan->jabatan_penandatangan ?: 'Kepala Madrasah' }}<br><br><br>
                                    <b>{{ $pengaturan->nama_kepala ?: 'Sipulloh, M.Pd' }}</b><br>
                                    NIP.{{ $pengaturan->nip_kepala ?: '197005272007011022' }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @empty
            <div style="background: white; padding: 40px; border: 3px solid black; text-align: center; border-radius: 8px;">
                <h3>Tidak ada data peserta ujian yang siap dicetak.</h3>
                <p>Silakan kembali dan periksa filter atau sinkronkan data siswa terlebih dahulu.</p>
            </div>
        @endforelse

        <script>
            function toggleCutGuides() {
                document.querySelectorAll('.kertas-a4').forEach(function(el) {
                    el.classList.toggle('show-cut-guides');
                });
            }

            // Panggil window.print() otomatis setelah semua aset selesai dimuat (hanya pada mode cetak dokumen)
            window.onload = function() {
                setTimeout(function() {
                    window.print();
                }, 600);
            };
        </script>
    @else
        {{-- Mode Live Preview Tunggal (Ukuran Nyata 9.8cm x 6.875cm) untuk di-embed ke /ujian/pengaturan-ttd --}}
        @php
            $peserta = $pesertaList->first();
        @endphp
        <div class="preview-card-container">
            <div class="kartu-wrapper" id="cardWrapper">
                {{-- 1. Bagian Kop / Header Kartu --}}
                <div class="header-kartu">
                    @if($pengaturan->logo_url)
                        <img class="header-logo" id="prev_logo_img" src="{{ $pengaturan->logo_url }}" alt="Logo">
                    @endif
                    <div class="header-teks">
                        <h3 id="prev_kop_1">{{ $pengaturan->kop_baris_1 ?: 'KARTU PESERTA' }}</h3>
                        <p id="prev_kop_2">{{ $pengaturan->kop_baris_2 ?: ($periode->jenis?->judul_kop_kartu ?: 'ASESMEN SUMATIF') }}</p>
                        <p id="prev_kop_3">{{ $pengaturan->kop_baris_3 ?: ($pengaturan->nama_instansi ?: 'MIN 2 TANGGAMUS') }}</p>
                        <p id="prev_kop_4">{{ $pengaturan->kop_baris_4 ?: ('TAHUN PELAJARAN ' . $periode->tahun_pelajaran) }}</p>
                    </div>
                </div>

                {{-- 2. Bagian Tabel Identitas Peserta --}}
                <table class="tabel-data">
                    <tr>
                        <td class="label">Nama Peserta</td>
                        <td class="titik-dua">:</td>
                        <td class="nilai"><b id="prev_nama_peserta">{{ $peserta->nama_peserta ?? 'ARCIO RAZIQ DANISH SYAHPUTRA' }}</b></td>
                    </tr>
                    <tr>
                        <td class="label">Tanggal Lahir</td>
                        <td class="titik-dua">:</td>
                        <td class="nilai">{{ $peserta->tempat_tanggal_lahir ?? 'PRINGSEWU, 17 November 2019' }}</td>
                    </tr>
                    <tr>
                        <td class="label">NISN</td>
                        <td class="titik-dua">:</td>
                        <td class="nilai">{{ $peserta->nisn ?? '3192770807' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Nomor Peserta</td>
                        <td class="titik-dua">:</td>
                        <td class="nilai">{{ $peserta->nomor_peserta ?? '26-0101-001' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Kelas / Ruang</td>
                        <td class="titik-dua">:</td>
                        <td class="nilai">
                            {{ $peserta->nama_kelas ?? 'Kelas 1 a' }}@if(!empty($peserta->ruang?->nama_ruang)) / {{ $peserta->ruang->nama_ruang }}@endif
                        </td>
                    </tr>
                </table>

                {{-- 3. Bagian Bawah Kartu: Pasfoto di Kiri Bawah, TTD di Kanan Bawah --}}
                <div class="area-bawah">
                    <div class="foto-area">
                        <div class="foto-box">
                            @if(!empty($peserta->foto_url))
                                <img src="{{ $peserta->foto_url }}" alt="Foto">
                            @elseif($pengaturan->tampilkan_qrcode_validasi && !empty($peserta->qr_data_uri))
                                <img src="{{ $peserta->qr_data_uri }}" alt="QR" style="width: 100%; height: auto; object-fit: contain;">
                            @else
                                <div style="font-size: 8px; color: #999; text-align: center; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                                    FOTO<br>3x4
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="ttd-area" id="prev_ttd_area">
                        <div class="ttd-text">
                            <span id="prev_tempat">{{ $pengaturan->tempat_penetapan ?: 'Tanggamus' }}</span>, {{ $pengaturan->tanggal_penetapan ? $pengaturan->tanggal_penetapan->locale('id')->translatedFormat('d F Y') : date('d F Y') }}<br>
                            <span id="prev_jabatan">{{ $pengaturan->jabatan_penandatangan ?: 'Kepala Madrasah' }}</span><br><br><br>
                            <b id="prev_nama_kepala">{{ $pengaturan->nama_kepala ?: 'Sipulloh, M.Pd' }}</b><br>
                            NIP.<span id="prev_nip_kepala">{{ $pengaturan->nip_kepala ?: '197005272007011022' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</body>
</html>
