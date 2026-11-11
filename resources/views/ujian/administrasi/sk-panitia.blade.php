<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SK Panitia Ujian - {{ $activePeriode->nama_periode ?? 'Madrasah' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.2cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10.5pt;
            line-height: 1.35;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 10px;
        }
        .page-break {
            page-break-after: always;
        }
        .judul-sk {
            text-align: center;
            margin: 12px 0 15px 0;
        }
        .judul-sk h3 {
            margin: 0;
            font-size: 11.5pt;
            text-transform: uppercase;
            font-weight: bold;
        }
        .judul-sk .nomor-sk {
            font-size: 10pt;
            margin-top: 2px;
            font-weight: bold;
        }
        .judul-sk .tentang {
            font-size: 10.5pt;
            font-weight: bold;
            margin-top: 8px;
            text-transform: uppercase;
        }
        .konsiderans-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 10pt;
        }
        .konsiderans-table td {
            vertical-align: top;
            padding: 2.5px 3px;
        }
        .diktum-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 10pt;
        }
        .diktum-table td {
            vertical-align: top;
            padding: 2.5px 3px;
        }
        .tabel-panitia {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 15px;
            font-size: 10pt;
        }
        .tabel-panitia th, .tabel-panitia td {
            border: 1px solid #000;
            padding: 5px 8px;
        }
        .tabel-panitia th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .ttd-block {
            margin-top: 20px;
            width: 100%;
            display: flex;
            justify-content: flex-end;
        }
        .ttd-box {
            width: 250px;
            text-align: center;
            font-size: 10pt;
        }
        .print-toolbar {
            position: fixed;
            top: 15px;
            right: 15px;
            background: #FFD43B;
            border: 2px solid #000;
            padding: 8px 16px;
            font-family: Arial, sans-serif;
            font-weight: bold;
            font-size: 12px;
            cursor: pointer;
            box-shadow: 2px 2px 0 #000;
            border-radius: 6px;
            z-index: 999;
        }
        @media print {
            .print-toolbar { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <button type="button" onclick="window.print()" class="print-toolbar">
        🖨️ Cetak / Simpan PDF
    </button>

    <!-- Halaman 1: Lembar Keputusan -->
    <div class="page-break">
        <!-- Kop Surat Resmi -->
        <x-kop-surat :useBase64="true" />

        <div id="konten-sk-container">
            @if(!empty($ttdSetting?->konten_sk_panitia))
                {!! $ttdSetting->konten_sk_panitia !!}
            @else
                <div class="judul-sk">
                    <h3>KEPUTUSAN KEPALA MADRASAH IBTIDAIYAH NEGERI 2 TANGGAMUS</h3>
                    <div class="nomor-sk">NOMOR: B-&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/Mi.08.04/PP.01.1/{{ date('m/Y') }}</div>
                    <div class="tentang">
                        TENTANG<br>
                        PENETAPAN PANITIA PELAKSANA {{ strtoupper($activePeriode->nama_periode ?? 'UJIAN MADRASAH') }}<br>
                        TAHUN PELAJARAN {{ $activePeriode->tahun_pelajaran ?? '-' }}
                    </div>
                    <div style="font-size: 10pt; font-weight: bold; margin-top: 6px;">
                        DENGAN RAHMAT TUHAN YANG MAHA ESA<br>
                        KEPALA MADRASAH IBTIDAIYAH NEGERI 2 TANGGAMUS,
                    </div>
                </div>

                <table class="konsiderans-table">
                    <tr>
                        <td style="width: 15%;" class="font-bold">Menimbang</td>
                        <td style="width: 2%;">:</td>
                        <td style="width: 83%; text-align: justify;">
                            a. Bahwa dalam rangka menjamin kelancaran, ketertiban, dan mutu penyelenggaraan {{ $activePeriode->nama_periode ?? 'Ujian Madrasah' }}, dipandang perlu membentuk Panitia Pelaksana;<br>
                            b. Bahwa guru/pegawai yang namanya tercantum dalam lampiran keputusan ini dipandang cakap dan memenuhi syarat untuk ditugaskan sebagai panitia pelaksana.
                        </td>
                    </tr>
                    <tr>
                        <td class="font-bold">Mengingat</td>
                        <td>:</td>
                        <td style="text-align: justify;">
                            1. Undang-Undang Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional;<br>
                            2. Peraturan Menteri Agama RI Nomor 90 Tahun 2013 tentang Penyelenggaraan Pendidikan Madrasah;<br>
                            3. Keputusan Direktur Jenderal Pendidikan Islam Kementerian Agama RI tentang Petunjuk Teknis Penyelenggaraan Asesmen Madrasah.
                        </td>
                    </tr>
                </table>

                <div style="text-align: center; font-weight: bold; margin: 8px 0;">MEMUTUSKAN:</div>

                <table class="diktum-table">
                    <tr>
                        <td style="width: 15%;" class="font-bold">Menetapkan</td>
                        <td style="width: 2%;">:</td>
                        <td style="width: 83%;" class="font-bold">
                            KEPUTUSAN KEPALA MADRASAH TENTANG PENETAPAN PANITIA PELAKSANA {{ strtoupper($activePeriode->nama_periode ?? 'UJIAN MADRASAH') }} TAHUN PELAJARAN {{ $activePeriode->tahun_pelajaran ?? '-' }}.
                        </td>
                    </tr>
                    <tr>
                        <td class="font-bold">KESATU</td>
                        <td>:</td>
                        <td style="text-align: justify;">
                            Menetapkan susunan Panitia Pelaksana Ujian sebagaimana tercantum dalam lampiran yang merupakan bagian tidak terpisahkan dari keputusan ini.
                        </td>
                    </tr>
                    <tr>
                        <td class="font-bold">KEDUA</td>
                        <td>:</td>
                        <td style="text-align: justify;">
                            Panitia Pelaksana bertugas merencanakan, mengorganisasikan, mengamankan naskah, mengawasi pelaksanaan, serta melaporkan hasil evaluasi pelaksanaan ujian kepada Kepala Madrasah.
                        </td>
                    </tr>
                    <tr>
                        <td class="font-bold">KETIGA</td>
                        <td>:</td>
                        <td style="text-align: justify;">
                            Segala biaya yang timbul akibat diterbitkannya keputusan ini dibebankan pada anggaran Bantuan Operasional Sekolah (BOS) yang sah.
                        </td>
                    </tr>
                    <tr>
                        <td class="font-bold">KEEMPAT</td>
                        <td>:</td>
                        <td style="text-align: justify;">
                            Keputusan ini berlaku sejak tanggal ditetapkan, dan apabila terdapat kekeliruan akan diperbaiki sebagaimana mestinya.
                        </td>
                    </tr>
                </table>
            @endif
        </div>

        <div class="ttd-block">
            <div class="ttd-box">
                <div>Ditetapkan di: {{ $activePeriode->tempat_cetak ?? 'Tanggamus' }}</div>
                <div>Pada tanggal: {{ date('d F Y') }}</div>
                <div style="font-weight: bold; margin-top: 3px;">Kepala Madrasah,</div>
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

    <!-- Halaman 2: Lampiran Struktur Kepanitiaan -->
    <div>
        <!-- Kop Surat Resmi -->
        <x-kop-surat :useBase64="true" />

        <div style="margin-top: 10px; margin-bottom: 12px; font-size: 10pt;">
            <div class="font-bold">LAMPIRAN KEPUTUSAN KEPALA MIN 2 TANGGAMUS</div>
            <div>NOMOR : B-&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/Mi.08.04/PP.01.1/{{ date('m/Y') }}</div>
            <div class="font-bold">TENTANG : SUSUNAN PANITIA PELAKSANA {{ strtoupper($activePeriode->nama_periode ?? 'UJIAN MADRASAH') }}</div>
        </div>

        <table class="tabel-panitia">
            <thead>
                <tr>
                    <th style="width: 35px;">No</th>
                    <th>Nama Lengkap Guru / Pegawai</th>
                    <th style="width: 140px;">NIP</th>
                    <th style="width: 160px;">Jabatan Kepanitiaan</th>
                </tr>
            </thead>
            <tbody>
                <!-- Penanggung Jawab Default: Kepala Madrasah -->
                <tr>
                    <td class="text-center">1</td>
                    <td class="font-bold">{{ $ttdSetting->nama_kepala ?? ($setting->card_principal_name ?? 'Sipulloh, M.Pd') }}</td>
                    <td class="text-center">{{ $ttdSetting->nip_kepala ?? ($setting->card_principal_nip ?? '197005272007011022') }}</td>
                    <td class="text-center font-bold">Penanggung Jawab</td>
                </tr>
                @forelse($panitias as $idx => $p)
                    <tr>
                        <td class="text-center">{{ $idx + 2 }}</td>
                        <td class="font-bold">{{ $p->teacher?->user?->name ?? '-' }}</td>
                        <td class="text-center">{{ $p->teacher?->nip ?: '-' }}</td>
                        <td class="text-center font-bold">{{ ucwords(str_replace('_', ' ', $p->jabatan)) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center" style="padding: 15px; font-style: italic;">
                            Belum ada panitia tambahan yang ditunjuk pada periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="ttd-block">
            <div class="ttd-box">
                <div>Ditetapkan di: {{ $activePeriode->tempat_cetak ?? 'Tanggamus' }}</div>
                <div>Pada tanggal: {{ date('d F Y') }}</div>
                <div style="font-weight: bold; margin-top: 3px;">Kepala Madrasah,</div>
                <div style="height: 55px;"></div>
                <div class="font-bold" style="text-decoration: underline;">
                    {{ $ttdSetting->nama_kepala ?? ($setting->card_principal_name ?? 'Sipulloh, M.Pd') }}
                </div>
                <div style="font-size: 9pt;">
                    NIP. {{ $ttdSetting->nip_kepala ?? ($setting->card_principal_nip ?? '197005272007011022') }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
