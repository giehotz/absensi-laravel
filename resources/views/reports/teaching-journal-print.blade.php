<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Jurnal Kegiatan Harian Guru Mengajar</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1.2cm 1.5cm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            line-height: 1.35;
            font-size: 10pt;
            background: #fff;
            margin: 0;
            padding: 0;
        }

        .no-print-toolbar {
            background-color: #f8fafc;
            border-bottom: 2px solid #000;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            border: 2px solid #000;
            box-shadow: 2px 2px 0px 0px #000;
            transition: all 0.15s;
        }
        .btn:hover {
            transform: translate(-1px, -1px);
            box-shadow: 3px 3px 0px 0px #000;
        }
        .btn-print {
            background-color: #FFD43B;
            color: #000;
        }
        .btn-back {
            background-color: #fff;
            color: #000;
        }

        @media print {
            .no-print-toolbar {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }

        .document-title {
            text-align: center;
            margin: 10px 0 14px 0;
        }

        .document-title h2 {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            margin: 0;
            padding: 0;
        }

        .document-title p {
            font-size: 10pt;
            margin: 3px 0 0 0;
        }

        .meta-info-table {
            width: 100%;
            margin-bottom: 10px;
            border-collapse: collapse;
            font-size: 9.5pt;
        }

        .meta-info-table td {
            padding: 2px 4px;
            vertical-align: top;
        }

        /* 9 Columns Exact Table */
        table.report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 9pt;
        }

        table.report-table th,
        table.report-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: top;
        }

        table.report-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            vertical-align: middle;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .signature-section {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
        }

        .signature-table {
            width: 100%;
            border: none;
            border-collapse: collapse;
            font-size: 10pt;
        }

        .signature-table td {
            border: none;
            padding: 0;
            vertical-align: top;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Toolbar khusus tampilan layar (disembunyikan saat dicetak) -->
    <div class="no-print-toolbar">
        <div>
            <span style="font-weight: bold; font-size: 14px;">Pratinjau Jurnal Guru Mengajar</span>
            <span style="font-size: 11px; color: #64748b; margin-left: 8px;">(Format resmi A4 Landscape)</span>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="javascript:history.back()" class="btn btn-back">← Kembali</a>
            <button onclick="window.print()" class="btn btn-print">🖨️ Cetak Dokumen / Simpan PDF</button>
        </div>
    </div>

    <!-- 1. KOP SURAT RESMI -->
    <x-kop-surat :useBase64="true" />

    <!-- 2. JUDUL DOKUMEN -->
    <div class="document-title">
        <h2>JURNAL KEGIATAN HARIAN GURU MENGAJAR</h2>
        @if(!empty($startDate) || !empty($endDate))
            <p>Periode: {{ $startDate ? \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') : 'Awal' }} s.d. {{ $endDate ? \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') : 'Sekarang' }}</p>
        @endif
    </div>

    <!-- METADATA GURU & KELAS -->
    <table class="meta-info-table">
        <tr>
            <td style="width: 15%; font-weight: bold;">Nama Guru</td>
            <td style="width: 35%;">: {{ $teacher?->user?->name ?? 'Semua Guru / Terlampir' }}</td>
            <td style="width: 15%; font-weight: bold;">Mata Pelajaran</td>
            <td style="width: 35%;">: {{ $selectedSubject?->name ?? 'Semua Mata Pelajaran' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">NIP</td>
            <td style="font-family: monospace;">: {{ $teacher?->nip ?? '-' }}</td>
            <td style="font-weight: bold;">Kelas</td>
            <td>: {{ $selectedClass?->name ?? 'Semua Kelas' }}</td>
        </tr>
    </table>

    <!-- 3. TABEL 9 KOLOM PERSIS SESUAI GAMBAR REFERENSI -->
    <table class="report-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 4%;">No.</th>
                <th rowspan="2" style="width: 11%;">Hari / Tanggal</th>
                <th rowspan="2" style="width: 7%;">Kelas</th>
                <th rowspan="2" style="width: 6%;">Pertemuan Ke-</th>
                <th rowspan="2" style="width: 18%;">Tujuan Pembelajaran</th>
                <th rowspan="2" style="width: 22%;">Kegiatan Belajar Mengajar</th>
                <th colspan="3" style="width: 9%;">Absensi Siswa</th>
                <th rowspan="2" style="width: 7%;">Prosentase Kehadiran Siswa</th>
                <th rowspan="2" style="width: 16%;">Permasalahan Dalam Proses KBM</th>
            </tr>
            <tr>
                <th style="width: 3%;">S</th>
                <th style="width: 3%;">I</th>
                <th style="width: 3%;">A</th>
            </tr>
        </thead>
        <tbody>
            @forelse($journals as $index => $journal)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <div style="font-weight: bold;">{{ $journal->day_name }}</div>
                    <div style="font-size: 8.5pt;">{{ $journal->formatted_date }}</div>
                </td>
                <td class="text-center" style="font-weight: bold;">{{ $journal->schoolClass?->name ?? '-' }}</td>
                <td class="text-center" style="font-weight: bold;">{{ $journal->meeting_number }}</td>
                <td>{{ $journal->learning_objective }}</td>
                <td>{{ $journal->teaching_activity }}</td>
                <td class="text-center" style="font-weight: {{ $journal->count_sakit > 0 ? 'bold' : 'normal' }};">
                    {{ $journal->count_sakit }}
                </td>
                <td class="text-center" style="font-weight: {{ $journal->count_izin > 0 ? 'bold' : 'normal' }};">
                    {{ $journal->count_izin }}
                </td>
                <td class="text-center" style="font-weight: {{ $journal->count_alpa > 0 ? 'bold; color: #b91c1c;' : 'normal' }};">
                    {{ $journal->count_alpa }}
                </td>
                <td class="text-center" style="font-weight: bold;">
                    {{ number_format($journal->attendance_percentage, 1) }}%
                </td>
                <td>
                    {{ $journal->teaching_problem ?: '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="11" class="text-center" style="padding: 16px; font-style: italic;">
                    Belum ada data catatan jurnal kegiatan mengajar pada kriteria ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 4. BLOK TANDA TANGAN RESMI -->
    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td style="width: 50%;">
                    <p style="margin: 0;">&nbsp;</p>
                    <p style="margin: 3px 0 0 0; font-weight: bold;">Guru Mata Pelajaran,</p>
                    <div style="height: 60px;"></div>
                    <p style="margin: 0; font-weight: bold; text-decoration: underline;">
                        {{ $teacher?->user?->name ?? '( ............................................... )' }}
                    </p>
                    <p style="margin: 2px 0 0 0;">NIP. {{ $teacher?->nip ?? '............................................' }}</p>
                </td>
                <td style="width: 50%;">
                    <p style="margin: 0;">
                        {{ $setting?->school_address ? explode(',', $setting->school_address)[0] : 'Tanggamus' }}, 
                        {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                    </p>
                    <p style="margin: 3px 0 0 0; font-weight: bold;">Kepala Madrasah / Sekolah,</p>
                    <div style="height: 60px;"></div>
                    <p style="margin: 0; font-weight: bold; text-decoration: underline;">
                        {{ $setting?->card_principal_name ?? '( ............................................... )' }}
                    </p>
                    <p style="margin: 2px 0 0 0;">NIP. {{ $setting?->card_principal_nip ?? '............................................' }}</p>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
