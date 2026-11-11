<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label Amplop Soal & LJK - {{ $activePeriode->nama_periode ?? 'Madrasah' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 10px;
        }
        .page-break {
            page-break-after: always;
        }
        .amplop-container {
            border: 3px solid #000;
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 25px;
            background-color: #fff;
            box-shadow: 3px 3px 0 #000;
        }
        .judul-amplop {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .judul-amplop h2 {
            margin: 0;
            font-size: 13pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .tabel-info {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5pt;
            margin-bottom: 12px;
        }
        .tabel-info td {
            padding: 3px 4px;
            vertical-align: top;
        }
        .badge-ruang {
            background-color: #FFD43B;
            border: 1.5px solid #000;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 900;
            font-size: 11pt;
            display: inline-block;
        }
        .tabel-isi {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 9.5pt;
        }
        .tabel-isi th, .tabel-isi td {
            border: 1px solid #000;
            padding: 5px 8px;
        }
        .tabel-isi th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .tabel-serah-terima {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 9.5pt;
        }
        .tabel-serah-terima th, .tabel-serah-terima td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }
        .tabel-serah-terima th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
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
            $pesertaCount = $sheet['peserta_count'];
            $pengawas = $sheet['pengawas'];
        @endphp

        <div class="amplop-container {{ !$loop->last ? 'page-break' : '' }}">
            <!-- Kop Surat Resmi -->
            <x-kop-surat :useBase64="true" />

            <div class="judul-amplop">
                <h2>LABEL AMPLOP NASKAH SOAL & LEMBAR JAWABAN</h2>
                <div style="font-size: 10pt; font-weight: bold; margin-top: 2px;">
                    {{ strtoupper($activePeriode->nama_periode ?? 'UJIAN MADRASAH') }} 
                    @if($activePeriode && $activePeriode->tahun_pelajaran)
                        - TP {{ $activePeriode->tahun_pelajaran }}
                    @endif
                </div>
            </div>

            <!-- Detail Naskah & Ruang -->
            <table class="tabel-info">
                <tr>
                    <td style="width: 25%;" class="font-bold">Mata Pelajaran</td>
                    <td style="width: 2%;">:</td>
                    <td style="width: 43%;" class="font-bold">{{ $j->nama_mapel ?? 'Semua Mapel / Cadangan' }}</td>
                    <td style="width: 30%; text-align: right;" rowspan="2">
                        <div class="badge-ruang">{{ $r->nama_ruang }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="font-bold">Tingkat / Rombel</td>
                    <td>:</td>
                    <td>{{ $j->target_kelas ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="font-bold">Hari, Tanggal</td>
                    <td>:</td>
                    <td>{{ $j?->tanggal_ujian ? $j->tanggal_ujian->locale('id')->translatedFormat('l, d F Y') : '-' }}</td>
                    <td class="font-bold text-right">
                        Sesi: {{ $j->sesi_ke ?? '1' }} ({{ substr($j?->jam_mulai ?? '07:30', 0, 5) }} - {{ substr($j?->jam_selesai ?? '09:30', 0, 5) }} WIB)
                    </td>
                </tr>
            </table>

            <!-- Rincian Dokumen dalam Amplop -->
            <div class="font-bold" style="margin-bottom: 4px; font-size: 10pt;">A. ISI BERKAS DALAM AMPLOP:</div>
            <table class="tabel-isi">
                <thead>
                    <tr>
                        <th style="width: 35px;">No</th>
                        <th>Jenis Dokumen / Berkas</th>
                        <th style="width: 140px;">Jumlah Dititipkan</th>
                        <th style="width: 140px;">Jumlah Dikembalikan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">1</td>
                        <td class="font-bold">Naskah Soal Ujian</td>
                        <td class="text-center">{{ $pesertaCount ?: '.....' }} Eksemplar</td>
                        <td class="text-center">..... Eksemplar</td>
                    </tr>
                    <tr>
                        <td class="text-center">2</td>
                        <td class="font-bold">Lembar Jawaban Siswa (LJK)</td>
                        <td class="text-center">{{ $pesertaCount ?: '.....' }} Lembar</td>
                        <td class="text-center">..... Terpakai / ..... Sisa</td>
                    </tr>
                    <tr>
                        <td class="text-center">3</td>
                        <td>Daftar Hadir Peserta Ujian</td>
                        <td class="text-center">1 Set (Asli)</td>
                        <td class="text-center">..... Lembar Terisi</td>
                    </tr>
                    <tr>
                        <td class="text-center">4</td>
                        <td>Berita Acara Pelaksanaan (BAP)</td>
                        <td class="text-center">1 Set</td>
                        <td class="text-center">1 Set Ditandatangani</td>
                    </tr>
                </tbody>
            </table>

            <!-- Checklist Serah Terima Naskah -->
            <div class="font-bold" style="margin-top: 8px; margin-bottom: 4px; font-size: 10pt;">B. BUKTI SERAH TERIMA BERKAS:</div>
            <table class="tabel-serah-terima">
                <thead>
                    <tr>
                        <th style="width: 50%;">1. Penyerahan Naskah (Sebelum Ujian)</th>
                        <th style="width: 50%;">2. Pengembalian Berkas (Setelah Ujian)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 10px; vertical-align: top;">
                            <div style="display: flex; justify-content: space-between; font-size: 8.5pt;">
                                <div>
                                    <div class="font-bold">Yang Menyerahkan (Panitia):</div>
                                    <div style="height: 40px;"></div>
                                    <div>(...................................)</div>
                                </div>
                                <div>
                                    <div class="font-bold">Penerima (Pengawas Ruang):</div>
                                    <div style="height: 40px;"></div>
                                    <div>( {{ $pengawas?->pengawas1?->user?->name ?? '...................................' }} )</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 10px; vertical-align: top;">
                            <div style="display: flex; justify-content: space-between; font-size: 8.5pt;">
                                <div>
                                    <div class="font-bold">Pengawas Ruang:</div>
                                    <div style="height: 40px;"></div>
                                    <div>( {{ $pengawas?->pengawas1?->user?->name ?? '...................................' }} )</div>
                                </div>
                                <div>
                                    <div class="font-bold">Penerima (Panitia):</div>
                                    <div style="height: 40px;"></div>
                                    <div>(...................................)</div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div style="font-size: 8pt; color: #555; margin-top: 8px; text-align: center; font-style: italic;">
                Catatan: Lembar ini ditempel pada bagian depan amplop coklat naskah soal dan berkas hasil ujian.
            </div>
        </div>
    @empty
        <div style="text-align: center; padding: 50px; font-family: sans-serif;">
            <h3>Tidak ada data amplop yang dapat dicetak.</h3>
        </div>
    @endforelse
</body>
</html>
