<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Hadir Peserta Ujian - {{ $activePeriode->nama_periode ?? 'Madrasah' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10.5pt;
            line-height: 1.25;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 15px;
        }
        .page-break {
            page-break-after: always;
        }
        .judul-dokumen {
            text-align: center;
            margin: 10px 0 12px 0;
        }
        .judul-dokumen h3 {
            margin: 0;
            font-size: 13pt;
            text-transform: uppercase;
            text-decoration: underline;
            font-weight: bold;
        }
        .judul-dokumen .sub-judul {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 3px;
            text-transform: uppercase;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 10px;
            font-size: 10pt;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 2px 4px;
            vertical-align: top;
        }
        .tabel-peserta {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        .tabel-peserta th, .tabel-peserta td {
            border: 1px solid #000;
            padding: 4.5px 6px;
            font-size: 9.5pt;
        }
        .tabel-peserta th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .ttd-box-cell {
            height: 24px;
            position: relative;
        }
        .ttd-nomor {
            font-size: 8.5pt;
            font-weight: normal;
        }
        .rekap-ttd-block {
            width: 100%;
            margin-top: 15px;
            font-size: 10pt;
            border-collapse: collapse;
        }
        .rekap-box {
            border: 1px solid #000;
            padding: 6px 10px;
            width: 45%;
            font-size: 9.5pt;
        }
        .ttd-pengawas-table {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
            font-size: 10pt;
        }
        .ttd-pengawas-table td {
            text-align: center;
            vertical-align: top;
            width: 50%;
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

    @forelse($sheets as $sheetIndex => $sheet)
        @php
            $r = $sheet['ruang'];
            $j = $sheet['jadwal'];
            $pesertas = $sheet['pesertas'];
            $pengawas = $sheet['pengawas'];
        @endphp

        <div class="{{ !$loop->last ? 'page-break' : '' }}">
            <!-- Kop Surat Resmi -->
            <x-kop-surat :useBase64="true" />

            <div class="judul-dokumen">
                <h3>DAFTAR HADIR PESERTA UJIAN</h3>
                <div class="sub-judul">
                    {{ $activePeriode->nama_periode ?? 'UJIAN MADRASAH' }}
                    @if($activePeriode && $activePeriode->tahun_pelajaran)
                        - TP {{ $activePeriode->tahun_pelajaran }}
                    @endif
                </div>
            </div>

            <!-- Meta Data Jadwal & Ruang -->
            <table class="meta-table">
                <tr>
                    <td style="width: 16%;">Mata Pelajaran</td>
                    <td style="width: 2%;">:</td>
                    <td style="width: 38%;" class="font-bold">
                        @if($isBlangko)
                            ................................................
                        @else
                            {{ $j->nama_mapel ?? 'Semua Mapel' }}
                        @endif
                    </td>
                    <td style="width: 16%;">Ruang Ujian</td>
                    <td style="width: 2%;">:</td>
                    <td style="width: 26%;" class="font-bold">{{ $r->nama_ruang }}</td>
                </tr>
                <tr>
                    <td>Hari, Tanggal</td>
                    <td>:</td>
                    <td>
                        @if($isBlangko || !$j)
                            ................................................
                        @else
                            {{ $j->tanggal_ujian ? $j->tanggal_ujian->locale('id')->translatedFormat('l, d F Y') : '-' }}
                        @endif
                    </td>
                    <td>Sesi / Waktu</td>
                    <td>:</td>
                    <td>
                        @if($isBlangko || !$j)
                            Sesi ..... / ...... s.d ...... WIB
                        @else
                            Sesi {{ $j->sesi_ke }} ({{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }} WIB)
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Tingkat / Kelas</td>
                    <td>:</td>
                    <td>
                        @if($isBlangko)
                            ................................................
                        @else
                            {{ $j->target_kelas ?? 'Semua Tingkat' }}
                        @endif
                    </td>
                    <td>Bentuk Ujian</td>
                    <td>:</td>
                    <td>{{ $j->jenis_soal ?? 'Tertulis / CBT' }}</td>
                </tr>
            </table>

            <!-- Tabel Daftar Peserta -->
            <table class="tabel-peserta">
                <thead>
                    <tr>
                        <th style="width: 30px;">No</th>
                        <th style="width: 95px;">No. Peserta</th>
                        <th style="width: 85px;">NISN</th>
                        <th>Nama Peserta</th>
                        <th style="width: 70px;">Kelas</th>
                        <th style="width: 130px;">Tanda Tangan</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalPeserta = $isBlangko ? max(20, $r->kapasitas_maksimal) : $pesertas->count();
                    @endphp

                    @for($i = 0; $i < $totalPeserta; $i++)
                        @php
                            $p = $isBlangko ? null : $pesertas->get($i);
                            $nomorUrut = $i + 1;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $nomorUrut }}</td>
                            <td class="text-center font-bold font-mono">{{ $p?->nomor_peserta ?? '..............' }}</td>
                            <td class="text-center">{{ $p?->nisn ?? ($p?->nis_lokal ?? '..........') }}</td>
                            <td class="font-bold">{{ $p?->nama_peserta ?? '' }}</td>
                            <td class="text-center">{{ $p?->nama_kelas ?? ($p?->schoolClass?->name ?? '') }}</td>
                            <td class="ttd-box-cell">
                                @if($nomorUrut % 2 !== 0)
                                    <span class="ttd-nomor">{{ $nomorUrut }}. ....................</span>
                                @else
                                    <div style="text-align: right; padding-right: 5px;">
                                        <span class="ttd-nomor">{{ $nomorUrut }}. ....................</span>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>

            <!-- Rekapitulasi & Tanda Tangan Pengawas -->
            <table style="width: 100%; margin-top: 12px; border-collapse: collapse;">
                <tr>
                    <td style="width: 48%; vertical-align: top;">
                        <div class="rekap-box">
                            <div class="font-bold" style="text-decoration: underline; margin-bottom: 4px;">REKAPITULASI RUANG:</div>
                            <div>• Jumlah Peserta Seharusnya : {{ $pesertas->count() ?: '.....' }} Orang</div>
                            <div>• Jumlah Peserta Hadir : ..... Orang</div>
                            <div>• Jumlah Peserta Tidak Hadir : ..... Orang</div>
                        </div>
                    </td>
                    <td style="width: 4%;"></td>
                    <td style="width: 48%; vertical-align: top;">
                        <div style="text-align: center; font-size: 10pt;">
                            <div>{{ $activePeriode->tempat_cetak ?? 'Tanggamus' }}, {{ $j?->tanggal_ujian ? $j->tanggal_ujian->locale('id')->translatedFormat('d F Y') : date('d F Y') }}</div>
                            <div class="font-bold" style="margin-top: 2px;">Pengawas Ruang Ujian,</div>
                        </div>
                    </td>
                </tr>
            </table>

            <table class="ttd-pengawas-table">
                <tr>
                    <td>
                        <div class="font-bold">Pengawas 1</div>
                        <div style="height: 50px;"></div>
                        <div class="font-bold" style="text-decoration: underline;">
                            {{ $pengawas?->pengawas1?->user?->name ?? '............................................' }}
                        </div>
                        <div style="font-size: 9pt;">
                            NIP. {{ $pengawas?->pengawas1?->nip ?? '....................................' }}
                        </div>
                    </td>
                    <td>
                        <div class="font-bold">Pengawas 2</div>
                        <div style="height: 50px;"></div>
                        <div class="font-bold" style="text-decoration: underline;">
                            {{ $pengawas?->pengawas2?->user?->name ?? '............................................' }}
                        </div>
                        <div style="font-size: 9pt;">
                            NIP. {{ $pengawas?->pengawas2?->nip ?? '....................................' }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    @empty
        <div style="text-align: center; padding: 50px; font-family: sans-serif;">
            <h3>Tidak ada data peserta atau ruang yang dapat dicetak.</h3>
            <p>Silakan pilih periode, ruang, atau jadwal ujian yang valid.</p>
        </div>
    @endforelse
</body>
</html>
