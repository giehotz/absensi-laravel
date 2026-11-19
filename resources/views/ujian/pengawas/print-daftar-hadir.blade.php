<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Hadir Pengawas Ruang Ujian - {{ $activePeriode->nama_periode ?? 'Ujian' }}</title>
    <style>
        * {
            box-sizing: border-box;
        }
        @page {
            size: A4 landscape;
            margin: 0.8cm 1.2cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.25;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        .page-sheet {
            width: 100%;
        }
        .page-break {
            page-break-after: always;
            break-after: page;
        }
        .judul-dokumen {
            text-align: center;
            margin: 6px 0 10px 0;
        }
        .judul-dokumen h3 {
            margin: 0;
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .judul-dokumen .periode-info {
            margin-top: 2px;
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .info-hari-box {
            margin-bottom: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11pt;
        }
        .tabel-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            page-break-inside: auto;
        }
        .tabel-data th, .tabel-data td {
            border: 1px solid #000;
            padding: 5px 6px;
            font-size: 11pt; /* Ukuran teks dalam tabel 11pt sesuai instruksi */
            vertical-align: middle;
        }
        .tabel-data tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        .tabel-data th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .ttd-table {
            width: 100%;
            margin-top: 16px;
            border-collapse: collapse;
            font-size: 11pt;
            page-break-inside: avoid;
        }
        .ttd-table td {
            border: none;
            padding: 0;
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
        @media screen {
            body {
                background-color: #e2e8f0;
                padding: 20px 10px;
            }
            .page-sheet {
                background: #fff;
                padding: 20px 30px;
                margin: 0 auto 25px auto;
                max-width: 297mm;
                min-height: 210mm;
                box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
            }
        }
        @media print {
            .print-toolbar { display: none !important; }
            body {
                background: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .page-sheet {
                background: transparent !important;
                padding: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
                max-width: none !important;
                min-height: auto !important;
            }
        }
    </style>
</head>
<body>
    <button type="button" onclick="window.print()" class="print-toolbar">
        🖨️ Cetak / Simpan PDF
    </button>

    @forelse($groupedPengawas as $dateKey => $items)
        @php
            $firstItem = $items->first();
            $jadwalDate = $firstItem?->jadwal?->tanggal_ujian;
            $namaHari = $jadwalDate ? $jadwalDate->locale('id')->translatedFormat('l, d F Y') : ($dateKey !== 'unknown' ? $dateKey : '-');
        @endphp

        <div class="page-sheet {{ !$loop->last ? 'page-break' : '' }}">
            {{-- Kop Surat Resmi Lembaga --}}
            <x-kop-surat :useBase64="true" />

            <div class="judul-dokumen">
                <h3>DAFTAR HADIR PENGAWAS RUANG UJIAN</h3>
                <div class="periode-info">
                    {{ strtoupper($activePeriode->nama_periode ?? 'UJIAN MADRASAH') }}
                </div>
                <div style="font-size: 10pt; font-weight: normal; margin-top: 1px;">
                    Tahun Pelajaran {{ $activePeriode->tahun_pelajaran ?? '-' }} (Semester {{ ucfirst($activePeriode->semester ?? '-') }})
                </div>
            </div>

            <div class="info-hari-box">
                <div>
                    <strong>Hari / Tanggal :</strong> {{ $namaHari }}
                </div>
                <div>
                    <strong>Jumlah Pengawas :</strong> {{ $items->count() * 2 }} Orang ({{ $items->count() }} Ruang/Sesi)
                </div>
            </div>

            <table class="tabel-data">
                <thead>
                    <tr>
                        <th style="width: 35px;">No</th>
                        <th style="width: 260px;">Nama Pengawas Ruang</th>
                        <th style="width: 140px;">Sesi / Waktu</th>
                        <th style="width: 90px;">Ruang</th>
                        <th>Mata Pelajaran</th>
                        <th style="width: 210px;">Tanda Tangan Pengawas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $idx => $pw)
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td>
                                <div>{{ $pw->pengawas1?->user?->name ?: '..................................................' }}</div>
                                @if($pw->pengawas2)
                                    <div style="margin-top: 4px;">{{ $pw->pengawas2?->user?->name }}</div>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="font-bold">Sesi {{ $pw->jadwal?->sesi_ke ?? '1' }}</span><br>
                                <span style="font-size: 10pt;">
                                    {{ substr($pw->jadwal?->jam_mulai ?? '07:30', 0, 5) }} - {{ substr($pw->jadwal?->jam_selesai ?? '09:30', 0, 5) }}
                                </span>
                            </td>
                            <td class="text-center font-bold">
                                {{ $pw->ruang?->nama_ruang ?? '-' }}
                            </td>
                            <td>
                                <strong>{{ $pw->jadwal?->nama_mapel ?? '-' }}</strong>
                            </td>
                            <td style="vertical-align: middle;">
                                <div style="font-size: 9.5pt; padding: 2px 0; {{ ($idx + 1) % 2 === 1 ? 'text-align: left;' : 'text-align: right; padding-right: 15px;' }}">
                                    {{ $idx + 1 }}. ................................
                                </div>
                                @if($pw->pengawas2)
                                    <div style="font-size: 9.5pt; padding: 2px 0; {{ ($idx + 1) % 2 === 1 ? 'text-align: right; padding-right: 15px;' : 'text-align: left;' }}">
                                        {{ $idx + 1 }}. ................................
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center" style="padding: 20px;">
                                Belum ada jadwal penugasan pengawas untuk tanggal ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Bagian Tanda Tangan 2 Kolom Sejajar: Kiri Kepala Madrasah, Kanan Ketua Panitia --}}
            <table class="ttd-table">
                <tr>
                    <td style="width: 50%; text-align: center; vertical-align: top;">
                        <div>Mengetahui,</div>
                        <div style="font-weight: bold; margin-top: 2px;">Kepala Madrasah,</div>
                        <div style="height: 48px;"></div>
                        <div style="font-weight: bold; text-decoration: underline;">
                            {{ $namaKepala }}
                        </div>
                        <div style="font-size: 10pt; margin-top: 2px;">
                            NIP. {{ $nipKepala }}
                        </div>
                    </td>
                    <td style="width: 50%; text-align: center; vertical-align: top;">
                        <div>{{ $tempatCetak }}, {{ $tanggalCetak->locale('id')->translatedFormat('d F Y') }}</div>
                        <div style="font-weight: bold; margin-top: 2px;">Ketua Panitia Ujian,</div>
                        <div style="height: 48px;"></div>
                        <div style="font-weight: bold; text-decoration: underline;">
                            {{ $namaKetua }}
                        </div>
                        <div style="font-size: 10pt; margin-top: 2px;">
                            NIP. {{ $nipKetua }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    @empty
        <div class="page-sheet">
            <x-kop-surat :useBase64="true" />
            <div class="judul-dokumen">
                <h3>DAFTAR HADIR PENGAWAS RUANG UJIAN</h3>
            </div>
            <p class="text-center" style="padding: 40px; font-style: italic;">
                Tidak ada data jadwal pengawas ruang ujian untuk kriteria yang dipilih.
            </p>
        </div>
    @endforelse
</body>
</html>
