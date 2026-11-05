<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Hadir Pengawas Ruang Ujian</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.5cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.3;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 20px;
        }
        .judul-dokumen {
            text-align: center;
            margin: 15px 0;
        }
        .judul-dokumen h4 {
            margin: 0;
            font-size: 12pt;
            text-transform: uppercase;
            text-decoration: underline;
        }
        .tabel-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .tabel-data th, .tabel-data td {
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 10pt;
        }
        .tabel-data th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .ttd-block {
            margin-top: 30px;
            width: 100%;
            display: flex;
            justify-content: flex-end;
        }
        .ttd-box {
            width: 250px;
            text-align: center;
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

    {{-- Kop Surat Resmi Lembaga --}}
    <x-kop-surat :useBase64="true" />

    <div class="judul-dokumen">
        <h4>DAFTAR HADIR PENGAWAS RUANG UJIAN</h4>
    </div>

    <table class="tabel-data">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 90px;">Hari / Tanggal</th>
                <th style="width: 65px;">Ruang</th>
                <th>Mata Pelajaran</th>
                <th>Nama Pengawas Ruang</th>
                <th style="width: 120px;">Tanda Tangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pengawas as $idx => $pw)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>
                        {{ $pw->jadwal?->tanggal_ujian?->locale('id')->translatedFormat('l') }}<br>
                        <span style="font-size: 9pt;">{{ $pw->jadwal?->tanggal_ujian?->format('d/m/Y') }}</span>
                    </td>
                    <td class="text-center" style="font-weight: bold;">
                        {{ $pw->ruang?->nama_ruang }}
                    </td>
                    <td>
                        <strong>{{ $pw->jadwal?->nama_mapel }}</strong><br>
                        <span style="font-size: 9pt;">Kelas: {{ $pw->jadwal?->target_kelas }}</span>
                    </td>
                    <td>
                        <div>1. {{ $pw->pengawas1?->user?->name ?: '..........................................' }}</div>
                        @if($pw->pengawas2)
                            <div style="margin-top: 4px;">2. {{ $pw->pengawas2?->user?->name }}</div>
                        @endif
                    </td>
                    <td style="vertical-align: middle;">
                        <div style="font-size: 9pt; padding: 4px 0;">1. ....................</div>
                        @if($pw->pengawas2)
                            <div style="font-size: 9pt; padding: 4px 0; text-align: right;">2. ....................</div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px;">Belum ada jadwal penugasan pengawas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="ttd-block">
        <div class="ttd-box">
            <div>{{ $setting->school_city ?? 'Tanggamus' }}, {{ date('d F Y') }}</div>
            <div style="font-weight: bold; margin-top: 3px;">Ketua Panitia Ujian,</div>
            <div style="height: 65px;"></div>
            <div style="font-weight: bold; text-decoration: underline;">
                ( .................................................... )
            </div>
            <div style="font-size: 9.5pt;">NIP. -</div>
        </div>
    </div>
</body>
</html>
