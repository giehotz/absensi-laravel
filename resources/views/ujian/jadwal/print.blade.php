<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Pelaksanaan Ujian - {{ $activePeriode->nama_periode ?? 'Madrasah' }}</title>
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
        .tabel-jadwal {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .tabel-jadwal th, .tabel-jadwal td {
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 10pt;
        }
        .tabel-jadwal th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
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
        <h4>JADWAL PELAKSANAAN UJIAN</h4>
        <div style="font-size: 10.5pt; font-weight: bold; margin-top: 3px;">
            {{ strtoupper($activePeriode->nama_periode ?? 'UJIAN MADRASAH') }}
        </div>
        <div style="font-size: 9.5pt;">
            Tahun Pelajaran {{ $activePeriode->tahun_pelajaran ?? '-' }} (Semester {{ $activePeriode->semester ?? '-' }})
        </div>
    </div>

    <table class="tabel-jadwal">
        <thead>
            <tr>
                <th style="width: 35px;">No</th>
                <th style="width: 110px;">Hari, Tanggal</th>
                <th style="width: 90px;">Waktu</th>
                <th style="width: 50px;">Sesi</th>
                <th>Mata Pelajaran</th>
                <th style="width: 110px;">Rombel / Kelas</th>
                <th style="width: 80px;">Bentuk</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jadwals as $idx => $j)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>
                        {{ $j->tanggal_ujian->locale('id')->translatedFormat('l') }},<br>
                        {{ $j->tanggal_ujian->format('d/m/Y') }}
                    </td>
                    <td class="text-center">
                        {{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }}
                    </td>
                    <td class="text-center">{{ $j->sesi_ke }}</td>
                    <td style="font-weight: bold;">{{ $j->nama_mapel }}</td>
                    <td class="text-center">{{ $j->target_kelas }}</td>
                    <td class="text-center">{{ $j->jenis_soal }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px;">Belum ada jadwal yang ditetapkan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="ttd-block">
        <div class="ttd-box">
            <div>{{ $activePeriode->tempat_cetak ?? 'Tanggamus' }}, {{ $activePeriode->tanggal_cetak_kartu ? $activePeriode->tanggal_cetak_kartu->locale('id')->translatedFormat('d F Y') : date('d F Y') }}</div>
            <div style="font-weight: bold; margin-top: 3px;">Kepala Madrasah,</div>
            <div style="height: 65px;"></div>
            <div style="font-weight: bold; text-decoration: underline;">
                {{ $setting->card_principal_name ?? ($setting->headmasterTeacher?->user?->name ?? 'Sipulloh, M.Pd') }}
            </div>
            <div style="font-size: 9.5pt;">
                NIP. {{ $setting->card_principal_nip ?? ($setting->headmasterTeacher?->nip ?? '197005272007011022') }}
            </div>
        </div>
    </div>
</body>
</html>
