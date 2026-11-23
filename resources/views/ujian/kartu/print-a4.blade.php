@if($isPreview ?? false)
    {{-- Mode Live Preview Tunggal (Ukuran Nyata 9.8cm x 6.875cm) untuk di-embed ke /ujian/pengaturan-ttd --}}
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Preview Kartu Ujian</title>
        <style>
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
            .kartu-wrapper::after {
                content: "";
                position: absolute;
                top: 0; left: 0; right: 0; bottom: 0;
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
        </style>
    </head>
    <body>
        @php $peserta = $pesertaList->first(); @endphp
        <div class="preview-card-container">
            <div class="kartu-wrapper" id="cardWrapper">
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
    </body>
    </html>
@else
    {{-- Mode Full Pratinjau & Cetak A4 --}}
    @php
        $pages = $pesertaList->chunk(8);
    @endphp

    <x-print-preview-layout 
        :title="'Cetak Kartu Ujian (Grid 2x4) - ' . ($pengaturan->nama_instansi ?? 'MIN 2 Tanggamus')"
        orientation="portrait"
        paperSize="A4"
        :backUrl="route('ujian.kartu.index')"
        pageMargin="0"
        sheetClass="p-2 sm:p-4"
    >
        <x-slot:actions>
            <span class="text-xs font-bold font-mono bg-slate-100 border border-black px-2 py-1 rounded">
                Total: <strong>{{ $pesertaList->count() }}</strong> Siswa ({{ $pages->count() }} Lembar)
            </span>
            <button type="button" onclick="toggleCutGuides()" class="px-2 py-1 text-xs font-bold border-2 border-black rounded shadow-[1.5px_1.5px_0px_0px_#000] bg-[#FFD43B] hover:bg-amber-300 text-black">
                ✂️ Garis Potong
            </button>
        </x-slot:actions>

        <style>
            .kertas-kartu-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 0.35cm;
                width: 100%;
            }
            .kartu-wrapper {
                position: relative;
                width: 100%;
                border: 2px solid #000;
                box-sizing: border-box;
                padding: 6px;
                overflow: hidden;
                border-radius: 5px;
                background-color: #ffffff;
                font-family: Arial, sans-serif;
            }
            .show-cut-guides .kartu-wrapper {
                outline: 1px dashed #999;
                outline-offset: 1px;
            }
            .kartu-wrapper::after {
                content: "";
                position: absolute;
                top: 0; left: 0; right: 0; bottom: 0;
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
            .kartu-wrapper > * {
                position: relative;
                z-index: 1;
            }
            .header-kartu {
                display: flex;
                align-items: center;
                border-bottom: 2px solid #000;
                padding-bottom: 4px;
                margin-bottom: 4px;
            }
            .header-logo {
                width: 38px;
                height: auto;
                margin-right: 8px;
                object-fit: contain;
            }
            .header-teks {
                flex: 1;
                text-align: center;
            }
            .header-teks h3 {
                margin: 0;
                font-size: 12px;
                text-transform: uppercase;
                font-weight: 900;
            }
            .header-teks p {
                margin: 1px 0 0 0;
                font-size: 9.5px;
                font-weight: bold;
            }
            .tabel-data {
                width: 100%;
                font-size: 10px;
                border-collapse: collapse;
            }
            .tabel-data td {
                padding: 2px 1px;
                vertical-align: top;
            }
            .tabel-data .label {
                width: 36%;
                font-weight: bold;
            }
            .tabel-data .titik-dua {
                width: 4%;
                text-align: center;
            }
            .tabel-data .nilai {
                width: 60%;
            }
            .area-bawah {
                display: flex;
                width: 100%;
                margin-top: 4px;
                align-items: flex-end;
                justify-content: space-between;
            }
            .foto-area {
                width: 48px;
                flex: 0 0 48px;
                display: flex;
                align-items: flex-end;
                justify-content: flex-start;
                order: 1;
            }
            .foto-box {
                width: 48px;
                height: 62px;
                border: 1px dashed #94a3b8;
                background: #fff;
                box-sizing: border-box;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
            }
            .foto-box img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            .ttd-area {
                position: relative;
                font-size: 9.5px;
                padding-right: 2px;
                text-align: left;
                order: 2;
                max-width: 62%;
                min-height: 62px;
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
                width: 2.4cm;
                height: 2.4cm;
                background-image: url("{{ $pengaturan->stempel_url }}");
                background-size: contain;
                background-repeat: no-repeat;
                opacity: 0.85;
                pointer-events: none;
                z-index: 1;
            }
            @endif
        </style>

        <div id="kartuContainer">
            @forelse($pages as $page)
                <div class="kertas-a4">
                    <div class="kertas-kartu-grid {{ !$loop->last ? 'page-break' : '' }}">
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

                            {{-- 2. Bagian Tabel Identitas Peserta --}}
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
            </div>
            @empty
                <div style="text-align: center; padding: 40px;">
                    <h3>Tidak ada data peserta ujian yang siap dicetak.</h3>
                </div>
            @endforelse
        </div>

        <script>
            function toggleCutGuides() {
                const container = document.getElementById('kartuContainer');
                if (container) {
                    container.classList.toggle('show-cut-guides');
                }
            }
        </script>
    </x-print-preview-layout>
@endif