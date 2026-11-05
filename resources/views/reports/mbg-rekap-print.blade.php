<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Rekapitulasi Distribusi MBG - {{ $range['period_label'] }}</title>
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
                margin: 0;
                padding: 0;
            }
        }

        .document-title {
            text-align: center;
            margin: 14px 0 12px 0;
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
            margin: 4px 0 0 0;
        }

        /* Standard Data Table */
        table.report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 9.5pt;
        }

        table.report-table th,
        table.report-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: middle;
        }

        table.report-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9pt;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /* Signatures section */
        .signature-section {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
        }

        .signature-table {
            width: 100%;
            border: none;
            border-collapse: collapse;
        }

        .signature-table td {
            border: none;
            padding: 0;
            vertical-align: top;
            text-align: center;
            font-size: 10pt;
        }
    </style>
</head>
<body>

    <!-- No-Print Toolbar -->
    <div class="no-print-toolbar">
        <div>
            <span style="font-weight: bold; font-size: 14px;">Pratinjau Cetak Rekapitulasi MBG</span>
            <span style="font-size: 12px; color: #64748b; margin-left: 8px;">Periode: {{ $range['period_label'] }}</span>
        </div>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn btn-print">
                <span>🖨️ Cetak / Simpan PDF</span>
            </button>
            <a href="{{ route('mbg.rekap.index', request()->query()) }}" class="btn btn-back">
                <span>&larr; Kembali</span>
            </a>
        </div>
    </div>

    {{-- 1. WAJIB: KOP SURAT RESMI DENGAN BASE64 --}}
    <x-kop-surat :useBase64="true" />

    {{-- 2. JUDUL DOKUMEN / LAPORAN --}}
    <div class="document-title">
        <h2>LAPORAN REKAPITULASI PENYALURAN MAKAN BERGIZI GRATIS (MBG)</h2>
        <p><strong>Periode:</strong> {{ $range['period_label'] }} ({{ $range['start_date']->translatedFormat('d F Y') }} s.d. {{ $range['end_date']->translatedFormat('d F Y') }})</p>
    </div>

    {{-- 3. TABEL DATA REKAPITULASI --}}
    <table class="report-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 4%;">No</th>
                <th rowspan="2" style="width: 9%;">Tanggal</th>
                <th rowspan="2">Menu Makanan</th>
                <th colspan="2">Sasaran Siswa</th>
                <th colspan="2">Sasaran Guru & Staf</th>
                <th rowspan="2" style="width: 8%;">Total Porsi Disalurkan</th>
                <th rowspan="2" style="width: 6%;">Sisa Porsi</th>
                <th rowspan="2" style="width: 6%;">Capaian (%)</th>
                <th rowspan="2" style="width: 14%;">Keterangan</th>
            </tr>
            <tr>
                <th style="width: 7%;">Target</th>
                <th style="width: 8%;">Realisasi</th>
                <th style="width: 7%;">Target</th>
                <th style="width: 8%;">Realisasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($distributions as $idx => $item)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center">{{ $item->date ? $item->date->format('d/m/Y') : '-' }}</td>
                    <td>{{ $item->menu_name }}</td>
                    <td class="text-center">{{ number_format($item->target_students, 0, ',', '.') }}</td>
                    <td class="text-center" style="font-weight: bold;">{{ number_format($item->distributed_students, 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($item->target_teachers, 0, ',', '.') }}</td>
                    <td class="text-center" style="font-weight: bold;">{{ number_format($item->distributed_teachers, 0, ',', '.') }}</td>
                    <td class="text-center" style="font-weight: bold;">{{ number_format($item->total_distributed, 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($item->leftover_portions, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $item->success_rate }}%</td>
                    <td>{{ $item->notes ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 18px;">
                        <em>Tidak ada catatan data distribusi makanan bergizi pada periode ini.</em>
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($distributions->isNotEmpty())
            <tfoot>
                <tr style="font-weight: bold; background-color: #f2f2f2;">
                    <td colspan="3" class="text-center" style="text-transform: uppercase;">Jumlah Total Periode</td>
                    <td class="text-center">{{ number_format($stats['target_students'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($stats['distributed_students'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($stats['target_teachers'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($stats['distributed_teachers'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($stats['total_distributed'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($stats['total_leftovers'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ $stats['success_rate'] }}%</td>
                    <td class="text-center" style="font-size: 8.5pt;">Rata-rata: {{ $stats['avg_daily_distributed'] }} porsi/hari</td>
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- 4. BAGIAN TANDA TANGAN RESMI --}}
    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td style="width: 50%;">
                    <p style="margin: 0;">Mengetahui / Memeriksa,</p>
                    <p style="margin: 2px 0 0 0; font-weight: bold;">Kepala Madrasah / Sekolah</p>
                    <div style="height: 60px;"></div>
                    <p style="margin: 0; font-weight: bold; text-decoration: underline;">
                        {{ $setting?->card_principal_name ?? '( ................................................... )' }}
                    </p>
                    <p style="margin: 2px 0 0 0;">
                        NIP. {{ $setting?->card_principal_nip ?? '....................................................' }}
                    </p>
                </td>
                <td style="width: 50%;">
                    <p style="margin: 0;">
                        {{ $setting?->school_district ?: ($setting?->district ?: ($setting?->school_address ? trim(explode(',', $setting->school_address)[0]) : 'Tempat')) }}, 
                        {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                    </p>
                    <p style="margin: 2px 0 0 0; font-weight: bold;">Petugas Distribusi MBG,</p>
                    <div style="height: 60px;"></div>
                    <p style="margin: 0; font-weight: bold; text-decoration: underline;">
                        {{ auth()->user()->name ?? '( ................................................... )' }}
                    </p>
                    <p style="margin: 2px 0 0 0;">
                        NIP. {{ auth()->user()->officer_nip ?? (auth()->user()->teacher?->nip ?? '-') }}
                    </p>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
